document.addEventListener('DOMContentLoaded', function() {
    const showMoreButtons = document.querySelectorAll('.tg-show-more-btn');
    
    showMoreButtons.forEach(button => {
        button.addEventListener('click', function() {
            const month = this.dataset.month;
            const monthContainer = document.querySelector(`.tg-month-posts[data-month="${month}"]`);
            
            if (monthContainer) {
                const hiddenPosts = monthContainer.querySelectorAll('.tg-archive-post-item.tg-hidden');
                hiddenPosts.forEach(post => {
                    post.classList.remove('tg-hidden');
                });
                
                this.style.display = 'none';
            }
        });
    });
    
    document.querySelectorAll('.tg-month-posts').forEach(container => {
        const posts = container.querySelectorAll('.tg-archive-post-item');
        if (posts.length > 3) {
            for (let i = 3; i < posts.length; i++) {
                posts[i].classList.add('tg-hidden');
            }
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const ajaxForms = document.querySelectorAll('form[data-ajax="true"]');
    
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : (lang === 'ru' ? 'Отправить' : 'Submit');
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>' + (lang === 'ru' ? 'Отправка...' : 'Sending...');
            }
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const successHtml = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            ${data.message}
                        </div>
                    `;
                    form.innerHTML = successHtml;
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                } else {
                    let existingError = form.querySelector('.alert-danger');
                    if (!existingError) {
                        const errorHtml = `
                            <div class="alert alert-danger mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                ${data.message}
                            </div>
                        `;
                        form.insertAdjacentHTML('afterbegin', errorHtml);
                    } else {
                        existingError.innerHTML = `
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${data.message}
                        `;
                    }
                    
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                    
                    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(lang === 'ru' ? 'Произошла ошибка при отправке формы. Попробуйте позже.' : 'An error occurred while submitting the form. Please try again later.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tg-register-form');
    if (!form) return;
    
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirm');
    const usernameStatus = document.getElementById('username-status');
    const emailStatus = document.getElementById('email-status');
    const passwordStrength = document.getElementById('password-strength');
    const passwordMatch = document.getElementById('password-match');
    
    function checkUsername() {
        const username = usernameInput.value;
        
        if (username.length === 0) {
            usernameStatus.innerHTML = '';
            return;
        }
        
        if (username.length < 3) {
            usernameStatus.innerHTML = '<span class="tg-status-error">' + (lang === 'ru' ? 'Слишком короткое' : 'Too short') + '</span>';
            return;
        }
        
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            usernameStatus.innerHTML = '<span class="tg-status-error">' + (lang === 'ru' ? 'Недопустимые символы' : 'Invalid characters') + '</span>';
            return;
        }
        
        usernameStatus.innerHTML = '<span class="tg-status-valid">' + (lang === 'ru' ? 'Доступно' : 'Available') + '</span>';
    }
    
    function checkEmail() {
        const email = emailInput.value;
        
        if (email.length === 0) {
            emailStatus.innerHTML = '';
            return;
        }
        
        if (!email.includes('@') || !email.includes('.')) {
            emailStatus.innerHTML = '<span class="tg-status-error">' + (lang === 'ru' ? 'Неверный формат' : 'Invalid format') + '</span>';
            return;
        }
        
        emailStatus.innerHTML = '<span class="tg-status-valid">' + (lang === 'ru' ? 'Верный' : 'Valid') + '</span>';
    }
    
    function checkPasswordStrength() {
        const password = passwordInput.value;
        const reqLength = document.getElementById('req-length');
        
        if (password.length === 0) {
            passwordStrength.innerHTML = '';
            reqLength.classList.remove('valid', 'invalid');
            return;
        }
        
        if (password.length >= 6) {
            reqLength.classList.add('valid');
            reqLength.classList.remove('invalid');
            reqLength.querySelector('svg').style.color = '#31b131';
        } else {
            reqLength.classList.add('invalid');
            reqLength.classList.remove('valid');
            reqLength.querySelector('svg').style.color = '#dc3545';
        }
        
        let strength = 'weak';
        if (password.length >= 8) strength = 'medium';
        if (password.length >= 10 && /[A-Z]/.test(password) && /[0-9]/.test(password)) strength = 'strong';
        
        const strengthText = strength === 'weak' ? (lang === 'ru' ? 'Слабый' : 'Weak') : strength === 'medium' ? (lang === 'ru' ? 'Средний' : 'Medium') : (lang === 'ru' ? 'Сильный' : 'Strong');
        const strengthColor = strength === 'weak' ? '#dc3545' : strength === 'medium' ? '#f1c40f' : '#31b131';
        
        passwordStrength.innerHTML = `<span class="tg-status" style="color: ${strengthColor};">${strengthText}</span>`;
    }
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (confirm.length === 0) {
            passwordMatch.innerHTML = '';
            return;
        }
        
        if (password === confirm) {
            passwordMatch.innerHTML = '<span class="tg-status-valid">✓ ' + (lang === 'ru' ? 'Совпадают' : 'Match') + '</span>';
        } else {
            passwordMatch.innerHTML = '<span class="tg-status-error">✗ ' + (lang === 'ru' ? 'Не совпадают' : 'Don\'t match') + '</span>';
        }
    }
    
    function togglePassword(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        
        button.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                button.innerHTML = '<svg class="icon icon-eye-slash" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#eye-slash"></use></svg>';
            } else {
                input.type = 'password';
                button.innerHTML = '<svg class="icon icon-eye" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#eye"></use></svg>';
            }
        });
    }
    
    if (usernameInput) {
        usernameInput.addEventListener('input', checkUsername);
        usernameInput.addEventListener('blur', checkUsername);
    }
    
    if (emailInput) {
        emailInput.addEventListener('input', checkEmail);
        emailInput.addEventListener('blur', checkEmail);
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength();
            checkPasswordMatch();
        });
    }
    
    if (confirmInput) {
        confirmInput.addEventListener('input', checkPasswordMatch);
    }
    
    togglePassword('password', 'toggle-password');
    togglePassword('password_confirm', 'toggle-confirm-password');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const username = usernameInput.value;
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const terms = document.getElementById('terms').checked;
            
            let isValid = true;
            let errorMessage = '';
            
            if (username.length < 3) {
                isValid = false;
                errorMessage = lang === 'ru' ? 'Имя пользователя должно содержать не менее 3 символов' : 'Username must be at least 3 characters';
            } else if (password.length < 6) {
                isValid = false;
                errorMessage = lang === 'ru' ? 'Пароль должен содержать не менее 6 символов' : 'Password must be at least 6 characters';
            } else if (password !== confirm) {
                isValid = false;
                errorMessage = lang === 'ru' ? 'Пароли не совпадают' : 'Passwords do not match';
            } else if (!terms) {
                isValid = false;
                errorMessage = lang === 'ru' ? 'Вы должны согласиться с условиями использования' : 'You must agree to the Terms of Service';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
                return false;
            }
            
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<svg class="icon icon-hourglass tg-mr-1" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#hourglass"></use></svg> ' + (lang === 'ru' ? 'Создаю аккаунт...' : 'Creating account...');
            submitBtn.disabled = true;
            
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }, 5000);
        });
    }
    
    if (usernameInput.value) checkUsername();
    if (emailInput.value) checkEmail();
    if (passwordInput.value) checkPasswordStrength();
    checkPasswordMatch();
});