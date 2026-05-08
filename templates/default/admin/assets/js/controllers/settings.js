document.addEventListener('DOMContentLoaded', function() {
    function toggleDependentSettings() {
        const readingTime = document.getElementById('show_reading_time');
        const disableReadingTime = document.getElementById('allow_disable_reading_time');
        
        if (readingTime && disableReadingTime) {
            const isReadingTimeEnabled = readingTime.checked;
            disableReadingTime.disabled = !isReadingTimeEnabled;
            if (!isReadingTimeEnabled) {
                disableReadingTime.checked = false;
            }
        }

        const rating = document.getElementById('enable_rating');
        const disableRating = document.getElementById('allow_disable_rating');
        const guestRating = document.getElementById('guest_rating');
        
        if (rating) {
            const isRatingEnabled = rating.checked;
            
            if (disableRating) {
                disableRating.disabled = !isRatingEnabled;
                if (!isRatingEnabled) {
                    disableRating.checked = false;
                }
            }
            if (guestRating) {
                guestRating.disabled = !isRatingEnabled;
                if (!isRatingEnabled) {
                    guestRating.checked = false;
                }
            }
        }

        const tags = document.getElementById('enable_tags');
        const tagSettings = document.querySelectorAll('[name*="max_tags_per_post"], [name*="popular_tags_count"]');
        
        if (tags) {
            const isTagsEnabled = tags.checked;
            tagSettings.forEach(setting => {
                setting.disabled = !isTagsEnabled;
            });
        }

        const comments = document.getElementById('enable_comments');
        const commentSettings = document.querySelectorAll('[name*="guest_comments"], [name*="comment_approval"], [name*="comment_depth"], [name*="comments_per_page"]');
        
        if (comments) {
            const isCommentsEnabled = comments.checked;
            commentSettings.forEach(setting => {
                setting.disabled = !isCommentsEnabled;
            });
        }

        const backupsEnabled = document.getElementById('template_backups_enabled');
        const backupSettings = document.querySelectorAll('[name*="template_backups_count"], [name*="template_backups_cleanup"]');
        
        if (backupsEnabled) {
            const isBackupsEnabled = backupsEnabled.checked;
            backupSettings.forEach(setting => {
                setting.disabled = !isBackupsEnabled;
            });
        }
    }

    toggleDependentSettings();

    const dependentCheckboxes = [
        'show_reading_time', 
        'enable_rating', 
        'enable_tags', 
        'enable_comments',
        'template_backups_enabled'
    ];

    dependentCheckboxes.forEach(checkboxId => {
        const checkbox = document.getElementById(checkboxId);
        if (checkbox) {
            checkbox.addEventListener('change', toggleDependentSettings);
        }
    });

    const selectAll = document.getElementById('select_all_blocks');
    const deselectAll = document.getElementById('deselect_all_blocks');
    const blockCheckboxes = document.querySelectorAll('input[name="settings[enabled_blocks][]"]');

    function updateBlockCardStyle(checkbox) {
        const card = checkbox.closest('.card');
        if (!card) return;

        const isChecked = checkbox.checked;
        const icon = card.querySelector('i');
        
        if (isChecked) {
            card.classList.remove('border-secondary', 'disabled');
            card.classList.add('border-primary');
            icon.classList.remove('text-muted');
            icon.classList.add('text-primary');
        } else {
            card.classList.remove('border-primary');
            card.classList.add('border-secondary', 'disabled');
            icon.classList.remove('text-primary');
            icon.classList.add('text-muted');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('click', function(e) {
            e.preventDefault();
            blockCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
                updateBlockCardStyle(checkbox);
            });
        });
    }

    if (deselectAll) {
        deselectAll.addEventListener('click', function(e) {
            e.preventDefault();
            blockCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                updateBlockCardStyle(checkbox);
            });
        });
    }

    blockCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBlockCardStyle(this);
        });
        
        updateBlockCardStyle(checkbox);
    });

    function enhanceUX() {
        const firstInput = document.querySelector('form input:not([type="hidden"]), form select, form textarea');
        if (firstInput && !firstInput.disabled) {}

        const dangerousButtons = document.querySelectorAll('a[href*="cleanup-backups"], .btn-danger');
        dangerousButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm(lang === 'ru' ? 'Вы уверены, что хотите выполнить это действие? Это действие нельзя отменить.' : 'Are you sure you want to perform this action? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });

        const numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('blur', function() {
                const min = parseInt(this.min) || 1;
                const max = parseInt(this.max) || 999999;
                const value = parseInt(this.value) || min;
                
                if (value < min) {
                    this.value = min;
                } else if (value > max) {
                    this.value = max;
                }
            });
        });
    }

    enhanceUX();
});