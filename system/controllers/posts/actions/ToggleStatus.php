<?php

namespace posts\actions;

/**
* Действие переключения статуса поста в административной панели 
* @package posts\actions
*/
class ToggleStatus extends PostAction {
    
    /**
    * Метод выполнения переключения статуса поста
    * @return void
    * @throws \Exception Если ID не указафн
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            throw new \Exception(LANG_ACTION_POSTS_TOGGLESTATUS_NO_ID);
        }

        try {
            $post = $this->postModel->getById($id);
            if (!$post) {
                throw new \Exception(LANG_ACTION_POSTS_TOGGLESTATUS_POST_NOT_FOUND);
            }
            
            $newStatus = $post['status'] === 'published' ? 'draft' : 'published';
            
            $this->postModel->update($id, ['status' => $newStatus]);
            
            $statusText = $newStatus === 'published' ? LANG_ACTION_POSTS_TOGGLESTATUS_PUBLISHED : LANG_ACTION_POSTS_TOGGLESTATUS_DRAFT;
            \Notification::success(sprintf(LANG_ACTION_POSTS_TOGGLESTATUS_SUCCESS, $statusText));
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_POSTS_TOGGLESTATUS_ERROR, $e->getMessage()));
        }
        
        $this->redirect(ADMIN_URL . '/posts');
    }
}