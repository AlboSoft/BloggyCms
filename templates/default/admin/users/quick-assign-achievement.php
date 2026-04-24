<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'trophy', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/users" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '18', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_BACK_BTN; ?>
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <?php if ($user['avatar']) { ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo $user['avatar']; ?>" 
                                class="rounded-circle me-3" 
                                style="width: 64px; height: 64px; object-fit: cover;"
                                alt="<?php echo html($user['username']); ?>">
                        <?php } else { ?>
                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center bg-light" 
                                style="width: 64px; height: 64px;">
                                <?php echo bloggy_icon('bs', 'person', '24', '#6C6C6C'); ?>
                            </div>
                        <?php } ?>
                        
                        <div>
                            <h5 class="mb-1"><?php echo html($user['username']); ?></h5>
                            <p class="text-muted mb-1"><?php echo html($user['email']); ?></p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo $user['status'] === 'active' ? LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_STATUS_ACTIVE : LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_STATUS_BANNED; ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                        $userModel = new UserModel($db);
                        $userAchievements = $userModel->getUserUnlockedAchievements($user['id']);
                    ?>
                    
                    <?php if (!empty($userAchievements)) { ?>
                    <div class="mb-3">
                        <h6 class="mb-2">
                            <?php echo bloggy_icon('bs', 'check-circle', '18', '#28a745', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_CURRENT_ACHIEVEMENTS_TITLE; ?>
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($userAchievements as $achievement) { ?>
                                <div class="achievement-badge-small" data-bs-toggle="tooltip" 
                                     title="<?php echo html($achievement['name']); ?> - <?php echo html($achievement['description']); ?>">
                                    <?php if ($achievement['image']) { ?>
                                        <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo $achievement['image']; ?>" 
                                            class="rounded" 
                                            style="width: 32px; height: 32px; object-fit: cover;"
                                            alt="<?php echo html($achievement['name']); ?>">
                                    <?php } else { ?>
                                        <div class="rounded d-flex align-items-center justify-content-center" 
                                            style="width: 32px; height: 32px; background: <?php echo $achievement['icon_color']; ?>;">
                                            <?php 
                                            $iconName = str_replace('bi-', '', $achievement['icon']);
                                            echo bloggy_icon('bs', $iconName, '14', '#fff'); 
                                            ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
 
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <?php echo bloggy_icon('bs', 'award', '20', '#000', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_SELECT_TITLE; ?>
                    </h5>
                    
                    <?php if (!empty($availableAchievements)) { ?>
                    <form method="post" id="assignAchievementForm">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        
                        <div class="row">
                            <?php foreach ($availableAchievements as $achievement) { ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   name="achievement_id" 
                                                   value="<?php echo $achievement['id']; ?>" 
                                                   id="achievement_<?php echo $achievement['id']; ?>"
                                                   required>
                                            <label class="form-check-label w-100" for="achievement_<?php echo $achievement['id']; ?>">
                                                <div class="d-flex align-items-center mb-2">
                                                    <?php if ($achievement['image']) { ?>
                                                        <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo $achievement['image']; ?>" 
                                                            class="rounded me-2" 
                                                            style="width: 40px; height: 40px; object-fit: cover;"
                                                            alt="<?php echo html($achievement['name']); ?>">
                                                    <?php } else { ?>
                                                        <div class="rounded me-2 d-flex align-items-center justify-content-center" 
                                                            style="width: 40px; height: 40px; background: <?php echo $achievement['icon_color']; ?>;">
                                                            <?php 
                                                            $iconName = str_replace('bi-', '', $achievement['icon']);
                                                            echo bloggy_icon('bs', $iconName, '20', '#fff'); 
                                                            ?>
                                                        </div>
                                                    <?php } ?>
                                                    
                                                    <div>
                                                        <h6 class="mb-0"><?php echo html($achievement['name']); ?></h6>
                                                        <small class="text-muted"><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_MANUAL_BADGE; ?></small>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($achievement['description']) { ?>
                                                    <p class="small text-muted mb-2">
                                                        <?php echo html($achievement['description']); ?>
                                                    </p>
                                                <?php } ?>
                                                
                                                <?php if (!empty($achievement['conditions'])) { ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <?php echo bloggy_icon('bs', 'info-circle', '14', '#6C6C6C', 'me-1'); ?>
                                                            <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_CONDITIONS_PREFIX; ?>
                                                        </small>
                                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                                            <?php foreach ($achievement['conditions'] as $condition) { ?>
                                                                <?php 
                                                                $conditionText = '';
                                                                switch($condition['condition_type']) {
                                                                    case 'registration_days':
                                                                        $conditionText = LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_CONDITION_REGISTRATION_DAYS;
                                                                        break;
                                                                    case 'comments_count':
                                                                        $conditionText = LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_CONDITION_COMMENTS_COUNT;
                                                                        break;
                                                                    case 'posts_count':
                                                                        $conditionText = LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_CONDITION_POSTS_COUNT;
                                                                        break;
                                                                    case 'login_days':
                                                                        $conditionText = LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_CONDITION_LOGIN_DAYS;
                                                                        break;
                                                                }
                                                                ?>
                                                                <span class="badge bg-info small">
                                                                    <?php echo html($conditionText); ?> 
                                                                    <?php echo html($condition['operator']); ?> 
                                                                    <?php echo html($condition['value']); ?>
                                                                </span>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                <?php } else { ?>
                                                    <small class="text-muted">
                                                        <?php echo bloggy_icon('bs', 'info-circle', '14', '#6C6C6C', 'me-1'); ?>
                                                        <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_NO_CONDITIONS; ?>
                                                    </small>
                                                <?php } ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        
                        <div class="mt-4">
                            <div class="form-floating mb-3">
                                <textarea class="form-control" name="reason" id="reason" 
                                          placeholder="<?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_REASON_PLACEHOLDER; ?>" 
                                          style="height: 100px"></textarea>
                                <label for="reason">
                                    <?php echo bloggy_icon('bs', 'chat-text', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_REASON_LABEL; ?>
                                </label>
                                <div class="form-text">
                                    <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_REASON_HINT; ?>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="send_notification" 
                                               id="sendNotification" checked>
                                        <label class="form-check-label" for="sendNotification">
                                            <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_NOTIFY_LABEL; ?>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="window.history.back()">
                                        <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_CANCEL_BTN; ?>
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <?php echo bloggy_icon('bs', 'check-lg', '18', '#fff', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_SUBMIT_BTN; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php } else { ?>
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <?php echo bloggy_icon('bs', 'emoji-frown', '48', '#adb5bd'); ?>
                        </div>
                        <h5 class="text-muted"><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_NO_ACHIEVEMENTS_TITLE; ?></h5>
                        <p class="text-muted mb-3">
                            <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_NO_ACHIEVEMENTS_HINT; ?>
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="<?php echo ADMIN_URL; ?>/user-achievements/create" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'plus-lg', '18', '#fff', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_CREATE_ACHIEVEMENT_BTN; ?>
                            </a>
                            <a href="<?php echo ADMIN_URL; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'person', '18', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_EDIT_USER_BTN; ?>
                            </a>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <?php echo bloggy_icon('bs', 'lightbulb', '20', '#ffc107', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_TITLE; ?>
                    </h6>
                    
                    <div class="mb-3">
                        <h6 class="small text-muted mb-2"><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_MANUAL_TITLE; ?></h6>
                        <p class="small">
                            <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_MANUAL_DESC; ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="small text-muted mb-2"><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_AUTO_TITLE; ?></h6>
                        <p class="small">
                            <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_AUTO_DESC; ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="small text-muted mb-2"><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_WHEN_TITLE; ?></h6>
                        <ul class="small ps-3 mb-0">
                            <li><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_WHEN_ITEM1; ?></li>
                            <li><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_WHEN_ITEM2; ?></li>
                            <li><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_WHEN_ITEM3; ?></li>
                            <li><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_WHEN_ITEM4; ?></li>
                            <li><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_INFO_WHEN_ITEM5; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <?php echo bloggy_icon('bs', 'bar-chart', '20', '#007bff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_STATS_TITLE; ?>
                    </h6>
                    
                    <?php
                        $totalAchievements = $userModel->getAllAchievements(array('active' => true));
                        $manualAchievements = array_filter($totalAchievements, function($a) { return $a['type'] == 'manual'; });
                        $autoAchievements = array_filter($totalAchievements, function($a) { return $a['type'] == 'auto'; });
                    ?>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small"><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_STATS_MANUAL; ?></span>
                            <span class="small fw-bold"><?php echo count($manualAchievements); ?></span>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-warning" 
                                 style="width: <?php echo count($manualAchievements) > 0 ? '100%' : '0%'; ?>"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small"><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_STATS_AUTO; ?></span>
                            <span class="small fw-bold"><?php echo count($autoAchievements); ?></span>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-info" 
                                 style="width: <?php echo count($autoAchievements) > 0 ? '100%' : '0%'; ?>"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small"><?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_STATS_USER; ?></span>
                            <span class="small fw-bold"><?php echo count($userAchievements); ?></span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" 
                                 style="width: <?php echo count($totalAchievements) > 0 ? (count($userAchievements) / count($totalAchievements) * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="<?php echo ADMIN_URL; ?>/user-achievements" class="btn btn-sm btn-outline-primary">
                            <?php echo bloggy_icon('bs', 'trophy', '16', '#0d6efd', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_MANAGE_BTN; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const form = document.getElementById('assignAchievementForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedAchievement = form.querySelector('input[name="achievement_id"]:checked');
            if (!selectedAchievement) {
                e.preventDefault();
                alert('<?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_ALERT_NO_SELECTION; ?>');
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php echo LANG_TEMPLATE_USERS_QUICK_ASSIGN_ACHIEVEMENT_ASSIGNING_TEXT; ?>';
            
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 5000);
        });
    }
    
    const achievementCards = document.querySelectorAll('.form-check-input[type="radio"]');
    achievementCards.forEach(card => {
        card.addEventListener('change', function() {
            document.querySelectorAll('.card').forEach(c => {
                c.classList.remove('border-primary', 'shadow-sm');
            });
            
            if (this.checked) {
                const parentCard = this.closest('.card');
                parentCard.classList.add('border-primary', 'shadow-sm');
            }
        });

        if (card.checked) {
            const parentCard = card.closest('.card');
            parentCard.classList.add('border-primary', 'shadow-sm');
        }
    });
    
    achievementCards.forEach(card => {
        card.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('reason')?.focus();
            }
        });
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>