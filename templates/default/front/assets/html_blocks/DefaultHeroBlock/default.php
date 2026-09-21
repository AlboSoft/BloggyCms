<?php
/**
 * Hero Block - Habr Pro Style
 * Строгий, минималистичный, без скруглений
 */
$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'left';
$hasImage = !empty($settings['image']);
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
        $rgb = hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
        $customStyles[] = '--accent-rgb: ' . $rgb;
    }
}
$paddingTop = (int)($settings['padding_top'] ?? 48);
$paddingBottom = (int)($settings['padding_bottom'] ?? 48);
$customStyles[] = '--padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--padding-bottom: ' . $paddingBottom . 'px';
$sectionClass = 'hero-character';
$sectionClass .= ' theme-' . $theme;
$sectionClass .= ' align-' . $align;
if(!empty($settings['custom_css_class'])) {
    $sectionClass .= ' ' . html($settings['custom_css_class']);
}
$imageHtml = '';
if($hasImage) {
    $imageUrl = BlockImageHelper::getImageUrl($settings['image']);
    $imageStyle = $settings['image_style'] ?? 'square';
    $imageHtml = '<img src="' . $imageUrl . '" class="hero-image ' . $imageStyle . '" alt="" loading="lazy">';
}
$imagePosition = $settings['image_position'] ?? 'right';
$contentOrder = $imagePosition === 'left' ? 'order-lg-2' : '';
$imageOrder = $imagePosition === 'left' ? 'order-lg-1' : '';
$contentColClass = $hasImage ? 'col-lg-7' : 'col-lg-8';
$imageColClass = $hasImage ? 'col-lg-5' : '';
$buttonsHtml = '';
if(!empty($settings['buttons'])) {
    $buttonsHtml .= '<div class="buttons">';
    foreach($settings['buttons'] as $index => $btn) {
        $btnClass = $index === 0 ? 'primary' : 'secondary';
        $buttonsHtml .= '<a href="' . html($btn['url']) . '" class="btn ' . $btnClass . '">' . html($btn['text']) . '</a>';
    }
    $buttonsHtml .= '</div>';
}
?>
<section id="<?= html($settings['custom_id'] ?? '') ?>" class="<?= $sectionClass ?>" style="<?= implode('; ', $customStyles) ?>">
    <div class="container">
        <div class="row">
            <div class="content-col <?= $contentColClass ?> <?= $contentOrder ?>">
                <?php if(!empty($settings['badge'])) { ?><div class="badge"><?= html($settings['badge']) ?></div><?php } ?>
                <?php if(!empty($settings['title'])) { ?><h1><?= $settings['title'] ?></h1><?php } ?>
                <?php if(!empty($settings['description'])) { ?><div class="description"><?= nl2br(html($settings['description'])) ?></div><?php } ?>
                <?= $buttonsHtml ?>
            </div>
            <?php if($hasImage) { ?>
            <div class="image-col <?= $imageColClass ?> <?= $imageOrder ?>"><?= $imageHtml ?></div>
            <?php } ?>
        </div>
    </div>
</section>
