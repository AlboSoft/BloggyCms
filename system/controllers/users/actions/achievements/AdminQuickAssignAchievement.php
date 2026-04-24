<?php

namespace users\actions\achievements;

/**
* Действие быстрого назначения ручного достижения пользователю в административной панели
* @package users\actions\achievements
*/
class AdminQuickAssignAchievement extends AdminAchievementAction {
    
    /**
    * Метод выполнения быстрого назначения ачивки
    * @return void
    */
    public function execute() {
        try {

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $userId = $this->params['user_id'] ?? null;
                if ($userId) {
                    $user = $this->userModel->getById($userId);
                    if ($user) {
                        $this->addBreadcrumb(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_BREADCRUMB_DASHBOARD, ADMIN_URL);
                        $this->addBreadcrumb(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_BREADCRUMB_USERS, ADMIN_URL . '/users');
                        $this->addBreadcrumb(sprintf(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_BREADCRUMB_EDIT, ($user['display_name'] ?? $user['username'])), ADMIN_URL . '/users/edit/' . $userId);
                        $this->addBreadcrumb(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_BREADCRUMB_QUICK_ASSIGN);
                    }
                }
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest();
                return;
            }

            $this->handleGetRequest();

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/users');
        }
    }
    
    /**
    * Обрабатывает POST-запрос на назначение ачивки 
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest() {
        $userId = $_POST['user_id'] ?? null;
        $achievementId = $_POST['achievement_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        $sendNotification = isset($_POST['send_notification']) ? true : false;
        
        if (!$userId || !$achievementId) {
            throw new \Exception(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_MISSING_IDS);
        }
        
        $user = $this->userModel->getById($userId);
        if (!$user) {
            throw new \Exception(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_USER_NOT_FOUND);
        }
        
        $achievement = $this->userModel->getAchievementById($achievementId);
        if (!$achievement) {
            throw new \Exception(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_ACHIEVEMENT_NOT_FOUND);
        }
        
        if ($achievement['type'] !== 'manual') {
            throw new \Exception(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_MANUAL_ONLY);
        }
        
        $this->saveAchievementAssignmentHistory($userId, $achievementId, $reason);
        
        $this->userModel->assignAchievementToUser($userId, $achievementId);
        
        if ($sendNotification) {
            $this->sendAchievementNotification($user, $achievement, $reason);
        }
        
        \Notification::success(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_SUCCESS);
        $this->redirect(ADMIN_URL . '/users/edit/' . $userId);
    }
    
    /**
    * Обрабатывает GET-запрос для отображения формы назначения 
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handleGetRequest() {
        $userId = $this->params['user_id'] ?? null;
        if (!$userId) {
            throw new \Exception(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_NO_USER_ID);
        }
        
        $user = $this->userModel->getById($userId);
        if (!$user) {
            throw new \Exception(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_USER_NOT_FOUND);
        }
        
        $allAchievements = $this->userModel->getAllAchievements(['active' => true]);
        
        $userAchievements = $this->userModel->getUserUnlockedAchievements($userId);
        $userAchievementIds = array_column($userAchievements, 'id');
        
        $availableAchievements = array_filter($allAchievements, function($achievement) use ($userAchievementIds) {
            return $achievement['type'] == 'manual' && !in_array($achievement['id'], $userAchievementIds);
        });
        
        $this->render('admin/users/quick-assign-achievement', [
            'user' => $user,
            'availableAchievements' => $availableAchievements,
            'pageTitle' => LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_PAGE_TITLE
        ]);
    }
    
    /**
    * Сохраняет историю назначения ачивки 
    * @param int $userId ID пользователя
    * @param int $achievementId ID ачивки
    * @param string $reason Причина назначения
    * @return bool Результат операции
    */
    private function saveAchievementAssignmentHistory($userId, $achievementId, $reason) {
        $this->db->query(
            "INSERT INTO achievement_assignment_history (user_id, achievement_id, admin_id, reason) 
             VALUES (?, ?, ?, ?)",
            [$userId, $achievementId, $_SESSION['user_id'] ?? null, $reason]
        );
        
        return true;
    }
    
    /**
    * Отправляет уведомление пользователю о назначении ачивки
    * @param array $user Данные пользователя
    * @param array $achievement Данные ачивки
    * @param string $reason Причина назначения
    * @return bool Результат операции
    */
    private function sendAchievementNotification($user, $achievement, $reason) {
        $message = sprintf(LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_NOTIFICATION_MESSAGE, $achievement['name']);
        
        if (!empty($reason)) {
            $message .= " " . LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_NOTIFICATION_REASON . ": " . $reason;
        }

        $this->db->query(
            "INSERT INTO notifications (user_id, type, title, message, is_read) 
             VALUES (?, 'achievement', ?, ?, 0)",
            [$user['id'], LANG_ACTION_USERS_ADMINQUICKASSIGNACHIEVEMENT_NOTIFICATION_TITLE, $message]
        );
        
        return true;
    }
}