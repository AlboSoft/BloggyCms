<?php
class User implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getById',
        'getByUsername',
        'authenticate',
        'authenticateByEmail',
        'getTotalCount',
        'getActiveUsers',
        'updatePassword'
    ];

    private $db;

    /**
    * Конструктор класса пользователя
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
    * Аутентификация пользователя по имени и паролю
    * @param string $username Имя пользователя
    * @param string $password Пароль в открытом виде
    * @return array|false Массив данных пользователя при успехе, false при неудаче
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
    * Получение пользователя по ID
    * @param int $id Идентификатор пользователя
    * @return array|null Массив данных пользователя или null если не найден
    */
    public function getById($id) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        
        if ($user) {
            $user['display_name'] = $user['display_name'] ?? '';
            $user['bio'] = $user['bio'] ?? '';
            $user['website'] = $user['website'] ?? '';
            $user['avatar'] = $user['avatar'] ?? 'default.jpg';
            $user['status'] = $user['status'] ?? 'active';
        }
        
        return $user;
    }

    /**
    * Обновление пароля пользователя с проверкой текущего
    * @param int $id Идентификатор пользователя
    * @param string $currentPassword Текущий пароль для проверки
    * @param string $newPassword Новый пароль
    * @return bool true при успешном обновлении, false при ошибке верификации
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
    * Получение всех пользователей с фильтрацией
    * @param string|null $status Фильтр по статусу (active/inactive)
    * @return array Массив пользователей, удовлетворяющих фильтрам
    */
    public function getAllWithFilters($status = null) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
    * Поиск пользователя по имени пользователя
    * @param string $username Имя пользователя для поиска
    * @return array|null Данные пользователя или null если не найден
    */
    public function getByUsername($username) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE username = ?",
            [$username]
        );
    }

    /**
    * Поиск пользователя по email адресу
    * @param string $email Email адрес для поиска
    * @return array|null Данные пользователя или null если не найден
    */
    public function getByEmail($email) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }

    /**
    * Создание нового пользователя в базе данных
    * @param array $data Ассоциативный массив данных пользователя
    * @return int ID созданного пользователя
    * @throws Exception При ошибке выполнения SQL запроса
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
        return $this->db->lastInsertId();
    }

    /**
    * Обновление данных пользователя
    * @param int $id Идентификатор пользователя
    * @param array $data Ассоциативный массив обновляемых полей и значений
    * @return bool|int true/количество обновленных строк при успехе, false при ошибке
    */
    public function update($id, $data) {

        if (method_exists($this->db, 'update')) {
            $validFields = ['display_name', 'email', 'website', 'bio', 'avatar', 'password', 'username', 'status'];
            $filteredData = array_intersect_key($data, array_flip($validFields));
            
            return $this->db->update('users', $filteredData, ['id' => $id]);
        } else {
            $fields = [];
            $values = [];

            foreach ($data as $field => $value) {
                $fields[] = "{$field} = ?";
                $values[] = $value;
            }

            $values[] = $id;

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            return $this->db->query($sql, $values);
        }
    }

    /**
    * Удаление пользователя из базы данных
    * @param int $id Идентификатор удаляемого пользователя
    * @return bool|int true/количество удаленных строк при успехе
    * @throws Exception При ошибке выполнения SQL запроса
    */
    public function delete($id) {
        return $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
    }
    
    /**
    * Получение общего количества пользователей в системе
    * @return int Количество пользователей в базе данных
    */
    public function getTotalCount() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM users");
        return $result['count'] ?? 0;
    }
    
    /**
    * Получение списка администраторов системы
    * @return array Массив администраторов
    */
    public function getAdmins() {
        return $this->db->fetchAll("SELECT * FROM users WHERE is_admin = 1 ORDER BY username");
    }
    
    /**
    * Получение списка активных пользователей
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
    * Аутентификация пользователя по email и паролю
    * @param string $email Email пользователя
    * @param string $password Пароль в открытом виде
    * @return array|false Массив данных пользователя при успехе, false при неудаче
    */
    public function authenticateByEmail($email, $password) {
        $user = $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        return $user;
    }

}