<?php

namespace profile\actions;

/**
* Действие удаления аккаунта пользователя
*/
class Delete extends ProfileAction {

    /**
    * Метод выполнения удаления аккаунта
    * @return void
    */
    public function execute() {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_UNAUTHORIZED]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $password = isset($input['password']) ? (string)$input['password'] : '';
        $csrfToken = $input['csrf_token'] ?? '';

        if (!$this->validateApiCsrfToken($csrfToken)) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_INVALID_CSRF]);
            return;
        }

        if ($password === '') {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_INVALID_PASSWORD]);
            return;
        }

        $userId = (int)$_SESSION['user_id'];

        try {
            $user = $this->userModel->getById($userId);

            if (!$user || !password_verify($password, $user['password'])) {
                echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_INVALID_PASSWORD]);
                return;
            }

            if (!empty($user['is_admin'])) {
                $adminsCount = (int)$this->db->fetchValue(
                    "SELECT COUNT(*) FROM users WHERE is_admin = 1"
                );

                if ($adminsCount <= 1) {
                    echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_LAST_ADMIN]);
                    return;
                }
            }

            $this->deleteUserAvatar($user);

            if (class_exists('\\SessionTracker')) {
                try {
                    \SessionTracker::terminateAllExceptCurrent($this->db, $userId);
                } catch (\Throwable $e) {
                    // не мешаем удалению аккаунта
                }
            }

            if (!$this->userModel->delete($userId)) {
                echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_ERROR]);
                return;
            }

            $this->destroySession();

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_ERROR]);
        }
    }

    /**
    * Удаляет аватар пользователя
    * @param array $user Данные пользователя
    * @return void
    */
    private function deleteUserAvatar($user) {
        if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') {
            $avatarPath = UPLOADS_PATH . '/avatars/' . $user['avatar'];
            if (file_exists($avatarPath)) {
                @unlink($avatarPath);
            }
        }
    }

    /**
    * Полностью уничтожает текущую сессию
    * @return void
    */
    private function destroySession() {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

}
