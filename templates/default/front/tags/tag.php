<?php
/**
 * Template Name: Страница тега - Habr Pro
 */
$fieldModel = new FieldModel($db);
$tagModel = new TagModel($db);
$tagPrefix = SettingsHelper::get('controller_tags', 'tag_prefix', '#');
?>
<div class="tg-tag-page">
    <div class="tg-container">
        <div class="tg-tag-header">
            <div class="tg-tag-header-left">
                <div class="tg-tag-icon-large"><span style="font-weight:800"><?php echo html($tagPrefix); ?></span></div>
                <div class="tg-tag-info">
                    <h1 class="tg-tag-title"><?php echo html($tagPrefix); ?><?php echo html($tag['name']); ?></h1>
                    <?php if (!empty($tag['description'])) { ?><p class="tg-tag-description"><?php echo html($tag['description']); ?></p><?php } ?>
                    <div class="tg-tag-meta"><span class="tg-meta-item"><?php echo (int)($tag['posts_count'] ?? count($posts ?? [])); ?> публикаций</span></div>
                </div>
            </div>
            <div class="tg-tag-actions">
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">все посты</a>
                <a href="<?php echo BASE_URL; ?>/tags" class="tg-btn tg-btn-outline tg-btn-sm">все теги</a>
            </div>
        </div>
        
        <div class="tg-tag-posts">
            <?php if (!empty($posts)) { ?>
            <div class="tg-posts-list">
                <?php foreach ($posts as $post) { 
                    $showCoverInList = !isset($post['show_cover_in_list']) || (int)$post['show_cover_in_list'] === 1;
                    $featuredImage = ($post['featured_image'] && $showCoverInList) ? BASE_URL . '/uploads/images/' . html($post['featured_image']) : null;
                    $isPasswordProtected = isset($post['password_protected']) && $post['password_protected'] == 1;
                    $postTags = $tagModel->getForPost($post['id']);
                ?>
                <article class="tg-post-card">
                    <div class="tg-post-meta-top"><span class="tg-post-category"><?php echo html($tagPrefix); ?><?php echo html($tag['name']); ?></span><span class="tg-post-date"><?php echo time_ago($post['created_at']); ?></span></div>
                    <div class="tg-post-title-row">
                        <?php if ($featuredImage) { ?><div class="tg-post-thumb"><a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>"><img src="<?php echo $featuredImage; ?>" alt="<?php echo html($post['title']); ?>" loading="lazy"></a></div><?php } ?>
                        <h2 class="tg-post-title"><a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>"><?php echo html($post['title']); ?></a><?php if ($isPasswordProtected) { ?><span class="tg-post-lock">🔒</span><?php } ?></h2>
                    </div>
                    <?php if (!empty($post['short_description'])) { ?><p class="tg-post-excerpt"><?php echo html($post['short_description']); ?></p><?php } ?>
                    <div class="tg-post-actions">
                        <div class="tg-post-actions-left">
                            <button class="tg-action-btn tg-like-btn <?php echo isset($post['userLiked']) && $post['userLiked'] ? 'tg-active' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">♥ <?php echo $post['likes_count'] ?? 0; ?></button>
                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']) . '#comments'; ?>" class="tg-action-btn">💬 <?php echo $post['comments_count'] ?? 0; ?></a>
                            <button class="tg-action-btn tg-bookmark-btn <?php echo isset($post['userBookmarked']) && $post['userBookmarked'] ? 'tg-active' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">★</button>
                        </div>
                        <div class="tg-post-views">👁 <?php echo $post['views'] ?? 0; ?></div>
                    </div>
                    <?php if (!empty($postTags)) { ?><div class="tg-post-tags"><?php foreach ($postTags as $t) { ?><a href="<?php echo BASE_URL; ?>/tag/<?php echo html($t['slug']); ?>" class="tg-tag"><?php echo html($t['name']); ?></a><?php } ?></div><?php } ?>
                </article>
                <?php } ?>
            </div>
            <?php if (!empty($pagination) && $pagination['has_more']) { ?><div class="tg-load-more"><a href="<?php echo $pagination['next_url']; ?>" class="tg-btn tg-btn-outline">показать еще</a></div><?php } ?>
            <?php } else { ?>
                <div class="tg-empty-state"><h3 class="tg-empty-state-title">пусто</h3><p class="tg-empty-state-text">по этому тегу пока нет публикаций</p></div>
            <?php } ?>
        </div>
    </div>
</div>
<?php ob_start(); ?><script>window.baseUrl='<?= BASE_URL; ?>';window.userLoggedIn=<?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script><?php front_bottom_js(ob_get_clean()); ?>
<?php echo add_frontend_js('/templates/default/front/assets/js/user-action.js'); ?>
