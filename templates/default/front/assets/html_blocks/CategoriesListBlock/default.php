<?php
/**
 * Categories List Block - Habr Pro - строгий список
 */
$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'left';
$displayStyle = $settings['display_style'] ?? 'cards';
$columns = (int)($settings['columns'] ?? 3);
$showPostCount = !empty($settings['show_post_count']);
$imageStyle = $settings['image_style'] ?? 'icon';

$customStyles = [];
if($theme === 'custom') {
    if(!empty($settings['background_color'])) $customStyles[] = '--bg-color: ' . html($settings['background_color']);
    if(!empty($settings['text_color'])) $customStyles[] = '--text-color: ' . html($settings['text_color']);
}
if(!empty($settings['accent_color'])) {
    $customStyles[] = '--accent-color: ' . html($settings['accent_color']);
}
$paddingTop = (int)($settings['padding_top'] ?? 32);
$paddingBottom = (int)($settings['padding_bottom'] ?? 32);
$customStyles[] = '--padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--padding-bottom: ' . $paddingBottom . 'px';

$sectionClass = 'categories-list theme-' . $theme . ' align-' . $align;
if(!empty($settings['custom_css_class'])) $sectionClass .= ' ' . html($settings['custom_css_class']);

$categories = $this->categories ?? [];

function getCategoryImageUrlHabr($category) {
    if (!empty($category['image'])) {
        if (strpos($category['image'], 'http') === 0 || strpos($category['image'], '/') === 0) return $category['image'];
        return '/uploads/images/' . $category['image'];
    }
    return '';
}
function renderCategoryHabr($category, $settings) {
    $imageStyle = $settings['image_style'] ?? 'icon';
    $imageUrl = getCategoryImageUrlHabr($category);
    $html = '';
    $html .= '<a href="/category/' . html($category['slug']) . '" class="category-card">';
    if ($imageStyle === 'thumbnail' && $imageUrl) {
        $html .= '<div class="category-image thumbnail"><img src="' . $imageUrl . '" alt="' . html($category['name']) . '" loading="lazy"></div>';
    }
    $html .= '<div class="category-name">' . html($category['name']) . '</div>';
    if (!empty($category['description'])) {
        $html .= '<div class="category-description">' . html(mb_substr($category['description'],0,80)) . '</div>';
    }
    if (!empty($settings['show_post_count'])) {
        $html .= '<div class="category-count">' . ($category['posts_count'] ?? 0) . ' публ.</div>';
    }
    $html .= '</a>';
    return $html;
}
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
        <?php if(!empty($categories)) { ?>
            <div class="categories-grid cols-<?= $columns ?>">
                <?php foreach($categories as $cat) echo renderCategoryHabr($cat, $settings); ?>
            </div>
        <?php } else { ?><p style="font-size:13px;color:#6c6c6c">нет категорий</p><?php } ?>
    </div>
</section>
