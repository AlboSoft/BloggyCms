<?php

namespace users\actions\achievements;

/**
* Действие удаления достижения (ачивки) в административной панели
* @package users\actions\achievements
*/
class AdminAchievementDelete extends AdminAchievementAction {
    
    /**
    * Метод выполнения удаления ачивки
    * @return void
    */
    public function execute() {
        try {

            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTDELETE_NO_ID);
            }
            
            $achievement = $this->userModel->getAchievementById($id);
            if (!$achievement) {
                throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTDELETE_NOT_FOUND);
            }
            
            if (!empty($achievement['image'])) {
                $uploadDir = UPLOADS_PATH . '/achievements/';
                $imagePath = $uploadDir . $achievement['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $this->userModel->deleteAchievement($id);
            
            \Notification::success(LANG_ACTION_USERS_ADMINACHIEVEMENTDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_USERS_ADMINACHIEVEMENTDELETE_ERROR, $e->getMessage()));
        }
        
        $this->redirect(ADMIN_URL . '/user-achievements');
    }
}