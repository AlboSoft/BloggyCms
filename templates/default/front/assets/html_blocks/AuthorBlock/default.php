<?php
/**
 * Author Block - Habr Pro - минималистичный
 */
?>
<div class="tg-author-card <?php echo $settings['custom_css_class'] ?? ''; ?>" <?php if (!empty($settings['custom_id'])) { ?>id="<?php echo $settings['custom_id']; ?>"<?php } ?> style="padding-bottom: <?php echo (int)($settings['padding_bottom'] ?? 16); ?>px">
    <div class="tg-author-card-inner">
        <?php if (!empty($this->avatarUrl)) { ?>
            <div class="tg-author-avatar tg-avatar-<?php echo $settings['avatar_style'] ?? 'square'; ?>">
                <img src="<?php echo $this->avatarUrl; ?>" alt="<?php echo html($settings['name'] ?? 'Автор'); ?>" loading="lazy">
            </div>
        <?php } ?>
        <?php if (!empty($settings['name'])) { ?><h3 class="tg-author-name"><?php echo html($settings['name']); ?></h3><?php } ?>
        <?php if (!empty($settings['role'])) { ?><div class="tg-author-role"><?php echo html($settings['role']); ?></div><?php } ?>
        <?php if (!empty($settings['description'])) { ?><div class="tg-author-description"><?php echo nl2br(html($settings['description'])); ?></div><?php } ?>
        <?php if (!empty($this->socialLinks)) { ?>
            <div class="tg-author-social">
                <?php foreach ($this->socialLinks as $link) { $network = $link['network']; $url = $link['url']; ?>
                    <a href="<?php echo html($url); ?>" class="tg-social-link" target="_blank" rel="noopener noreferrer" title="<?php echo ucfirst($network); ?>">↗</a>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($settings['email']) || !empty($settings['phone']) || !empty($settings['website'])) { ?>
            <div class="tg-author-contacts">
                <?php if (!empty($settings['email'])) { ?><a href="mailto:<?php echo html($settings['email']); ?>" class="tg-contact-link"><?php echo html($settings['email']); ?></a><?php } ?>
                <?php if (!empty($settings['website'])) { ?><a href="<?php echo html($settings['website']); ?>" class="tg-contact-link" target="_blank" rel="noopener"><?php echo preg_replace('#^https?://#', '', html($settings['website'])); ?></a><?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($settings['show_button']) && !empty($settings['button_text'])) { ?>
            <div class="tg-author-button"><a href="<?php echo html($settings['button_url'] ?? '#'); ?>" class="tg-btn tg-btn-primary" target="<?php echo $settings['button_target'] ?? '_self'; ?>"><?php echo html($settings['button_text']); ?></a></div>
        <?php } ?>
    </div>
</div>
