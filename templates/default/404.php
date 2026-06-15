<!DOCTYPE html>
<html lang="<?php echo html(substr(SettingsHelper::get('general', 'site_language', 'ru_RU'), 0, 2)); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo LANG_404_PAGE_TITLE; ?></title>
    <link href="/templates/default/front/assets/css/404.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="error-wrapper">
        <div class="error-code">
            <span>4</span>
            <span class="accent-zero">0</span>
            <span>4</span>
        </div>
        <hr>
    </div>

    <h1><?php echo LANG_404_SUBTITLE; ?></h1>

    <div class="message">
        <?php echo LANG_404_MESSAGE; ?>
    </div>

    <a href="<?php echo BASE_URL; ?>" class="home-btn">
        <span>←</span> <?php echo LANG_404_BACK_HOME; ?>
    </a>

</div>

</body>
</html>