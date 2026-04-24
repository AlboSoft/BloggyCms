<?php
/**
 * Template Name: Страница просмотра достижения
 */

$currentUserId = $_SESSION['user_id'] ?? null;
?>

<div class="tg-page">
    <div class="tg-container">

        <div class="tg-achievement-detail">
            <div class="tg-achievement-sidebar">
                <div class="tg-card tg-mb-4">
                    <div class="tg-card-body tg-achievement-icon-block">
                        <div class="tg-achievement-icon-large">
                            <?php if (!empty($achievement['image'])) { ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo html($achievement['image']); ?>" 
                                     alt="<?php echo html($achievement['name']); ?>"
                                     class="tg-achievement-image">
                            <?php } else { ?>
                                <div class="tg-achievement-icon-placeholder" 
                                     style="background: <?php echo $achievement['icon_color'] ?? 'var(--tg-primary)'; ?>">
                                    <?php echo bloggy_icon('bs', $achievement['icon'] ?? 'trophy', '48', 'white'); ?>
                                </div>
                            <?php } ?>
                        </div>
                        
                        <?php if ($userHasAchievement) { ?>
                            <div class="tg-achievement-status">
                                <span class="tg-badge tg-badge-success">
                                    <?php echo bloggy_icon('bs', 'check-circle', '14', '#155724', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_YOU_HAVE; ?>
                                </span>
                            </div>
                        <?php } ?>
                        
                        <div class="tg-achievement-stats">
                            <div class="tg-stat tg-stat-large">
                                <span class="tg-stat-value"><?php echo $total = (int)($achievement['unlocked_count'] ?? 0); ?></span>
                                <span class="tg-stat-label"><?php echo plural($total, [LANG_TEMPLATE_ACHIEVEMENT_DETAIL_USERS_1, LANG_TEMPLATE_ACHIEVEMENT_DETAIL_USERS_2, LANG_TEMPLATE_ACHIEVEMENT_DETAIL_USERS_3]); ?></span>
                            </div>
                            
                            <div class="tg-stat tg-stat-large">
                                <span class="tg-stat-value"><?php echo $achievement['percent']; ?>%</span>
                                <span class="tg-stat-label"><?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_PERCENT_LABEL; ?></span>
                            </div>
                            
                            <div class="tg-progress">
                                <div class="tg-progress-bar" style="width: <?php echo $achievement['percent']; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                 
                <?php if (!empty($achievement['formatted_conditions'])) { ?>
                <div class="tg-card">
                    <div class="tg-card-header">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'gear', '16', 'var(--tg-primary)', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_CONDITIONS_TITLE; ?>
                        </h3>
                    </div>
                    <div class="tg-card-body">
                        <ul class="tg-conditions-list">
                            <?php foreach ($achievement['formatted_conditions'] as $condition) { ?>
                                <li class="tg-condition-item">
                                    <span class="tg-condition-icon"><?php echo bloggy_icon('bs', 'check2', '14', 'var(--tg-primary)'); ?></span>
                                    <span class="tg-condition-text"><?php echo html($condition); ?></span>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <div class="tg-achievement-main">
                <div class="tg-card tg-mb-4">
                    <div class="tg-card-body">
                        <h1 class="tg-achievement-title">
                            <?php echo html($achievement['name']); ?>
                        </h1>
                        
                        <p class="tg-achievement-description">
                            <?php echo html($achievement['description']); ?>
                        </p>
                        
                        <div class="tg-achievement-meta">
                            <div class="tg-meta-item">
                                <span class="tg-meta-label">
                                    <?php echo bloggy_icon('bs', 'clock', '10', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_TYPE_LABEL; ?>
                                </span>
                                <span class="tg-meta-value">
                                    <?php echo $achievement['type'] == 'auto' ? LANG_TEMPLATE_ACHIEVEMENT_DETAIL_TYPE_AUTO : LANG_TEMPLATE_ACHIEVEMENT_DETAIL_TYPE_MANUAL; ?>
                                </span>
                            </div>
                            
                            <div class="tg-meta-item">
                                <span class="tg-meta-label">
                                    <?php echo bloggy_icon('bs', 'calendar', '10', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_CREATED_LABEL; ?>
                                </span>
                                <span class="tg-meta-value">
                                    <?php echo date('d.m.Y', strtotime($achievement['created_at'])); ?>
                                </span>
                            </div>
                            
                            <div class="tg-meta-item">
                                <span class="tg-meta-label">
                                    <?php echo bloggy_icon('bs', 'star', '10', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_PRIORITY_LABEL; ?>
                                </span>
                                <span class="tg-meta-value">
                                    <?php echo $achievement['priority']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tg-card">
                    <div class="tg-card-header tg-card-header-with-count">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'people', '16', 'var(--tg-primary)', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_USERS_TITLE; ?>
                        </h3>
                        <span class="tg-badge tg-badge-count">
                            <?php echo $pagination['total'] ?? 0; ?>
                        </span>
                    </div>
                    
                    <div class="tg-card-body">
                        <?php if (!empty($users)) { ?>
                            <div class="tg-users-grid tg-users-grid-achievement">
                                <?php foreach ($users as $user) { ?>
                                    <div class="tg-user-mini-card">
                                        <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($user['username']); ?>" 
                                           class="tg-user-mini-link">
                                            <div class="tg-user-mini-avatar">
                                                <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                                                    <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($user['avatar']); ?>" 
                                                         class="tg-avatar tg-avatar-medium"
                                                         alt="<?php echo html($user['display_name'] ?? $user['username']); ?>">
                                                <?php } else { ?>
                                                    <div class="tg-avatar-placeholder tg-avatar-medium">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            
                                            <div class="tg-user-mini-info">
                                                <div class="tg-user-mini-name">
                                                    <?php echo html($user['display_name'] ?? $user['username']); ?>
                                                </div>
                                                <div class="tg-user-mini-username">
                                                    @<?php echo html($user['username']); ?>
                                                </div>
                                            </div>
                                        </a>
                                        
                                        <div class="tg-user-mini-date">
                                            <?php echo bloggy_icon('bs', 'calendar3', '10', 'currentColor', 'tg-mr-1'); ?>
                                            <?php echo sprintf(LANG_TEMPLATE_ACHIEVEMENT_DETAIL_UNLOCKED_AT, date('d.m.Y', strtotime($user['unlocked_at']))); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <?php if (isset($pagination) && $pagination['total_pages'] > 1) { ?>
                                <div class="tg-pagination tg-mt-4">
                                    <?php if ($pagination['page'] > 1) { ?>
                                        <a href="?page=<?php echo $pagination['page'] - 1; ?>" class="tg-page-link">
                                            <?php echo bloggy_icon('bs', 'chevron-left', '14', 'currentColor'); ?>
                                            <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_BACK_BTN; ?>
                                        </a>
                                    <?php } ?>
                                    
                                    <?php 
                                    $startPage = max(1, $pagination['page'] - 2);
                                    $endPage = min($pagination['total_pages'], $pagination['page'] + 2);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++) { 
                                    ?>
                                        <a href="?page=<?php echo $i; ?>" 
                                           class="tg-page-link <?php echo $i == $pagination['page'] ? 'tg-active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php } ?>
                                    
                                    <?php if ($pagination['page'] < $pagination['total_pages']) { ?>
                                        <a href="?page=<?php echo $pagination['page'] + 1; ?>" class="tg-page-link">
                                            <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_NEXT_BTN; ?>
                                            <?php echo bloggy_icon('bs', 'chevron-right', '14', 'currentColor'); ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            
                        <?php } else { ?>
                            <div class="tg-empty-state tg-empty-state-small">
                                <div class="tg-empty-state-icon">
                                    <?php echo bloggy_icon('bs', 'people', '48', 'var(--tg-text-secondary)'); ?>
                                </div>
                                <h4 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_NO_USERS_TITLE; ?></h4>
                                <p class="tg-empty-state-text">
                                    <?php echo LANG_TEMPLATE_ACHIEVEMENT_DETAIL_NO_USERS_TEXT; ?>
                                </p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>