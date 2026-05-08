document.addEventListener('DOMContentLoaded', function() {
    const movePostsRadio = document.getElementById('move_posts');
    const deleteAllRadio = document.getElementById('delete_all');
    const targetCategorySelect = document.getElementById('target_category_select');
    const optionLabels = document.querySelectorAll('.option-label');
    
    function toggleCategorySelect() {
        if (movePostsRadio.checked) {
            targetCategorySelect.required = true;
            targetCategorySelect.disabled = false;
            targetCategorySelect.style.opacity = '1';
        } else {
            targetCategorySelect.required = false;
            targetCategorySelect.disabled = true;
            targetCategorySelect.style.opacity = '0.5';
        }
    }
    
    optionLabels.forEach(label => {
        label.addEventListener('click', function() {
            const radio = document.getElementById(this.getAttribute('for'));
            radio.checked = true;
            toggleCategorySelect();
        });
    });
    
    movePostsRadio.addEventListener('change', toggleCategorySelect);
    deleteAllRadio.addEventListener('change', toggleCategorySelect);
    
    toggleCategorySelect();
});

function confirmDeletion() {
    const deleteAllRadio = document.getElementById('delete_all');
    const targetCategorySelect = document.getElementById('target_category_select');
    
    if (deleteAllRadio.checked) {
        return confirm(lang === 'ru' ? 'ВНИМАНИЕ! Вы собираетесь удалить категорию и все посты в ней. Это действие нельзя отменить. Вы уверены?' : 'WARNING! You are about to delete a category and all posts within it. This action cannot be undone. Are you sure?');
    } else {
        if (!targetCategorySelect.value) {
            alert(lang === 'ru' ? 'Пожалуйста, выберите категорию для перемещения постов' : 'Please select a category to move the posts to');
            return false;
        }
        return confirm(lang === 'ru' ? 'Вы уверены, что хотите удалить категорию и переместить посты в выбранную категорию?' : 'Are you sure you want to delete the category and move the posts to the selected category?');
    }
}