<?php

namespace profile\actions;

/**
* Действие получения списка активных сессий пользователя
*/
class Sessions extends ProfileAction {

    /**
    * Метод выполнения получения сессий
    * @return void
    */
    public function execute() {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_SESSIONS_UNAUTHORIZED]);
            return;
        }

        $userId = (int)$_SESSION['user_id'];

        if (class_exists('\\SessionTracker')) {
            \SessionTracker::cleanup($this->db, $userId);
            \SessionTracker::touch($this->db, $userId);
            $sessions = \SessionTracker::getForUser($this->db, $userId);
        } else {
            $sessions = [];
        }

        if (empty($sessions)) {
            $sessions[] = [
                'id' => session_id(),
                'session_id' => session_id(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'device' => $this->parseUserAgent($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'last_activity' => date('d.m.Y H:i'),
                'created_at' => date('d.m.Y H:i'),
                'is_current' => true
            ];
        } else {
            foreach ($sessions as &$session) {
                if (!empty($session['is_current'])) {
                    $session['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
                    $session['device'] = $this->parseUserAgent($_SERVER['HTTP_USER_AGENT'] ?? '');
                } else {
                    $session['device'] = LANG_ACTION_PROFILE_SESSIONS_DEVICE_UNKNOWN;
                }
            }
            unset($session);
        }

        echo json_encode(['success' => true, 'sessions' => $sessions]);
    }

    /**
    * Парсит User Agent для определения устройства
    * @param string $userAgent Строка User Agent
    * @return string Название устройства
    */
    private function parseUserAgent($userAgent) {
        if (empty($userAgent)) {
            return LANG_ACTION_PROFILE_SESSIONS_DEVICE_UNKNOWN;
        }

        if (strpos($userAgent, 'Windows') !== false) {
            return LANG_ACTION_PROFILE_SESSIONS_DEVICE_WINDOWS;
        } elseif (strpos($userAgent, 'Macintosh') !== false) {
            return LANG_ACTION_PROFILE_SESSIONS_DEVICE_MAC;
        } elseif (strpos($userAgent, 'iPhone') !== false) {
            return LANG_ACTION_PROFILE_SESSIONS_DEVICE_IPHONE;
        } elseif (strpos($userAgent, 'iPad') !== false) {
            return LANG_ACTION_PROFILE_SESSIONS_DEVICE_IPAD;
        } elseif (strpos($userAgent, 'Android') !== false) {
            return LANG_ACTION_PROFILE_SESSIONS_DEVICE_ANDROID;
        } elseif (strpos($userAgent, 'Linux') !== false) {
            return LANG_ACTION_PROFILE_SESSIONS_DEVICE_LINUX;
        }

        return LANG_ACTION_PROFILE_SESSIONS_DEVICE_OTHER;
    }
}
