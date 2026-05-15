<?php
$fieldModel = new FieldModel($this->db);
$customFields = $fieldModel->getActiveByEntityType('user');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'person-gear', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_USERS_EDIT_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/users" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_EDIT_BACK_BTN; ?>
        </a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_USERS_EDIT_MAIN_INFO_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <?php echo LANG_TEMPLATE_USERS_EDIT_USERNAME_LABEL; ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="username" 
                                           value="<?php echo html($user['username'] ?? ''); ?>" 
                                           required maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <?php echo LANG_TEMPLATE_USERS_EDIT_EMAIL_LABEL; ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo html($user['email'] ?? ''); ?>" 
                                           required maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="change_password" name="change_password">
                                <label class="form-check-label" for="change_password">
                                    <?php echo LANG_TEMPLATE_USERS_EDIT_CHANGE_PASSWORD_LABEL; ?>
                                </label>
                            </div>
                        </div>

                        <div class="row password-fields" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_NEW_PASSWORD_LABEL; ?></label>
                                    <input type="password" class="form-control" name="password">
                                    <div class="form-text"><?php echo LANG_TEMPLATE_USERS_EDIT_PASSWORD_HINT; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_CONFIRM_PASSWORD_LABEL; ?></label>
                                    <input type="password" class="form-control" name="password_confirm">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_DISPLAY_NAME_LABEL; ?></label>
                            <input type="text" class="form-control" name="display_name" 
                                   value="<?php echo html($user['display_name'] ?? ''); ?>" 
                                   maxlength="100">
                            <div class="form-text"><?php echo LANG_TEMPLATE_USERS_EDIT_DISPLAY_NAME_HINT; ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_BIO_LABEL; ?></label>
                            <textarea class="form-control" name="bio" rows="3" 
                                      maxlength="500"><?php echo html($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_WEBSITE_LABEL; ?></label>
                            <input type="url" class="form-control" name="website" 
                                   value="<?php echo html($user['website'] ?? ''); ?>" 
                                   maxlength="255">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_USERS_EDIT_AVATAR_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_UPLOAD_AVATAR_LABEL; ?></label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                            <div class="form-text">
                                <?php echo LANG_TEMPLATE_USERS_EDIT_AVATAR_HINT; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                        <div class="mt-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_CURRENT_AVATAR_LABEL; ?></label>
                            <div>
                                <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo $user['avatar']; ?>" 
                                     class="rounded" style="max-width: 150px; max-height: 150px;">
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_avatar" name="remove_avatar">
                                        <label class="form-check-label" for="remove_avatar">
                                            <?php echo LANG_TEMPLATE_USERS_EDIT_REMOVE_AVATAR_LABEL; ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php if (!empty($customFields)) { ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_USERS_EDIT_CUSTOM_FIELDS_TITLE; ?></h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($customFields as $field) { ?>
                                <div class="mb-3">
                                    <label class="form-label small">
                                        <?php echo html($field['name']); ?>
                                        <?php if ($field['is_required']) { ?>
                                            <span class="text-danger">*</span>
                                        <?php } ?>
                                    </label>
                                    
                                    <?php 
                                    $currentValue = $fieldModel->getFieldValue('user', $user['id'], $field['system_name']);
                                    ?>
                                    
                                    <?php echo $fieldModel->renderFieldInput($field, $currentValue, 'user', $user['id']); ?>
                                    
                                    <?php if (!empty($field['description'])) { ?>
                                        <div class="form-text small"><?php echo html($field['description']); ?></div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center text-muted">
                            <?php echo bloggy_icon('bs', 'input-cursor-text', '32', '#6C6C6C', 'mb-2'); ?>
                            <p class="mt-2 mb-0"><?php echo LANG_TEMPLATE_USERS_EDIT_NO_CUSTOM_FIELDS_TITLE; ?></p>
                            <small><?php echo LANG_TEMPLATE_USERS_EDIT_NO_CUSTOM_FIELDS_HINT; ?></small>
                        </div>
                    </div>
                <?php } ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_USERS_EDIT_GROUPS_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <?php
                            $userModel = new UserModel($db);
                            $allGroups = $userModel->getAllGroups();
                            $userGroups = $userModel->getUserGroups($user['id'] ?? 0);
                        ?>
                        <?php if (!empty($allGroups)) { ?>
                            <div class="mb-3">
                                <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_GROUPS_SELECT_LABEL; ?></label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($allGroups as $group) { ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            name="groups[]" value="<?php echo $group['id']; ?>"
                                            id="group_<?php echo $group['id']; ?>"
                                            <?php echo in_array($group['id'], $userGroups) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="group_<?php echo $group['id']; ?>">
                                            <strong><?php echo html($group['name']); ?></strong>
                                            <?php if ($group['is_default']) { ?>
                                                <span class="badge bg-success ms-1"><?php echo LANG_TEMPLATE_USERS_EDIT_GROUPS_DEFAULT_BADGE; ?></span>
                                            <?php } ?>
                                            <?php if ($group['description']) { ?>
                                                <br>
                                                <small class="text-muted"><?php echo html($group['description']); ?></small>
                                            <?php } ?>
                                        </label>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="form-text">
                                    <?php echo LANG_TEMPLATE_USERS_EDIT_GROUPS_HINT; ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="text-center text-muted py-3">
                                <?php echo bloggy_icon('bs', 'diagram-3', '32', '#6C6C6C', 'mb-2'); ?>
                                <p class="mt-2 mb-0"><?php echo LANG_TEMPLATE_USERS_EDIT_GROUPS_EMPTY_TITLE; ?></p>
                                <a href="<?php echo ADMIN_URL; ?>/user-groups/create" class="btn btn-sm btn-outline-primary mt-2">
                                    <?php echo bloggy_icon('bs', 'plus', '14', '#0d6efd', 'me-1'); ?><?php echo LANG_TEMPLATE_USERS_EDIT_GROUPS_CREATE_BTN; ?>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'trophy', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_USERS_EDIT_ACHIEVEMENTS_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                            $userModel = new UserModel($db);
                            $allAchievements = $userModel->getAllAchievements(array('active' => true));
                            $userAchievements = array();
                            if (isset($user['id']) && $user['id']) {
                                $userAchievementIds = $userModel->getUserUnlockedAchievements($user['id']);
                                $userAchievements = array_column($userAchievementIds, 'id');
                            }
                        ?>
                        
                        <?php if (!empty($allAchievements)) { ?>
                            <div class="achievements-list">
                                <?php foreach ($allAchievements as $achievement) { 
                                    $isChecked = in_array($achievement['id'], $userAchievements);
                                    $isManual = $achievement['type'] == 'manual';
                                ?>
                                    <div class="achievement-item <?php echo $isChecked ? 'checked' : ''; ?>">
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            name="achievements[]" 
                                            value="<?php echo $achievement['id']; ?>"
                                            id="achievement_<?php echo $achievement['id']; ?>"
                                            <?php echo $isChecked ? 'checked' : ''; ?>
                                            <?php echo !$isManual ? 'disabled' : ''; ?>>
                                        
                                        <label class="achievement-label" for="achievement_<?php echo $achievement['id']; ?>">
                                            <?php if ($achievement['image']) { ?>
                                                <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo $achievement['image']; ?>" 
                                                    class="achievement-icon"
                                                    alt="<?php echo html($achievement['name']); ?>">
                                            <?php } else { ?>
                                                <div class="achievement-icon" style="background: <?php echo $achievement['icon_color']; ?>;">
                                                    <?php 
                                                    $iconName = str_replace('bi-', '', $achievement['icon']);
                                                    echo bloggy_icon('bs', $iconName, '14', '#fff'); 
                                                    ?>
                                                </div>
                                            <?php } ?>
                                            
                                            <span class="achievement-name"><?php echo html($achievement['name']); ?></span>
                                            
                                            <?php if (!$isManual) { ?>
                                                <span class="badge bg-info"><?php echo LANG_TEMPLATE_USERS_EDIT_ACHIEVEMENTS_AUTO_BADGE; ?></span>
                                            <?php } else { ?>
                                                <span class="badge bg-warning"><?php echo LANG_TEMPLATE_USERS_EDIT_ACHIEVEMENTS_MANUAL_BADGE; ?></span>
                                            <?php } ?>
                                        </label>
                                        
                                        <?php if ($isManual && $isChecked) { ?>
                                            <button type="button" 
                                                class="unassign-btn"
                                                data-user-id="<?php echo $user['id']; ?>"
                                                data-achievement-id="<?php echo $achievement['id']; ?>"
                                                data-achievement-name="<?php echo htmlspecialchars($achievement['name']); ?>"
                                                title="<?php echo LANG_TEMPLATE_USERS_EDIT_ACHIEVEMENTS_UNASSIGN_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'trash', '14', '#dc3545'); ?>
                                            </button>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-text mt-3">
                                <?php echo LANG_TEMPLATE_USERS_EDIT_ACHIEVEMENTS_HINT; ?>
                            </div>
                        <?php } else { ?>
                            <div class="text-center text-muted py-3">
                                <?php echo bloggy_icon('bs', 'trophy', '32', '#6C6C6C', 'mb-2'); ?>
                                <p class="mt-2 mb-0"><?php echo LANG_TEMPLATE_USERS_EDIT_ACHIEVEMENTS_EMPTY_TITLE; ?></p>
                                <a href="<?php echo ADMIN_URL; ?>/user-achievements/create" class="btn btn-sm btn-outline-primary mt-2">
                                    <?php echo bloggy_icon('bs', 'plus', '14', '#0d6efd', 'me-1'); ?><?php echo LANG_TEMPLATE_USERS_EDIT_ACHIEVEMENTS_CREATE_BTN; ?>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_USERS_EDIT_USER_SETTINGS_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <?php if ($user['id'] == 1) { ?>
                                <div class="alert alert-warning mb-0">
                                    <?php echo bloggy_icon('bs', 'shield-lock', '16', '#856404', 'me-2'); ?>
                                    <?php echo LANG_TEMPLATE_USERS_EDIT_MAIN_ADMIN_WARNING; ?>
                                </div>
                                <input type="hidden" name="is_admin" value="1">
                            <?php } else { ?>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1" <?php echo ($user['is_admin'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_admin">
                                        <?php echo bloggy_icon('bs', 'shield-lock', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_USERS_EDIT_IS_ADMIN_LABEL; ?>
                                    </label>
                                    <div class="form-text"><?php echo LANG_TEMPLATE_USERS_EDIT_IS_ADMIN_HINT; ?></div>
                                </div>
                            <?php } ?>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_EDIT_STATUS_LABEL; ?></label>
                            <select class="form-select" name="status" required>
                                <option value="active" <?php echo ($user['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_EDIT_STATUS_ACTIVE; ?></option>
                                <option value="banned" <?php echo ($user['status'] ?? 'active') === 'banned' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_EDIT_STATUS_BANNED; ?></option>
                            </select>
                        </div>

                        <div class="text-muted small">
                            <div><strong>ID:</strong> <?php echo $user['id']; ?></div>
                            <div><strong><?php echo LANG_TEMPLATE_USERS_EDIT_REGISTERED_LABEL; ?></strong> <?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '18', '#fff', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_EDIT_SAVE_BTN; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/users" class="btn btn-outline-secondary">
                        <?php echo LANG_TEMPLATE_USERS_EDIT_CANCEL_BTN; ?>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
    add_admin_css('templates/default/admin/assets/css/controllers/achievement-user.css');
    add_admin_js('templates/default/admin/assets/js/controllers/user-achievements.js');
?>