<?php
/**
 * Tags Block Template
 */

$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'center';
$displayStyle = $settings['display_style'] ?? 'cloud';
$columns = (int)($settings['columns'] ?? 3);
$showPostCount = !empty($settings['show_post_count']);
$showIcon = !empty($settings['show_icon']);
$gradientCards = !empty($settings['gradient_cards']);
$imageStyle = $settings['image_style'] ?? 'icon';
$imageSize = $settings['image_size'] ?? 'md';
$imageRounded = !empty($settings['image_rounded']);
$imageShadow = !empty($settings['image_shadow']);

$customStyles = [];
if($theme === 'custom') {
    if(!empty($settings['background_color'])) {
        $customStyles[] = '--bg-color: ' . html($settings['background_color']);
    }
    if(!empty($settings['text_color'])) {
        $customStyles[] = '--text-color: ' . html($settings['text_color']);
    }
}
if(!empty($settings['accent_color'])) {
    $customStyles[] = '--accent-color: ' . html($settings['accent_color']);
    $hex = ltrim($settings['accent_color'], '#');
    if(strlen($hex) === 6) {
        $rgb = hexdec(substr($hex, 0, 2)) . ', ' . 
               hexdec(substr($hex, 2, 2)) . ', ' . 
               hexdec(substr($hex, 4, 2));
        $customStyles[] = '--accent-rgb: ' . $rgb;
    }
}
if(!empty($settings['card_background'])) {
    $customStyles[] = '--card-bg: ' . html($settings['card_background']);
}
if($gradientCards && !empty($settings['accent_color'])) {
    $customStyles[] = '--gradient-start: ' . html($settings['accent_color']) . '15';
    $customStyles[] = '--gradient-end: ' . html($settings['accent_color']) . '05';
}

$paddingTop = (int)($settings['padding_top'] ?? 80);
$paddingBottom = (int)($settings['padding_bottom'] ?? 80);
$customStyles[] = '--padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--padding-bottom: ' . $paddingBottom . 'px';

$sectionClass = 'tags-block';
$sectionClass .= ' theme-' . $theme;
$sectionClass .= ' align-' . $align;
$sectionClass .= ' style-' . $displayStyle;
if(!empty($settings['custom_css_class'])) {
    $sectionClass .= ' ' . html($settings['custom_css_class']);
}

$tags = $this->tags ?? [];

$tagIcon = '';
if(function_exists('bloggy_icon')) {
    $tagIcon = bloggy_icon('bs', 'tag', '20 20', 'currentColor', '');
} else {
    $tagIcon = '<svg width="20" height="20" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586V2z"/><path d="M5.5 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/></svg>';
}
?>

<section id="<?php echo html($settings['custom_id'] ?? ''); ?>" class="<?php echo $sectionClass; ?>" style="<?php echo implode('; ', $customStyles); ?>">
    <div class="container">
        
        <?php if(!empty($settings['badge']) || !empty($settings['title']) || !empty($settings['description'])) { ?>
            <div class="header">
                <?php if(!empty($settings['badge'])) { ?>
                    <span class="section-badge"><?php echo html($settings['badge']); ?></span>
                <?php } ?>
                <?php if(!empty($settings['title'])) { ?>
                    <h2 class="section-title"><?php echo $settings['title']; ?></h2>
                <?php } ?>
                <?php if(!empty($settings['description'])) { ?>
                    <p class="section-description"><?php echo nl2br(html($settings['description'])); ?></p>
                <?php } ?>
            </div>
        <?php } ?>
        
        <?php if(!empty($tags)) { ?>
            
            <?php if($displayStyle === 'cloud') { ?>
                <div class="tags-cloud">
                    <?php foreach($tags as $tag) {
                        $weight = $tag['weight'] ?? 3;
                    ?>
                    <a href="/tag/<?php echo html($tag['slug']); ?>" class="tag-pill weight-<?php echo $weight; ?>">

                        <span class="tag-pill-hash">#</span>

                        <span class="tag-pill-name"><?php echo html($tag['name']); ?></span>

                        <?php if($showPostCount) { ?>
                            <span class="tag-pill-count"><?php echo $tag['posts_count'] ?? 0; ?></span>
                        <?php } ?>
                        
                    </a>
                    <?php } ?>
                </div>
            <?php } elseif($displayStyle === 'cards' || $displayStyle === 'grid') { ?>
                <div class="tags-grid cols-<?php echo $columns; ?>">
                    <?php foreach($tags as $tag) {
                        $imageUrl = $this->getTagImageUrl($tag);
                        $hasImage = ($imageStyle !== 'icon' && $imageStyle !== 'none');
                    ?>
                    <a href="/tag/<?php echo html($tag['slug']); ?>" class="tag-card">
                        <div class="tag-card-body">
                            <?php if($hasImage && $imageStyle !== 'cover' && $imageStyle !== 'background') { ?>
                                <div class="tag-card-media-small">
                                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo html($tag['name']); ?>" loading="lazy">
                                </div>
                            <?php } elseif($imageStyle === 'icon' && $showIcon) { ?>
                                <div class="tag-card-icon"><?php echo $tagIcon; ?></div>
                            <?php } ?>
                            
                            <h3 class="tag-card-title">
                                <span class="tag-hash">#</span><?php echo html($tag['name']); ?>
                            </h3>
                            
                            <?php if($showPostCount) { 
                              $count = $tag['posts_count'] ?? 0;
                              $countText = ($lang === 'ru') 
                              ? plural_form($count, ['пост', 'поста', 'постов']) 
                              : ($count === 1 ? 'post' : 'posts');
                            ?>
                            <div class="tag-card-meta">
                             <span class="tag-posts-count">
                            <?php echo $count . ' ' . $countText; ?>
                            </span>
                            </div>
                             <?php } ?>
                        </div>
                        <div class="tag-card-arrow">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </div>
                        <?php if($imageStyle === 'background' && $hasImage) { ?>
                            <div class="tag-card-bg" style="background-image: url('<?php echo $imageUrl; ?>')"></div>
                        <?php } ?>
                    </a>
                    <?php } ?>
                </div>
            <?php } elseif($displayStyle === 'list') { ?>
                <div class="tags-list">
                    <?php foreach($tags as $tag) {
                        $imageUrl = $this->getTagImageUrl($tag);
                    ?>
                    <a href="/tag/<?php echo html($tag['slug']); ?>" class="tag-list-item">
                        <div class="tag-list-media">
                            <?php if($imageStyle === 'thumbnail' || $imageStyle === 'side') { ?>
                                <img src="<?php echo $imageUrl; ?>" alt="<?php echo html($tag['name']); ?>" loading="lazy">
                            <?php } elseif($showIcon) { ?>
                                <div class="tag-list-icon"><?php echo $tagIcon; ?></div>
                            <?php } ?>
                        </div>
                        <div class="tag-list-content">
                            <h3 class="tag-list-title">
                                <span class="tag-hash">#</span><?php echo html($tag['name']); ?>
                            </h3>
                        </div>
                       <?php if($showPostCount) { 
                        $count = $tag['posts_count'] ?? 0;
                        $countText = ($lang === 'ru') 
                        ? plural_form($count, ['пост', 'поста', 'постов']) 
                        : ($count === 1 ? 'post' : 'posts');
                       ?>
                     <div class="tag-list-count">
                    <?php echo $count; ?>
                     <span class="label"><?php echo $countText; ?></span>
                     </div>
                    <?php } ?>
                    </a>
                    <?php } ?>
                </div>
            <?php } elseif($displayStyle === 'compact') { ?>
                <div class="tags-compact">
                    <?php foreach($tags as $tag) { ?>
                    <a href="/tag/<?php echo html($tag['slug']); ?>" class="tag-chip">

                        <?php if($showIcon) { ?>
                            <span class="tag-chip-icon"><?php echo $tagIcon; ?></span>
                        <?php } ?>

                        <span class="tag-chip-name"><?php echo html($tag['name']); ?></span>

                        <?php if($showPostCount) { ?>
                            <span class="tag-chip-count"><?php echo $tag['posts_count'] ?? 0; ?></span>
                        <?php } ?>
                    </a>
                    <?php } ?>
                </div>
            <?php } ?>
            
        <?php } else { ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h3>Теги не найдены</h3>
                <p>Пока на сайте нет тегов или они не назначены постам</p>
            </div>
        <?php } ?>
        
    </div>
</section>
