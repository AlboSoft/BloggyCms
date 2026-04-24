<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><?php echo bloggy_icon('bs', 'people', '24', '#000', 'me-2 controller-svg'); ?> <?php echo LANG_TEMPLATE_USERS_INDEX_TITLE; ?></h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/user-groups" class="btn btn-outline-secondary"><?php echo bloggy_icon('bs', 'diagram-3', '20', '#000', 'me-2'); ?><?php echo LANG_TEMPLATE_USERS_INDEX_GROUPS_BTN; ?></a>
            <a href="<?php echo ADMIN_URL; ?>/user-achievements" class="btn btn-outline-secondary"><?php echo bloggy_icon('bs', 'trophy', '20', '#000', 'me-2'); ?><?php echo LANG_TEMPLATE_USERS_INDEX_ACHIEVEMENTS_BTN; ?></a>
            <a href="<?php echo ADMIN_URL; ?>/settings?tab=components&controller=users" class="btn btn-outline-secondary"><?php echo bloggy_icon('bs', 'gear-fill', '20', '#000', 'me-2'); ?><?php echo LANG_TEMPLATE_USERS_INDEX_SETTINGS_BTN; ?></a>
            <a href="<?php echo ADMIN_URL; ?>/users/create" class="btn btn-primary"><?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?><?php echo LANG_TEMPLATE_USERS_INDEX_ADD_BTN; ?></a>
        </div>
    </div>

    <?php if (SettingsHelper::get('controller_users', 'show_filter') == true) { ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label"><?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_GROUP_LABEL; ?></label>
                        <select name="group" class="form-select">
                            <option value=""><?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_ALL_GROUPS; ?></option>
                            <?php foreach ($allGroups as $group) { ?>
                                <option value="<?php echo $group['id']; ?>" <?php echo ($_GET['group'] ?? '') == $group['id'] ? 'selected' : ''; ?>>
                                    <?php echo html($group['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_STATUS_LABEL; ?></label>
                        <select name="status" class="form-select">
                            <option value=""><?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_ALL_STATUSES; ?></option>
                            <option value="active" <?php echo ($_GET['status'] ?? '') == 'active' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_ACTIVE; ?></option>
                            <option value="banned" <?php echo ($_GET['status'] ?? '') == 'banned' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_BANNED; ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_SEARCH_LABEL; ?></label>
                        <input type="text" name="search" class="form-control" placeholder="<?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_SEARCH_PLACEHOLDER; ?>" 
                            value="<?php echo html($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><?php echo bloggy_icon('bs', 'funnel', '18', '#fff', 'me-2'); ?><?php echo LANG_TEMPLATE_USERS_INDEX_FILTER_APPLY_BTN; ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>

    <?php if (SettingsHelper::get('controller_users', 'show_info') == true) { ?>
        <div class="alert alert-info d-flex align-items-center mb-3">
            <?php echo bloggy_icon('bs', 'info-circle', '16', '#5AAFC9', 'me-2'); ?>
            <span><?php echo html($randomHint); ?></span>
        </div>
    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($users)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'people', '48', '#838383', 'me-2 controller-svg'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_USERS_INDEX_EMPTY_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_USERS_INDEX_EMPTY_HINT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/users/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?> <?php echo LANG_TEMPLATE_USERS_INDEX_ADD_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_USERS_INDEX_TABLE_USER; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_INDEX_TABLE_EMAIL; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_INDEX_TABLE_GROUPS; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_INDEX_TABLE_STATUS; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_INDEX_TABLE_REGISTERED; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_USERS_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user) { ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($user['avatar']) { ?>
                                                <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo $user['avatar']; ?>" 
                                                    class="rounded-circle me-2" 
                                                    style="width: 40px; height: 40px; object-fit: cover;"
                                                    alt="<?php echo html($user['username']); ?>">
                                            <?php } else { ?>
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-light" 
                                                    style="width: 40px; height: 40px;">
                                                    <?php echo bloggy_icon('bs', 'person', '20', '#6C6C6C'); ?>
                                                </div>
                                            <?php } ?>
                                            <div>
                                                <strong><?php echo html($user['username']); ?></strong>
                                                <?php if ($user['id'] == $_SESSION['user_id']) { ?>
                                                    <span class="badge bg-info ms-2"><?php echo LANG_TEMPLATE_USERS_INDEX_YOU_BADGE; ?></span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo html($user['email']); ?></td>
                                    <td>
                                        <?php if (!empty($user['groups'])) { ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach ($user['groups'] as $group) { ?>
                                                    <span class="badge bg-secondary">
                                                        <?php echo html($group['name']); ?>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        <?php } else { ?>
                                            <span class="text-muted"><?php echo LANG_TEMPLATE_USERS_INDEX_NO_GROUPS; ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo $user['status'] === 'active' ? LANG_TEMPLATE_USERS_INDEX_STATUS_ACTIVE : LANG_TEMPLATE_USERS_INDEX_STATUS_BANNED; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <?php if ($user['status'] === 'active' && $user['id'] != $_SESSION['user_id']) { ?>
                                                <a href="<?php echo ADMIN_URL; ?>/users/toggle-status/<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="<?php echo LANG_TEMPLATE_USERS_INDEX_BAN_TITLE; ?>"
                                                   onclick="return confirm('<?php echo LANG_TEMPLATE_USERS_INDEX_BAN_CONFIRM; ?>')">
                                                    <?php echo bloggy_icon('bs', 'lock', '16', '#000'); ?>
                                                </a>
                                            <?php } elseif ($user['status'] === 'banned' && $user['id'] != $_SESSION['user_id']) { ?>
                                                <a href="<?php echo ADMIN_URL; ?>/users/toggle-status/<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-outline-success"
                                                   title="<?php echo LANG_TEMPLATE_USERS_INDEX_UNBAN_TITLE; ?>"
                                                   onclick="return confirm('<?php echo LANG_TEMPLATE_USERS_INDEX_UNBAN_CONFIRM; ?>')">
                                                    <?php echo bloggy_icon('bs', 'unlock', '16', '#000'); ?>
                                                </a>
                                            <?php } ?>
                                            
                                            <a href="<?php echo ADMIN_URL; ?>/users/edit/<?php echo $user['id']; ?>" 
                                                class="btn btn-sm btn-outline-primary"
                                                title="<?php echo LANG_TEMPLATE_USERS_INDEX_EDIT_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                            </a>

                                            <a href="<?php echo ADMIN_URL; ?>/users/quick-assign-achievement/<?php echo $user['id']; ?>" 
                                                class="btn btn-sm btn-outline-info"
                                                title="<?php echo LANG_TEMPLATE_USERS_INDEX_ASSIGN_ACHIEVEMENT_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'trophy', '16', '#000'); ?>
                                            </a>

                                            <?php if ($user['id'] != $_SESSION['user_id']) { ?>
                                            <a href="<?php echo ADMIN_URL; ?>/users/delete/<?php echo $user['id']; ?>" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('<?php echo LANG_TEMPLATE_USERS_INDEX_DELETE_CONFIRM; ?>')"
                                                title="<?php echo LANG_TEMPLATE_USERS_INDEX_DELETE_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                            </a>
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