<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'trophy', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_TITLE; ?>
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/user-achievements/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_ADD_BTN; ?>
            </a>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_FILTER_TYPE_LABEL; ?></label>
                    <select name="type" class="form-select">
                        <option value=""><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_FILTER_ALL_TYPES; ?></option>
                        <option value="auto" <?php echo ($_GET['type'] ?? '') == 'auto' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_FILTER_AUTO; ?></option>
                        <option value="manual" <?php echo ($_GET['type'] ?? '') == 'manual' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_FILTER_MANUAL; ?></option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_FILTER_SEARCH_LABEL; ?></label>
                    <input type="text" name="search" class="form-control" placeholder="<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_FILTER_SEARCH_PLACEHOLDER; ?>" 
                        value="<?php echo html($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <?php echo bloggy_icon('bs', 'funnel', '18', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_FILTER_APPLY_BTN; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($achievements)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'trophy', '48', '#838383'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_EMPTY_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_EMPTY_HINT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/user-achievements/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_CREATE_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50"></th>
                                <th><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_TABLE_ACHIEVEMENT; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_TABLE_CONDITIONS; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_TABLE_UNLOCKED; ?></th>
                                <th><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_TABLE_STATUS; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($achievements as $achievement) { ?>
                                <tr>
                                    <td>
                                        <?php if ($achievement['image']) { ?>
                                            <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo $achievement['image']; ?>" 
                                                class="rounded" 
                                                style="width: 40px; height: 40px; object-fit: cover;"
                                                alt="<?php echo html($achievement['name']); ?>">
                                        <?php } else { ?>
                                            <div class="d-flex align-items-center justify-content-center rounded" 
                                                style="width: 40px; height: 40px; background: <?php echo $achievement['icon_color']; ?>;">
                                                <?php 
                                                $iconName = str_replace('bi-', '', $achievement['icon']);
                                                echo bloggy_icon('bs', $iconName, '20', '#fff'); 
                                                ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo html($achievement['name']); ?></strong>
                                            <?php if ($achievement['type'] == 'manual') { ?>
                                                <span class="badge bg-warning ms-2"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_MANUAL_BADGE; ?></span>
                                            <?php } ?>
                                        </div>
                                        <?php if ($achievement['description']) { ?>
                                            <small class="text-muted"><?php echo html($achievement['description']); ?></small>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($achievement['conditions'])) { ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach ($achievement['conditions'] as $condition) { ?>
                                                    <?php 
                                                    $conditionText = '';
                                                    switch($condition['condition_type']) {
                                                        case 'registration_days':
                                                            $conditionText = LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_CONDITION_REGISTRATION_DAYS;
                                                            break;
                                                        case 'comments_count':
                                                            $conditionText = LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_CONDITION_COMMENTS_COUNT;
                                                            break;
                                                        case 'posts_count':
                                                            $conditionText = LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_CONDITION_POSTS_COUNT;
                                                            break;
                                                        case 'login_days':
                                                            $conditionText = LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_CONDITION_LOGIN_DAYS;
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-info">
                                                        <?php echo html($conditionText); ?> 
                                                        <?php echo html($condition['operator']); ?> 
                                                        <?php echo html($condition['value']); ?>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        <?php } else { ?>
                                            <span class="text-muted"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_NO_CONDITIONS; ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo $achievement['unlocked_count'] ?? 0; ?> <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_USERS; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $achievement['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $achievement['is_active'] ? LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_ACTIVE : LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_INACTIVE; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?php echo ADMIN_URL; ?>/user-achievements/toggle/<?php echo $achievement['id']; ?>" 
                                               class="btn btn-sm btn-outline-<?php echo $achievement['is_active'] ? 'warning' : 'success'; ?>"
                                               title="<?php echo $achievement['is_active'] ? LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_DEACTIVATE_TITLE : LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_ACTIVATE_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', $achievement['is_active'] ? 'pause' : 'play', '16', '#000'); ?>
                                            </a>
                                            
                                            <a href="<?php echo ADMIN_URL; ?>/user-achievements/edit/<?php echo $achievement['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_EDIT_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                            </a>
                                            
                                            <a href="<?php echo ADMIN_URL; ?>/user-achievements/delete/<?php echo $achievement['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_DELETE_CONFIRM; ?>')"
                                               title="<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_INDEX_DELETE_TITLE; ?>">
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