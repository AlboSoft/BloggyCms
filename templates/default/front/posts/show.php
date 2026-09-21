<?php
/**
 * Template Name: Страница поста - Habr Pro Style
 * Чистая статья без скруглений, максимум читаемости
 */
$isPasswordProtected = $post['password_protected'] == 1; 
$allowComments = isset($post['allow_comments']) ? $post['allow_comments'] == 1 : true;
$templatePath = TEMPLATES_PATH . '/' . DEFAULT_TEMPLATE;
$totalComments = $totalComments ?? ($post['comments_count'] ?? 0);
?>
<div class="tg-post-page">
    <div class="tg-container">
        <div class="tg-two-columns">
            <div class="tg-page-main">
                <div class="tg-post-header">
                    <div class="tg-post-meta-top">
                        <?php if (!empty($post['category_name'])) { ?>
                        <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" class="tg-post-category">
                            <?php echo html($post['category_name']); ?>
                        </a>
                        <?php } ?>
                        <span class="tg-post-date"><?php echo date('d.m.Y в H:i', strtotime($post['created_at'])); ?></span>
                        <?php if ($isPasswordProtected) { ?>
                            <span class="tg-post-protected">🔒 закрыто</span>
                        <?php } ?>
                    </div>
                    
                    <h1 class="tg-post-title">
                        <?php echo html($post['title']); ?>
                        <?php if (isset($post['is_adult']) && $post['is_adult']) { ?>
                            <span class="tg-adult-badge">18+</span>
                        <?php } ?>
                    </h1>
                    
                    <div class="tg-post-author">
                        <div class="tg-author-avatar">
                            <?php if (!empty($post['author_avatar']) && $post['author_avatar'] !== 'default.jpg') { ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($post['author_avatar']); ?>" alt="<?php echo html($post['author_name'] ?? 'автор'); ?>">
                            <?php } else { ?>
                                <div class="tg-avatar-placeholder-small"><?php echo strtoupper(substr(($post['author_name'] ?? 'A'), 0, 1)); ?></div>
                            <?php } ?>
                        </div>
                        <div class="tg-author-info">
                            <span class="tg-author-name"><?php echo html($post['author_name'] ?? 'автор'); ?></span>
                            <span style="color:#8a8a8a;font-size:11px;margin-left:8px"><?php echo html($post['author_username'] ?? ''); ?></span>
                        </div>
                    </div>
                    
                    <div class="tg-post-actions tg-mt-3">
                        <button class="tg-action-btn tg-like-btn <?php echo isset($userLiked) && $userLiked ? 'tg-active' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                            ♥ <span class="tg-action-count"><?php echo $post['likes_count'] ?? 0; ?></span>
                        </button>
                        <button class="tg-action-btn tg-bookmark-btn <?php echo isset($userBookmarked) && $userBookmarked ? 'tg-active' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                            ★ закладки
                        </button>
                        <a href="#comments" class="tg-action-btn">💬 <?php echo $post['comments_count'] ?? 0; ?></a>
                        <div class="tg-post-views tg-ml-auto">👁 <?php echo $post['views'] ?? 0; ?></div>
                    </div>
                </div>
                
                <?php if ($post['featured_image'] && (!isset($post['show_cover_in_post']) || (int)$post['show_cover_in_post'] === 1)) { ?>
                    <div class="tg-post-image-full">
                        <img src="<?php echo BASE_URL; ?>/uploads/images/<?php echo html($post['featured_image']); ?>" alt="<?php echo html($post['title']); ?>" loading="lazy">
                    </div>
                <?php } ?>
                
                <div class="tg-post-content">
                    <?php if (!empty($blocks)) { ?>
                        <?php foreach ($blocks as $block) { ?>
                            <div class="tg-post-block tg-post-block-<?php echo $block['type']; ?>">
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
                        if (!empty($value)) { $hasValues = true; break; }
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
                            <span class="tg-custom-field-value"><?php echo $fieldModel->renderFieldDisplay($field, $value, 'post', $post['id']); ?></span>
                        </div>
                        <?php } } ?>
                    </div>
                <?php } ?>
                <?php } ?>
                
                <?php if (!empty($post['tags'])) { ?>
                <div class="tg-post-tags">
                    <?php foreach ($post['tags'] as $tag) { ?>
                    <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" class="tg-tag"><?php echo html($tag['name']); ?></a>
                    <?php } ?>
                </div>
                <?php } ?>
                
                <?php if (!$isPasswordProtected && $allowComments) { ?>
                <section id="comments" class="tg-comments-section">
                    <div class="tg-comments-header">
                        <h3 class="tg-comments-title">комментарии</h3>
                        <span class="tg-comments-count"><?php echo $totalComments; ?></span>
                        <button class="tg-btn tg-btn-sm tg-btn-outline tg-ml-auto" onclick="document.getElementById('comment-form')?.scrollIntoView({behavior:'smooth'})">
                            написать
                        </button>
                    </div>
                    <?php include $templatePath . '/front/comments/list.php'; ?>
                    <?php include $templatePath . '/front/comments/form.php'; ?>
                </section>
                <?php } elseif ($isPasswordProtected) { ?>
                <div class="tg-comments-disabled">
                    <div class="tg-alert tg-alert-info">
                        <div class="tg-alert-content"><strong>комментарии закрыты</strong><p class="tg-mb-0">публикация защищена паролем</p></div>
                    </div>
                </div>
                <?php } elseif (!$allowComments) { ?>
                <div class="tg-comments-disabled">
                    <div class="tg-alert tg-alert-info">
                        <div class="tg-alert-content"><strong>комментарии отключены</strong><p class="tg-mb-0">автор закрыл обсуждение</p></div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="tg-page-sidebar">
                <div class="tg-sidebar-card">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;color:#000">о публикации</div>
                    <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;color:#6c6c6c">
                        <div>просмотров: <strong style="color:#000"><?php echo $post['views'] ?? 0; ?></strong></div>
                        <div>лайков: <strong style="color:#000"><?php echo $post['likes_count'] ?? 0; ?></strong></div>
                        <div>комментариев: <strong style="color:#000"><?php echo $post['comments_count'] ?? 0; ?></strong></div>
                        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #e8e8e8">
                            <div style="font-size:11px;color:#8a8a8a">опубликовано</div>
                            <div style="font-weight:600;color:#000"><?php echo date('d.m.Y', strtotime($post['created_at'])); ?></div>
                        </div>
                    </div>
                </div>
                <?php if (!empty($post['tags'])) { ?>
                <div class="tg-sidebar-card">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;color:#000">теги</div>
                    <div style="display:flex;flex-wrap:wrap;gap:4px">
                        <?php foreach ($post['tags'] as $tag) { ?>
                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" class="tg-tag" style="border:1px solid #e8e8e8;padding:2px 6px;background:#fff"><?php echo html($tag['name']); ?></a>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeBtn = document.querySelector('.tg-like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            if (!isLoggedIn) { window.location.href = '<?php echo BASE_URL; ?>/login'; return; }
            const postId = this.dataset.postId;
            const countSpan = this.querySelector('.tg-action-count');
            const wasActive = this.classList.contains('tg-active');
            this.classList.toggle('tg-active');
            if (countSpan) {
                const currentCount = parseInt(countSpan.textContent) || 0;
                countSpan.textContent = wasActive ? currentCount - 1 : currentCount + 1;
            }
            fetch(`<?php echo BASE_URL; ?>/post/like/${postId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(() => {
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
            if (!isLoggedIn) { window.location.href = '<?php echo BASE_URL; ?>/login'; return; }
            const postId = this.dataset.postId;
            this.classList.toggle('tg-active');
            fetch(`<?php echo BASE_URL; ?>/post/bookmark/${postId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(() => this.classList.toggle('tg-active'));
        });
    }
});
</script>
<?php front_bottom_js(ob_get_clean()); ?>
<?php echo add_frontend_js('/templates/default/front/assets/js/user-action.js'); ?>
