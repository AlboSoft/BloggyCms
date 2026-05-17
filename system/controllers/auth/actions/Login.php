<?php
namespace auth\actions;

/**
* Действие для входа пользователя в систему (фронтенд)
*/
class Login extends AuthAction {
    
    private $loginAttemptModel;

    /**
    * Конструктор действия входа пользователя
    * @param \Database $db Объект подключения к базе данных
    * @param array $params Дополнительные параметры маршрутизации
    */
    public function __construct($db, $params = []) {
        parent::__construct($db, $params);
        $this->loginAttemptModel = new \LoginAttemptModel($db);
    }

    /**
    * Основной метод выполнения процесса входа пользователя 
    */
    public function execute() {
        try {
            $this->addBreadcrumb(LANG_ACTION_AUTH_LOGIN_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_AUTH_LOGIN_BREADCRUMB_LOGIN);
            
            $this->pageTitle = LANG_ACTION_AUTH_LOGIN_PAGE_TITLE;

            if ($this->loginAttemptModel->isBlocked()) {
                $this->showBlockedPage();
                return;
            }

            $authSettings = $this->getFrontAuthSettings();
            $maxAttempts = $authSettings['count_auth'] ?? 5;
            $blockTime = $authSettings['count_time'] ?? 30;
            $disableRestore = $authSettings['disable_restore'] ?? false;
            $authRedirect = $authSettings['auth_redirect'] ?? 'show_profile';
            $attemptsInfo = $this->loginAttemptModel->getAttemptsInfo();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$this->validateCsrfToken()) {
                    throw new \Exception(LANG_ACTION_AUTH_LOGIN_INVALID_CSRF);
                }

                if (empty($_POST['email'])) {
                    throw new \Exception(LANG_ACTION_AUTH_LOGIN_EMAIL_REQUIRED);
                }

                if (empty($_POST['password'])) {
                    throw new \Exception(LANG_ACTION_AUTH_LOGIN_PASSWORD_REQUIRED);
                }

                $newAttempts = $this->loginAttemptModel->incrementAttempt(null, $maxAttempts, $blockTime);
                
                if ($newAttempts['is_blocked']) {
                    $this->showBlockedPage();
                    return;
                }

                $user = $this->userModel->authenticateByEmail($_POST['email'], $_POST['password']);
                
                if (!$user) {
                    throw new \Exception(sprintf(LANG_ACTION_AUTH_LOGIN_INVALID_CREDENTIALS, $newAttempts['attempts'], $maxAttempts));
                }

                if ($user['status'] !== 'active') {
                    throw new \Exception(LANG_ACTION_AUTH_LOGIN_ACCOUNT_BLOCKED);
                }

                $this->loginAttemptModel->resetAttempts();
                $this->updateUserLastLogin($user['id']);
                $this->updateUserActivity($user['id']);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['is_admin'] = ($user['is_admin'] == 1 || $user['is_admin'] === true);

                if (!empty($_POST['remember_me'])) {
                    $cookieLifetime = 86400 * 30;
                    
                    session_set_cookie_params([
                        'lifetime' => $cookieLifetime,
                        'path' => '/',
                        'domain' => '',
                        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                    
                    session_regenerate_id(true);
                    
                    setcookie(
                        session_name(),
                        session_id(),
                        [
                            'expires' => time() + $cookieLifetime,
                            'path' => '/',
                            'domain' => '',
                            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]
                    );
                }

                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onUserLogin($user['id']);
                } catch (\Exception $e) {}

                $redirectUrl = $this->getRedirectUrl($user, $authRedirect);
                unset($_SESSION['redirect_url']);

                \Notification::success(sprintf(LANG_ACTION_AUTH_LOGIN_WELCOME, ($user['display_name'] ?: $user['username'])));
                $this->redirect($redirectUrl);
                return;
            }

            $this->render('front/auth/login', [
                'csrf_token' => $this->generateCsrfToken(),
                'email' => $_POST['email'] ?? '',
                'currentAttempts' => $attemptsInfo['attempts'],
                'maxAttempts' => $maxAttempts,
                'disable_restore' => $disableRestore,
                'enable_register' => $authSettings['enable_register'] ?? true,
                'disable_register_reason' => $authSettings['disable_register_reason'] ?? LANG_ACTION_AUTH_LOGIN_REGISTER_DISABLED_DEFAULT
            ]);

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            
            $this->render('front/auth/login', [
                'email' => $_POST['email'] ?? '',
                'csrf_token' => $this->generateCsrfToken(),
                'currentAttempts' => $this->loginAttemptModel->getAttemptsInfo()['attempts'],
                'maxAttempts' => $maxAttempts,
                'disable_restore' => $disableRestore
            ]);
        }
    }
    
    /**
    * Обновление времени последнего входа пользователя в системе 
    * @param int $userId Идентификатор пользователя
    * @return void
    */
    private function updateUserLastLogin($userId) {
        try {
            $this->userModel->update($userId, [
                'last_login' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {}
    }
    
    /**
    * Обновление времени последней активности пользователя
    * @param int $userId Идентификатор пользователя
    * @return void
    */
    private function updateUserActivity($userId) {
        try {
            $activityManager = \UserActivityManager::getInstance($this->db);
            $activityManager->touch($userId);
        } catch (\Exception $e) {}
    }
    
    /**
    * Получение настроек авторизации для фронтенда из конфигурации
    * @return array Настройки авторизации для фронтенда
    */
    private function getFrontAuthSettings() {
        return [
            'count_auth' => \SettingsHelper::get('controller_auth', 'count_auth', 5),
            'count_time' => \SettingsHelper::get('controller_auth', 'count_time', 30),
            'disable_restore' => \SettingsHelper::get('controller_auth', 'disable_restore', false),
            'auth_redirect' => \SettingsHelper::get('controller_auth', 'auth_redirect', 'show_profile'),
            'enable_register' => \SettingsHelper::get('controller_auth', 'enable_register', true),
            'disable_register_reason' => \SettingsHelper::get('controller_auth', 'disable_register_reason', LANG_ACTION_AUTH_LOGIN_REGISTER_DISABLED_DEFAULT)
        ];
    }
    
    /**
    * Определение URL для редиректа после успешного входа пользователя
    * @param array $user Данные аутентифицированного пользователя
    * @param string $redirectOption Настройка редиректа из конфигурации
    * @return string URL для перенаправления
    */
    private function getRedirectUrl($user, $redirectOption) {
        if (isset($_SESSION['redirect_url'])) {
            return $_SESSION['redirect_url'];
        }
        
        switch ($redirectOption) {
            case 'show_profile':
                return BASE_URL . '/profile/' . $user['username'];
                
            case 'show_index':
            default:
                return BASE_URL;
        }
    }

    /**
    * Отображение страницы с информацией о временной блокировке входа
    */
    private function showBlockedPage() {
        $this->clearBreadcrumbs();
        $this->addBreadcrumb(LANG_ACTION_AUTH_LOGIN_BREADCRUMB_HOME, BASE_URL);
        $this->addBreadcrumb(LANG_ACTION_AUTH_LOGIN_BREADCRUMB_LOGIN, BASE_URL . '/login');
        $this->addBreadcrumb(LANG_ACTION_AUTH_LOGIN_BREADCRUMB_BLOCKED);
        
        $this->setPageTitle(LANG_ACTION_AUTH_LOGIN_BLOCKED_TITLE);
        
        $unlockTime = $this->loginAttemptModel->getUnlockTime();
        $remainingTime = $unlockTime - time();
        $remainingMinutes = ceil($remainingTime / 60);
        
        $this->render('front/auth/login_blocked', [
            'unlockTime' => $unlockTime,
            'remainingMinutes' => $remainingMinutes
        ]);
    }
}