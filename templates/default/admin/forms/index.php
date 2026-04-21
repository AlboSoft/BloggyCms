<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'ui-checks', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_FORMS_INDEX_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/forms/create" class="btn btn-primary">
            <?php echo bloggy_icon('bs', 'plus-circle', '16', '#fff', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_FORMS_INDEX_CREATE_BTN; ?>
        </a>
    </div>

    <?php
    $stats = $formModel->getStatistics();
    ?>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'ui-checks', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_forms']; ?></h3>
                            <small><?php echo LANG_TEMPLATE_FORMS_INDEX_STAT_TOTAL_FORMS; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'check-circle', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['active_forms']; ?></h3>
                            <small><?php echo LANG_TEMPLATE_FORMS_INDEX_STAT_ACTIVE_FORMS; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'send', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_submissions']; ?></h3>
                            <small><?php echo LANG_TEMPLATE_FORMS_INDEX_STAT_TOTAL_SUBMISSIONS; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'envelope', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['unread_submissions']; ?></h3>
                            <small><?php echo LANG_TEMPLATE_FORMS_INDEX_STAT_NEW_SUBMISSIONS; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($forms)) { ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <?php echo bloggy_icon('bs', 'ui-checks', '48', '#6C6C6C', 'mb-3'); ?>
                <h5 class="text-muted"><?php echo LANG_TEMPLATE_FORMS_INDEX_NO_FORMS_TITLE; ?></h5>
                <p class="text-muted mb-4"><?php echo LANG_TEMPLATE_FORMS_INDEX_NO_FORMS_TEXT; ?></p>
                <a href="<?php echo ADMIN_URL; ?>/forms/create" class="btn btn-primary">
                    <?php echo bloggy_icon('bs', 'plus-circle', '16', '#fff', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_INDEX_CREATE_BTN; ?>
                </a>
            </div>
        </div>
    <?php } else { ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><?php echo LANG_TEMPLATE_FORMS_INDEX_TABLE_ID; ?></th>
                                <th><?php echo LANG_TEMPLATE_FORMS_INDEX_TABLE_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_FORMS_INDEX_TABLE_SLUG; ?></th>
                                <th><?php echo LANG_TEMPLATE_FORMS_INDEX_TABLE_FIELDS; ?></th>
                                <th><?php echo LANG_TEMPLATE_FORMS_INDEX_TABLE_SUBMISSIONS; ?></th>
                                <th><?php echo LANG_TEMPLATE_FORMS_INDEX_TABLE_STATUS; ?></th>
                                <th><?php echo LANG_TEMPLATE_FORMS_INDEX_TABLE_DATE; ?></th>
                                <th class="end"><?php echo LANG_TEMPLATE_FORMS_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($forms as $form) { 
                                $fieldsCount = count($form['structure'] ?? []);
                                $submissionsCount = $formModel->getSubmissionsCount($form['id']);
                            ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo html($form['id']); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo html($form['name']); ?></strong>
                                    <?php if (!empty($form['description'])) { ?>
                                        <small class="d-block text-muted"><?php echo html(mb_substr($form['description'], 0, 50)); ?>...</small>
                                    <?php } ?>
                                </td>
                                <td>
                                    <code><?php echo html($form['slug']); ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $fieldsCount; ?> <?php echo LANG_TEMPLATE_FORMS_INDEX_FIELDS_COUNT; ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/forms/show/<?php echo $form['id']; ?>" 
                                    class="badge bg-info text-decoration-none">
                                        <?php echo $submissionsCount; ?> <?php echo LANG_TEMPLATE_FORMS_INDEX_SUBMISSIONS_COUNT; ?>
                                    </a>
                                </td>
                                <td>
                                    <form method="POST" action="<?php echo ADMIN_URL; ?>/forms/toggle-status/<?php echo $form['id']; ?>" 
                                        class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-<?php echo $form['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $form['status'] === 'active' ? LANG_TEMPLATE_FORMS_INDEX_STATUS_ACTIVE : LANG_TEMPLATE_FORMS_INDEX_STATUS_INACTIVE; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y', strtotime($form['created_at'])); ?>
                                    </small>
                                </td>
                                <td class="end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo ADMIN_URL; ?>/forms/preview/<?php echo $form['id']; ?>" 
                                        class="btn btn-outline-secondary" 
                                        title="<?php echo LANG_TEMPLATE_FORMS_INDEX_ACTION_PREVIEW; ?>">
                                            <?php echo bloggy_icon('bs', 'eye', '16', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/forms/edit/<?php echo $form['id']; ?>" 
                                        class="btn btn-outline-primary" 
                                        title="<?php echo LANG_TEMPLATE_FORMS_INDEX_ACTION_EDIT; ?>">
                                            <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/forms/settings/<?php echo $form['id']; ?>" 
                                        class="btn btn-outline-info" 
                                        title="<?php echo LANG_TEMPLATE_FORMS_INDEX_ACTION_SETTINGS; ?>">
                                            <?php echo bloggy_icon('bs', 'gear', '16', '#000'); ?>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="<?php echo LANG_TEMPLATE_FORMS_INDEX_ACTION_DELETE; ?>"
                                                onclick="confirmDelete(<?php echo $form['id']; ?>, '<?php echo html($form['name']); ?>')">
                                            <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php ob_start(); ?>
<script>
    function confirmDelete(formId, formName) {
        if (confirm('<?php echo LANG_TEMPLATE_FORMS_INDEX_DELETE_CONFIRM; ?>' + formName + '?")) {
            window.location.href = '<?php echo ADMIN_URL; ?>/forms/delete/' + formId;
        }
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>