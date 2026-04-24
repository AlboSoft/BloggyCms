<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'diagram-3', '24', '#000', 'me-2'); ?>
            <?php echo sprintf(LANG_TEMPLATE_USERS_MANAGE_GROUPS_TITLE, html($user['username'])); ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_BACK_BTN; ?>
        </a>
    </div>

    <form method="post">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label mb-3"><?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_SELECT_LABEL; ?></label>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                <?php if (!empty($allGroups)) { ?>
                                    <?php foreach ($allGroups as $group) { ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" 
                                                name="groups[]" value="<?php echo $group['id']; ?>"
                                                id="group_<?php echo $group['id']; ?>"
                                                <?php echo in_array($group['id'], $userGroups) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="group_<?php echo $group['id']; ?>">
                                                <strong><?php echo html($group['name']); ?></strong>
                                                <?php if ($group['is_default']) { ?>
                                                    <span class="badge bg-success ms-1"><?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_DEFAULT_BADGE; ?></span>
                                                <?php } ?>
                                                <?php if ($group['description']) { ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo html($group['description']); ?></small>
                                                <?php } ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                <?php } else { ?>
                                    <div class="text-center text-muted py-3">
                                        <?php echo bloggy_icon('bs', 'diagram-3', '32', '#6C6C6C', 'mb-2'); ?>
                                        <p class="mt-2 mb-0"><?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_NO_GROUPS_TITLE; ?></p>
                                        <a href="<?php echo ADMIN_URL; ?>/user-groups/create" class="btn btn-sm btn-outline-primary mt-2">
                                            <?php echo bloggy_icon('bs', 'plus', '14', '#0d6efd', 'me-1'); ?><?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_CREATE_BTN; ?>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-text mt-2">
                                <?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_HINT; ?>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '18', '#fff', 'me-1'); ?><?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_SAVE_BTN; ?>
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-outline-secondary">
                                <?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_CANCEL_BTN; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title border-bottom pb-2 mb-3"><?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_USER_INFO_TITLE; ?></h6>
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo $user['avatar']; ?>" 
                                     class="rounded-circle me-3" 
                                     style="width: 60px; height: 60px; object-fit: cover;"
                                     alt="<?php echo html($user['username']); ?>">
                            <?php } else { ?>
                                <div class="rounded-circle me-3 d-flex align-items-center justify-content-center bg-light" 
                                     style="width: 60px; height: 60px;">
                                    <?php echo bloggy_icon('bs', 'person', '24', '#6C6C6C'); ?>
                                </div>
                            <?php } ?>
                            <div>
                                <strong><?php echo html($user['username']); ?></strong>
                                <div class="text-muted small"><?php echo html($user['email']); ?></div>
                            </div>
                        </div>
                        
                        <div class="small text-muted">
                            <div class="mb-1">
                                <?php echo bloggy_icon('bs', 'circle-fill', '12', $user['status'] === 'active' ? '#198754' : '#dc3545', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_STATUS_LABEL; ?> <span class="fw-medium"><?php echo $user['status'] === 'active' ? LANG_TEMPLATE_USERS_MANAGE_GROUPS_STATUS_ACTIVE : LANG_TEMPLATE_USERS_MANAGE_GROUPS_STATUS_BANNED; ?></span>
                            </div>
                            <div>
                                <?php echo bloggy_icon('bs', 'calendar', '14', '#6C6C6C', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_REGISTERED_LABEL; ?> <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php echo LANG_TEMPLATE_USERS_MANAGE_GROUPS_SAVING_TEXT; ?>';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }, 5000);
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>