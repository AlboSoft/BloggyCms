<?php

namespace auth\actions;

/**
* Действие для безопасного выхода пользователя из системы
*/
class Logout extends AuthAction {
    
    /**
    * Основной метод выполнения процесса выхода из системы
    */
    public function execute() {
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if ($userId && class_exists('\\SessionTracker')) {
            try {
                \SessionTracker::terminateCurrent(\Database::getInstance(), $userId);
            } catch (\Throwable $e) {
                // выход из аккаунта не должен зависеть от очистки списка сессий
            }
        }

        session_unset();
        session_destroy();
        
        \Notification::success(LANG_ACTION_AUTH_LOGOUT_SUCCESS);
        $this->redirect(BASE_URL);
    }
}