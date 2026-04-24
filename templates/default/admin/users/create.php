<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'person-plus', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_USERS_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/users" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_CREATE_BACK_BTN; ?>
        </a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <?php echo LANG_TEMPLATE_USERS_CREATE_USERNAME_LABEL; ?>
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
                                        <?php echo LANG_TEMPLATE_USERS_CREATE_EMAIL_LABEL; ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" name="email" 
                                        value="<?php echo html($user['email'] ?? ''); ?>" 
                                        required maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <?php echo LANG_TEMPLATE_USERS_CREATE_PASSWORD_LABEL; ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" name="password" required>
                                    <div class="form-text"><?php echo LANG_TEMPLATE_USERS_CREATE_PASSWORD_HINT; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <?php echo LANG_TEMPLATE_USERS_CREATE_PASSWORD_CONFIRM_LABEL; ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" name="password_confirm" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_CREATE_DISPLAY_NAME_LABEL; ?></label>
                            <input type="text" class="form-control" name="display_name" 
                                value="<?php echo html($user['display_name'] ?? ''); ?>" 
                                maxlength="100">
                            <div class="form-text"><?php echo LANG_TEMPLATE_USERS_CREATE_DISPLAY_NAME_HINT; ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_CREATE_BIO_LABEL; ?></label>
                            <textarea class="form-control" name="bio" rows="3" 
                                    maxlength="500"><?php echo html($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_CREATE_WEBSITE_LABEL; ?></label>
                            <input type="url" class="form-control" name="website" 
                                value="<?php echo html($user['website'] ?? ''); ?>" 
                                maxlength="255">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_CREATE_AVATAR_LABEL; ?></label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php
                    $fieldModel = new FieldModel($db);
                    $customFields = $fieldModel->getActiveByEntityType('user');
                ?>

                <?php if (!empty($customFields)) { ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_USERS_CREATE_CUSTOM_FIELDS_TITLE; ?></h5>
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
                                    $config = json_decode($field['config'] ?? '{}', true);
                                    $value = $config['default_value'] ?? '';
                                    ?>
                                    
                                    <?php echo $fieldModel->renderFieldInput($field, $value, 'user', 0); ?>
                                    
                                    <?php if (!empty($field['description'])) { ?>
                                        <div class="form-text small"><?php echo html($field['description']); ?></div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_USERS_CREATE_GROUPS_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <?php
                            $userModel = new UserModel($db);
                            $allGroups = $userModel->getAllGroups();
                        ?>
                        <?php if (!empty($allGroups)) { ?>
                            <div class="mb-3">
                                <label class="form-label"><?php echo LANG_TEMPLATE_USERS_CREATE_GROUPS_SELECT_LABEL; ?></label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($allGroups as $group) { ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            name="groups[]" value="<?php echo $group['id']; ?>"
                                            id="group_<?php echo $group['id']; ?>"
                                            <?php echo ($group['is_default'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="group_<?php echo $group['id']; ?>">
                                            <strong><?php echo html($group['name']); ?></strong>
                                            <?php if ($group['is_default']) { ?>
                                                <span class="badge bg-success ms-1"><?php echo LANG_TEMPLATE_USERS_CREATE_GROUPS_DEFAULT_BADGE; ?></span>
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
                                    <?php echo LANG_TEMPLATE_USERS_CREATE_GROUPS_HINT; ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="text-center text-muted py-3">
                                <?php echo bloggy_icon('bs', 'diagram-3', '32', '#6C6C6C', 'mb-2'); ?>
                                <p class="mt-2 mb-0"><?php echo LANG_TEMPLATE_USERS_CREATE_GROUPS_EMPTY_TITLE; ?></p>
                                <a href="<?php echo ADMIN_URL; ?>/user-groups/create" class="btn btn-sm btn-outline-primary mt-2">
                                    <?php echo bloggy_icon('bs', 'plus', '14', '#0d6efd', 'me-1'); ?><?php echo LANG_TEMPLATE_USERS_CREATE_GROUPS_CREATE_BTN; ?>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'trophy', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_USERS_CREATE_ACHIEVEMENTS_TITLE; ?>
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
                            <div class="mb-3">
                                <label class="form-label"><?php echo LANG_TEMPLATE_USERS_CREATE_ACHIEVEMENTS_SELECT_LABEL; ?></label>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($allAchievements as $achievement) { ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" 
                                                name="achievements[]" value="<?php echo $achievement['id']; ?>"
                                                id="achievement_<?php echo $achievement['id']; ?>"
                                                <?php echo in_array($achievement['id'], $userAchievements) ? 'checked' : ''; ?>
                                                <?php echo $achievement['type'] == 'auto' ? 'disabled title="' . LANG_TEMPLATE_USERS_CREATE_ACHIEVEMENTS_AUTO_DISABLED_TITLE . '"' : ''; ?>>
                                            <label class="form-check-label d-flex align-items-center" for="achievement_<?php echo $achievement['id']; ?>">
                                                <?php if ($achievement['image']) { ?>
                                                    <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo $achievement['image']; ?>" 
                                                        class="rounded me-2" 
                                                        style="width: 24px; height: 24px; object-fit: cover;"
                                                        alt="<?php echo html($achievement['name']); ?>">
                                                <?php } else { ?>
                                                    <div class="rounded me-2 d-flex align-items-center justify-content-center" 
                                                        style="width: 24px; height: 24px; background: <?php echo $achievement['icon_color']; ?>;">
                                                        <?php 
                                                        $iconName = str_replace('bi-', '', $achievement['icon']);
                                                        echo bloggy_icon('bs', $iconName, '12', '#fff'); 
                                                        ?>
                                                    </div>
                                                <?php } ?>
                                                
                                                <div>
                                                    <strong><?php echo html($achievement['name']); ?></strong>
                                                    <?php if ($achievement['type'] == 'auto') { ?>
                                                        <span class="badge bg-info ms-1 small"><?php echo LANG_TEMPLATE_USERS_CREATE_ACHIEVEMENTS_AUTO_BADGE; ?></span>
                                                    <?php } else { ?>
                                                        <span class="badge bg-warning ms-1 small"><?php echo LANG_TEMPLATE_USERS_CREATE_ACHIEVEMENTS_MANUAL_BADGE; ?></span>
                                                    <?php } ?>
                                                    <?php if ($achievement['description']) { ?>
                                                        <br>
                                                        <small class="text-muted"><?php echo html($achievement['description']); ?></small>
                                                    <?php } ?>
                                                </div>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-text">
                                    <?php echo LANG_TEMPLATE_USERS_CREATE_ACHIEVEMENTS_HINT; ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="text-center text-muted py-3">
                                <?php echo bloggy_icon('bs', 'trophy', '32', '#6C6C6C', 'mb-2'); ?>
                                <p class="mt-2 mb-0"><?php echo LANG_TEMPLATE_USERS_CREATE_ACHIEVEMENTS_EMPTY_TITLE; ?></p>
                                <a href="<?php echo ADMIN_URL; ?>/user-achievements/create" class="btn btn-sm btn-outline-primary mt-2">
                                    <?php echo bloggy_icon('bs', 'plus', '14', '#0d6efd', 'me-1'); ?><?php echo LANG_TEMPLATE_USERS_CREATE_ACHIEVEMENTS_CREATE_BTN; ?>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_CREATE_ROLE_LABEL; ?></label>
                            <select class="form-select" name="role" required>
                                <option value="user"><?php echo LANG_TEMPLATE_USERS_CREATE_ROLE_USER; ?></option>
                                <option value="admin"><?php echo LANG_TEMPLATE_USERS_CREATE_ROLE_ADMIN; ?></option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_CREATE_STATUS_LABEL; ?></label>
                            <select class="form-select" name="status" required>
                                <option value="active"><?php echo LANG_TEMPLATE_USERS_CREATE_STATUS_ACTIVE; ?></option>
                                <option value="banned"><?php echo LANG_TEMPLATE_USERS_CREATE_STATUS_BANNED; ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '18', '#fff', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_CREATE_SUBMIT_BTN; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmInput = document.querySelector('input[name="password_confirm"]');
    const form = document.querySelector('form');
    
    function validatePasswords() {
        if (passwordInput.value !== confirmInput.value) {
            confirmInput.setCustomValidity('<?php echo LANG_TEMPLATE_USERS_CREATE_VALIDATION_PASSWORDS_MISMATCH; ?>');
        } else {
            confirmInput.setCustomValidity('');
        }
    }
    
    passwordInput.addEventListener('input', validatePasswords);
    confirmInput.addEventListener('input', validatePasswords);
    passwordInput.addEventListener('input', function() {
        if (this.value.length > 0 && this.value.length < 6) {
            this.setCustomValidity('<?php echo LANG_TEMPLATE_USERS_CREATE_VALIDATION_PASSWORD_MIN_LENGTH; ?>');
        } else {
            this.setCustomValidity('');
        }
    });
    
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php echo LANG_TEMPLATE_USERS_CREATE_SUBMIT_CREATING_TEXT; ?>';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }, 5000);
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>