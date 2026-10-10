class NotificationSystem {
    constructor() {
        this.config = this.readConfig();
        this.stack = null;
        this.items = [];
        this.audioCtx = null;
        this.init();
    }

    readConfig() {
        const raw = window.ADMIN_NOTIFY_CONFIG || {};
        const flag = (value, fallback) => {
            if (value === undefined || value === null || value === '') return fallback;
            return value === true || value === 1 || value === '1';
        };
        const durationSec = Math.max(1, parseInt(raw.duration, 10) || 4);
        return {
            position: raw.position || 'top-right',
            style: raw.style || 'aurora',
            animation: raw.animation || 'slide',
            size: raw.size || 'normal',
            duration: durationSec * 1000,
            maxVisible: Math.max(1, parseInt(raw.maxVisible, 10) || 4),
            showProgress: flag(raw.showProgress, true),
            pauseOnHover: flag(raw.pauseOnHover, true),
            showIcon: flag(raw.showIcon, true),
            showTitle: flag(raw.showTitle, true),
            sound: flag(raw.sound, false)
        };
    }

    init() {
        this.ensureStack();
        const toastData = document.getElementById('notification-data');
        if (toastData) {
            this.showSessionNotification(toastData);
        }
        this.initNotificationsSystem();
    }

    ensureStack() {
        if (this.stack && document.body.contains(this.stack)) {
            this.applyStackPosition(this.stack, this.config.position);
            return this.stack;
        }
        let stack = document.getElementById('admin-notify-root');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'admin-notify-root';
            document.body.appendChild(stack);
        }
        stack.className = 'admin-notify-stack';
        stack.setAttribute('aria-live', 'polite');
        stack.setAttribute('aria-relevant', 'additions');
        this.applyStackPosition(stack, this.config.position);
        this.stack = stack;
        return stack;
    }

    applyStackPosition(stack, position) {
        const pos = position || 'top-right';
        stack.classList.remove('is-top', 'is-bottom', 'is-left', 'is-right', 'is-center');
        if (pos.indexOf('top') === 0) stack.classList.add('is-top');
        if (pos.indexOf('bottom') === 0) stack.classList.add('is-bottom');
        if (pos.indexOf('left') !== -1) stack.classList.add('is-left');
        if (pos.indexOf('right') !== -1) stack.classList.add('is-right');
        if (pos.indexOf('center') !== -1) stack.classList.add('is-center');
        stack.dataset.position = pos;
    }

    normalizeType(type) {
        const map = { error: 'danger', err: 'danger', ok: 'success', fail: 'danger', alert: 'warning' };
        type = String(type || 'primary').toLowerCase();
        return map[type] || type;
    }

    typeMeta(type) {
        const ru = (typeof lang !== 'undefined' ? lang : 'ru') === 'ru';
        const titles = {
            success: ru ? 'Успешно' : 'Success',
            danger: ru ? 'Ошибка' : 'Error',
            warning: ru ? 'Внимание' : 'Warning',
            info: ru ? 'Информация' : 'Info',
            primary: ru ? 'Уведомление' : 'Notification'
        };
        return {
            title: titles[type] || titles.primary
        };
    }

    iconSvg(type) {
        const icons = {
            success: '<polyline points="20 6 9 17 4 12"></polyline>',
            danger: '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>',
            warning: '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
            info: '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
            primary: '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>'
        };
        return '<svg viewBox="0 0 24 24" aria-hidden="true">' + (icons[type] || icons.primary) + '</svg>';
    }

    closeSvg() {
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
    }

    showSessionNotification(toastData) {
        const message = toastData.dataset.message || '';
        const type = toastData.dataset.type || 'primary';
        toastData.remove();
        this.showNotification(message, type);
    }

    showNotification(message, type = 'primary', positionOrOptions, delay) {
        if (message === undefined || message === null || message === '') return null;

        const cfg = this.config;
        let options = {};
        if (positionOrOptions && typeof positionOrOptions === 'object') {
            options = positionOrOptions;
        } else if (typeof positionOrOptions === 'string') {
            options.position = positionOrOptions;
        }
        if (typeof delay === 'number') {
            options.duration = delay;
        }

        type = this.normalizeType(options.type || type);
        const meta = this.typeMeta(type);
        const duration = typeof options.duration === 'number' ? options.duration : cfg.duration;
        const style = options.style || cfg.style;
        const animation = options.animation || cfg.animation;
        const size = options.size || cfg.size;
        const showProgress = options.showProgress !== undefined ? !!options.showProgress : cfg.showProgress;
        const pauseOnHover = options.pauseOnHover !== undefined ? !!options.pauseOnHover : cfg.pauseOnHover;
        const showIcon = options.showIcon !== undefined ? !!options.showIcon : cfg.showIcon;
        const showTitle = options.showTitle !== undefined ? !!options.showTitle : cfg.showTitle;
        const sound = options.sound !== undefined ? !!options.sound : cfg.sound;
        const animName = String(animation).replace('slide-fade', 'slide-fade');

        const stack = this.ensureStack();
        this.trimStack(cfg.maxVisible - 1);

        const el = document.createElement('div');
        el.className = [
            'admin-notify',
            'admin-notify--' + style,
            'admin-notify--' + type,
            'admin-notify--' + size,
            'admin-notify--anim-' + animName
        ].join(' ');
        el.setAttribute('role', type === 'danger' ? 'alert' : 'status');

        const iconHtml = showIcon ? '<div class="admin-notify__icon">' + this.iconSvg(type) + '</div>' : '';
        const titleHtml = showTitle ? '<div class="admin-notify__title"></div>' : '';
        const progressHtml = showProgress ? '<div class="admin-notify__progress"><span></span></div>' : '';

        el.innerHTML =
            '<div class="admin-notify__glow"></div>' +
            '<div class="admin-notify__card">' +
                iconHtml +
                '<div class="admin-notify__body">' + titleHtml + '<div class="admin-notify__text"></div></div>' +
                '<button type="button" class="admin-notify__close" aria-label="' + ((typeof lang !== 'undefined' && lang === 'ru') ? 'Закрыть' : 'Close') + '">' + this.closeSvg() + '</button>' +
                progressHtml +
            '</div>';

        const titleNode = el.querySelector('.admin-notify__title');
        const textNode = el.querySelector('.admin-notify__text');
        if (titleNode) titleNode.textContent = meta.title;
        if (textNode) textNode.textContent = String(message);

        stack.appendChild(el);
        this.items.push(el);

        requestAnimationFrame(() => {
            el.classList.add('is-in');
        });

        const state = {
            duration: duration,
            remaining: duration,
            startedAt: Date.now(),
            timer: null,
            paused: false
        };
        el._notifyState = state;

        const hide = () => this.hideNotification(el);
        el.querySelector('.admin-notify__close').addEventListener('click', hide);

        if (showProgress) {
            const bar = el.querySelector('.admin-notify__progress > span');
            if (bar) bar.style.animationDuration = duration + 'ms';
        }

        if (pauseOnHover) {
            el.addEventListener('mouseenter', () => this.pauseNotification(el));
            el.addEventListener('mouseleave', () => this.resumeNotification(el));
        }

        state.timer = setTimeout(hide, duration);
        if (sound) this.playSound(type);
        return el;
    }

    pauseNotification(el) {
        const state = el._notifyState;
        if (!state || state.paused) return;
        state.paused = true;
        state.remaining = Math.max(0, state.remaining - (Date.now() - state.startedAt));
        if (state.timer) {
            clearTimeout(state.timer);
            state.timer = null;
        }
        el.classList.add('is-paused');
    }

    resumeNotification(el) {
        const state = el._notifyState;
        if (!state || !state.paused) return;
        state.paused = false;
        state.startedAt = Date.now();
        el.classList.remove('is-paused');
        const bar = el.querySelector('.admin-notify__progress > span');
        if (bar) {
            bar.style.animation = 'none';
            bar.offsetHeight;
            bar.style.animation = 'admin-notify-progress ' + state.remaining + 'ms linear forwards';
        }
        state.timer = setTimeout(() => this.hideNotification(el), state.remaining);
    }

    hideNotification(el) {
        if (!el || el.classList.contains('is-out')) return;
        const state = el._notifyState;
        if (state && state.timer) {
            clearTimeout(state.timer);
            state.timer = null;
        }
        this.items = this.items.filter((item) => item !== el);
        el.classList.remove('is-in');
        el.classList.add('is-out');
        let removed = false;
        const done = () => {
            if (removed) return;
            removed = true;
            if (el.parentNode) el.remove();
        };
        el.addEventListener('transitionend', done, { once: true });
        setTimeout(done, 500);
    }

    trimStack(keep) {
        while (this.items.length > keep) {
            this.hideNotification(this.items[0]);
        }
    }

    playSound(type) {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            if (!this.audioCtx) this.audioCtx = new Ctx();
            const ctx = this.audioCtx;
            if (ctx.state === 'suspended') ctx.resume();
            const now = ctx.currentTime;
            const tones = {
                success: [523.25, 783.99],
                danger: [392, 277.18],
                warning: [440, 554.37],
                info: [523.25, 659.25],
                primary: [493.88, 659.25]
            };
            const pair = tones[type] || tones.primary;
            pair.forEach((freq, index) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, now);
                gain.gain.exponentialRampToValueAtTime(0.045, now + 0.02 + index * 0.07);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.22 + index * 0.1);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now + index * 0.07);
                osc.stop(now + 0.28 + index * 0.1);
            });
        } catch (e) {}
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

function adminActIcon(name) {
    const base = (typeof window !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
    return '<svg class="icon icon-' + name + ' act-ic" width="16" height="16" style="fill: currentColor" aria-hidden="true">'
        + '<use href="' + base + '/templates/default/admin/icons/bs.svg#' + name + '"></use></svg>';
}

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
                    
                    <div class="mt-2">
                        <div class="act-group" role="group">
                            ${!notification.is_read ? `
                            <button type="button" class="act act--publish mark-read-btn" data-id="${notification.id}" aria-label="${lang === 'ru' ? 'Прочитано' : 'Mark read'}" data-tip="${lang === 'ru' ? 'Прочитано' : 'Mark read'}">${adminActIcon('check-lg')}</button>
                            ` : ''}

                            ${isComment && notification.data?.post_id ? `
                            <a href="${ADMIN_URL || ''}/comments"
                            class="act act--chat"
                            target="_blank"
                            aria-label="${lang === 'ru' ? 'К комментариям' : 'To comments'}"
                            data-tip="${lang === 'ru' ? 'К комментариям' : 'To comments'}">${adminActIcon('chat-dots')}</a>
                            ` : ''}

                            <button type="button" class="act act--delete delete-btn" data-id="${notification.id}" aria-label="${lang === 'ru' ? 'Удалить' : 'Delete'}" data-tip="${lang === 'ru' ? 'Удалить' : 'Delete'}">${adminActIcon('trash')}</button>
                        </div>
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

window.notify = function(message, type, options) {
    if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
        return window.notificationSystem.showNotification(message, type, options);
    }
};

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