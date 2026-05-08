class NotificationSystem {
    constructor() {
        this.init();
    }

    init() {
        const toastData = document.getElementById('notification-data');
        if (toastData) {
            this.showSessionNotification(toastData);
        }
        
        this.initNotificationsSystem();
    }

    showSessionNotification(toastData) {
        const message = toastData.dataset.message;
        const type = toastData.dataset.type;
        const position = toastData.dataset.position || 'top-right';
        const delay = 3000;

        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            
            this.applyPositionStyles(container, position);
            document.body.appendChild(container);
        }
        
        const toastEl = document.createElement('div');
        toastEl.className = 'toast';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        
        toastEl.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${lang === 'ru' ? 'Уведомление' : 'Notification'}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="${lang === 'ru' ? 'Закрыть' : 'Close'}"></button>
            </div>
            <div class="toast-body">${message}</div>
            <div class="toast-progress"></div>
        `;
        
        container.appendChild(toastEl);
        
        const toast = new bootstrap.Toast(toastEl, {
            animation: true,
            autohide: true,
            delay: delay
        });
        
        this.applyToastStyles(toastEl, type);
        setTimeout(() => {
            toastEl.classList.add('show');
        }, 100);

        this.startProgressBar(toastEl, delay);
        
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.classList.remove('show');
            setTimeout(() => {
                toastEl.remove();
                if (container.children.length === 0) {
                    container.remove();
                }
            }, 400);
        });

        toastEl.addEventListener('mouseenter', () => {
            this.pauseProgressBar(toastEl);
        });

        toastEl.addEventListener('mouseleave', () => {
            this.resumeProgressBar(toastEl, toast);
        });

        toastData.remove();
    }

    startProgressBar(toastEl, duration) {
        const progressBar = toastEl.querySelector('.toast-progress');
        if (!progressBar) return;

        progressBar.style.animation = 'none';
        progressBar.offsetHeight;

        progressBar.style.animation = `progressBar ${duration}ms linear forwards`;
        progressBar.style.animationPlayState = 'running';
        toastEl._progressStartTime = Date.now();
        toastEl._progressDuration = duration;
        toastEl._progressRemaining = duration;
    }

    pauseProgressBar(toastEl) {
        const progressBar = toastEl.querySelector('.toast-progress');
        if (!progressBar || !toastEl._progressStartTime) return;

        const elapsed = Date.now() - toastEl._progressStartTime;
        toastEl._progressRemaining = toastEl._progressDuration - elapsed;
        
        progressBar.style.animationPlayState = 'paused';
    }

    resumeProgressBar(toastEl, toastInstance) {
        const progressBar = toastEl.querySelector('.toast-progress');
        if (!progressBar || !toastEl._progressRemaining) return;

        progressBar.style.animation = 'none';
        progressBar.offsetHeight;

        progressBar.style.animation = `progressBar ${toastEl._progressRemaining}ms linear forwards`;
        progressBar.style.animationPlayState = 'running';
        
        toastEl._progressStartTime = Date.now();
        toastEl._progressDuration = toastEl._progressRemaining;
    }

    applyPositionStyles(container, position) {
        container.style.position = 'fixed';
        container.style.zIndex = '9999';
        container.style.padding = '1rem';
        
        switch(position) {
            case 'top-left':
                container.style.top = '0';
                container.style.left = '0';
                container.style.alignItems = 'flex-start';
                break;
            case 'top-right':
                container.style.top = '0';
                container.style.right = '0';
                container.style.alignItems = 'flex-end';
                break;
            case 'bottom-left':
                container.style.bottom = '0';
                container.style.left = '0';
                container.style.alignItems = 'flex-start';
                break;
            case 'bottom-right':
                container.style.bottom = '0';
                container.style.right = '0';
                container.style.alignItems = 'flex-end';
                break;
            case 'top-center':
                container.style.top = '0';
                container.style.left = '50%';
                container.style.transform = 'translateX(-50%)';
                container.style.alignItems = 'center';
                break;
            case 'bottom-center':
                container.style.bottom = '0';
                container.style.left = '50%';
                container.style.transform = 'translateX(-50%)';
                container.style.alignItems = 'center';
                break;
        }
    }

    applyToastStyles(toastEl, type) {
        const bgClass = `bg-${type}`;
        toastEl.classList.add(bgClass);
    }

    showNotification(message, type = 'primary', position = 'top-right', delay = 5000) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            this.applyPositionStyles(container, position);
            document.body.appendChild(container);
        }
        
        const toastEl = document.createElement('div');
        toastEl.className = 'toast';
        toastEl.setAttribute('role', 'alert');
        
        toastEl.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${lang === 'ru' ? 'Уведомление' : 'Notification'}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="${lang === 'ru' ? 'Закрыть' : 'Close'}"></button>
            </div>
            <div class="toast-body">${message}</div>
            <div class="toast-progress"></div>
        `;
        
        container.appendChild(toastEl);
        
        const toast = new bootstrap.Toast(toastEl, {
            animation: true,
            autohide: true,
            delay: delay
        });
        
        this.applyToastStyles(toastEl, type);
        
        setTimeout(() => {
            toastEl.classList.add('show');
        }, 100);

        this.startProgressBar(toastEl, delay);
        
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.classList.remove('show');
            setTimeout(() => {
                toastEl.remove();
                if (container.children.length === 0) {
                    container.remove();
                }
            }, 400);
        });

        toastEl.addEventListener('mouseenter', () => {
            this.pauseProgressBar(toastEl);
        });

        toastEl.addEventListener('mouseleave', () => {
            this.resumeProgressBar(toastEl, toast);
        });
        
        return toast;
    }

    initNotificationsSystem() {
        if (window.location.pathname.includes('/admin/')) {
            this.initAdminNotifications();
        }
    }

    initAdminNotifications() {
        this.adminNotifications = new AdminNotificationsSystem();
        this.adminNotifications.init();
    }
}

class AdminNotificationsSystem {
    constructor() {
        this.baseUrl = window.ADMIN_URL || '/admin';
        this.notificationBell = document.querySelector('.admin-btn-notifications');
        this.dropdownContent = document.getElementById('notifications-dropdown-content');
        this.unreadCount = 0;
        this.updateInterval = null;
        this.isInitialized = false;
    }

    init() {
        if (!this.notificationBell || this.isInitialized) return;

        this.isInitialized = true;
        
        this.loadUnreadCount();
        this.loadDropdownNotifications();
        this.updateInterval = setInterval(() => {
            this.loadUnreadCount();
        }, 30000);
        this.initEventListeners();
    }

    async loadUnreadCount() {
        try {
            const response = await fetch(`${this.baseUrl}/notifications/get-unread-count`);
            const data = await response.json();
            
            if (data.success) {
                this.updateBadge(data.count);
            } else {}
        } catch (error) {}
    }

    async loadDropdownNotifications() {
        if (!this.dropdownContent) return;

        try {
            const response = await fetch(`${this.baseUrl}/notifications/get-list?limit=5&unread_only=true`);
            const data = await response.json();
            
            if (data.success) {
                this.renderDropdownNotifications(data.notifications);
            } else {
                this.showDropdownError();
            }
        } catch (error) {
            this.showDropdownError();
        }
    }

    showDropdownError() {
        if (!this.dropdownContent) return;
        
        this.dropdownContent.innerHTML = `
            <div class="text-center py-3">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 32px;"></i>
                <p class="text-muted mt-2 mb-0">${lang === 'ru' ? 'Ошибка загрузки уведомлений' : 'Error loading notifications'}</p>
                <button class="btn btn-sm btn-primary mt-2" onclick="window.notificationSystem.adminNotifications.loadDropdownNotifications()">
                    ${lang === 'ru' ? 'Повторить' : 'Retry'}
                </button>
            </div>
        `;
    }

    updateBadge(count) {
        const badge = this.notificationBell.querySelector('.notification-badge');
        const prevCount = this.unreadCount;
        this.unreadCount = count;

        if (badge) {
            if (count > 0) {
                let sizeClass = '';
                if (count < 10) {
                    sizeClass = 'small';
                } else if (count > 99) {
                    sizeClass = 'large';
                    count = '99+';
                } else {
                    sizeClass = '';
                }
                
                badge.textContent = count;
                badge.style.display = 'flex';
                badge.className = 'notification-badge ' + sizeClass + ' inside';
                
                if (count > prevCount) {
                    badge.classList.add('pulse');
                    setTimeout(() => badge.classList.remove('pulse'), 2000);
                    
                    this.updatePageTitle(count);

                    this.animateBell();
                }
            } else {
                badge.style.display = 'none';
                this.updatePageTitle(0);
            }
        }
    }

    animateBell() {
        const bellIcon = this.notificationBell.querySelector('.btn-icon-wrapper i');
        if (bellIcon) {
            bellIcon.style.transform = 'rotate(20deg)';
            setTimeout(() => {
                bellIcon.style.transform = 'rotate(-20deg)';
                setTimeout(() => {
                    bellIcon.style.transform = 'rotate(0deg)';
                }, 150);
            }, 150);
        }
    }

    updatePageTitle(count) {
        const title = document.title;
        const cleanTitle = title.replace(/^\(\d+\)\s*/, '');
        
        if (count > 0) {
            document.title = `(${count}) ${cleanTitle}`;
        } else {
            document.title = cleanTitle;
        }
    }

    renderDropdownNotifications(notifications) {
        if (!this.dropdownContent) return;

        if (notifications.length === 0) {
            this.dropdownContent.innerHTML = `
                <div class="text-center py-3">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 32px;"></i>
                    <p class="text-muted mt-2 mb-0">${lang === 'ru' ? 'Нет непрочитанных уведомлений' : 'No unread notifications'}</p>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const isNew = !notification.is_read ? 'notification-new' : '';
            const isComment = notification.type === 'new_comment';
            const commentClass = isComment ? 'notification-comment' : '';
            const icon = isComment ? 'chat-left-text' : (notification.icon || 'bell');
            
            html += `
            <div class="notification-item-dropdown border-bottom pb-2 mb-2 ${isNew} ${commentClass}" 
                data-id="${notification.id}"
                data-type="${notification.type}"
                data-post-id="${notification.data?.post_id || ''}"
                style="cursor: pointer;"
                onclick="window.notificationSystem.adminNotifications.handleNotificationClick(event, ${notification.id}, '${notification.type}')">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-${notification.color || 'primary'} text-white d-flex align-items-center justify-content-center" 
                            style="width: 32px; height: 32px; font-size: 14px;">
                            <i class="bi bi-${icon}"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-2" style="font-size: 13px;">
                        <div class="d-flex justify-content-between">
                            <strong class="text-truncate" style="max-width: 180px;">
                                ${this.escapeHtml(notification.title || (lang === 'ru' ? 'Уведомление' : 'Notification'))}
                            </strong>
                            <small class="text-muted">${notification.time || ''}</small>
                        </div>
                        <div class="notification-content">
                            ${notification.message}
                        </div>
                        ${notification.created_by ? `
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-person"></i> ${this.escapeHtml(notification.created_by)}
                        </small>
                        ` : ''}
                        
                        ${isComment && notification.data?.post_title ? `
                        <div class="mt-1">
                            <a href="${BASE_URL || ''}/post/${notification.data?.post_slug || ''}" 
                            class="text-decoration-none text-primary small d-block"
                            onclick="event.stopPropagation();"
                            target="_blank">
                                <i class="bi bi-file-text"></i> 
                                ${this.truncateText(this.escapeHtml(notification.data.post_title), 30)}
                            </a>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            `;
        });

        this.dropdownContent.innerHTML = html;
    }

    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    handleNotificationClick(event, id, type) {
        event.preventDefault();
        event.stopPropagation();
        
        this.markAsRead(id);
        
        const dropdown = this.notificationBell.closest('.dropdown');
        const bsDropdown = bootstrap.Dropdown.getInstance(dropdown);
        if (bsDropdown) {
            bsDropdown.hide();
        }
        
        if (type === 'new_comment') {
            window.location.href = `${this.baseUrl}/comments`;
        } else {
            window.location.href = `${this.baseUrl}/notifications`;
        }
    }

    async markAsRead(id, reload = true) {
        try {
            const response = await fetch(`${this.baseUrl}/notifications/mark-as-read/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
            });
            
            const data = await response.json();
            
            if (data.success && reload) {
                this.loadDropdownNotifications();
                this.loadUnreadCount();
            }
        } catch (error) {}
    }

    async markAllAsRead() {
        if (this.unreadCount === 0) {
            window.notificationSystem.showNotification(lang === 'ru' ? 'Нет непрочитанных уведомлений' : 'No unread notifications', 'warning');
            return;
        }

        if (confirm(lang === 'ru' ? 'Отметить все уведомления как прочитанные?' : 'Mark all notifications as read?')) {
            try {
                const response = await fetch(`${this.baseUrl}/notifications/mark-all-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.loadDropdownNotifications();
                    this.loadUnreadCount();
                    window.notificationSystem.showNotification(lang === 'ru' ? 'Все уведомления отмечены как прочитанные' : 'All notifications marked as read', 'success');
                }
            } catch (error) {}
        }
    }

    async clearReadNotifications() {
        if (confirm(lang === 'ru' ? 'Удалить все прочитанные уведомления?' : 'Delete all read notifications?')) {
            try {
                const response = await fetch(`${this.baseUrl}/notifications/clear`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (window.location.pathname.includes('/notifications')) {
                        window.location.reload();
                    } else {
                        window.notificationSystem.showNotification(lang === 'ru' ? 'Прочитанные уведомления удалены' : 'Read notifications deleted', 'success');
                    }
                }
            } catch (error) {}
        }
    }

    initEventListeners() {
        const markAllBtn = document.getElementById('mark-all-read-dropdown');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.markAllAsRead();
            });
        }

        const dropdown = this.notificationBell.closest('.dropdown');
        if (dropdown) {
            dropdown.addEventListener('show.bs.dropdown', () => {
                this.loadDropdownNotifications();
            });
        }

        document.addEventListener('click', (e) => {
            if (e.target && e.target.id === 'mark-all-read-btn') {
                e.preventDefault();
                this.markAllAsRead();
            }
            
            if (e.target && e.target.id === 'clear-read-btn') {
                e.preventDefault();
                this.clearReadNotifications();
            }
        });
    }

    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
        this.isInitialized = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.notificationSystem = new NotificationSystem();
    
    if (document.getElementById('notifications-list')) {
        initNotificationsPage();
    }
});

function initNotificationsPage() {
    loadNotificationsPage();
    
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('mark-read-btn') || e.target.closest('.mark-read-btn')) {
            const btn = e.target.classList.contains('mark-read-btn') ? e.target : e.target.closest('.mark-read-btn');
            const id = btn.dataset.id;
            await markAsReadPage(id);
        }
        
        if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
            const btn = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
            const id = btn.dataset.id;
            await deleteNotificationPage(id);
        }
    });
}

async function loadNotificationsPage() {
    const container = document.getElementById('notifications-list');
    if (!container) return;
    
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">${lang === 'ru' ? 'Загрузка...' : 'Loading...'}</span>
            </div>
            <p class="text-muted mt-2">${lang === 'ru' ? 'Загрузка уведомлений...' : 'Loading notifications...'}</p>
        </div>
    `;
    
    try {
        const response = await fetch('/admin/notifications/get-list?limit=50');
        const data = await response.json();
        
        if (data.success) {
            renderNotificationsPage(data.notifications);
        }
    } catch (error) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
                <p class="text-muted mt-3">${lang === 'ru' ? 'Ошибка загрузки уведомлений' : 'Error loading notifications'}</p>
                <button class="btn btn-primary" onclick="loadNotificationsPage()">${lang === 'ru' ? 'Повторить' : 'Retry'}</button>
            </div>
        `;
    }
}

function renderNotificationsPage(notifications) {
    const container = document.getElementById('notifications-list');
    if (!container) return;
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 48px;"></i>
                <p class="text-muted mt-3">${lang === 'ru' ? 'У вас пока нет уведомлений' : 'You have no notifications yet'}</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        const isReadClass = notification.is_read ? 'opacity-75' : 'notification-unread';
        const badgeColor = notification.is_read ? 'secondary' : 'primary';
        const isComment = notification.type === 'new_comment';
        
        html += `
        <div class="notification-item ${isReadClass} border-bottom pb-3 mb-3" id="notification-${notification.id}">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <div class="rounded-circle bg-${notification.color || 'primary'} text-white d-flex align-items-center justify-content-center" 
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-${notification.icon || 'bell'}"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="mb-1">${escapeHtml(notification.title || (lang === 'ru' ? 'Уведомление' : 'Notification'))}</h6>
                        <div>
                            <span class="badge bg-${badgeColor} me-2">
                                ${notification.is_read ? (lang === 'ru' ? 'Прочитано' : 'Read') : (lang === 'ru' ? 'Новое' : 'New')}
                            </span>
                            <small class="text-muted">${notification.time_full || notification.time || ''}</small>
                        </div>
                    </div>
                    
                    <div class="notification-content mb-2">
                        ${notification.message}
                    </div>
                    
                    ${notification.created_by ? `
                    <small class="text-muted d-block mb-2">
                        <i class="bi bi-person"></i> ${lang === 'ru' ? 'От:' : 'From:'} ${escapeHtml(notification.created_by)}
                    </small>
                    ` : ''}
                    
                    ${isComment && notification.data?.post_title ? `
                    <div class="card bg-light border mb-2">
                        <div class="card-body py-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-text text-primary me-2"></i>
                                <div>
                                    <a href="${BASE_URL || ''}/post/${notification.data?.post_slug || ''}" 
                                    class="text-decoration-none text-primary fw-bold"
                                    target="_blank">
                                        ${escapeHtml(notification.data.post_title)}
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        ${lang === 'ru' ? 'ID комментария:' : 'Comment ID:'} ${notification.data.comment_id || '—'}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <div class="mt-2 d-flex gap-2">
                        ${!notification.is_read ? `
                        <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="${notification.id}">
                            <i class="bi bi-check me-1"></i> ${lang === 'ru' ? 'Прочитано' : 'Mark read'}
                        </button>
                        ` : ''}
                        
                        ${isComment && notification.data?.post_id ? `
                        <a href="${ADMIN_URL || ''}/comments" 
                        class="btn btn-sm btn-outline-info"
                        target="_blank">
                            <i class="bi bi-chat-dots me-1"></i> ${lang === 'ru' ? 'К комментариям' : 'To comments'}
                        </a>
                        ` : ''}
                        
                        <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${notification.id}">
                            <i class="bi bi-trash me-1"></i> ${lang === 'ru' ? 'Удалить' : 'Delete'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    });
    
    container.innerHTML = html;
}

async function markAsReadPage(id) {
    try {
        const response = await fetch(`/admin/notifications/mark-as-read/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const notificationEl = document.getElementById(`notification-${id}`);
            if (notificationEl) {
                notificationEl.classList.remove('notification-unread');
                notificationEl.classList.add('opacity-75');
                const badge = notificationEl.querySelector('.badge');
                if (badge) {
                    badge.className = 'badge bg-secondary me-2';
                    badge.textContent = lang === 'ru' ? 'Прочитано' : 'Read';
                }
                
                const markReadBtn = notificationEl.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            }
            
            if (window.notificationSystem && window.notificationSystem.adminNotifications) {
                window.notificationSystem.adminNotifications.loadUnreadCount();
            }
        }
    } catch (error) {
        alert(lang === 'ru' ? 'Ошибка при обновлении уведомления' : 'Error updating notification');
    }
}

async function deleteNotificationPage(id) {
    if (!confirm(lang === 'ru' ? 'Удалить это уведомление?' : 'Delete this notification?')) return;
    
    try {
        const response = await fetch(`/admin/notifications/delete/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const notificationEl = document.getElementById(`notification-${id}`);
            if (notificationEl) {
                notificationEl.remove();
            }
            
            if (window.notificationSystem && window.notificationSystem.adminNotifications) {
                window.notificationSystem.adminNotifications.loadUnreadCount();
            }
            
            const container = document.getElementById('notifications-list');
            if (container && container.children.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 48px;"></i>
                        <p class="text-muted mt-3">${lang === 'ru' ? 'У вас пока нет уведомлений' : 'You have no notifications yet'}</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        alert(lang === 'ru' ? 'Ошибка при удалении уведомления' : 'Error deleting notification');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.AdminNotifications = {
    showNotification: (message, type = 'primary') => {
        if (window.notificationSystem) {
            window.notificationSystem.showNotification(message, type);
        }
    },
    
    updateCount: () => {
        if (window.notificationSystem && window.notificationSystem.adminNotifications) {
            window.notificationSystem.adminNotifications.loadUnreadCount();
        }
    },
    
    reloadDropdown: () => {
        if (window.notificationSystem && window.notificationSystem.adminNotifications) {
            window.notificationSystem.adminNotifications.loadDropdownNotifications();
        }
    }
};