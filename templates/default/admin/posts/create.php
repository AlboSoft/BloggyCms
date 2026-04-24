<?php
add_admin_js('templates/default/admin/assets/js/controllers/post-blocks.js');
add_admin_js('templates/default/admin/assets/js/controllers/tags-autocomplete.js');
add_admin_js('templates/default/admin/assets/js/controllers/posts-management.js');
add_admin_js('templates/default/admin/assets/js/controllers/image-upload.js');
add_admin_css('templates/default/admin/assets/css/controllers/post-blocks.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'file-text', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_POSTS_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/posts" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_POSTS_CREATE_BACK_BTN; ?>
        </a>
    </div>

    <form method="post" id="post-form" enctype="multipart/form-data">
        <input type="hidden" name="blocks" id="blocks-input" value="<?php echo html(json_encode(array()), ENT_QUOTES); ?>">
        <input type="hidden" name="uploaded_image_path" id="uploaded-image-path" value="">
        <input type="hidden" name="uploaded_image_url" id="uploaded-image-url" value="">
        
        <div id="blocks-files-container" style="display: none;"></div>
        
        <div class="row">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_CREATE_TITLE_LABEL; ?></label>
                            <input type="text" class="form-control form-control-lg" name="title" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_CREATE_SHORT_DESC_LABEL; ?></label>
                            <textarea class="form-control" name="short_description" rows="3" 
                                    placeholder="<?php echo LANG_TEMPLATE_POSTS_CREATE_SHORT_DESC_PLACEHOLDER; ?>"></textarea>
                            <div class="form-text"><?php echo LANG_TEMPLATE_POSTS_CREATE_SHORT_DESC_HINT; ?></div>
                        </div>
                        
                        <div class="card mb-4 sticky-top" style="top: 20px; z-index: 1000;">
                            <div class="card-header bg-white py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-muted small"><?php echo LANG_TEMPLATE_POSTS_CREATE_AVAILABLE_BLOCKS; ?></h6>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm me-2" id="block-category-filter" style="width: auto;">
                                            <option value="all"><?php echo LANG_TEMPLATE_POSTS_CREATE_FILTER_ALL; ?></option>
                                            <option value="text">🖊️ <?php echo LANG_TEMPLATE_POSTS_CREATE_FILTER_TEXT; ?></option>
                                            <option value="media">🎞️ <?php echo LANG_TEMPLATE_POSTS_CREATE_FILTER_MEDIA; ?></option>
                                            <option value="layout">🔩 <?php echo LANG_TEMPLATE_POSTS_CREATE_FILTER_LAYOUT; ?></option>
                                            <option value="advanced">🧲 <?php echo LANG_TEMPLATE_POSTS_CREATE_FILTER_ADVANCED; ?></option>
                                            <option value="basic">✔️ <?php echo LANG_TEMPLATE_POSTS_CREATE_FILTER_BASIC; ?></option>
                                        </select>
                                        
                                        <div class="input-group input-group-sm" style="width: 200px;">
                                            <input type="text" class="form-control" id="block-search" placeholder="<?php echo LANG_TEMPLATE_POSTS_CREATE_SEARCH_PLACEHOLDER; ?>">
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
                                    <div class="text-center text-muted py-5 empty-state">
                                        <?php echo bloggy_icon('bs', 'inbox', '48', '#6C6C6C', 'mb-3'); ?>
                                        <p class="mb-1"><?php echo LANG_TEMPLATE_POSTS_CREATE_NO_BLOCKS_TITLE; ?></p>
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_POSTS_CREATE_NO_BLOCKS_HINT; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="post_blocks" id="post_blocks_data" value="">
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_CREATE_FEATURED_IMAGE_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="image-upload-area border-2 border-dashed rounded-3 p-5 text-center position-relative"
                            id="imageUploadArea"
                            style="border-color: #dee2e6; border-style: dashed; background: #f8f9fa; transition: all 0.3s ease;">
                            
                            <div class="upload-default" id="uploadDefault">
                                <div class="mb-3">
                                    <?php echo bloggy_icon('bs', 'cloud-arrow-up', '48', '#6C6C6C'); ?>
                                </div>
                                <h5 class="text-muted mb-2"><?php echo LANG_TEMPLATE_POSTS_CREATE_DRAG_TEXT; ?></h5>
                                <p class="text-muted small mb-3"><?php echo LANG_TEMPLATE_POSTS_CREATE_OR_TEXT; ?></p>
                                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('featured-image-input').click()">
                                    <?php echo LANG_TEMPLATE_POSTS_CREATE_SELECT_FILE_BTN; ?>
                                </button>
                                <div class="mt-2">
                                    <small class="text-muted"><?php echo LANG_TEMPLATE_POSTS_CREATE_FILE_HINT; ?></small>
                                </div>
                            </div>

                            <div class="upload-preview d-none" id="uploadPreview">
                                <div class="position-relative d-inline-block">
                                    <img src="" alt="Preview" class="rounded shadow-sm" 
                                        style="max-height: 200px; max-width: 100%;" id="imagePreview">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                            onclick="removeImage()" style="border-radius: 50%; width: 30px; height: 30px;">
                                        <?php echo bloggy_icon('bs', 'x', '16', '#fff'); ?>
                                    </button>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted" id="fileName"></small>
                                </div>
                            </div>

                            <div class="upload-progress mt-3 d-none" id="uploadProgress">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                        role="progressbar" style="width: 0%" id="progressBar"></div>
                                </div>
                                <small class="text-muted mt-1" id="progressText"><?php echo LANG_TEMPLATE_POSTS_CREATE_UPLOADING_TEXT; ?></small>
                            </div>

                            <input type="file" class="d-none" id="featured-image-input" name="featured_image" accept="image/*">
                        </div>
                    </div>
                </div>

                <?php
                    $fieldModel = new FieldModel($this->db);
                    $customFields = $fieldModel->getActiveByEntityType('post');
                ?>

                <?php if (!empty($customFields)) { ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_CREATE_CUSTOM_FIELDS_TITLE; ?></h5>
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
                                    $config = json_decode($field['config'] ?? '{}', true);
                                    $value = $config['default_value'] ?? '';
                                    ?>
                                    
                                    <?php echo $fieldModel->renderFieldInput($field, $value, 'post', 0); ?>
                                    
                                    <?php if (!empty($field['description'])) { ?>
                                        <div class="form-text small"><?php echo html($field['description']); ?></div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
                
            </div>
            
            <div class="col-lg-3">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_CREATE_PUBLISH_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_CREATE_STATUS_LABEL; ?></label>
                            <select class="form-select" name="status">
                                <option value="draft" selected><?php echo LANG_TEMPLATE_POSTS_CREATE_STATUS_DRAFT; ?></option>
                                <option value="published"><?php echo LANG_TEMPLATE_POSTS_CREATE_STATUS_PUBLISHED; ?></option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="password_protected" name="password_protected">
                                <label class="form-check-label" for="password_protected">
                                    <?php echo LANG_TEMPLATE_POSTS_CREATE_PASSWORD_PROTECT_LABEL; ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4 password-field" style="display: none;">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_CREATE_PASSWORD_LABEL; ?></label>
                            <input type="text" class="form-control" name="password">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_CREATE_PUBLISH_DATE_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="change_publish_date" name="change_publish_date">
                                <label class="form-check-label" for="change_publish_date">
                                    <?php echo LANG_TEMPLATE_POSTS_CREATE_CHANGE_DATE_LABEL; ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="publish-date-field" style="display: none;">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_CREATE_DATETIME_LABEL; ?></label>
                            <input type="datetime-local" class="form-control" name="publish_date" 
                                max="<?php echo date('Y-m-d\TH:i'); ?>" 
                                value="<?php echo date('Y-m-d\TH:i'); ?>">
                            <div class="form-text">
                                <?php echo sprintf(LANG_TEMPLATE_POSTS_CREATE_DATE_HINT, date('d.m.Y H:i')); ?>
                            </div>
                        </div>
                        
                        <div class="current-publish-date">
                            <small class="text-muted">
                                <?php echo LANG_TEMPLATE_POSTS_CREATE_CURRENT_DATE_LABEL; ?> <strong><?php echo date('d.m.Y H:i'); ?></strong>
                            </small>
                        </div>
                    </div>
                </div>
                
                <?php if ($hasCategories) { ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_CREATE_CATEGORY_TITLE; ?></h5>
                        </div>
                        <div class="card-body">
                            <select class="form-select" name="category_id" required>
                                <option value=""><?php echo LANG_TEMPLATE_POSTS_CREATE_SELECT_CATEGORY; ?></option>
                                <?php foreach ($categories as $category) { ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo isset($post['category_id']) && $post['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo html($category['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php } else { ?>
                    <input type="hidden" name="category_id" value="NULL">
                <?php } ?>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_CREATE_TAGS_TITLE; ?></h5>
                    </div>
                    <div class="card-body" data-max-tags="<?php echo $maxTags; ?>">
                        <div id="tags-container"></div>
                        
                        <input type="text" 
                            class="form-control" 
                            id="tag-search" 
                            placeholder="<?php echo LANG_TEMPLATE_POSTS_CREATE_TAGS_PLACEHOLDER; ?>"
                            autocomplete="off">
                        
                        <div class="form-text d-flex justify-content-between align-items-center mt-2">
                            <span><?php echo LANG_TEMPLATE_POSTS_CREATE_TAGS_HINT; ?></span>
                            <span class="badge bg-light text-dark" id="tags-counter">
                                <span id="current-tags-count">0</span> / 
                                <span id="max-tags-count"><?php echo $maxTags; ?></span>
                            </span>
                        </div>
                        
                        <input type="hidden" name="tags_json" id="tags-json" value="[]">
                        
                        <div class="dropdown">
                            <div class="dropdown-menu w-100" id="tags-suggestions" style="display: none; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'shield-lock', '16', '#000', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $userModel = new UserModel($this->db);
                        $groups = $userModel->getAllGroups();
                        $groups[] = array(
                            'id' => 'guest',
                            'name' => LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_GUEST,
                            'description' => LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_GUEST_DESC
                        );
                        ?>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label small">
                                    <?php echo bloggy_icon('bs', 'eye', '14', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_SHOW_LABEL; ?>
                                </label>
                                <select class="form-select form-select-sm" name="show_to_groups[]" multiple size="4">
                                    <option value=""><?php echo LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_ALL_GROUPS; ?></option>
                                    <?php foreach ($groups as $group) { ?>
                                        <option value="<?php echo $group['id']; ?>">
                                            <?php echo html($group['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text small"><?php echo LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_SHOW_HINT; ?></div>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label small">
                                    <?php echo bloggy_icon('bs', 'eye-slash', '14', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_HIDE_LABEL; ?>
                                </label>
                                <select class="form-select form-select-sm" name="hide_from_groups[]" multiple size="4">
                                    <option value=""><?php echo LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_NO_HIDE; ?></option>
                                    <?php foreach ($groups as $group) { ?>
                                        <option value="<?php echo $group['id']; ?>">
                                            <?php echo html($group['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text small"><?php echo LANG_TEMPLATE_POSTS_CREATE_VISIBILITY_HIDE_HINT; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" 
                            value="1" <?php echo isset($post['allow_comments']) && $post['allow_comments'] == 1 ? 'checked' : 'checked'; ?>>
                        <label class="form-check-label" for="allow_comments">
                            <?php echo LANG_TEMPLATE_POSTS_CREATE_ALLOW_COMMENTS_LABEL; ?>
                        </label>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_CREATE_SEO_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_CREATE_SEO_TITLE_LABEL; ?></label>
                            <input type="text" class="form-control" name="seo_title" 
                                placeholder="<?php echo LANG_TEMPLATE_POSTS_CREATE_SEO_TITLE_PLACEHOLDER; ?>">
                            <div class="form-text"><?php echo LANG_TEMPLATE_POSTS_CREATE_SEO_TITLE_HINT; ?></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_CREATE_META_DESC_LABEL; ?></label>
                            <textarea class="form-control" name="meta_description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_POSTS_CREATE_SUBMIT_BTN; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
    window.availablePostBlocks = <?php echo json_encode($postBlockManager->getPostBlocksForJS()); ?>;
    window.initialPostBlocks = <?php echo json_encode(array()); ?>;
    window.MAX_TAGS_PER_POST = <?php echo $maxTags; ?>;
</script>
<?php admin_bottom_js(ob_get_clean()); ?>