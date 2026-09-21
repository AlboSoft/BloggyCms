<?php
/**
 * Latest Posts Block - Habr Pro Style
 */
$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'left';
$columns = (int)($settings['columns'] ?? 3);

$customStyles = [];
if($theme === 'custom') {
    if(!empty($settings['background_color'])) $customStyles[] = '--bg-color: ' . html($settings['background_color']);
    if(!empty($settings['text_color'])) $customStyles[] = '--text-color: ' . html($settings['text_color']);
}
if(!empty($settings['accent_color'])) $customStyles[] = '--accent-color: ' . html($settings['accent_color']);
if(!empty($settings['card_background'])) $customStyles[] = '--card-bg: ' . html($settings['card_background']);

$paddingTop = (int)($settings['padding_top'] ?? 32);
$paddingBottom = (int)($settings['padding_bottom'] ?? 32);
$customStyles[] = '--padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--padding-bottom: ' . $paddingBottom . 'px';

$sectionClass = 'latest-posts theme-' . $theme . ' align-' . $align;
if(!empty($settings['custom_css_class'])) $sectionClass .= ' ' . html($settings['custom_css_class']);

$posts = $this->posts ?? [];
?>
<section id="<?= html($settings['custom_id'] ?? '') ?>" class="<?= $sectionClass ?>" style="<?= implode('; ', $customStyles) ?>">
    <div class="container">
        <?php if(!empty($settings['badge']) || !empty($settings['title']) || !empty($settings['description'])) { ?>
            <div class="header">
                <?php if(!empty($settings['badge'])) { ?><div class="badge"><?= html($settings['badge']) ?></div><?php } ?>
                <?php if(!empty($settings['title'])) { ?><h2><?= $settings['title'] ?></h2><?php } ?>
                <?php if(!empty($settings['description'])) { ?><div class="header-description"><?= nl2br(html($settings['description'])) ?></div><?php } ?>
            </div>
        <?php } ?>
        
        <?php if(!empty($posts)) { ?>
            <div class="posts-grid cols-<?= $columns ?>">
                <?php foreach($posts as $post) {
                    $postUrl = $this->getPostUrl($post);
                    $postTitle = html($post['title'] ?? '');
                    $postExcerpt = html($post['excerpt'] ?? '');
                    $postDate = $post['formatted_date'] ?? '';
                    $categoryName = html($post['category_name'] ?? '');
                    $authorName = html($post['author_name'] ?? $post['author_display_name'] ?? $post['author_username'] ?? '');
                    $views = (int)($post['views'] ?? 0);
                    $showImage = !empty($settings['show_featured_image']) && !empty($post['featured_image']) && (!isset($post['show_cover_in_list']) || (int)$post['show_cover_in_list'] === 1);
                    $imageUrl = $showImage ? $this->getPostImageUrl($post) : '';
                ?>
                <article class="post-card">
                    <?php if($showImage) { ?>
                        <div class="post-image"><a href="<?= $postUrl ?>"><img src="<?= $imageUrl ?>" alt="<?= $postTitle ?>" loading="lazy"></a></div>
                    <?php } ?>
                    <div class="post-content">
                        <?php if(!empty($settings['show_category']) && !empty($categoryName)) { ?><div class="post-category"><?= $categoryName ?></div><?php } ?>
                        <h3 class="post-title"><a href="<?= $postUrl ?>"><?= $postTitle ?></a></h3>
                        <?php if(!empty($settings['show_excerpt']) && !empty($postExcerpt)) { ?><div class="post-excerpt"><?= $postExcerpt ?></div><?php } ?>
                        <div class="post-meta">
                            <?php if(!empty($settings['show_date']) && !empty($postDate)) { ?><span class="post-date"><?= $postDate ?></span><?php } ?>
                            <?php if(!empty($settings['show_views'])) { ?><span class="post-views">👁 <?= $views ?></span><?php } ?>
                            <?php if(!empty($settings['show_author']) && !empty($authorName)) { ?><span class="post-author"><?= $authorName ?></span><?php } ?>
                        </div>
                    </div>
                </article>
                <?php } ?>
            </div>
        <?php } else { ?><p style="font-size:13px;color:#6c6c6c">нет публикаций</p><?php } ?>
    </div>
</section>
