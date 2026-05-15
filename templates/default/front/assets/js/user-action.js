class ProfileManager {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setupLikeHandlers();
            this.setupBookmarkHandlers();
            this.setupBookmarkRemovalHandlers();
            this.setupCommentScroll();
        });
    }

    setupLikeHandlers() {
        document.querySelectorAll('.btn-like').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
        });

        document.querySelectorAll('.btn-like').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleLike(btn);
            });
        });
    }

    async handleLike(button) {
        
        if (!window.userLoggedIn) {
            window.location.href = window.baseUrl + '/login';
            return;
        }
        
        const postId = button.dataset.postId;
        const countSpan = button.querySelector('.like-count');
        const icon = button.querySelector('svg');
        const originalHTML = button.innerHTML;
        const originalCount = countSpan ? countSpan.textContent : '0';
        const originalIconHref = icon ? icon.querySelector('use').getAttribute('href') : '';

        button.disabled = true;
        button.style.opacity = '0.7';
        
        try {
            const response = await fetch(`${window.baseUrl}/post/like/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                const updateCounter = (span) => {
                    if (!span) return;
                    
                    span.style.transition = 'all 0.3s ease';
                    span.style.transform = 'scale(1.5)';
                    span.style.color = '#ff0000';
                    span.textContent = data.likes_count;
                    span.setAttribute('data-last-value', data.likes_count);
                    setTimeout(() => {
                        span.style.transform = 'scale(1)';
                        span.style.color = '';
                    }, 300);
                };
                
                const updateIcon = (btn) => {
                    const icon = btn.querySelector('svg use');
                    if (icon) {
                        if (data.liked) {
                            icon.setAttribute('href', icon.getAttribute('href').replace('#heart', '#heart-fill'));
                            btn.classList.add('liked');
                        } else {
                            icon.setAttribute('href', icon.getAttribute('href').replace('#heart-fill', '#heart'));
                            btn.classList.remove('liked');
                        }
                    }
                };
                
                updateCounter(countSpan);
                updateIcon(button);
                
                document.querySelectorAll(`.btn-like[data-post-id="${postId}"]`).forEach(otherBtn => {
                    if (otherBtn !== button) {
                        const otherCount = otherBtn.querySelector('.like-count');
                        updateCounter(otherCount);
                        updateIcon(otherBtn);
                    }
                });
                
                setTimeout(() => {
                    const checkSpan = button.querySelector('.like-count');
                    if (checkSpan && checkSpan.textContent !== String(data.likes_count)) {
                        
                        checkSpan.textContent = data.likes_count;
                        checkSpan.style.backgroundColor = '#ffeb3b';
                        checkSpan.style.border = '2px solid red';
                        
                        setTimeout(() => {
                            checkSpan.style.backgroundColor = '';
                            checkSpan.style.border = '';
                        }, 2000);
                    }
                }, 500);
                
            } else {
                if (countSpan) countSpan.textContent = originalCount;
                if (icon && originalIconHref) icon.querySelector('use').setAttribute('href', originalIconHref);
            }
            
        } catch (error) {
            if (countSpan) countSpan.textContent = originalCount;
            if (icon && originalIconHref) icon.querySelector('use').setAttribute('href', originalIconHref);
        } finally {
            button.disabled = false;
            button.style.opacity = '';
            button.innerHTML = button.innerHTML;
        }
    }

    setupBookmarkHandlers() {
        document.querySelectorAll('.btn-bookmark:not(.remove-bookmark)').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
        });
        
        document.querySelectorAll('.btn-bookmark:not(.remove-bookmark)').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleBookmarkToggle(btn);
            });
        });
    }

    async handleBookmarkToggle(button) {
        if (!window.userLoggedIn) {
            window.location.href = window.baseUrl + '/login';
            return;
        }
        
        const postId = button.dataset.postId;
        const icon = button.querySelector('svg');
        button.disabled = true;
        button.style.opacity = '0.7';
        
        try {
            const response = await fetch(`${window.baseUrl}/post/bookmark/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (icon) {
                    const use = icon.querySelector('use');
                    if (use) {
                        if (data.bookmarked) {
                            use.setAttribute('href', use.getAttribute('href').replace('#bookmark', '#bookmark-fill'));
                            button.classList.add('bookmarked');
                        } else {
                            use.setAttribute('href', use.getAttribute('href').replace('#bookmark-fill', '#bookmark'));
                            button.classList.remove('bookmarked');
                        }
                    }
                }
                
                document.querySelectorAll(`.btn-bookmark[data-post-id="${postId}"]:not(.remove-bookmark)`).forEach(otherBtn => {
                    if (otherBtn !== button) {
                        const otherIcon = otherBtn.querySelector('svg');
                        if (otherIcon) {
                            const otherUse = otherIcon.querySelector('use');
                            if (otherUse) {
                                if (data.bookmarked) {
                                    otherUse.setAttribute('href', otherUse.getAttribute('href').replace('#bookmark', '#bookmark-fill'));
                                    otherBtn.classList.add('bookmarked');
                                } else {
                                    otherUse.setAttribute('href', otherUse.getAttribute('href').replace('#bookmark-fill', '#bookmark'));
                                    otherBtn.classList.remove('bookmarked');
                                }
                            }
                        }
                    }
                });
                
                if (!data.bookmarked && window.location.pathname.includes('/bookmarks')) {
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                }
                
            } else {}
        } catch (error) {
            console.error(lang === 'ru' ? 'Ошибка сети:' : 'Network error:', error);
        } finally {
            button.disabled = false;
            button.style.opacity = '';
        }
    }

    setupBookmarkRemovalHandlers() {
        
        document.querySelectorAll('.remove-bookmark').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
        });
        
        document.querySelectorAll('.remove-bookmark').forEach((btn, index) => {
            
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleBookmarkRemoval(btn);
            });
        });
        
    }

    async handleBookmarkRemoval(button) {
        
        const postId = button.dataset.postId;
        const card = button.closest('.bookmark-item');
        
        if (!postId || !card) {
            return;
        }
        
        const originalContent = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = window.bloggyIcon ? window.bloggyIcon('bs', 'arrow-clockwise', '14', 'currentColor') : '...';
        button.style.opacity = '0.7';
        
        try {
            const response = await fetch(`${window.baseUrl}/post/bookmark/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success && !data.bookmarked) {
                
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateX(-100px)';
                card.style.marginTop = '-20px';
                card.style.marginBottom = '0';
                
                setTimeout(() => {
                    card.remove();
                    this.updateBookmarksCounter();
                    const remainingBookmarks = document.querySelectorAll('.bookmark-item').length;
                    
                    if (remainingBookmarks === 0) {
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    }
                    
                    this.showNotification(lang === 'ru' ? 'Публикация удалена из закладок' : 'Publication removed from bookmarks', 'success');
                    
                }, 400);
                
            } else {
                this.showNotification(lang === 'ru' ? 'Ошибка при удалении закладки' : 'Error removing bookmark', 'error');
                
                button.innerHTML = originalContent;
                button.disabled = false;
                button.style.opacity = '';
            }
            
        } catch (error) {
            this.showNotification(lang === 'ru' ? 'Ошибка сети. Пожалуйста, попробуйте еще раз.' : 'Network error. Please try again.', 'error');
            button.innerHTML = originalContent;
            button.disabled = false;
            button.style.opacity = '';
        }
    }

    updateBookmarksCounter() {
        
        const counterElement = document.querySelector('.text-muted.small.mb-0');
        if (counterElement) {
            const currentCount = document.querySelectorAll('.bookmark-item').length;
            const iconHtml = window.bloggyIcon ? window.bloggyIcon('bs', 'journal-bookmark', '14', '#6c757d', 'me-1') : '';
            counterElement.innerHTML = `${iconHtml} ${currentCount} ${lang === 'ru' ? 'сохранённых публикаций' : 'saved publications'}`;
            counterElement.style.transition = 'all 0.3s ease';
            counterElement.style.transform = 'scale(1.1)';
            counterElement.style.color = '#dc3545';
            
            setTimeout(() => {
                counterElement.style.transform = 'scale(1)';
                counterElement.style.color = '';
            }, 300);
        }
    }

    setupCommentScroll() {
        if (typeof scrollToCommentForm === 'function') {
            window.scrollToCommentForm = function() {
                const commentForm = document.getElementById('comment-form');
                if (commentForm) {
                    commentForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    setTimeout(() => {
                        const contentInput = document.getElementById('content');
                        if (contentInput) {
                            contentInput.focus();
                        }
                    }, 300);
                }
            };
        }

        if (window.location.hash === '#comments' || window.location.hash.startsWith('#comment-')) {
            setTimeout(() => {
                const element = document.querySelector(window.location.hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    if (window.location.hash.startsWith('#comment-')) {
                        element.classList.add('highlighted-comment');
                        setTimeout(() => {
                            element.classList.remove('highlighted-comment');
                        }, 3000);
                    }
                }
            }, 500);
        }
    }

    showNotification(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 280px;
            max-width: 350px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
            backdrop-filter: blur(8px);
        `;
        
        const icon = type === 'success' ? 
            '<div class="rounded-circle bg-success bg-opacity-10 p-2 me-2" style="width: 36px; height: 36px;">' +
            '<i class="bi bi-check-circle-fill text-success"></i></div>' : 
            '<div class="rounded-circle bg-danger bg-opacity-10 p-2 me-2" style="width: 36px; height: 36px;">' +
            '<i class="bi bi-exclamation-triangle-fill text-danger"></i></div>';
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                ${icon}
                <div class="flex-grow-1" style="font-size: 0.9rem;">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
}

new ProfileManager();