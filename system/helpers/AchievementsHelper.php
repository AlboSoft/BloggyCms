<?php

/**
* Вспомогательный класс для работы с достижениями (ачивками)
* @package Helpers
*/
class AchievementsHelper {
    
    /** @var object|null Подключение к базе данных (статическое) */
    private static $db = null;
    
    /**
    * Устанавливает подключение к базе данных для всех статических методов 
    * @param object $db Подключение к базе данных
    * @return void
    */
    public static function setDatabase($db) {
        self::$db = $db;
    }
    
    /**
    * Получает все достижения пользователя
    * @param int $userId ID пользователя
    * @return array Массив достижений пользователя
    */
    public static function getUserAchievements($userId) {
        if (!self::$db) return [];
        
        $userModel = new UserModel(self::$db);
        return $userModel->getUserAchievements($userId);
    }
    
    /**
    * Получает прогресс выполнения достижения в процентах
    * @param int $userId ID пользователя
    * @param int $achievementId ID достижения
    * @return float Прогресс в процентах (0-100)
    */
    public static function getAchievementProgress($userId, $achievementId) {
        if (!self::$db) return 0;
        
        $userModel = new UserModel(self::$db);
        $data = self::$db->fetch(
            "SELECT progress, max_value FROM user_achievements_data 
             WHERE user_id = ? AND achievement_id = ?",
            [$userId, $achievementId]
        );
        
        if ($data && $data['max_value'] > 0) {
            return ($data['progress'] / $data['max_value']) * 100;
        }
        
        return 0;
    }
    
    /**
    * Обновляет статистику пользователя для проверки достижений
    * @param int $userId ID пользователя
    * @param string $statType Тип статистики
    * @param int $increment Значение увеличения (не используется)
    * @return bool Результат операции
    */
    public static function updateUserStat($userId, $statType, $increment = 1) {
        if (!self::$db) return false;
        
        $userModel = new UserModel(self::$db);
        return $userModel->updateUserStats($userId, $statType);
    }
    
    /**
    * Рендерит бейдж достижения в указанном размере 
    * @param array $achievement Данные достижения
    * @param string $size Размер: 'sm' (32px), 'md' (64px), 'lg' (96px)
    * @return string HTML-код бейджа
    */
    public static function renderAchievementBadge($achievement, $size = 'md') {
        $sizes = [
            'sm' => '32px',
            'md' => '64px',
            'lg' => '96px'
        ];
        
        $sizePx = $sizes[$size] ?? '64px';
        
        ob_start();
        ?>
        <div class="achievement-badge" style="position: relative; width: <?= $sizePx ?>; height: <?= $sizePx ?>;">
            <?php if($achievement['image']) { ?>
                <img src="<?= BASE_URL ?>/uploads/achievements/<?= $achievement['image'] ?>" 
                    alt="<?= html($achievement['name']) ?>"
                    class="img-fluid rounded-circle"
                    style="width: 100%; height: 100%; object-fit: cover;">
            <?php } else { ?>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 100%; height: 100%; background: <?= $achievement['icon_color'] ?>;">
                    <i class="bi bi-<?= $achievement['icon'] ?> text-white" 
                       style="font-size: <?= $size == 'sm' ? '16px' : ($size == 'md' ? '24px' : '32px') ?>"></i>
                </div>
            <?php } ?>
            
            <?php if($achievement['is_unlocked']) { ?>
                <div class="position-absolute top-0 end-0">
                    <i class="bi bi-check-circle-fill text-success" 
                       style="font-size: <?= $size == 'sm' ? '12px' : '16px' ?>"></i>
                </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Рендерит сетку достижений пользователя с прогресс-барами
    * @param int $userId ID пользователя
    * @return string HTML-код сетки достижений
    */
    public static function renderAchievementList($userId) {
        if (!self::$db) return '';
        
        $achievements = self::getUserAchievements($userId);
        if (empty($achievements)) {
            return '<p class="text-muted">' . LANG_HELPER_ACHIEVEMENTS_NO_ACHIEVEMENTS . '</p>';
        }
        
        ob_start();
        ?>
        <div class="achievements-grid">
            <?php foreach($achievements as $achievement) { ?>
                <div class="achievement-item <?= $achievement['is_unlocked'] ? 'unlocked' : 'locked' ?>"
                    data-bs-toggle="tooltip" 
                    title="<?= html($achievement['name']) ?> - <?= html($achievement['description']) ?>">
                    <?php echo self::renderAchievementBadge($achievement, 'sm'); ?>
                    
                    <?php if(!$achievement['is_unlocked']) { ?>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar" 
                                style="width: <?= self::getAchievementProgress($userId, $achievement['id']) ?>%"></div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Проверяет и разблокирует достижения для пользователя
    * @param int $userId ID пользователя
    * @return bool Результат операции
    */
    public static function checkAndUnlockAchievements($userId) {
        if (!self::$db) return false;
        
        $userModel = new UserModel(self::$db);
        return $userModel->updateUserStats($userId, 'all');
    }
}