<?php
/**
 * Template Name: Редактирование профиля
 */
?>

<div class="tg-profile-edit">
    <div class="tg-container">
        
        <div class="tg-card">
            <div class="tg-card-header">
                <h1 class="tg-card-title">
                    <?php echo bloggy_icon('bs', 'pencil-square', '24', 'currentColor', 'tg-mr-2'); ?>
                    <?php echo LANG_TEMPLATE_PROFILE_EDIT_TITLE; ?>
                </h1>
                <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($user['username']); ?>" class="btn btn-outline-secondary btn-sm">
                    <?php echo bloggy_icon('bs', 'arrow-left', '14', 'currentColor', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_PROFILE_EDIT_BACK_BTN; ?>
                </a>
            </div>
            
            <div class="tg-card-body">
                <?php if (isset($_SESSION['error_message'])) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo html($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php } ?>
                
                <?php if (isset($_SESSION['success_message'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo html($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php } ?>

                <ul class="nav nav-tabs mb-4" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-info-tab" data-bs-toggle="tab" data-bs-target="#profile-info" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'person', '16', 'currentColor', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_INFO_TAB; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-password-tab" data-bs-toggle="tab" data-bs-target="#profile-password" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'key', '16', 'currentColor', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_PASSWORD_TAB; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-sessions-tab" data-bs-toggle="tab" data-bs-target="#profile-sessions" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'laptop', '16', 'currentColor', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_SESSIONS_TAB; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-additional-tab" data-bs-toggle="tab" data-bs-target="#profile-additional" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'gear', '16', 'currentColor', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_ADDITIONAL_TAB; ?>
                        </button>
                    </li>
                </ul>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/profile/update" enctype="multipart/form-data" id="profile-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action_type" id="action_type" value="update_profile">
                    
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="profile-info" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="tg-profile-avatar-section text-center">
                                        <div class="tg-profile-avatar-preview">
                                            <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                                                <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($user['avatar']); ?>" 
                                                     alt="<?php echo LANG_TEMPLATE_PROFILE_EDIT_AVATAR_ALT; ?>" 
                                                     id="avatar-preview"
                                                     class="avatar-preview-img">
                                            <?php } else { ?>
                                                <div class="avatar-placeholder avatar-preview-placeholder" id="avatar-preview">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <label class="btn btn-outline-primary btn-sm">
                                                <?php echo bloggy_icon('bs', 'cloud-upload', '14', 'currentColor', 'me-1'); ?>
                                                <?php echo LANG_TEMPLATE_PROFILE_EDIT_UPLOAD_AVATAR_BTN; ?>
                                                <input type="file" 
                                                       name="avatar" 
                                                       accept="image/jpeg,image/png,image/gif,image/webp"
                                                       class="d-none"
                                                       onchange="previewAvatar(this)">
                                            </label>
                                            <p class="text-muted small mt-2">
                                                <?php echo LANG_TEMPLATE_PROFILE_EDIT_AVATAR_HINT; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_USERNAME_LABEL; ?></label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="<?php echo html($user['username']); ?>" 
                                               disabled>
                                        <div class="form-text text-muted">
                                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_USERNAME_HINT; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_DISPLAY_NAME_LABEL; ?></label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="display_name" 
                                               value="<?php echo html($user['display_name'] ?? ''); ?>"
                                               placeholder="<?php echo LANG_TEMPLATE_PROFILE_EDIT_DISPLAY_NAME_PLACEHOLDER; ?>">
                                        <div class="form-text text-muted">
                                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_DISPLAY_NAME_HINT; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_EMAIL_LABEL; ?> <span class="text-danger">*</span></label>
                                        <input type="email" 
                                               class="form-control" 
                                               name="email" 
                                               value="<?php echo html($user['email']); ?>" 
                                               required>
                                        <div class="form-text text-muted">
                                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_EMAIL_HINT; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_WEBSITE_LABEL; ?></label>
                                        <input type="url" 
                                               class="form-control" 
                                               name="website" 
                                               value="<?php echo html($user['website'] ?? ''); ?>"
                                               placeholder="https://example.com">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_BIO_LABEL; ?></label>
                                        <textarea class="form-control" 
                                                  name="bio" 
                                                  rows="4" 
                                                  placeholder="<?php echo LANG_TEMPLATE_PROFILE_EDIT_BIO_PLACEHOLDER; ?>"><?php echo html($user['bio'] ?? ''); ?></textarea>
                                        <div class="form-text text-muted">
                                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_BIO_HINT; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($customFields)) { ?>
                                        <hr class="my-4">
                                        <h4 class="mb-3"><?php echo LANG_TEMPLATE_PROFILE_EDIT_ADDITIONAL_INFO_TITLE; ?></h4>
                                        
                                        <?php foreach ($customFields as $field) { 
                                            $config = json_decode($field['config'] ?? '{}', true);
                                            $isRequired = (bool)$field['is_required'];
                                            $requiredMark = $isRequired ? ' <span class="text-danger">*</span>' : '';
                                        ?>
                                            <div class="mb-3">
                                                <label class="form-label"><?php echo html($field['name']); ?><?php echo $requiredMark; ?></label>
                                                <?php 
                                                echo $fieldManager->renderFieldInput(
                                                    $field['type'],
                                                    $field['system_name'],
                                                    $field['value'],
                                                    $config,
                                                    'user',
                                                    $user['id']
                                                );
                                                ?>
                                                <?php if (!empty($field['description'])) { ?>
                                                    <div class="form-text text-muted"><?php echo html($field['description']); ?></div>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="profile-password" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <?php echo bloggy_icon('bs', 'info-circle', '16', 'currentColor', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_PROFILE_EDIT_PASSWORD_INFO; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_CURRENT_PASSWORD_LABEL; ?></label>
                                        <input type="password" 
                                               class="form-control" 
                                               name="current_password" 
                                               autocomplete="current-password">
                                        <div class="form-text text-muted">
                                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_CURRENT_PASSWORD_HINT; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_NEW_PASSWORD_LABEL; ?></label>
                                                <input type="password" 
                                                       class="form-control" 
                                                       name="new_password" 
                                                       id="new_password"
                                                       autocomplete="new-password">
                                                <div class="form-text text-muted">
                                                    <?php echo LANG_TEMPLATE_PROFILE_EDIT_NEW_PASSWORD_HINT; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_CONFIRM_PASSWORD_LABEL; ?></label>
                                                <input type="password" 
                                                       class="form-control" 
                                                       id="confirm_password" 
                                                       autocomplete="new-password">
                                                <div class="invalid-feedback">
                                                    <?php echo LANG_TEMPLATE_PROFILE_EDIT_PASSWORD_MISMATCH_ERROR; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="profile-sessions" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <?php echo bloggy_icon('bs', 'info-circle', '16', 'currentColor', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_PROFILE_EDIT_SESSIONS_INFO; ?>
                                    </div>
                                    
                                    <div class="sessions-list" id="sessions-list">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden"><?php echo LANG_TEMPLATE_PROFILE_EDIT_LOADING_TEXT; ?></span>
                                            </div>
                                            <p class="mt-2"><?php echo LANG_TEMPLATE_PROFILE_EDIT_LOADING_SESSIONS; ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="terminate-all-sessions">
                                            <?php echo bloggy_icon('bs', 'x-circle', '14', 'currentColor', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ALL_BTN; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="profile-additional" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-danger">
                                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', 'currentColor', 'me-1'); ?>
                                        <strong><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_WARNING_TITLE; ?></strong> 
                                        <?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_WARNING_TEXT; ?>
                                    </div>
                                    
                                    <div class="card border-danger">
                                        <div class="card-header bg-danger text-white">
                                            <strong><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_ACCOUNT_TITLE; ?></strong>
                                        </div>
                                        <div class="card-body">
                                            <p><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_DESCRIPTION; ?></p>
                                            <ul>
                                                <li><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_ITEM_1; ?></li>
                                                <li><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_ITEM_2; ?></li>
                                                <li><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_ITEM_3; ?></li>
                                                <li><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_ITEM_4; ?></li>
                                            </ul>
                                            <p class="text-muted small">
                                                <strong><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_NOTE_TITLE; ?></strong> 
                                                <?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_NOTE_TEXT; ?>
                                            </p>
                                            
                                            <div class="mt-4">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="confirm-delete">
                                                    <label class="form-check-label" for="confirm-delete">
                                                        <?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_CONFIRM_LABEL; ?>
                                                    </label>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label"><?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_PASSWORD_LABEL; ?></label>
                                                    <input type="password" 
                                                           class="form-control" 
                                                           id="delete-password" 
                                                           placeholder="<?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_PASSWORD_PLACEHOLDER; ?>">
                                                </div>
                                                
                                                <button type="button" class="btn btn-danger" id="delete-account-btn" disabled>
                                                    <?php echo bloggy_icon('bs', 'trash', '16', 'currentColor', 'me-1'); ?>
                                                    <?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_ACCOUNT_BTN; ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tg-form-actions mt-4">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <?php echo bloggy_icon('bs', 'check-lg', '16', 'currentColor', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_SAVE_BTN; ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($user['username']); ?>" class="btn btn-outline-secondary">
                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_CANCEL_BTN; ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.id = 'avatar-preview';
                img.className = 'avatar-preview-img';
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submit-btn');
    const actionType = document.getElementById('action_type');
    
    function validatePassword() {
        if (newPassword && confirmPassword) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                return false;
            } else {
                confirmPassword.classList.remove('is-invalid');
                return true;
            }
        }
        return true;
    }
    
    if (newPassword && confirmPassword) {
        newPassword.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePassword);
        
        submitBtn.addEventListener('click', function(e) {
            if (newPassword.value && newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                confirmPassword.classList.add('is-invalid');
                alert('<?php echo LANG_TEMPLATE_PROFILE_EDIT_PASSWORD_MISMATCH_ALERT; ?>');
            }
        });
    }
    
    function loadSessions() {
        fetch('<?php echo BASE_URL; ?>/profile/sessions')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderSessions(data.sessions);
                }
            })
            .catch(error => {
                document.getElementById('sessions-list').innerHTML = `
                    <div class="alert alert-warning">
                        <?php echo LANG_TEMPLATE_PROFILE_EDIT_SESSIONS_LOAD_ERROR; ?>
                    </div>
                `;
            });
    }
    
    function renderSessions(sessions) {
        const container = document.getElementById('sessions-list');
        if (!sessions || sessions.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-muted"><?php echo LANG_TEMPLATE_PROFILE_EDIT_NO_SESSIONS; ?></div>';
            return;
        }
        
        let html = '';
        sessions.forEach(session => {
            const isCurrent = session.is_current;
            html += `
                <div class="session-item ${isCurrent ? 'session-current' : ''}">
                    <div class="session-info">
                        <div class="session-icon">
                            <?php echo bloggy_icon('bs', 'laptop', '20', 'var(--tg-text-secondary)'); ?>
                        </div>
                        <div class="session-details">
                            <div class="session-device">
                                ${session.device || '<?php echo LANG_TEMPLATE_PROFILE_EDIT_UNKNOWN_DEVICE; ?>'}
                                ${isCurrent ? '<span class="current-badge ms-2"><?php echo LANG_TEMPLATE_PROFILE_EDIT_CURRENT_BADGE; ?></span>' : ''}
                            </div>
                            <div class="session-meta">
                                IP: ${session.ip} • <?php echo LANG_TEMPLATE_PROFILE_EDIT_LAST_ACTIVITY_LABEL; ?> ${session.last_activity}
                            </div>
                        </div>
                    </div>
                    ${!isCurrent ? `
                        <button type="button" class="btn btn-sm btn-outline-danger terminate-session" data-session-id="${session.id}">
                            <?php echo LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_BTN; ?>
                        </button>
                    ` : ''}
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        document.querySelectorAll('.terminate-session').forEach(btn => {
            btn.addEventListener('click', function() {
                const sessionId = this.dataset.sessionId;
                if (confirm('<?php echo LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_CONFIRM; ?>')) {
                    terminateSession(sessionId);
                }
            });
        });
    }
    
    function terminateSession(sessionId) {
        fetch('<?php echo BASE_URL; ?>/profile/terminate-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ session_id: sessionId, csrf_token: '<?php echo $csrf_token; ?>' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadSessions();
            } else {
                alert(data.message || '<?php echo LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ERROR; ?>');
            }
        })
        .catch(error => {
            alert('<?php echo LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ERROR; ?>');
        });
    }
    
    document.getElementById('terminate-all-sessions')?.addEventListener('click', function() {
        if (confirm('<?php echo LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ALL_CONFIRM; ?>')) {
            fetch('<?php echo BASE_URL; ?>/profile/terminate-all-sessions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ csrf_token: '<?php echo $csrf_token; ?>' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadSessions();
                } else {
                    alert(data.message || '<?php echo LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ALL_ERROR; ?>');
                }
            })
            .catch(error => {
                alert('<?php echo LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ALL_ERROR; ?>');
            });
        }
    });
    
    const deleteCheckbox = document.getElementById('confirm-delete');
    const deletePassword = document.getElementById('delete-password');
    const deleteBtn = document.getElementById('delete-account-btn');
    
    if (deleteCheckbox && deletePassword && deleteBtn) {
        function checkDeleteForm() {
            deleteBtn.disabled = !(deleteCheckbox.checked && deletePassword.value.length > 0);
        }
        
        deleteCheckbox.addEventListener('change', checkDeleteForm);
        deletePassword.addEventListener('input', checkDeleteForm);
        
        deleteBtn.addEventListener('click', function() {
            if (confirm('<?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_FINAL_CONFIRM; ?>')) {
                fetch('<?php echo BASE_URL; ?>/profile/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        password: deletePassword.value, 
                        csrf_token: '<?php echo $csrf_token; ?>' 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_SUCCESS; ?>');
                        window.location.href = '<?php echo BASE_URL; ?>';
                    } else {
                        alert(data.message || '<?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_ERROR; ?>');
                    }
                })
                .catch(error => {
                    alert('<?php echo LANG_TEMPLATE_PROFILE_EDIT_DELETE_ERROR; ?>');
                });
            }
        });
    }
    
    const sessionsTab = document.getElementById('profile-sessions-tab');
    if (sessionsTab) {
        sessionsTab.addEventListener('shown.bs.tab', function() {
            loadSessions();
        });
    }
    
    loadSessions();
});
</script>