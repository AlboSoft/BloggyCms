<?php
/**
 * Template Name: Все категории - Habr Pro
 */
?>
<div class="tg-categories-page">
    <div class="tg-container">
        <div class="tg-page-header">
            <h1 class="tg-page-title"><?php echo LANG_TEMPLATE_CATEGORIES_ALL_TITLE; ?></h1>
            <p class="tg-page-subtitle"><?php echo $total_categories; ?> <?php echo plural_form($total_categories, [LANG_TEMPLATE_CATEGORIES_SECTION_1, LANG_TEMPLATE_CATEGORIES_SECTION_2, LANG_TEMPLATE_CATEGORIES_SECTION_3]); ?></p>
        </div>
        
        <?php if (!empty($categories)) { ?>
        <div class="tg-categories-grid tg-layout-<?php echo $layout; ?>">
            <?php foreach ($categories as $index => $category) { 
                $postCount = $category['posts_count'] ?? 0;
            ?>
            <a href="<?php echo BASE_URL; ?>/category/<?php echo html($category['slug']); ?>" class="tg-category-card">
                <div class="tg-category-card-inner">
                    <div class="tg-category-icon-wrapper">
                        <?php if ($show_images && !empty($category['image'])) { ?>
                            <img src="<?php echo BASE_URL . '/uploads/images/' . html($category['image']); ?>" alt="<?php echo html($category['name']); ?>" class="tg-category-image">
                        <?php } else { ?>
                            <span style="font-weight:800;font-size:14px"><?php echo strtoupper(mb_substr($category['name'],0,2)); ?></span>
                        <?php } ?>
                    </div>
                    <div class="tg-category-content">
                        <h3 class="tg-category-name">
                            <?php echo html($category['name']); ?>
                            <?php if ($category['password_protected'] == 1) { ?><span class="tg-category-protected">🔒</span><?php } ?>
                        </h3>
                        <?php if ($show_descriptions && !empty($category['description'])) { ?>
                            <p class="tg-category-description"><?php echo html(mb_substr($category['description'], 0, 80)); ?></p>
                        <?php } ?>
                        <div class="tg-category-meta">
                            <?php if ($show_post_counts) { ?><span class="tg-category-posts"><?php echo $postCount; ?> публикаций</span><?php } ?>
                        </div>
                    </div>
                    <div class="tg-category-arrow">→</div>
                </div>
            </a>
            <?php } ?>
        </div>
        
        <div class="tg-categories-stats" style="margin-top:16px">
            <div class="tg-stats-grid">
                <div class="tg-stat-card">
                    <div class="tg-stat-content"><span class="tg-stat-label">всего разделов</span><span class="tg-stat-value"><?php echo $total_categories; ?></span></div>
                </div>
                <?php
                $totalPosts = array_sum(array_column($categories, 'posts_count'));
                $protectedCount = count(array_filter($categories, fn($cat) => $cat['password_protected'] == 1));
                ?>
                <div class="tg-stat-card"><div class="tg-stat-content"><span class="tg-stat-label">публикаций</span><span class="tg-stat-value"><?php echo $totalPosts; ?></span></div></div>
                <div class="tg-stat-card"><div class="tg-stat-content"><span class="tg-stat-label">закрытых</span><span class="tg-stat-value"><?php echo $protectedCount; ?></span></div></div>
            </div>
        </div>
        
        <?php if ($pagination['has_more']) { ?>
        <div class="tg-pagination"><a href="<?php echo $pagination['next_url']; ?>" class="tg-btn tg-btn-outline">показать еще</a></div>
        <?php } ?>
        
        <?php } else { ?>
        <div class="tg-empty-state">
            <h3 class="tg-empty-state-title">нет разделов</h3>
            <p class="tg-empty-state-text">пока нет категорий</p>
            <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">к публикациям</a>
        </div>
        <?php } ?>
    </div>
</div>
