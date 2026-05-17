document.addEventListener('DOMContentLoaded', function() {
    initFormValidation();
    initPasswordStrength();
    initPasswordToggle();
    initDatabaseTest();
    initProgressIndicator();
    initAutoDetectUrl();
    initToggleSwitches();
});

function getCurrentLang() {
    return document.documentElement.lang || 'ru';
}

function initToggleSwitches() {
    document.querySelectorAll('.toggle-switch').forEach(toggle => {
        let checkbox = null;
        
        const parent = toggle.closest('label');
        if (parent) {
            checkbox = parent.querySelector('input[type="checkbox"]');
        }
        
        if (!checkbox) {
            checkbox = toggle.previousElementSibling;
            if (checkbox && checkbox.type !== 'checkbox') {
                checkbox = null;
            }
        }
        
        if (!checkbox) {
            let parentElement = toggle.parentElement;
            while (parentElement && !checkbox) {
                checkbox = parentElement.querySelector('input[type="checkbox"]');
                parentElement = parentElement.parentElement;
            }
        }
        
        if (checkbox && checkbox.type === 'checkbox') {
            const slider = toggle.querySelector('.toggle-slider');
            if (checkbox.checked && slider) {
                slider.style.backgroundColor = '#3498db';
            } else if (slider) {
                slider.style.backgroundColor = '#ccc';
            }
            
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                checkbox.checked = !checkbox.checked;
                
                const changeEvent = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(changeEvent);
                
                const sliderEl = this.querySelector('.toggle-slider');
                if (sliderEl) {
                    if (checkbox.checked) {
                        sliderEl.style.backgroundColor = '#3498db';
                    } else {
                        sliderEl.style.backgroundColor = '#ccc';
                    }
                }
            });
            
            checkbox.addEventListener('change', function() {
                const sliderEl = toggle.querySelector('.toggle-slider');
                if (sliderEl) {
                    if (this.checked) {
                        sliderEl.style.backgroundColor = '#3498db';
                    } else {
                        sliderEl.style.backgroundColor = '#ccc';
                    }
                }
            });
        }
    });
}

function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    const lang = getCurrentLang();
    const errorMessage = lang === 'ru' ? 'Заполните все обязательные поля' : 'Please fill all required fields';
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.classList.add('error');
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
                return;
            }
            
            const requiredFields = form.querySelectorAll('[required]');
            let hasEmpty = false;
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    hasEmpty = true;
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (hasEmpty) {
                event.preventDefault();
                event.stopPropagation();
                showNotification(errorMessage, 'error');
                return;
            }
            
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span>';
            }
            
            form.classList.add('was-validated');
        });
        
        form.querySelectorAll('[required]').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('error');
            });
        });
    });
}

function initPasswordStrength() {
    const passwordInput = document.getElementById('admin_password');
    if (!passwordInput) return;
    
    const lang = getCurrentLang();
    const weakText = lang === 'ru' ? 'Слабый пароль' : 'Weak password';
    const mediumText = lang === 'ru' ? 'Средний пароль' : 'Medium password';
    const strongText = lang === 'ru' ? 'Надёжный пароль' : 'Strong password';
    
    const strengthContainer = document.createElement('div');
    strengthContainer.className = 'password-strength';
    strengthContainer.innerHTML = `
        <div class="strength-meter">
            <div class="strength-bar" style="width: 0"></div>
        </div>
        <div class="strength-text" style="font-size: 0.85rem; color: var(--tg-gray);"></div>
    `;
    
    passwordInput.parentElement.appendChild(strengthContainer);
    const strengthBar = strengthContainer.querySelector('.strength-bar');
    const strengthText = strengthContainer.querySelector('.strength-text');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        strengthBar.className = 'strength-bar';
        if (strength.score <= 1) {
            strengthBar.classList.add('weak');
            strengthText.textContent = weakText;
        } else if (strength.score === 2) {
            strengthBar.classList.add('medium');
            strengthText.textContent = mediumText;
        } else {
            strengthBar.classList.add('strong');
            strengthText.textContent = strongText;
        }
    });
    
    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        return { score: Math.min(score, 3) };
    }
}

function initPasswordToggle() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    const lang = getCurrentLang();
    const toggleTitle = lang === 'ru' ? 'Показать/скрыть пароль' : 'Show/hide password';
    
    passwordFields.forEach(field => {
        if (field.closest('.password-wrapper')?.querySelector('.password-toggle')) {
            return;
        }
        
        const wrapper = field.parentElement;
        
        if (!wrapper.classList.contains('password-wrapper')) {
            const newWrapper = document.createElement('div');
            newWrapper.className = 'password-wrapper';
            wrapper.parentNode.insertBefore(newWrapper, wrapper);
            newWrapper.appendChild(field);
        }
        
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'password-toggle';
        toggleBtn.title = toggleTitle;
        toggleBtn.innerHTML = '<svg class="icon icon-eye" width="16" height="16" fill="currentColor" style="display: inline-block; vertical-align: middle;"><use href="../templates/default/admin/icons/bs.svg#eye"/></svg>';
        const parent = field.closest('.password-wrapper') || field.parentElement;
        parent.style.position = 'relative';
        
        parent.appendChild(toggleBtn);
        
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
            
            const icon = this.querySelector('use');
            if (icon) {
                const href = type === 'password' ? '../templates/default/admin/icons/bs.svg#eye' : '../templates/default/admin/icons/bs.svg#eye-slash';
                icon.setAttribute('href', href);
            }
        });
    });
}

function initDatabaseTest() {
    const testBtn = document.getElementById('test-connection');
    if (!testBtn) return;
    
    const originalText = testBtn.innerHTML;
    
    testBtn.addEventListener('click', async function() {
        const form = document.getElementById('db-form');
        const formData = new FormData(form);
        const lang = getCurrentLang();
        const checkingText = lang === 'ru' ? 'Проверка...' : 'Checking...';
        const successText = lang === 'ru' ? 'Подключение успешно!' : 'Connection successful!';
        const errorText = lang === 'ru' ? 'Ошибка' : 'Error';
        const networkErrorText = lang === 'ru' ? 'Ошибка сети' : 'Network error';
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner"></span> ' + checkingText;
        
        try {
            const response = await fetch('ajax/test-connection.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                showNotification(successText, 'success');
            } else {
                showNotification(errorText + ': ' + result.message, 'error');
            }
        } catch (error) {
            showNotification(networkErrorText, 'error');
        } finally {
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });
}

function initProgressIndicator() {
    const progressBar = document.querySelector('.progress-bar');
    if (!progressBar) return;
    
    const step = parseInt(document.body.dataset.step || 1);
    const progress = (step / 4) * 100;
    
    setTimeout(() => {
        progressBar.style.width = progress + '%';
    }, 100);
}

function initAutoDetectUrl() {
    const urlField = document.getElementById('site_url');
    if (!urlField || urlField.value) return;
    
    const protocol = window.location.protocol + '//';
    const host = window.location.host;
    let path = window.location.pathname.split('/install')[0].replace(/\\/g, '/');
    const fullUrl = (protocol + host + path).replace(/\/+$/, '');
    
    urlField.value = fullUrl;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

window.generatePassword = function() {
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    let password = '';
    const lang = getCurrentLang();
    const generatedText = lang === 'ru' ? 'Пароль сгенерирован' : 'Password generated';
    
    for (let i = 0; i < 12; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    const passwordField = document.getElementById('admin_password');
    const confirmField = document.getElementById('admin_password_confirm');
    
    if (passwordField && confirmField) {
        passwordField.value = password;
        confirmField.value = password;
        showNotification(generatedText, 'success');
    }
};