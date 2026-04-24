<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'shield-lock', '24', '#000', 'me-2'); ?>
            <?php echo sprintf(LANG_TEMPLATE_USERS_GROUPS_PERMISSIONS_TITLE, html($group['name'])); ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/user-groups" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_GROUPS_PERMISSIONS_BACK_BTN; ?>
        </a>
    </div>

    <form method="post">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <?php if (empty($allPermissions)) { ?>
                            <div class="text-center py-4">
                                <?php echo bloggy_icon('bs', 'shield-slash', '48', '#6C6C6C', 'mb-3'); ?>
                                <h5 class="text-muted mt-3"><?php echo LANG_TEMPLATE_USERS_GROUPS_PERMISSIONS_NO_PERMISSIONS_TITLE; ?></h5>
                                <p class="text-muted"><?php echo LANG_TEMPLATE_USERS_GROUPS_PERMISSIONS_NO_PERMISSIONS_HINT; ?></p>
                            </div>
                        <?php } else { ?>
                            <?php foreach ($allPermissions as $controller => $permissions) { ?>
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="mb-3 text-uppercase text-primary fw-bold">
                                    <?php echo bloggy_icon('bs', 'folder', '16', '#0d6efd', 'me-2'); ?>
                                    <?php echo html(ucfirst($controller)); ?>
                                </h6>
                                
                                <div class="row">
                                    <?php foreach ($permissions as $key => $permission) { ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body p-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permissions[]" value="<?php echo $key; ?>"
                                                           id="perm_<?php echo $key; ?>"
                                                           <?php echo in_array($key, $groupPermissions) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label fw-bold" for="perm_<?php echo $key; ?>">
                                                        <?php echo html($permission['title']); ?>
                                                    </label>
                                                </div>
                                                <?php if (!empty($permission['description'])) { ?>
                                                <p class="small text-muted mb-0">
                                                    <?php echo html($permission['description']); ?>
                                                </p>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                        <?php } ?>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '18', '#fff', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_USERS_GROUPS_PERMISSIONS_SAVE_BTN; ?>
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/user-groups" class="btn btn-outline-secondary">
                                <?php echo LANG_TEMPLATE_USERS_GROUPS_PERMISSIONS_CANCEL_BTN; ?>
                            </a>
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
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php echo LANG_TEMPLATE_USERS_GROUPS_PERMISSIONS_SAVING_TEXT; ?>';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }, 5000);
    });

    function setupSectionSelectors() {
        document.querySelectorAll('.permissions-section').forEach(section => {
            const checkboxes = section.querySelectorAll('input[type="checkbox"]');
            const selectAllBtn = section.querySelector('.select-all-btn');
            
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                });
            }
        });
    }
    
    setupSectionSelectors();
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>