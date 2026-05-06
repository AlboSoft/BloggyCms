<?php

/**
* Модель для работы с пользователями, группами и достижениями
* @package Models
*/
class UserModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'authenticate',
        'authenticateByEmail',
        'getById',
        'getByUsername',
        'updatePassword',
        'getActiveUsers',
        'getTotalCount',
        'getAdmins',
        'getUserAchievements',
        'getUserUnlockedAchievements',
        'getAchievementById',
        'getAllAchievements',
        'getAchievementUsersPreview',
        'getAchievementUsers',
        'formatConditions',
        'getAllGroups',
        'getGroupById',
        'getUserGroups',
        'getUserGroupsWithDetails'
    ];
    
    private $db;

    /**
    * Конструктор модели 
    * @param object $db Подключение к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
    * Аутентифицирует пользователя по имени пользователя и паролю 
    * @param string $username Имя пользователя
    * @param string $password Пароль
    * @return array|false Данные пользователя или false при неудаче
    */
    public function authenticate($username, $password) {
        $user = $this->db->fetch("SELECT * FROM users WHERE username = ?", [$username]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        $user['is_admin'] = (bool)($user['is_admin'] ?? false);
        
        return $user;
    }

    /**
    * Получает пользователя по ID 
    * @param int $id ID пользователя
    * @return array|null Данные пользователя с установленными значениями по умолчанию
    */
    public function getById($id) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        
        if ($user) {
            $user['display_name'] = $user['display_name'] ?? '';
            $user['bio'] = $user['bio'] ?? '';
            $user['website'] = $user['website'] ?? '';
            $user['avatar'] = $user['avatar'] ?? 'default.jpg';
            $user['role'] = $user['role'] ?? 'user';
            $user['status'] = $user['status'] ?? 'active';
            
            if (empty($user['last_login'])) {
                $user['last_login'] = date('Y-m-d H:i:s');
            }
        }
        
        return $user;
    }

    /**
    * Обновляет пароль пользователя 
    * @param int $id ID пользователя
    * @param string $currentPassword Текущий пароль
    * @param string $newPassword Новый пароль
    * @return bool Результат операции
    */
    public function updatePassword($id, $currentPassword, $newPassword) {
        $user = $this->getById($id);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        return $this->update($id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /**
    * Получает всех пользователей с фильтрацией по роли и статусу
    * @param string|null $role Фильтр по роли
    * @param string|null $status Фильтр по статусу
    * @return array Массив пользователей
    */
    public function getAllWithFilters($role = null, $status = null) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
    * Получает пользователя по имени пользователя 
    * @param string $username Имя пользователя
    * @return array|null Данные пользователя
    */
    public function getByUsername($username) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE username = ?",
            [$username]
        );
    }

    /**
    * Получает пользователя по email 
    * @param string $email Email пользователя
    * @return array|null Данные пользователя
    */
    public function getByEmail($email) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }

    /**
    * Создает нового пользователя и добавляет его в группу по умолчанию 
    * @param array $data Данные пользователя
    * @return int ID созданного пользователя
    */
    public function create($data) {
        $fields = [];
        $placeholders = [];
        $values = [];

        foreach ($data as $field => $value) {
            $fields[] = $field;
            $placeholders[] = '?';
            $values[] = $value;
        }

        $sql = "INSERT INTO users (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->query($sql, $values);
        $userId = $this->db->lastInsertId();

        $this->addUserToDefaultGroup($userId);
        
        try {
            $notificationModel = new \NotificationModel($this->db);
            $notificationModel->addNewUserNotification($userId, $data);
        } catch (\Exception $e) {
            error_log('Failed to send user registration notification: ' . $e->getMessage());
        }
        
        return $userId;
    }

    /**
    * Обновляет данные пользователя
    * @param int $id ID пользователя
    * @param array $data Данные для обновления
    * @return bool Результат операции
    */
    public function update($id, $data) {
        if (method_exists($this->db, 'update')) {
            $validFields = ['display_name', 'email', 'website', 'bio', 'avatar', 'password', 'username', 'role', 'status', 'last_login', 'last_admin_ip'];
            $filteredData = array_intersect_key($data, array_flip($validFields));
            
            if (empty($filteredData)) {
                return true;
            }
            
            $result = $this->db->update('users', $filteredData, ['id' => $id]);
            
            return $result !== false;
            
        } else {
            $fields = [];
            $values = [];
            
            $allowedFields = ['display_name', 'email', 'website', 'bio', 'avatar', 'password', 'username', 'role', 'status', 'last_login', 'last_admin_ip'];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $fields[] = "{$field} = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($fields)) {
                return true;
            }
            
            $values[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->query($sql, $values);
            
            return $stmt !== false;
        }
    }

    /**
    * Удаляет пользователя и все связанные данные (в транзакции) 
    * @param int $id ID пользователя
    * @return bool Результат операции
    * @throws \Exception При ошибке
    */
    public function delete($id) {
        try {
            $this->db->query("START TRANSACTION");
            $this->db->query("DELETE FROM users_groups WHERE user_id = ?", [$id]);
            $this->db->query("DELETE FROM user_achievements_data WHERE user_id = ?", [$id]);
            $this->db->query("DELETE FROM password_resets WHERE user_id = ?", [$id]);
            $this->db->query("UPDATE posts SET user_id = NULL WHERE user_id = ?", [$id]);
            $this->db->query("UPDATE comments SET user_id = NULL WHERE user_id = ?", [$id]);
            $result = $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
            $this->db->query("COMMIT");
            
            return $result;
            
        } catch (\Exception $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
    
    /**
    * Получает общее количество пользователей 
    * @return int Количество пользователей
    */
    public function getTotalCount() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM users");
        return $result['count'] ?? 0;
    }
    
    /**
    * Получает список администраторов 
    * @return array Массив администраторов
    */
    public function getAdmins() {
        return $this->db->fetchAll("SELECT * FROM users WHERE role = 'admin' ORDER BY username");
    }
    
    /**
    * Получает список активных пользователей
    * @return array Массив активных пользователей
    */
    public function getActiveUsers() {
        return $this->db->fetchAll("
            SELECT * FROM users 
            WHERE status = 'active' 
            ORDER BY created_at DESC
        ");
    }

    /**
    * Аутентифицирует пользователя по email и паролю
    * @param string $email Email
    * @param string $password Пароль
    * @return array|false Данные пользователя или false
    */
    public function authenticateByEmail($email, $password) {
        $user = $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        return $user;
    }

    /**
    * Получает все группы пользователей
    * 
    * @return array Массив групп
    */
    public function getAllGroups() {
        return $this->db->fetchAll("SELECT * FROM user_groups ORDER BY name");
    }

    /**
    * Получает группу по ID 
    * @param int $id ID группы
    * @return array|null Данные группы
    */
    public function getGroupById($id) {
        return $this->db->fetch("SELECT * FROM user_groups WHERE id = ?", [$id]);
    }

    /**
    * Создает новую группу пользователей
    * @param array $data Данные группы
    * @return bool Результат операции
    */
    public function createGroup($data) {
        if (!empty($data['is_default'])) {
            $this->db->query("UPDATE user_groups SET is_default = 0");
        }
        return $this->db->insert('user_groups', $data);
    }

    /**
    * Обновляет данные группы 
    * @param int $id ID группы
    * @param array $data Данные для обновления
    * @return bool Результат операции
    */
    public function updateGroup($id, $data) {
        if (!empty($data['is_default'])) {
            $this->db->query("UPDATE user_groups SET is_default = 0");
        }
        return $this->db->update('user_groups', $data, ['id' => $id]);
    }

    /**
    * Удаляет группу и все связанные данные 
    * @param int $id ID группы
    * @return bool Результат операции
    */
    public function deleteGroup($id) {
        $this->db->query("DELETE FROM users_groups WHERE group_id = ?", [$id]);
        $this->db->query("DELETE FROM group_permissions WHERE group_id = ?", [$id]);
        return $this->db->query("DELETE FROM user_groups WHERE id = ?", [$id]);
    }

    /**
    * Получает права доступа группы
    * @param int $groupId ID группы
    * @return array Массив ключей прав
    */
    public function getGroupPermissions($groupId) {
        $permissions = $this->db->fetchAll("
            SELECT permission_key 
            FROM group_permissions 
            WHERE group_id = ?
        ", [$groupId]);
        
        return array_column($permissions, 'permission_key');
    }

    /**
    * Обновляет права доступа группы
    * @param int $groupId ID группы
    * @param array $permissions Массив ключей прав
    * @return bool Результат операции
    */
    public function updateGroupPermissions($groupId, $permissions) {
        $this->db->query("DELETE FROM group_permissions WHERE group_id = ?", [$groupId]);
        
        foreach ($permissions as $permission) {
            $this->db->insert('group_permissions', [
                'group_id' => $groupId,
                'permission_key' => $permission
            ]);
        }
        
        return true;
    }

    /**
    * Получает пользователей с информацией о группах и фильтрацией 
    * @param array $filters Массив фильтров (role, status, group, search, reg_mode, reg_date, reg_date_from, reg_date_to)
    * @return array Массив пользователей с группами
    */
    public function getUsersWithGroups($filters = []) {
        $sql = "
            SELECT 
                u.*,
                GROUP_CONCAT(DISTINCT ug.id) as group_ids,
                GROUP_CONCAT(DISTINCT ug.name) as group_names
            FROM users u
            LEFT JOIN users_groups uug ON u.id = uug.user_id
            LEFT JOIN user_groups ug ON uug.group_id = ug.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['group'])) {
            $sql .= " AND ug.id = ?";
            $params[] = $filters['group'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.display_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Фильтрация по дате регистрации
        if (!empty($filters['reg_mode'])) {
            if ($filters['reg_mode'] === 'day' && !empty($filters['reg_date'])) {
                $sql .= " AND DATE(u.created_at) = ?";
                $params[] = $filters['reg_date'];
            } elseif ($filters['reg_mode'] === 'period') {
                if (!empty($filters['reg_date_from'])) {
                    $sql .= " AND DATE(u.created_at) >= ?";
                    $params[] = $filters['reg_date_from'];
                }
                if (!empty($filters['reg_date_to'])) {
                    $sql .= " AND DATE(u.created_at) <= ?";
                    $params[] = $filters['reg_date_to'];
                }
            }
        }
        
        $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
        
        $users = $this->db->fetchAll($sql, $params);
        
        foreach ($users as &$user) {
            $user['groups'] = [];
            if (!empty($user['group_names'])) {
                $groupNames = explode(',', $user['group_names']);
                $groupIds = explode(',', $user['group_ids']);
                
                foreach ($groupNames as $index => $name) {
                    if (!empty($name) && isset($groupIds[$index])) {
                        $user['groups'][] = [
                            'id' => $groupIds[$index],
                            'name' => $name
                        ];
                    }
                }
            }
            
            unset($user['group_names'], $user['group_ids']);
        }
        
        return $users;
    }

    /**
    * Получает группу по умолчанию 
    * @return array|null Данные группы
    */
    public function getDefaultGroup() {
        $group = $this->db->fetch("SELECT * FROM user_groups WHERE is_default = 1 LIMIT 1");
        
        return $group;
    }

    /**
    * Обновляет группы пользователя 
    * @param int $userId ID пользователя
    * @param array $groupIds Массив ID групп
    * @return bool Результат операции
    */
    public function updateUserGroups($userId, $groupIds) {
        $this->db->query("DELETE FROM users_groups WHERE user_id = ?", [$userId]);
        
        foreach ($groupIds as $groupId) {
            $this->db->insert('users_groups', [
                'user_id' => $userId,
                'group_id' => $groupId
            ]);
        }
        
        return true;
    }

    /**
    * Получает ID групп пользователя 
    * @param int $userId ID пользователя
    * @return array Массив ID групп
    */
    public function getUserGroups($userId) {
        $groups = $this->db->fetchAll("
            SELECT ug.id 
            FROM user_groups ug
            JOIN users_groups uug ON ug.id = uug.group_id
            WHERE uug.user_id = ?
        ", [$userId]);
        
        return array_column($groups, 'id');
    }

    /**
    * Добавляет пользователя в группу по умолчанию
    * @param int $userId ID пользователя
    * @return bool Результат операции
    */
    public function addUserToDefaultGroup($userId) {
        try {
            
            $defaultGroup = $this->getDefaultGroup();
            
            if ($defaultGroup) {
                $existing = $this->db->fetch(
                    "SELECT id FROM users_groups WHERE user_id = ? AND group_id = ?",
                    [$userId, $defaultGroup['id']]
                );
                
                if (!$existing) {
                    $result = $this->db->insert('users_groups', [
                        'user_id' => $userId,
                        'group_id' => $defaultGroup['id']
                    ]);

                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
    * Получает группы пользователя с детальной информацией
    * @param int $userId ID пользователя
    * @return array Массив групп
    */
    public function getUserGroupsWithDetails($userId) {
        try {
            return $this->db->fetchAll(
                "SELECT g.* 
                FROM user_groups g 
                INNER JOIN users_groups ug ON g.id = ug.group_id 
                WHERE ug.user_id = ?
                ORDER BY g.name",
                [$userId]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /**
    * Обновляет последний IP-адрес администратора 
    * @param int $userId ID пользователя
    * @param string $ip IP-адрес
    * @return bool Результат операции
    */
    public function updateLastAdminIP($userId, $ip) {
        return $this->db->query(
            "UPDATE users SET last_admin_ip = ? WHERE id = ?",
            [$ip, $userId]
        );
    }

    /**
    * Получает последний IP-адрес администратора 
    * @param string $username Имя пользователя
    * @return string|null IP-адрес
    */
    public function getLastAdminIP($username) {
        $user = $this->getByUsername($username);
        return $user['last_admin_ip'] ?? null;
    }

    /**
    * Получает ID групп пользователя (с обработкой ошибок) 
    * @param int $userId ID пользователя
    * @return array Массив ID групп
    */
    public function getUserGroupIds($userId) {
        try {
            $result = $this->db->fetchAll(
                "SELECT group_id FROM users_groups WHERE user_id = ?",
                [$userId]
            );
            
            return array_column($result, 'group_id');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
    * Возвращает имя таблицы ачивок 
    * @return string
    */
    public function getAchievementsTable() {
        return 'user_achievements';
    }

    /**
    * Возвращает имя таблицы условий ачивок
    * @return string
    */
    public function getAchievementConditionsTable() {
        return 'achievement_conditions';
    }

    /**
    * Возвращает имя таблицы конфигурации ачивок 
    * @return string
    */
    public function getAchievementsConfigTable() {
        return 'achievements_config';
    }

    /**
    * Возвращает имя таблицы данных ачивок пользователей 
    * @return string
    */
    public function getAchievementsUserTable() {
        return 'user_achievements_data';
    }

    /**
    * Получает все достижения с фильтрацией 
    * @param array $filters Массив фильтров (type, active, search)
    * @return array Массив достижений
    */
    public function getAllAchievements($filters = []) {
        $sql = "SELECT ua.*, 
                COUNT(DISTINCT uad.user_id) as unlocked_count
                FROM user_achievements ua
                LEFT JOIN user_achievements_data uad ON ua.id = uad.achievement_id AND uad.is_unlocked = 1
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['type'])) {
            $sql .= " AND ua.type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['active'])) {
            $sql .= " AND ua.is_active = 1";
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (ua.name LIKE ? OR ua.description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " GROUP BY ua.id ORDER BY ua.priority DESC, ua.name ASC";
        
        $achievements = $this->db->fetchAll($sql, $params);
        
        foreach ($achievements as &$achievement) {
            $achievement['conditions'] = $this->getAchievementConditions($achievement['id']);
        }
        
        return $achievements;
    }

    /**
    * Получает достижение по ID
    * @param int $id ID достижения
    * @return array|null Данные достижения
    */
    public function getAchievementById($id) {
        $achievement = $this->db->fetch(
            "SELECT * FROM user_achievements WHERE id = ?",
            [$id]
        );
        
        if ($achievement) {
            $achievement['conditions'] = $this->getAchievementConditions($id);
            $achievement['user_count'] = $this->getAchievementUnlockedCount($id);
        }
        
        return $achievement;
    }

    /**
    * Получает условия достижения
    * @param int $achievementId ID достижения
    * @return array Массив условий
    */
    public function getAchievementConditions($achievementId) {
        return $this->db->fetchAll(
            "SELECT * FROM achievement_conditions WHERE achievement_id = ? ORDER BY id",
            [$achievementId]
        );
    }

    /**
    * Создает новое достижение
    * @param array $data Данные достижения
    * @return int ID созданного достижения
    */
    public function createAchievement($data) {
        $achievementData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'type' => $data['type'] ?? 'auto',
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'priority' => $data['priority'] ?? 0
        ];
        
        if (!empty($data['image'])) {
            $achievementData['image'] = $data['image'];
        }
        
        $columns = array_keys($achievementData);
        $placeholders = array_fill(0, count($columns), '?');
        $values = array_values($achievementData);
        
        $sql = "INSERT INTO user_achievements (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->db->query($sql, $values);
        $achievementId = $this->db->lastInsertId();

        if (!empty($data['conditions']) && is_array($data['conditions'])) {
            foreach ($data['conditions'] as $condition) {
                if (!empty($condition['type']) && !empty($condition['operator']) && isset($condition['value'])) {
                    $this->db->query(
                        "INSERT INTO achievement_conditions (achievement_id, condition_type, operator, value) 
                        VALUES (?, ?, ?, ?)",
                        [$achievementId, $condition['type'], $condition['operator'], $condition['value']]
                    );
                }
            }
        }
        
        return $achievementId;
    }

    /**
    * Обновляет существующее достижение 
    * @param int $id ID достижения
    * @param array $data Данные для обновления
    * @return bool Результат операции
    */
    public function updateAchievement($id, $data) {
        $achievementData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'type' => $data['type'] ?? 'auto',
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'priority' => $data['priority'] ?? 0
        ];
        
        if (!empty($data['image'])) {
            $achievementData['image'] = $data['image'];
        } elseif (isset($data['remove_image']) && $data['remove_image']) {
            $achievementData['image'] = null;
        }
        
        $fields = [];
        $values = [];
        
        foreach ($achievementData as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $sql = "UPDATE user_achievements SET " . implode(', ', $fields) . " WHERE id = ?";
        $updated = $this->db->query($sql, $values);
        
        if (isset($data['conditions']) && is_array($data['conditions'])) {
            $this->db->query("DELETE FROM achievement_conditions WHERE achievement_id = ?", [$id]);
            
            foreach ($data['conditions'] as $condition) {
                if (!empty($condition['type']) && !empty($condition['operator']) && isset($condition['value'])) {
                    $this->db->query(
                        "INSERT INTO achievement_conditions (achievement_id, condition_type, operator, value) 
                        VALUES (?, ?, ?, ?)",
                        [$id, $condition['type'], $condition['operator'], $condition['value']]
                    );
                }
            }
        }
        
        return $updated;
    }

    /**
    * Удаляет достижение 
    * @param int $id ID достижения
    * @return bool Результат операции
    */
    public function deleteAchievement($id) {
        $tables = $this->db->fetchAll("SHOW TABLES LIKE 'achievement_rewards'");
        
        $this->db->query("DELETE FROM user_achievements_data WHERE achievement_id = ?", [$id]);
        $this->db->query("DELETE FROM achievement_conditions WHERE achievement_id = ?", [$id]);
        
        if (!empty($tables)) {
            $this->db->query("DELETE FROM achievement_rewards WHERE achievement_id = ?", [$id]);
        }
        
        return $this->db->query("DELETE FROM user_achievements WHERE id = ?", [$id]);
    }

    /**
    * Получает достижения пользователя 
    * @param int $userId ID пользователя
    * @return array Массив достижений
    */
    public function getUserAchievements($userId) {
        $sql = "SELECT ua.*, uad.progress, uad.max_value, uad.is_unlocked, uad.unlocked_at
                FROM user_achievements ua
                LEFT JOIN user_achievements_data uad ON ua.id = uad.achievement_id AND uad.user_id = ?
                WHERE ua.is_active = 1
                ORDER BY ua.priority DESC, ua.name ASC";
        
        $achievements = $this->db->fetchAll($sql, [$userId]);
        
        foreach ($achievements as &$achievement) {
            if ($achievement['progress'] === null) {
                $this->initUserAchievement($userId, $achievement['id']);
                $achievement['progress'] = 0;
                $achievement['max_value'] = 100;
                $achievement['is_unlocked'] = 0;
                $achievement['unlocked_at'] = null;
            }
        }
        
        return $achievements;
    }

    /**
    * Инициализирует запись прогресса для достижения пользователя 
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @return void
    */
    public function initUserAchievement($userId, $achievementId) {
        $exists = $this->db->fetch(
            "SELECT id FROM user_achievements_data WHERE user_id = ? AND achievement_id = ?",
            [$userId, $achievementId]
        );
        
        if (!$exists) {
            $this->db->query(
                "INSERT INTO user_achievements_data (user_id, achievement_id, progress, max_value, is_unlocked) 
                VALUES (?, ?, 0, 100, 0)",
                [$userId, $achievementId]
            );
        }
    }

    /**
    * Обновляет прогресс пользователя по достижению 
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @param int $progress Новый прогресс
    * @return int Обновленный прогресс
    */
    public function updateUserAchievementProgress($userId, $achievementId, $progress) {
        $data = $this->db->fetch(
            "SELECT * FROM user_achievements_data WHERE user_id = ? AND achievement_id = ?",
            [$userId, $achievementId]
        );
        
        if (!$data) {
            $this->initUserAchievement($userId, $achievementId);
            $data = ['progress' => 0, 'is_unlocked' => 0];
        }
        
        $newProgress = max($data['progress'], $progress);
        $this->db->query(
            "UPDATE user_achievements_data SET progress = ?, updated_at = NOW() 
            WHERE user_id = ? AND achievement_id = ?",
            [$newProgress, $userId, $achievementId]
        );
        
        $achievement = $this->getAchievementById($achievementId);
        if ($achievement && $this->checkAchievementConditions($userId, $achievementId)) {
            $this->unlockAchievement($userId, $achievementId);
        }
        
        return $newProgress;
    }

    /**
    * Проверяет выполнение условий для достижения 
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @return bool true если все условия выполнены
    */
    public function checkAchievementConditions($userId, $achievementId) {
        $achievement = $this->getAchievementById($achievementId);
        if (!$achievement || $achievement['type'] !== 'auto') {
            return false;
        }
        
        $conditions = $achievement['conditions'];
        if (empty($conditions)) {
            return false;
        }
        
        $this->initUserAchievement($userId, $achievementId);
        
        foreach ($conditions as $condition) {
            $userValue = $this->getUserStatValue($userId, $condition['condition_type']);

            $this->updateUserAchievementProgress($userId, $achievementId, $userValue);
            
            if (!$this->evaluateCondition($userValue, $condition['operator'], $condition['value'])) {
                return false;
            }
        }
        
        return true;
    }

    /**
    * Получает значение статистики пользователя по типу 
    * @param int $userId ID пользователя
    * @param string $statType Тип статистики
    * @return int Значение
    */
    public function getUserStatValue($userId, $statType) {
        $user = $this->getById($userId);
        if (!$user) {
            return 0;
        }
        
        switch ($statType) {
            case 'registration_days':
                if ($user && $user['created_at']) {
                    try {
                        $regDate = new DateTime($user['created_at']);
                        $now = new DateTime();
                        $interval = $regDate->diff($now);
                        $days = (int)$interval->days;
                        return $days;
                    } catch (Exception $e) {
                        return 0;
                    }
                }
                return 0;
                
            case 'posts_count':
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND status = 'published'",
                    [$userId]
                );
                return (int)($result['count'] ?? 0);
                
            case 'comments_count':
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM comments WHERE user_id = ? AND status = 'approved'",
                    [$userId]
                );
                $count = (int)($result['count'] ?? 0);
                return $count;
                
            case 'likes_count':
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM post_likes WHERE user_id = ?",
                    [$userId]
                );
                $count = (int)($result['count'] ?? 0);
                return $count;
                
            case 'bookmarks_count':
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ?",
                    [$userId]
                );
                $count = (int)($result['count'] ?? 0);
                return $count;
                
            case 'login_days':
                $result = $this->db->fetch(
                    "SELECT COUNT(DISTINCT DATE(last_login)) as count FROM users WHERE id = ? AND last_login IS NOT NULL",
                    [$userId]
                );
                $count = (int)($result['count'] ?? 0);
                return $count;
                
            default:
                return 0;
        }
    }

    /**
    * Проверяет выполнение условия сравнения
    * @param mixed $value Значение пользователя
    * @param string $operator Оператор
    * @param mixed $expected Ожидаемое значение
    * @return bool Результат сравнения
    */
    public function evaluateCondition($value, $operator, $expected) {
        if (is_numeric($expected)) {
            $expected = (float)$expected;
            $value = (float)$value;
        }
        
        switch ($operator) {
            case '>': return $value > $expected;
            case '<': return $value < $expected;
            case '=': return $value == $expected;
            case '>=': return $value >= $expected;
            case '<=': return $value <= $expected;
            case '!=': return $value != $expected;
            default: return false;
        }
    }

    /**
    * Разблокирует достижение для пользователя 
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @return bool Результат операции
    */
    public function unlockAchievement($userId, $achievementId) {
        $data = $this->db->fetch(
            "SELECT is_unlocked FROM user_achievements_data WHERE user_id = ? AND achievement_id = ?",
            [$userId, $achievementId]
        );
        
        if ($data && $data['is_unlocked']) {
            return true;
        }
        
        $this->db->query(
            "UPDATE user_achievements_data SET is_unlocked = 1, unlocked_at = NOW(), updated_at = NOW() 
            WHERE user_id = ? AND achievement_id = ?",
            [$userId, $achievementId]
        );
        
        $achievement = $this->getAchievementById($achievementId);
        $this->triggerAchievementUnlocked($userId, $achievement);
        
        return true;
    }

    /**
    * Блокирует достижение для пользователя (снимает разблокировку) 
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @return bool Результат операции
    */
    public function lockAchievement($userId, $achievementId) {
        return $this->db->update('user_achievements_data', [
            'is_unlocked' => 0,
            'unlocked_at' => null
        ], ['user_id' => $userId, 'achievement_id' => $achievementId]);
    }

    /**
    * Назначает достижение пользователю (принудительно)
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @return bool Результат операции
    */
    public function assignAchievementToUser($userId, $achievementId) {
        $this->initUserAchievement($userId, $achievementId);

        return $this->unlockAchievement($userId, $achievementId);
    }

    /**
    * Удаляет достижение у пользователя
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @return bool Результат операции
    */
    public function removeAchievementFromUser($userId, $achievementId) {
        return $this->lockAchievement($userId, $achievementId);
    }

    /**
    * Получает количество пользователей, разблокировавших достижение 
    * @param int $achievementId ID достижения
    * @return int Количество
    */
    public function getAchievementUnlockedCount($achievementId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM user_achievements_data 
            WHERE achievement_id = ? AND is_unlocked = 1",
            [$achievementId]
        );
        return $result['count'] ?? 0;
    }

    /**
    * Получает разблокированные достижения пользователя 
    * @param int $userId ID пользователя
    * @return array Массив достижений
    */
    public function getUserUnlockedAchievements($userId) {
        return $this->db->fetchAll(
            "SELECT ua.*, uad.unlocked_at 
            FROM user_achievements ua
            JOIN user_achievements_data uad ON ua.id = uad.achievement_id
            WHERE uad.user_id = ? AND uad.is_unlocked = 1
            ORDER BY uad.unlocked_at DESC",
            [$userId]
        );
    }

    /**
    * Триггер события разблокировки достижения 
    * @param int $userId ID пользователя
    * @param array $achievement Данные достижения
    * @return bool
    */
    public function triggerAchievementUnlocked($userId, $achievement) {
        return true;
    }

    /**
    * Обновляет статистику пользователя и проверяет достижения 
    * @param int $userId ID пользователя
    * @param string $statType Тип статистики
    * @return bool Результат
    */
    public function updateUserStats($userId, $statType) {
        $achievements = $this->db->fetchAll(
            "SELECT id FROM user_achievements WHERE type = 'auto' AND is_active = 1"
        );
        
        foreach ($achievements as $achievement) {
            if ($this->checkAchievementConditions($userId, $achievement['id'])) {
                $this->unlockAchievement($userId, $achievement['id']);
            }
        }
        
        return true;
    }

    /**
    * Получает превью пользователей для ачивки 
    * @param int $achievementId ID достижения
    * @param int $limit Количество
    * @return array Массив пользователей
    */
    public function getAchievementUsersPreview($achievementId, $limit = 5) {
        return $this->db->fetchAll("
            SELECT u.id, u.username, u.display_name, u.avatar, uad.unlocked_at
            FROM users u
            INNER JOIN user_achievements_data uad ON u.id = uad.user_id
            WHERE uad.achievement_id = ? AND uad.is_unlocked = 1
            ORDER BY uad.unlocked_at DESC
            LIMIT ?
        ", [$achievementId, $limit]);
    }

    /**
    * Получает всех пользователей с ачивкой с пагинацией
    * @param int $achievementId ID достижения
    * @param int $page Номер страницы
    * @param int $perPage На странице
    * @return array Массив с пользователями и пагинацией
    */
    public function getAchievementUsers($achievementId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $users = $this->db->fetchAll("
            SELECT u.id, u.username, u.display_name, u.avatar, uad.unlocked_at
            FROM users u
            INNER JOIN user_achievements_data uad ON u.id = uad.user_id
            WHERE uad.achievement_id = ? AND uad.is_unlocked = 1
            ORDER BY uad.unlocked_at DESC
            LIMIT ? OFFSET ?
        ", [$achievementId, $perPage, $offset]);
        
        $total = $this->db->fetch("
            SELECT COUNT(*) as count
            FROM user_achievements_data
            WHERE achievement_id = ? AND is_unlocked = 1
        ", [$achievementId]);
        
        return [
            'users' => $users,
            'total' => $total['count'] ?? 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil(($total['count'] ?? 0) / $perPage)
        ];
    }

    /**
    * Получает общее количество активных пользователей 
    * @return int Количество
    */
    public function getTotalUsersCount() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        return $result['count'] ?? 0;
    }

    /**
    * Получает общее количество разблокированных ачивок
    * @return int Количество
    */
    public function getTotalUnlockedAchievements() {
        $result = $this->db->fetch("
            SELECT COUNT(DISTINCT user_id, achievement_id) as count 
            FROM user_achievements_data 
            WHERE is_unlocked = 1
        ");
        return $result['count'] ?? 0;
    }

    /**
    * Форматирует условия для отображения 
    * @param array $conditions Массив условий
    * @return array Массив отформатированных строк
    */
    public function formatConditions($conditions) {
        $formatted = [];
        $conditionTypes = [
            'registration_days' => 'Дней с регистрации',
            'comments_count' => 'Комментариев',
            'likes_count' => 'Лайков',
            'bookmarks_count' => 'Закладок',
            'login_days' => 'Дней входа'
        ];
        
        $operators = [
            '>' => 'больше',
            '<' => 'меньше',
            '=' => 'равно',
            '>=' => 'больше или равно',
            '<=' => 'меньше или равно',
            '!=' => 'не равно'
        ];
        
        foreach ($conditions as $condition) {
            $type = $conditionTypes[$condition['condition_type']] ?? $condition['condition_type'];
            $operator = $operators[$condition['operator']] ?? $condition['operator'];
            $value = $condition['value'];
            
            $formatted[] = "$type $operator $value";
        }
        
        return $formatted;
    }

    /**
    * Проверяет, имеет ли пользователь конкретную ачивку 
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @return bool true если имеет
    */
    public function userHasAchievement($userId, $achievementId) {
        $result = $this->db->fetch(
            "SELECT is_unlocked FROM user_achievements_data 
            WHERE user_id = ? AND achievement_id = ?",
            [$userId, $achievementId]
        );
        return $result && $result['is_unlocked'];
    }

    /**
    * Получает последнюю полученную ачивку пользователя
    * @param int $userId ID пользователя
    * @return array|null Данные последней ачивки или null
    */
    public function getLastAchievement($userId) {
        $sql = "SELECT ua.*, uad.unlocked_at 
                FROM user_achievements ua
                JOIN user_achievements_data uad ON ua.id = uad.achievement_id
                WHERE uad.user_id = ? 
                AND uad.is_unlocked = 1
                ORDER BY uad.unlocked_at DESC 
                LIMIT 1";
        
        $achievement = $this->db->fetch($sql, [$userId]);
        
        if ($achievement) {
            $achievement['unlocked_formatted'] = date('d.m.Y H:i', strtotime($achievement['unlocked_at']));
            $achievement['tooltip_text'] = sprintf(
                LANG_USER_MODEL_ACHIEVEMENT_TOOLTIP,
                $achievement['name'],
                $achievement['unlocked_formatted']
            );
        }
        
        return $achievement;
    }

    /**
    * Проверяет, включено ли отображение последней ачивки в настройках
    * @return bool
    */
    public static function isShowLastAchievementEnabled() {
        return (bool)\SettingsHelper::get('controller_users', 'show_last_achievement', true);
    }

}