<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'diagram-3', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/user-groups/create" class="btn btn-primary">
            <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_ADD_BTN; ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($groups)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'diagram-3', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_EMPTY_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_EMPTY_HINT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/user-groups/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_ADD_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_TABLE_NAME; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_TABLE_DESCRIPTION; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_TABLE_USERS; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_TABLE_DEFAULT; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groups as $group) { ?>
                            <tr>
                                <td>
                                    <strong><?php echo html($group['name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo html($group['description'] ?? ''); ?>
                                </td>
                                <td>
                                    <?php
                                        $userCount = $db->fetchValue(
                                            "SELECT COUNT(*) FROM users_groups WHERE group_id = ?", 
                                            array($group['id'])
                                        );
                                    ?>
                                    <span class="badge bg-secondary"><?php echo $userCount; ?></span>
                                </td>
                                <td>
                                    <?php if ($group['is_default']) { ?>
                                        <span class="badge bg-success"><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_YES; ?></span>
                                    <?php } else { ?>
                                        <span class="badge bg-light text-dark"><?php echo LANG_TEMPLATE_USERS_GROUPS_INDEX_NO; ?></span>
                                    <?php } ?>
                                </td>
                                    <td>
                                        <?php echo admin_action_group([
                                            ['type' => 'permissions', 'url' => ADMIN_URL . '/user-groups/permissions/' . $group['id'], 'title' => LANG_TEMPLATE_USERS_GROUPS_INDEX_PERMISSIONS_TITLE],
                                            ['type' => 'edit', 'url' => ADMIN_URL . '/user-groups/edit/' . $group['id'], 'title' => LANG_TEMPLATE_USERS_GROUPS_INDEX_EDIT_TITLE],
                                            !$group['is_default']
                                                ? ['type' => 'delete', 'url' => ADMIN_URL . '/user-groups/delete/' . $group['id'], 'title' => LANG_TEMPLATE_USERS_GROUPS_INDEX_DELETE_TITLE, 'confirm' => LANG_TEMPLATE_USERS_GROUPS_INDEX_DELETE_CONFIRM]
                                                : null,
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