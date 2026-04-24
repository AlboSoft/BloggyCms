<?php

namespace users\actions;

/**
* Действие удаления пользователя в административной панели 
* @package users\actions
*/
class AdminDelete extends UserAction {
    
    /**
    * Метод выполнения удаления пользователя
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_USERS_ADMINDELETE_NO_ID);
            $this->redirect(ADMIN_URL . '/users');
            return;
        }
        
        try {
            if ($id == $this->getCurrentUserId()) {
                \Notification::error(LANG_ACTION_USERS_ADMINDELETE_CANNOT_DELETE_SELF);
                $this->redirect(ADMIN_URL . '/users');
                return;
            }

            $user = $this->userModel->getById($id);
            
            if (!$user) {
                \Notification::error(LANG_ACTION_USERS_ADMINDELETE_USER_NOT_FOUND);
                $this->redirect(ADMIN_URL . '/users');
                return;
            }
            
            if ($user['role'] === 'admin') {
                $adminsCount = $this->userModel->db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                if ($adminsCount['count'] <= 1) {
                    \Notification::error(LANG_ACTION_USERS_ADMINDELETE_LAST_ADMIN);
                    $this->redirect(ADMIN_URL . '/users');
                    return;
                }
            }
            
            $this->deleteUserAvatar($user);
            
            $this->userModel->delete($id);
            
            \Notification::success(LANG_ACTION_USERS_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_USERS_ADMINDELETE_ERROR, $e->getMessage()));
        }
        
        $this->redirect(ADMIN_URL . '/users');
    }
    
    /**
    * Удаляет аватар пользователя с сервера
    * @param array $user Данные пользователя
    * @return void
    */
    protected function deleteUserAvatar($user) {
        if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') {
            $filePath = UPLOADS_PATH . '/avatars/' . $user['avatar'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }
}