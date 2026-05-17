<?php
// system/controllers/posts/hooks/disable.php

\Event::listen('controller.render.before', function($data) {
    $isDisabled = \SettingsHelper::get('controller_posts', 'disable', false);
    
    if (!$isDisabled) {
        return;
    }
    
    // Проверяем, админка это или фронт
    $isAdmin = strpos($_SERVER['REQUEST_URI'], '/admin') === 0;
    
    // Если это фронтенд и шаблон относится к постам
    if (!$isAdmin && isset($data['template']) && 
        (strpos($data['template'], 'posts/') !== false || 
         strpos($data['template'], 'front/posts/') !== false)) {
        
        http_response_code(404);
        
        $templateFile = TEMPLATES_PATH . '/' . DEFAULT_TEMPLATE . '/front/404.php';
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo "404 - Posts module is disabled";
        }
        exit;
    }
}, 100);