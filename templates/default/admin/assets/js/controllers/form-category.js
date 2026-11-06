document.addEventListener('DOMContentLoaded', function() {
    const passwordProtectedCheckbox = document.getElementById('password_protected');
    const passwordField = document.querySelector('.password-field');
    
    if (passwordProtectedCheckbox && passwordField) {
        passwordProtectedCheckbox.addEventListener('change', function() {
            passwordField.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                document.getElementById('password').value = '';
            }
        });
    }

    const nameInput = document.querySelector('input[name="name"]');
    const slugInput = document.querySelector('input[name="slug"]');
    
    if (nameInput && slugInput) {
        nameInput.addEventListener('blur', function() {
            if (!slugInput.value) {
                const slug = this.value.toLowerCase()
                    .replace(/[^\w\u0400-\u04FF]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });
    }

    function setupCharacterCounter(selector, maxLength) {
        const input = document.querySelector(selector);
        if (!input) return;
        
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        input.parentNode.appendChild(counter);
        
        function updateCounter() {
            const length = input.value.length;
            counter.textContent = `${length}/${maxLength}`;
            counter.className = `form-text text-end ${length > maxLength ? 'text-danger' : 'text-muted'}`;
        }
        
        input.addEventListener('input', updateCounter);
        updateCounter();
    }

    setupCharacterCounter('input[name="meta_title"]', 60);
    setupCharacterCounter('textarea[name="meta_description"]', 160);

});
