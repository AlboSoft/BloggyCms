<?php

namespace notifications\actions;

class AdminDelete extends NotificationsAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        $isAjax = $this->isAjaxRequest();
        
        if (!$id) {
            $this->sendError(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_ID_NOT_SPECIFIED, $isAjax);
            return;
        }
        
        try {
            $userId = $this->getCurrentUserId();
            
            $notification = $this->notificationModel->getNotificationById($id);
            
            if (!$notification) {
                throw new \Exception(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_NOT_FOUND);
            }
            
            if ($notification['user_id'] != $userId) {
                throw new \Exception(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_ACCESS_DENIED);
            }
            
            $result = $this->notificationModel->delete($id, $userId);
            
            if ($result && $result->rowCount() > 0) {
                if ($isAjax) {
                    $unreadCount = $this->notificationModel->getUnreadCount($userId);
                    $this->sendJsonResponse(true, LANG_ACTION_NOTIFICATIONS_ADMINDELETE_SUCCESS, [
                        'unread_count' => $unreadCount
                    ]);
                } else {
                    \Notification::success(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_SUCCESS);
                    $this->redirectToPreviousPage();
                }
            } else {
                throw new \Exception(LANG_ACTION_NOTIFICATIONS_ADMINDELETE_DELETE_FAILED);
            }
            
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), $isAjax);
        }
    }
    
    /**
    * Отправляет успешный JSON-ответ
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
        exit;
    }
    
    /**
    * Отправляет сообщение об ошибке
    */
    private function sendError($message, $isAjax) {
        if ($isAjax) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
            exit;
        } else {
            \Notification::error($message);
            $this->redirectToPreviousPage();
        }
    }
    
    private function redirectToPreviousPage() {
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? ADMIN_URL . '/notifications';
        $this->redirect($redirectUrl);
    }
}