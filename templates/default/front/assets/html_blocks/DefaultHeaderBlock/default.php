<?php
/**
 * Header Block - Habr Pro Style
 * Минималистичный, без скруглений, с четкой типографикой
 */
$theme = $settings['theme'] ?? 'light';
$logoUrl = !empty($settings['logo_path']) ? BlockImageHelper::getImageUrl($settings['logo_path']) : '';
$logoAlt = html($settings['logo_alt'] ?? 'Логотип');
$siteName = html($settings['site_name'] ?? 'bloggy');
$logoLink = html($settings['logo_link'] ?? '/');
$showSiteName = !empty($settings['show_site_name']);
$mainMenuId = $settings['main_menu_id'] ?? '';
$profileMenuId = $settings['profile_menu_id'] ?? '';
$showSearch = !empty($settings['show_search']);
$searchPlaceholder = html($settings['search_placeholder'] ?? 'Поиск');
$searchPage = html($settings['search_page'] ?? '/search');
$stickyHeader = !empty($settings['sticky_header']);
$showShadow = !empty($settings['show_shadow']);
$containerClass = $settings['container_type'] ?? 'container';
$heightClass = 'header-height--' . ($settings['header_height'] ?? 'md');
$mobileBreakpoint = (int)($settings['mobile_breakpoint'] ?? 992);
$headerClasses = ['site-header', "site-header--{$theme}", $heightClass];
if ($stickyHeader) $headerClasses[] = 'is-sticky';
if ($showShadow) $headerClasses[] = 'has-shadow';

$isLoggedIn = isset($_SESSION['user_id']);
$currentUserId = $_SESSION['user_id'] ?? null;
$userDisplayName = '';
$userAvatarUrl = BASE_URL . '/uploads/avatars/default.jpg';
$userProfileUrl = '/profile';
if ($isLoggedIn && $currentUserId) {
    try {
        $db = DatabaseRegistry::getDb();
        $user = (new UserModel($db))->getById($currentUserId);
        if ($user) {
            $userDisplayName = html($user['display_name'] ?? $user['username'] ?? 'юзер');
            $userProfileUrl = '/profile/' . $user['username'];
            if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') {
                $avatar = $user['avatar'];
                $userAvatarUrl = strpos($avatar, 'http') === 0 ? $avatar : BASE_URL . '/uploads/avatars/' . $avatar;
            }
        }
    } catch (Exception $e) {}
}

$mainMenuHtml = !empty($mainMenuId) ? MenuRenderer::renderById($mainMenuId) : '';
$profileMenuHtml = !empty($profileMenuId) ? MenuRenderer::renderById($profileMenuId) : '';
$mainMenuHtml = str_replace('<a href="#"', '<a href="#" onclick="return false;"', $mainMenuHtml);
$profileMenuHtml = str_replace('<a href="#"', '<a href="#" onclick="return false;"', $profileMenuHtml);
?>
<header class="<?= implode(' ', $headerClasses) ?>" data-header data-mobile-breakpoint="<?= $mobileBreakpoint ?>">
    <div class="<?= $containerClass ?>">
        <div class="header__inner">
            <div class="header__brand">
                <a href="<?= $logoLink ?>" class="header__logo-link" aria-label="<?= $siteName ?>">
                    <?php if ($logoUrl) { ?>
                    <img src="<?= $logoUrl ?>" alt="<?= $logoAlt ?>" class="header__logo-img" width="120" height="28" fetchpriority="high" decoding="async">
                    <?php } ?>
                    <?php if ($showSiteName || !$logoUrl) { ?><span class="header__site-title"><?= $siteName ?></span><?php } ?>
                </a>
            </div>
            <button class="header__burger" type="button" data-burger aria-label="Меню" aria-expanded="false">
                <span class="header__burger-line"></span><span class="header__burger-line"></span><span class="header__burger-line"></span>
            </button>
            <nav class="header__nav" data-nav>
                <div class="header__nav-inner">
                    <?= $mainMenuHtml ?>
                    <?php if (!$isLoggedIn) { ?>
                    <div class="header__mobile-auth">
                        <a href="/login" class="header__btn header__btn--primary">войти</a>
                        <a href="/register" class="header__btn header__btn--outline">регистрация</a>
                    </div>
                    <?php } ?>
                </div>
            </nav>
            <div class="header__actions">
                <?php if ($showSearch) { ?>
                <button class="header__icon-btn" type="button" data-search-toggle aria-label="Поиск" aria-expanded="false"><?= bloggy_icon('bs', 'search', '16 16') ?></button>
                <?php } ?>
                <?php if ($isLoggedIn) { ?>
                <div class="header__profile" data-profile>
                    <button class="header__profile-btn" type="button" data-profile-toggle aria-label="Профиль" aria-expanded="false">
                        <span class="header__avatar-wrapper"><img src="<?= $userAvatarUrl ?>" alt="" class="header__avatar" loading="lazy"></span>
                        <span class="header__username"><?= $userDisplayName ?></span>
                        <?= bloggy_icon('bs', 'chevron-down', '12 12', 'currentColor', 'header__chevron') ?>
                    </button>
                    <?php if ($profileMenuHtml) { ?>
                    <div class="header__dropdown" data-profile-menu>
                        <div class="header__dropdown-header">
                            <span class="header__dropdown-name"><?= $userDisplayName ?></span>
                            <a href="<?= $userProfileUrl ?>" class="header__dropdown-link">профиль</a>
                        </div>
                        <div class="header__dropdown-divider"></div>
                        <?= $profileMenuHtml ?>
                    </div>
                    <?php } ?>
                </div>
                <?php } else { ?>
                <div class="header__desktop-auth"><a href="/login" class="header__btn header__btn--primary">войти</a></div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php if ($showSearch) { ?>
    <div class="header__search-panel" data-search-panel hidden>
        <div class="<?= $containerClass ?>">
            <form action="<?= $searchPage ?>" method="get" class="header__search-form">
                <div class="header__search-input-wrapper">
                    <?= bloggy_icon('bs', 'search', '14 14', 'currentColor', 'header__search-icon') ?>
                    <input type="text" name="q" class="header__search-input" placeholder="<?= $searchPlaceholder ?>" value="<?= html($_GET['q'] ?? '') ?>" autocomplete="off">
                </div>
                <button type="submit" class="header__search-submit">найти</button>
            </form>
        </div>
    </div>
    <?php } ?>
</header>
