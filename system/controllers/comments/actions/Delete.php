<?php

namespace comments\actions;

/**
* Действие удаления комментария пользователем
* @package comments\actions
*/
class Delete extends CommentAction {
    
    /**
    * Метод выполнения удаления комментария
    * @return void
    */
    public function execute() {
        
        $id = $this->params['id'] ?? null;
        $isAjax = $this->isAjaxRequest();
        
        if (!$id) {
            $this->sendError(LANG_ACTION_COMMENTS_DELETE_ID_NOT_SPECIFIED, $isAjax);
            return;
        }
        
        try {
            $currentUserId = $this->getCurrentUserId();
            if (!$currentUserId) {
                $this->sendError(LANG_ACTION_COMMENTS_DELETE_NOT_AUTH, $isAjax);
                return;
            }

            $comment = $this->commentModel->getCommentById($id);
            
            if (!$comment) {
                $this->sendError(LANG_ACTION_COMMENTS_DELETE_NOT_FOUND, $isAjax);
                return;
            }

            $userId = $comment['user_id'] ?? null;
            
            if (!\AuthHelper::canDeleteComment($userId)) {
                $this->sendError(LANG_ACTION_COMMENTS_DELETE_NO_PERMISSION, $isAjax);
                return;
            }

            $deleteRecursive = true;
            if ($deleteRecursive && $this->hasChildComments($id)) {
                $result = $this->commentModel->deleteCommentRecursive($id);
            } else {
                $result = $this->commentModel->deleteComment($id);
            }
            
            if ($result) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => LANG_ACTION_COMMENTS_DELETE_SUCCESS,
                        'comment_id' => $id,
                        'has_replies' => $this->hasChildComments($id)
                    ]);
                    return;
                } else {
                    \Notification::success(LANG_ACTION_COMMENTS_DELETE_SUCCESS);
                }
            } else {
                throw new \Exception(LANG_ACTION_COMMENTS_DELETE_FAILED);
            }
            
        } catch (\Exception $e) {
            $this->sendError(LANG_ACTION_COMMENTS_DELETE_ERROR . $e->getMessage(), $isAjax);
        }
        
        if (!$isAjax) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
        }
    }
    
    /**
    * Проверка наличия дочерних комментариев
    * @param int $parentId ID родительского комментария
    * @return bool true если у комментария есть дочерние комментарии
    */
    private function hasChildComments($parentId) {
        $sql = "SELECT COUNT(*) as count FROM comments WHERE parent_id = ?";
        $result = $this->db->fetch($sql, [$parentId]);
        return ($result['count'] ?? 0) > 0;
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
}