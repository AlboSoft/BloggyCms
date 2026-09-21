<?php
/**
 * Feedback Block - Habr Pro
 */
$layout = $settings['layout'] ?? 'form-left';
$theme = $settings['theme'] ?? 'light';
$showContactInfo = !empty($settings['show_contact_info']);
$showFormTitle = !empty($settings['show_form_title']);
$formExists = $settings['form_exists'] ?? false;
$formHtml = $settings['form_html'] ?? '';

$customStyles = [];
if ($theme === 'custom') {
    if (!empty($settings['background_color'])) $customStyles[] = '--fb-bg-color: ' . html($settings['background_color']);
    if (!empty($settings['text_color'])) $customStyles[] = '--fb-text-color: ' . html($settings['text_color']);
}
if (!empty($settings['accent_color'])) $customStyles[] = '--fb-accent: ' . html($settings['accent_color']);
$paddingTop = (int)($settings['padding_top'] ?? 32);
$paddingBottom = (int)($settings['padding_bottom'] ?? 32);
$customStyles[] = '--fb-padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--fb-padding-bottom: ' . $paddingBottom . 'px';

$sectionClass = 'feedback-block theme-' . $theme . ' layout-' . $layout;
if (!empty($settings['custom_css_class'])) $sectionClass .= ' ' . html($settings['custom_css_class']);
?>
<section id="<?= html($settings['custom_id'] ?? '') ?>" class="<?= $sectionClass ?>" style="<?= implode('; ', $customStyles) ?>">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px" class="feedback-grid">
            <div class="feedback-form-card">
                <?php if ($showFormTitle && !empty($settings['form_title'])) { ?><h3 class="feedback-form-title"><?= html($settings['form_title']) ?></h3><?php } ?>
                <?php if ($formExists && !empty($formHtml)) { ?><?= $formHtml ?><?php } else { ?><div class="tg-alert tg-alert-warning">форма не выбрана</div><?php } ?>
            </div>
            <?php if ($showContactInfo) { ?>
            <div class="feedback-info-content">
                <?php if (!empty($settings['contact_title'])) { ?><h3 class="feedback-info-title"><?= html($settings['contact_title']) ?></h3><?php } ?>
                <?php if (!empty($settings['contact_description'])) { ?><p class="feedback-info-description"><?= nl2br(html($settings['contact_description'])) ?></p><?php } ?>
                <div class="feedback-contacts">
                    <?php if (!empty($settings['contact_phone'])) { ?><div class="feedback-contact-item"><div class="feedback-contact-icon">☎</div><div class="feedback-contact-content"><span class="feedback-contact-label">телефон</span><a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings['contact_phone']) ?>" class="feedback-contact-value"><?= html($settings['contact_phone']) ?></a></div></div><?php } ?>
                    <?php if (!empty($settings['contact_email'])) { ?><div class="feedback-contact-item"><div class="feedback-contact-icon">@</div><div class="feedback-contact-content"><span class="feedback-contact-label">email</span><a href="mailto:<?= html($settings['contact_email']) ?>" class="feedback-contact-value"><?= html($settings['contact_email']) ?></a></div></div><?php } ?>
                    <?php if (!empty($settings['contact_address'])) { ?><div class="feedback-contact-item"><div class="feedback-contact-icon">⌖</div><div class="feedback-contact-content"><span class="feedback-contact-label">адрес</span><span class="feedback-contact-value"><?= html($settings['contact_address']) ?></span></div></div><?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<style>@media(max-width:768px){.feedback-grid{grid-template-columns:1fr !important}}</style>
