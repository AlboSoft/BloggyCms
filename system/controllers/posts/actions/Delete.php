<?php

namespace posts\actions;

/**
* Действие удаления поста в административной панели
* @package posts\actions
*/
class Delete extends PostAction {
    
    /**
    * Метод выполнения удаления поста
    * Проверяет наличие ID, существование поста, удаляет связанное изображение, 
    * @return void
    * @throws \Exception Если ID не указан
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            throw new \Exception(LANG_ACTION_POSTS_DELETE_NO_ID);
        }

        try {
            $post = $this->postModel->getById($id);
            
            if (!$post) {
                \Notification::error(LANG_ACTION_POSTS_DELETE_POST_NOT_FOUND);
                $this->redirect(ADMIN_URL . '/posts');
                return;
            }
            
            if ($post['featured_image']) {
                $filePath = UPLOADS_PATH . '/images/' . $post['featured_image'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $this->postModel->delete($id);
            
            \Notification::success(LANG_ACTION_POSTS_DELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_POSTS_DELETE_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/posts');
    }
}