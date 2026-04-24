<?php

/**
* Middleware для проверки доступа к административной панели
*/
class AdminAuthMiddleware {

    /**
    * Основной метод обработки middleware
    * @return bool
    */
    public static function handle() {

        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        if (self::isStaticFile($currentUri)) {
            return true;
        }
        
        $normalizedUri = rtrim(str_replace('\\', '/', $currentUri), '/') . '/';
        if (strpos($normalizedUri, '/admin/login/') === 0 || 
            $currentUri === ADMIN_URL . '/login' ||
            $currentUri === ADMIN_URL . '/login/') {
            return true;
        }
        
        if (strpos($currentUri, '/admin') === 0) {
            return self::checkAdminAccess();
        }
        
        return true;
    }
    
    /**
    * Проверка доступа администратора
    * @return bool
    */
    private static function checkAdminAccess() {
        $isAjax = self::isAjaxRequest();
        
        if (!isset($_SESSION['user_id'])) {
            if ($isAjax) {
                self::jsonError(LANG_CORE_ADMINAUTHMIDDLEWARE_ERROR_AUTH_REQUIRED, 401);
            } else {
                self::redirect(ADMIN_URL . '/login', LANG_CORE_ADMINAUTHMIDDLEWARE_ERROR_AUTH_REQUIRED);
            }
            return false;
        }
        
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            if ($isAjax) {
                self::jsonError(LANG_CORE_ADMINAUTHMIDDLEWARE_ERROR_ACCESS_DENIED, 403);
            } else {
                self::redirect(BASE_URL, LANG_CORE_ADMINAUTHMIDDLEWARE_ERROR_ACCESS_DENIED);
            }
            return false;
        }
        
        return true;
    }
    
    /**
    * Проверяет, является ли URI статическим файлом
    * @param string $uri URI для проверки
    * @return bool
    */
    private static function isStaticFile($uri) {
        $staticExtensions = [
            'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg',
            'woff', 'woff2', 'ttf', 'eot', 'map', 'txt', 'xml'
        ];
        
        $extension = pathinfo($uri, PATHINFO_EXTENSION);
        return in_array(strtolower($extension), $staticExtensions);
    }
    
    /**
    * Проверяет, является ли запрос AJAX-запросом
    * @return bool
    */
    private static function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Прерывание выполнения с редиректом
    * @param string $url URL для редиректа
    * @param string|null $message Сообщение об ошибке
    */
    private static function redirect($url, $message = null) {
        if ($message) {
            \Notification::error($message);
        }
        header('Location: ' . $url);
        exit;
    }
    
    /**
    * Прерывание выполнения с JSON-ответом (для AJAX)
    * @param string $message Сообщение об ошибке
    * @param int $statusCode HTTP статус код
    */
    private static function jsonError($message, $statusCode = 403) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}