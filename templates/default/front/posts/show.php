<?php
/**
 * Template Name: Страница поста
 */

$isPasswordProtected = $post['password_protected'] == 1; 
$allowComments = isset($post['allow_comments']) ? $post['allow_comments'] == 1 : true;
$templatePath = TEMPLATES_PATH . '/' . DEFAULT_TEMPLATE;
$totalComments = $totalComments ?? ($post['comments_count'] ?? 0);
?>
<div class="tg-post-page">
    <div class="tg-container">
        
        <div class="tg-post-header tg-mb-4">
            <div class="tg-post-meta-top">
                <?php if (!empty($post['category_name'])) { ?>
                <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                   class="tg-post-category">
                    <?php echo html($post['category_name']); ?>
                </a>
                <?php } ?>
                
                <span class="tg-post-date">
                    <?php echo bloggy_icon('bs', 'calendar', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo date('d.m.Y', strtotime($post['created_at'])); ?>
                </span>
                
                <?php if ($isPasswordProtected) { ?>
                    <span class="tg-post-protected" title="<?php echo LANG_TEMPLATE_POST_PASSWORD_PROTECTED_TITLE; ?>">
                        <?php echo bloggy_icon('bs', 'lock-fill', '14', 'currentColor'); ?>
                        <span><?php echo LANG_TEMPLATE_POST_PROTECTED_LABEL; ?></span>
                    </span>
                <?php } ?>
            </div>
            
            <h1 class="tg-post-title">
                <?php echo html($post['title']); ?>
                <?php if (isset($post['is_adult']) && $post['is_adult']) { ?>
                    <span class="tg-adult-badge">
                        <?php echo bloggy_icon('bs', 'cone-striped', '20', '#dc3545'); ?>
                        18+
                    </span>
                <?php } ?>
            </h1>
            
            <div class="tg-post-author">
                <div class="tg-author-avatar">
                    <?php if (!empty($post['author_avatar']) && $post['author_avatar'] !== 'default.jpg') { ?>
                        <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($post['author_avatar']); ?>" 
                             alt="<?php echo html($post['author_name'] ?? LANG_TEMPLATE_POST_AUTHOR_DEFAULT); ?>">
                    <?php } else { ?>
                        <div class="tg-avatar-placeholder-small">
                            <?php echo strtoupper(substr(($post['author_name'] ?? 'A'), 0, 1)); ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="tg-author-info">
                    <span class="tg-author-name"><?php echo html($post['author_name'] ?? LANG_TEMPLATE_POST_AUTHOR_DEFAULT); ?></span>
                </div>
            </div>
            
            <div class="tg-post-actions tg-mt-3">
                <button class="tg-action-btn tg-like-btn <?php echo isset($userLiked) && $userLiked ? 'tg-active' : ''; ?>" 
                        data-post-id="<?php echo $post['id']; ?>"
                        title="<?php echo LANG_TEMPLATE_POST_LIKE_BTN_TITLE; ?>">
                    <?php 
                    $heartIcon = (isset($userLiked) && $userLiked) ? 'heart-fill' : 'heart';
                    echo bloggy_icon('bs', $heartIcon, '18', 'currentColor', 'tg-mr-1');
                    ?>
                    <span class="tg-action-count"><?php echo $post['likes_count'] ?? 0; ?></span>
                </button>
                
                <button class="tg-action-btn tg-bookmark-btn <?php echo isset($userBookmarked) && $userBookmarked ? 'tg-active' : ''; ?>" 
                        data-post-id="<?php echo $post['id']; ?>"
                        title="<?php echo LANG_TEMPLATE_POST_BOOKMARK_BTN_TITLE; ?>">
                    <?php 
                    $bookmarkIcon = (isset($userBookmarked) && $userBookmarked) ? 'bookmark-fill' : 'bookmark';
                    echo bloggy_icon('bs', $bookmarkIcon, '18', 'currentColor', 'tg-mr-1');
                    ?>
                    <span><?php echo LANG_TEMPLATE_POST_BOOKMARK_BTN_TEXT; ?></span>
                </button>
                
                <a href="#comments" class="tg-action-btn">
                    <?php echo bloggy_icon('bs', 'chat-dots', '18', 'currentColor', 'tg-mr-1'); ?>
                    <span class="tg-action-count"><?php echo $post['comments_count'] ?? 0; ?></span>
                </a>
                
                <div class="tg-post-views tg-ml-auto">
                    <?php echo bloggy_icon('bs', 'eye', '16', 'currentColor', 'tg-mr-1'); ?>
                    <span><?php echo $post['views'] ?? 0; ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($post['featured_image']) { ?>
        <div class="tg-post-image-full tg-mb-4">
            <img src="<?php echo BASE_URL; ?>/uploads/images/<?php echo html($post['featured_image']); ?>" 
                 alt="<?php echo html($post['title']); ?>"
                 loading="lazy">
        </div>
        <?php } ?>
        
        <div class="tg-post-content tg-mb-5">
            <?php if (!empty($blocks)) { ?>
                <?php foreach ($blocks as $block) { ?>
                    <div class="tg-post-block tg-post-block-<?php echo $block['type']; ?> tg-mb-4">
                        <?php 
                        if (is_array($block['content'])) {
                            echo BlockRenderer::render($block);
                        } else {
                            echo $block['content'];
                        }
                        ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
        
        <?php
            $fieldModel = new FieldModel($this->db);
            $customFields = $fieldModel->getActiveByEntityType('post');
        ?>
                        
        <?php if (!empty($customFields)) { 
            $hasValues = false;
            foreach ($customFields as $field) { 
                $value = $fieldModel->getFieldValue('post', $post['id'], $field['system_name']);
                if (!empty($value)) {
                    $hasValues = true;
                    break;
                }
            }
        ?>
                        
        <?php if ($hasValues) { ?>
            <div class="tg-custom-fields">
                <?php foreach ($customFields as $field) { 
                    $value = $fieldModel->getFieldValue('post', $post['id'], $field['system_name']);
                    if (!empty($value)) { 
                ?>
                <div class="tg-custom-field-block">
                    <span class="tg-custom-field-label"><?php echo html($field['name']); ?>:</span>
                    <span class="tg-custom-field-value">
                        <?php echo $fieldModel->renderFieldDisplay($field, $value, 'post', $post['id']); ?>
                    </span>
                </div>
                <?php } } ?>
            </div>
        <?php } ?>
        <?php } ?>
        
        <?php if (!empty($post['tags'])) { ?>
        <div class="tg-post-tags tg-mb-4">
            <?php foreach ($post['tags'] as $tag) { ?>
            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" class="tg-tag">
                #<?php echo html($tag['name']); ?>
            </a>
            <?php } ?>
        </div>
        <?php } ?>
        
        <?php if (!$isPasswordProtected && $allowComments) { ?>
        <section id="comments" class="tg-comments-section tg-mt-5">
            <?php if (isset($_GET['comment_updated'])) { ?>
            <div class="tg-alert tg-alert-success tg-mb-4">
                <div class="tg-alert-icon">
                    <?php echo bloggy_icon('bs', 'check-circle', '18', 'currentColor'); ?>
                </div>
                <div class="tg-alert-content">
                    <strong><?php echo LANG_TEMPLATE_POST_COMMENT_SENT_TITLE; ?></strong>
                    <p class="tg-mb-0"><?php echo LANG_TEMPLATE_POST_COMMENT_SENT_TEXT; ?></p>
                    <?php if (isset($_GET['scroll_to_comment'])) { ?>
                    <a href="#comment-<?php echo (int)$_GET['scroll_to_comment']; ?>" class="tg-alert-link">
                        <?php echo LANG_TEMPLATE_POST_COMMENT_GO_TO_LINK; ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            
            <div class="tg-comments-header">
                <h3 class="tg-comments-title">
                    <?php echo bloggy_icon('bs', 'chat-dots', '20', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_POST_COMMENTS_TITLE; ?>
                </h3>
                <span class="tg-comments-count"><?php echo $totalComments; ?></span>
                <button class="tg-btn tg-btn-sm tg-btn-outline tg-ml-auto" onclick="scrollToCommentForm()">
                    <?php echo bloggy_icon('bs', 'pen', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_POST_WRITE_COMMENT_BTN; ?>
                </button>
            </div>
            
            <?php include $templatePath . '/front/comments/list.php'; ?>
            
            <?php include $templatePath . '/front/comments/form.php'; ?>
            
        </section>
        <?php } elseif ($isPasswordProtected) { ?>
        <div class="tg-comments-disabled tg-mt-5">
            <div class="tg-alert tg-alert-info">
                <div class="tg-alert-icon">
                    <?php echo bloggy_icon('bs', 'lock', '18', 'currentColor'); ?>
                </div>
                <div class="tg-alert-content">
                    <strong><?php echo LANG_TEMPLATE_POST_COMMENTS_DISABLED_PASSWORD_TITLE; ?></strong>
                    <p class="tg-mb-0"><?php echo LANG_TEMPLATE_POST_COMMENTS_DISABLED_PASSWORD_TEXT; ?></p>
                </div>
            </div>
        </div>
        <?php } elseif (!$allowComments) { ?>
        <div class="tg-comments-disabled tg-mt-5">
            <div class="tg-alert tg-alert-info">
                <div class="tg-alert-icon">
                    <?php echo bloggy_icon('bs', 'chat-left-x', '18', 'currentColor'); ?>
                </div>
                <div class="tg-alert-content">
                    <strong><?php echo LANG_TEMPLATE_POST_COMMENTS_DISABLED_AUTHOR_TITLE; ?></strong>
                    <p class="tg-mb-0"><?php echo LANG_TEMPLATE_POST_COMMENTS_DISABLED_AUTHOR_TEXT; ?></p>
                </div>
            </div>
        </div>
        <?php } ?>
        
    </div>
</div>

<?php 
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeBtn = document.querySelector('.tg-like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            
            if (!isLoggedIn) {
                window.location.href = '<?php echo BASE_URL; ?>/login';
                return;
            }
            
            const postId = this.dataset.postId;
            const countSpan = this.querySelector('.tg-action-count');
            const icon = this.querySelector('svg use');
            const wasActive = this.classList.contains('tg-active');
            this.classList.toggle('tg-active');
            
            if (countSpan) {
                const currentCount = parseInt(countSpan.textContent) || 0;
                countSpan.textContent = wasActive ? currentCount - 1 : currentCount + 1;
            }
            
            if (icon) {
                const iconHref = icon.getAttribute('href');
                const newIcon = wasActive ? 'heart' : 'heart-fill';
                icon.setAttribute('href', iconHref.replace(/heart(-fill)?/, newIcon));
            }
            
            fetch(`<?php echo BASE_URL; ?>/post/like/${postId}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(() => {
                this.classList.toggle('tg-active');
                if (countSpan) {
                    const currentCount = parseInt(countSpan.textContent) || 0;
                    countSpan.textContent = wasActive ? currentCount + 1 : currentCount - 1;
                }
            });
        });
    }
    
    const bookmarkBtn = document.querySelector('.tg-bookmark-btn');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function() {
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            
            if (!isLoggedIn) {
                window.location.href = '<?php echo BASE_URL; ?>/login';
                return;
            }
            
            const postId = this.dataset.postId;
            const icon = this.querySelector('svg use');
            const wasActive = this.classList.contains('tg-active');
            
            this.classList.toggle('tg-active');
            
            if (icon) {
                const iconHref = icon.getAttribute('href');
                const newIcon = wasActive ? 'bookmark' : 'bookmark-fill';
                icon.setAttribute('href', iconHref.replace(/bookmark(-fill)?/, newIcon));
            }
            
            fetch(`<?php echo BASE_URL; ?>/post/bookmark/${postId}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(() => {
                this.classList.toggle('tg-active');
            });
        });
    }
});

function scrollToCommentForm() {
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.scrollIntoView({ behavior: 'smooth' });
        setTimeout(() => {
            const textarea = commentForm.querySelector('textarea');
            if (textarea) textarea.focus();
        }, 300);
    }
}
</script>
<?php front_bottom_js(ob_get_clean()); ?>
<?php echo add_frontend_js('/templates/default/front/assets/js/user-action.js'); ?>