document.addEventListener('DOMContentLoaded', function() {

    const changePasswordCheckbox = document.getElementById('change_password');
    const passwordFields = document.querySelector('.password-fields');
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmInput = document.querySelector('input[name="password_confirm"]');
    const form = document.querySelector('form');
    
    function togglePasswordValidation(enable) {
        if (passwordInput) {
            passwordInput.disabled = !enable;
            if (!enable) {
                passwordInput.removeAttribute('required');
                passwordInput.value = '';
            }
        }
        if (confirmInput) {
            confirmInput.disabled = !enable;
            if (!enable) {
                confirmInput.removeAttribute('required');
                confirmInput.value = '';
            }
        }
    }
    
    function validatePasswords() {
        if (!changePasswordCheckbox || !changePasswordCheckbox.checked) {
            return true;
        }
        
        if (!passwordInput.value && !confirmInput.value) {
            return true;
        }
        
        if (passwordInput.value.length < 6) {
            passwordInput.setCustomValidity(lang === 'ru' ? 'Пароль должен содержать минимум 6 символов' : 'Password must be at least 6 characters');
            return false;
        } else {
            passwordInput.setCustomValidity('');
        }
        
        if (passwordInput.value !== confirmInput.value) {
            confirmInput.setCustomValidity(lang === 'ru' ? 'Пароли не совпадают' : 'Passwords do not match');
            return false;
        } else {
            confirmInput.setCustomValidity('');
        }
        
        return true;
    }
    
    if (changePasswordCheckbox && passwordFields) {
        if (!changePasswordCheckbox.checked) {
            togglePasswordValidation(false);
        } else {
            togglePasswordValidation(true);
        }
        
        changePasswordCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordFields.style.display = 'block';
                togglePasswordValidation(true);
            } else {
                passwordFields.style.display = 'none';
                togglePasswordValidation(false);
            }
        });
        
        passwordInput.addEventListener('input', function() {
            if (changePasswordCheckbox.checked) {
                if (this.value.length > 0 && this.value.length < 6) {
                    this.setCustomValidity(lang === 'ru' ? 'Пароль должен содержать минимум 6 символов' : 'Password must be at least 6 characters');
                } else {
                    this.setCustomValidity('');
                }
                validatePasswords();
            }
        });
        
        confirmInput.addEventListener('input', function() {
            if (changePasswordCheckbox.checked) {
                validatePasswords();
            }
        });
    }
    
    if (form) {
        let isSubmitting = false;
        
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (changePasswordCheckbox && changePasswordCheckbox.checked) {
                if (!validatePasswords()) {
                    e.preventDefault();
                    return false;
                }
            }
            
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            
            isSubmitting = true;
            
            if (submitBtn) {
                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${lang === 'ru' ? 'Сохранение...' : 'Saving...'}`;
                submitBtn.setAttribute('data-original-html', originalHtml);
            }
            
            return true;
        });
        
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && submitBtn.disabled && submitBtn.getAttribute('data-original-html')) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtn.getAttribute('data-original-html');
        }
    }

    document.querySelectorAll('.unassign-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = this.dataset.userId;
            const achievementId = this.dataset.achievementId;
            const achievementName = this.dataset.achievementName;
            
            if (confirm(lang === 'ru' ? `Вы уверены, что хотите отозвать ачивку "${achievementName}" у пользователя?` : `Are you sure you want to revoke the achievement "${achievementName}" from the user?`)) {
                const originalHtml = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                
                fetch(ADMIN_URL + '/user-achievements/unassign/' + userId + '/' + achievementId, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const toast = document.createElement('div');
                        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
                        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
                        toast.innerHTML = lang === 'ru' ? 'Ачивка успешно отозвана' : 'Achievement revoked successfully';
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 3000);
                        
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert(data.message || (lang === 'ru' ? 'Ошибка при отзыве ачивки' : 'Error revoking achievement'));
                        this.disabled = false;
                        this.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(lang === 'ru' ? 'Ошибка при отзыве ачивки' : 'Error revoking achievement');
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                });
            }
        });
    });
});