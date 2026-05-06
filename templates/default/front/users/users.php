<?php
/**
 * Template Name: Страница списка пользователей 
 */

$totalPosts = 0;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

if (!empty($users)) {
    foreach ($users as $user) {
        $totalPosts += $user['posts_count'] ?? 0;
    }
}
?>

<div class="tg-page">
    <div class="tg-container">

        <div class="tg-card tg-mb-4">
            <div class="tg-card-body">
                <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1 class="tg-page-title tg-mb-1" style="font-size: 28px; margin-bottom: 4px;">
                            <?php echo bloggy_icon('bs', 'people-fill', '24', 'var(--tg-primary)', 'tg-mr-2'); ?>
                            <?php echo LANG_TEMPLATE_USERS_TITLE; ?>
                        </h1>
                        <p class="tg-text-muted" style="margin-left: 36px;">
                            <span style="font-weight: 600; color: #31b131;"><?php echo $online = (int)($online_count ?? 0); ?></span> 
                            <?php echo plural($online, [LANG_TEMPLATE_USERS_ONLINE_1, LANG_TEMPLATE_USERS_ONLINE_2, LANG_TEMPLATE_USERS_ONLINE_3]); ?>
                            <?php echo LANG_TEMPLATE_USERS_OUT_OF; ?>
                            <span style="font-weight: 600; color: var(--tg-primary);"><?php echo $total = (int)($total_users ?? 0); ?></span> 
                            <?php echo plural($total, [LANG_TEMPLATE_USERS_MEMBER_1, LANG_TEMPLATE_USERS_MEMBER_2, LANG_TEMPLATE_USERS_MEMBER_3]); ?>
                        </p>
                    </div>
                    
                    <?php if ($isAdmin) { ?>
                    <div class="tg-stats" style="gap: 12px;">
                        <div class="tg-stat" style="background: var(--tg-bg); padding: 8px 16px; border-radius: var(--tg-radius-md); min-width: 100px;">
                            <span class="tg-stat-value" style="font-size: 20px;"><?php echo $total = (int)($totalPosts ?? 0); ?></span>
                            <span class="tg-stat-label"><?php echo plural($total, [LANG_TEMPLATE_USERS_POST_1, LANG_TEMPLATE_USERS_POST_2, LANG_TEMPLATE_USERS_POST_3]); ?></span>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="tg-users-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <?php if (!empty($users)) { ?>
                <?php foreach ($users as $user) { 
                    $isUserAdmin = ($user['role'] === 'admin' || !empty($user['is_admin']));
                ?>
                    <div class="tg-card tg-user-card" style="display: flex; flex-direction: column; height: 100%; transition: var(--tg-transition);">
                        <div class="tg-card-body" style="flex: 1; display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <div style="position: relative; flex-shrink: 0;">
                                    <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($user['username']); ?>" class="tg-avatar-link">
                                        <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                                            <img class="tg-avatar" src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($user['avatar']); ?>" 
                                                 alt="<?php echo html($user['display_name'] ?? $user['username']); ?>"
                                                 style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover;">
                                        <?php } else { ?>
                                            <div class="tg-avatar-placeholder" style="width: 56px; height: 56px; font-size: 24px;">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                        <?php } ?>
                                        
                                        <?php if ($user['is_online'] ?? false) { ?>
                                            <span class="tg-online" style="position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; border: 2px solid var(--tg-surface);" 
                                                  title="<?php echo LANG_TEMPLATE_USERS_ONLINE_TITLE; ?>"></span>
                                        <?php } ?>
                                    </a>
                                </div>
                                
                                <div style="flex: 1; min-width: 0;">
                                    <h3 class="tg-user-name" style="font-size: 16px; font-weight: 600; margin: 0 0 2px 0;">
                                        <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($user['username']); ?>" 
                                           style="color: var(--tg-text); text-decoration: none; transition: var(--tg-transition);">
                                            <?php echo html($user['display_name'] ?? $user['username']); ?>
                                        </a>
                                    </h3>
                                    <?php if ($showLastAchievement && !empty($user['last_achievement'])) { ?>
                                        <span class="badge achievement-latest-badge ms-2" 
                                            title="<?php echo html($user['last_achievement']['tooltip_text'] ?? $user['last_achievement']['name']); ?>"
                                            data-bs-toggle="tooltip">
                                            <?php if (!empty($user['last_achievement']['image'])) { ?>
                                                <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo $user['last_achievement']['image']; ?>" 
                                                    style="width: 12px; height: 12px; object-fit: cover; border-radius: 2px; margin-right: 4px;">
                                            <?php } else { ?>
                                                <?php 
                                                $iconName = str_replace('bi-', '', $user['last_achievement']['icon'] ?? 'trophy');
                                                echo bloggy_icon('bs', $iconName, '10', '#ffc107', 'me-1');
                                                ?>
                                            <?php } ?>
                                            <?php echo html($user['last_achievement']['name']); ?>
                                        </span>
                                    <?php } ?>
                                    <div class="tg-text-muted" style="font-size: 12px;">
                                        @<?php echo html($user['username']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($user['groups'])) { ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 12px;">
                                    <?php foreach (array_slice($user['groups'], 0, 2) as $group) { ?>
                                        <span class="tg-badge" style="background: var(--tg-bg); font-size: 11px; padding: 2px 8px;">
                                            <?php echo bloggy_icon('bs', 'people-fill', '10', 'var(--tg-primary)', 'tg-mr-1'); ?>
                                            <?php echo html($group['name']); ?>
                                        </span>
                                    <?php } ?>
                                    <?php if (count($user['groups']) > 2) { ?>
                                        <span class="tg-badge" style="background: var(--tg-bg); font-size: 11px;">
                                            +<?php echo count($user['groups']) - 2; ?>
                                        </span>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            
                            <div class="tg-stats" style="gap: 8px; margin-bottom: 12px; padding: 8px; background: var(--tg-bg); border-radius: var(--tg-radius-md);">
                                <?php if ($isAdmin || $isUserAdmin) { ?>
                                <div class="tg-stat" style="flex: 1; text-align: center;">
                                    <span class="tg-stat-value" style="font-size: 16px;"><?php echo $total = (int)($user['posts_count'] ?? 0); ?></span>
                                    <span class="tg-stat-label" style="font-size: 10px;"><?php echo plural($total, [LANG_TEMPLATE_USERS_POST_1, LANG_TEMPLATE_USERS_POST_2, LANG_TEMPLATE_USERS_POST_3]); ?></span>
                                </div>
                                <?php } ?>
                                <div class="tg-stat" style="flex: 1; text-align: center;">
                                    <span class="tg-stat-value" style="font-size: 16px;"><?php echo $user['comments_count'] ?? 0; ?></span>
                                    <span class="tg-stat-label" style="font-size: 10px;"><?php echo LANG_TEMPLATE_USERS_COMMENT_LABEL; ?></span>
                                </div>
                                <div class="tg-stat" style="flex: 1; text-align: center;">
                                    <span class="tg-stat-value" style="font-size: 16px;"><?php echo $total = (int)($user['unlocked_achievements_count'] ?? 0); ?></span>
                                    <span class="tg-stat-label" style="font-size: 10px;"><?php echo plural($total, [LANG_TEMPLATE_USERS_ACHIEVEMENT_1, LANG_TEMPLATE_USERS_ACHIEVEMENT_2, LANG_TEMPLATE_USERS_ACHIEVEMENT_3]); ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($user['bio'])) { ?>
                            <div class="tg-bio-preview" style="margin-bottom: 12px; font-size: 13px; color: var(--tg-text-secondary); line-height: 1.4;">
                                <p style="margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php echo html(truncate_text(strip_tags($user['bio']), 80)); ?>
                                </p>
                            </div>
                            <?php } ?>
                            
                            <div style="margin-top: auto; padding-top: 12px; border-top: 1px solid var(--tg-border);">
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px;">
                                    <?php if ($user['is_online'] ?? false) { ?>
                                        <span style="display: flex; align-items: center; gap: 4px; color: #31b131;">
                                            <?php echo bloggy_icon('bs', 'circle-fill', '8', '#31b131'); ?>
                                            <span style="font-weight: 500;"><?php echo LANG_TEMPLATE_USERS_ONLINE_BADGE; ?></span>
                                        </span>
                                    <?php } elseif (!empty($user['last_activity_human'])) { ?>
                                        <span style="display: flex; align-items: center; gap: 4px; color: var(--tg-text-secondary);">
                                            <?php echo bloggy_icon('bs', 'clock-history', '10', 'currentColor'); ?>
                                            <span><?php echo sprintf(LANG_TEMPLATE_USERS_WAS_ONLINE, $user['last_activity_human']); ?></span>
                                        </span>
                                    <?php } else { ?>
                                        <span style="display: flex; align-items: center; gap: 4px; color: var(--tg-text-secondary);">
                                            <?php echo bloggy_icon('bs', 'clock-history', '10', 'currentColor'); ?>
                                            <span><?php echo LANG_TEMPLATE_USERS_NO_ACTIVITY; ?></span>
                                        </span>
                                    <?php } ?>
                                    
                                    <?php if (!empty($user['registration_days'])) { ?>
                                        <span style="display: flex; align-items: center; gap: 4px; color: var(--tg-text-secondary);">
                                            <?php echo bloggy_icon('bs', 'calendar-day', '10', 'currentColor'); ?>
                                            <span><?php echo sprintf(LANG_TEMPLATE_USERS_DAYS, $user['registration_days']); ?></span>
                                        </span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tg-card-footer" style="padding: 12px 16px;">
                            <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($user['username']); ?>" 
                               class="tg-btn tg-btn-outline" style="width: 100%; justify-content: center;">
                                <?php echo bloggy_icon('bs', 'person', '14', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo LANG_TEMPLATE_USERS_PROFILE_BTN; ?>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="tg-empty-state" style="grid-column: 1 / -1;">
                    <div class="tg-empty-state-icon" style="margin-bottom: 24px;">
                        <?php echo bloggy_icon('bs', 'people', '64', 'var(--tg-text-secondary)'); ?>
                    </div>
                    <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_USERS_EMPTY_TITLE; ?></h3>
                    <p class="tg-empty-state-text">
                        <?php echo LANG_TEMPLATE_USERS_EMPTY_TEXT; ?>
                    </p>
                    <?php if (!isset($_SESSION['user_id'])) { ?>
                    <a href="<?php echo BASE_URL; ?>/auth/register" class="tg-btn tg-btn-primary tg-mt-3">
                        <?php echo bloggy_icon('bs', 'person-plus', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_USERS_REGISTER_BTN; ?>
                    </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        
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
    </div>
</div>

<?php 
if (!empty($users)) { 
    ob_start();
?>
<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<?php 
    front_bottom_js(ob_get_clean());
    echo add_frontend_js('/templates/default/front/assets/js/user-action.js');
} 
?>