<?php

namespace comments\actions;

/**
* Действие добавления нового комментария
* @package comments\actions
*/
class Add extends CommentAction {
    
    /**
    * Метод выполнения добавления комментария
    * @return void
    */
    public function execute() {

        $isAjax = $this->isAjaxRequest();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                http_response_code(405);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => LANG_ACTION_COMMENTS_ADD_INVALID_METHOD
                ]);
            } else {
                \Notification::error(LANG_ACTION_COMMENTS_ADD_INVALID_METHOD);
                $this->redirect(BASE_URL);
            }
            return;
        }
        
        try {
            if (!\AuthHelper::canAddComment()) {
                $this->sendError(LANG_ACTION_COMMENTS_ADD_NO_PERMISSION, $isAjax);
                return;
            }

            if (empty($_POST['post_id'])) {
                $this->sendError(LANG_ACTION_COMMENTS_ADD_NO_POST_ID, $isAjax);
                return;
            }

            if (empty($_POST['content'])) {
                $this->sendError(LANG_ACTION_COMMENTS_ADD_EMPTY_CONTENT, $isAjax);
                return;
            }

            $authorName = LANG_ACTION_COMMENTS_ADD_ANONYMOUS;
            $currentUserId = $this->getCurrentUserId();
            
            if ($currentUserId) {
                $user = $this->userModel->getById($currentUserId);
                $authorName = $user['display_name'] ?? $user['username'] ?? LANG_ACTION_COMMENTS_ADD_USER;
            } elseif (!empty($_POST['author_name'])) {
                $authorName = $_POST['author_name'];
            }

            $status = 'pending';
            if (\AuthHelper::canAddCommentWithoutModeration() || $this->isAdmin()) {
                $status = 'approved';
            }

            $data = [
                'post_id' => (int)$_POST['post_id'],
                'user_id' => $currentUserId,
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'author_name' => $authorName,
                'author_email' => $_POST['author_email'] ?? null,
                'content' => trim($_POST['content']),
                'status' => $status
            ];

            $result = $this->commentModel->addComment($data);
            
            if ($result) {
                $commentId = $this->db->lastInsertId();
                
                if ($currentUserId) {
                    try {
                        $achievementTriggers = new \AchievementTriggers($this->db);
                        $achievementTriggers->onCommentCreated($currentUserId);
                    } catch (\Exception $e) {}
                }
                
                $notificationSetting = \SettingsHelper::get('controller_notifications', 'variables', 'pending');
        
                if ($notificationSetting === 'all' || 
                    ($notificationSetting === 'pending' && $status === 'pending')) {
                    $this->sendNewCommentNotification($commentId, $data);
                }
                
                if ($isAjax) {
                    $comment = $this->commentModel->getCommentById($commentId);
                    $commentData = $this->controller->getCommentWithUserData($comment);
                    
                    $response = [
                        'success' => true,
                        'comment' => $commentData,
                        'is_admin' => $this->isAdmin(),
                        'needs_moderation' => $status === 'pending',
                        'message' => $status === 'approved' 
                            ? LANG_ACTION_COMMENTS_ADD_SUCCESS_APPROVED
                            : LANG_ACTION_COMMENTS_ADD_SUCCESS_PENDING
                    ];
                    
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    return;
                } 
                else {
                    if ($status === 'pending') {
                        \Notification::success(LANG_ACTION_COMMENTS_ADD_PENDING_NOTICE);
                        
                        $post = $this->postModel->getById($_POST['post_id']);
                        if ($post) {
                            $redirectUrl = BASE_URL . '/post/' . $post['slug'] . '?pending_comment=1&scroll_to_comment=1';
                            $this->redirect($redirectUrl);
                            return;
                        }
                    } else {
                        \Notification::success(LANG_ACTION_COMMENTS_ADD_SUCCESS_APPROVED);
                    }
                }
            } else {
                throw new \Exception(LANG_ACTION_COMMENTS_ADD_SAVE_ERROR);
            }
            
        } catch (\Exception $e) {
            $message = LANG_ACTION_COMMENTS_ADD_ERROR . $e->getMessage();
            
            if ($isAjax) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
            } else {
                \Notification::error($message);
            }
        }
        
        if (!$isAjax) {
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/post/' . ($_POST['post_id'] ?? '');
            $this->redirect($redirectUrl);
        }
    }
    
    /**
    * Отправка сообщения об ошибке
    * @param string $message Текст сообщения об ошибке
    * @param bool $isAjax Является ли запрос AJAX-запросом
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
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
        }
    }

    /**
    * Отправка уведомления о новом комментарии
    * @param int $commentId ID созданного комментария
    * @param array $commentData Данные комментария
    * @return bool Результат отправки уведомления
    */
    private function sendNewCommentNotification($commentId, $commentData) {
        try {
            if (class_exists('NotificationModel')) {
                $notificationModel = new \NotificationModel($this->db);
                
                return $notificationModel->addNewCommentNotification($commentId, [
                    'user_id' => $commentData['user_id'] ?? null,
                    'author_name' => $commentData['author_name'] ?? LANG_ACTION_COMMENTS_ADD_ANONYMOUS,
                    'content' => $commentData['content'] ?? ''
                ]);
            }
        } catch (\Exception $e) {}
        
        return false;
    }
}