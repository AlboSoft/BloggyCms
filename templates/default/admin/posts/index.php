<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'file-text', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_POSTS_INDEX_TITLE; ?>
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/post-blocks" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'bricks', '20', '#2c2c2c', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_POSTS_INDEX_POST_BLOCKS_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/fields/entity/post" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'input-cursor-text', '20', '#2c2c2c', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_POSTS_INDEX_CUSTOM_FIELDS_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/categories" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'folder', '20', '#2c2c2c', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_POSTS_INDEX_CATEGORIES_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/posts/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_POSTS_INDEX_CREATE_BTN; ?>
            </a>
        </div>
    </div>

    <?php 
    $categoryModel = new CategoryModel($this->db);
    $allCategories = $categoryModel->getAll();
    $hasCategories = !empty($allCategories);
    ?>

    <?php if ($hasCategories) { ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_CATEGORY_LABEL; ?></label>
                    <select name="category" class="form-select">
                        <option value=""><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_ALL_CATEGORIES; ?></option>
                        <?php 
                        $selectedCategory = $_GET['category'] ?? '';
                        foreach ($allCategories as $cat) { 
                        ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $selectedCategory == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo html($cat['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_STATUS_LABEL; ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_ALL_STATUSES; ?></option>
                        <option value="published" <?php echo ($_GET['status'] ?? '') == 'published' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_PUBLISHED; ?></option>
                        <option value="draft" <?php echo ($_GET['status'] ?? '') == 'draft' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_DRAFT; ?></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_CREATE_MODE_LABEL; ?></label>
                    <select name="create_mode" class="form-select" id="createModeSelect">
                        <option value="">-</option>
                        <option value="day" <?php echo ($_GET['create_mode'] ?? '') == 'day' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_CREATE_MODE_DAY; ?></option>
                        <option value="period" <?php echo ($_GET['create_mode'] ?? '') == 'period' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_CREATE_MODE_PERIOD; ?></option>
                    </select>
                </div>
                <div class="col-md-2" id="dayCreateDateContainer" style="<?php echo ($_GET['create_mode'] ?? '') == 'day' ? '' : 'display: none;'; ?>">
                    <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_CREATE_DATE_LABEL; ?></label>
                    <input type="date" name="create_date" class="form-control" 
                        value="<?php echo html($_GET['create_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4" id="periodCreateDateContainer" style="<?php echo ($_GET['create_mode'] ?? '') == 'period' ? '' : 'display: none;'; ?>">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_CREATE_DATE_FROM; ?></label>
                            <input type="date" name="create_date_from" class="form-control" 
                                value="<?php echo html($_GET['create_date_from'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_CREATE_DATE_TO; ?></label>
                            <input type="date" name="create_date_to" class="form-control" 
                                value="<?php echo html($_GET['create_date_to'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary w-100">
                        <?php echo bloggy_icon('bs', 'funnel', '16', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_POSTS_INDEX_FILTER_APPLY_BTN; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($posts)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'file-text', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_POSTS_INDEX_EMPTY_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_POSTS_INDEX_EMPTY_HINT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/posts/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_POSTS_INDEX_CREATE_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_POSTS_INDEX_TABLE_TITLE; ?></th>
                                <th><?php echo LANG_TEMPLATE_POSTS_INDEX_TABLE_CATEGORY; ?></th>
                                <th><?php echo LANG_TEMPLATE_POSTS_INDEX_TABLE_STATUS; ?></th>
                                <th><?php echo LANG_TEMPLATE_POSTS_INDEX_TABLE_VIEWS; ?></th>
                                <th><?php echo LANG_TEMPLATE_POSTS_INDEX_TABLE_LIKES; ?></th>
                                <th><?php echo LANG_TEMPLATE_POSTS_INDEX_TABLE_DATE; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_POSTS_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post) { ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($post['featured_image']) { ?>
                                            <img src="<?php echo BASE_URL; ?>/uploads/images/<?php echo $post['featured_image']; ?>" 
                                                 class="rounded me-2" 
                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                 alt="<?php echo html($post['title']); ?>">
                                        <?php } else { ?>
                                            <div class="rounded me-2 d-flex align-items-center justify-content-center bg-light" 
                                                 style="width: 40px; height: 40px;">
                                                <?php echo bloggy_icon('bs', 'image', '20', '#6C6C6C'); ?>
                                            </div>
                                        <?php } ?>
                                        <div>
                                            <strong><?php echo html($post['title']); ?></strong>
                                            <?php if ($post['password_protected']) { ?>
                                                <span class="badge bg-warning ms-2" title="<?php echo LANG_TEMPLATE_POSTS_INDEX_PASSWORD_PROTECTED_TITLE; ?>">
                                                    <?php echo bloggy_icon('bs', 'lock', '12', '#000'); ?>
                                                </span>
                                            <?php } ?>
                                            <?php if (isset($post['is_adult']) && $post['is_adult']) { ?>
                                                <span class="badge bg-danger ms-2" title="<?php echo LANG_TEMPLATE_ADULT_BADGE_TITLE; ?>" style="font-size: 11px; padding: 3px 6px;">
                                                    18+
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($post['category_name'])) { ?>
                                        <span class="badge bg-light text-dark">
                                            <?php echo html($post['category_name']); ?>
                                        </span>
                                    <?php } else { ?>
                                        <span class="text-muted">—</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo $post['status'] === 'published' ? LANG_TEMPLATE_POSTS_INDEX_STATUS_PUBLISHED : LANG_TEMPLATE_POSTS_INDEX_STATUS_DRAFT; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php echo bloggy_icon('bs', 'eye', '16', '#6C6C6C'); ?>
                                        <span class="fw-medium"><?php echo $post['views'] ?? 0; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php if (($post['likes_count'] ?? 0) > 0) { ?>
                                            <?php echo bloggy_icon('bs', 'heart-fill', '16', '#ff6161'); ?>
                                            <span class="likes-count-stat"><?php echo $post['likes_count'] ?? 0; ?></span>
                                        <?php } else { ?>
                                            <?php echo bloggy_icon('bs', 'heart-fill', '16', '#878787'); ?>
                                            <span class="fw-bold text-muted">0</span>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?php echo BASE_URL; ?>/post/<?php echo $post['slug']; ?>" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           target="_blank"
                                           title="<?php echo LANG_TEMPLATE_POSTS_INDEX_ACTION_VIEW_TITLE; ?>">
                                            <?php echo bloggy_icon('bs', 'eye', '14', '#000'); ?>
                                        </a>
                                        <?php if ($post['status'] === 'published') { ?>
                                            <a href="<?php echo ADMIN_URL; ?>/posts/toggle-status/<?php echo $post['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               title="<?php echo LANG_TEMPLATE_POSTS_INDEX_ACTION_MOVE_TO_DRAFT_TITLE; ?>"
                                               onclick="return confirm('<?php echo LANG_TEMPLATE_POSTS_INDEX_CONFIRM_MOVE_TO_DRAFT; ?>')">
                                                <?php echo bloggy_icon('bs', 'archive', '14', '#000'); ?>
                                            </a>
                                        <?php } else { ?>
                                            <a href="<?php echo ADMIN_URL; ?>/posts/toggle-status/<?php echo $post['id']; ?>" 
                                               class="btn btn-sm btn-outline-success"
                                               title="<?php echo LANG_TEMPLATE_POSTS_INDEX_ACTION_PUBLISH_TITLE; ?>"
                                               onclick="return confirm('<?php echo LANG_TEMPLATE_POSTS_INDEX_CONFIRM_PUBLISH; ?>')">
                                                <?php echo bloggy_icon('bs', 'check-lg', '14', '#000'); ?>
                                            </a>
                                        <?php } ?>
                                        <a href="<?php echo ADMIN_URL; ?>/posts/edit/<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="<?php echo LANG_TEMPLATE_POSTS_INDEX_ACTION_EDIT_TITLE; ?>">
                                            <?php echo bloggy_icon('bs', 'pencil', '14', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/posts/delete/<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('<?php echo LANG_TEMPLATE_POSTS_INDEX_CONFIRM_DELETE; ?>')"
                                           title="<?php echo LANG_TEMPLATE_POSTS_INDEX_ACTION_DELETE_TITLE; ?>">
                                            <?php echo bloggy_icon('bs', 'trash', '14', '#000'); ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createModeSelect = document.getElementById('createModeSelect');
    const dayCreateDateContainer = document.getElementById('dayCreateDateContainer');
    const periodCreateDateContainer = document.getElementById('periodCreateDateContainer');
    
    if (createModeSelect) {
        createModeSelect.addEventListener('change', function() {
            if (this.value === 'day') {
                dayCreateDateContainer.style.display = '';
                periodCreateDateContainer.style.display = 'none';
                periodCreateDateContainer.querySelectorAll('input').forEach(input => input.value = '');
            } else if (this.value === 'period') {
                dayCreateDateContainer.style.display = 'none';
                periodCreateDateContainer.style.display = '';
                dayCreateDateContainer.querySelector('input').value = '';
            } else {
                dayCreateDateContainer.style.display = 'none';
                periodCreateDateContainer.style.display = 'none';
                dayCreateDateContainer.querySelector('input').value = '';
                periodCreateDateContainer.querySelectorAll('input').forEach(input => input.value = '');
            }
        });
    }
});
</script>