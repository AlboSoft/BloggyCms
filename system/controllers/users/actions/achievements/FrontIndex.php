<?php

namespace users\actions\achievements;

/**
* Действие отображения публичного списка всех достижений системы
* @package users\actions\achievements
*/
class FrontIndex extends AdminAchievementAction {
    
    /**
    * Метод выполнения отображения публичного списка достижений
    * @return void
    */
    public function execute() {
        try {
            $this->addBreadcrumb(LANG_ACTION_USERS_FRONTINDEX_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_FRONTINDEX_BREADCRUMB_USERS, BASE_URL . '/users');
            $this->addBreadcrumb(LANG_ACTION_USERS_FRONTINDEX_BREADCRUMB_ACHIEVEMENTS);
            $this->setPageTitle(LANG_ACTION_USERS_FRONTINDEX_PAGE_TITLE);
            
            $achievements = $this->userModel->getAllAchievements(['active' => true]);
            
            foreach ($achievements as &$achievement) {
                $this->enrichAchievementData($achievement);
            }
            
            usort($achievements, function($a, $b) {
                return $a['percent'] <=> $b['percent'];
            });
            
            $totalAchievements = count($achievements);
            $totalUsers = $this->userModel->getTotalUsersCount();
            $totalUnlockedAchievements = $this->userModel->getTotalUnlockedAchievements();
            $userAchievements = $this->getUserAchievements();
            
            $this->render('front/users/achievements/index', [
                'achievements' => $achievements,
                'userAchievements' => $userAchievements,
                'totalAchievements' => $totalAchievements,
                'totalUsers' => $totalUsers,
                'totalUnlockedAchievements' => $totalUnlockedAchievements,
                'pageTitle' => LANG_ACTION_USERS_FRONTINDEX_RENDER_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_USERS_FRONTINDEX_ERROR);
            $this->redirect(BASE_URL . '/users');
        }
    }
    
    /**
    * Обогащает данные одной ачивки дополнительной информацией 
    * @param array &$achievement Ссылка на массив с данными ачивки
    * @return void
    */
    private function enrichAchievementData(&$achievement) {
        $achievement['conditions'] = $this->userModel->getAchievementConditions($achievement['id']);
        $achievement['unlocked_count'] = $this->userModel->getAchievementUnlockedCount($achievement['id']);
        $achievement['preview_users'] = $this->userModel->getAchievementUsersPreview($achievement['id'], 5);
        $achievement['formatted_conditions'] = $this->userModel->formatConditions($achievement['conditions']);
        
        $totalUsers = $this->userModel->getTotalUsersCount();
        if ($totalUsers > 0) {
            $achievement['percent'] = round(($achievement['unlocked_count'] / $totalUsers) * 100, 1);
        } else {
            $achievement['percent'] = 0;
        }
    }
    
    /**
    * Получает массив ачивок текущего пользователя (если авторизован)
    * @return array Массив с ключами ID ачивок и значениями is_unlocked
    */
    private function getUserAchievements() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        $userAchievementsData = $this->userModel->getUserAchievements($_SESSION['user_id']);
        return array_column($userAchievementsData, 'is_unlocked', 'id');
    }
}