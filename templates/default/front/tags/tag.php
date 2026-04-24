<?php
/**
 * Template Name: Страница тега
 */

$fieldModel = new FieldModel($db);
$tagModel = new TagModel($db);
$minPostsToShow = SettingsHelper::get('controller_tags', 'min_posts_to_show', 1);
$defaultTagImage = SettingsHelper::get('controller_tags', 'default_tag_image', '');
$tagPrefix = SettingsHelper::get('controller_tags', 'tag_prefix', '#');
?>

<div class="tg-tag-page">
    <div class="tg-container">
        
        <div class="tg-tag-header tg-mb-4">
            <div class="tg-tag-header-left">
                <div class="tg-tag-icon-large">
                    <?php if (!empty($tag['image'])) { ?>
                        <img src="<?php echo BASE_URL . '/uploads/tags/' . html($tag['image']); ?>" 
                             alt="<?php echo html($tag['name']); ?>">
                    <?php } elseif (!empty($defaultTagImage)) { ?>
                        <img src="<?php echo BASE_URL . '/uploads/settings/tags/' . html($defaultTagImage); ?>" 
                             alt="<?php echo html($tag['name']); ?>">
                    <?php } else { ?>
                        <?php echo bloggy_icon('bs', 'tag-fill', '28', 'var(--tg-primary)'); ?>
                    <?php } ?>
                </div>
                <div class="tg-tag-info">
                    <h1 class="tg-tag-title">
                        <?php echo html($tagPrefix); ?><?php echo html($tag['name']); ?>
                    </h1>
                    
                    <?php if (!empty($tag['description'])) { ?>
                    <p class="tg-tag-description tg-text-muted">
                        <?php echo html($tag['description']); ?>
                    </p>
                    <?php } ?>
                    
                    <div class="tg-tag-meta">
                        <span class="tg-meta-item">
                            <?php echo bloggy_icon('bs', 'file-text', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo ($total = (int)($tag['posts_count'] ?? count($posts ?? []))) . ' ' . plural($total, [LANG_TEMPLATE_TAG_POST_1, LANG_TEMPLATE_TAG_POST_2, LANG_TEMPLATE_TAG_POST_3]); ?>
                        </span>
                        
                        <?php if (!empty($tag['created_at'])) { ?>
                        <span class="tg-meta-item">
                            <?php echo bloggy_icon('bs', 'calendar', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo sprintf(LANG_TEMPLATE_TAG_ADDED_AT, date('d.m.Y', strtotime($tag['created_at']))); ?>
                        </span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
            <div class="tg-tag-actions">
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">
                    <?php echo bloggy_icon('bs', 'grid-3x3-gap', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_TAG_ALL_POSTS_BTN; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/tags" class="tg-btn tg-btn-outline tg-btn-sm">
                    <?php echo bloggy_icon('bs', 'tags', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_TAG_ALL_TAGS_BTN; ?>
                </a>
            </div>
        </div>
        
        <div class="tg-navigation-cards tg-mb-4">
            <div class="tg-nav-card">
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-nav-link">
                    <?php echo bloggy_icon('bs', 'grid-3x3-gap', '20', 'var(--tg-primary)'); ?>
                    <span><?php echo LANG_TEMPLATE_TAG_NAV_ALL_POSTS; ?></span>
                </a>
            </div>
            <div class="tg-nav-card">
                <a href="<?php echo BASE_URL; ?>/categories" class="tg-nav-link">
                    <?php echo bloggy_icon('bs', 'folder', '20', 'var(--tg-primary)'); ?>
                    <span><?php echo LANG_TEMPLATE_TAG_NAV_CATEGORIES; ?></span>
                </a>
            </div>
            <div class="tg-nav-card">
                <a href="<?php echo BASE_URL; ?>/tags" class="tg-nav-link">
                    <?php echo bloggy_icon('bs', 'tags', '20', 'var(--tg-primary)'); ?>
                    <span><?php echo LANG_TEMPLATE_TAG_NAV_ALL_TAGS; ?></span>
                </a>
            </div>
        </div>
        
        <div class="tg-tag-posts">
            
            <?php if (!empty($posts)) { ?>
            
            <div class="tg-posts-list">
                <?php foreach ($posts as $post) { 
                    $featuredImage = $post['featured_image'] 
                        ? BASE_URL . '/uploads/images/' . html($post['featured_image']) 
                        : null;
                    $isPasswordProtected = isset($post['password_protected']) && $post['password_protected'] == 1;
                    $postTags = $tagModel->getForPost($post['id']);
                ?>
                <article class="tg-post-card">
                    <?php if ($featuredImage) { ?>
                    <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>" class="tg-post-image-link">
                        <div class="tg-post-image">
                            <img src="<?php echo $featuredImage; ?>" 
                                 alt="<?php echo html($post['title']); ?>"
                                 loading="lazy">
                        </div>
                    </a>
                    <?php } ?>
                    
                    <div class="tg-post-content">
                        <div class="tg-post-meta-top">
                            <?php if (!empty($post['category_name'])) { ?>
                            <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                               class="tg-post-category">
                                <?php echo html($post['category_name']); ?>
                            </a>
                            <?php } ?>
                            
                            <span class="tg-post-date">
                                <?php echo bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo time_ago($post['created_at']); ?>
                            </span>
                        </div>
                        
                        <h2 class="tg-post-title">
                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                <?php echo html($post['title']); ?>
                            </a>
                            <?php if ($isPasswordProtected) { ?>
                                <span class="tg-post-lock" title="<?php echo LANG_TEMPLATE_TAG_PASSWORD_PROTECTED_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'lock-fill', '14', 'currentColor'); ?>
                                </span>
                            <?php } ?>
                        </h2>
                        
                        <?php if (!empty($post['short_description'])) { ?>
                        <p class="tg-post-excerpt">
                            <?php echo html($post['short_description']); ?>
                        </p>
                        <?php } ?>
                        
                        <div class="tg-post-actions">
                            <div class="tg-post-actions-left">
                                <button class="tg-action-btn tg-like-btn <?php echo isset($post['userLiked']) && $post['userLiked'] ? 'tg-active' : ''; ?>" 
                                        data-post-id="<?php echo $post['id']; ?>"
                                        title="<?php echo LANG_TEMPLATE_TAG_LIKE_BTN_TITLE; ?>">
                                    <?php 
                                    $heartIcon = (isset($post['userLiked']) && $post['userLiked']) ? 'heart-fill' : 'heart';
                                    echo bloggy_icon('bs', $heartIcon, '16', 'currentColor', 'tg-mr-1');
                                    ?>
                                    <span class="tg-action-count"><?php echo $post['likes_count'] ?? 0; ?></span>
                                </button>
                                
                                <a href="<?php echo BASE_URL . '/post/' . html($post['slug']) . '#comments'; ?>" 
                                   class="tg-action-btn tg-comments-link"
                                   title="<?php echo LANG_TEMPLATE_TAG_COMMENTS_BTN_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'chat-dots', '16', 'currentColor', 'tg-mr-1'); ?>
                                    <span class="tg-action-count"><?php echo $post['comments_count'] ?? 0; ?></span>
                                </a>
                                
                                <button class="tg-action-btn tg-bookmark-btn <?php echo isset($post['userBookmarked']) && $post['userBookmarked'] ? 'tg-active' : ''; ?>" 
                                        data-post-id="<?php echo $post['id']; ?>"
                                        title="<?php echo LANG_TEMPLATE_TAG_BOOKMARK_BTN_TITLE; ?>">
                                    <?php 
                                    $bookmarkIcon = (isset($post['userBookmarked']) && $post['userBookmarked']) ? 'bookmark-fill' : 'bookmark';
                                    echo bloggy_icon('bs', $bookmarkIcon, '16', 'currentColor');
                                    ?>
                                </button>
                            </div>
                            
                            <div class="tg-post-views">
                                <?php echo bloggy_icon('bs', 'eye', '14', 'currentColor', 'tg-mr-1'); ?>
                                <span><?php echo $post['views'] ?? 0; ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($postTags)) { ?>
                        <div class="tg-post-tags tg-mt-3">
                            <?php foreach ($postTags as $postTag) { 
                                $isCurrentTag = $postTag['slug'] === $tag['slug'];
                            ?>
                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($postTag['slug']); ?>" 
                               class="tg-tag <?php echo $isCurrentTag ? 'tg-tag-current' : ''; ?>">
                                <?php echo html($tagPrefix); ?><?php echo html($postTag['name']); ?>
                            </a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </article>
                <?php } ?>
            </div>
            <?php if (!empty($pagination) && $pagination['has_more']) { ?>
            <div class="tg-load-more tg-mt-5 tg-text-center">
                <a href="<?php echo $pagination['next_url']; ?>" class="tg-btn tg-btn-outline">
                    <?php echo bloggy_icon('bs', 'arrow-down', '16', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_TAG_SHOW_MORE_BTN; ?>
                </a>
            </div>
            <?php } ?>
            
            <?php } else { ?>
            
            <div class="tg-empty-state">
                <div class="tg-empty-state-icon">
                    <?php echo bloggy_icon('bs', 'tag', '48', 'var(--tg-text-secondary)'); ?>
                </div>
                <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_TAG_NO_POSTS_TITLE; ?></h3>
                <p class="tg-empty-state-text tg-text-muted">
                    <?php echo LANG_TEMPLATE_TAG_NO_POSTS_TEXT; ?>
                </p>
                <div class="tg-empty-actions">
                    <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                        <?php echo bloggy_icon('bs', 'arrow-left', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_TAG_ALL_POSTS_BTN; ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/tags" class="tg-btn tg-btn-outline tg-ml-2">
                        <?php echo bloggy_icon('bs', 'tags', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_TAG_ALL_TAGS_BTN; ?>
                    </a>
                </div>
            </div>
            
            <?php } ?>
            
        </div>
    </div>
</div> 

<?php 
ob_start();
?>
<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<?php front_bottom_js(ob_get_clean()); ?>
<?php echo add_frontend_js('/templates/default/front/assets/js/user-action.js'); ?>