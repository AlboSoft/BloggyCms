<?php
/**
* Template Name: Habr Pro - Main Layout - 0 radius, 1220px minimal
*/
$maintenanceMode = SettingsHelper::get('general', 'maintenance_mode', false);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
if ($maintenanceMode && !$isAdmin) {
    http_response_code(503);
    header('Retry-After: 3600');
    $siteName = SettingsHelper::get('general', 'site_name', 'BloggyCMS');
    $lang = html(substr(SettingsHelper::get('general', 'site_language', 'ru_RU'), 0, 2));
    $fav = SettingsHelper::get('general', 'favicon', '');
    $favUrl = !empty($fav) ? BASE_URL . '/' . $fav : BASE_URL . '/templates/default/admin/assets/img/favicon.png';
    ?>
    <!DOCTYPE html>
    <html lang="<?= $lang ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= LANG_TEMPLATE_LAYOUT_MAINTENANCE_TITLE ?> - <?= html($siteName) ?></title>
        <link rel="icon" href="<?= $favUrl ?>">
        <style>
          *{margin:0;padding:0;box-sizing:border-box}
          body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#fff;color:#000;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
          .maintenance-page{max-width:400px;width:100%;border:1px solid #000;padding:24px;text-align:left}
          .maintenance-icon{font-size:24px;margin-bottom:12px}
          .maintenance-title{font-size:18px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px}
          .maintenance-message{font-size:13px;color:#6c6c6c;line-height:1.5}
        </style>
    </head>
    <body>
        <div class="maintenance-page">
            <div class="maintenance-icon">—</div>
            <h1 class="maintenance-title"><?= LANG_TEMPLATE_LAYOUT_MAINTENANCE_TITLE ?></h1>
            <div class="maintenance-message"><?= nl2br(html(SettingsHelper::get('general', 'maintenance_message', LANG_TEMPLATE_LAYOUT_MAINTENANCE_MESSAGE))) ?></div>
        </div>
    </body>
    </html>
    <?php exit;
}
?>
<?php init_blocks_cache(); ?>
<?php
$favicon = SettingsHelper::get('general', 'favicon', '');
$faviconUrl = !empty($favicon) ? BASE_URL . '/' . $favicon : BASE_URL . '/templates/default/admin/assets/img/favicon.png';
$langCode = html(substr(SettingsHelper::get('general', 'site_language', 'ru_RU'), 0, 2));
$siteName = SettingsHelper::get('general', 'site_name', 'BloggyCMS');
?>
<!DOCTYPE html>
<html lang="<?= $langCode ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html($siteName) ?> - <?= html($title ?? LANG_TEMPLATE_LAYOUT_DEFAULT_TITLE) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= $faviconUrl ?>">
    <meta name="site-language" content="<?= $langCode ?>">
    <?php if(!empty(SettingsHelper::get('general', 'site_description'))) { ?>
    <meta name="description" content="<?= html(SettingsHelper::get('general', 'site_description')) ?>">
    <?php } ?>
    <?php if(!empty(SettingsHelper::get('general', 'site_author'))) { ?>
    <meta name="author" content="<?= html(SettingsHelper::get('general', 'site_author')) ?>" />
    <?php } ?>
    <?php if (isset($schemaData) && is_array($schemaData)) { ?>
    <script type="application/ld+json"><?= json_encode($schemaData, JSON_UNESCAPED_UNICODE) ?></script>
    <?php } ?>
    <?= base_front_css(['bootstrap.min','main']) ?>
    <link rel="stylesheet" href="<?= get_blocks_css_url() ?>">
    <?= render_front_css() ?>
</head>
<body class="tg-body">
    <div id="front-notifications-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999"></div>

    <?= render_html_block('header') ?>
    <?= render_html_block('breadcrumbs') ?>

    <main class="tg-main"><?= $content ?></main>

    <?= render_html_block('footer') ?>
    <?= render_html_block('top') ?>
    <?= render_html_block('cookies') ?>

    <script>const lang = document.querySelector('meta[name="site-language"]')?.content || 'ru';</script>
    <?= base_front_js(['bootstrap.bundle.min','notifications','main']) ?>
    <?= render_front_js() ?>
    <?= render_front_bottom_js() ?>

    <?php if(isset($_SESSION['toast'])) { ?>
        <div id="notification-data" data-message="<?= html($_SESSION['toast']['message']) ?>" data-type="<?= html($_SESSION['toast']['type']) ?>" style="display:none"></div>
        <?php unset($_SESSION['toast']); ?>
    <?php } ?>
</body>
</html>
