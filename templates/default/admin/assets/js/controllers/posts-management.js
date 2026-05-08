if (typeof PostsManagement !== 'undefined') {

} else {
    class PostsManagement {
        constructor() {
            this.init();
        }

        init() {
            this.setupPasswordToggle();
            this.setupFormSubmission();
            this.setupDateToggle();
        }

        setupPasswordToggle() {
            const passwordCheckbox = document.getElementById('password_protected');
            const passwordField = document.querySelector('.password-field');
            
            if (passwordCheckbox && passwordField) {
                passwordCheckbox.addEventListener('change', function() {
                    passwordField.style.display = this.checked ? 'block' : 'none';
                });
                passwordField.style.display = passwordCheckbox.checked ? 'block' : 'none';
            }
        }

        setupDateToggle() {
            const dateCheckbox = document.getElementById('change_publish_date');
            const dateField = document.querySelector('.publish-date-field');
            const currentDateInfo = document.querySelector('.current-publish-date');

            if (dateCheckbox && dateField) {
                dateCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        dateField.style.display = 'block';
                        currentDateInfo.style.display = 'none';
                    } else {
                        dateField.style.display = 'none';
                        currentDateInfo.style.display = 'block';
                    }
                });
                
                const isChecked = dateCheckbox.checked;
                dateField.style.display = isChecked ? 'block' : 'none';
                currentDateInfo.style.display = isChecked ? 'none' : 'block';
            }
        }

        setupFormSubmission() {
            const postForm = document.getElementById('post-form');
            if (postForm) {
                postForm.addEventListener('submit', (e) => {
                    this.handleFormSubmit(e);
                });
            }
        }

        handleFormSubmit(e) {
            if (window.BlockManager) {
                if (typeof window.BlockManager.prepareFormData === 'function') {
                    window.BlockManager.prepareFormData();
                }
                
                if (typeof window.BlockManager.updateBlocksInput === 'function') {
                    window.BlockManager.updateBlocksInput();
                }
            }
            
            this.showLoadingIndicator();
        }

        showLoadingIndicator() {
            const submitBtn = document.querySelector('#post-form [type="submit"]');
            if (submitBtn) {
                const originalBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${lang === 'ru' ? 'Сохранение...' : 'Saving...'}`;
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }, 5000);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        new PostsManagement();
    });
}