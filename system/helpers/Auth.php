<?php

/**
* Класс для работы с аутентификацией и данными текущего пользователя 
* @package Core
*/
class Auth {
    
    /** @var object|null Подключение к базе данных (статическое) */
    private static $db = null;
    
    /** @var array Кэш данных пользователей для уменьшения запросов к БД */
    private static $userCache = [];
    
    /**
    * Инициализирует класс с подключением к базе данных
    * @param object $db Подключение к базе данных
    * @return void
    */
    public static function init($db) {
        self::$db = $db;
    }
    
    /**
    * Получает данные текущего авторизованного пользователя 
    * @return array|null Данные пользователя или null, если не авторизован
    */
    public static function getUser() {

        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $userId = $_SESSION['user_id'];
        
        if (isset(self::$userCache[$userId])) {
            return self::$userCache[$userId];
        }
        
        if (!self::$db) {
            self::$db = $GLOBALS['db'] ?? null;
            
            if (!self::$db && isset($GLOBALS['app']) && isset($GLOBALS['app']->db)) {
                self::$db = $GLOBALS['app']->db;
            }
        }
        
        if (self::$db) {
            $userModel = new UserModel(self::$db);
            $user = $userModel->getById($userId);
            
            if ($user) {
                self::$userCache[$userId] = $user;
                return $user;
            }
        }
        
        return null;
    }
    
    /**
    * Проверяет, авторизован ли пользователь 
    * @return bool true если пользователь авторизован
    */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
    * Проверяет, является ли текущий пользователь администратором 
    * @return bool true если пользователь администратор
    */
    public static function isAdmin() {
        $user = self::getUser();
        return $user && (!empty($user['is_admin']) || $user['role'] === 'admin');
    }
    
    /**
    * Получает ID текущего пользователя 
    * @return int|null ID пользователя или null
    */
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
    * Получает имя пользователя (username) текущего пользователя 
    * @return string|null Имя пользователя или null
    */
    public static function getUsername() {
        $user = self::getUser();
        return $user['username'] ?? null;
    }
    
    /**
    * Получает отображаемое имя текущего пользователя
    * @return string Отображаемое имя
    */
    public static function getDisplayName() {
        $user = self::getUser();
        return $user['display_name'] ?? $user['username'] ?? LANG_HELPER_AUTH_DEFAULT_DISPLAY_NAME;
    }
    
    /**
    * Получает URL аватара текущего пользователя
    * @return string URL аватара (загруженный или стандартный)
    */
    public static function getAvatar() {
        $user = self::getUser();
        if ($user && !empty($user['avatar'])) {
            return BlockImageHelper::getImageUrl($user['avatar']);
        }
        return BASE_URL . '/assets/img/avatar/01.jpg';
    }
}