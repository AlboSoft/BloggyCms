<?php

namespace comments\actions;

/**
* Действие редактирования комментария в админ-панели
* @package comments\actions
*/
class AdminEdit extends CommentAction {
    
    /**
    * Метод выполнения редактирования комментария
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_COMMENTS_ADMINEDIT_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/comments');
            return;
        }
        
        try {
            $comment = $this->commentModel->getCommentById($id);
            
            if (!$comment) {
                \Notification::error(LANG_ACTION_COMMENTS_ADMINEDIT_NOT_FOUND);
                $this->redirect(ADMIN_URL . '/comments');
                return;
            }
            
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_ADMINEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_ADMINEDIT_BREADCRUMB_COMMENTS, ADMIN_URL . '/comments');
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_ADMINEDIT_BREADCRUMB_EDIT . $id);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'content' => $_POST['content'] ?? '',
                    'status' => $_POST['status'] ?? 'pending'
                ];
                
                $this->commentModel->updateComment($id, $data);

                \Notification::success(LANG_ACTION_COMMENTS_ADMINEDIT_SUCCESS);
                
                $this->redirect(ADMIN_URL . '/comments');
                return;
            }

            $this->render('admin/comments/edit', [
                'comment' => $comment,
                'pageTitle' => LANG_ACTION_COMMENTS_ADMINEDIT_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_COMMENTS_ADMINEDIT_ERROR);
            $this->redirect(ADMIN_URL . '/comments');
        }
    }

}