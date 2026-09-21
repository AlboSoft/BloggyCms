<?php
/**
 * Services Block - Habr Pro
 */
$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'left';
$columns = $settings['columns'] ?? '3';

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

$sectionClass = 'services-character theme-' . $theme . ' align-' . $align;
if(!empty($settings['custom_css_class'])) $sectionClass .= ' ' . html($settings['custom_css_class']);

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
        <?php if(!empty($settings['badge']) || !empty($settings['title']) || !empty($settings['description'])) { ?>
        <div class="header">
            <?php if(!empty($settings['badge'])) { ?><div class="badge"><?= html($settings['badge']) ?></div><?php } ?>
            <?php if(!empty($settings['title'])) { ?><h2><?= $settings['title'] ?></h2><?php } ?>
            <?php if(!empty($settings['description'])) { ?><div class="header-description"><?= nl2br(html($settings['description'])) ?></div><?php } ?>
        </div>
        <?php } ?>
        <?php if(!empty($settings['services'])) { ?>
            <div class="services-grid cols-<?= $columns ?>">
                <?php foreach($settings['services'] as $service) { ?>
                    <div class="service-card">
                        <?php if(!empty($service['image'])) { $img = BlockImageHelper::getImageUrl($service['image']); ?>
                            <div class="service-image"><img src="<?= $img ?>" alt="<?= html($service['title']) ?>" loading="lazy"></div>
                        <?php } ?>
                        <?php if(!empty($service['title'])) { ?><h3 class="service-title"><?= html($service['title']) ?></h3><?php } ?>
                        <?php if(!empty($service['description'])) { ?><div class="service-description"><?= nl2br(html($service['description'])) ?></div><?php } ?>
                        <?php if(!empty($service['price'])) { ?><div class="service-price"><?= html($service['price']) ?></div><?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?= $buttonsHtml ?>
    </div>
</section>
