<?php

namespace notifications\actions;

/**
* Действие удаления конкретного уведомления
* @package notifications\actions
*/
class AdminDelete extends NotificationsAction {
    
    /**
    * Метод выполнения удаления уведомления
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        $isAjax = $this->isAjaxRequest();
        
        if (!$id) {
            $this->sendError(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_ID_NOT_SPECIFIED, $isAjax);
            return;
        }
        
        try {
            $userId = $this->getCurrentUserId();
            
            $this->validateNotificationAccess($id, $userId);
            
            $result = $this->deleteNotification($id, $userId);
            
            $this->handleDeleteResult($result, $userId, $isAjax);
            
        } catch (\Exception $e) {
            $this->sendError(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_ERROR . $e->getMessage(), $isAjax);
            return;
        }
        
        if (!$isAjax) {
            $this->redirectToPreviousPage();
        }
    }
    
    /**
    * Проверяет существование уведомления и права доступа к нему 
    * @param int $id ID уведомления
    * @param int $userId ID пользователя
    * @throws \Exception Если уведомление не найдено или доступ запрещен
    * @return void
    */
    private function validateNotificationAccess($id, $userId) {
        $notifications = $this->notificationModel->getUserNotifications($userId, 1, 0, false);
        
        $notificationExists = false;
        foreach ($notifications as $notification) {
            if ($notification['id'] == $id) {
                $notificationExists = true;
                break;
            }
        }
        
        if (!$notificationExists) {
            throw new \Exception(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_NOT_FOUND);
        }
    }
    
    /**
    * Выполняет удаление уведомления из базы данных
    * @param int $id ID уведомления
    * @param int $userId ID пользователя
    * @return object Результат выполнения запроса
    * @throws \Exception Если не удалось удалить уведомление
    */
    private function deleteNotification($id, $userId) {
        $result = $this->notificationModel->delete($id, $userId);
        
        if (!$result || $result->rowCount() === 0) {
            throw new \Exception(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_DELETE_FAILED);
        }
        
        return $result;
    }
    
    /**
    * Обрабатывает результат успешного удаления
    * @param object $result Результат выполнения запроса
    * @param int $userId ID пользователя
    * @param bool $isAjax Флаг AJAX-запроса
    * @return void
    */
    private function handleDeleteResult($result, $userId, $isAjax) {
        if ($isAjax) {
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            
            $this->sendJsonResponse(true, LANG_ACTION_NOTIFICATIONS_ADMINDELETE_SUCCESS, [
                'unread_count' => $unreadCount
            ]);
        } else {
            \Notification::success(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_SUCCESS);
        }
    }
    
    /**
    * Отправляет успешный JSON-ответ с дополнительными данными
    * @param bool $success Флаг успешности операции
    * @param string $message Сообщение для пользователя
    * @param array $extra Дополнительные данные для ответа
    * @return void
    */
    private function sendJsonResponse($success, $message, $extra = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        ));
    }
    
    /**
    * Отправляет сообщение об ошибке в зависимости от типа запроса
    * @param string $message Сообщение об ошибке
    * @param bool $isAjax Флаг AJAX-запроса
    * @return void
    */
    private function sendError($message, $isAjax) {
        if ($isAjax) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
        } else {
            \Notification::error($message);
            $this->redirectToPreviousPage();
        }
    }
    
    /**
    * Перенаправляет на предыдущую страницу или на страницу уведомлений
    * @return void
    */
    private function redirectToPreviousPage() {
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? ADMIN_URL . '/notifications';
        $this->redirect($redirectUrl);
    }
}