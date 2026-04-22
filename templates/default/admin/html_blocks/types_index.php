<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'boxes', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TITLE; ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks" class="btn btn-outline-secondary me-2">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
                <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_BACK_TO_BLOCKS; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks/select-type" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_CREATE_BLOCK; ?>
            </a>
        </div>
    </div>

    <div class="alert alert-info">
        <div class="d-flex">
            <?php echo bloggy_icon('bs', 'info-circle-fill', '16', '#000', 'me-2 mt-1'); ?>
            <div>
                <strong><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ALERT_TITLE; ?></strong><br>
                <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ALERT_TEXT; ?>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($blockTypes)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'boxes', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_NO_TYPES_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_NO_TYPES_TEXT; ?></p>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_SYSTEM_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_TEMPLATE; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_AUTHOR; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_VERSION; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_STATUS; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blockTypes as $systemName => $type) { ?>
                                <tr class="<?php echo (!$type['is_active'] && $systemName !== 'DefaultBlock') ? 'table-warning' : ''; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong><?php echo html($type['name']); ?></strong>
                                                <div class="text-muted small">
                                                    <?php echo html($type['description']); ?>
                                                </div>
                                                <?php if (!$type['is_visible_in_creation'] && $systemName !== 'DefaultBlock') { ?>
                                                    <div class="text-warning small mt-1">
                                                        <?php echo bloggy_icon('bs', 'eye-slash', '16', '#ffc107', 'me-1'); ?>
                                                        <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_HIDDEN_ON_CREATE; ?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-muted"><?php echo html($systemName); ?></code>
                                    </td>
                                    <td>
                                        <?php if (!empty($type['template']) && $type['template'] !== 'all') { ?>
                                            <span class="badge bg-info"><?php echo html($type['template']); ?></span>
                                        <?php } else { ?>
                                            <span class="badge bg-light text-dark">all</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <small><?php echo html($type['author'] ?? 'BloggyCMS'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo html($type['version'] ?? '1.0.0'); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($systemName === 'DefaultBlock') { ?>
                                            <span class="badge bg-success"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STATUS_ACTIVE; ?></span>
                                        <?php } else { ?>
                                            <?php if ($type['is_active']) { ?>
                                                <span class="badge bg-success"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STATUS_ACTIVE; ?></span>
                                            <?php } else { ?>
                                                <span class="badge bg-warning"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STATUS_DISABLED; ?></span>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <?php if ($systemName !== 'DefaultBlock') { ?>
                                                <?php $isActive = $type['is_active'] ?? true; ?>
                                                <a href="<?php echo ADMIN_URL; ?>/html-blocks/types/toggle/<?php echo $systemName; ?>" 
                                                   class="btn btn-sm <?php echo $isActive ? 'btn-warning' : 'btn-success'; ?>"
                                                   title="<?php echo $isActive ? LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_DISABLE_TITLE : LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ENABLE_TITLE; ?>">
                                                    <?php echo bloggy_icon('bs', 'power', '16', $isActive ? '#000' : '#fff'); ?>
                                                    <?php echo $isActive ? '' : ' ' . LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ENABLE_SHORT; ?>
                                                </a>
                                                <a href="<?php echo ADMIN_URL; ?>/html-blocks/types/delete/<?php echo $systemName; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('<?php echo sprintf(LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_DELETE_CONFIRM, $systemName); ?>')"
                                                   title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_DELETE_TITLE; ?>">
                                                    <?php echo bloggy_icon('bs', 'trash', '16', '#000', 'me-1'); ?>
                                                    <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_DELETE_BTN; ?>
                                                </a>
                                            <?php } else { ?>
                                                <span class="text-muted small"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_SYSTEM_BADGE; ?></span>
                                            <?php } ?>
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