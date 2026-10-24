<?php

namespace profile\actions;

/**
* Действие завершения всех сессий пользователя кроме текущей
*/
class TerminateAllSessions extends ProfileAction {

    /**
    * Метод выполнения завершения всех сессий
    * @return void
    */
    public function execute() {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATEALLSESSIONS_UNAUTHORIZED]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';

        if (!$this->validateApiCsrfToken($csrfToken)) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATEALLSESSIONS_INVALID_CSRF]);
            return;
        }

        try {
            if (\SessionTracker::terminateAllExceptCurrent($this->db, (int)$_SESSION['user_id'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATEALLSESSIONS_ERROR]);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_TERMINATEALLSESSIONS_ERROR]);
        }
    }

}
