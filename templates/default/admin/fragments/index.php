<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'puzzle', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_TITLE; ?>
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/fragments/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_CREATE_BTN; ?>
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
            <?php if (empty($fragments)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'puzzle', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_NO_FRAGMENTS_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_NO_FRAGMENTS_TEXT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/fragments/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_CREATE_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                             <tr>
                                <th><?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_TABLE_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_TABLE_SYSTEM_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_TABLE_DESCRIPTION; ?></th>
                                <th><?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_TABLE_ENTRIES; ?></th>
                                <th><?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_TABLE_STATUS; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_TABLE_ACTIONS; ?></th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fragments as $fragment) { 
                                $stats = $this->fragmentModel->getStats($fragment['id']);
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo html($fragment['name']); ?></strong>
                                    </td>
                                    <td>
                                        <code class="text-muted"><?php echo html($fragment['system_name']); ?></code>
                                    </td>
                                    <td>
                                        <?php echo html(mb_substr($fragment['description'] ?? '', 0, 100)); ?>
                                        <?php if (mb_strlen($fragment['description'] ?? '') > 100) { ?>...<?php } ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/fragments/entries/<?php echo $fragment['id']; ?>" 
                                           class="badge bg-info text-decoration-none">
                                            <?php echo $stats['total']; ?> <?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_ENTRIES_COUNT; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $fragment['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $fragment['status'] === 'active' ? LANG_TEMPLATE_FRAGMENTS_INDEX_STATUS_ACTIVE : LANG_TEMPLATE_FRAGMENTS_INDEX_STATUS_INACTIVE; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo ADMIN_URL; ?>/fragments/entries/<?php echo $fragment['id']; ?>" 
                                               class="btn btn-outline-secondary"
                                               title="<?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_ACTION_ENTRIES; ?>">
                                                <?php echo bloggy_icon('bs', 'list-ul', '16', '#000'); ?>
                                            </a>
                                            <a href="<?php echo ADMIN_URL; ?>/fragments/fields/<?php echo $fragment['id']; ?>" 
                                               class="btn btn-outline-info"
                                               title="<?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_ACTION_FIELDS; ?>">
                                                <?php echo bloggy_icon('bs', 'input-cursor-text', '16', '#000'); ?>
                                            </a>
                                            <a href="<?php echo ADMIN_URL; ?>/fragments/edit/<?php echo $fragment['id']; ?>" 
                                               class="btn btn-outline-primary"
                                               title="<?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_ACTION_EDIT; ?>">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                            </a>
                                            <a href="<?php echo ADMIN_URL; ?>/fragments/delete/<?php echo $fragment['id']; ?>" 
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('<?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_DELETE_CONFIRM; ?>')"
                                               title="<?php echo LANG_TEMPLATE_FRAGMENTS_INDEX_ACTION_DELETE; ?>">
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