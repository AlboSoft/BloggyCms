<!DOCTYPE html>
<html lang="<?php echo html(substr(SettingsHelper::get('general', 'site_language', 'ru_RU'), 0, 2)); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-language" content="<?php echo html(substr(SettingsHelper::get('general', 'site_language', 'ru_RU'), 0, 2)); ?>">
    <title><?php echo LANG_500_PAGE_TITLE; ?></title>
    <link href="/templates/default/front/assets/css/500.css" rel="stylesheet">
</head>
<body>
    <canvas id="bgCanvas"></canvas>
    <div class="scanlines"></div>

    <div class="content">
        <div class="status-bar">
            <div class="status-dot"></div>
            <span class="status-text"><?php echo LANG_500_STATUS_TEXT; ?></span>
        </div>

        <div class="error-code" id="errorCode">
            <span class="digit">5</span>
            <span class="digit digit-zero">
                <span class="zero-inner">0</span>
                <div class="zero-ring"></div>
                <div class="zero-glow"></div>
            </span>
            <span class="digit digit-zero">
                <span class="zero-inner">0</span>
                <div class="zero-ring"></div>
                <div class="zero-glow"></div>
            </span>
        </div>

        <div class="subtitle" id="subtitle"></div>

        <div class="message">
            <?php echo LANG_500_MESSAGE; ?>
        </div>

        <div class="log-block" id="logBlock"></div>

        <a href="<?php echo BASE_URL; ?>" class="home-btn">← <?php echo LANG_500_BACK_HOME; ?></a>
    </div>

    <div class="bottom-stamp" id="timestamp"></div>
<script>const lang = document.querySelector('meta[name="site-language"]')?.content || 'ru';</script>
<script src="/templates/default/front/assets/js/500.js"></script>
</body>
</html>