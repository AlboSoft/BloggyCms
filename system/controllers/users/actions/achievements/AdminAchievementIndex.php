<?php

namespace users\actions\achievements;

/**
* Действие отображения списка всех достижений (ачивок) в административной панели
* @package users\actions\achievements
*/
class AdminAchievementIndex extends AdminAchievementAction {
    
    /**
    * Метод выполнения отображения списка ачивок
    * @return void
    */
    public function execute() {
        try {

            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINACHIEVEMENTINDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINACHIEVEMENTINDEX_BREADCRUMB_ACHIEVEMENTS, ADMIN_URL . '/user-achievements');
            
            $type = $_GET['type'] ?? null;
            $search = $_GET['search'] ?? null;
            
            $filters = [];
            if ($type) {
                $filters['type'] = $type;
            }
            if ($search) {
                $filters['search'] = $search;
            }
            
            $achievements = $this->userModel->getAllAchievements($filters);
            
            $this->render('admin/user-achievements/index', [
                'achievements' => $achievements,
                'pageTitle' => LANG_ACTION_USERS_ADMINACHIEVEMENTINDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_USERS_ADMINACHIEVEMENTINDEX_ERROR, $e->getMessage()));
            $this->redirect(ADMIN_URL);
        }
    }

}