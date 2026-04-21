<?php add_admin_js('templates/default/admin/assets/js/controllers/delete-cat.js'); ?>

<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-4">
                    <div class="d-flex align-items-center">
                        <div class="p-3 me-3">
                            <?php echo bloggy_icon('bs', 'trash', '22', 'currentColor', 'text-danger'); ?>
                        </div>
                        <div>
                            <h4 class="card-title mb-1 text-dark"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_TITLE; ?></h4>
                            <p class="text-muted mb-0"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_CATEGORY_LABEL; ?> "<?php echo html($category['name']) ?>"</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="alert alert-danger border-0 bg-danger mb-4">
                        <div class="d-flex align-items-start">
                            <?php echo bloggy_icon('bs', 'exclamation-triangle-fill', '22', 'currentColor', 'text-white me-3 mt-1'); ?>
                            <div>
                                <h5 class="alert-heading mb-2 text-white"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_WARNING_TITLE; ?></h5>
                                <p class="mb-0 text-white"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_WARNING_TEXT; ?> <strong><?php echo $postsCount . ' ' . plural_form($postsCount, explode('|', LANG_TEMPLATE_CATEGORIES_DELETE_POSTS_ENDING)); ?></strong>. <?php echo LANG_TEMPLATE_CATEGORIES_DELETE_CHOOSE_ACTION; ?></p>
                            </div>
                        </div>
                    </div>

                    <form method="post" id="deleteForm">
                        <div class="options-container">
                            <div class="option-card mb-3">
                                <input class="form-check-input d-none" type="radio" name="delete_action" id="move_posts" value="move_posts" checked>
                                <label class="option-label w-100 p-4 rounded-3 border cursor-pointer" for="move_posts">
                                    <div class="d-flex align-items-center">
                                        <div class="option-check me-3">
                                            <div class="check-circle">
                                                <?php echo bloggy_icon('bs', 'check-lg', '18', 'white'); ?>
                                            </div>
                                        </div>
                                        <div class="option-icon bg-primary rounded-circle p-2 me-3">
                                            <?php echo bloggy_icon('bs', 'arrow-left-right', '24', 'currentColor', 'text-white'); ?>
                                        </div>
                                        <div class="option-content flex-grow-1">
                                            <h6 class="mb-1 fw-semibold"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_MOVE_TITLE; ?></h6>
                                            <p class="text-muted mb-2 small"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_MOVE_DESC; ?></p>
                                            <div class="category-select mt-3">
                                                <select name="target_category_id" class="form-select form-select-sm" id="target_category_select" style="max-width: 300px;">
                                                    <option value=""><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_SELECT_CATEGORY; ?></option>
                                                    <?php foreach ($otherCategories as $cat): ?>
                                                        <option value="<?= $cat['id'] ?>"><?php echo html($cat['name']) ?> (<?= $cat['posts_count'] ?? 0 ?> <?php echo plural_form($cat['posts_count'] ?? 0, explode('|', LANG_TEMPLATE_CATEGORIES_DELETE_POSTS_ENDING_SHORT)); ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div class="option-card">
                                <input class="form-check-input d-none" type="radio" name="delete_action" id="delete_all" value="delete_all">
                                <label class="option-label w-100 p-4 rounded-3 border cursor-pointer" for="delete_all">
                                    <div class="d-flex align-items-center">
                                        <div class="option-check me-3">
                                            <div class="check-circle">
                                                <?php echo bloggy_icon('bs', 'check-lg', '18', 'white'); ?>
                                            </div>
                                        </div>
                                        <div class="option-icon bg-danger rounded-circle p-2 me-3">
                                            <?php echo bloggy_icon('bs', 'trash', '24', 'currentColor', 'text-white'); ?>
                                        </div>
                                        <div class="option-content flex-grow-1">
                                            <h6 class="mb-1 fw-semibold text-danger"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_DELETE_ALL_TITLE; ?></h6>
                                            <p class="text-danger mb-0 small">
                                                <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', 'currentColor', 'text-danger'); ?>
                                                <?php echo LANG_TEMPLATE_CATEGORIES_DELETE_DELETE_ALL_DESC; ?> <strong><?= $postsCount ?></strong>)
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-5 pt-4 border-top">
                            <button type="submit" class="btn btn-danger px-4 py-2" onclick="return confirmDeletion()">
                                <?php echo bloggy_icon('bs', 'trash', '22', '#fff', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_CATEGORIES_DELETE_CONFIRM_BTN; ?>
                            </button>
                            <a href="<?= ADMIN_URL ?>/categories" class="btn btn-outline-secondary px-4 py-2">
                                <?php echo bloggy_icon('bs', 'arrow-left', '22', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_CATEGORIES_DELETE_CANCEL_BTN; ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title mb-3"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_INFO_TITLE; ?></h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-primary fw-bold fs-4"><?= $postsCount ?></div>
                            <div class="text-muted small"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_INFO_POSTS; ?></div>
                        </div>
                        <div class="col-4">
                            <div class="text-success fw-bold fs-4"><?= date('d.m.Y', strtotime($category['created_at'])) ?></div>
                            <div class="text-muted small"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_INFO_CREATED; ?></div>
                        </div>
                        <div class="col-4">
                            <div class="text-info fw-bold fs-4"><?= $category['sort_order'] ?></div>
                            <div class="text-muted small"><?php echo LANG_TEMPLATE_CATEGORIES_DELETE_INFO_POSITION; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>