<?php

namespace auth\actions;

/**
* Действие для авторизации администратора с расширенной безопасностью
*/
class AdminLogin extends AuthAction {

    private $loginAttemptModel;

    /**
    * Конструктор действия входа администратора
    * @param \Database $db Объект подключения к базе данных
    * @param array $params Дополнительные параметры маршрутизации
    */
    public function __construct($db, $params = []) {
        parent::__construct($db, $params);
        $this->loginAttemptModel = new \LoginAttemptModel($db);
    }

    /**
    * Основной метод выполнения действия входа администратора
    */
    public function execute() {
        if ($this->loginAttemptModel->isBlocked()) {
            $this->showBlockedPage();
            return;
        }

        $authSettings = $this->getAuthSettings();
        $showQA = $authSettings['show_qa'] ?? false;
        $qaParam = $authSettings['qa_param'] ?? 'opt2';
        $wordsArray = $authSettings['words_array'] ?? [];
        $maxAttempts = $authSettings['count_auth'] ?? 3;
        $blockTime = $authSettings['count_time'] ?? 20;

        if ($showQA === '1' || $showQA === 1) {
            $showQA = true;
        }

        $attemptsInfo = $this->loginAttemptModel->getAttemptsInfo();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\CsrfToken::verify($token, 'admin_login')) {
                $_SESSION['admin_login_attempt_failed'] = true;
                $this->showBlockedPage();
                return;
            }

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $newAttempts = $this->loginAttemptModel->incrementAttempt(null, $maxAttempts, $blockTime);
            
            if ($newAttempts['is_blocked']) {
                $this->showBlockedPage();
                return;
            }
            
            $shouldShowQA = $this->shouldShowQuestions($showQA, $qaParam, $username);
            
            if ($shouldShowQA) {
                if (empty($_POST['qa_answer'])) {
                    $randomQA = $this->getRandomQuestion($wordsArray);
                    $this->render('admin/login', [
                        'username' => $username,
                        'password' => $password,
                        'showQuestion' => true,
                        'question' => $randomQA['question'],
                        'expectedAnswer' => $randomQA['answer'],
                        'authSettings' => $authSettings,
                        'currentAttempts' => $newAttempts['attempts'],
                        'maxAttempts' => $maxAttempts,
                        'error' => LANG_ACTION_AUTH_ADMINLOGIN_ANSWER_REQUIRED,
                        'csrf_token' => \CsrfToken::generate('admin_login')
                    ]);
                    return;
                } else {
                    $userAnswer = $_POST['qa_answer'] ?? '';
                    $expectedAnswer = $_POST['expected_answer'] ?? '';
                    
                    if (strtolower(trim($userAnswer)) !== strtolower(trim($expectedAnswer))) {
                        $randomQA = $this->getRandomQuestion($wordsArray);
                        $this->render('admin/login', [
                            'username' => $username,
                            'password' => $password,
                            'showQuestion' => true,
                            'question' => $randomQA['question'],
                            'expectedAnswer' => $randomQA['answer'],
                            'authSettings' => $authSettings,
                            'currentAttempts' => $newAttempts['attempts'],
                            'maxAttempts' => $maxAttempts,
                            'error' => LANG_ACTION_AUTH_ADMINLOGIN_INVALID_ANSWER,
                            'csrf_token' => \CsrfToken::generate('admin_login')
                        ]);
                        return;
                    }
                }
            }
            
            $this->processAdminLogin($username, $password, $authSettings, $newAttempts['attempts']);
            return;
        }

        $shouldShowQA = $this->shouldShowQuestions($showQA, $qaParam, '');
        
        if ($shouldShowQA) {
            $randomQA = $this->getRandomQuestion($wordsArray);
            $this->render('admin/login', [
                'showQuestion' => true,
                'question' => $randomQA['question'],
                'expectedAnswer' => $randomQA['answer'],
                'authSettings' => $authSettings,
                'currentAttempts' => $attemptsInfo['attempts'],
                'maxAttempts' => $maxAttempts,
                'csrf_token' => \CsrfToken::generate('admin_login')
            ]);
        } else {
            $this->render('admin/login', [
                'showQuestion' => false,
                'authSettings' => $authSettings,
                'currentAttempts' => $attemptsInfo['attempts'],
                'maxAttempts' => $maxAttempts,
                'csrf_token' => \CsrfToken::generate('admin_login')
            ]);
        }
    }

    /**
    * Получение настроек безопасности авторизации из базы данных
    */
    private function getAuthSettings() {
        return [
            'show_qa' => \SettingsHelper::get('controller_auth', 'show_qa', false),
            'qa_param' => \SettingsHelper::get('controller_auth', 'qa_param', 'opt2'),
            'words_array' => \SettingsHelper::get('controller_auth', 'words_array', []),
            'count_auth' => \SettingsHelper::get('controller_auth', 'count_auth', 3),
            'count_time' => \SettingsHelper::get('controller_auth', 'count_time', 20)
        ];
    }

    /**
    * Проверка наличия активной блокировки страницы входа
    */
    private function isLoginBlocked() {
        $blockedUntil = $_SESSION['login_blocked_until'] ?? 0;
        return time() < $blockedUntil;
    }

    /**
    * Получение времени разблокировки страницы входа
    */
    private function getUnlockTime() {
        $blockedUntil = $_SESSION['login_blocked_until'] ?? 0;
        return $blockedUntil;
    }

    /**
    * Установка блокировки страницы входа
    */
    private function blockLoginPage($blockTimeMinutes) {
        $blockTimeSeconds = $blockTimeMinutes * 60;
        $_SESSION['login_blocked_until'] = time() + $blockTimeSeconds;
        $_SESSION['login_attempts'] = 0;
    }

    /**
    * Увеличение счетчика неудачных попыток входа
    */
    private function incrementLoginAttempts() {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    }

    /**
    * Получение текущего количества неудачных попыток
    */
    private function getLoginAttemptsCount() {
        return $_SESSION['login_attempts'] ?? 0;
    }

    /**
    * Сброс счетчика неудачных попыток входа 
    */
    private function resetLoginAttempts() {
        unset($_SESSION['login_attempts']);
    }

    /**
    * Отображение страницы с информацией о блокировке входа 
    * @return void
    */
    private function showBlockedPage() {
        $unlockTime = $this->loginAttemptModel->getUnlockTime();
        $remainingTime = $unlockTime - time();
        $remainingMinutes = ceil($remainingTime / 60);
        
        $this->render('admin/auth/login_blocked', [
            'unlockTime' => $unlockTime,
            'remainingMinutes' => $remainingMinutes
        ]);
    }

    /**
    * Безопасное получение случайного вопроса из массива
    */
    private function getRandomQuestion($wordsArray) {
        if (empty($wordsArray) || !is_array($wordsArray)) {
            return ['question' => LANG_ACTION_AUTH_ADMINLOGIN_NO_QUESTIONS, 'answer' => 'none'];
        }
        
        $validQuestions = array_filter($wordsArray, function($qa) {
            return !empty($qa['question']) && !empty($qa['answer']);
        });
        
        if (empty($validQuestions)) {
            return ['question' => LANG_ACTION_AUTH_ADMINLOGIN_NO_QUESTIONS, 'answer' => 'none'];
        }
        
        $randomIndex = array_rand($validQuestions);
        return $validQuestions[$randomIndex];
    }

    /**
    * Определение необходимости показа контрольных вопросов
*/
    private function shouldShowQuestions($showQA, $qaParam, $username) {
        if (!$showQA) return false;
        
        switch ($qaParam) {
            case 'opt1':
                return isset($_SESSION['admin_login_attempt_failed']) && $_SESSION['admin_login_attempt_failed'];
                
            case 'opt2':
                $lastIP = $this->getLastAdminIP($username);
                $currentIP = $_SERVER['REMOTE_ADDR'];
                return $lastIP && $lastIP !== $currentIP;
                
            case 'opt3':
                return true;
                
            default:
                return false;
        }
    }

    /**
    * Получение последнего известного IP адреса администратора
    * @param string $username Имя пользователя для поиска
    * @return string|null IP адрес или null если не найден
    */
    private function getLastAdminIP($username) {
        if (empty($username)) return null;
        
        $user = $this->userModel->getByUsername($username);
        return $user['last_admin_ip'] ?? null;
    }

    /**
    * Обновление последнего IP адреса администратора 
    * @param int $userId Идентификатор пользователя
    * @param string $ip IP адрес для сохранения
    * @return void
    */
    private function updateLastAdminIP($userId, $ip) {
        $this->db->query(
            "UPDATE users SET last_admin_ip = ? WHERE id = ?",
            [$ip, $userId]
        );
    }

    /**
    * Обработка основных учетных данных администратора 
    */
    private function processAdminLogin($username, $password, $authSettings, $currentAttempts) {
        $user = $this->userModel->authenticate($username, $password);
        if ($user && ($user['is_admin'] == 1 || $user['is_admin'] === true)) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = true;

            $this->updateLastAdminIP($user['id'], $_SERVER['REMOTE_ADDR']);

            $activityManager = UserActivityManager::getInstance($this->db);
            $activityManager->touch($user['id']);

            unset($_SESSION['admin_login_attempt_failed']);
            $this->loginAttemptModel->resetAttempts();
            $this->redirect(ADMIN_URL . '/');
        } else {
            $_SESSION['admin_login_attempt_failed'] = true;

            $showQA = $authSettings['show_qa'] ?? false;
            $qaParam = $authSettings['qa_param'] ?? 'opt2';
            $wordsArray = $authSettings['words_array'] ?? [];

            $shouldShowQA = $this->shouldShowQuestions($showQA, $qaParam, $username);
            $randomQA = $this->getRandomQuestion($wordsArray);

            $this->render('admin/login', [
                'error' => LANG_ACTION_AUTH_ADMINLOGIN_INVALID_CREDENTIALS,
                'showQuestion' => $shouldShowQA,
                'question' => $shouldShowQA ? $randomQA['question'] : '',
                'expectedAnswer' => $shouldShowQA ? $randomQA['answer'] : '',
                'authSettings' => $authSettings,
                'currentAttempts' => $currentAttempts,
                'maxAttempts' => $authSettings['count_auth'] ?? 3,
                'csrf_token' => \CsrfToken::generate('admin_login')
            ]);
        }
    }
}