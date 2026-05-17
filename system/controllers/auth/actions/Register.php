<?php

namespace auth\actions;

/**
* Класс действия "Регистрация"
*/
class Register extends AuthAction {
    
    /**
    * Выполнение действия регистрации пользователя
    * Управляет полным процессом регистрации от отображения формы до создания учетной записи
    */
    public function execute() {
        try {
            $this->addBreadcrumb(LANG_ACTION_AUTH_REGISTER_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_AUTH_REGISTER_BREADCRUMB_REGISTER);
            $this->pageTitle = LANG_ACTION_AUTH_REGISTER_PAGE_TITLE;
            
            $authSettings = $this->getFrontAuthSettings();
            
            $enableRegisterSetting = $authSettings['enable_register'] ?? '0';
            
            if ($enableRegisterSetting === '1' || $enableRegisterSetting === 1 || $enableRegisterSetting === true) {
                $disableRegisterReason = $authSettings['disable_register_reason'] ?? LANG_ACTION_AUTH_REGISTER_DISABLED_DEFAULT;
                
                $this->render('front/auth/register', [
                    'csrf_token' => $this->generateCsrfToken(),
                    'error' => $disableRegisterReason,
                    'registration_disabled' => true
                ]);
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!$this->validateCsrfToken()) {
                    throw new \Exception(LANG_ACTION_AUTH_REGISTER_INVALID_CSRF);
                }

                if (empty($_POST['username'])) {
                    throw new \Exception(LANG_ACTION_AUTH_REGISTER_USERNAME_REQUIRED);
                }

                if (empty($_POST['email'])) {
                    throw new \Exception(LANG_ACTION_AUTH_REGISTER_EMAIL_REQUIRED);
                }

                if (empty($_POST['password'])) {
                    throw new \Exception(LANG_ACTION_AUTH_REGISTER_PASSWORD_REQUIRED);
                }

                if ($_POST['password'] !== $_POST['password_confirm']) {
                    throw new \Exception(LANG_ACTION_AUTH_REGISTER_PASSWORD_MISMATCH);
                }

                if ($this->userModel->getByUsername($_POST['username'])) {
                    throw new \Exception(LANG_ACTION_AUTH_REGISTER_USERNAME_EXISTS);
                }

                if ($this->userModel->getByEmail($_POST['email'])) {
                    throw new \Exception(LANG_ACTION_AUTH_REGISTER_EMAIL_EXISTS);
                }

                $userData = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'display_name' => $_POST['display_name'] ?? $_POST['username'],
                    'status' => 'active'
                ];

                $userId = $this->userModel->create($userData);
                $defaultGroup = $this->userModel->getDefaultGroup();
                $groupName = $defaultGroup ? $defaultGroup['name'] : '';

                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onUserRegistered($userId);
                } catch (\Exception $e) {}

                $user = $this->userModel->getById($userId);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['is_admin'] = false;

                $message = LANG_ACTION_AUTH_REGISTER_SUCCESS;
                if ($groupName) {
                    $message .= sprintf(LANG_ACTION_AUTH_REGISTER_GROUP_ADDED, $groupName);
                }
                
                \Notification::success($message);
                $this->redirect(BASE_URL);
                return;
            }

            $this->render('front/auth/register', [
                'csrf_token' => $this->generateCsrfToken(),
                'registration_disabled' => false
            ]);

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            
            $this->render('front/auth/register', [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'display_name' => $_POST['display_name'] ?? '',
                'csrf_token' => $this->generateCsrfToken(),
                'registration_disabled' => false
            ]);
        }
    }

    /**
    * Получение настроек авторизации для фронтенда
    * Извлекает параметры регистрации из системы настроек
    */
    private function getFrontAuthSettings() {
        return [
            'enable_register' => \SettingsHelper::get('controller_auth', 'enable_register', '0'),
            'disable_register_reason' => \SettingsHelper::get('controller_auth', 'disable_register_reason', LANG_ACTION_AUTH_REGISTER_DISABLED_DEFAULT)
        ];
    }
}