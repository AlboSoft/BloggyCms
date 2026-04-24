<?php

namespace users\actions\achievements;

/**
* Действие назначения достижения (ачивки) пользователю в административной панели
* @package users\actions\achievements
*/
class AdminAchievementAssign extends AdminAchievementAction {
    
    /**
    * Метод выполнения назначения ачивки пользователю
    * @return void
    */
    public function execute() {
        try {

            $userId = $this->params['user_id'] ?? null;
            if (!$userId) {
                throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTASSIGN_NO_USER_ID);
            }
            
            $user = $this->userModel->getById($userId);
            if (!$user) {
                throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTASSIGN_USER_NOT_FOUND);
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($userId);
                return;
            }
            
            $this->renderAssignForm($user);
            
        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/users');
        }
    }
    
    /**
    * Обрабатывает POST-запрос на назначение ачивки
    * @param int $userId ID пользователя
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest($userId) {
        $achievementId = $_POST['achievement_id'] ?? null;
        if (!$achievementId) {
            throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTASSIGN_NO_ACHIEVEMENT);
        }
        
        $achievement = $this->userModel->getAchievementById($achievementId);
        if (!$achievement) {
            throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTASSIGN_ACHIEVEMENT_NOT_FOUND);
        }
        
        $this->userModel->assignAchievementToUser($userId, $achievementId);
        
        \Notification::success(LANG_ACTION_USERS_ADMINACHIEVEMENTASSIGN_SUCCESS);
        $this->redirect(ADMIN_URL . '/users/edit/' . $userId);
    }
    
    /**
    * Отображает форму назначения ачивки
    * @param array $user Данные пользователя
    * @return void
    */
    private function renderAssignForm($user) {
        $achievements = $this->userModel->getAllAchievements(['active' => true]);
        
        $this->render('admin/users/assign-achievement', [
            'user' => $user,
            'achievements' => $achievements,
            'pageTitle' => LANG_ACTION_USERS_ADMINACHIEVEMENTASSIGN_PAGE_TITLE
        ]);
    }
}