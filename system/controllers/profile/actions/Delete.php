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
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_UNAUTHORIZED]);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $password = $input['password'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCsrfToken($csrfToken)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_INVALID_CSRF]);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        $user = $this->userModel->getById($userId);
        if (!$user || !password_verify($password, $user['password'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_INVALID_PASSWORD]);
            return;
        }
        
        if ($user['role'] === 'admin') {
            $adminsCount = $this->db->fetchValue(
                "SELECT COUNT(*) FROM users WHERE role = 'admin'"
            );
            if ($adminsCount <= 1) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_DELETE_LAST_ADMIN]);
                return;
            }
        }
        
        $this->deleteUserAvatar($user);
        
        if ($this->userModel->delete($userId)) {
            session_destroy();
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
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
    * Проверяет CSRF токен
    * @param string $token Токен для проверки
    * @return bool Результат проверки
    */
    private function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}