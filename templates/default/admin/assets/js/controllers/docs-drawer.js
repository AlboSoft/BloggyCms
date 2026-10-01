(function() {
    'use strict';

    function initDocsDrawer() {
        const drawerEl = document.getElementById('docsOffcanvasDrawer');
        if (!drawerEl) return;

        const drawerTitle = document.getElementById('docsDrawerTitle');
        const drawerBody = document.getElementById('docsDrawerBody');
        const drawerFullLink = document.getElementById('docsDrawerFullLink');

        let offcanvasInstance = null;
        if (window.bootstrap && window.bootstrap.Offcanvas) {
            offcanvasInstance = window.bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
        }

        window.openDocsDrawer = function(section, docKey) {
            if (!offcanvasInstance && window.bootstrap && window.bootstrap.Offcanvas) {
                offcanvasInstance = window.bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
            }

            if (drawerBody) {
                drawerBody.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Загрузка документации...</p>
                    </div>
                `;
            }

            if (offcanvasInstance) {
                offcanvasInstance.show();
            }

            const url = new URL(window.ADMIN_URL + '/docs/api', window.location.origin);
            if (section) url.searchParams.set('section', section);
            if (docKey) url.searchParams.set('doc', docKey);

            fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (drawerTitle) drawerTitle.textContent = data.title;
                    if (drawerBody) drawerBody.innerHTML = data.html;
                    if (drawerFullLink) {
                        drawerFullLink.href = data.full_url;
                        drawerFullLink.style.display = '';
                    }

                    if (drawerBody) {
                        drawerBody.querySelectorAll('a[data-doc-link]').forEach(link => {
                            link.addEventListener('click', function(e) {
                                e.preventDefault();
                                const doc = this.getAttribute('data-doc-link');
                                if (doc) {
                                    window.openDocsDrawer(null, doc);
                                }
                            });
                        });
                    }
                } else {
                    if (drawerBody) {
                        drawerBody.innerHTML = `<div class="alert alert-warning">${data.html || 'Не удалось загрузить справку.'}</div>`;
                    }
                }
            })
            .catch(err => {
                console.error('Docs drawer error:', err);
                if (drawerBody) {
                    drawerBody.innerHTML = `<div class="alert alert-danger">Ошибка сети при получении документации.</div>`;
                }
            });
        };

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-open-docs]');
            if (btn) {
                e.preventDefault();
                const section = btn.getAttribute('data-docs-section') || '';
                const doc = btn.getAttribute('data-docs-file') || '';
                window.openDocsDrawer(section, doc);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDocsDrawer);
    } else {
        initDocsDrawer();
    }
})();
