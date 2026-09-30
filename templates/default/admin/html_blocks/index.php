<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'code-square', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TITLE; ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks/clear-cache"
               class="btn btn-warning me-2"
               onclick="return confirm('<?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_CLEAR_CACHE_CONFIRM; ?>')">
                <?php echo bloggy_icon('bs', 'arrow-repeat', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_CLEAR_CACHE_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks/types" class="btn btn-outline-secondary me-2">
                <?php echo bloggy_icon('bs', 'boxes', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TYPES_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks/select-type" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_CREATE_BTN; ?>
            </a>
        </div>
    </div>

    <div class="text-muted small mb-3">
        <?php echo bloggy_icon('bs', 'palette', '14', '#6c757d', 'me-1'); ?>
        <?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_ACTIVE_TEMPLATE; ?> <strong><?php echo html($currentTemplate); ?></strong>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($blocks)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'code-square', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_NO_BLOCKS_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_NO_BLOCKS_TEXT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/html-blocks/select-type" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_CREATE_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TABLE_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TABLE_TYPE; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TABLE_TEMPLATE; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TABLE_SLUG; ?></th>
                                <th><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TABLE_TYPE_STATUS; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blocks as $block) {
                                $typeIsActive = $block['type_is_active'] ?? false;
                                $typeIsCompatible = $block['type_is_compatible'] ?? false;
                                $typeIsAvailable = $block['type_is_available'] ?? false;
                            ?>
                            <tr class="<?php echo !$typeIsAvailable ? 'table-warning' : ''; ?>">
                                <td>
                                    <strong><?php echo html($block['name']); ?></strong>
                                    <?php if (!$typeIsActive) { ?>
                                        <div class="text-warning small mt-1">
                                            <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#ffc107', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TYPE_DISABLED; ?>
                                        </div>
                                    <?php } elseif (!$typeIsCompatible) { ?>
                                        <div class="text-warning small mt-1">
                                            <?php echo bloggy_icon('bs', 'palette', '16', '#ffc107', 'me-1'); ?>
                                            <?php echo sprintf(LANG_TEMPLATE_HTMLBLOCKS_INDEX_TYPE_TEMPLATE_MISMATCH, html($block['block_type_template'] ?? '')); ?>
                                        </div>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo html($block['type_name'] ?? LANG_TEMPLATE_HTMLBLOCKS_INDEX_DEFAULT_TYPE); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($block['template']) && $block['template'] !== 'all') { ?>
                                        <span class="badge bg-info"><?php echo html($block['template']); ?></span>
                                    <?php } else { ?>
                                        <span class="badge bg-light text-dark">default</span>
                                    <?php } ?>
                                </td>
                                <td><code class="text-muted"><?php echo html($block['slug']); ?></code></td>
                                <td>
                                    <?php if ($typeIsAvailable) { ?>
                                        <span class="badge bg-success"><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_STATUS_ACTIVE; ?></span>
                                    <?php } elseif (!$typeIsActive) { ?>
                                        <span class="badge bg-warning"><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_STATUS_TYPE_DISABLED; ?></span>
                                    <?php } else { ?>
                                        <span class="badge bg-warning"><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_STATUS_TEMPLATE_MISMATCH; ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php echo admin_action_group([
                                        $typeIsAvailable
                                            ? ['type' => 'edit', 'url' => ADMIN_URL . '/html-blocks/edit/' . $block['id'], 'title' => LANG_TEMPLATE_HTMLBLOCKS_INDEX_ACTION_EDIT]
                                            : ['type' => 'edit', 'title' => !$typeIsActive ? LANG_TEMPLATE_HTMLBLOCKS_INDEX_ACTION_DISABLED_TITLE : LANG_TEMPLATE_HTMLBLOCKS_INDEX_ACTION_TEMPLATE_MISMATCH_TITLE, 'disabled' => true],
                                        ['type' => 'delete', 'url' => ADMIN_URL . '/html-blocks/delete/' . $block['id'], 'title' => LANG_TEMPLATE_HTMLBLOCKS_INDEX_ACTION_DELETE, 'confirm' => LANG_TEMPLATE_HTMLBLOCKS_INDEX_DELETE_CONFIRM],
                                    ]); ?>
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
