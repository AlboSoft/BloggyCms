<?php
/**
* Template Name: Страница тега
*/

$templatePath = TEMPLATES_PATH . '/' . (function_exists('get_current_template') ? get_current_template() : DEFAULT_TEMPLATE);

$minPostsToShow = SettingsHelper::get('controller_tags', 'min_posts_to_show', 1);
$defaultTagImage = SettingsHelper::get('controller_tags', 'default_tag_image', '');
$tagPrefix = SettingsHelper::get('controller_tags', 'tag_prefix', '#');

$pagination = [
    'current_page' => (int)($current_page ?? 1),
    'total_pages' => (int)($total_pages ?? 0),
];
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
                            <?php echo ($total = (int)($total_posts ?? count($posts ?? []))) . ' ' . plural($total, [LANG_TEMPLATE_TAG_POST_1, LANG_TEMPLATE_TAG_POST_2, LANG_TEMPLATE_TAG_POST_3]); ?>
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

        <?php if (!empty($posts)) { ?>
            <div class="tg-posts-card">
                <div class="tg-posts-list"
                     data-base-url="<?php echo BASE_URL; ?>"
                     data-logged-in="<?php echo isset($_SESSION['user_id']) ? '1' : '0'; ?>">
                    <?php foreach ($posts as $post) {
                        include $templatePath . '/front/posts/_card.php';
                    } ?>
                </div>

                <?php
                $paginationBase = BASE_URL . '/tag/' . html($tag['slug']);
                include $templatePath . '/front/posts/_pagination.php';
                ?>
            </div>
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

<?php
ob_start();
?>
<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<?php
front_bottom_js(ob_get_clean());
echo add_frontend_js('/templates/default/front/assets/js/post-list.js');
?>