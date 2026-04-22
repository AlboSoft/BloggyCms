<?php

namespace notifications\actions;

/**
* Действие отметки всех уведомлений как прочитанных
* @package notifications\actions
*/
class AdminMarkAllAsRead extends NotificationsAction {
    
    /**
    * Метод выполнения отметки всех уведомлений как прочитанных
    * @return void
    */
    public function execute() {
        $isAjax = $this->isAjaxRequest();
        
        try {
            $userId = $this->getCurrentUserId();
            
            $this->checkUnreadNotificationsExist($userId, $isAjax);
            
            $result = $this->notificationModel->markAllAsRead($userId);
            
            $this->handleMarkAllResult($result, $isAjax);
            
        } catch (\Exception $e) {
            $this->handleMarkAllError($e, $isAjax);
        }
        
        if (!$isAjax) {
            $this->redirectToPreviousPage();
        }
    }
    
    /**
    * Проверяет наличие непрочитанных уведомлений перед отметкой
    * @param int $userId ID пользователя
    * @param bool $isAjax Флаг AJAX-запроса
    * @throws \Exception Если нет непрочитанных уведомлений (через отправку ответа)
    * @return void
    */
    private function checkUnreadNotificationsExist($userId, $isAjax) {
        $unreadCount = $this->notificationModel->getUnreadCount($userId);
        
        if ($unreadCount == 0) {
            if ($isAjax) {
                $this->sendJsonResponse(false, LANG_ACTION_NOTIFICATIONS_ADMINMARKALLASREAD_NO_UNREAD);
                exit;
            } else {
                \Notification::warning(LANG_ACTION_NOTIFICATIONS_ADMINMARKALLASREAD_NO_UNREAD);
                $this->redirect(ADMIN_URL . '/notifications');
                exit;
            }
        }
    }
    
    /**
    * Обрабатывает результат операции отметки всех уведомлений 
    * @param bool $result Результат операции
    * @param bool $isAjax Флаг AJAX-запроса
    * @throws \Exception Если операция не удалась
    * @return void
    */
    private function handleMarkAllResult($result, $isAjax) {
        if ($result) {
            if ($isAjax) {
                $this->sendJsonResponse(true, LANG_ACTION_NOTIFICATIONS_ADMINMARKALLASREAD_SUCCESS, [
                    'unread_count' => 0
                ]);
            } else {
                \Notification::success(LANG_ACTION_NOTIFICATIONS_ADMINMARKALLASREAD_SUCCESS);
            }
        } else {
            throw new \Exception(LANG_ACTION_NOTIFICATIONS_ADMINMARKALLASREAD_FAILED);
        }
    }
    
    /**
    * Обрабатывает ошибки, возникшие при отметке уведомлений 
    * @param \Exception $e Исключение
    * @param bool $isAjax Флаг AJAX-запроса
    * @return void
    */
    private function handleMarkAllError($e, $isAjax) {
        $errorMessage = LANG_ACTION_NOTIFICATIONS_ADMINMARKALLASREAD_ERROR . $e->getMessage();
        
        if ($isAjax) {
            $this->sendJsonResponse(false, $errorMessage, [], 500);
        } else {
            \Notification::error($errorMessage);
        }
    }
    
    /**
    * Отправляет JSON-ответ для AJAX-запросов 
    * @param bool $success Флаг успешности операции
    * @param string $message Сообщение для пользователя
    * @param array $extra Дополнительные данные для ответа
    * @param int $httpCode HTTP-код ответа (по умолчанию 200)
    * @return void
    */
    private function sendJsonResponse($success, $message, $extra = [], $httpCode = 200) {
        if (!$success && $httpCode === 200) {
            $httpCode = 400;
        }
        
        http_response_code($httpCode);
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
    * Перенаправляет на предыдущую страницу или на страницу уведомлений
    * @return void
    */
    private function redirectToPreviousPage() {
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? ADMIN_URL . '/notifications';
        $this->redirect($redirectUrl);
    }
}