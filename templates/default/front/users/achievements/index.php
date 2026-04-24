<?php
/**
 * Template Name: Страница всех достижений системы 
 */

$currentUserId = $_SESSION['user_id'] ?? null;
?>

<div class="tg-page">
    <div class="tg-container">

        <div class="tg-card tg-mb-4">
            <div class="tg-card-body">
                <h1 class="tg-page-title tg-mb-3" style="font-size: 28px;">
                    <?php echo bloggy_icon('bs', 'trophy-fill', '28', 'var(--tg-primary)', 'tg-mr-2'); ?>
                    <?php echo LANG_TEMPLATE_ACHIEVEMENTS_SYSTEM_TITLE; ?>
                </h1>
                
                <div class="tg-stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
                    <div class="tg-card" style="background: var(--tg-surface); padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; background: var(--tg-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <?php echo bloggy_icon('bs', 'trophy', '24', 'var(--tg-primary)'); ?>
                            </div>
                            <div>
                                <div class="tg-stat-value" style="font-size: 24px; font-weight: 600; color: var(--tg-text); line-height: 1.2;">
                                    <?php echo $totalAchievements ?? 0; ?>
                                </div>
                                <div class="tg-stat-label" style="font-size: 14px; color: var(--tg-text-secondary);">
                                    <?php echo LANG_TEMPLATE_ACHIEVEMENTS_STATS_TOTAL; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tg-card" style="background: var(--tg-surface); padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; background: var(--tg-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <?php echo bloggy_icon('bs', 'people', '24', 'var(--tg-primary)'); ?>
                            </div>
                            <div>
                                <div class="tg-stat-value" style="font-size: 24px; font-weight: 600; color: var(--tg-text); line-height: 1.2;">
                                    <?php echo $total = (int)($totalUsers ?? 0); ?>
                                </div>
                                <div class="tg-stat-label" style="font-size: 14px; color: var(--tg-text-secondary);">
                                    <?php echo plural($total, [LANG_TEMPLATE_ACHIEVEMENTS_USER_1, LANG_TEMPLATE_ACHIEVEMENTS_USER_2, LANG_TEMPLATE_ACHIEVEMENTS_USER_3]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tg-card" style="background: var(--tg-surface); padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; background: var(--tg-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <?php echo bloggy_icon('bs', 'star', '24', 'var(--tg-primary)'); ?>
                            </div>
                            <div>
                                <div class="tg-stat-value" style="font-size: 24px; font-weight: 600; color: var(--tg-text); line-height: 1.2;">
                                    <?php echo $totalUnlockedAchievements ?? 0; ?>
                                </div>
                                <div class="tg-stat-label" style="font-size: 14px; color: var(--tg-text-secondary);">
                                    <?php echo LANG_TEMPLATE_ACHIEVEMENTS_STATS_UNLOCKED; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tg-achievements-list">
            <?php if (!empty($achievements)) { ?>
                <?php foreach ($achievements as $achievement) {
                    $isUnlocked = isset($userAchievements[$achievement['id']]) && $userAchievements[$achievement['id']];
                ?> 
                    <div class="tg-card tg-mb-4">
                        <div class="tg-card-body">
                            <div style="display: grid; grid-template-columns: 140px 1fr 200px; gap: 24px; align-items: start;">
                                <div style="text-align: center;">
                                    <div style="margin-bottom: 12px;">
                                        <?php if (!empty($achievement['image'])) { ?>
                                            <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo html($achievement['image']); ?>" 
                                                 alt="<?php echo html($achievement['name']); ?>"
                                                 style="width: 100px; height: 100px;">
                                        <?php } else { ?>
                                            <div class="tg-achievement-icon" style="width: 100px; height: 100px; margin: 0 auto; border-radius: 50%; background: <?php echo $achievement['icon_color'] ?? 'var(--tg-primary)'; ?>; display: flex; align-items: center; justify-content: center;">
                                                <?php echo bloggy_icon('bs', $achievement['icon'] ?? 'trophy', '40', 'white'); ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    
                                    <?php if ($isUnlocked) { ?>
                                        <span class="tg-badge" style="background: #d4edda; color: #155724; padding: 4px 12px;">
                                            <?php echo bloggy_icon('bs', 'check-circle', '12', '#155724', 'tg-mr-1'); ?>
                                            <?php echo LANG_TEMPLATE_ACHIEVEMENTS_UNLOCKED_BADGE; ?>
                                        </span>
                                    <?php } ?>
                                </div>
                                
                                <div>
                                    <h3 class="tg-achievement-name" style="font-size: 18px; font-weight: 600; margin: 0 0 8px 0;">
                                        <a href="<?php echo BASE_URL; ?>/achievement/<?php echo $achievement['id']; ?>" 
                                           style="color: var(--tg-text); text-decoration: none; transition: var(--tg-transition);">
                                            <?php echo html($achievement['name']); ?>
                                        </a>
                                    </h3>
                                    
                                    <p class="tg-achievement-description" style="font-size: 14px; color: var(--tg-text-secondary); margin-bottom: 16px;">
                                        <?php echo html($achievement['description']); ?>
                                    </p>
                                    
                                    <?php if (!empty($achievement['formatted_conditions'])) { ?>
                                    <div class="tg-achievement-conditions" style="margin-bottom: 16px;">
                                        <h6 style="font-size: 12px; font-weight: 600; color: var(--tg-text-secondary); margin: 0 0 8px 0; display: flex; align-items: center;">
                                            <?php echo bloggy_icon('bs', 'gear', '12', 'currentColor', 'tg-mr-1'); ?>
                                            <?php echo LANG_TEMPLATE_ACHIEVEMENTS_CONDITIONS_TITLE; ?>
                                        </h6>
                                        <ul style="list-style: none; padding: 0; margin: 0;">
                                            <?php foreach ($achievement['formatted_conditions'] as $condition) { ?>
                                                <li style="font-size: 13px; color: var(--tg-text-secondary); margin-bottom: 4px; display: flex; align-items: center;">
                                                    <?php echo bloggy_icon('bs', 'arrow-right', '10', 'currentColor', 'tg-mr-1'); ?>
                                                    <?php echo html($condition); ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <?php } ?>
                                    
                                    <div class="tg-achievement-users">
                                        <h6 style="font-size: 12px; font-weight: 600; color: var(--tg-text-secondary); margin: 0 0 8px 0; display: flex; align-items: center;">
                                            <?php echo bloggy_icon('bs', 'people', '12', 'currentColor', 'tg-mr-1'); ?>
                                            <?php echo LANG_TEMPLATE_ACHIEVEMENTS_USERS_TITLE; ?>
                                        </h6>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <?php if (!empty($achievement['preview_users'])) { ?>
                                                <div class="tg-user-avatars" style="display: flex;">
                                                    <?php foreach ($achievement['preview_users'] as $user) { ?>
                                                        <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($user['username']); ?>" 
                                                           class="tg-user-avatar-link"
                                                           title="<?php echo html($user['display_name'] ?? $user['username']); ?>"
                                                           style="margin-left: -5px; transition: var(--tg-transition);">
                                                            <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                                                                <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($user['avatar']); ?>" 
                                                                     style="width: 30px; height: 30px; border-radius: 50%; border: 2px solid var(--tg-surface); object-fit: cover;"
                                                                     alt="<?php echo html($user['display_name'] ?? $user['username']); ?>">
                                                            <?php } else { ?>
                                                                <div class="tg-avatar-placeholder" 
                                                                     style="width: 30px; height: 30px; border-radius: 50%; border: 2px solid var(--tg-surface); font-size: 12px;">
                                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                                </div>
                                                            <?php } ?>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                                
                                                <?php if ($achievement['unlocked_count'] > 5) { ?>
                                                    <span class="tg-text-muted" style="font-size: 12px;">
                                                        <?php echo sprintf(LANG_TEMPLATE_ACHIEVEMENTS_MORE_USERS, $achievement['unlocked_count'] - 5); ?>
                                                    </span>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <span class="tg-text-muted" style="font-size: 12px;"><?php echo LANG_TEMPLATE_ACHIEVEMENTS_NO_USERS; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="background: var(--tg-bg); border-radius: var(--tg-radius-md); padding: 16px;">
                                    <div style="text-align: center; margin-bottom: 12px;">
                                        <div style="font-size: 24px; font-weight: 600; color: var(--tg-primary); line-height: 1.2;">
                                            <?php echo $achievement['unlocked_count']; ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--tg-text-secondary);">
                                            <?php echo LANG_TEMPLATE_ACHIEVEMENTS_UNLOCKED_STATS; ?>
                                        </div>
                                    </div>
                                    
                                    <div style="text-align: center; margin-bottom: 16px;">
                                        <div style="font-size: 20px; font-weight: 600; color: var(--tg-text); line-height: 1.2;">
                                            <?php echo $achievement['percent']; ?>%
                                        </div>
                                        <div style="font-size: 12px; color: var(--tg-text-secondary);">
                                            <?php echo LANG_TEMPLATE_ACHIEVEMENTS_PERCENT_STATS; ?>
                                        </div>
                                    </div>
                                    
                                    <div style="background: var(--tg-border); height: 6px; border-radius: 3px; margin-bottom: 16px; overflow: hidden;">
                                        <div style="background: var(--tg-primary); width: <?php echo $achievement['percent']; ?>%; height: 100%;"></div>
                                    </div>
                                    
                                    <a href="<?php echo BASE_URL; ?>/achievement/<?php echo $achievement['id']; ?>" 
                                       class="tg-btn tg-btn-outline" style="width: 100%; justify-content: center;">
                                        <?php echo bloggy_icon('bs', 'info-circle', '14', 'currentColor', 'tg-mr-1'); ?>
                                        <?php echo LANG_TEMPLATE_ACHIEVEMENTS_DETAILS_BTN; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                
                <?php if (isset($pagination) && $pagination['total_pages'] > 1) { ?>
                    <div class="tg-pagination tg-mt-5">
                        <?php if ($pagination['current_page'] > 1) { ?>
                            <a href="?page=<?php echo $pagination['current_page'] - 1; ?>" class="tg-page-link">
                                <?php echo bloggy_icon('bs', 'chevron-left', '16', 'currentColor'); ?>
                            </a>
                        <?php } ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++) { ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="tg-page-link <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php } ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']) { ?>
                            <a href="?page=<?php echo $pagination['current_page'] + 1; ?>" class="tg-page-link">
                                <?php echo bloggy_icon('bs', 'chevron-right', '16', 'currentColor'); ?>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>
                
            <?php } else { ?>
                <div class="tg-empty-state">
                    <div class="tg-empty-state-icon" style="margin-bottom: 24px;">
                        <?php echo bloggy_icon('bs', 'emoji-frown', '64', 'var(--tg-text-secondary)'); ?>
                    </div>
                    <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_ACHIEVEMENTS_EMPTY_TITLE; ?></h3>
                    <p class="tg-empty-state-text">
                        <?php echo LANG_TEMPLATE_ACHIEVEMENTS_EMPTY_TEXT; ?>
                    </p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>