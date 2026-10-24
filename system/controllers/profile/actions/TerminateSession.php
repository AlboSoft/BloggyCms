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
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_UNAUTHORIZED]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = isset($input['session_id']) ? (string)$input['session_id'] : '';
        $csrfToken = $input['csrf_token'] ?? '';

        if (!$this->validateApiCsrfToken($csrfToken)) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_INVALID_CSRF]);
            return;
        }

        if ($sessionId === '') {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_NO_SESSION_ID]);
            return;
        }

        if ($sessionId === session_id()) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_CURRENT]);
            return;
        }

        try {
            if (\SessionTracker::terminate($this->db, (int)$_SESSION['user_id'], $sessionId)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_ERROR]);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATESESSION_ERROR]);
        }
    }

}
