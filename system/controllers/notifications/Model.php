<?php

/**
* Модель для работы с уведомлениями в базе данных
* @package Models
*/
class NotificationModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getUserNotifications',
        'getUserNotificationsWithDetails',
        'getUnreadCount',
        'getStats',
        'markAsRead',
        'markAllAsRead',
        'delete',
        'clearRead'
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
    * Добавляет новое уведомление в базу данных
    * @param array $data Данные уведомления
    * @return bool|int Результат выполнения запроса
    */
    public function add($data) {
        $sql = "INSERT INTO notifications (type, title, message, data, user_id, created_by) 
                VALUES (:type, :title, :message, :data, :user_id, :created_by)";
        
        return $this->db->query($sql, [
            ':type' => $data['type'],
            ':title' => $data['title'],
            ':message' => $data['message'],
            ':data' => json_encode($data['data'] ?? []),
            ':user_id' => $data['user_id'] ?? null,
            ':created_by' => $data['created_by'] ?? null
        ]);
    }

    /**
    * Получает список всех администраторов системы
    * @return array Массив пользователей с правами администратора
    */
    private function getAdminUsers() {
        $sql = "SELECT id FROM users WHERE is_admin = 1 OR role = 'admin'";
        return $this->db->fetchAll($sql);
    }

    /**
    * Получает уведомления для конкретного пользователя 
    * @param int $userId ID пользователя
    * @param int $limit Максимальное количество записей (по умолчанию 10)
    * @param int $offset Смещение для пагинации (по умолчанию 0)
    * @param bool $unreadOnly Только непрочитанные (по умолчанию false)
    * @return array Массив уведомлений
    */
    public function getUserNotifications($userId, $limit = 10, $offset = 0, $unreadOnly = false) {
        $sql = "SELECT n.*, u.username as created_by_username
                FROM notifications n
                LEFT JOIN users u ON n.created_by = u.id
                WHERE n.user_id = :user_id";
        
        $params = [':user_id' => $userId];
        
        if ($unreadOnly) {
            $sql .= " AND n.is_read = 0";
        }
        
        $sql .= " ORDER BY n.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
    * Получает количество непрочитанных уведомлений пользователя
    * @param int $userId ID пользователя
    * @return int Количество непрочитанных уведомлений
    */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = :user_id AND is_read = 0";
        
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        return $result['count'] ?? 0;
    }

    /**
    * Отмечает конкретное уведомление как прочитанное 
    * @param int $id ID уведомления
    * @param int $userId ID пользователя (для проверки владельца)
    * @return bool Результат выполнения запроса
    */
    public function markAsRead($id, $userId) {
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->query($sql, [
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    /**
    * Отмечает все уведомления пользователя как прочитанные
    * @param int $userId ID пользователя
    * @return bool Результат выполнения запроса
    */
    public function markAllAsRead($userId) {
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE user_id = :user_id AND is_read = 0";
        
        return $this->db->query($sql, [':user_id' => $userId]);
    }

    /**
    * Удаляет конкретное уведомление 
    * @param int $id ID уведомления
    * @param int $userId ID пользователя (для проверки владельца)
    * @return bool Результат выполнения запроса
    */
    public function delete($id, $userId) {
        $sql = "DELETE FROM notifications WHERE id = :id AND user_id = :user_id";
        return $this->db->query($sql, [
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    /**
    * Удаляет все прочитанные уведомления пользователя
    * @param int $userId ID пользователя
    * @return bool Результат выполнения запроса
    */
    public function clearRead($userId) {
        $sql = "DELETE FROM notifications WHERE user_id = :user_id AND is_read = 1";
        return $this->db->query($sql, [':user_id' => $userId]);
    }

    /**
    * Получает статистику уведомлений для пользователя 
    * @param int $userId ID пользователя
    * @return array Статистика с полями
    */
    public function getStats($userId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count,
                    COUNT(DISTINCT type) as types_count
                FROM notifications 
                WHERE user_id = :user_id";
        
        return $this->db->fetch($sql, [':user_id' => $userId]);
    }

    /**
    * Добавляет уведомление о новом комментарии с расширенными данными
    * @param int $commentId ID комментария
    * @param array $commentData Дополнительные данные комментария (опционально)
    * @return bool true при успешном добавлении, false при ошибке
    */
    public function addNewCommentNotification($commentId, $commentData = []) {
        try {

            $commentModel = new \CommentModel($this->db);
            $comment = $commentModel->getCommentById($commentId);
            
            if (!$comment) {
                return false;
            }
            
            $postModel = new \PostModel($this->db);
            $post = $postModel->getById($comment['post_id']);
            
            $authorName = $comment['author_name'] ?? 'Аноним';
            if (!empty($comment['user_id'])) {
                $userModel = new \UserModel($this->db);
                $user = $userModel->getById($comment['user_id']);
                if ($user) {
                    $authorName = $user['display_name'] ?? $user['username'] ?? $authorName;
                }
            }
            
            $contentPreview = $comment['content'];
            if (mb_strlen($contentPreview) > 150) {
                $contentPreview = mb_substr($contentPreview, 0, 150) . '...';
            }
            
            $data = [
                'type' => 'new_comment',
                'title' => 'Новый комментарий на модерацию',
                'message' => "{$authorName} оставил комментарий",
                'data' => [
                    'comment_id' => $commentId,
                    'post_id' => $comment['post_id'],
                    'post_title' => $post['title'] ?? 'Неизвестный пост',
                    'post_slug' => $post['slug'] ?? '',
                    'author_name' => $authorName,
                    'author_email' => $comment['author_email'] ?? null,
                    'content_preview' => $contentPreview,
                    'content_full' => $comment['content'],
                    'created_at' => $comment['created_at']
                ],
                'created_by' => $comment['user_id'] ?? null
            ];
            
            $admins = $this->getAdminUsers();
            foreach ($admins as $admin) {
                $data['user_id'] = $admin['id'];
                $this->add($data);
            }
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
    * Получает уведомления пользователя с расширенными данными
    * @param int $userId ID пользователя
    * @param int $limit Максимальное количество записей (по умолчанию 10)
    * @param int $offset Смещение для пагинации (по умолчанию 0)
    * @param bool $unreadOnly Только непрочитанные (по умолчанию false)
    * @return array Массив уведомлений с дополнительными данными
    */
    public function getUserNotificationsWithDetails($userId, $limit = 10, $offset = 0, $unreadOnly = false) {
        $sql = "SELECT n.*, u.username as created_by_username,
                       u.avatar as created_by_avatar,
                       u.display_name as created_by_display_name
                FROM notifications n
                LEFT JOIN users u ON n.created_by = u.id
                WHERE n.user_id = :user_id";
        
        $params = [':user_id' => $userId];
        
        if ($unreadOnly) {
            $sql .= " AND n.is_read = 0";
        }
        
        $sql .= " ORDER BY n.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $notifications = $this->db->fetchAll($sql, $params);
        
        foreach ($notifications as &$notification) {
            $data = json_decode($notification['data'] ?? '{}', true);
            
            if ($notification['type'] === 'new_comment' && !empty($data['post_id'])) {
                try {
                    $postModel = new \PostModel($this->db);
                    $post = $postModel->getById($data['post_id']);
                    
                    if ($post) {
                        $data['post_url'] = BASE_URL . '/post/' . $post['slug'];
                        $data['post_title'] = $post['title'];
 
                        if (empty($data['content_preview']) && !empty($data['content_full'])) {
                            $content = $data['content_full'];
                            $data['content_preview'] = mb_strlen($content) > 150 
                                ? mb_substr($content, 0, 150) . '...' 
                                : $content;
                        }
                    }
                } catch (\Exception $e) {
                }
            }
            
            $notification['data'] = json_encode($data);
        }
        
        return $notifications;
    }

    /**
    * Добавляет уведомление о регистрации нового пользователя
    * @param int $userId ID созданного пользователя
    * @param array $userData Данные пользователя
    * @return bool
    */
    public function addNewUserNotification($userId, $userData) {
        try {

            $notifyEnabled = \SettingsHelper::get('controller_notifications', 'notify_on_user_registration', true);
            
            if (!$notifyEnabled) {
                return false;
            }
            
            $username = $userData['username'] ?? '';
            $email = $userData['email'] ?? '';
            $displayName = $userData['display_name'] ?? $username;
            
            $userModel = new \UserModel($this->db);
            
            $admins = $userModel->getAdmins();
            
            if (empty($admins)) {
                return false;
            }
            
            $message = "Зарегистрирован новый пользователь: {$displayName}";
            
            foreach ($admins as $admin) {
                $this->add([
                    'type' => 'new_user',
                    'title' => 'Новый пользователь',
                    'message' => $message,
                    'data' => json_encode([
                        'user_id' => $userId,
                        'username' => $username,
                        'display_name' => $displayName,
                        'email' => $email,
                        'created_at' => date('Y-m-d H:i:s')
                    ]),
                    'user_id' => $admin['id'],
                    'created_by' => $userId
                ]);
            }
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }

}