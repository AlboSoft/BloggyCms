<?php
/**
 * Tags Block - Habr Pro - строгий список тегов
 */
$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'left';
$displayStyle = $settings['display_style'] ?? 'cloud';
$columns = (int)($settings['columns'] ?? 3);
$showPostCount = !empty($settings['show_post_count']);

$customStyles = [];
if($theme === 'custom') {
    if(!empty($settings['background_color'])) $customStyles[] = '--bg-color: ' . html($settings['background_color']);
    if(!empty($settings['text_color'])) $customStyles[] = '--text-color: ' . html($settings['text_color']);
}
if(!empty($settings['accent_color'])) $customStyles[] = '--accent-color: ' . html($settings['accent_color']);
$paddingTop = (int)($settings['padding_top'] ?? 32);
$paddingBottom = (int)($settings['padding_bottom'] ?? 32);
$customStyles[] = '--padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--padding-bottom: ' . $paddingBottom . 'px';

$sectionClass = 'tags-block theme-' . $theme . ' align-' . $align . ' style-' . $displayStyle;
if(!empty($settings['custom_css_class'])) $sectionClass .= ' ' . html($settings['custom_css_class']);

$tags = $this->tags ?? [];
?>
<section id="<?= html($settings['custom_id'] ?? '') ?>" class="<?= $sectionClass ?>" style="<?= implode('; ', $customStyles) ?>">
    <div class="container">
        <?php if(!empty($settings['badge']) || !empty($settings['title']) || !empty($settings['description'])) { ?>
            <div class="header">
                <?php if(!empty($settings['badge'])) { ?><span class="section-badge"><?= html($settings['badge']) ?></span><?php } ?>
                <?php if(!empty($settings['title'])) { ?><h2 class="section-title"><?= $settings['title'] ?></h2><?php } ?>
                <?php if(!empty($settings['description'])) { ?><p class="section-description"><?= nl2br(html($settings['description'])) ?></p><?php } ?>
            </div>
        <?php } ?>
        <?php if(!empty($tags)) { ?>
            <?php if($displayStyle === 'cloud') { ?>
                <div class="tags-cloud">
                    <?php foreach($tags as $tag) { ?>
                    <a href="/tag/<?= html($tag['slug']) ?>" class="tag-pill"><span>#</span><?= html($tag['name']) ?><?php if($showPostCount) { ?><span style="opacity:.6;margin-left:4px;font-size:10px"><?= $tag['posts_count'] ?? 0 ?></span><?php } ?></a>
                    <?php } ?>
                </div>
            <?php } elseif($displayStyle === 'cards' || $displayStyle === 'grid') { ?>
                <div class="tags-grid cols-<?= $columns ?>">
                    <?php foreach($tags as $tag) { ?>
                    <a href="/tag/<?= html($tag['slug']) ?>" class="tag-card"><div class="tag-card-body"><h3 class="tag-card-title">#<?= html($tag['name']) ?></h3><?php if($showPostCount) { ?><div class="tag-card-count"><?= $tag['posts_count'] ?? 0 ?> публ.</div><?php } ?></div></a>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="tags-compact">
                    <?php foreach($tags as $tag) { ?><a href="/tag/<?= html($tag['slug']) ?>" class="tag-chip">#<?= html($tag['name']) ?><?php if($showPostCount) { ?><span style="opacity:.6">(<?= $tag['posts_count'] ?? 0 ?>)</span><?php } ?></a><?php } ?>
                </div>
            <?php } ?>
        <?php } else { ?><p style="font-size:12px;color:#6c6c6c">нет тегов</p><?php } ?>
    </div>
</section>
