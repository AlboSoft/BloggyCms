<?php
/**
 * Template Name: Страница закладок
 */
?>

<div class="tg-bookmarks-page">
    <div class="tg-container">
        
        <div class="tg-bookmarks-header tg-mb-4">
            <div class="tg-bookmarks-header-left">
                <div class="tg-bookmarks-icon">
                    <?php echo bloggy_icon('bs', 'bookmark-star', '24', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-bookmarks-info">
                    <h1 class="tg-bookmarks-title"><?php echo LANG_TEMPLATE_BOOKMARKS_TITLE; ?></h1>
                    <p class="tg-bookmarks-subtitle tg-text-muted">
                        <?php echo bloggy_icon('bs', 'bookmark', '14', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo ($total = (int)($bookmarks_count ?? 0)) . ' ' . plural($total, [LANG_TEMPLATE_BOOKMARKS_POST_1, LANG_TEMPLATE_BOOKMARKS_POST_2, LANG_TEMPLATE_BOOKMARKS_POST_3]); ?>
                    </p>
                </div>
            </div>
            
            <?php if (!empty($posts)) { ?>
            <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">
                <?php echo bloggy_icon('bs', 'compass', '14', 'currentColor', 'tg-mr-1'); ?>
                <?php echo LANG_TEMPLATE_BOOKMARKS_ALL_POSTS_BTN; ?>
            </a>
            <?php } ?>
        </div>
        
        <?php if (empty($posts)) { ?>
            <div class="tg-empty-state">
                <div class="tg-empty-state-icon">
                    <?php echo bloggy_icon('bs', 'bookmark', '48', 'var(--tg-text-secondary)'); ?>
                </div>
                <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_BOOKMARKS_EMPTY_TITLE; ?></h3>
                <p class="tg-empty-state-text tg-text-muted">
                    <?php echo LANG_TEMPLATE_BOOKMARKS_EMPTY_TEXT; ?>
                </p>
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                    <?php echo bloggy_icon('bs', 'compass', '16', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_BOOKMARKS_FIND_POSTS_BTN; ?>
                </a>
            </div>
            
        <?php } else { ?>
            
            <div class="tg-bookmarks-grid">
                <?php foreach ($posts as $post) { 
                    $featuredImage = $post['featured_image'] 
                        ? BASE_URL . '/uploads/images/' . html($post['featured_image']) 
                        : null;
                ?>
                <div class="tg-bookmark-item" data-post-id="<?php echo $post['id']; ?>">
                    
                    <?php if ($featuredImage) { ?>
                    <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>" class="tg-bookmark-image">
                        <img src="<?php echo $featuredImage; ?>" 
                             alt="<?php echo html($post['title']); ?>"
                             loading="lazy">
                    </a>
                    <?php } ?>
                    
                    <div class="tg-bookmark-content">
                        <?php if (!empty($post['category_name'])) { ?>
                        <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                           class="tg-bookmark-category">
                            <?php echo html($post['category_name']); ?>
                        </a>
                        <?php } ?>
                        
                        <h3 class="tg-bookmark-title">
                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                <?php echo html($post['title']); ?>
                            </a>
                        </h3>
                        
                        <div class="tg-bookmark-meta">
                            <span class="tg-bookmark-date">
                                <?php echo bloggy_icon('bs', 'bookmark', '12', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo sprintf(LANG_TEMPLATE_BOOKMARKS_SAVED_AT, time_ago($post['bookmarked_at'])); ?>
                            </span>
                            
                            <?php if ($post['views'] > 0) { ?>
                            <span class="tg-bookmark-views">
                                <?php echo bloggy_icon('bs', 'eye', '12', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo $post['views'] . ' ' . plural($post['views'], [LANG_TEMPLATE_BOOKMARKS_VIEW_1, LANG_TEMPLATE_BOOKMARKS_VIEW_2, LANG_TEMPLATE_BOOKMARKS_VIEW_3]); ?>
                            </span>
                            <?php } ?>
                        </div>
                    </div>

                    <button class="tg-bookmark-remove" 
                            data-post-id="<?php echo $post['id']; ?>"
                            title="<?php echo LANG_TEMPLATE_BOOKMARKS_REMOVE_TITLE; ?>">
                        <?php echo bloggy_icon('bs', 'x', '16', 'currentColor'); ?>
                    </button>
                </div>
                <?php } ?>
            </div>
            
        <?php } ?>
        
    </div>
</div>

<?php 
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.tg-bookmark-remove');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const bookmarkItem = this.closest('.tg-bookmark-item');
            
            if (!confirm('<?php echo LANG_TEMPLATE_BOOKMARKS_REMOVE_CONFIRM; ?>')) {
                return;
            }
            
            bookmarkItem.style.opacity = '0.5';
            bookmarkItem.style.pointerEvents = 'none';
            
            fetch(`<?php echo BASE_URL; ?>/post/bookmark/${postId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bookmarkItem.style.transition = 'all 0.3s ease';
                    bookmarkItem.style.transform = 'translateX(100%)';
                    bookmarkItem.style.opacity = '0';
                    
                    setTimeout(() => {
                        bookmarkItem.remove();
                        const remainingItems = document.querySelectorAll('.tg-bookmark-item');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
                        
                        const countElement = document.querySelector('.tg-bookmarks-subtitle');
                        if (countElement) {
                            const currentCount = parseInt(countElement.textContent) || 0;
                            countElement.innerHTML = `<?php echo bloggy_icon('bs', 'bookmark', '14', 'currentColor', 'tg-mr-1'); ?> ${currentCount - 1} <?php echo LANG_TEMPLATE_BOOKMARKS_POSTS_COUNT_TEXT; ?>`;
                        }
                    }, 300);
                } else {
                    bookmarkItem.style.opacity = '1';
                    bookmarkItem.style.pointerEvents = 'auto';
                    alert('<?php echo LANG_TEMPLATE_BOOKMARKS_REMOVE_ERROR; ?>');
                }
            })
            .catch(() => {
                bookmarkItem.style.opacity = '1';
                bookmarkItem.style.pointerEvents = 'auto';
                alert('<?php echo LANG_TEMPLATE_BOOKMARKS_REMOVE_ERROR; ?>');
            });
        });
    });
});
</script>
<?php front_bottom_js(ob_get_clean()); ?>

<?php 
ob_start();
?>
<script>
window.baseUrl = '<?= BASE_URL ?>';
window.userLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>

<?php front_bottom_js(ob_get_clean()); ?>

<?php echo add_frontend_js('/templates/default/front/assets/js/bookmarks.js'); ?>