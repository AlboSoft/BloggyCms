<?php

/**
* Менеджер для отслеживания активности пользователей
*/
class UserActivityManager {
    
    private $db;
    private static $instance = null;
    private $cache = [];
    
    private function __construct($db) {
        $this->db = $db;
    }
    
    public static function getInstance($db = null) {
        if (self::$instance === null && $db) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }
    
    /**
    * Обновляет активность пользователя 
    * @param int $userId ID пользователя
    * @return bool Результат обновления
    */
    public function touch($userId) {
        if (!$userId) return false;
        
        try {
            $tableCheck = $this->db->fetch("SHOW TABLES LIKE 'user_activity'");
            
            if ($tableCheck) {
                $sql = "INSERT INTO user_activity 
                        (user_id, last_activity, session_id, ip_address, user_agent) 
                        VALUES (?, NOW(), ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        last_activity = NOW(),
                        session_id = VALUES(session_id),
                        ip_address = VALUES(ip_address),
                        user_agent = VALUES(user_agent)";
                
                $params = [
                    $userId,
                    session_id(),
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
                ];
                
                $this->db->query($sql, $params);
                return true;
            } else {
                $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $this->db->query($sql, [$userId]);
                return true;
            }
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
    * Проверяет онлайн статус пользователя
    * @param int $userId ID пользователя
    * @return bool Онлайн ли пользователь
    */
    public function isOnline($userId) {
        if (!$userId) return false;
        
        $cacheKey = "online_{$userId}";
        $currentTime = time();
        
        if (isset($this->cache[$cacheKey])) {
            list($status, $timestamp) = $this->cache[$cacheKey];
            if ($currentTime - $timestamp < 5) {
                return $status;
            }
        }
        
        try {
            $tableCheck = $this->db->fetch("SHOW TABLES LIKE 'user_activity'");
            
            if ($tableCheck) {
                $sql = "SELECT 
                            CASE 
                                WHEN TIMESTAMPDIFF(SECOND, last_activity, NOW()) < 300 
                                THEN 1 ELSE 0 
                            END as is_online
                        FROM user_activity 
                        WHERE user_id = ? 
                        LIMIT 1";
                
                $result = $this->db->fetch($sql, [$userId]);
                
                if ($result) {
                    $isOnline = $result['is_online'] == 1;
                    $this->cache[$cacheKey] = [$isOnline, $currentTime];
                    return $isOnline;
                }
            }
            
            $sql = "SELECT 
                        CASE 
                            WHEN TIMESTAMPDIFF(SECOND, last_login, NOW()) < 300 
                            THEN 1 ELSE 0 
                        END as is_online
                    FROM users 
                    WHERE id = ? 
                    AND last_login IS NOT NULL
                    LIMIT 1";
            
            $result = $this->db->fetch($sql, [$userId]);
            
            $isOnline = $result && $result['is_online'] == 1;
            $this->cache[$cacheKey] = [$isOnline, $currentTime];
            
            return $isOnline;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
    * Получает информацию о последней активности 
    * @param int $userId ID пользователя
    * @return array Информация об активности
    */
    public function getLastActivityInfo($userId) {
        try {
            $tableCheck = $this->db->fetch("SHOW TABLES LIKE 'user_activity'");
            
            if ($tableCheck) {
                $sql = "SELECT last_activity 
                        FROM user_activity 
                        WHERE user_id = ? 
                        LIMIT 1";
                
                $result = $this->db->fetch($sql, [$userId]);
                
                if ($result && !empty($result['last_activity'])) {
                    return $this->formatActivityTime($result['last_activity']);
                }
            }
            
            $sql = "SELECT last_login FROM users WHERE id = ? LIMIT 1";
            $result = $this->db->fetch($sql, [$userId]);
            
            if ($result && !empty($result['last_login'])) {
                return $this->formatActivityTime($result['last_login']);
            }
            
            return ['human' => LANG_CORE_USERACTIVITY_NEVER, 'days' => 0];
            
        } catch (\Exception $e) {
            return ['human' => LANG_CORE_USERACTIVITY_UNKNOWN, 'days' => 0];
        }
    }
    
    /**
    * Форматирует время активности 
    * @param string $timestamp Временная метка
    * @return array Форматированное время
    */
    private function formatActivityTime($timestamp) {
        if (empty($timestamp)) {
            return ['human' => LANG_CORE_USERACTIVITY_NEVER, 'days' => 0];
        }
        
        $lastActivityTimestamp = strtotime($timestamp);
        $currentTimestamp = time();
        $secondsAgo = $currentTimestamp - $lastActivityTimestamp;
        
        if ($secondsAgo < 60) {
            $human = LANG_CORE_USERACTIVITY_JUST_NOW;
        } elseif ($secondsAgo < 3600) {
            $minutesAgo = floor($secondsAgo / 60);
            $human = sprintf(LANG_CORE_USERACTIVITY_MINUTES_AGO, $minutesAgo);
        } elseif ($secondsAgo < 86400) {
            $hoursAgo = floor($secondsAgo / 3600);
            $human = sprintf(LANG_CORE_USERACTIVITY_HOURS_AGO, $hoursAgo);
        } else {
            $daysAgo = floor($secondsAgo / 86400);
            $human = sprintf(LANG_CORE_USERACTIVITY_DAYS_AGO, $daysAgo);
        }
        
        return [
            'human' => $human,
            'days' => floor($secondsAgo / 86400)
        ];
    }
    
    /**
    * Очищает кэш
    */
    public function clearCache() {
        $this->cache = [];
    }
}