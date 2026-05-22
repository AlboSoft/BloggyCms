<div class="container-fluid p-0">
    <div class="d-flex btn-group justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'file-earmark-text', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_PAGES_INDEX_TITLE; ?>
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/post-blocks" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'bricks', '20', '#353434', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_PAGES_INDEX_POST_BLOCKS_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/fields/entity/page" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'input-cursor-text', '20', '#353434', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_PAGES_INDEX_CUSTOM_FIELDS_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/pages/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_PAGES_INDEX_CREATE_BTN; ?>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($pages)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'file-earmark-text', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_PAGES_INDEX_NO_PAGES_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_PAGES_INDEX_NO_PAGES_TEXT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/pages/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_PAGES_INDEX_CREATE_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_PAGES_INDEX_TABLE_TITLE; ?></th>
                                <th><?php echo LANG_TEMPLATE_PAGES_INDEX_TABLE_URL; ?></th>
                                <th><?php echo LANG_TEMPLATE_PAGES_INDEX_TABLE_STATUS; ?></th>
                                <th><?php echo LANG_TEMPLATE_PAGES_INDEX_TABLE_DATE; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_PAGES_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $renderPageRow = function($pages, $level = 0) use (&$renderPageRow) {
                                    foreach ($pages as $page) {
                                        $indent = $level * 30;
                                        $isChild = $level > 0;
                                        ?>
                                        <tr class="<?php echo $isChild ? 'child-page' : 'parent-page'; ?>">
                                            <td>
                                                <div style="padding-left: <?php echo $indent; ?>px; <?php echo $isChild ? 'font-style: italic; color: #6c757d;' : ''; ?>">
                                                    <?php if ($isChild) { ?>
                                                        <span class="text-muted me-1">↳</span>
                                                    <?php } ?>
                                                    <strong><?php echo html($page['title']); ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <code class="text-muted"><?php echo html($page['slug']); ?></code>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $page['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo $page['status'] === 'published' ? LANG_TEMPLATE_PAGES_INDEX_STATUS_PUBLISHED : LANG_TEMPLATE_PAGES_INDEX_STATUS_DRAFT; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo date('d.m.Y', strtotime($page['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="<?php echo BASE_URL; ?>/page/<?php echo $page['slug']; ?>"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    target="_blank"
                                                    title="<?php echo LANG_TEMPLATE_PAGES_INDEX_ACTION_VIEW; ?>">
                                                        <?php echo bloggy_icon('bs', 'eye', '16', '#000'); ?>
                                                    </a>
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/edit/<?php echo $page['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="<?php echo LANG_TEMPLATE_PAGES_INDEX_ACTION_EDIT; ?>">
                                                        <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                                    </a>
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/delete/<?php echo $page['id']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="<?php echo LANG_TEMPLATE_PAGES_INDEX_ACTION_DELETE; ?>"
                                                    onclick="return confirm('<?php echo LANG_TEMPLATE_PAGES_INDEX_DELETE_CONFIRM; ?>')">
                                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        if (!empty($page['children'])) {
                                            $renderPageRow($page['children'], $level + 1);
                                        }
                                    }
                                };

                                if (!empty($pages)) {
                                    $renderPageRow($pages);
                                }
                                ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</div>