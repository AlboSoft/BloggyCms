<?php

namespace profile\actions;

/**
* Действие завершения указанной сессии пользователя
*/
class TerminateSession extends ProfileAction {
    
    /**
    * Метод выполнения завершения сессии 
    * @return void
    */
    public function execute() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_UNAUTHORIZED]);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = $input['session_id'] ?? null;
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCsrfToken($csrfToken)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_INVALID_CSRF]);
            return;
        }
        
        if (!$sessionId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_NO_SESSION_ID]);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        if ($this->terminateUserSession($userId, $sessionId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_ERROR]);
        }
    }
    
    /**
    * Завершает указанную сессию пользователя
    * @param int $userId ID пользователя
    * @param int $sessionId ID сессии из БД
    * @return bool Результат операции
    */
    private function terminateUserSession($userId, $sessionId) {

        $session = $this->db->fetch(
            "SELECT session_id FROM user_sessions WHERE id = ? AND user_id = ?",
            [$sessionId, $userId]
        );
        
        if ($session && $session['session_id'] === session_id()) {
            return false;
        }
        
        $result = $this->db->query(
            "DELETE FROM user_sessions WHERE id = ? AND user_id = ?",
            [$sessionId, $userId]
        );
        
        return $result && $result->rowCount() > 0;
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