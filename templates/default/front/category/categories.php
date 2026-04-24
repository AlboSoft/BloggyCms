<?php
/**
 * Template Name: Страница всех категорий
 */
?>
<div class="tg-categories-page">
    <div class="tg-container">
        <div class="tg-categories-header tg-mb-4">
            <div class="tg-categories-header-left">
                <div class="tg-categories-icon">
                    <?php echo bloggy_icon('bs', 'folder', '24', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-categories-info">
                    <h1 class="tg-categories-title"><?php echo LANG_TEMPLATE_CATEGORIES_ALL_TITLE; ?></h1>
                    <p class="tg-categories-subtitle tg-text-muted">
                        <?php echo $total_categories; ?> <?php echo plural_form($total_categories, [LANG_TEMPLATE_CATEGORIES_SECTION_1, LANG_TEMPLATE_CATEGORIES_SECTION_2, LANG_TEMPLATE_CATEGORIES_SECTION_3]); ?>
                    </p>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">
                <?php echo bloggy_icon('bs', 'grid-3x3-gap', '14', 'currentColor', 'tg-mr-1'); ?>
                <?php echo LANG_TEMPLATE_CATEGORIES_ALL_POSTS_BTN; ?>
            </a>
        </div>
        
        <?php if (!empty($categories)) { ?>
        <div class="tg-categories-grid tg-layout-<?php echo $layout; ?>">
            <?php foreach ($categories as $index => $category) { 
                $colorPalette = [
                    '#c11c3b', '#0a11a8', '#002306', '#c76234',
                    '#8a2be2', '#20c997', '#fd7e14', '#6f42c1',
                    '#139090', '#d59801', '#296e4a', '#2b5278'
                ];
                $icons = [
                    'folder', 'folder2', 'folder-check', 'folder-fill', 'folder2-open'
                ];
                $color = $colorPalette[$index % count($colorPalette)];
                $icon = $icons[$index % count($icons)];
                $postCount = $category['posts_count'] ?? 0;
            ?>
            <a href="<?php echo BASE_URL; ?>/category/<?php echo html($category['slug']); ?>" class="tg-category-card tg-layout-<?php echo $layout; ?>">
                <div class="tg-category-card-inner">
                    <div class="tg-category-icon-wrapper" style="background-color: <?php echo $color; ?>20;">
                        <?php if ($show_images && !empty($category['image'])) { ?>
                            <img src="<?php echo BASE_URL . '/uploads/images/' . html($category['image']); ?>"
                                 alt="<?php echo html($category['name']); ?>"
                                 class="tg-category-image">
                        <?php } else { ?>
                            <?php echo bloggy_icon('bs', $icon, '24', $color); ?>
                        <?php } ?>
                    </div>
                    <div class="tg-category-content">
                        <h3 class="tg-category-name">
                            <?php echo html($category['name']); ?>
                            <?php if ($category['password_protected'] == 1) { ?>
                                <span class="tg-category-protected" title="<?php echo LANG_TEMPLATE_CATEGORIES_PASSWORD_PROTECTED_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'lock-fill', '12', 'currentColor'); ?>
                                </span>
                            <?php } ?>
                        </h3>
                        <?php if ($show_descriptions && !empty($category['description'])) { ?>
                            <p class="tg-category-description">
                                <?php echo html(mb_substr($category['description'], 0, 80)); ?>
                                <?php if (mb_strlen($category['description']) > 80) { ?>...<?php } ?>
                            </p>
                        <?php } ?>
                        <div class="tg-category-meta">
                            <?php if ($show_post_counts) { ?>
                            <span class="tg-category-posts">
                                <?php echo bloggy_icon('bs', 'file-text', '12', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo $postCount; ?> <?php echo plural_form($postCount, [LANG_TEMPLATE_CATEGORIES_POST_1, LANG_TEMPLATE_CATEGORIES_POST_2, LANG_TEMPLATE_CATEGORIES_POST_3]); ?>
                            </span>
                            <?php } ?>
                            <?php if (!empty($category['created_at'])) { ?>
                            <span class="tg-category-date">
                                • <?php echo date('d.m.Y', strtotime($category['created_at'])); ?>
                            </span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="tg-category-arrow">
                        <?php echo bloggy_icon('bs', 'chevron-right', '18', 'var(--tg-text-secondary)'); ?>
                    </div>
                </div>
            </a>
            <?php } ?>
        </div>
        
        <div class="tg-categories-stats tg-mt-5">
            <div class="tg-stats-grid">
                <div class="tg-stat-card">
                    <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                        <?php echo bloggy_icon('bs', 'folder', '20', 'var(--tg-primary)'); ?>
                    </div>
                    <div class="tg-stat-content">
                        <span class="tg-stat-label"><?php echo LANG_TEMPLATE_CATEGORIES_STATS_TOTAL_LABEL; ?></span>
                        <span class="tg-stat-value"><?php echo $total_categories; ?></span>
                    </div>
                </div>
                <?php
                $totalPosts = array_sum(array_column($categories, 'posts_count'));
                $protectedCount = count(array_filter($categories, function($cat) {
                    return $cat['password_protected'] == 1;
                }));
                ?>
                <div class="tg-stat-card">
                    <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                        <?php echo bloggy_icon('bs', 'file-text', '20', 'var(--tg-primary)'); ?>
                    </div>
                    <div class="tg-stat-content">
                        <span class="tg-stat-label"><?php echo LANG_TEMPLATE_CATEGORIES_STATS_POSTS_LABEL; ?></span>
                        <span class="tg-stat-value"><?php echo $totalPosts; ?></span>
                    </div>
                </div>
                <div class="tg-stat-card">
                    <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                        <?php echo bloggy_icon('bs', 'lock', '20', 'var(--tg-primary)'); ?>
                    </div>
                    <div class="tg-stat-content">
                        <span class="tg-stat-label"><?php echo LANG_TEMPLATE_CATEGORIES_STATS_PROTECTED_LABEL; ?></span>
                        <span class="tg-stat-value"><?php echo $protectedCount; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($pagination['has_more']) { ?>
        <div class="tg-pagination tg-mt-5 tg-text-center">
            <a href="<?php echo $pagination['next_url']; ?>" class="tg-btn tg-btn-outline">
                <?php echo bloggy_icon('bs', 'arrow-down', '16', 'currentColor', 'tg-mr-1'); ?>
                <?php echo LANG_TEMPLATE_CATEGORIES_SHOW_MORE_BTN; ?>
            </a>
        </div>
        <?php } ?>
        
        <?php } else { ?>
        <div class="tg-empty-state">
            <div class="tg-empty-state-icon">
                <?php echo bloggy_icon('bs', 'folders', '48', 'var(--tg-text-secondary)'); ?>
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