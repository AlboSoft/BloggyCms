<?php
/**
 * Template Name: Страница всех категорий
 */

$layout = $layout ?? 'grid';
$show_images = $show_images ?? true;
$show_descriptions = $show_descriptions ?? true;
$show_post_counts = $show_post_counts ?? true;

$totalPosts = isset($total_posts_in_categories)
    ? (int)$total_posts_in_categories
    : array_sum(array_map(function ($category) {
        return (int)($category['posts_count'] ?? 0);
    }, $categories ?? []));

$protectedCount = isset($protected_categories)
    ? (int)$protected_categories
    : count(array_filter($categories ?? [], function ($category) {
        return !empty($category['password_protected']);
    }));

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

$gridClass = 'tg-grid';
if ($layout === 'list') {
    $gridClass .= ' tg-grid--list';
}
?>
<div class="tg-categories-page">
    <div class="tg-container">
        <div class="tg-cat-hero">
            <div class="tg-cat-hero-main">
                <span class="tg-cat-hero-badge">
                    <?php echo bloggy_icon('bs', 'folder-fill', '13', 'currentColor'); ?>
                    <?php echo LANG_TEMPLATE_CATEGORIES_HERO_BADGE; ?>
                </span>
                <h1 class="tg-cat-hero-title">
                    <?php echo LANG_TEMPLATE_CATEGORIES_ALL_TITLE; ?><span>.</span>
                </h1>
                <p class="tg-cat-hero-desc">
                    <?php echo LANG_TEMPLATE_CATEGORIES_DESCRIPTION; ?>
                </p>
            </div>

            <div class="tg-cat-hero-side">
                <div class="tg-cat-hero-stats">
                    <span class="tg-cat-hero-stat">
                        <?php echo bloggy_icon('bs', 'folder', '14', 'currentColor'); ?>
                        <b><?php echo (int)$total_categories; ?></b>
                        <?php echo plural_form((int)$total_categories, [LANG_TEMPLATE_CATEGORIES_SECTION_1, LANG_TEMPLATE_CATEGORIES_SECTION_2, LANG_TEMPLATE_CATEGORIES_SECTION_3]); ?>
                    </span>
                    <span class="tg-cat-hero-stat">
                        <?php echo bloggy_icon('bs', 'file-text', '14', 'currentColor'); ?>
                        <b><?php echo (int)$totalPosts; ?></b>
                        <?php echo plural_form((int)$totalPosts, [LANG_TEMPLATE_CATEGORIES_POST_1, LANG_TEMPLATE_CATEGORIES_POST_2, LANG_TEMPLATE_CATEGORIES_POST_3]); ?>
                    </span>
                    <?php if ($protectedCount > 0) { ?>
                        <span class="tg-cat-hero-stat">
                            <?php echo bloggy_icon('bs', 'lock', '14', 'currentColor'); ?>
                            <b><?php echo (int)$protectedCount; ?></b>
                            <?php echo LANG_TEMPLATE_CATEGORIES_STATS_PROTECTED_LABEL; ?>
                        </span>
                    <?php } ?>
                </div>

                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">
                    <?php echo bloggy_icon('bs', 'grid-3x3-gap', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_CATEGORIES_ALL_POSTS_BTN; ?>
                    <?php echo bloggy_icon('bs', 'arrow-right', '14', 'currentColor', 'tg-ml-1'); ?>
                </a>
            </div>
        </div>

        <?php if (!empty($categories)) { ?>
            <div class="<?php echo $gridClass; ?>">
                <?php foreach ($categories as $index => $category) {
                    [$accent, $soft] = $colorPalette[$index % count($colorPalette)];
                    $postCount = (int)($category['posts_count'] ?? 0);
                    $hasImage = $show_images && !empty($category['image']);
                    ?>
                    <a href="<?php echo BASE_URL; ?>/category/<?php echo html($category['slug']); ?>"
                       class="tg-cat-card"
                       style="--cat-accent: <?php echo $accent; ?>; --cat-soft: <?php echo $soft; ?>; --cat-soft-alt: #ffffff;">
                        <div class="tg-cat-cover">
                            <?php if ($hasImage) { ?>
                                <img src="<?php echo BASE_URL . '/uploads/images/' . html($category['image']); ?>"
                                     alt="<?php echo html($category['name']); ?>"
                                     loading="lazy">
                            <?php } else { ?>
                                <span class="tg-cat-cover-icon">
                                    <?php echo bloggy_icon('bs', 'folder-fill', '28', $accent); ?>
                                </span>
                            <?php } ?>

                            <?php if (!empty($category['password_protected'])) { ?>
                                <span class="tg-cat-cover-lock" title="<?php echo LANG_TEMPLATE_CATEGORIES_PASSWORD_PROTECTED_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'lock-fill', '14', 'currentColor'); ?>
                                </span>
                            <?php } ?>

                            <?php if ($show_post_counts) { ?>
                                <span class="tg-cat-cover-badge">
                                    <?php echo bloggy_icon('bs', 'file-text', '12', 'currentColor'); ?>
                                    <?php echo $postCount; ?>
                                </span>
                            <?php } ?>
                        </div>

                        <div class="tg-cat-body">
                            <h2 class="tg-cat-name"><?php echo html($category['name']); ?></h2>

                            <?php if ($show_descriptions && !empty($category['description'])) { ?>
                                <p class="tg-cat-desc"><?php echo html($category['description']); ?></p>
                            <?php } ?>

                            <div class="tg-cat-footer">
                                <span class="tg-cat-footer-date">
                                    <?php if (!empty($category['created_at'])) { ?>
                                        <?php echo bloggy_icon('bs', 'calendar', '12', 'currentColor'); ?>
                                        <?php echo date('d.m.Y', strtotime($category['created_at'])); ?>
                                    <?php } else { ?>
                                        <?php echo bloggy_icon('bs', 'folder2-open', '12', 'currentColor'); ?>
                                        <?php echo html(LANG_TEMPLATE_CATEGORIES_HERO_BADGE); ?>
                                    <?php } ?>
                                </span>
                                <span class="tg-cat-open">
                                    <?php echo LANG_TEMPLATE_CATEGORIES_OPEN_BTN; ?>
                                    <?php echo bloggy_icon('bs', 'arrow-right', '13', 'currentColor'); ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php } ?>
            </div>

            <?php
            $paginationBase = BASE_URL . '/categories';
            include TEMPLATES_PATH . '/' . (function_exists('get_current_template') ? get_current_template() : DEFAULT_TEMPLATE) . '/front/posts/_pagination.php';
            ?>
        <?php } else { ?>
            <div class="tg-empty-state">
                <div class="tg-empty-state-icon">
                    <?php echo bloggy_icon('bs', 'folder2-open', '48', 'var(--tg-text-secondary)'); ?>
                </div>
                <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_CATEGORIES_EMPTY_TITLE; ?></h3>
                <p class="tg-empty-state-text tg-text-muted">
                    <?php echo LANG_TEMPLATE_CATEGORIES_EMPTY_TEXT; ?>
                </p>
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                    <?php echo bloggy_icon('bs', 'file-text', '16', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_CATEGORIES_EMPTY_POSTS_BTN; ?>
                </a>
            </div>
        <?php } ?>
    </div>
</div>