<?php

namespace users\actions;

/**
* Действие переключения статуса пользователя в административной панели
* @package users\actions
*/
class AdminToggleStatus extends UserAction {
    
    /**
    * Метод выполнения переключения статуса пользователя
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_USERS_ADMINTOGGLESTATUS_NO_ID);
            $this->redirect(ADMIN_URL . '/users');
            return;
        }
        
        try {
            if ($id == $this->getCurrentUserId()) {
                \Notification::error(LANG_ACTION_USERS_ADMINTOGGLESTATUS_CANNOT_TOGGLE_SELF);
                $this->redirect(ADMIN_URL . '/users');
                return;
            }

            $user = $this->userModel->getById($id);
            if (!$user) {
                throw new \Exception(LANG_ACTION_USERS_ADMINTOGGLESTATUS_USER_NOT_FOUND);
            }
            
            $newStatus = $user['status'] === 'active' ? 'banned' : 'active';
            
            $this->userModel->update($id, ['status' => $newStatus]);
            
            $statusText = $newStatus === 'active' ? LANG_ACTION_USERS_ADMINTOGGLESTATUS_ACTIVATED : LANG_ACTION_USERS_ADMINTOGGLESTATUS_BANNED;
            \Notification::success(sprintf(LANG_ACTION_USERS_ADMINTOGGLESTATUS_SUCCESS, $statusText));
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_USERS_ADMINTOGGLESTATUS_ERROR, $e->getMessage()));
        }
        
        $this->redirect(ADMIN_URL . '/users');
    }
}