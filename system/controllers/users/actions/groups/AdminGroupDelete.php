<?php

namespace users\actions\groups;

/**
* Действие удаления группы пользователей в административной панели
* @package users\actions\groups
*/
class AdminGroupDelete extends AdminGroupAction {
    
    /**
    * Метод выполнения удаления группы
    * @return void
    */
    public function execute() {
        try {

            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception(LANG_ACTION_USERS_ADMINGROUPDELETE_NO_ID);
            }

            $this->userModel->deleteGroup($id);
            
            \Notification::success(LANG_ACTION_USERS_ADMINGROUPDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_USERS_ADMINGROUPDELETE_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/user-groups');
    }
}