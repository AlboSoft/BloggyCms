<?php
/**
* Template Name: Основной макет
*/

$maintenanceMode = SettingsHelper::get('general', 'maintenance_mode', false);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

if ($maintenanceMode && !$isAdmin) {
    http_response_code(503);
    header('Retry-After: 3600');
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo LANG_TEMPLATE_LAYOUT_MAINTENANCE_TITLE . ' - ' . SettingsHelper::get('general', 'site_name', 'BloggyCMS'); ?></title>
        <link rel="stylesheet" href="templates/default/front/assets/css/maintenance.css">
    </head>
    <body>
        <div class="maintenance-page">
            <div class="maintenance-icon"><?php echo bloggy_icon('bs', 'gear-fill'); ?></div>
            <h1 class="maintenance-title"><?php echo LANG_TEMPLATE_LAYOUT_MAINTENANCE_TITLE; ?></h1>
            <div class="maintenance-message">
                <?= nl2br(html(SettingsHelper::get('general', 'maintenance_message', LANG_TEMPLATE_LAYOUT_MAINTENANCE_MESSAGE))) ?>
            </div>
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
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SettingsHelper::get('general', 'site_name', 'BloggyCMS') ?> - <?= $title ?? LANG_TEMPLATE_LAYOUT_DEFAULT_TITLE ?></title>
    <link rel="icon" type="image/x-icon" href="<?= $faviconUrl ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= $faviconUrl ?>">
    <link rel="apple-touch-icon" href="<?= $faviconUrl ?>">
    
    <?php if(!empty(SettingsHelper::get('general', 'site_description'))) { ?>
    <meta name="description" content="<?php echo SettingsHelper::get('general', 'site_description'); ?>">
    <?php } ?>
    <?php if(!empty(SettingsHelper::get('general', 'site_author'))) { ?>
        <meta name="author" content="<?php echo SettingsHelper::get('general', 'site_author'); ?>" />
    <?php } ?>
    <?php if (isset($schemaData) && is_array($schemaData)) { ?>
        <script type="application/ld+json"><?php echo json_encode($schemaData, JSON_UNESCAPED_UNICODE); ?></script>
    <?php } ?>
    <?php echo base_front_css(['bootstrap.min','main']); ?>
    <link rel="stylesheet" href="<?php echo get_blocks_css_url(); ?>">
    <?php echo render_front_css(); ?>
</head>
<body>

    <div id="front-notifications-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
    
    <?php echo render_html_block('header'); ?>
    <?php echo render_html_block('breadcrumbs'); ?>

    <?php echo $content ?>

    <?php echo render_html_block('footer'); ?>
    <?php echo render_html_block('top'); ?>
    <?php echo render_html_block('cookies'); ?>

    
    <?php echo base_front_js(['bootstrap.bundle.min','notifications','main']); ?>
    
    <?php echo render_front_js(); ?>
    <?php echo render_front_bottom_js(); ?>

    <?php if(isset($_SESSION['toast'])) { ?>
        <div id="notification-data" 
             data-message="<?php echo html($_SESSION['toast']['message']); ?>" 
             data-type="<?php echo html($_SESSION['toast']['type']); ?>"
             style="display: none;"></div>
        <?php unset($_SESSION['toast']); ?>
    <?php } ?>

</body>
</html>