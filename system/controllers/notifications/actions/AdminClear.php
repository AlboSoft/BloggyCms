<?php

namespace notifications\actions;

/**
* Действие очистки всех прочитанных уведомлений
* @package notifications\actions
* @extends NotificationsAction
*/
class AdminClear extends NotificationsAction {
    
    /**
    * Метод выполнения очистки прочитанных уведомлений
    * @return void
    */
    public function execute() {
        $isAjax = $this->isAjaxRequest();
        
        try {
            $userId = $this->getCurrentUserId();
            
            $this->checkReadNotificationsExist($userId, $isAjax);
            
            $result = $this->notificationModel->clearRead($userId);
            
            $this->handleClearResult($result, $isAjax);
            
        } catch (\Exception $e) {
            $this->handleClearError($e, $isAjax);
        }
        
        if (!$isAjax) {
            $this->redirect(ADMIN_URL . '/notifications');
        }
    }
    
    /**
    * Проверяет наличие прочитанных уведомлений перед очисткой 
    * @param int $userId ID пользователя
    * @param bool $isAjax Флаг AJAX-запроса
    * @throws \Exception Если нет прочитанных уведомлений
    * @return void
    */
    private function checkReadNotificationsExist($userId, $isAjax) {
        $stats = $this->notificationModel->getStats($userId);
        
        if ($stats['read_count'] == 0) {
            if ($isAjax) {
                $this->sendJsonResponse(false, LANG_ACTION_NOTIFICATIONS_ADMINCLEAR_NO_READ);
                exit;
            } else {
                \Notification::warning(LANG_ACTION_NOTIFICATIONS_ADMINCLEAR_NO_READ);
                $this->redirect(ADMIN_URL . '/notifications');
                exit;
            }
        }
    }
    
    /**
    * Обрабатывает результат операции очистки 
    * @param bool $result Результат операции очистки
    * @param bool $isAjax Флаг AJAX-запроса
    * @throws \Exception Если операция не удалась
    * @return void
    */
    private function handleClearResult($result, $isAjax) {
        if ($result) {
            if ($isAjax) {
                $this->sendJsonResponse(true, LANG_ACTION_NOTIFICATIONS_ADMINCLEAR_SUCCESS);
            } else {
                \Notification::success(LANG_ACTION_NOTIFICATIONS_ADMINCLEAR_SUCCESS);
            }
        } else {
            throw new \Exception(LANG_ACTION_NOTIFICATIONS_ADMINCLEAR_FAILED);
        }
    }
    
    /**
    * Обрабатывает ошибки, возникшие при очистке уведомлений
    * @param \Exception $e Исключение
    * @param bool $isAjax Флаг AJAX-запроса
    * @return void
    */
    private function handleClearError($e, $isAjax) {
        $errorMessage = LANG_ACTION_NOTIFICATIONS_ADMINCLEAR_ERROR . $e->getMessage();
        
        if ($isAjax) {
            $this->sendJsonResponse(false, $errorMessage, 500);
        } else {
            \Notification::error($errorMessage);
        }
    }
    
    /**
    * Отправляет JSON-ответ для AJAX-запросов
    * @param bool $success Флаг успешности операции
    * @param string $message Сообщение для пользователя
    * @param int $httpCode HTTP-код ответа (по умолчанию 200)
    * @return void
    */
    private function sendJsonResponse($success, $message, $httpCode = 200) {
        if (!$success && $httpCode === 200) {
            $httpCode = 400;
        }
        
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
    }
}