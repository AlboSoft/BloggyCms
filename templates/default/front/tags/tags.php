<?php
/**
* Template Name: Страница всех тегов
*/

$templatePath = TEMPLATES_PATH . '/' . (function_exists('get_current_template') ? get_current_template() : DEFAULT_TEMPLATE);

$defaultTagImage = SettingsHelper::get('controller_tags', 'default_tag_image', '');
$tagPrefix = SettingsHelper::get('controller_tags', 'tag_prefix', '#');

$colorPalette = [
    ['#4f46e5', '#eef2ff'],
    ['#0ea5e9', '#e0f2fe'],
    ['#10b981', '#dcfce7'],
    ['#f59e0b', '#fef3c7'],
    ['#ec4899', '#fce7f3'],
    ['#8b5cf6', '#f3e8ff'],
    ['#14b8a6', '#ccfbf1'],
    ['#f97316', '#ffedd5'],
];

$totalTags = (int)($total_tags ?? $pagination['total_items'] ?? count($tags ?? []));
$totalPostsInTags = isset($total_posts_in_tags) ? (int)$total_posts_in_tags : 0;
$mostPopularTag = $popular_tag ?? null;

// Если контроллер не передал данные — считаем по текущей странице
if ($mostPopularTag === null) {
    $maxPostsCount = 0;
    foreach ($tags ?? [] as $tag) {
        if (!isset($total_posts_in_tags)) {
            $totalPostsInTags += (int)($tag['posts_count'] ?? 0);
        }
        $tagPosts = (int)($tag['posts_count'] ?? 0);
        if ($tagPosts > $maxPostsCount) {
            $maxPostsCount = $tagPosts;
            $mostPopularTag = $tag;
        }
    }
}
?>
<div class="tg-tags-page">
    <div class="tg-container">

        <div class="tg-tags-hero">
            <h1 class="tg-tags-hero-title">
                <?php echo LANG_TEMPLATE_TAGS_ALL_TITLE; ?><span>.</span>
            </h1>
            <p class="tg-tags-hero-desc">
                <?php echo LANG_TEMPLATE_TAGS_ALL_DESCRIPTION; ?>
            </p>

            <div class="tg-tags-hero-stats">
                <span class="tg-tags-stat">
                    <?php echo bloggy_icon('bs', 'tag-fill', '14', 'currentColor'); ?>
                    <b><?php echo $totalTags; ?></b>
                    <?php echo LANG_TEMPLATE_TAGS_STATS_TOTAL_LABEL; ?>
                </span>
                <span class="tg-tags-stat">
                    <?php echo bloggy_icon('bs', 'newspaper', '14', 'currentColor'); ?>
                    <b><?php echo $totalPostsInTags; ?></b>
                    <?php echo LANG_TEMPLATE_TAGS_STATS_POSTS_LABEL; ?>
                </span>
                <?php if (!empty($mostPopularTag)) { ?>
                    <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($mostPopularTag['slug']); ?>" class="tg-tags-stat">
                        <?php echo bloggy_icon('bs', 'fire', '14', '#dc3545'); ?>
                        <?php echo LANG_TEMPLATE_TAGS_STATS_POPULAR_LABEL; ?>:
                        <b><?php echo html($tagPrefix); ?><?php echo html($mostPopularTag['name']); ?></b>
                    </a>
                <?php } ?>
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-tags-stat">
                    <?php echo bloggy_icon('bs', 'grid-3x3-gap', '14', 'currentColor'); ?>
                    <?php echo LANG_TEMPLATE_TAGS_STATS_ALL_POSTS_LABEL; ?>
                    <?php echo bloggy_icon('bs', 'arrow-right', '12', 'currentColor'); ?>
                </a>
            </div>
        </div>

        <?php if (!empty($tags)) { ?>
            <div class="tg-tags-cloud">
                <?php foreach ($tags as $index => $tag) {
                    [$accent, $soft] = $colorPalette[$index % count($colorPalette)];
                    $tagImage = '';
                    if (!empty($tag['image'])) {
                        $tagImage = BASE_URL . '/uploads/tags/' . html($tag['image']);
                    } elseif (!empty($defaultTagImage)) {
                        $tagImage = BASE_URL . '/uploads/settings/tags/' . html($defaultTagImage);
                    }

                    $tagPosts = (int)($tag['posts_count'] ?? 0);
                    ?>
                    <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>"
                       class="tg-tagcloud-item"
                       style="--tag-soft: <?php echo $soft; ?>;"
                       title="<?php echo html($tag['name']); ?><?php echo !empty($tag['description']) ? ' — ' . html($tag['description']) : ''; ?>">
                        <span class="tg-tagcloud-icon">
                            <?php if ($tagImage) { ?>
                                <img src="<?php echo $tagImage; ?>" alt="<?php echo html($tag['name']); ?>" loading="lazy">
                            <?php } else { ?>
                                <?php echo bloggy_icon('bs', 'tag-fill', '20', $accent); ?>
                            <?php } ?>
                        </span>

                        <span class="tg-tagcloud-body">
                            <span class="tg-tagcloud-name"><?php echo html($tagPrefix); ?><?php echo html($tag['name']); ?></span>
                            <span class="tg-tagcloud-meta">
                                <?php echo bloggy_icon('bs', 'file-text', '12', 'currentColor'); ?>
                                <?php echo $tagPosts; ?>
                                <?php echo plural_form($tagPosts, [LANG_TEMPLATE_TAGS_POST_1, LANG_TEMPLATE_TAGS_POST_2, LANG_TEMPLATE_TAGS_POST_3]); ?>
                                <?php if (!empty($tag['created_at'])) { ?>
                                    <span class="tg-tagcloud-dot"></span>
                                    <?php echo date('d.m.Y', strtotime($tag['created_at'])); ?>
                                <?php } ?>
                            </span>
                        </span>

                        <span class="tg-tagcloud-arrow">
                            <?php echo bloggy_icon('bs', 'arrow-right', '16', 'currentColor'); ?>
                        </span>
                    </a>
                <?php } ?>
            </div>

            <?php
            $paginationBase = BASE_URL . '/tags';
            include $templatePath . '/front/posts/_pagination.php';
            ?>
        <?php } else { ?>
            <div class="tg-empty-state">
                <div class="tg-empty-state-icon">
                    <?php echo bloggy_icon('bs', 'tags', '48', '#9ca3af'); ?>
                </div>
                <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_TAGS_EMPTY_TITLE; ?></h3>
                <p class="tg-empty-state-text tg-text-muted">
                    <?php echo LANG_TEMPLATE_TAGS_EMPTY_TEXT; ?>
                </p>
                <div class="tg-empty-actions">
                    <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                        <?php echo bloggy_icon('bs', 'newspaper', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_TAGS_ALL_POSTS_BTN; ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline tg-ml-2">
                        <?php echo bloggy_icon('bs', 'folder', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_TAGS_CATEGORIES_BTN; ?>
                    </a>
                </div>
            </div>
        <?php } ?>

    </div>
</div>