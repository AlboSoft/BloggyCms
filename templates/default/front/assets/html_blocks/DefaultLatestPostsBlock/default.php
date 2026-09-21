<?php
/**
 * Посты блога
 */
$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'center';
$columns = (int)($settings['columns'] ?? 3);

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

$paddingTop = (int)($settings['padding_top'] ?? 80);
$paddingBottom = (int)($settings['padding_bottom'] ?? 80);
$customStyles[] = '--padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--padding-bottom: ' . $paddingBottom . 'px';

$sectionClass = 'latest-posts';
$sectionClass .= ' theme-' . $theme;
$sectionClass .= ' align-' . $align;
if(!empty($settings['custom_css_class'])) {
    $sectionClass .= ' ' . html($settings['custom_css_class']);
}

$posts = $this->posts ?? [];

$calendarIcon = bloggy_icon('bs', 'calendar3', '16 16', 'currentColor', 'me-1');
$clockIcon = bloggy_icon('bs', 'clock', '16 16', 'currentColor', 'me-1');
$userIcon = bloggy_icon('bs', 'person', '16 16', 'currentColor', 'me-1');
$eyeIcon = bloggy_icon('bs', 'eye', '16 16', 'currentColor', 'me-1');

?>

<section id="<?php echo html($settings['custom_id'] ?? ''); ?>" class="<?php echo $sectionClass; ?>" style="<?php echo implode('; ', $customStyles); ?>">
    <div class="container">
        <?php if(!empty($settings['badge']) || !empty($settings['title']) || !empty($settings['description'])) { ?>
            <div class="header">
                <?php if(!empty($settings['badge'])) { ?>
                    <div class="badge"><?php echo html($settings['badge']); ?></div>
                <?php } ?>
                <?php if(!empty($settings['title'])) { ?>
                    <h2><?php echo $settings['title']; ?></h2>
                <?php } ?>
                <?php if(!empty($settings['description'])) { ?>
                    <div class="header-description"><?php echo nl2br(html($settings['description'])); ?></div>
                <?php } ?>
            </div>
        <?php } ?>
        
        <?php if(!empty($posts)) { ?>
            <div class="posts-grid cols-<?php echo $columns; ?>">
                <?php foreach($posts as $post) {
                    $postUrl = $this->getPostUrl($post);
                    $postTitle = html($post['title'] ?? '');
                    $postExcerpt = html($post['excerpt'] ?? '');
                    $postDate = $post['formatted_date'] ?? '';
                    $readTime = $post['read_time'] ?? 1;
                    $categoryName = html($post['category_name'] ?? '');
                    $authorName = html($post['author_name'] ?? $post['author_display_name'] ?? $post['author_username'] ?? '');
                    $views = (int)($post['views'] ?? 0);
                ?>
                <article class="post-card">
                    <?php if(!empty($settings['show_featured_image']) && !empty($post['featured_image'])
                        && (!isset($post['show_cover_in_list']) || (int)$post['show_cover_in_list'] === 1)) {
                        $imageUrl = $this->getPostImageUrl($post);
                        $readTime = !empty($post['content']) ? $this->calculateReadTime($post['content']) : 1;
                    ?>
                        <div class="post-image">
                            <a href="<?php echo $postUrl; ?>">
                                <img src="<?php echo $imageUrl; ?>"
                                    alt="<?php echo $postTitle; ?>"
                                    loading="lazy">
                            </a>
                            <?php if(!empty($settings['show_read_time'])) { ?>
                                <span class="read-time-badge">
                                    <?php echo $clockIcon . $this->formatReadTime($readTime); ?>
                                </span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    
                    <div class="post-content">
                        <?php if(!empty($settings['show_category']) && !empty($categoryName)) { ?>
                            <div class="post-category"><?php echo $categoryName; ?></div>
                        <?php } ?>
                        
                        <h3 class="post-title">
                            <a href="<?php echo $postUrl; ?>">
                                <?php echo $postTitle; ?>
                            </a>
                        </h3>
                        
                        <?php if(!empty($settings['show_excerpt']) && !empty($postExcerpt)) { ?>
                            <div class="post-excerpt">
                                <?php echo $postExcerpt; ?>
                            </div>
                        <?php } ?>
                        
                        <div class="post-meta">
                            <?php if(!empty($settings['show_date']) && !empty($postDate)) { ?>
                                <span class="post-date">
                                    <?php echo $calendarIcon . $postDate; ?>
                                </span>
                            <?php } ?>
                            
                            <?php if(!empty($settings['show_views'])) { ?>
                                <span class="post-views">
                                    <?php echo $eyeIcon . $views; ?>
                                </span>
                            <?php } ?>
                            
                            <?php if(!empty($settings['show_author']) && !empty($authorName)) { ?>
                                <span class="post-author">
                                    <?php echo $userIcon . $authorName; ?>
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                </article>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="text-center py-5">
                <p class="text-muted">Нет доступных постов</p>
            </div>
        <?php } ?>
    </div>
</section>