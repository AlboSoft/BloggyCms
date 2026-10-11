<?php

/**
* Менеджер для управления правами доступа пользователей
*/
class PermissionManager {

    /**
    * @var mixed Подключение к базе данных
    */
    private $db;
    
    /**
    * Конструктор PermissionManager
    * @param mixed $db Подключение к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
    * Загружает все permissions из всех контроллеров
    * @return array Массив разрешений по контроллерам
    */
    public function loadAllPermissions() {
        $permissions = [];
        $controllersPath = CONTROLLERS_PATH;
        
        foreach ((glob($controllersPath . '/*/permissions.php') ?: []) as $file) {
            $controllerPermissions = include $file;
            $controllerName = basename(dirname($file));
            $permissions[$controllerName] = $controllerPermissions;
        }
        
        return $permissions;
    }
    
    /**
    * Проверяет есть ли у пользователя право
    * @param int $userId ID пользователя
    * @param string $permissionKey Ключ разрешения
    * @return bool Есть ли у пользователя право
    */
    public function can($userId, $permissionKey) {
        $sql = "SELECT COUNT(*) FROM users_groups ug
                JOIN group_permissions gp ON ug.group_id = gp.group_id
                WHERE ug.user_id = ? AND gp.permission_key = ?";
        
        return $this->db->fetchValue($sql, [$userId, $permissionKey]) > 0;
    }
    
    /**
    * Проверяет права для текущего пользователя
    * @param string $permissionKey Ключ разрешения
    * @return bool Есть ли у текущего пользователя право
    */
    public function userCan($permissionKey) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        return $this->can($_SESSION['user_id'], $permissionKey);
    }
}