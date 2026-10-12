<!DOCTYPE html>
<html lang="<?php echo html(substr((string)SettingsHelper::get('general', 'admin_language', 'ru_RU'), 0, 2)); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html($pageTitle) ?> - <?php echo LANG_ADMIN_PANEL_TITLE; ?></title>
    <meta name="generator" content="BloggyCMS">
    <meta name="author" content="Albo Soft">
    <meta name="copyright" content="© <?php echo date('Y'); ?> Albo Soft. <?php echo LANG_ADMIN_ALL_RIGHTS_RESERVED; ?>">
    <meta name="application-name" content="BloggyCms">
    <meta name="admin-language" content="<?php echo html(substr((string)SettingsHelper::get('general', 'admin_language', 'ru_RU'), 0, 2)); ?>">
    <?php echo favicon(); ?>
    <?php echo base_admin_css(['bootstrap', 'icons', 'main', 'actions', 'notifications']); ?>
    <link rel="stylesheet" href="/templates/default/admin/assets/css/controllers/docs.css">
    <?php echo render_admin_css(); ?>
    <?php
        $nOn = static function ($key, $default = true) {
            $value = \SettingsHelper::get('controller_admin', $key, $default ? 1 : 0);
            if ($value === true || $value === 1 || $value === '1') {
                return true;
            }
            if ($value === false || $value === 0 || $value === '0' || $value === null || $value === '') {
                return false;
            }
            return (bool) $default;
        };
        $adminNotifyConfig = [
            'position'     => SettingsHelper::get('controller_admin', 'notification_position', 'top-right') ?: 'top-right',
            'style'        => SettingsHelper::get('controller_admin', 'notification_style', 'aurora') ?: 'aurora',
            'animation'    => SettingsHelper::get('controller_admin', 'notification_animation', 'slide') ?: 'slide',
            'size'         => SettingsHelper::get('controller_admin', 'notification_size', 'normal') ?: 'normal',
            'duration'     => max(1, (int) SettingsHelper::get('controller_admin', 'notification_duration', 4)),
            'maxVisible'   => max(1, (int) SettingsHelper::get('controller_admin', 'notification_max_visible', 4)),
            'showProgress' => $nOn('notification_show_progress', true),
            'pauseOnHover' => $nOn('notification_pause_on_hover', true),
            'showIcon'     => $nOn('notification_show_icon', true),
            'showTitle'    => $nOn('notification_show_title', true),
            'sound'        => $nOn('notification_sound', false),
        ];
    ?>
    <script>
        window.BASE_URL = '<?php echo BASE_URL ?>';
        window.ADMIN_URL = '<?php echo ADMIN_URL ?>';
        window.ADMIN_NOTIFY_CONFIG = <?php echo json_encode($adminNotifyConfig); ?>;
    </script>
</head>
<body>
    <?php if(isset($_SESSION['user_id'])) { 
        $currentUser = $userModel->getById($_SESSION['user_id']);
        $currentUri = $_SERVER['REQUEST_URI'];
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        $adminPath = $basePath ? $basePath . '/admin/' : '/admin/';

        $relativeUri = str_replace($basePath, '', $currentUri);
        $pathParts = explode('/', trim($relativeUri, '/'));

        $currentSection = '';
        if (isset($pathParts[0]) && $pathParts[0] === 'admin') {
            $currentSection = $pathParts[1] ?? '';
        }

        if ($currentUri === ADMIN_URL . '/' || $currentUri === ADMIN_URL || empty($currentSection)) {
            $currentSection = 'posts';
        }
    ?>
        <div class="admin-wrapper">
            <aside class="sidebar">
                    <div class="sidebar-brand">
                        <div class="brand-row">
                            <a href="<?php echo ADMIN_URL ?>" class="brand-logo">
                                <?php 
                                $logoImage = SettingsHelper::get('controller_admin', 'logo_image');
                                if (!empty($logoImage)) { 
                                ?>
                                    <img src="<?php echo BASE_URL . '/uploads/settings/admin/' . $logoImage; ?>" 
                                        alt="<?php echo LANG_ADMIN_FAVICON_ALT; ?>"
                                        class="brand-image">
                                <?php } else { ?>
                                    <img src="/templates/default/admin/assets/img/logo-outline-light.png" 
                                        alt="<?php echo LANG_ADMIN_FAVICON_ALT; ?>">
                                <?php } ?>
                                
                                <?php if (SettingsHelper::get('controller_admin', 'show_site_name_in_logo', true)) {
                                    $customSiteName = trim((string)SettingsHelper::get('controller_admin', 'custom_site_name', ''));
                                    $siteName = !empty($customSiteName) ? $customSiteName : SettingsHelper::get('general', 'site_name', 'BloggyCMS');
                                ?>
                                    <span><?php echo html($siteName); ?></span>
                                <?php } else { ?>
                                    <span>BLOGGY<span class="cms-highlight">CMS</span></span>
                                <?php } ?>
                            </a>
                        </div>
                    </div>
                <nav class="nav flex-column">
                    <?php

                    $menuCounters = [];
                    if (class_exists('AdminController') && method_exists('AdminController', 'getMenuCountersStatic')) {
                        $menuCounters = AdminController::getMenuCountersStatic($db);
                    }

                    $menuItems = [
                        ['section' => 'posts', 'url' => ADMIN_URL . '/posts', 'icon' => 'file-text', 'title' => LANG_ADMIN_POSTS, 'priority' => 10, 'counter' => $menuCounters['posts'] ?? null],
                        ['section' => 'categories', 'url' => ADMIN_URL . '/categories', 'icon' => 'folder', 'title' => LANG_ADMIN_CATEGORIES, 'priority' => 20, 'counter' => $menuCounters['categories'] ?? null],
                        ['section' => 'tags', 'url' => ADMIN_URL . '/tags', 'icon' => 'tags', 'title' => LANG_ADMIN_TAGS, 'priority' => 30, 'counter' => $menuCounters['tags'] ?? null],
                        ['section' => 'comments', 'url' => ADMIN_URL . '/comments', 'icon' => 'chat-dots', 'title' => LANG_ADMIN_COMMENTS, 'priority' => 40, 'counter' => $menuCounters['comments'] ?? null],
                        ['section' => 'users', 'url' => ADMIN_URL . '/users', 'icon' => 'people', 'title' => LANG_ADMIN_USERS, 'priority' => 50, 'counter' => $menuCounters['users'] ?? null],
                        ['section' => 'pages', 'url' => ADMIN_URL . '/pages', 'icon' => 'file-earmark', 'title' => LANG_ADMIN_PAGES, 'priority' => 60, 'counter' => $menuCounters['pages'] ?? null],
                        ['section' => 'html-blocks', 'url' => ADMIN_URL . '/html-blocks', 'icon' => 'grid-1x2', 'title' => LANG_ADMIN_CONTENT_BLOCKS, 'priority' => 70, 'counter' => $menuCounters['html-blocks'] ?? null],
                        ['section' => 'post-blocks', 'url' => ADMIN_URL . '/post-blocks', 'icon' => 'bricks', 'title' => LANG_ADMIN_POST_BLOCKS, 'priority' => 80],
                        ['section' => 'fragments', 'url' => ADMIN_URL . '/fragments', 'icon' => 'puzzle', 'title' => LANG_ADMIN_FRAGMENTS, 'priority' => 90],
                        ['section' => 'fields', 'url' => ADMIN_URL . '/fields', 'icon' => 'input-cursor-text', 'title' => LANG_ADMIN_FIELDS, 'priority' => 100],
                        ['section' => 'menu', 'url' => ADMIN_URL . '/menu', 'icon' => 'view-list', 'title' => LANG_ADMIN_MENU_BUILDER, 'priority' => 110],
                        ['section' => 'icons', 'url' => ADMIN_URL . '/icons', 'icon' => 'emoji-heart-eyes', 'title' => LANG_ADMIN_ICONS, 'priority' => 120],
                        ['section' => 'seo', 'url' => ADMIN_URL . '/seo', 'icon' => 'rss', 'title' => LANG_ADMIN_SEO, 'priority' => 130],
                        ['section' => 'debug', 'url' => ADMIN_URL . '/debug', 'icon' => 'bug', 'title' => LANG_ADMIN_DEBUG, 'priority' => 140],
                        ['section' => 'settings', 'url' => ADMIN_URL . '/settings', 'icon' => 'gear', 'title' => LANG_ADMIN_SETTINGS, 'priority' => 150],
                    ];
                    
                    $menuItems = Event::filter('admin.menu.items', $menuItems);
                    
                    usort($menuItems, function($a, $b) {
                        $priorityA = $a['priority'] ?? 100;
                        $priorityB = $b['priority'] ?? 100;
                        return $priorityA <=> $priorityB;
                    });
                    
                    foreach ($menuItems as $item) {
                        echo admin_menu_item(
                            $item['section'],
                            $item['url'],
                            $item['icon'],
                            $item['title'],
                            $item['counter'] ?? null
                        );
                    }
                    ?>
                </nav>
            </aside>

            <main class="content-wrapper">
                <header class="top-bar">
                    <div class="topbar-left">
                        <div class="version-wrapper-top" title="BloggyCMS <?php echo html(VersionHelper::getVersion()); ?>">
                            <span class="version-status-dot"></span>
                            <span class="version-badge-top"><?php echo LANG_ADMIN_VERSION; ?> <strong><?php echo html(VersionHelper::getVersion()); ?></strong></span>
                        </div>

                        <div id="updateBadgePlaceholder">
                            <a href="<?php echo ADMIN_URL ?>/check-updates" class="topbar-update-pill" title="<?php echo LANG_ADMIN_CHECK_UPDATES; ?>">
                                <span class="topbar-update-icon">
                                    <?php echo bloggy_icon('bs', 'arrow-repeat', '14', 'currentColor'); ?>
                                </span>
                                <span class="topbar-update-text"><?php echo LANG_ADMIN_CHECK_UPDATES; ?></span>
                            </a>
                        </div>

                        <a href="<?php echo BASE_URL ?>" target="_blank" class="topbar-site-pill" title="<?php echo LANG_ADMIN_GO_TO_SITE; ?>">
                            <span class="topbar-site-icon">
                                <?php echo bloggy_icon('bs', 'box-arrow-up-right', '13', 'currentColor'); ?>
                            </span>
                            <span class="topbar-site-text"><?php echo LANG_ADMIN_GO_TO_SITE; ?></span>
                        </a>
                    </div>

                    <div class="topbar-right">
                        <nav class="topbar-actions" aria-label="Quick navigation">
                            <a href="<?php echo ADMIN_URL ?>/addons"
                               class="admin-header-btn admin-btn-addons <?php echo $currentSection === 'addons' ? 'is-active' : ''; ?>"
                               title="<?php echo LANG_ADMIN_PACKAGES; ?>">
                                <div class="btn-icon-wrapper">
                                    <?php echo bloggy_icon('bs', 'box', '15 15', 'currentColor') ?>
                                </div>
                                <span class="btn-text"><?php echo LANG_ADMIN_PACKAGES; ?></span>
                            </a>

                            <a href="<?php echo ADMIN_URL ?>/controllers"
                               class="admin-header-btn admin-btn-controllers-nav <?php echo $currentSection === 'controllers' ? 'is-active' : ''; ?>"
                               title="<?php echo LANG_ADMIN_CONTROLLERS; ?>">
                                <div class="btn-icon-wrapper">
                                    <?php echo bloggy_icon('bs', 'cpu', '15 15', 'currentColor') ?>
                                </div>
                                <span class="btn-text"><?php echo LANG_ADMIN_CONTROLLERS; ?></span>
                            </a>

                            <a href="<?php echo ADMIN_URL ?>/docs"
                               class="admin-header-btn admin-btn-docs <?php echo $currentSection === 'docs' ? 'is-active' : ''; ?>"
                               title="<?php echo LANG_DOCS_HEADER_BTN_TITLE; ?>">
                                <div class="btn-icon-wrapper">
                                    <?php echo bloggy_icon('bs', 'book-half', '15 15', 'currentColor') ?>
                                </div>
                                <span class="btn-text"><?php echo LANG_DOCS_MENU_TITLE; ?></span>
                            </a>
                        </nav>

                        <span class="topbar-divider" aria-hidden="true"></span>

                        <div class="dropdown topbar-notifications-wrap">
                            <a href="#"
                               class="admin-header-btn admin-btn-notifications dropdown-toggle"
                               data-bs-toggle="dropdown"
                               data-bs-auto-close="outside"
                               data-notifications-count="0"
                               aria-label="<?php echo LANG_ADMIN_NOTIFICATIONS; ?>"
                               title="<?php echo LANG_ADMIN_NOTIFICATIONS; ?>">
                                <div class="btn-icon-wrapper">
                                    <i class="bi bi-bell bell-icon"></i>
                                    <span class="notification-ring-wave" aria-hidden="true"></span>
                                    <span class="notification-badge" style="display: none;"></span>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end notifications-dropdown-menu shadow-lg p-0">
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-bell-fill"></i>
                                        <h6 class="mb-0"><?php echo LANG_ADMIN_NOTIFICATIONS; ?></h6>
                                    </div>
                                    <a href="<?php echo ADMIN_URL ?>/notifications" class="notifications-header-link">
                                        <?php echo LANG_ADMIN_ALL_NOTIFICATIONS; ?> <i class="bi bi-chevron-right"></i>
                                    </a>
                                </div>
                                <div class="dropdown-body" style="max-height: 380px; overflow-y: auto;">
                                    <div id="notifications-dropdown-content" class="p-3">
                                        <div class="text-center py-3">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden"><?php echo LANG_ADMIN_LOADING_NOTIFICATIONS; ?></span>
                                            </div>
                                            <p class="text-muted mt-2 mb-0 small"><?php echo LANG_ADMIN_LOADING_NOTIFICATIONS; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-footer p-2 border-top">
                                    <div class="d-flex justify-content-between gap-2">
                                        <a href="#" class="btn btn-sm btn-outline-success flex-fill justify-content-center" id="mark-all-read-dropdown">
                                            <i class="bi bi-check2-all me-1"></i> <?php echo LANG_ADMIN_MARK_ALL_READ; ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL ?>/notifications" class="btn btn-sm btn-outline-primary flex-fill justify-content-center">
                                            <i class="bi bi-arrow-right me-1"></i> <?php echo LANG_ADMIN_GO_TO_NOTIFICATIONS; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown topbar-profile-wrap">
                            <a href="#"
                               class="topbar-profile-btn dropdown-toggle"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <span class="topbar-avatar-wrap">
                                    <?php if(!empty($currentUser['avatar']) && $currentUser['avatar'] !== 'default.jpg') { ?>
                                        <img src="<?php echo BASE_URL ?>/uploads/avatars/<?php echo $currentUser['avatar'] ?>" 
                                             class="topbar-avatar-img"
                                             alt="<?php echo html($currentUser['username']) ?>">
                                    <?php } else { ?>
                                        <span class="topbar-avatar-fallback">
                                            <i class="bi bi-person"></i>
                                        </span>
                                    <?php } ?>
                                    <span class="topbar-avatar-status"></span>
                                </span>
                                <span class="topbar-user-info">
                                    <span class="topbar-user-name"><?php echo html($currentUser['display_name'] ?: $currentUser['username']) ?></span>
                                </span>
                                <i class="bi bi-chevron-down topbar-profile-caret"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end topbar-profile-menu shadow-lg">
                                <li class="topbar-profile-header">
                                    <div class="fw-semibold text-dark text-truncate"><?php echo html($currentUser['display_name'] ?: $currentUser['username']) ?></div>
                                    <div class="text-muted small text-truncate">@<?php echo html($currentUser['username']) ?></div>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?php echo ADMIN_URL ?>/users/edit/<?php echo $currentUser['id'] ?>">
                                        <i class="bi bi-pencil-square me-2 text-primary"></i> <?php echo LANG_ADMIN_EDIT_PROFILE; ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?php echo BASE_URL ?>/profile/<?php echo html($currentUser['username']) ?>" target="_blank">
                                        <i class="bi bi-person-circle me-2 text-info"></i> <?php echo LANG_ADMIN_MY_PUBLIC_PROFILE; ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?php echo BASE_URL ?>" target="_blank">
                                        <i class="bi bi-globe2 me-2 text-success"></i> <?php echo LANG_ADMIN_GO_TO_SITE; ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center text-danger" href="<?php echo ADMIN_URL ?>/logout">
                                        <i class="bi bi-box-arrow-right me-2"></i> <?php echo LANG_ADMIN_LOGOUT; ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

                <?php if (isset($breadcrumbs) && $breadcrumbs instanceof BreadcrumbsManager && !$breadcrumbs->isEmpty()) { ?>
                    <div class="breadcrumb-wrapper-modern">
                        <?php echo $breadcrumbs->render([
                            'container_tag' => 'div',
                            'container_class' => 'admin-breadcrumb-modern',
                            'list_tag' => 'ol',
                            'list_class' => 'breadcrumb',
                            'item_tag' => 'li',
                            'item_class' => 'breadcrumb-item',
                            'active_class' => 'active',
                            'home_icon' => 'house-door'
                        ]); ?>
                    </div>
                <?php } ?>

                <div class="container-fluid px-5">
                    <?php echo $content ?>
                </div>
            </main>
        </div>
    <?php } else { ?>
        <?php echo $content ?>
    <?php } ?>

    <div id="admin-notify-root" class="admin-notify-stack" aria-live="polite" aria-relevant="additions"></div>

    <?php if(isset($_SESSION['user_id'])) { ?>
        <?php echo QuickActionsHelper::renderQuickActions() ?>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="docsOffcanvasDrawer" aria-labelledby="docsDrawerTitle">
            <div class="offcanvas-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <?php echo bloggy_icon('bs', 'book-half', '20', '#0d6efd'); ?>
                    <h5 class="offcanvas-title mb-0 fs-6 fw-bold" id="docsDrawerTitle"><?php echo LANG_DOCS_DRAWER_TITLE; ?></h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?php echo ADMIN_URL; ?>/docs" id="docsDrawerFullLink" class="btn btn-sm btn-outline-primary py-1 px-2 d-flex align-items-center" title="<?php echo LANG_DOCS_DRAWER_OPEN_FULL; ?>">
                        <?php echo bloggy_icon('bs', 'box-arrow-up-right', '14', null, 'me-1'); ?>
                        <span class="small"><?php echo LANG_DOCS_DRAWER_OPEN_FULL; ?></span>
                    </a>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="<?php echo LANG_DOCS_DRAWER_CLOSE; ?>"></button>
                </div>
            </div>
            <div class="offcanvas-body" id="docsDrawerBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php echo LANG_DOCS_DRAWER_LOADING; ?></span>
                    </div>
                    <p class="text-muted mt-2 mb-0"><?php echo LANG_DOCS_DRAWER_LOADING; ?></p>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php echo base_admin_js(['jquery-3.6.0.min', 'bootstrap', 'Sortable.min', 'main', 'jquery-ui.min', 'notifications', 'actions']); ?>
    <script src="/templates/default/admin/assets/js/controllers/docs-drawer.js"></script>
    <?php echo render_admin_js(); ?>
    <?php echo render_admin_bottom_js() ?>
    
    <?php if(isset($_SESSION['toast'])) { ?>
        <div id="notification-data" data-message="<?php echo html($_SESSION['toast']['message']) ?>" data-type="<?php echo html($_SESSION['toast']['type']) ?>" style="display: none;"></div>
        <?php unset($_SESSION['toast']); ?>
    <?php } ?>
</body>
</html>