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
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => LANG_ACTION_PROFILE_SESSIONS_UNAUTHORIZED]);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $sessions = $this->getUserSessions($userId);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'sessions' => $sessions]);
    }
    
    /**
    * Получает активные сессии пользователя
    * @param int $userId ID пользователя
    * @return array Массив сессий
    */
    private function getUserSessions($userId) {
        $sessions = [];
        
        $tableExists = $this->db->fetch(
            "SELECT 1 FROM information_schema.tables 
             WHERE table_schema = DATABASE() AND table_name = 'user_sessions'"
        );
        
        if ($tableExists) {
            $results = $this->db->fetchAll(
                "SELECT id, session_id, ip_address, user_agent, last_activity, created_at 
                 FROM user_sessions 
                 WHERE user_id = ? 
                 ORDER BY last_activity DESC",
                [$userId]
            );
            
            foreach ($results as $row) {
                $sessions[] = [
                    'id' => $row['id'],
                    'session_id' => $row['session_id'],
                    'ip' => $row['ip_address'],
                    'device' => $this->parseUserAgent($row['user_agent']),
                    'last_activity' => date('d.m.Y H:i', strtotime($row['last_activity'])),
                    'created_at' => date('d.m.Y H:i', strtotime($row['created_at'])),
                    'is_current' => ($row['session_id'] === session_id())
                ];
            }
        }
        
        if (empty($sessions)) {
            $sessions[] = [
                'id' => 0,
                'session_id' => session_id(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'device' => $this->parseUserAgent($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'last_activity' => date('d.m.Y H:i'),
                'created_at' => date('d.m.Y H:i'),
                'is_current' => true
            ];
        }
        
        return $sessions;
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
        } else {
            return LANG_ACTION_PROFILE_SESSIONS_DEVICE_OTHER;
        }
    }
}