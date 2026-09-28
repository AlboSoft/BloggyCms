<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'box', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_ADDONS_INDEX_TITLE; ?>
        </h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" id="check-updates-btn">
                <?php echo bloggy_icon('bs', 'arrow-repeat', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_ADDONS_INDEX_CHECK_UPDATES_BTN; ?>
            </button>
            <a href="<?php echo ADMIN_URL; ?>/addons/install" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'cloud-upload', '16', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_ADDONS_INDEX_INSTALL_BTN; ?>
            </a>
        </div>
    </div>

    <?php if (!empty($randomHint)) { ?>
        <div class="alert alert-info d-flex align-items-center mb-4">
            <?php echo bloggy_icon('bs', 'info-circle', '16', '#5AAFC9', 'me-2'); ?>
            <span><?php echo html($randomHint); ?></span>
        </div>
    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($addons)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'box', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_ADDONS_INDEX_NO_PACKAGES_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_ADDONS_INDEX_NO_PACKAGES_TEXT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/addons/install" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'cloud-upload', '16', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_ADDONS_INDEX_INSTALL_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_ADDONS_INDEX_TABLE_HEADER_PACKAGE; ?></th>
                                <th><?php echo LANG_TEMPLATE_ADDONS_INDEX_TABLE_HEADER_VERSION; ?></th>
                                <th><?php echo LANG_TEMPLATE_ADDONS_INDEX_TABLE_HEADER_TYPE; ?></th>
                                <th><?php echo LANG_TEMPLATE_ADDONS_INDEX_TABLE_HEADER_AUTHOR; ?></th>
                                <th><?php echo LANG_TEMPLATE_ADDONS_INDEX_TABLE_HEADER_INSTALLED_AT; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_ADDONS_INDEX_TABLE_HEADER_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($addons as $addon) { ?>
                            <tr id="addon-<?php echo $addon['id']; ?>">
                                <td>
                                    <div>
                                        <strong><?php echo html($addon['title']); ?></strong>
                                        <?php if (!empty($addon['description'])) { ?>
                                            <br><small class="text-muted"><?php echo html(mb_substr($addon['description'], 0, 100)); ?>...</small>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">v<?php echo html($addon['version_string']); ?></span>
                                    <br>
                                    <small class="text-muted" title="<?php echo date('d.m.Y H:i:s', strtotime($addon['installed_at'])); ?>">
                                        <?php echo time_ago($addon['installed_at']); ?>
                                    </small>
                                    <?php if (!empty($addon['updated_at']) && $addon['updated_at'] != $addon['installed_at']) { ?>
                                        <br>
                                        <small class="text-muted" title="<?php echo date('d.m.Y H:i:s', strtotime($addon['updated_at'])); ?>">
                                            <?php echo LANG_TEMPLATE_ADDONS_INDEX_UPDATED; ?> <?php echo time_ago($addon['updated_at']); ?>
                                        </small>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $addon['type'] === 'install' ? 'success' : 'warning'; ?>">
                                        <?php echo $addon['type'] === 'install' ? LANG_TEMPLATE_ADDONS_INDEX_TYPE_INSTALL : LANG_TEMPLATE_ADDONS_INDEX_TYPE_UPDATE; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($addon['author_name'])) { ?>
                                        <div><?php echo html($addon['author_name']); ?></div>
                                        <?php if (!empty($addon['author_email'])) { ?>
                                            <small class="text-muted"><?php echo html($addon['author_email']); ?></small>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <span class="text-muted">—</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y H:i', strtotime($addon['installed_at'])); ?>
                                    </small>
                                    <?php if (!empty($addon['updated_at'])) { ?>
                                        <br>
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_ADDONS_INDEX_UPDATED_SHORT; ?> <?php echo date('d.m.Y', strtotime($addon['updated_at'])); ?></small>
                                    <?php } ?>
                                </td>
                                <td class="text-end">
                                    <?php echo admin_action_group([
                                        ['type' => 'info', 'tag' => 'button', 'class' => 'info-addon', 'title' => LANG_TEMPLATE_ADDONS_INDEX_INFO_TITLE, 'attrs' => 'data-id="' . (int)$addon['id'] . '"'],
                                        ['type' => 'delete', 'url' => ADMIN_URL . '/addons/delete/' . $addon['id'], 'title' => LANG_TEMPLATE_ADDONS_INDEX_DELETE_TITLE, 'confirm' => sprintf(LANG_TEMPLATE_ADDONS_INDEX_DELETE_CONFIRM, $addon['title'])],
                                    ]); ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 text-muted small">
                    <div class="d-flex justify-content-between">
                        <span><?php echo sprintf(LANG_TEMPLATE_ADDONS_INDEX_TOTAL_PACKAGES, $addonCount); ?></span>
                        <span>
                            <?php echo bloggy_icon('bs', 'info-circle', '14', '#6C6C6C', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_ADDONS_INDEX_DELETE_NOTE; ?>
                        </span>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="modal fade" id="addonInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo LANG_TEMPLATE_ADDONS_INDEX_MODAL_TITLE; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addon-info-content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2"><?php echo LANG_TEMPLATE_ADDONS_INDEX_MODAL_LOADING; ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo LANG_TEMPLATE_ADDONS_INDEX_MODAL_CLOSE; ?></button>
            </div>
        </div>
    </div>
</div>

<?php add_admin_js('templates/default/admin/assets/js/controllers/addons.js'); ?>