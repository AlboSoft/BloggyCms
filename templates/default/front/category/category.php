<?php
/**
 * Template Name: Страница категории - Habr Pro
 */
$fieldModel = new FieldModel($this->db);
$customFields = $fieldModel->getActiveByEntityType('post');
?>
<div class="tg-category-page">
    <div class="tg-container">
        <div class="tg-category-header">
            <div class="tg-category-header-left">
                <div class="tg-category-icon-large">
                    <?php if (!empty($category['image'])) { ?>
                        <img src="<?php echo BASE_URL . '/uploads/images/' . html($category['image']); ?>" alt="<?php echo html($category['name']); ?>">
                    <?php } else { ?>
                        <span style="font-weight:800;font-size:16px"><?php echo strtoupper(mb_substr($category['name'],0,2)); ?></span>
                    <?php } ?>
                </div>
                <div class="tg-category-info">
                    <h1 class="tg-category-title"><?php echo html($category['name']); ?><?php if ($category['password_protected']) { ?><span class="tg-category-protected">🔒</span><?php } ?></h1>
                    <?php if (!empty($category['description'])) { ?><p class="tg-category-description"><?php echo html($category['description']); ?></p><?php } ?>
                    <div class="tg-category-meta">
                        <span class="tg-meta-item"><?php echo (int)($total_posts ?? count($posts ?? [])); ?> публикаций</span>
                        <?php if (!empty($category['created_at'])) { ?><span class="tg-meta-item">с <?php echo date('d.m.Y', strtotime($category['created_at'])); ?></span><?php } ?>
                    </div>
                </div>
            </div>
            <div class="tg-category-actions">
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">все посты</a>
                <a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline tg-btn-sm">разделы</a>
            </div>
        </div>
        
        <?php if ($category['password_protected'] && !$hasAccess) { ?>
            <div class="tg-password-card">
                <h2 class="tg-password-title">закрытый раздел</h2>
                <p class="tg-password-text">введите пароль для доступа</p>
                <form id="tg-category-password-form" class="tg-password-form">
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                    <div class="tg-field"><input type="password" name="password" class="tg-input" placeholder="пароль" required></div>
                    <div class="tg-password-actions"><button type="submit" class="tg-btn tg-btn-primary tg-btn-block" id="tg-submit-password">открыть</button></div>
                    <div id="tg-password-error" class="tg-alert tg-alert-error" style="display:none;margin-top:12px"></div>
                </form>
            </div>
        <?php } else { ?>
            <div class="tg-category-posts">
                <?php if (!empty($posts)) { ?>
                    <div class="tg-posts-list">
                        <?php foreach ($posts as $post) {
                            $showCoverInList = !isset($post['show_cover_in_list']) || (int)$post['show_cover_in_list'] === 1;
                            $featuredImage = ($post['featured_image'] && $showCoverInList) ? BASE_URL . '/uploads/images/' . html($post['featured_image']) : null;
                            $isPasswordProtected = isset($post['password_protected']) && $post['password_protected'] == 1;
                            $tagModel = new TagModel($db);
                            $postTags = $tagModel->getForPost($post['id']);
                        ?>
                        <article class="tg-post-card">
                            <div class="tg-post-meta-top">
                                <a href="<?php echo BASE_URL; ?>/category/<?php echo html($category['slug']); ?>" class="tg-post-category"><?php echo html($category['name']); ?></a>
                                <span class="tg-post-date"><?php echo time_ago($post['created_at']); ?></span>
                            </div>
                            <div class="tg-post-title-row">
                                <?php if ($featuredImage) { ?><div class="tg-post-thumb"><a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>"><img src="<?php echo $featuredImage; ?>" alt="<?php echo html($post['title']); ?>" loading="lazy"></a></div><?php } ?>
                                <h2 class="tg-post-title"><a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>"><?php echo html($post['title']); ?></a><?php if ($isPasswordProtected) { ?><span class="tg-post-lock">🔒</span><?php } ?></h2>
                            </div>
                            <?php if (!empty($post['short_description'])) { ?><p class="tg-post-excerpt"><?php echo html($post['short_description']); ?></p><?php } ?>
                            <div class="tg-post-actions">
                                <div class="tg-post-actions-left">
                                    <button class="tg-action-btn tg-like-btn <?php echo isset($post['userLiked']) && $post['userLiked'] ? 'tg-active' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">♥ <span class="tg-action-count"><?php echo $post['likes_count'] ?? 0; ?></span></button>
                                    <a href="<?php echo BASE_URL . '/post/' . html($post['slug']) . '#comments'; ?>" class="tg-action-btn">💬 <?php echo $post['comments_count'] ?? 0; ?></a>
                                    <button class="tg-action-btn tg-bookmark-btn <?php echo isset($post['userBookmarked']) && $post['userBookmarked'] ? 'tg-active' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">★</button>
                                </div>
                                <div class="tg-post-views">👁 <?php echo $post['views'] ?? 0; ?></div>
                            </div>
                            <?php if (!empty($postTags)) { ?><div class="tg-post-tags"><?php foreach ($postTags as $tag) { ?><a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" class="tg-tag"><?php echo html($tag['name']); ?></a><?php } ?></div><?php } ?>
                        </article>
                        <?php } ?>
                    </div>
                    <?php if (!empty($pagination) && $pagination['has_more']) { ?><div class="tg-load-more"><a href="<?php echo $pagination['next_url']; ?>" class="tg-btn tg-btn-outline">показать еще</a></div><?php } ?>
                <?php } else { ?>
                    <div class="tg-empty-state"><h3 class="tg-empty-state-title">пусто</h3><p class="tg-empty-state-text">в этом разделе пока нет публикаций</p><div class="tg-empty-actions"><a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">все публикации</a><a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline">разделы</a></div></div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<?php if ($category['password_protected'] && !$hasAccess) { ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tg-category-password-form');
    const errorDiv = document.getElementById('tg-password-error');
    const submitBtn = document.getElementById('tg-submit-password');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'проверка...'; submitBtn.disabled = true; errorDiv.style.display = 'none';
            fetch('/category/check-password/<?php echo $category['id']; ?>', { method: 'POST', body: formData })
            .then(r => r.json()).then(data => {
                if (data.success) { submitBtn.innerHTML = 'успешно'; setTimeout(() => location.reload(), 800); }
                else {
                    submitBtn.innerHTML = originalText; submitBtn.disabled = false;
                    errorDiv.textContent = data.message || 'неверный пароль'; errorDiv.style.display = 'block';
                    form.querySelector('input[name="password"]').value = '';
                }
            }).catch(() => {
                submitBtn.innerHTML = originalText; submitBtn.disabled = false;
                errorDiv.textContent = 'ошибка проверки'; errorDiv.style.display = 'block';
            });
        });
    }
});
</script>
<?php } ?>
<?php ob_start(); ?><script>window.baseUrl='<?= BASE_URL; ?>';window.userLoggedIn=<?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script><?php front_bottom_js(ob_get_clean()); ?>
<?php echo add_frontend_js('/templates/default/front/assets/js/user-action.js'); ?>
