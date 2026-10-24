<?php

/**
* Отслеживание активных сессий пользователей.
* Использует таблицу user_sessions_online из штатной схемы БД.
* @package Core
*/
class SessionTracker {

    /**
    * Имя таблицы с онлайн-сессиями (без префикса БД)
    */
    const TABLE = 'user_sessions_online';

    /**
    * Ключ сессии, в котором хранится идентификатор отслеживаемой сессии
    */
    const SESSION_KEY = 'bloggy_tracked_session';

    /**
    * @var bool|null Кеш результата проверки наличия таблицы
    */
    private static $tableExists = null;

    /**
    * Проверяет наличие таблицы онлайн-сессий
    * @param object $db Подключение к базе данных
    * @return bool
    */
    public static function tableExists($db) {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }

        try {
            $prefix = method_exists($db, 'getPrefix') ? $db->getPrefix() : (defined('DB_PREFIX') ? DB_PREFIX : '');
            $row = $db->fetch(
                "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1",
                [$prefix . self::TABLE]
            );
            self::$tableExists = !empty($row);
        } catch (\Throwable $e) {
            self::$tableExists = false;
        }

        return self::$tableExists;
    }

    /**
    * Записывает текущую сессию пользователя в БД
    * @param object $db Подключение к базе данных
    * @param int $userId ID пользователя
    * @return bool
    */
    public static function touch($db, $userId) {
        if (!$userId || !self::tableExists($db)) {
            return false;
        }

        $sessionId = session_id();

        if ($sessionId === '') {
            return false;
        }

        try {
            $row = $db->fetch(
                "SELECT session_id FROM " . self::TABLE . " WHERE session_id = ? LIMIT 1",
                [$sessionId]
            );

            if ($row) {
                $db->query(
                    "UPDATE " . self::TABLE . " SET user_id = ?, last_activity = NOW() WHERE session_id = ?",
                    [(int)$userId, $sessionId]
                );
            } else {
                $db->query(
                    "INSERT INTO " . self::TABLE . " (session_id, user_id, last_activity) VALUES (?, ?, NOW())",
                    [$sessionId, (int)$userId]
                );
            }

            $_SESSION[self::SESSION_KEY] = $sessionId;

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
    * Проверяет, что текущая сессия не была завершена, и обновляет её активность
    * @param object $db Подключение к базе данных
    * @param int $userId ID пользователя
    * @return bool false, если сессия была завершена
    */
    public static function validateAndTouch($db, $userId) {
        if (!$userId || !self::tableExists($db)) {
            return true;
        }

        $sessionId = session_id();

        if ($sessionId === '') {
            return true;
        }

        $trackedSession = $_SESSION[self::SESSION_KEY] ?? null;

        if ($trackedSession !== null && $trackedSession !== $sessionId) {
            try {
                $db->query(
                    "DELETE FROM " . self::TABLE . " WHERE session_id = ? AND user_id = ?",
                    [$trackedSession, (int)$userId]
                );
            } catch (\Throwable $e) {

            }

            $trackedSession = null;
            unset($_SESSION[self::SESSION_KEY]);
        }

        try {
            if ($trackedSession !== null) {
                $row = $db->fetch(
                    "SELECT session_id FROM " . self::TABLE . " WHERE session_id = ? AND user_id = ? LIMIT 1",
                    [$sessionId, (int)$userId]
                );

                if (!$row) {
                    return false;
                }
            }
        } catch (\Throwable $e) {
            return true;
        }

        // Если запись не удалась (например, нет прав на таблицу), не разлогиниваем пользователя
        self::touch($db, $userId);

        return true;
    }

    /**
    * Возвращает активные сессии пользователя
    * @param object $db Подключение к базе данных
    * @param int $userId ID пользователя
    * @return array
    */
    public static function getForUser($db, $userId) {
        if (!$userId || !self::tableExists($db)) {
            return [];
        }

        try {
            $rows = $db->fetchAll(
                "SELECT session_id, last_activity FROM " . self::TABLE . " WHERE user_id = ? ORDER BY last_activity DESC",
                [(int)$userId]
            );
        } catch (\Throwable $e) {
            return [];
        }

        $currentSessionId = session_id();
        $sessions = [];

        foreach ($rows as $row) {
            $sessions[] = [
                'id' => $row['session_id'],
                'session_id' => $row['session_id'],
                'ip' => null,
                'device' => null,
                'last_activity' => !empty($row['last_activity']) ? date('d.m.Y H:i', strtotime($row['last_activity'])) : '',
                'created_at' => !empty($row['last_activity']) ? date('d.m.Y H:i', strtotime($row['last_activity'])) : '',
                'is_current' => $row['session_id'] === $currentSessionId,
            ];
        }

        return $sessions;
    }

    /**
    * Удаляет старые записи о сессиях пользователя
    * @param object $db Подключение к базе данных
    * @param int $userId ID пользователя
    * @param int $days Количество дней хранения
    * @return void
    */
    public static function cleanup($db, $userId, $days = 30) {
        if (!$userId || !self::tableExists($db)) {
            return;
        }

        try {
            $db->query(
                "DELETE FROM " . self::TABLE . " WHERE user_id = ? AND last_activity < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)",
                [(int)$userId]
            );
        } catch (\Throwable $e) {
            // игнорируем ошибку очистки
        }
    }

    /**
    * Завершает указанную сессию пользователя
    * @param object $db Подключение к базе данных
    * @param int $userId ID пользователя
    * @param string $sessionId Идентификатор сессии
    * @return bool
    */
    public static function terminate($db, $userId, $sessionId) {
        if (!$userId || $sessionId === '' || $sessionId === session_id() || !self::tableExists($db)) {
            return false;
        }

        try {
            $stmt = $db->query(
                "DELETE FROM " . self::TABLE . " WHERE session_id = ? AND user_id = ?",
                [$sessionId, (int)$userId]
            );

            return $stmt !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
    * Удаляет запись о текущей сессии (используется при выходе из аккаунта)
    * @param object $db Подключение к базе данных
    * @param int $userId ID пользователя
    * @return bool
    */
    public static function terminateCurrent($db, $userId) {
        if (!$userId || !self::tableExists($db)) {
            return false;
        }

        $trackedSession = $_SESSION[self::SESSION_KEY] ?? null;
        $currentSession = session_id();
        $sessionIds = array_values(array_unique(array_filter([$trackedSession, $currentSession])));

        if (empty($sessionIds)) {
            return false;
        }

        try {
            $placeholders = implode(', ', array_fill(0, count($sessionIds), '?'));
            $params = array_merge($sessionIds, [(int)$userId]);
            $db->query(
                "DELETE FROM " . self::TABLE . " WHERE session_id IN (" . $placeholders . ") AND user_id = ?",
                $params
            );
            unset($_SESSION[self::SESSION_KEY]);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
    * Завершает все сессии пользователя, кроме текущей
    * @param object $db Подключение к базе данных
    * @param int $userId ID пользователя
    * @return bool
    */
    public static function terminateAllExceptCurrent($db, $userId) {
        if (!$userId || !self::tableExists($db)) {
            return false;
        }

        try {
            $db->query(
                "DELETE FROM " . self::TABLE . " WHERE user_id = ? AND session_id != ?",
                [(int)$userId, session_id()]
            );

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
