<?php

namespace comments\actions;

/**
* Действие одобрения комментария в админ-панели
* @package comments\actions
*/
class AdminApprove extends CommentAction {
    
    /**
    * Метод выполнения одобрения комментария 
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        $isAjax = $this->isAjaxRequest();
        
        if (!$id) {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => LANG_ACTION_COMMENTS_ADMINAPPROVE_ID_NOT_SPECIFIED
                ]);
                return;
            } else {
                \Notification::error(LANG_ACTION_COMMENTS_ADMINAPPROVE_ID_NOT_SPECIFIED);
                $this->redirect(ADMIN_URL . '/comments');
                return;
            }
        }
        
        try {
            $comment = $this->commentModel->getCommentById($id);
            if (!$comment) {
                if ($isAjax) {
                    http_response_code(404);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => LANG_ACTION_COMMENTS_ADMINAPPROVE_NOT_FOUND
                    ]);
                    return;
                } else {
                    \Notification::error(LANG_ACTION_COMMENTS_ADMINAPPROVE_NOT_FOUND);
                    $this->redirect(ADMIN_URL . '/comments');
                    return;
                }
            }
            
            if ($comment['status'] === 'approved') {
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => LANG_ACTION_COMMENTS_ADMINAPPROVE_ALREADY_APPROVED
                    ]);
                    return;
                } else {
                    \Notification::warning(LANG_ACTION_COMMENTS_ADMINAPPROVE_ALREADY_APPROVED);
                    $this->redirect(ADMIN_URL . '/comments');
                    return;
                }
            }
            
            $this->commentModel->approveComment($id);
            
            if ($isAjax) {
                $updatedComment = $this->commentModel->getCommentById($id);
                
                $isAdminPage = strpos($_SERVER['HTTP_REFERER'] ?? '', '/admin/') !== false;
                
                if ($isAdminPage) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => LANG_ACTION_COMMENTS_ADMINAPPROVE_SUCCESS,
                        'comment_id' => $id,
                        'new_status' => 'approved'
                    ]);
                } else {
                    if ($this->controller) {
                        $commentData = $this->controller->getCommentWithUserData($updatedComment);
                        
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'comment' => $commentData,
                            'message' => LANG_ACTION_COMMENTS_ADMINAPPROVE_APPROVED,
                            'comment_id' => $id
                        ]);
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => LANG_ACTION_COMMENTS_ADMINAPPROVE_SUCCESS,
                            'comment_id' => $id
                        ]);
                    }
                }
                return;
            } 
            else {
                \Notification::success(LANG_ACTION_COMMENTS_ADMINAPPROVE_SUCCESS);
            }
            
        } catch (\Exception $e) {
            $errorMessage = LANG_ACTION_COMMENTS_ADMINAPPROVE_ERROR . $e->getMessage();
            
            if ($isAjax) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
                return;
            } else {
                \Notification::error($errorMessage);
            }
        }
        
        if (!$isAjax) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? ADMIN_URL . '/comments');
        }
    }
}