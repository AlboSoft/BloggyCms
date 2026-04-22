<?php

namespace notifications\actions;

/**
* Действие отметки конкретного уведомления как прочитанного
* @package notifications\actions
*/
class AdminMarkAsRead extends NotificationsAction {
    
    /**
    * Метод выполнения отметки уведомления как прочитанного
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        $isAjax = $this->isAjaxRequest();
        
        if (!$id) {
            $this->sendError(LANG_ACTION_NOTIFICATIONS_ADMINMARKASREAD_ID_NOT_SPECIFIED, $isAjax);
            return;
        }
        
        try {
            $userId = $this->getCurrentUserId();
            
            $result = $this->markNotificationAsRead($id, $userId);
            
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            
            $this->handleSuccessResult($result, $unreadCount, $isAjax);
            
        } catch (\Exception $e) {
            $this->sendError(LANG_ACTION_NOTIFICATIONS_ADMINMARKASREAD_ERROR . $e->getMessage(), $isAjax);
        }
    }
    
    /**
    * Отмечает уведомление как прочитанное в базе данных 
    * @param int $id ID уведомления
    * @param int $userId ID пользователя
    * @return object Результат выполнения запроса
    * @throws \Exception Если уведомление не найдено
    */
    private function markNotificationAsRead($id, $userId) {
        $result = $this->notificationModel->markAsRead($id, $userId);
        
        if (!$result || $result->rowCount() === 0) {
            throw new \Exception(LANG_ACTION_NOTIFICATIONS_ADMINMARKASREAD_NOT_FOUND);
        }
        
        return $result;
    }
    
    /**
    * Обрабатывает успешный результат операции
    * @param object $result Результат выполнения запроса
    * @param int $unreadCount Обновленное количество непрочитанных уведомлений
    * @param bool $isAjax Флаг AJAX-запроса
    * @return void
    */
    private function handleSuccessResult($result, $unreadCount, $isAjax) {
        if ($isAjax) {
            $this->sendJsonResponse(true, LANG_ACTION_NOTIFICATIONS_ADMINMARKASREAD_SUCCESS, [
                'unread_count' => $unreadCount
            ]);
        } else {
            \Notification::success(LANG_ACTION_NOTIFICATIONS_ADMINMARKASREAD_SUCCESS);
            $this->redirectToPreviousPage();
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