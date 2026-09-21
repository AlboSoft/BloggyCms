<?php
/**
 * Footer Block - Habr Pro Style
 * Минималистичный футер без скруглений
 */
$logoUrl = $this->getLogoUrl($settings);
$logoAlt = html($settings['logo_alt'] ?? 'Логотип');
$siteName = html($settings['site_name'] ?? 'bloggy');
$siteDescription = html($settings['site_description'] ?? '');
$footerMenu1 = !empty($settings['footer_menu_1']) ? MenuRenderer::renderById($settings['footer_menu_1']) : '';
$footerMenu2 = !empty($settings['footer_menu_2']) ? MenuRenderer::renderById($settings['footer_menu_2']) : '';
$menu1Title = html($settings['menu_1_title'] ?? 'разделы');
$menu2Title = html($settings['menu_2_title'] ?? 'инфо');
$showRecentPosts = !empty($settings['show_recent_posts']);
$recentPostsTitle = html($settings['recent_posts_title'] ?? 'свежее');
$showRecentTags = !empty($settings['show_recent_tags']);
$recentTagsTitle = html($settings['recent_tags_title'] ?? 'теги');
$showCategories = !empty($settings['show_categories']);
$categoriesTitle = html($settings['categories_title'] ?? 'категории');
$categoriesStyle = $settings['categories_style'] ?? 'pills';
$categoriesShowCount = !empty($settings['categories_show_count']);
$showContacts = !empty($settings['show_contacts']);
$contactsTitle = html($settings['contacts_title'] ?? 'контакты');
$contactEmail = html($settings['contact_email'] ?? '');
$contactPhone = html($settings['contact_phone'] ?? '');
$contactAddress = html($settings['contact_address'] ?? '');
$socialLinks = is_array($settings['social_links'] ?? []) ? $settings['social_links'] : [];
$copyrightText = $settings['copyright_text'] ?? '© ' . date('Y') . ' ' . $siteName;
$footerLinks = is_array($settings['footer_links'] ?? []) ? $settings['footer_links'] : [];
$bgColor = $settings['background_color'] ?? '#ffffff';
$textColor = $settings['text_color'] ?? '#6c6c6c';
$accentColor = $settings['accent_color'] ?? '#000000';
$headingColor = $settings['heading_color'] ?? '#000000';

$recentPosts = $this->recentPosts ?? [];
$recentTags = $this->recentTags ?? [];
$categories = $this->categories ?? [];

$socialIcons = [
    'telegram' => ['icon' => 'telegram-plane', 'label' => 'Telegram'],
    'vk' => ['icon' => 'vk', 'label' => 'VK'],
    'youtube' => ['icon' => 'youtube', 'label' => 'YouTube'],
    'github' => ['icon' => 'github', 'label' => 'GitHub'],
    'twitter' => ['icon' => 'twitter', 'label' => 'X'],
    'instagram' => ['icon' => 'instagram', 'label' => 'Instagram'],
    'facebook' => ['icon' => 'facebook-square', 'label' => 'FB'],
    'linkedin' => ['icon' => 'linkedin', 'label' => 'LinkedIn'],
];
?>
<footer class="site-footer" style="background:<?= html($bgColor) ?>;color:<?= html($textColor) ?>;--footer-accent:<?= html($accentColor) ?>;--footer-heading:<?= html($headingColor) ?>">
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col footer-brand">
                    <?php if ($logoUrl) { ?>
                        <a href="/" class="footer-logo"><img src="<?= $logoUrl ?>" alt="<?= $logoAlt ?>" class="footer-logo-img"></a>
                    <?php } ?>
                    <h3 class="footer-site-name"><a href="/"><?= $siteName ?></a></h3>
                    <?php if ($siteDescription) { ?><p class="footer-description"><?= $siteDescription ?></p><?php } ?>
                    <?php if (!empty($socialLinks)) { ?>
                        <div class="footer-social">
                            <?php foreach ($socialLinks as $link) {
                                if (empty($link['network']) || empty($link['url'])) continue;
                                $iconData = $socialIcons[$link['network']] ?? null;
                                if (!$iconData) continue;
                            ?>
                            <a href="<?= html($link['url']) ?>" class="footer-social-link" target="_blank" rel="noopener noreferrer" aria-label="<?= $iconData['label'] ?>">
                                <?php if(function_exists('bloggy_icon')) echo bloggy_icon('brands', $iconData['icon'], '14 14', 'currentColor', ''); ?>
                            </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <?php if ($footerMenu1) { ?>
                    <div class="footer-col footer-menu">
                        <h4 class="footer-title"><?= $menu1Title ?></h4>
                        <nav class="footer-nav"><?= $footerMenu1 ?></nav>
                    </div>
                <?php } ?>
                <?php if ($footerMenu2) { ?>
                    <div class="footer-col footer-menu">
                        <h4 class="footer-title"><?= $menu2Title ?></h4>
                        <nav class="footer-nav"><?= $footerMenu2 ?></nav>
                    </div>
                <?php } ?>
                <?php if ($showRecentPosts || $showRecentTags) { ?>
                    <div class="footer-col footer-widgets">
                        <?php if ($showRecentPosts && !empty($recentPosts)) { ?>
                            <div class="footer-widget">
                                <h4 class="footer-title"><?= $recentPostsTitle ?></h4>
                                <ul class="footer-posts-list">
                                    <?php foreach ($recentPosts as $post) { ?>
                                        <li class="footer-post-item"><a href="/post/<?= html($post['slug'] ?? $post['id']) ?>" class="footer-post-link"><?= html($post['title'] ?? '') ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>
                        <?php if ($showRecentTags && !empty($recentTags)) { ?>
                            <div class="footer-widget">
                                <h4 class="footer-title"><?= $recentTagsTitle ?></h4>
                                <div class="footer-tags-cloud">
                                    <?php foreach ($recentTags as $tag) { ?>
                                    <a href="/tag/<?= html($tag['slug'] ?? '') ?>" class="footer-tag">#<?= html($tag['name'] ?? '') ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php if ($showCategories && !empty($categories)) { ?>
        <div class="footer-categories-bar">
            <div class="container">
                <div class="categories-bar-inner">
                    <?php if ($categoriesTitle) { ?><span class="categories-bar-label"><?= $categoriesTitle ?>:</span><?php } ?>
                    <div class="categories-bar-list">
                        <?php foreach ($categories as $category) {
                            $catUrl = '/category/' . html($category['slug'] ?? $category['id']);
                            $catName = html($category['name'] ?? '');
                            $catCount = $category['posts_count'] ?? 0;
                        ?>
                        <a href="<?= $catUrl ?>" class="category-pill"><?= $catName ?><?php if ($categoriesShowCount) { ?><span class="category-count"><?= $catCount ?></span><?php } ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if ($showContacts && ($contactEmail || $contactPhone || $contactAddress)) { ?>
        <div class="footer-contacts-bar">
            <div class="container">
                <div class="contacts-bar-inner">
                    <?php if ($contactsTitle) { ?><span class="contacts-bar-label"><?= $contactsTitle ?></span><?php } ?>
                    <div class="contacts-bar-list">
                        <?php if ($contactEmail) { ?><a href="mailto:<?= $contactEmail ?>" class="contact-item"><?= $contactEmail ?></a><?php } ?>
                        <?php if ($contactPhone) { ?><a href="tel:<?= preg_replace('/[^0-9+]/', '', $contactPhone) ?>" class="contact-item"><?= $contactPhone ?></a><?php } ?>
                        <?php if ($contactAddress) { ?><span class="contact-item"><?= $contactAddress ?></span><?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner">
                <div class="footer-copyright"><?= $copyrightText ?></div>
                <?php if (!empty($footerLinks)) { ?>
                    <nav class="footer-legal">
                        <?php foreach ($footerLinks as $link) {
                            if (empty($link['title']) || empty($link['url'])) continue;
                        ?>
                        <a href="<?= html($link['url']) ?>" class="footer-legal-link" target="<?= html($link['target'] ?? '_self') ?>"><?= html($link['title']) ?></a>
                        <?php } ?>
                    </nav>
                <?php } ?>
            </div>
        </div>
    </div>
</footer>
