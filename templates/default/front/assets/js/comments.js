/**
 * BloggyCMS Comments System
 * Система комментариев с AJAX
 */

class CommentsSystem {
    constructor(config = {}) {
        this.config = {
            baseUrl: config.base_url || '/',
            adminUrl: config.admin_url || '/admin',
            postId: config.post_id || 0,
            isAdmin: config.is_admin || false,
            isLoggedIn: config.is_logged_in || false,
            currentUserId: config.current_user_id || 0,
            maxDepth: config.max_depth || 4,
            showEmoji: config.show_emodji || false,
            emojiList: config.emodji_list || [],
            enableAjax: true
        };
        
        this.isEmojiPickerOpen = false;
        this.init();
    }
    
    init() {
        this.setupNotifications();
        this.setupCommentForm();
        this.setupDeleteHandlers();
        this.setupReplyHandlers();
        this.setupDeepRepliesToggle();
        this.setupHighlighting();
        this.bindGlobalFunctions();
        this.setupApproveHandlers();
        this.setupEmojiPicker();
    }

    setupApproveHandlers() {
        document.addEventListener('click', (e) => {
            const approveBtn = e.target.closest('.btn-admin-approve, [data-action="approve-comment"]');
            if (approveBtn) {
                e.preventDefault();
                this.handleApproveClick(approveBtn);
            }
        });
    }

    async handleApproveClick(approveBtn) {
        const commentId = this.extractApproveCommentId(approveBtn);
        if (!commentId) return;
        
        const commentElement = document.getElementById(`comment-${commentId}`);
        const originalHTML = approveBtn.innerHTML;
        
        approveBtn.innerHTML = this.getIconHtml('hourglass-bottom', 'currentColor', 'mr-1 pb-1') + (lang === 'ru' ? 'Одобрение...' : 'Approving...');
        approveBtn.disabled = true;
        
        try {
            const response = await fetch(`${this.config.adminUrl}/comments/approve/${commentId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.handleApproveSuccess(commentId, commentElement, approveBtn, result);
            } else {
                this.handleApproveError(result.message, approveBtn, originalHTML);
            }
            
        } catch (error) {
            this.handleApproveError(lang === 'ru' ? 'Ошибка сети' : 'Network error', approveBtn, originalHTML);
        }
    }

    setupEmojiPicker() {
        
        const emojiTrigger = document.getElementById('emojiTrigger');
        if (!emojiTrigger) {
            return;
        }
        
        const emojiPicker = document.getElementById('emojiPicker');
        const textarea = document.getElementById('content');
        
        if (!emojiPicker || !textarea) {
            return;
        }
        
        const newTrigger = emojiTrigger.cloneNode(true);
        emojiTrigger.parentNode.replaceChild(newTrigger, emojiTrigger);
        const newPicker = emojiPicker.cloneNode(true);
        emojiPicker.parentNode.replaceChild(newPicker, emojiPicker);
        const updatedTrigger = document.getElementById('emojiTrigger');
        const updatedPicker = document.getElementById('emojiPicker');
        const updatedTextarea = document.getElementById('content');
        updatedPicker.style.display = 'none';
        updatedPicker.classList.add('d-none');
        this.isEmojiPickerOpen = false;
        updatedTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            if (!this.isEmojiPickerOpen) {
                this.openEmojiPicker(updatedPicker, updatedTrigger);
            } else {
                this.closeEmojiPicker(updatedPicker);
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!this.isEmojiPickerOpen) return;
            
            const emojiBtn = e.target.closest('.emoji-item');
            if (emojiBtn) {
                e.preventDefault();
                e.stopPropagation();
                this.insertEmoji(updatedTextarea, emojiBtn.getAttribute('data-emoji'));
                this.closeEmojiPicker(updatedPicker);
            }
        });
        
        document.addEventListener('click', (e) => {
            if (this.isEmojiPickerOpen &&
                !updatedPicker.contains(e.target) &&
                !updatedTrigger.contains(e.target)) {
                this.closeEmojiPicker(updatedPicker);
            }
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isEmojiPickerOpen) {
                this.closeEmojiPicker(updatedPicker);
            }
        });
    }
    
    openEmojiPicker(picker, trigger) {
        
        const formContainer = document.querySelector('.comments-form');
        if (!formContainer) {
            return;
        }
        
        picker.classList.remove('d-none');
        picker.style.display = 'block';
        picker.style.visibility = 'hidden';
        picker.style.position = 'absolute';
        
        requestAnimationFrame(() => {
            const pickerRect = picker.getBoundingClientRect();
            const triggerRect = trigger.getBoundingClientRect();
            const formRect = formContainer.getBoundingClientRect();
            const triggerTopRelative = triggerRect.top - formRect.top;
            const triggerLeftRelative = triggerRect.left - formRect.left;
            let topPosition = triggerTopRelative + triggerRect.height + 8;
            let leftPosition = triggerLeftRelative + triggerRect.width - pickerRect.width;
            
            if (leftPosition < 0) {
                leftPosition = 0;
            }
            
            if (topPosition + pickerRect.height > formRect.height) {
                topPosition = triggerTopRelative - pickerRect.height - 8;
                
                if (topPosition < 0) {
                    topPosition = 0;
                    picker.style.maxHeight = (formRect.height - 20) + 'px';
                    picker.style.overflowY = 'auto';
                }
            }
            
            picker.style.position = 'absolute';
            picker.style.top = topPosition + 'px';
            picker.style.left = leftPosition + 'px';
            picker.style.zIndex = '10000';
            picker.style.visibility = 'visible';
            picker.style.opacity = '0';
            picker.style.transform = 'translateY(-10px)';
            
            requestAnimationFrame(() => {
                picker.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                picker.style.opacity = '1';
                picker.style.transform = 'translateY(0)';
            });
            
            this.isEmojiPickerOpen = true;
        });
    }
    
    closeEmojiPicker(picker) {
        picker.style.opacity = '0';
        picker.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            picker.classList.add('d-none');
            picker.style.display = 'none';
            picker.style.cssText = '';
            picker.className = 'emoji-picker-container d-none';
            
            this.isEmojiPickerOpen = false;
        }, 200);
    }

    testEmojiPicker() {
        const emojiTrigger = document.getElementById('emojiTrigger');
        const emojiPicker = document.getElementById('emojiPicker');
        const textarea = document.getElementById('content');
        
        if (emojiPicker) {
            const originalDisplay = emojiPicker.style.display;
            emojiPicker.style.display = 'block';
            emojiPicker.style.visibility = 'hidden';
            
            const rect = emojiPicker.getBoundingClientRect();
            emojiPicker.style.display = originalDisplay;
            emojiPicker.style.visibility = '';
        }
    }
    
    insertEmoji(textarea, emoji) {
        if (!textarea || !emoji) return;
        
        const originalValue = textarea.value;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        textarea.value = originalValue.substring(0, start) + emoji + originalValue.substring(end);
        const newPosition = start + emoji.length;
        textarea.selectionStart = textarea.selectionEnd = newPosition;
        textarea.focus();
        
        setTimeout(() => {
            const inputEvent = new Event('input', { bubbles: true });
            textarea.dispatchEvent(inputEvent);
        }, 10);
    }

    handleApproveSuccess(commentId, commentElement, approveBtn, result) {
        this.showNotification(result.message, 'success');
        
        approveBtn.remove();
        
        if (commentElement) {
            
            commentElement.classList.remove('comment-pending');
            
            const moderationBadge = commentElement.querySelector('.badge-moderation');
            if (moderationBadge) {
                moderationBadge.remove();
            }
            
            const replyBtn = commentElement.querySelector('.btn-reply');
            if (replyBtn && replyBtn.style.display === 'none') {
                replyBtn.style.display = 'inline-flex';
            }
            
            const commentContent = commentElement.querySelector('.comment-content');
            if (commentContent && commentContent.style.fontStyle === 'italic') {
                commentContent.style.fontStyle = 'normal';
                commentContent.style.color = '';
            }
            
            if (result.comment) {
                this.updateCommentElement(commentElement, result.comment);
            }
            
            commentElement.classList.add('comment-approved');
            setTimeout(() => {
                commentElement.classList.remove('comment-approved');
            }, 2000);
        }
        
        if (!window.location.href.includes('/admin/')) {
            this.updateCommentsCounter(1);
        }
    }

    updateCommentElement(element, commentData) {
        const dateElement = element.querySelector('.comment-date');
        if (dateElement && commentData.created_at) {
            const icon = dateElement.querySelector('.icon-clock')?.outerHTML || '';
            dateElement.innerHTML = icon + ' ' + commentData.created_at;
        }
        
        if (commentData.status) {
            element.dataset.status = commentData.status;
        }
    }

    handleApproveError(message, approveBtn, originalHTML) {
        this.showNotification(message, 'error');
        approveBtn.innerHTML = originalHTML;
        approveBtn.disabled = false;
    }

    extractApproveCommentId(element) {
        return element.getAttribute('href')?.match(/comments\/approve\/(\d+)/)?.[1] ||
               element.getAttribute('data-comment-id');
    }
    
    setupNotifications() {
        if (!document.getElementById('notifications-container')) {
            const container = document.createElement('div');
            container.id = 'notifications-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 350px;
            `;
            document.body.appendChild(container);
        }
    }
    
    showNotification(message, type = 'info') {
        const container = document.getElementById('notifications-container');
        if (!container) {
            return;
        }
        
        const notificationId = 'notification-' + Date.now();
        const alertClass = this.getAlertClass(type);
        const icon = this.getNotificationIcon(type);
        
        const notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = `alert ${alertClass} alert-dismissible fade show`;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                ${icon}
                <div>${message}</div>
            </div>
        `;
        
        container.appendChild(notification);
        
        setTimeout(() => {
            const alert = document.getElementById(notificationId);
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    }
    
    getAlertClass(type) {
        const classes = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        return classes[type] || 'alert-info';
    }
    
    getNotificationIcon(type) {
        const icons = {
            'success': '<svg class="icon icon-check-square mr-1" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#check-square"></use></svg>',
            'error': '<svg class="icon icon-x-square-fill mr-1" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#x-square-fill"></use></svg>',
            'warning': '<svg class="icon icon-info-square mr-1" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#info-square"></use></svg>',
            'info': '<svg class="icon icon-info-lg mr-1" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#info-lg"></use></svg>'
        };
        return icons[type] || '<svg class="icon icon-info-circle mr-1" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#info-circle"></use></svg>';
    }
    
    setupCommentForm() {
        const form = document.getElementById('comment-form-element');
        if (!form) return;
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitComment(form);
        });
    }
    
    async submitComment(form) {
        try {
            if (!this.validateCommentForm(form)) {
                return;
            }
            
            this.showFormLoading(true);
            
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            const response = await fetch(`${this.config.baseUrl}/comment/add`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.handleCommentSuccess(result, form);
            } else {
                this.handleCommentError(result.message);
            }
            
        } catch (error) {
            this.handleCommentError(lang === 'ru' ? 'Произошла ошибка при отправке комментария' : 'An error occurred while sending the comment');
        } finally {
            this.showFormLoading(false);
        }
    }
    
    validateCommentForm(form) {
        const content = form.querySelector('#content')?.value.trim() || '';
        
        if (content.length < 10) {
            this.showNotification(lang === 'ru' ? 'Текст комментария должен содержать не менее 10 символов' : 'Comment text must be at least 10 characters', 'error');
            form.querySelector('#content')?.focus();
            return false;
        }
        
        if (content.length > 5000) {
            this.showNotification(lang === 'ru' ? 'Текст комментария слишком длинный (максимум 5000 символов)' : 'Comment text is too long (maximum 5000 characters)', 'error');
            form.querySelector('#content')?.focus();
            return false;
        }
        
        if (!this.config.isLoggedIn) {
            const authorName = form.querySelector('#author_name')?.value.trim() || '';
            const authorEmail = form.querySelector('#author_email')?.value.trim() || '';
            
            if (!authorName) {
                this.showNotification(lang === 'ru' ? 'Пожалуйста, введите ваше имя' : 'Please enter your name', 'error');
                form.querySelector('#author_name')?.focus();
                return false;
            }
            
            if (!authorEmail || !this.isValidEmail(authorEmail)) {
                this.showNotification(lang === 'ru' ? 'Пожалуйста, введите корректный email' : 'Please enter a valid email', 'error');
                form.querySelector('#author_email')?.focus();
                return false;
            }
        }
        
        return true;
    }
    
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    showFormLoading(show) {
        const submitBtn = document.getElementById('comment-submit-btn');
        const loadingIndicator = document.getElementById('comment-loading');
        const submitText = document.getElementById('comment-submit-text');
        
        if (submitBtn) {
            submitBtn.disabled = show;
        }
        
        if (loadingIndicator) {
            loadingIndicator.style.display = show ? 'inline-block' : 'none';
        }
        
        if (submitText) {
            submitText.textContent = show ? (lang === 'ru' ? 'Отправка...' : 'Sending...') : 
                (document.getElementById('comment-parent-id')?.value > 0 ? (lang === 'ru' ? 'Отправить ответ' : 'Send reply') : (lang === 'ru' ? 'Отправить комментарий' : 'Send comment'));
        }
    }
    
    handleCommentSuccess(result, form) {
        this.showNotification(result.message, 'success');
        
        if (result.comment) {
            this.addNewCommentToDOM(result.comment);
        }
        
        form.reset();
        this.cancelReply();
        
        if (!result.needs_moderation) {
            this.updateCommentsCounter(1);
        }
    }
    
    handleCommentError(message) {
        this.showNotification(message, 'error');
    }
    
    setupDeleteHandlers() {
        document.addEventListener('click', (e) => {
            const deleteBtn = e.target.closest('.delete-comment-btn, [data-action="delete-comment"]');
            if (deleteBtn) {
                e.preventDefault();
                this.handleDeleteClick(deleteBtn);
            }
        });
    }
    
    async handleDeleteClick(deleteBtn) {
        const commentId = this.extractCommentId(deleteBtn);
        if (!commentId) return;
        
        const commentAuthor = this.getCommentAuthor(commentId);
        const confirmed = await this.showDeleteConfirmation(commentAuthor);
        
        if (!confirmed) return;
        
        await this.deleteComment(commentId, deleteBtn);
    }
    
    extractCommentId(element) {
        return element.getAttribute('data-comment-id') || 
               element.href?.match(/comment\/delete\/(\d+)/)?.[1];
    }
    
    getCommentAuthor(commentId) {
        const commentElement = document.getElementById(`comment-${commentId}`);
        return commentElement?.querySelector('.comment-author strong')?.textContent?.trim() || '';
    }
    
    async showDeleteConfirmation(commentAuthor = '') {
        return new Promise((resolve) => {
            if (window.confirm(lang === 'ru' 
                ? `Вы уверены, что хотите удалить комментарий${commentAuthor ? ' от ' + commentAuthor : ''}?`
                : `Are you sure you want to delete the comment${commentAuthor ? ' by ' + commentAuthor : ''}?`)) {
                resolve(true);
            } else {
                resolve(false);
            }
        });
    }
    
    async deleteComment(commentId, deleteBtn) {
        const originalHTML = deleteBtn.innerHTML;
        const originalDisabled = deleteBtn.disabled;
        
        deleteBtn.innerHTML = '<svg class="icon icon-hourglass-bottom" width="18" height="18" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#hourglass-bottom"></use></svg> ' + (lang === 'ru' ? 'Удаление...' : 'Deleting...');
        deleteBtn.disabled = true;
        
        try {
            const response = await fetch(`${this.config.baseUrl}/comment/delete/${commentId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.handleDeleteSuccess(commentId, result);
            } else {
                this.handleDeleteError(result.message, deleteBtn, originalHTML, originalDisabled);
            }
            
        } catch (error) {
            this.handleDeleteError(lang === 'ru' ? 'Ошибка сети' : 'Network error', deleteBtn, originalHTML, originalDisabled);
        }
    }
    
    handleDeleteSuccess(commentId, result) {
        this.removeCommentFromDOM(commentId, result.has_replies);
        this.showNotification(result.message, 'success');
        this.updateCommentsCounter(-1);
    }
    
    handleDeleteError(message, deleteBtn, originalHTML, originalDisabled) {
        this.showNotification(message, 'error');
        deleteBtn.innerHTML = originalHTML;
        deleteBtn.disabled = originalDisabled;
    }
    
    setupReplyHandlers() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.reply-btn')) {
                e.preventDefault();
                this.handleReplyClick(e.target.closest('.reply-btn'));
            }
        });
        
        document.addEventListener('click', (e) => {
            if (e.target.closest('.cancel-reply-btn')) {
                e.preventDefault();
                this.cancelReply();
            }
        });
    }
    
    handleReplyClick(replyBtn) {
        const commentId = replyBtn.getAttribute('data-comment-id');
        const author = replyBtn.getAttribute('data-comment-author');
        
        this.setReplyTo(commentId, author);
        this.scrollToCommentForm();
        this.focusCommentField(author);
    }
    
    setReplyTo(commentId, author = '') {
        const parentIdInput = document.getElementById('comment-parent-id');
        if (parentIdInput) {
            parentIdInput.value = commentId;
        }
        
        const formTitle = document.getElementById('comment-form-title');
        if (formTitle) {
            formTitle.textContent = author 
                ? (lang === 'ru' ? `Ответить на комментарий ${author}` : `Reply to ${author}'s comment`)
                : (lang === 'ru' ? 'Ответить на комментарий' : 'Reply to comment');
        }
        
        const submitText = document.getElementById('comment-submit-text');
        if (submitText) {
            submitText.textContent = lang === 'ru' ? 'Отправить ответ' : 'Send reply';
        }
    }
    
    scrollToCommentForm() {
        const commentForm = document.getElementById('comment-form');
        if (commentForm) {
            commentForm.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }
    }
    
    focusCommentField(author = '') {
        const contentInput = document.getElementById('content');
        if (contentInput) {
            contentInput.focus();
            
            if (author) {
                this.addMention(author, contentInput);
            }
        }
    }
    
    addMention(author, contentInput) {
        const currentValue = contentInput.value.trim();
        const mention = `@${author}, `;
        
        if (!currentValue.startsWith(mention)) {
            contentInput.value = mention + currentValue;
            contentInput.selectionStart = contentInput.selectionEnd = mention.length;
        }
    }
    
    cancelReply() {
        const parentIdInput = document.getElementById('comment-parent-id');
        if (parentIdInput) {
            parentIdInput.value = 0;
        }
        
        const formTitle = document.getElementById('comment-form-title');
        if (formTitle) {
            formTitle.textContent = lang === 'ru' ? 'Оставить комментарий' : 'Leave a comment';
        }
        
        const submitText = document.getElementById('comment-submit-text');
        if (submitText) {
            submitText.textContent = lang === 'ru' ? 'Отправить комментарий' : 'Send comment';
        }
        
        const contentInput = document.getElementById('content');
        if (contentInput) {
            const currentValue = contentInput.value.trim();
            if (currentValue.startsWith('@')) {
                contentInput.value = currentValue.replace(/^@[^,]+,\s*/, '');
            }
        }
    }
    
    setupDeepRepliesToggle() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-show-deep-replies')) {
                e.preventDefault();
                const button = e.target.closest('.btn-show-deep-replies');
                const targetId = button.getAttribute('data-target');
                const container = document.getElementById(targetId);
                
                if (container) {
                    this.toggleDeepReplies(button, container);
                }
            }
        });
    }
    
    toggleDeepReplies(button, container) {
        if (container.style.display === 'none' || !container.style.display) {
            container.style.display = 'block';
            button.innerHTML = this.getIconHtml('chevron-up') + '<span>' + (lang === 'ru' ? 'Скрыть ответы' : 'Hide replies') + '</span>';
        } else {
            container.style.display = 'none';
            button.innerHTML = this.getIconHtml('chevron-down') + '<span>' + (lang === 'ru' ? 'Показать ответы' : 'Show replies') + '</span>';
        }
    }
    
    setupHighlighting() {
        const hash = window.location.hash;
        if (hash && hash.startsWith('#comment-')) {
            this.highlightComment(hash);
        }
    }
    
    highlightComment(commentHash) {
        setTimeout(() => {
            const commentElement = document.querySelector(commentHash);
            if (commentElement) {
                commentElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                commentElement.classList.add('comment-highlighted');
                
                setTimeout(() => {
                    commentElement.classList.remove('comment-highlighted');
                }, 3000);
            }
        }, 500);
    }
    
    addNewCommentToDOM(commentData) {
        const commentHtml = this.generateCommentHTML(commentData);
        const parentId = commentData.parent_id || 0;
        
        if (parentId > 0) {
            this.appendToParent(parentId, commentHtml, commentData.id);
        } else {
            this.appendToRoot(commentHtml, commentData.id);
        }
        
        if (commentData.is_pending) {
            const commentElement = document.getElementById(`comment-${commentData.id}`);
            if (commentElement) {
                commentElement.classList.add('comment-pending');
            }
        }
    }
    
    generateCommentHTML(commentData) {
        const isPending = commentData.is_pending || false;
        const isOwnComment = commentData.is_own_comment || false;
        const isAdmin = commentData.is_admin || false;
        const userGroups = commentData.user_groups || [];
        const canEdit = commentData.can_edit || false;
        const canDelete = commentData.can_delete || false;
        const canReply = commentData.can_reply || false;
        const parentId = commentData.parent_id || 0;
        const showGroups = this.config.show_groups !== false;
        const showAdminBadge = this.config.show_admin_badge || false;
        const adminBadgeTitle = this.config.admin_badge_title || (lang === 'ru' ? 'Администратор' : 'Administrator');
        const adminBadgeIcon = this.config.admin_badge_icon || 'bs:rocket';
        const adminBadgeBgColor = this.config.admin_badge_bg_color || '#007bff';
        const adminBadgeTextColor = this.config.admin_badge_text_color || '#ffffff';
        const moderationText = this.config.moderation_text || (lang === 'ru' ? 'На модерации' : 'On moderation');
        const youText = this.config.you_text || (lang === 'ru' ? 'Вы' : 'You');
        const replyText = this.config.reply_text || (lang === 'ru' ? 'Ответить' : 'Reply');
        const editText = this.config.edit_text || (lang === 'ru' ? 'Редактировать' : 'Edit');
        const deleteText = this.config.delete_text || (lang === 'ru' ? 'Удалить' : 'Delete');
        
        let groupsHTML = '';
        if (showGroups && userGroups.length > 0) {
            groupsHTML = '<div class="user-groups">';
            userGroups.forEach(group => {
                groupsHTML += `<span class="badge-group" title="Группа: ${group.name}" data-group-id="${group.id}">${group.name}</span>`;
            });
            groupsHTML += '</div>';
        }
        
        let adminBadgeStyle = '';
        let adminBadgeIconColor = 'currentColor';
        if (showAdminBadge && isAdmin && adminBadgeBgColor && adminBadgeTextColor) {
            adminBadgeStyle = `style="background-color: ${adminBadgeBgColor}; color: ${adminBadgeTextColor};"`;
            adminBadgeIconColor = adminBadgeTextColor;
        }
        
        let adminBadgeHTML = '';
        if (showAdminBadge && isAdmin) {
            const iconParts = adminBadgeIcon.split(':');
            const iconSet = iconParts[0] || 'bs';
            const iconName = iconParts[1] || 'rocket';
            
            adminBadgeHTML = `
                <span class="badge-admin" title="${adminBadgeTitle}" ${adminBadgeStyle}>
                    ${this.getIconHtml(iconName, adminBadgeIconColor, 'me-1')}
                    <span class="badge-text">${adminBadgeTitle}</span>
                </span>
            `;
        }
        
        let moderationBadgeHTML = '';
        if (isPending && isOwnComment) {
            moderationBadgeHTML = `
                <span class="badge-moderation" title="${moderationText}">
                    ${this.getIconHtml('clock', 'currentColor', 'me-1')}
                    <span class="badge-text">${moderationText}</span>
                </span>
            `;
        }
        
        let ownBadgeHTML = '';
        if (isOwnComment) {
            ownBadgeHTML = `
                <span class="badge-own" title="${lang === 'ru' ? 'Ваш комментарий' : 'Your comment'}">
                    ${this.getIconHtml('person-check', 'currentColor', 'me-1')}
                    <span class="badge-text">${youText}</span>
                </span>
            `;
        }
        
        let replyBadgeHTML = '';
        if (parentId > 0) {
            replyBadgeHTML = `
                <span class="badge-reply" title="${lang === 'ru' ? 'Ответ' : 'Reply'}">
                    ${this.getIconHtml('reply', 'currentColor', 'me-1')}
                </span>
            `;
        }
        
        let actionsHTML = '';
        
        if (canReply && !isPending) {
            actionsHTML += `
                <button type="button" 
                        class="btn-action btn-reply reply-btn"
                        data-comment-id="${commentData.id}"
                        data-comment-author="${commentData.author_name}">
                    ${this.getIconHtml('reply', 'currentColor', 'me-1')}
                    <span>${replyText}</span>
                </button>
            `;
        }
        
        if (canEdit) {
            const editUrl = this.config.is_admin ? 
                `${this.config.admin_url}/comments/edit/${commentData.id}` :
                `${this.config.base_url}/comment/edit/${commentData.id}`;
            
            actionsHTML += `
                <a href="${editUrl}" class="btn-action btn-edit">
                    ${this.getIconHtml('pencil', 'currentColor', 'me-1')}
                    <span>${editText}</span>
                </a>
            `;
        }
        
        if (canDelete) {
            actionsHTML += `
                <a href="${this.config.base_url}/comment/delete/${commentData.id}" 
                class="btn-action btn-delete delete-comment-btn"
                data-comment-id="${commentData.id}">
                    ${this.getIconHtml('trash', 'currentColor', 'me-1')}
                    <span>${deleteText}</span>
                </a>
            `;
        }
        
        if (this.config.is_admin) {
            actionsHTML += `
                <a href="${this.config.admin_url}/comments/edit/${commentData.id}" 
                class="btn-action btn-admin"
                title="${lang === 'ru' ? 'Редактировать (админ)' : 'Edit (admin)'}">
                    ${this.getIconHtml('gear', 'currentColor', 'me-1')}
                    <span>${lang === 'ru' ? 'Админ' : 'Admin'}</span>
                </a>
            `;
        }
        
        let displayDate = commentData.created_at;
        if (commentData.created_at === 'Только что' || commentData.created_at.includes('секунд') || commentData.created_at.includes('минут')) {
            displayDate = commentData.created_at;
        } else {
            displayDate = commentData.created_at;
        }
        
        return `
            <div class="comment-item ${parentId > 0 ? 'comment-reply' : ''} 
                ${isPending ? 'comment-pending' : ''} 
                level-0"
                id="comment-${commentData.id}"
                data-comment-id="${commentData.id}"
                data-parent-id="${parentId}"
                data-level="0"
                data-is-admin="${isAdmin ? '1' : '0'}">
                
                <div class="comment-container">
                    <div class="comment-header">
                        <div class="comment-avatar">
                            <img src="${commentData.author_avatar}" 
                                alt="${commentData.author_name}"
                                class="rounded-circle"
                                onerror="this.onerror=null; this.src='${this.config.base_url}/uploads/avatars/default.jpg'">
                            ${parentId > 0 ? '<div class="reply-line"></div>' : ''}
                        </div>
                        
                        <div class="comment-info">
                            <div class="comment-author">
                                <strong>${commentData.author_name}</strong>
                                
                                ${replyBadgeHTML}
                                ${moderationBadgeHTML}
                                ${ownBadgeHTML}
                                ${adminBadgeHTML}
                                ${groupsHTML}
                            </div>
                            
                            <div class="comment-meta">
                                <span class="comment-date">
                                    ${this.getIconHtml('clock', 'currentColor', 'mr-1 pb-1')}
                                    ${displayDate}
                                </span>
                                
                                ${commentData.was_edited ? `
                                    <span class="comment-updated" title="${lang === 'ru' ? 'Отредактирован' : 'Edited'}${commentData.updated_at ? ': ' + commentData.updated_at : ''}">
                                        ${this.getIconHtml('pencil', 'currentColor', 'mr-1 pb-1')}
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                        
                        ${this.config.is_admin && commentData.status === 'pending' ? `
                            <div class="comment-admin-actions">
                                <a href="${this.config.admin_url}/comments/approve/${commentData.id}" 
                                class="btn-admin-approve" 
                                title="${lang === 'ru' ? 'Одобрить комментарий' : 'Approve comment'}"
                                data-comment-id="${commentData.id}">
                                    ${this.getIconHtml('check-lg', '#ffffff', '')}
                                </a>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="comment-content">
                        ${(commentData.content || '').replace(/\n/g, '<br>')}
                    </div>
                    
                    ${actionsHTML ? `
                    <div class="comment-actions">
                        ${actionsHTML}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    getIconHtml(iconName, color = 'currentColor', classes = '') {
        return `<svg class="icon icon-${iconName} ${classes}" width="18" height="18" style="fill: ${color}"><use href="/templates/default/admin/icons/bs.svg#${iconName}"></use></svg>`;
    }
    
    appendToParent(parentId, commentHtml, newCommentId) {
        const parentElement = document.getElementById(`comment-${parentId}`);
        if (!parentElement) return;
        
        let repliesContainer = parentElement.querySelector('.comment-replies');
        if (!repliesContainer) {
            repliesContainer = document.createElement('div');
            repliesContainer.className = 'comment-replies';
            parentElement.querySelector('.comment-container').appendChild(repliesContainer);
        }
        
        repliesContainer.insertAdjacentHTML('afterbegin', commentHtml);
        
        setTimeout(() => {
            this.scrollToComment(newCommentId);
        }, 100);
    }
    
    appendToRoot(commentHtml, newCommentId) {
        const commentsSection = document.querySelector('.comments-section');
        if (commentsSection) {
            const commentsList = commentsSection.querySelector('.comments-list');
            if (commentsList) {
                commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                
                const commentsEmpty = commentsList.querySelector('.comments-empty');
                if (commentsEmpty) {
                    commentsEmpty.remove();
                }
            }
        } else {
            const commentsEmpty = document.querySelector('.comments-empty');
            if (commentsEmpty) {
                const newSection = document.createElement('div');
                newSection.className = 'comments-section';
                newSection.innerHTML = `
                    <div class="comments-list">
                        ${commentHtml}
                    </div>
                `;
                commentsEmpty.replaceWith(newSection);
            }
        }
        
        setTimeout(() => {
            this.scrollToComment(newCommentId);
        }, 100);
    }
    
    scrollToComment(commentId) {
        const commentElement = document.getElementById(`comment-${commentId}`);
        if (commentElement) {
            commentElement.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            commentElement.classList.add('new-comment');
            
            setTimeout(() => {
                commentElement.classList.remove('new-comment');
            }, 2000);
        }
    }
    
    removeCommentFromDOM(commentId, hasReplies = false) {
        const element = document.getElementById(`comment-${commentId}`);
        if (!element) return;
        
        if (hasReplies) {
            this.markAsDeleted(element);
        } else {
            this.animateAndRemove(element);
        }
    }
    
    markAsDeleted(element) {
        element.classList.add('comment-deleted-container');
        element.innerHTML = `
            <div class="comment-deleted">
                <div class="deleted-message">
                    <svg class="icon icon-trash mr-1 pb-1" width="18" height="18" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#trash"></use></svg>
                    ${lang === 'ru' ? 'Комментарий удален' : 'Comment deleted'}
                </div>
                <div class="deleted-replies">
                    ${lang === 'ru' ? 'Ответы на этот комментарий сохранены' : 'Replies to this comment are preserved'}
                </div>
            </div>
        `;
    }
    
    animateAndRemove(element) {
        element.style.transition = 'all 0.3s ease';
        element.style.opacity = '0';
        element.style.transform = 'translateX(-20px)';
        element.style.maxHeight = '0';
        element.style.marginBottom = '0';
        element.style.padding = '0';
        element.style.overflow = 'hidden';
        
        setTimeout(() => {
            element.remove();
            this.removeEmptyRepliesContainer(element);
            this.checkEmptyCommentsList();
        }, 300);
    }
    
    removeEmptyRepliesContainer(removedElement) {
        const parentContainer = removedElement.closest('.comment-replies');
        if (parentContainer && parentContainer.children.length === 0) {
            parentContainer.remove();
        }
    }
    
    checkEmptyCommentsList() {
        const commentsList = document.querySelector('.comments-list');
        if (commentsList && commentsList.children.length === 0) {
            commentsList.innerHTML = `
                <div class="comments-empty text-center py-5">
                    ${this.getIconHtml('chat-text', '#6c757d', 'mb-3')}
                    <h5 class="text-muted mt-3">${lang === 'ru' ? 'Комментариев пока нет' : 'No comments yet'}</h5>
                    <p class="text-muted">${lang === 'ru' ? 'Будьте первым, кто оставит комментарий!' : 'Be the first to leave a comment!'}</p>
                </div>
            `;
        }
    }
    
    updateCommentsCounter(delta) {
        const commentsCountElement = document.querySelector('.comments-count');
        if (commentsCountElement) {
            const currentText = commentsCountElement.textContent;
            const currentCount = parseInt(currentText.match(/\d+/)?.[0] || 0);
            const newCount = Math.max(0, currentCount + delta);
            commentsCountElement.textContent = ` (${newCount})`;
        }
        
        const postCommentsCount = document.querySelector('.post-comments-count');
        if (postCommentsCount) {
            const currentCount = parseInt(postCommentsCount.textContent) || 0;
            const newCount = Math.max(0, currentCount + delta);
            postCommentsCount.textContent = newCount;
        }
        
        if (this.config.postId) {
            const listCommentsCount = document.querySelector(`.post-list-comments[data-post-id="${this.config.postId}"]`);
            if (listCommentsCount) {
                const currentCount = parseInt(listCommentsCount.textContent) || 0;
                const newCount = Math.max(0, currentCount + delta);
                listCommentsCount.textContent = newCount;
            }
        }
    }
    
    bindGlobalFunctions() {
        window.showNotification = (message, type) => this.showNotification(message, type);
        window.updateCommentsCounter = (delta) => this.updateCommentsCounter(delta);
        window.cancelReply = () => this.cancelReply();
        window.addCommentToDOM = (commentData) => this.addNewCommentToDOM(commentData);
    }

    hasCommentForm() {
        return !!document.getElementById('comment-form-element');
    }
    
    hasComments() {
        return !!document.querySelector('.comment-item');
    }
    
    getCommentsCount() {
        return document.querySelectorAll('.comment-item').length;
    }

    resetCommentForm() {
        const form = document.getElementById('comment-form-element');
        if (form) {
            form.reset();
            this.cancelReply();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.bloggyCommentsConfig) {
        window.commentsSystem = new CommentsSystem(window.bloggyCommentsConfig);
    } else {
        window.commentsSystem = new CommentsSystem({});
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = CommentsSystem;
}