<!DOCTYPE html>
<html lang="<?php echo html(substr(SettingsHelper::get('general', 'admin_language', 'ru_RU'), 0, 2)); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html($pageTitle) ?> - <?php echo LANG_ADMIN_PANEL_TITLE; ?></title>
    <meta name="generator" content="BloggyCMS">
    <meta name="author" content="Albo Soft">
    <meta name="copyright" content="© <?php echo date('Y'); ?> Albo Soft. <?php echo LANG_ADMIN_ALL_RIGHTS_RESERVED; ?>">
    <meta name="application-name" content="BloggyCms">
    <meta name="admin-language" content="<?php echo html(substr(SettingsHelper::get('general', 'admin_language', 'ru_RU'), 0, 2)); ?>">
    <?php echo favicon(); ?>
    <?php echo base_admin_css(['bootstrap', 'icons', 'main']); ?>
    <?php echo render_admin_css(); ?>
    <script>window.BASE_URL = '<?php echo BASE_URL ?>'; window.ADMIN_URL = '<?php echo ADMIN_URL ?>';</script>
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
            <aside class="sidebar" <?php if(!empty(SettingsHelper::get('controller_admin', 'bg_panel'))) { ?>style="background-size: cover; background-image: url('<?php echo BASE_URL ?>/uploads/settings/admin/<?php echo SettingsHelper::get('controller_admin', 'bg_panel') ?>')"<?php } ?>>
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
                                    $customSiteName = trim(SettingsHelper::get('controller_admin', 'custom_site_name', ''));
                                    $siteName = !empty($customSiteName) ? $customSiteName : SettingsHelper::get('general', 'site_name', 'BloggyCMS');
                                ?>
                                    <span><?php echo html($siteName); ?></span>
                                <?php } else { ?>
                                    <span>BLOGGY<span class="cms-highlight">CMS</span></span>
                                <?php } ?>
                            </a>
                            
                            <div class="version-wrapper">
                                <span class="version-badge"><?php echo LANG_ADMIN_VERSION; ?> <?php echo VersionHelper::getVersion(); ?></span>
                                <a href="/admin/check-updates" class="version-update" title="<?php echo LANG_ADMIN_CHECK_UPDATES; ?>">
                                    <?php echo bloggy_icon('bs', 'arrow-repeat', '14', '#ffffff'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <nav class="nav flex-column">
                    <?php
                    $menuItems = [
                        ['section' => 'posts', 'url' => ADMIN_URL . '/posts', 'icon' => 'file-text', 'title' => LANG_ADMIN_POSTS, 'priority' => 10],
                        ['section' => 'categories', 'url' => ADMIN_URL . '/categories', 'icon' => 'folder', 'title' => LANG_ADMIN_CATEGORIES, 'priority' => 20],
                        ['section' => 'tags', 'url' => ADMIN_URL . '/tags', 'icon' => 'tags', 'title' => LANG_ADMIN_TAGS, 'priority' => 30],
                        ['section' => 'comments', 'url' => ADMIN_URL . '/comments', 'icon' => 'chat-dots', 'title' => LANG_ADMIN_COMMENTS, 'priority' => 40],
                        ['section' => 'users', 'url' => ADMIN_URL . '/users', 'icon' => 'people', 'title' => LANG_ADMIN_USERS, 'priority' => 50],
                        ['section' => 'pages', 'url' => ADMIN_URL . '/pages', 'icon' => 'file-earmark', 'title' => LANG_ADMIN_PAGES, 'priority' => 60],
                        ['section' => 'html-blocks', 'url' => ADMIN_URL . '/html-blocks', 'icon' => 'grid-1x2', 'title' => LANG_ADMIN_CONTENT_BLOCKS, 'priority' => 70],
                        ['section' => 'post-blocks', 'url' => ADMIN_URL . '/post-blocks', 'icon' => 'bricks', 'title' => LANG_ADMIN_POST_BLOCKS, 'priority' => 80],
                        ['section' => 'fragments', 'url' => ADMIN_URL . '/fragments', 'icon' => 'puzzle', 'title' => LANG_ADMIN_FRAGMENTS, 'priority' => 90],
                        ['section' => 'fields', 'url' => ADMIN_URL . '/fields', 'icon' => 'input-cursor-text', 'title' => LANG_ADMIN_FIELDS, 'priority' => 100],
                        ['section' => 'menu', 'url' => ADMIN_URL . '/menu', 'icon' => 'view-list', 'title' => LANG_ADMIN_MENU_BUILDER, 'priority' => 110],
                        ['section' => 'forms', 'url' => ADMIN_URL . '/forms', 'icon' => 'mailbox', 'title' => LANG_ADMIN_FORM_BUILDER, 'priority' => 120],
                        ['section' => 'icons', 'url' => ADMIN_URL . '/icons', 'icon' => 'emoji-heart-eyes', 'title' => LANG_ADMIN_ICONS, 'priority' => 130],
                        ['section' => 'seo', 'url' => ADMIN_URL . '/seo', 'icon' => 'rss', 'title' => LANG_ADMIN_SEO, 'priority' => 140],
                        ['section' => 'debug', 'url' => ADMIN_URL . '/debug', 'icon' => 'bug', 'title' => LANG_ADMIN_DEBUG, 'priority' => 145],
                        ['section' => 'settings', 'url' => ADMIN_URL . '/settings', 'icon' => 'gear', 'title' => LANG_ADMIN_SETTINGS, 'priority' => 150],
                        ['section' => 'templates', 'url' => ADMIN_URL . '/templates', 'icon' => 'palette', 'title' => LANG_ADMIN_TEMPLATE, 'priority' => 160],
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
                            $item['title']
                        );
                    }
                    ?>
                </nav>
            </aside>

            <main class="content-wrapper">
                <div class="top-bar d-flex justify-content-between align-items-center">
                    <div class="breadcrumb-wrapper"></div>
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center me-3" style="gap: 10px;">
                            <div class="dropdown">
                                <a href="#" class="admin-header-btn admin-btn-notifications d-flex align-items-center dropdown-toggle" 
                                data-bs-toggle="dropdown" 
                                data-notifications-count="0"
                                title="<?php echo LANG_ADMIN_NOTIFICATIONS; ?>">
                                    <div class="btn-icon-wrapper">
                                        <i class="bi bi-bell"></i>
                                        <span class="notification-badge" style="display: none;"></span>
                                    </div>
                                    <span class="btn-text"><?php echo LANG_ADMIN_NOTIFICATIONS; ?></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end shadow-lg p-0" style="min-width: 320px;">
                                    <div class="dropdown-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><?php echo LANG_ADMIN_NOTIFICATIONS; ?></h6>
                                        <a href="<?php echo ADMIN_URL ?>/notifications" class="text-white text-decoration-none">
                                            <small><?php echo LANG_ADMIN_ALL_NOTIFICATIONS; ?></small>
                                        </a>
                                    </div>
                                    <div class="dropdown-body" style="max-height: 400px; overflow-y: auto;">
                                        <div id="notifications-dropdown-content" class="p-3">
                                            <div class="text-center py-3">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                    <span class="visually-hidden"><?php echo LANG_ADMIN_LOADING_NOTIFICATIONS; ?></span>
                                                </div>
                                                <p class="text-muted mt-2 mb-0"><?php echo LANG_ADMIN_LOADING_NOTIFICATIONS; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown-footer p-2 border-top">
                                        <div class="d-flex justify-content-between">
                                            <a href="#" class="btn btn-sm btn-outline-success" id="mark-all-read-dropdown">
                                                <i class="bi bi-check2-all me-1"></i> <?php echo LANG_ADMIN_MARK_ALL_READ; ?>
                                            </a>
                                            <a href="<?php echo ADMIN_URL ?>/notifications" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-arrow-right me-1"></i> <?php echo LANG_ADMIN_GO_TO_NOTIFICATIONS; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <a href="<?php echo ADMIN_URL ?>/addons" class="admin-header-btn admin-btn-addons d-flex align-items-center" title="<?php echo LANG_ADMIN_PACKAGES; ?>">
                                <div class="btn-icon-wrapper">
                                    <?php echo bloggy_icon('bs', 'box', '16 16', '#28a745') ?>
                                </div>
                                <span class="btn-text"><?php echo LANG_ADMIN_PACKAGES; ?></span>
                            </a>

                            <a href="<?php echo ADMIN_URL ?>/controllers" class="admin-header-btn admin-btn-addons d-flex align-items-center" title="<?php echo LANG_ADMIN_CONTROLLERS; ?>">
                                <div class="btn-icon-wrapper">
                                    <?php echo bloggy_icon('bs', 'cpu', '16 16', '#7b1fa2') ?>
                                </div>
                                <span class="btn-text"><?php echo LANG_ADMIN_CONTROLLERS; ?></span>
                            </a>
                        </div>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if(!empty($currentUser['avatar']) && $currentUser['avatar'] !== 'default.jpg') { ?>
                                    <img src="<?php echo BASE_URL ?>/uploads/avatars/<?php echo $currentUser['avatar'] ?>" 
                                         class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px; object-fit: cover;"
                                         alt="<?php echo html($currentUser['username']) ?>">
                                <?php } else { ?>
                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-secondary text-white" 
                                         style="width: 32px; height: 32px;">
                                        <i class="bi bi-person" style="font-size: 16px;"></i>
                                    </div>
                                <?php } ?>
                                <span class="text-muted">
                                    <?php echo html($currentUser['display_name'] ?: $currentUser['username']) ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?php echo ADMIN_URL ?>/users/edit/<?php echo $currentUser['id'] ?>">
                                        <i class="bi bi-pencil me-2"></i> <?php echo LANG_ADMIN_EDIT_PROFILE; ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?php echo BASE_URL ?>/profile/<?php echo html($currentUser['username']) ?>" target="_blank">
                                        <i class="bi bi-person-circle me-2"></i> <?php echo LANG_ADMIN_MY_PUBLIC_PROFILE; ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?php echo BASE_URL ?>" target="_blank">
                                        <i class="bi bi-eye me-2"></i> <?php echo LANG_ADMIN_GO_TO_SITE; ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center text-danger" href="<?php echo ADMIN_URL ?>/logout">
                                        <i class="bi bi-box-arrow-right me-2"></i> <?php echo LANG_ADMIN_LOGOUT; ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php if (isset($breadcrumbs) && $breadcrumbs instanceof BreadcrumbsManager && !$breadcrumbs->isEmpty()): ?>
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
                <?php endif; ?>

                <div class="container-fluid px-5">
                    <?php echo $content ?>
                </div>
            </main>
        </div>
    <?php } else { ?>
        <?php echo $content ?>
    <?php } ?>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="<?php echo LANG_ADMIN_TOAST_CLOSE; ?>"></button>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['user_id'])) { ?>
        <?php echo QuickActionsHelper::renderQuickActions() ?>
    <?php } ?>

    <?php echo base_admin_js(['jquery-3.6.0.min', 'bootstrap', 'Sortable.min', 'main', 'jquery-ui.min', 'notifications']); ?>
    <?php echo render_admin_js(); ?>
    <?php echo render_admin_bottom_js() ?>
    
    <?php if(isset($_SESSION['toast'])) { ?>
        <div id="notification-data" data-message="<?php echo html($_SESSION['toast']['message']) ?>" data-type="<?php echo html($_SESSION['toast']['type']) ?>" data-position="<?php echo SettingsHelper::get('controller_admin', 'notification_position', 'top-left') ?>" style="display: none;"></div>
        <?php unset($_SESSION['toast']); ?>
    <?php } ?>
</body>
</html>