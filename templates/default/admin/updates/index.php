<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'arrow-repeat', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_UPDATES_CHECK_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_UPDATES_CHECK_BACK_BTN; ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <?php if ($update_result['has_update']) { ?>
                <div class="mb-4">
                    <div class="display-1 text-warning">
                        <?php echo bloggy_icon('bs', 'arrow-up-circle', '64', '#ffc107'); ?>
                    </div>
                </div>
                <h4 class="mb-3"><?php echo LANG_TEMPLATE_UPDATES_CHECK_UPDATE_AVAILABLE_TITLE; ?></h4>
                <p class="text-muted mb-2">
                    <?php echo LANG_TEMPLATE_UPDATES_CHECK_CURRENT_VERSION_LABEL; ?> <strong><?php echo html($current_version); ?></strong><br>
                    <?php echo LANG_TEMPLATE_UPDATES_CHECK_AVAILABLE_VERSION_LABEL; ?> <strong><?php echo html($update_result['latest_version']); ?></strong>
                </p>
                <?php if ($update_result['changelog']) { ?>
                    <div class="alert alert-info mt-4 text-start">
                        <strong><?php echo LANG_TEMPLATE_UPDATES_CHECK_CHANGELOG_TITLE; ?></strong>
                        <pre class="mt-2 mb-0 small"><?php echo html($update_result['changelog']); ?></pre>
                    </div>
                <?php } ?>
                <?php if ($update_result['download_url']) { ?>
                    <a href="<?php echo html($update_result['download_url']); ?>" class="btn btn-primary mt-3" target="_blank">
                        <?php echo bloggy_icon('bs', 'download', '16', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_UPDATES_CHECK_DOWNLOAD_BTN; ?>
                    </a>
                <?php } ?>
            <?php } else { ?>
                <div class="mb-4">
                    <div class="display-1 text-success">
                        <?php echo bloggy_icon('bs', 'check-circle', '64', '#198754'); ?>
                    </div>
                </div>
                <h4 class="mb-3"><?php echo html($update_result['message']); ?></h4>
                <p class="text-muted">
                    <?php echo LANG_TEMPLATE_UPDATES_CHECK_CURRENT_VERSION_LABEL; ?> <strong><?php echo html($current_version); ?></strong>
                </p>
                <?php if (!empty($current_version_name)) { ?>
                    <p class="text-muted small">
                        <?php echo html($current_version_name); ?>
                        <?php if (!empty($current_version_date)) { ?>
                            (<?php echo sprintf(LANG_TEMPLATE_UPDATES_CHECK_FROM_DATE, date('d.m.Y', strtotime($current_version_date))); ?>)
                        <?php } ?>
                    </p>
                <?php } ?>
            <?php } ?>
            
            <div class="mt-4 pt-3 border-top">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="bg-light rounded p-3">
                            <div class="d-flex justify-content-between align-items-center small text-muted">
                                <span><?php echo LANG_TEMPLATE_UPDATES_CHECK_CORE_VERSION_LABEL; ?></span>
                                <span class="fw-bold"><?php echo html($current_version); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center small text-muted mt-2">
                                <span><?php echo LANG_TEMPLATE_UPDATES_CHECK_BUILD_LABEL; ?></span>
                                <span class="fw-bold">#<?php echo (int)$current_build; ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center small text-muted mt-2">
                                <span><?php echo LANG_TEMPLATE_UPDATES_CHECK_RELEASE_DATE_LABEL; ?></span>
                                <span class="fw-bold">
                                    <?php echo !empty($current_version_date) ? date('d.m.Y', strtotime($current_version_date)) : '—'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="/admin/check-updates" class="btn btn-primary">
                    <?php echo bloggy_icon('bs', 'arrow-repeat', '16', '#ffffff', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_UPDATES_CHECK_AGAIN_BTN; ?>
                </a>
            </div>
        </div>
    </div>
</div>