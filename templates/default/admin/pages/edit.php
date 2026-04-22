<?php
    add_admin_js('templates/default/admin/assets/js/controllers/image-upload.js');
    add_admin_js('templates/default/admin/assets/js/controllers/post-blocks.js');
    add_admin_css('templates/default/admin/assets/css/controllers/post-blocks.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'pencil-square', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_PAGES_EDIT_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/pages" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_PAGES_EDIT_BACK_BTN; ?>
        </a>
    </div>

    <form method="post" id="page-form" enctype="multipart/form-data">
        <input type="hidden" name="page_id" value="<?php echo $page['id']; ?>">
        <input type="hidden" name="post_blocks" id="post_blocks_data" value="<?php echo html(json_encode($preparedBlocks ?? array()), ENT_QUOTES); ?>">

        <div class="row">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_PAGES_EDIT_TITLE_LABEL; ?></label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="title" 
                                   value="<?php echo html($page['title']); ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_PAGES_EDIT_SLUG_LABEL; ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo BASE_URL; ?>/page/</span>
                                <input type="text"
                                    class="form-control"
                                    name="slug"
                                    id="page-slug"
                                    value="<?php echo html($page['slug']); ?>"
                                    placeholder="<?php echo LANG_TEMPLATE_PAGES_EDIT_SLUG_PLACEHOLDER; ?>"
                                    title="<?php echo LANG_TEMPLATE_PAGES_EDIT_SLUG_TITLE; ?>">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="generate-slug-btn"
                                        title="<?php echo LANG_TEMPLATE_PAGES_EDIT_SLUG_GENERATE_BTN_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'magic', '16', '#000'); ?>
                                </button>
                            </div>
                            <div class="form-text">
                                <?php echo LANG_TEMPLATE_PAGES_EDIT_SLUG_HINT; ?>
                            </div>
                        </div>
                        
                        <div class="card mb-4 sticky-top" style="top: 20px; z-index: 1000;">
                            <div class="card-header bg-white py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-muted small"><?php echo LANG_TEMPLATE_PAGES_EDIT_AVAILABLE_BLOCKS; ?></h6>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm me-2" id="block-category-filter" style="width: auto;">
                                            <option value="all"><?php echo LANG_TEMPLATE_PAGES_EDIT_FILTER_ALL; ?></option>
                                            <option value="text">🖊️ <?php echo LANG_TEMPLATE_PAGES_EDIT_FILTER_TEXT; ?></option>
                                            <option value="media">🎞️ <?php echo LANG_TEMPLATE_PAGES_EDIT_FILTER_MEDIA; ?></option>
                                            <option value="layout">🔩 <?php echo LANG_TEMPLATE_PAGES_EDIT_FILTER_LAYOUT; ?></option>
                                            <option value="advanced">🧲 <?php echo LANG_TEMPLATE_PAGES_EDIT_FILTER_ADVANCED; ?></option>
                                            <option value="basic">✔️ <?php echo LANG_TEMPLATE_PAGES_EDIT_FILTER_BASIC; ?></option>
                                        </select>

                                        <div class="input-group input-group-sm" style="width: 200px;">
                                            <input type="text" class="form-control" id="block-search" placeholder="<?php echo LANG_TEMPLATE_PAGES_EDIT_SEARCH_PLACEHOLDER; ?>">
                                            <button class="btn btn-outline-secondary" type="button" id="clear-search">
                                                <?php echo bloggy_icon('bs', 'x', '16', '#000'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-2">
                                <div id="post-block-buttons" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-0">
                                <div id="post-blocks-container" class="min-h-100" style="min-height: 400px;">
                                    <?php if (empty($preparedBlocks)) { ?>
                                    <div class="text-center text-muted py-5 empty-state">
                                        <?php echo bloggy_icon('bs', 'inbox', '48', '#6C6C6C', 'mb-3'); ?>
                                        <p class="mb-1"><?php echo LANG_TEMPLATE_PAGES_EDIT_NO_BLOCKS_TITLE; ?></p>
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_PAGES_EDIT_NO_BLOCKS_HINT; ?></small>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <?php
                            $fieldModel = new FieldModel($this->db);
                            $customFields = $fieldModel->getActiveByEntityType('page');
                        ?>

                        <?php if (!empty($customFields)) { ?>
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-0">
                                    <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_PAGES_EDIT_CUSTOM_FIELDS_TITLE; ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($customFields as $field) { ?>
                                        <div class="mb-3">
                                            <label class="form-label small">
                                                <?php echo html($field['name']); ?>
                                                <?php if ($field['is_required']) { ?>
                                                    <span class="text-danger">*</span>
                                                <?php } ?>
                                            </label>
                                            
                                            <?php 
                                            $value = $fieldModel->getFieldValue('page', $page['id'], $field['system_name']);
                                            ?>
                                            
                                            <?php echo $fieldModel->renderFieldInput($field, $value, 'page', $page['id']); ?>
                                            
                                            <?php if (!empty($field['description'])) { ?>
                                                <div class="form-text small"><?php echo html($field['description']); ?></div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>

            <div class="col-lg-3">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_PAGES_EDIT_PUBLISH_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_PAGES_EDIT_STATUS_LABEL; ?></label>
                            <select name="status" class="form-select" required>
                                <option value="draft" <?php echo $page['status'] == 'draft' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_PAGES_EDIT_STATUS_DRAFT; ?></option>
                                <option value="published" <?php echo $page['status'] == 'published' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_PAGES_EDIT_STATUS_PUBLISHED; ?></option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_PAGES_EDIT_SUBMIT_BTN; ?>
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pages" class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'x-lg', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_PAGES_EDIT_CANCEL_BTN; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
window.availablePostBlocks = <?php echo json_encode($postBlockManager->getPostBlocksForJS('page')); ?>;
window.initialPostBlocks = <?php echo json_encode($preparedBlocks ?? (isset($page) ? [] : array())); ?>;
<?php if (isset($page)) { ?>window.isEditMode = true;<?php } ?>
</script>
<?php admin_bottom_js(ob_get_clean()); ?>