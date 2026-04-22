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
                                $typeIsActive = $block['type_is_active'] ?? true;
                            ?>
                            <tr class="<?php echo (!$typeIsActive) ? 'table-warning' : ''; ?>">
                                <td>
                                    <strong><?php echo html($block['name']); ?></strong>
                                    <?php if (!$typeIsActive) { ?>
                                    <div class="text-warning small mt-1">
                                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#ffc107', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_TYPE_DISABLED; ?>
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
                                    <span class="badge bg-light text-dark">all</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <code class="text-muted"><?php echo html($block['slug']); ?></code>
                                </td>
                                <td>
                                    <?php if ($typeIsActive) { ?>
                                        <span class="badge bg-success"><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_STATUS_ACTIVE; ?></span>
                                    <?php } else { ?>
                                        <span class="badge bg-warning"><?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_STATUS_TYPE_DISABLED; ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php if ($typeIsActive) { ?>
                                            <a href="<?php echo ADMIN_URL; ?>/html-blocks/edit/<?php echo $block['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_ACTION_EDIT; ?>">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                            </a>
                                        <?php } else { ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    disabled
                                                    title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_ACTION_DISABLED_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#6c757d'); ?>
                                            </button>
                                        <?php } ?>
                                        
                                        <a href="<?php echo ADMIN_URL; ?>/html-blocks/delete/<?php echo $block['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('<?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_DELETE_CONFIRM; ?>')"
                                           title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_INDEX_ACTION_DELETE; ?>">
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