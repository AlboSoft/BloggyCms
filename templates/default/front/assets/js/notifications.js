class FrontNotificationSystem {
    constructor() {
        this.initContainer();
        this.initSessionNotification();
        this.initStyles();
        this.bindGlobalEvents();
    }
    
    initContainer() {
        this.container = document.getElementById('front-notifications-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'front-notifications-container';
            this.container.className = 'toast-container position-fixed top-0 end-0 p-3';
            this.container.style.cssText = 'z-index: 1055;';
            document.body.appendChild(this.container);
        }
    }
    
    initStyles() {
        const styleId = 'front-notifications-styles';
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                .front-toast {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: white;
                    border-left: 4px solid #34d399;
                    border: none !important;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
                    backdrop-filter: blur(10px);
                    min-width: 320px;
                    max-width: 400px;
                    margin-bottom: 0.75rem;
                    transition: transform 0.2s ease;
                }
                
                .front-toast.success {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    border-left: 4px solid #34d399;
                }
                
                .front-toast.danger {
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    border-left: 4px solid #f87171;
                }
                
                .front-toast.warning {
                    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                    border-left: 4px solid #fbbf24;
                }
                
                .front-toast.info {
                    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
                    border-left: 4px solid #22d3ee;
                }
                
                .front-toast.primary {
                    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                    border-left: 4px solid #60a5fa;
                }
                
                .front-toast .toast-header {
                    display: flex;
                    align-items: center;
                    padding: 1rem 1.25rem 0.5rem;
                    background: transparent !important;
                    border: none;
                    color: white;
                }
                
                .front-toast .toast-header .me-auto {
                    font-weight: 600;
                    font-size: 18px;
                    display: flex;
                    gap: 0.5rem;
                    color: white;
                }
                
                .front-toast .toast-header .me-auto::before {
                    content: '';
                    width: 16px;
                    height: 16px;
                    flex-shrink: 0;
                }
                
                .front-toast.success .toast-header .me-auto::before {
                    content: '✓';
                    color: currentColor;
                }
                
                .front-toast.danger .toast-header .me-auto::before {
                    content: '⚠';
                    color: currentColor;
                }
                
                .front-toast.warning .toast-header .me-auto::before {
                    content: '⚠';
                    color: currentColor;
                }
                
                .front-toast.info .toast-header .me-auto::before {
                    content: '💡';
                    color: currentColor;
                }
                
                .front-toast.primary .toast-header .me-auto::before {
                    content: 'ℹ';
                    color: currentColor;
                }
                
                .front-toast .toast-body {
                    padding: 0 1.25rem 1.25rem;
                    font-size: 0.95rem;
                    line-height: 1.5;
                    background: transparent;
                    color: white;
                }
                
                .front-toast .btn-close {
                    width: 24px;
                    height: 24px;
                    padding: 0;
                    background: transparent;
                    border: none;
                    border-radius: 50%;
                    position: relative;
                    opacity: 0.7;
                    transition: all 0.2s ease;
                    margin-left: auto;
                    filter: invert(1) brightness(100);
                }
                
                .front-toast .btn-close:hover {
                    opacity: 1;
                    background: rgba(255, 255, 255, 0.1);
                }
                
                .front-toast .btn-close::before,
                .front-toast .btn-close::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 12px;
                    height: 2px;
                    background: currentColor;
                    border-radius: 1px;
                }
                
                .front-toast .btn-close::before {
                    transform: translate(-50%, -50%) rotate(45deg);
                }
                
                .front-toast .btn-close::after {
                    transform: translate(-50%, -50%) rotate(-45deg);
                }
                
                .front-toast-progress {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: rgba(255, 255, 255, 0.7);
                    width: 100%;
                    transform-origin: left;
                    border-radius: 0 0 0.25rem 0.25rem;
                }
                
                @keyframes progressBar {
                    from { transform: scaleX(1); }
                    to { transform: scaleX(0); }
                }
                
                .front-toast:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.2) !important;
                }
                
                @media (max-width: 768px) {
                    #front-notifications-container {
                        padding: 0.5rem !important;
                        width: 100% !important;
                        max-width: 100vw !important;
                    }
                    
                    .front-toast {
                        min-width: unset !important;
                        max-width: unset !important;
                        width: calc(100vw - 1rem) !important;
                        margin: 0 auto 0.5rem !important;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    initSessionNotification() {
        const toastData = document.getElementById('notification-data');
        if (toastData && toastData.dataset.message) {
            const message = toastData.dataset.message;
            const type = toastData.dataset.type || 'primary';
            const delay = 5000;
            
            this.show(message, type, delay);
            toastData.remove();
        }
    }
    
    bindGlobalEvents() {
        window.showNotification = (message, type = 'primary', delay = 5000) => {
            this.show(message, type, delay);
        };
    }
    
    show(message, type = 'primary', delay = 5000) {
        const toastId = 'toast-' + Date.now();
        
        let title = lang === 'ru' ? 'Уведомление' : 'Notification';
        switch(type) {
            case 'success': title = lang === 'ru' ? 'Успешно' : 'Success'; break;
            case 'danger': title = lang === 'ru' ? 'Ошибка' : 'Error'; break;
            case 'warning': title = lang === 'ru' ? 'Внимание' : 'Warning'; break;
            case 'info': title = lang === 'ru' ? 'Информация' : 'Info'; break;
            case 'primary': title = lang === 'ru' ? 'Сообщение' : 'Message'; break;
        }
        
        const toastHtml = `
            <div id="${toastId}" class="toast front-toast ${type}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">${title}</strong>
                    <small class="text-white opacity-75">${lang === 'ru' ? 'Только что' : 'Just now'}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="${lang === 'ru' ? 'Закрыть' : 'Close'}"></button>
                </div>
                <div class="toast-body">
                    ${this.escapeHtml(message)}
                </div>
                <div class="front-toast-progress" style="animation: progressBar ${delay}ms linear forwards;"></div>
            </div>
        `;
        
        this.container.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastEl = document.getElementById(toastId);
        
        const toast = new bootstrap.Toast(toastEl, {
            animation: true,
            autohide: delay > 0,
            delay: delay
        });
        
        this.startProgressBar(toastEl, delay);
        
        toast.show();
        
        toastEl.addEventListener('mouseenter', () => {
            this.pauseProgressBar(toastEl);
        });
        
        toastEl.addEventListener('mouseleave', () => {
            this.resumeProgressBar(toastEl, toast, delay);
        });
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            setTimeout(() => {
                if (toastEl.parentNode) {
                    toastEl.remove();
                }
            }, 300);
        });
        
        return toast;
    }
    
    startProgressBar(toastEl, duration) {
        const progressBar = toastEl.querySelector('.front-toast-progress');
        if (!progressBar) return;
        
        progressBar.style.animation = 'none';
        void progressBar.offsetWidth;
        
        progressBar.style.animation = `progressBar ${duration}ms linear forwards`;
        progressBar.style.animationPlayState = 'running';
        
        toastEl._progressStartTime = Date.now();
        toastEl._progressDuration = duration;
        toastEl._progressRemaining = duration;
    }
    
    pauseProgressBar(toastEl) {
        const progressBar = toastEl.querySelector('.front-toast-progress');
        if (!progressBar || !toastEl._progressStartTime) return;
        
        const elapsed = Date.now() - toastEl._progressStartTime;
        toastEl._progressRemaining = toastEl._progressDuration - elapsed;
        
        progressBar.style.animationPlayState = 'paused';
    }
    
    resumeProgressBar(toastEl, toastInstance, delay) {
        const progressBar = toastEl.querySelector('.front-toast-progress');
        if (!progressBar || !toastEl._progressRemaining) return;
        
        progressBar.style.animation = 'none';
        void progressBar.offsetWidth;
        
        progressBar.style.animation = `progressBar ${toastEl._progressRemaining}ms linear forwards`;
        progressBar.style.animationPlayState = 'running';
        
        toastEl._progressStartTime = Date.now();
        toastEl._progressDuration = toastEl._progressRemaining;
    }
    
    success(message, delay = 5000) {
        return this.show(message, 'success', delay);
    }
    
    danger(message, delay = 5000) {
        return this.show(message, 'danger', delay);
    }
    
    warning(message, delay = 5000) {
        return this.show(message, 'warning', delay);
    }
    
    info(message, delay = 5000) {
        return this.show(message, 'info', delay);
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.frontNotifications = new FrontNotificationSystem();
});