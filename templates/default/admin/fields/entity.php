<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'input-cursor-text', '24', '#000', 'me-2'); ?>
            <?php echo sprintf(LANG_TEMPLATE_FIELDS_ENTITY_TITLE, html($entityName)); ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/fields/create/<?php echo $entityType; ?>" class="btn btn-primary">
            <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_FIELDS_ENTITY_ADD_BTN; ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($fields)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'input-cursor-text', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_FIELDS_ENTITY_NO_FIELDS_TITLE; ?></h5>
                    <p class="text-muted"><?php echo sprintf(LANG_TEMPLATE_FIELDS_ENTITY_NO_FIELDS_TEXT, html(mb_strtolower($entityName))); ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/fields/create/<?php echo $entityType; ?>" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_FIELDS_ENTITY_ADD_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th><?php echo LANG_TEMPLATE_FIELDS_ENTITY_TABLE_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_FIELDS_ENTITY_TABLE_SYSTEM_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_FIELDS_ENTITY_TABLE_TYPE; ?></th>
                                <th><?php echo LANG_TEMPLATE_FIELDS_ENTITY_TABLE_REQUIRED; ?></th>
                                <th><?php echo LANG_TEMPLATE_FIELDS_ENTITY_TABLE_STATUS; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_FIELDS_ENTITY_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($fields as $field) { ?>
                            <tr>
                                <td><?php echo $field['sort_order']; ?></td>
                                <td>
                                    <strong><?php echo html($field['name']); ?></strong>
                                    <?php if(!empty($field['description'])) { ?>
                                        <br><small class="text-muted"><?php echo html($field['description']); ?></small>
                                    <?php } ?>
                                </td>
                                <td>
                                    <code class="text-muted"><?php echo html($field['system_name']); ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $field['type']; ?></span>
                                </td>
                                <td>
                                    <?php if($field['is_required']) { ?>
                                        <span class="badge bg-danger"><?php echo LANG_TEMPLATE_FIELDS_ENTITY_YES; ?></span>
                                    <?php } else { ?>
                                        <span class="badge bg-secondary"><?php echo LANG_TEMPLATE_FIELDS_ENTITY_NO; ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $field['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $field['is_active'] ? LANG_TEMPLATE_FIELDS_ENTITY_ACTIVE : LANG_TEMPLATE_FIELDS_ENTITY_INACTIVE; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?php echo ADMIN_URL; ?>/fields/toggle/<?php echo $field['id']; ?>" 
                                           class="btn btn-sm btn-<?php echo $field['is_active'] ? 'warning' : 'success'; ?>"
                                           title="<?php echo $field['is_active'] ? LANG_TEMPLATE_FIELDS_ENTITY_DISABLE_TITLE : LANG_TEMPLATE_FIELDS_ENTITY_ENABLE_TITLE; ?>">
                                            <?php echo bloggy_icon('bs', 'power', '16', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/fields/edit/<?php echo $field['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="<?php echo LANG_TEMPLATE_FIELDS_ENTITY_EDIT_TITLE; ?>">
                                            <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/fields/delete/<?php echo $field['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('<?php echo LANG_TEMPLATE_FIELDS_ENTITY_DELETE_CONFIRM; ?>')"
                                           title="<?php echo LANG_TEMPLATE_FIELDS_ENTITY_DELETE_TITLE; ?>">
                                            <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</div>