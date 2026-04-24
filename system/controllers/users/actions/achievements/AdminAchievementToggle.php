<?php

namespace users\actions\achievements;

/**
* Действие переключения статуса активности достижения (ачивки) в административной панели
* @package users\actions\achievements
*/
class AdminAchievementToggle extends AdminAchievementAction {
    
    /**
    * Метод выполнения переключения статуса ачивки
    * @return void
    */
    public function execute() {
        try {

            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTTOGGLE_NO_ID);
            }
            
            $achievement = $this->userModel->getAchievementById($id);
            if (!$achievement) {
                throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTTOGGLE_NOT_FOUND);
            }
            
            $newStatus = $achievement['is_active'] ? 0 : 1;
            
            $this->db->query(
                "UPDATE user_achievements SET is_active = ? WHERE id = ?",
                [$newStatus, $id]
            );
            
            $statusText = $newStatus ? LANG_ACTION_USERS_ADMINACHIEVEMENTTOGGLE_ACTIVATED : LANG_ACTION_USERS_ADMINACHIEVEMENTTOGGLE_DEACTIVATED;
            \Notification::success(sprintf(LANG_ACTION_USERS_ADMINACHIEVEMENTTOGGLE_SUCCESS, $statusText));
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_USERS_ADMINACHIEVEMENTTOGGLE_ERROR, $e->getMessage()));
        }
        
        $this->redirect(ADMIN_URL . '/user-achievements');
    }
}