<?php

/**
* Хендлер для отладки и логирования ошибок
* @package Helpers
*/
class DebugHandler {
    
    private static $db = null;
    private static $enabled = false;
    private static $initialized = false;
    
    /**
    * Инициализация хендлера
    * @param bool $enabled Включен ли режим отладки
    * @param object|null $db Подключение к БД
    */
    public static function init($enabled = false, $db = null) {
        if (self::$initialized) {
            return;
        }
        
        self::$enabled = $enabled;
        self::$db = $db ?: (isset($GLOBALS['db']) ? $GLOBALS['db'] : null);
        
        if (self::$enabled && self::$db) {

            set_error_handler([self::class, 'handleError']);
            set_exception_handler([self::class, 'handleException']);
            register_shutdown_function([self::class, 'handleShutdown']);

            ini_set('display_errors', 0);
            error_reporting(E_ALL);
        }
        
        self::$initialized = true;
    }
    
    /**
    * Обработчик ошибок PHP
    * @param int $errno
    * @param string $errstr
    * @param string $errfile
    * @param int $errline
    * @return bool
    */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!self::$enabled || !self::$db) {
            return false;
        }

        if (($errno === E_WARNING || $errno === E_NOTICE) && 
            (strpos($errstr, 'Undefined constant') !== false || 
             strpos($errstr, 'Use of undefined constant') !== false ||
             (strpos($errstr, 'Constant LANG_') !== false && strpos($errstr, 'already defined') !== false))) {
            error_log("[LANG WARNING] $errstr in $errfile on line $errline");
            return true;
        }
        
        $type = 'notice';
        if (in_array($errno, [E_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $type = 'error';
        } elseif (in_array($errno, [E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING])) {
            $type = 'warning';
        }
        
        $context = self::getContext();
        
        $data = [
            'type' => $type,
            'code' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'trace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)),
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        try {
            $debugModel = new DebugModel(self::$db);
            $debugModel->save($data);
        } catch (Exception $e) {
            // Если не можем сохранить в БД, пишем в лог файл как запасной вариант
            error_log("[DebugHandler] Cannot save to DB: " . $e->getMessage());
            error_log("[DebugHandler] Original error: {$errstr} in {$errfile}:{$errline}");
        }
        
        return false;
    }
    
    /**
    * Обработчик исключений
    * @param Throwable $exception
    */
    public static function handleException($exception) {
        if (!self::$enabled || !self::$db) {
            return;
        }
        
        $context = self::getContext();
        
        $data = [
            'type' => 'exception',
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => json_encode($exception->getTrace(), JSON_UNESCAPED_UNICODE),
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        try {
            $debugModel = new DebugModel(self::$db);
            $debugModel->save($data);
        } catch (Exception $e) {
            error_log("[DebugHandler] Cannot save exception to DB: " . $e->getMessage());
            error_log("[DebugHandler] Original exception: " . $exception->getMessage());
        }
    }
    
    /**
    * Обработчик фатальных ошибок
    */
    public static function handleShutdown() {
        if (!self::$enabled || !self::$db) {
            return;
        }
        
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
    
    /**
    * Собирает контекст для логирования (переменные, сессии, запрос)
    * @return array
    */
    private static function getContext() {
        $context = [];
        
        if (!empty($_GET)) {
            $context['get'] = $_GET;
        }
        
        if (!empty($_POST)) {
            $safePost = $_POST;
            foreach ($safePost as $key => $value) {
                if (stripos($key, 'password') !== false || stripos($key, 'passwd') !== false) {
                    $safePost[$key] = '***';
                }
            }
            $context['post'] = $safePost;
        }
        
        if (!empty($_SESSION)) {
            $safeSession = $_SESSION;
            $sensitiveKeys = ['password', 'passwd', 'csrf_token', 'csrf_tokens'];
            foreach ($sensitiveKeys as $key) {
                if (isset($safeSession[$key])) {
                    $safeSession[$key] = '***';
                }
            }
            $context['session'] = $safeSession;
        }
        
        $context['server'] = [
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'http_method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'http_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
            'http_referer' => $_SERVER['HTTP_REFERER'] ?? null
        ];
        
        return $context;
    }
}