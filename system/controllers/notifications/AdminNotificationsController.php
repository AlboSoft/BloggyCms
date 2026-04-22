<?php

/**
* Контроллер управления уведомлениями в административной панели
* @package Controllers
*/
class AdminNotificationsController extends Controller {
    
    private $notificationModel;
    private $userModel;
    
    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_NOTIFICATIONS_MANIFEST_NAME,
        'author' => 'BloggyCMS',
        'version' => '1.0.0',
        'has_settings' => false,
        'description' => LANG_CONTROLLER_NOTIFICATIONS_MANIFEST_DESCRIPTION
    ];
    
    /**
    * Конструктор контроллера
    * @param object $db Подключение к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        
        $this->notificationModel = new NotificationModel($db);
        $this->userModel = new UserModel($db);
        
    }
    
    /**
    * Обрабатывает ситуацию с отсутствием прав доступа
    * @return void
    */
    private function handleAccessDenied() {
        if ($this->isAjaxRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            die(json_encode([
                'success' => false,
                'message' => LANG_CONTROLLER_NOTIFICATIONS_ACCESS_DENIED
            ]));
        } else {
            Notification::error(LANG_CONTROLLER_NOTIFICATIONS_ACCESS_DENIED);
            $this->redirect(ADMIN_URL . '/login');
            exit;
        }
    }
    
    /**
    * Проверяет, является ли текущий запрос AJAX-запросом 
    * @return bool true если запрос AJAX, false в противном случае
    */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Получить количество непрочитанных уведомлений (AJAX) 
    * @return void
    */
    public function getUnreadCountAction() {
        try {
            $userId = Auth::getUserId();
            $count = $this->notificationModel->getUnreadCount($userId);
            
            $this->jsonSuccess(['count' => (int)$count]);
            
        } catch (\Exception $e) {
            $this->jsonError(LANG_CONTROLLER_NOTIFICATIONS_GET_ERROR . $e->getMessage(), 500);
        }
    }
    
    /**
    * Получить список уведомлений (AJAX)
    * @return void
    */
    public function getListAction() {
        try {
            $userId = Auth::getUserId();
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = (int)($_GET['offset'] ?? 0);
            $unreadOnly = isset($_GET['unread_only']) ? (bool)$_GET['unread_only'] : false;
            
            $notifications = $this->notificationModel->getUserNotificationsWithDetails(
                $userId, 
                $limit, 
                $offset,
                $unreadOnly
            );
            
            $formatted = [];
            foreach ($notifications as $notification) {
                $formatted[] = $this->formatNotification($notification);
            }
            
            $this->jsonSuccess([
                'notifications' => $formatted,
                'total' => count($formatted)
            ]);
            
        } catch (\Exception $e) {
            $this->jsonError(LANG_CONTROLLER_NOTIFICATIONS_GET_ERROR . $e->getMessage(), 500);
        }
    }
    
    /**
    * Главная страница уведомлений
    * @return void
    */
    public function adminIndexAction() {
        $action = new \notifications\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отметить конкретное уведомление как прочитанное
    * @param int $id ID уведомления
    * @return void
    */
    public function adminMarkAsReadAction($id) {
        $action = new \notifications\actions\AdminMarkAsRead($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отметить все уведомления пользователя как прочитанные 
    * @return void
    */
    public function adminMarkAllAsReadAction() {
        $action = new \notifications\actions\AdminMarkAllAsRead($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Удалить конкретное уведомление
    * @param int $id ID уведомления
    * @return void
    */
    public function adminDeleteAction($id) {
        $action = new \notifications\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Очистить все прочитанные уведомления
    * @return void
    */
    public function adminClearAction() {
        $action = new \notifications\actions\AdminClear($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Форматирует уведомление для вывода в интерфейсе
    * @param array $notification Данные уведомления из БД
    * @return array Отформатированное уведомление
    */
    private function formatNotification($notification) {
        $data = json_decode($notification['data'] ?? '{}', true);
        
        $result = [
            'id' => (int)$notification['id'],
            'type' => $notification['type'],
            'data' => $data,
            'is_read' => (bool)$notification['is_read'],
            'icon' => $this->getNotificationIcon($notification['type']),
            'color' => $this->getNotificationColor($notification['type']),
            'time' => $this->formatTime($notification['created_at']),
            'time_full' => date('d.m.Y H:i', strtotime($notification['created_at'])),
            'created_by' => $notification['created_by_display_name'] ?? 
                        $notification['created_by_username'] ?? null,
            'created_at' => $notification['created_at']
        ];
        
        switch ($notification['type']) {
            case 'new_comment':
                return $this->formatCommentNotification($notification, $data, $result);
            case 'new_user':
                return $this->formatNewUserNotification($notification, $data, $result);
            case 'system':
                return $this->formatSystemNotification($notification, $data, $result);
            case 'system_error':
                return $this->formatErrorNotification($notification, $data, $result);
            default:
                return array_merge($result, [
                    'title' => $notification['title'],
                    'message' => $notification['message']
                ]);
        }
    }

    private function formatErrorNotification($notification, $data, $result) {
        $errorLink = ADMIN_URL . '/debug';
        
        $message = $notification['message'];
        $message .= '<div class="mt-2">';
        $message .= '<a href="' . $errorLink . '" class="btn btn-sm btn-outline-danger">';
        $message .= '<i class="bi bi-bug me-1"></i>' . LANG_CONTROLLER_NOTIFICATIONS_GO_TO_ERROR;
        $message .= '</a>';
        $message .= '</div>';
        
        return array_merge($result, [
            'title' => $notification['title'],
            'message' => $message
        ]);
    }
    
    /**
    * Форматирует уведомление о новом комментарии 
    * @param array $notification Исходное уведомление
    * @param array $data Дополнительные данные
    * @param array $result Базовый результат форматирования
    * @return array Отформатированное уведомление
    */
    private function formatCommentNotification($notification, $data, $result) {
        $postTitle = $data['post_title'] ?? LANG_CONTROLLER_NOTIFICATIONS_UNKNOWN_POST;
        $authorName = $data['author_name'] ?? LANG_CONTROLLER_NOTIFICATIONS_ANONYMOUS;
        $contentPreview = $data['content_preview'] ?? '';
        
        $message = sprintf(LANG_CONTROLLER_NOTIFICATIONS_COMMENT_MESSAGE, $authorName, $postTitle);
        
        if (!empty($contentPreview)) {
            $message .= "<br><br><em>\"{$contentPreview}\"</em>";
        }
        
        return array_merge($result, [
            'title' => LANG_CONTROLLER_NOTIFICATIONS_COMMENT_TITLE,
            'message' => $message
        ]);
    }
    
    /**
    * Форматирует уведомление о новом пользователе
    * @param array $notification Исходное уведомление
    * @param array $data Дополнительные данные
    * @param array $result Базовый результат форматирования
    * @return array Отформатированное уведомление
    */
    private function formatNewUserNotification($notification, $data, $result) {
        $userName = $data['username'] ?? $notification['created_by_username'] ?? LANG_CONTROLLER_NOTIFICATIONS_NEW_USER;
        
        return array_merge($result, [
            'title' => LANG_CONTROLLER_NOTIFICATIONS_NEW_USER_TITLE,
            'message' => sprintf(LANG_CONTROLLER_NOTIFICATIONS_NEW_USER_MESSAGE, $userName)
        ]);
    }
    
    /**
    * Форматирует системное уведомление
    * @param array $notification Исходное уведомление
    * @param array $data Дополнительные данные
    * @param array $result Базовый результат форматирования
    * @return array Отформатированное уведомление
    */
    private function formatSystemNotification($notification, $data, $result) {
        return array_merge($result, [
            'title' => $notification['title'] ?? LANG_CONTROLLER_NOTIFICATIONS_SYSTEM_TITLE,
            'message' => $notification['message'] ?? ''
        ]);
    }
    
    /**
    * Получить иконку для типа уведомления
    * @param string $type Тип уведомления
    * @return string Название иконки Bootstrap Icons
    */
    private function getNotificationIcon($type) {
        $icons = [
            'new_comment' => 'chat-dots',
            'new_user' => 'person-plus',
            'system' => 'gear',
            'system_error' => 'bug',
            'warning' => 'exclamation-triangle',
            'info' => 'info-circle'
        ];
        return $icons[$type] ?? 'bell';
    }
    
    /**
    * Получить цвет для типа уведомления (для Bootstrap классов)
    * @param string $type Тип уведомления
    * @return string Название цвета Bootstrap
    */
    private function getNotificationColor($type) {
        $colors = [
            'new_comment' => 'primary',
            'new_user' => 'success',
            'system' => 'secondary',
            'system_error' => 'danger',
            'warning' => 'warning',
            'info' => 'info'
        ];
        return $colors[$type] ?? 'secondary';
    }
    
    /**
    * Форматирует время в человекочитаемый формат
    * @param string $datetime Дата и время в формате MySQL
    * @return string Отформатированное время
    */
    private function formatTime($datetime) {
        $now = time();
        $time = strtotime($datetime);
        $diff = $now - $time;
        
        if ($diff < 60) {
            return LANG_CONTROLLER_NOTIFICATIONS_JUST_NOW;
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' ' . $this->pluralize($minutes, explode('|', LANG_CONTROLLER_NOTIFICATIONS_MINUTES_FORMS)) . ' ' . LANG_CONTROLLER_NOTIFICATIONS_AGO;
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' ' . $this->pluralize($hours, explode('|', LANG_CONTROLLER_NOTIFICATIONS_HOURS_FORMS)) . ' ' . LANG_CONTROLLER_NOTIFICATIONS_AGO;
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' ' . $this->pluralize($days, explode('|', LANG_CONTROLLER_NOTIFICATIONS_DAYS_FORMS)) . ' ' . LANG_CONTROLLER_NOTIFICATIONS_AGO;
        } else {
            return date('d.m.Y', $time);
        }
    }
    
    /**
    * Склонение числительных для русского языка 
    * @param int $number Число
    * @param array $forms Массив форм слова [именительный, родительный единственное, родительное множественное]
    * @return string Правильная форма слова
    */
    private function pluralize($number, $forms) {
        $cases = [2, 0, 1, 1, 1, 2];
        return $forms[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
    
    /**
    * Отправляет успешный JSON-ответ
    * @param array $data Дополнительные данные для ответа
    * @return void
    */
    private function jsonSuccess($data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }
    
    /**
    * Отправляет JSON-ответ с ошибкой
    * @param string $message Сообщение об ошибке
    * @param int $code HTTP-код ответа (по умолчанию 400)
    * @return void
    */
    private function jsonError($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}