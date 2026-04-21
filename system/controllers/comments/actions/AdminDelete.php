<?php

namespace comments\actions;

/**
* Действие удаления комментария в админ-панели
* @package comments\actions
*/
class AdminDelete extends CommentAction {
    
    /**
    * Метод выполнения удаления комментария
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_COMMENTS_ADMINDELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/comments');
            return;
        }
        
        try {
            $this->commentModel->deleteComment($id);
            \Notification::success(LANG_ACTION_COMMENTS_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_COMMENTS_ADMINDELETE_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/comments');
    }
}