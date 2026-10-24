/**
* Действия в списке постов: лайк и закладка.
* Используется на страницах /posts, /category/{slug} и /tag/{slug}.
*/
(function () {
    if (window.__bloggyPostListInit) {
        return;
    }
    window.__bloggyPostListInit = true;

    document.addEventListener('DOMContentLoaded', function () {
        const list = document.querySelector('.tg-posts-list');
        if (!list) {
            return;
        }

        const baseUrl = list.dataset.baseUrl || '';
        const isLoggedIn = list.dataset.loggedIn === '1';

        function redirectToLogin() {
            window.location.href = baseUrl + '/login';
        }

        function setIcon(button, name, active) {
            const icon = button.querySelector('svg use');
            if (!icon) {
                return;
            }
            const href = icon.getAttribute('href') || '';
            const next = href.replace(new RegExp(name + '(-fill)?'), active ? name + '-fill' : name);
            if (next !== href) {
                icon.setAttribute('href', next);
            }
        }

        function setActive(button, active) {
            button.classList.toggle('tg-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        }

        /**
        * Отправляет POST-запрос и возвращает JSON-ответ.
        * @param {string} url
        * @returns {Promise<object>}
        */
        function request(url) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            });
        }

        list.addEventListener('click', function (event) {
            const likeButton = event.target.closest('.tg-like-btn');
            if (likeButton) {
                event.preventDefault();

                if (!isLoggedIn) {
                    redirectToLogin();
                    return;
                }

                if (likeButton.dataset.busy === '1') {
                    return;
                }
                likeButton.dataset.busy = '1';

                const postId = likeButton.dataset.postId;
                const countSpan = likeButton.querySelector('span');
                const wasActive = likeButton.classList.contains('tg-active');
                const previousCount = countSpan ? (parseInt(countSpan.textContent, 10) || 0) : 0;

                setActive(likeButton, !wasActive);
                setIcon(likeButton, 'heart', !wasActive);
                if (countSpan) {
                    countSpan.textContent = wasActive ? Math.max(previousCount - 1, 0) : previousCount + 1;
                }

                const revert = function () {
                    setActive(likeButton, wasActive);
                    setIcon(likeButton, 'heart', wasActive);
                    if (countSpan) {
                        countSpan.textContent = previousCount;
                    }
                };

                request(baseUrl + '/post/like/' + encodeURIComponent(postId))
                    .then(function (data) {
                        if (!data || data.success !== true) {
                            revert();
                            return;
                        }
                        setActive(likeButton, !!data.liked);
                        setIcon(likeButton, 'heart', !!data.liked);
                        if (countSpan && typeof data.likes_count !== 'undefined') {
                            countSpan.textContent = data.likes_count;
                        }
                    })
                    .catch(revert)
                    .finally(function () {
                        delete likeButton.dataset.busy;
                    });

                return;
            }

            const bookmarkButton = event.target.closest('.tg-bookmark-btn');
            if (bookmarkButton) {
                event.preventDefault();

                if (!isLoggedIn) {
                    redirectToLogin();
                    return;
                }

                if (bookmarkButton.dataset.busy === '1') {
                    return;
                }
                bookmarkButton.dataset.busy = '1';

                const postId = bookmarkButton.dataset.postId;
                const wasActive = bookmarkButton.classList.contains('tg-active');

                setActive(bookmarkButton, !wasActive);
                setIcon(bookmarkButton, 'bookmark', !wasActive);

                const revert = function () {
                    setActive(bookmarkButton, wasActive);
                    setIcon(bookmarkButton, 'bookmark', wasActive);
                };

                request(baseUrl + '/post/bookmark/' + encodeURIComponent(postId))
                    .then(function (data) {
                        if (!data || data.success !== true) {
                            revert();
                            return;
                        }
                        setActive(bookmarkButton, !!data.bookmarked);
                        setIcon(bookmarkButton, 'bookmark', !!data.bookmarked);
                    })
                    .catch(revert)
                    .finally(function () {
                        delete bookmarkButton.dataset.busy;
                    });
            }
        });
    });
})();