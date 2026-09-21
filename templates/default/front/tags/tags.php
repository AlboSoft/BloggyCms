<?php
/**
 * Template Name: Все теги - Habr Pro
 */
$minPostsToShow = SettingsHelper::get('controller_tags', 'min_posts_to_show', 1);
$tagPrefix = SettingsHelper::get('controller_tags', 'tag_prefix', '#');
?>
<div class="tg-tags-page">
    <div class="tg-container">
        <div class="tg-page-header">
            <h1 class="tg-page-title"><?php echo LANG_TEMPLATE_TAGS_ALL_TITLE; ?></h1>
            <p class="tg-page-description"><?php echo LANG_TEMPLATE_TAGS_ALL_DESCRIPTION; ?></p>
        </div>
        
        <div class="tg-tags-stats">
            <div class="tg-card">
                <div class="tg-card-body">
                    <h3 class="tg-card-title">статистика</h3>
                    <div class="tg-stats-grid">
                        <div class="tg-stat-card">
                            <div class="tg-stat-content"><span class="tg-stat-label">всего тегов</span><span class="tg-stat-value"><?php echo count($tags); ?></span></div>
                        </div>
                        <?php 
                        $mostPopularTag = null; $maxPostsCount = 0;
                        foreach ($tags as $tag) { if ($tag['posts_count'] > $maxPostsCount) { $maxPostsCount = $tag['posts_count']; $mostPopularTag = $tag; } }
                        ?>
                        <?php if ($mostPopularTag) { ?>
                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($mostPopularTag['slug']); ?>" class="tg-stat-card">
                                <div class="tg-stat-content"><span class="tg-stat-label">популярный</span><span class="tg-stat-value"><?php echo html($tagPrefix); ?><?php echo html($mostPopularTag['name']); ?></span></div>
                            </a>
                        <?php } ?>
                        <div class="tg-stat-card"><div class="tg-stat-content"><span class="tg-stat-label">публикаций</span><span class="tg-stat-value"><?php $total=0; foreach($tags as $t) $total+=$t['posts_count']; echo $total; ?></span></div></div>
                        <a href="<?php echo BASE_URL; ?>/posts" class="tg-stat-card"><div class="tg-stat-content"><span class="tg-stat-label">все посты</span><span class="tg-stat-value">→</span></div></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="tg-tags-header">
            <h2 class="tg-section-title">теги <span class="tg-section-title-count"><?php echo count($tags); ?></span></h2>
        </div>

        <div class="tg-tags-grid">
            <?php foreach ($tags as $tag) { if (($tag['posts_count'] ?? 0) < $minPostsToShow) continue; ?>
            <div class="tg-tag-card">
                <div class="tg-card-body">
                    <h3 class="tg-card-title"><a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>"><?php echo html($tagPrefix); ?><?php echo html($tag['name']); ?></a></h3>
                    <?php if (!empty($tag['description'])) { ?><p class="tg-card-text"><?php echo html(mb_substr($tag['description'],0,100)); ?></p><?php } ?>
                    <div class="tg-tag-card-footer"><span class="tg-tag-date"><?php echo $tag['posts_count']; ?> публикаций</span><a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" class="tg-btn tg-btn-sm tg-btn-outline">открыть</a></div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
