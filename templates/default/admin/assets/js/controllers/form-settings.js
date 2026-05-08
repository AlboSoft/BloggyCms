document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        const id = checkbox.id;
        if (id.includes('_enabled') || id.includes('_protection')) {
            const toggleFuncName = 'toggle' + 
                id.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join('');
            
            if (typeof window[toggleFuncName] === 'function') {
                checkbox.addEventListener('change', window[toggleFuncName]);
            }
        }
    });
    
    const generateSecretBtn = document.querySelector('[onclick="generateCaptchaSecret()"]');
    if (generateSecretBtn) {
        generateSecretBtn.addEventListener('click', function(e) {
            e.preventDefault();
            generateCaptchaSecret();
        });
    }
    
    const captchaTypeSelect = document.getElementById('captcha_type');
    if (captchaTypeSelect) {
        captchaTypeSelect.addEventListener('change', function() {
            updateCaptchaExample();
        });
    }
    
    const updateCaptchaBtn = document.querySelector('[onclick="updateCaptchaExample()"]');
    if (updateCaptchaBtn) {
        updateCaptchaBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetchCaptchaExample();
        });
    }
});

function fetchCaptchaExample() {
    const type = document.getElementById('captcha_type').value;
    const question = document.getElementById('captcha_question').value;
    
    fetch('<?= ADMIN_URL ?>/forms/generate-captcha-example', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=${encodeURIComponent(type)}&question=${encodeURIComponent(question)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('captcha_example').innerHTML = `
                <p><strong>${lang === 'ru' ? 'Вопрос:' : 'Question:'}</strong> ${data.question}</p>
                <p><strong>${lang === 'ru' ? 'Ответ:' : 'Answer:'}</strong> ${data.answer}</p>
            `;
        }
    })
    .catch(error => {
        console.error(lang === 'ru' ? 'Ошибка:' : 'Error:', error);
    });
}