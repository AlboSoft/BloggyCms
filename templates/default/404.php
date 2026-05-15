<!DOCTYPE html>
<html lang="<?php echo html(substr(SettingsHelper::get('general', 'site_language', 'ru_RU'), 0, 2)); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-language" content="<?php echo html(substr(SettingsHelper::get('general', 'site_language', 'ru_RU'), 0, 2)); ?>">
    <title><?php echo LANG_404_PAGE_TITLE; ?></title>
    <link href="/templates/default/front/assets/css/404.css" rel="stylesheet">
</head>
<body>
    <canvas id="bgCanvas"></canvas>
    <div class="scanlines"></div>
    <div class="glitch-overlay" id="glitchOverlay"></div>

    <div class="content" id="mainContent">
        <div class="error-code" id="errorCode">
            <span class="digit" data-char="4">4</span>
            <span class="digit zero-wrap">
                <span class="zero-portal" id="portalZero">0</span>
                <div class="portal-ring"></div>
                <div class="portal-glow"></div>
            </span>
            <span class="digit" data-char="4">4</span>
        </div>

        <div class="subtitle" id="subtitle"><?php echo LANG_404_SUBTITLE; ?></div>
        <div class="message" id="messageText">
            <?php echo LANG_404_MESSAGE; ?>
        </div>

        <div class="btn-wrapper" id="btnWrapper">
            <a href="<?php echo BASE_URL; ?>" class="home-btn" id="homeBtn">
                ← <?php echo LANG_404_BACK_HOME; ?>
            </a>
        </div>

        <div class="attempt-counter" id="attemptCounter"></div>
    </div>

    <div class="secret-hint"><?php echo LANG_404_SECRET_HINT; ?></div>
    
<script>const lang = document.querySelector('meta[name="site-language"]')?.content || 'ru';</script>
<script src="/templates/default/front/assets/js/404.js"></script>
</body>
</html>