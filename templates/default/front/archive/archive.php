<?php
/**
 * Template Name: Архив блога
 */

$monthNames = [
    1 => LANG_TEMPLATE_ARCHIVE_MONTH_JANUARY, 2 => LANG_TEMPLATE_ARCHIVE_MONTH_FEBRUARY,
    3 => LANG_TEMPLATE_ARCHIVE_MONTH_MARCH, 4 => LANG_TEMPLATE_ARCHIVE_MONTH_APRIL,
    5 => LANG_TEMPLATE_ARCHIVE_MONTH_MAY, 6 => LANG_TEMPLATE_ARCHIVE_MONTH_JUNE,
    7 => LANG_TEMPLATE_ARCHIVE_MONTH_JULY, 8 => LANG_TEMPLATE_ARCHIVE_MONTH_AUGUST,
    9 => LANG_TEMPLATE_ARCHIVE_MONTH_SEPTEMBER, 10 => LANG_TEMPLATE_ARCHIVE_MONTH_OCTOBER,
    11 => LANG_TEMPLATE_ARCHIVE_MONTH_NOVEMBER, 12 => LANG_TEMPLATE_ARCHIVE_MONTH_DECEMBER
];
?>

<div class="tg-archive-page">
    <div class="tg-container">
        
        <div class="tg-archive-header tg-mb-4">
            <div class="tg-archive-header-left">
                <div class="tg-archive-icon">
                    <?php echo bloggy_icon('bs', 'archive', '24', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-archive-info">
                    <h1 class="tg-archive-title"><?php echo LANG_TEMPLATE_ARCHIVE_TITLE; ?></h1>
                    <p class="tg-archive-subtitle tg-text-muted">
                        <?php echo LANG_TEMPLATE_ARCHIVE_SUBTITLE; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <?php
        $totalYears = count($postsByMonth ?? []);
        $totalMonths = 0;
        $totalPosts = 0;
        $firstYear = null;
        $lastYear = null;
        
        if (!empty($postsByMonth)) {
            $years = array_keys($postsByMonth);
            if (!empty($years)) {
                $firstYear = min($years);
                $lastYear = max($years);
            }
            
            foreach ($postsByMonth as $year => $months) {
                $totalMonths += count($months);
                foreach ($months as $month => $posts) {
                    $totalPosts += count($posts);
                }
            }
        }
        ?>
        
        <div class="tg-stats-grid tg-mb-4">
            <div class="tg-stat-card">
                <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                    <?php echo bloggy_icon('bs', 'calendar-range', '20', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-stat-content">
                    <span class="tg-stat-label"><?php echo LANG_TEMPLATE_ARCHIVE_STATS_PERIOD_LABEL; ?></span>
                    <span class="tg-stat-value">
                        <?php if ($firstYear && $lastYear) { ?>
                            <?php echo $firstYear ?> — <?php echo $lastYear ?>
                        <?php } else { ?>
                            <?php echo LANG_TEMPLATE_ARCHIVE_STATS_NO_DATA; ?>
                        <?php } ?>
                    </span>
                </div>
            </div>
            
            <div class="tg-stat-card">
                <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                    <?php echo bloggy_icon('bs', 'calendar-month', '20', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-stat-content">
                    <span class="tg-stat-label"><?php echo LANG_TEMPLATE_ARCHIVE_STATS_YEARS_LABEL; ?></span>
                    <span class="tg-stat-value"><?php echo $totalYears; ?> <?php echo plural_form($totalYears, [LANG_TEMPLATE_ARCHIVE_YEAR_1, LANG_TEMPLATE_ARCHIVE_YEAR_2, LANG_TEMPLATE_ARCHIVE_YEAR_3]); ?></span>
                </div>
            </div>
            
            <div class="tg-stat-card">
                <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                    <?php echo bloggy_icon('bs', 'calendar-week', '20', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-stat-content">
                    <span class="tg-stat-label"><?php echo LANG_TEMPLATE_ARCHIVE_STATS_MONTHS_LABEL; ?></span>
                    <span class="tg-stat-value"><?php echo $totalMonths; ?> <?php echo plural_form($totalMonths, [LANG_TEMPLATE_ARCHIVE_MONTH_1, LANG_TEMPLATE_ARCHIVE_MONTH_2, LANG_TEMPLATE_ARCHIVE_MONTH_3]); ?></span>
                </div>
            </div>
            
            <div class="tg-stat-card">
                <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                    <?php echo bloggy_icon('bs', 'file-text', '20', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-stat-content">
                    <span class="tg-stat-label"><?php echo LANG_TEMPLATE_ARCHIVE_STATS_POSTS_LABEL; ?></span>
                    <span class="tg-stat-value"><?php echo $totalPosts; ?> <?php echo plural_form($totalPosts, [LANG_TEMPLATE_ARCHIVE_POST_1, LANG_TEMPLATE_ARCHIVE_POST_2, LANG_TEMPLATE_ARCHIVE_POST_3]); ?></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($postsByMonth)) { ?>
        
            <div class="tg-archive-content">
                
                <?php 
                $yearIndex = 0;
                foreach ($postsByMonth as $year => $months) { 
                    $yearIndex++;
                    $yearPostsCount = 0;
                    foreach ($months as $month => $posts) {
                        $yearPostsCount += count($posts);
                    }
                ?>
                
                <div class="tg-year-section tg-mb-4">
                    <div class="tg-year-header">
                        <h2 class="tg-year-title"><?php echo $year; ?> <?php echo LANG_TEMPLATE_ARCHIVE_YEAR_SUFFIX; ?></h2>
                        <span class="tg-year-count">
                            <?php echo $yearPostsCount; ?> <?php echo plural_form($yearPostsCount, [LANG_TEMPLATE_ARCHIVE_POST_1, LANG_TEMPLATE_ARCHIVE_POST_2, LANG_TEMPLATE_ARCHIVE_POST_3]); ?>
                        </span>
                    </div>
                    
                    <div class="tg-months-grid">
                        <?php 
                        $monthIndex = 0;
                        foreach ($months as $month => $posts) { 
                            if (empty($posts)) continue;
                            
                            $monthName = $monthNames[$month] ?? LANG_TEMPLATE_ARCHIVE_MONTH_DEFAULT;
                            $monthIndex++;
                        ?>
                        
                        <div class="tg-month-card">
                            <div class="tg-month-header">
                                <div class="tg-month-icon">
                                    <?php echo bloggy_icon('bs', 'calendar-month', '18', 'var(--tg-primary)'); ?>
                                </div>
                                <h3 class="tg-month-title"><?php echo $monthName; ?></h3>
                                <span class="tg-month-count"><?php echo count($posts); ?></span>
                            </div>
                            
                            <div class="tg-month-posts" data-month="<?php echo $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT); ?>">
                                <?php foreach ($posts as $index => $post) { ?>
                                <div class="tg-archive-post-item <?php echo $index >= 3 ? 'tg-hidden' : ''; ?>">
                                    <div class="tg-post-date d-block">
                                        <span class="tg-post-day"><?php echo date('d', strtotime($post['created_at'])); ?></span>
                                        <span class="tg-post-month-short mx-1">
                                            <?php 
                                            $monthNum = date('n', strtotime($post['created_at']));
                                            $monthShort = mb_substr($monthNames[$monthNum], 0, 3, 'UTF-8');
                                            echo $monthShort;
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="tg-post-info">
                                        <h4 class="tg-post-title">
                                            <a href="<?php echo BASE_URL; ?>/post/<?php echo html($post['slug']); ?>">
                                                <?php echo html($post['title']); ?>
                                            </a>
                                        </h4>
                                        
                                        <div class="tg-post-meta">
                                            <?php if (!empty($post['category_name'])) { ?>
                                                <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                                                class="tg-post-category-link">
                                                    <?php echo bloggy_icon('bs', 'folder', '10', 'currentColor', 'tg-mr-1'); ?>
                                                    <?php echo html($post['category_name']); ?>
                                                </a>
                                            <?php } ?>
                                            
                                            <span class="tg-post-views">
                                                <?php echo bloggy_icon('bs', 'eye', '10', 'currentColor', 'tg-mr-1'); ?>
                                                <?php echo $post['views'] ?? 0; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <a href="<?php echo BASE_URL; ?>/post/<?php echo html($post['slug']); ?>" class="tg-post-link">
                                        <?php echo bloggy_icon('bs', 'chevron-right', '14', 'currentColor'); ?>
                                    </a>
                                </div>
                                <?php } ?>
                            </div>
                            
                            <?php if (count($posts) > 3) { ?>
                            <button class="tg-show-more-btn" data-month="<?php echo $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT); ?>">
                                <span><?php echo sprintf(LANG_TEMPLATE_ARCHIVE_SHOW_ALL, count($posts)); ?></span>
                                <?php echo bloggy_icon('bs', 'chevron-down', '14', 'currentColor', 'tg-ml-1'); ?>
                            </button>
                            <?php } ?>
                        </div>
                        
                        <?php } ?>
                    </div>
                </div>
                
                <?php } ?>
                
            </div>
        
        <?php } else { ?>
        
            <div class="tg-empty-state">
                <div class="tg-empty-state-icon">
                    <?php echo bloggy_icon('bs', 'archive', '48', 'var(--tg-text-secondary)'); ?>
                </div>
                <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_ARCHIVE_EMPTY_TITLE; ?></h3>
                <p class="tg-empty-state-text tg-text-muted">
                    <?php echo LANG_TEMPLATE_ARCHIVE_EMPTY_TEXT; ?>
                </p>
                <div class="tg-empty-actions">
                    <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                        <?php echo bloggy_icon('bs', 'newspaper', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_ARCHIVE_EMPTY_ALL_POSTS_BTN; ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline tg-ml-2">
                        <?php echo bloggy_icon('bs', 'folder', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_ARCHIVE_EMPTY_CATEGORIES_BTN; ?>
                    </a>
                </div>
            </div>
        
        <?php } ?>
        
    </div>
</div>