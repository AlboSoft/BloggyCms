<?php
/**
 * Template Name: Страница всех тегов 
 */

$minPostsToShow = SettingsHelper::get('controller_tags', 'min_posts_to_show', 1);
$defaultTagImage = SettingsHelper::get('controller_tags', 'default_tag_image', '');
$tagPrefix = SettingsHelper::get('controller_tags', 'tag_prefix', '#');

$colorPalette = [
    '#c11c3b', '#0a11a8', '#002306', '#c76234',
    '#8a2be2', '#20c997', '#fd7e14', '#6f42c1',
    '#139090', '#d59801', '#296e4a'
];
?>

<div class="tg-tags-page">
    <div class="tg-container">

        <div class="tg-page-header tg-mb-5">
            <h1 class="tg-page-title">
                <?php echo LANG_TEMPLATE_TAGS_ALL_TITLE; ?>
                <span class="tg-page-title-dot">.</span>
            </h1>
            <p class="tg-page-description tg-text-muted">
                <?php echo LANG_TEMPLATE_TAGS_ALL_DESCRIPTION; ?>
            </p>
        </div>
        
        <div class="tg-tags-stats tg-mb-5">
            <div class="tg-card">
                <div class="tg-card-body">
                    <h3 class="tg-card-title tg-mb-4">
                        <?php echo bloggy_icon('bs', 'tags', '18', '#4f46e5', 'tg-mr-2'); ?>
                        <?php echo LANG_TEMPLATE_TAGS_STATS_TITLE; ?>
                    </h3>
                    
                    <div class="tg-stats-grid">
                        <div class="tg-stat-card">
                            <div class="tg-stat-icon" style="background: rgba(79, 70, 229, 0.1);">
                                <?php echo bloggy_icon('bs', 'tag-fill', '18', '#4f46e5'); ?>
                            </div>
                            <div class="tg-stat-content">
                                <span class="tg-stat-label"><?php echo LANG_TEMPLATE_TAGS_STATS_TOTAL_LABEL; ?></span>
                                <span class="tg-stat-value"><?php echo count($tags); ?></span>
                            </div>
                        </div>
                        
                        <?php 
                        $mostPopularTag = null;
                        $maxPostsCount = 0;
                        
                        foreach ($tags as $tag) {
                            if ($tag['posts_count'] > $maxPostsCount) {
                                $maxPostsCount = $tag['posts_count'];
                                $mostPopularTag = $tag;
                            }
                        }
                        ?>
                        
                        <?php if ($mostPopularTag) { ?>
                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($mostPopularTag['slug']); ?>" class="tg-stat-card">
                                <div class="tg-stat-icon" style="background: rgba(220, 53, 69, 0.1);">
                                    <?php echo bloggy_icon('bs', 'fire', '18', '#dc3545'); ?>
                                </div>
                                <div class="tg-stat-content">
                                    <span class="tg-stat-label"><?php echo LANG_TEMPLATE_TAGS_STATS_POPULAR_LABEL; ?></span>
                                    <span class="tg-stat-value"><?php echo html($tagPrefix); ?><?php echo html($mostPopularTag['name']); ?></span>
                                </div>
                            </a>
                        <?php } ?>
                        
                        <div class="tg-stat-card">
                            <div class="tg-stat-icon" style="background: rgba(79, 70, 229, 0.1);">
                                <?php echo bloggy_icon('bs', 'newspaper', '18', '#4f46e5'); ?>
                            </div>
                            <div class="tg-stat-content">
                                <span class="tg-stat-label"><?php echo LANG_TEMPLATE_TAGS_STATS_POSTS_LABEL; ?></span>
                                <span class="tg-stat-value">
                                    <?php 
                                    $totalPostsInTags = 0;
                                    foreach ($tags as $tag) {
                                        $totalPostsInTags += $tag['posts_count'];
                                    }
                                    echo $totalPostsInTags; ?>
                                </span>
                            </div>
                        </div>
                        
                        <a href="<?php echo BASE_URL; ?>/posts" class="tg-stat-card">
                            <div class="tg-stat-icon" style="background: rgba(79, 70, 229, 0.1);">
                                <?php echo bloggy_icon('bs', 'grid-3x3-gap', '18', '#4f46e5'); ?>
                            </div>
                            <div class="tg-stat-content">
                                <span class="tg-stat-label"><?php echo LANG_TEMPLATE_TAGS_STATS_ALL_POSTS_LABEL; ?></span>
                                <span class="tg-stat-value"><?php echo LANG_TEMPLATE_TAGS_STATS_ALL_POSTS_VALUE; ?></span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($tags)) { ?>
        
            <div class="tg-tags-header tg-mb-4">
                <h2 class="tg-section-title">
                    <?php echo LANG_TEMPLATE_TAGS_SECTION_TITLE; ?>
                    <span class="tg-section-title-count"><?php echo count($tags); ?></span>
                </h2>
            </div>
            
            <div class="tg-tags-grid">
                <?php foreach ($tags as $index => $tag) { 
                    $bgColor = $colorPalette[$index % count($colorPalette)];
                    $tagImage = '';
                    
                    if (!empty($tag['image'])) {
                        $tagImage = BASE_URL . '/uploads/tags/' . html($tag['image']);
                    } elseif (!empty($defaultTagImage)) {
                        $tagImage = BASE_URL . '/uploads/settings/tags/' . html($defaultTagImage);
                    }
                ?>
                <div class="tg-tag-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start tg-mb-3">
                            <div class="tg-mr-3 flex-shrink-0">
                                <?php if ($tagImage) { ?>
                                    <div class="rounded-circle overflow-hidden" style="width: 56px; height: 56px;">
                                        <img src="<?php echo $tagImage; ?>" 
                                            alt="<?php echo html($tag['name']); ?>" 
                                            class="w-100 h-100" style="object-fit: cover;">
                                    </div>
                                <?php } else { ?>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                        style="width: 56px; height: 56px; background-color: <?php echo $bgColor; ?>20;">
                                        <?php echo bloggy_icon('bs', 'tag', '24', $bgColor); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <div class="flex-grow-1" style="min-width: 0;">
                                <h5 class="card-title tg-mb-1">
                                    <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" 
                                    class="text-decoration-none text-dark">
                                        <?php echo html($tagPrefix); ?><?php echo html($tag['name']); ?>
                                    </a>
                                </h5>
                                <div class="tg-text-muted small">
                                    <span class="d-flex align-items-center">
                                        <?php echo bloggy_icon('bs', 'file-text', '12', 'currentColor', 'tg-mr-1'); ?>
                                        <?php echo $tag['posts_count']; ?> 
                                        <?php echo plural_form($tag['posts_count'], [LANG_TEMPLATE_TAGS_POST_1, LANG_TEMPLATE_TAGS_POST_2, LANG_TEMPLATE_TAGS_POST_3]); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($tag['description'])) { ?>
                            <div class="card-text tg-text-muted small tg-mb-3">
                                <?php echo html(mb_strimwidth($tag['description'], 0, 120, '...')); ?>
                            </div>
                        <?php } ?>
                        
                        <div class="tag-card-footer">
                            <span class="tag-date">
                                <?php if (!empty($tag['created_at'])) { ?>
                                <?php echo date('d.m.Y', strtotime($tag['created_at'])); ?>
                                <?php } ?>
                            </span>
                            
                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" 
                            class="btn btn-sm btn-outline-primary">
                                <?php echo LANG_TEMPLATE_TAGS_VIEW_BTN; ?>
                                <?php echo bloggy_icon('bs', 'arrow-right', '14', 'currentColor', 'tg-ml-1'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <span class="badge bg-light text-dark">
                            <?php echo ($total = (int)($tag['posts_count'] ?? 0)) . ' ' . plural($total, [LANG_TEMPLATE_TAGS_POST_1, LANG_TEMPLATE_TAGS_POST_2, LANG_TEMPLATE_TAGS_POST_3]); ?>
                        </span>
                        <?php if (!empty($tag['updated_at'])) { ?>
                        <span class="badge bg-light text-dark">
                            <?php echo sprintf(LANG_TEMPLATE_TAGS_UPDATED_AT, date('d.m', strtotime($tag['updated_at']))); ?>
                        </span>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <?php if (!empty($pagination) && $pagination['total_pages'] > 1) { ?>
                <div class="tg-pagination tg-mt-5 tg-text-center">
                    <?php if ($pagination['current_page'] < $pagination['total_pages']) { ?>
                        <a href="<?php echo BASE_URL; ?>/tags?page=<?php echo $pagination['current_page'] + 1; ?>" class="btn btn-outline-primary btn-lg">
                            <?php echo bloggy_icon('bs', 'arrow-down', '16', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_TAGS_SHOW_MORE_BTN; ?>
                        </a>
                    <?php } else { ?>
                        <div class="tg-text-muted tg-py-3">
                            <?php echo LANG_TEMPLATE_TAGS_VIEWED_ALL; ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        
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
                    <a href="<?php echo BASE_URL; ?>/posts" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'newspaper', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_TAGS_ALL_POSTS_BTN; ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/categories" class="btn btn-outline-primary">
                        <?php echo bloggy_icon('bs', 'folder', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_TAGS_CATEGORIES_BTN; ?>
                    </a>
                </div>
            </div>
        
        <?php } ?>
        
    </div>
</div>