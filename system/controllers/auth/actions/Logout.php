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
        session_unset();
        session_destroy();
        
        \Notification::success(LANG_ACTION_AUTH_LOGOUT_SUCCESS);
        $this->redirect(BASE_URL);
    }
}