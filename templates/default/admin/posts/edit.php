<?php
add_admin_js('templates/default/admin/assets/js/controllers/tags-autocomplete.js');
add_admin_js('templates/default/admin/assets/js/controllers/posts-management.js');
add_admin_js('templates/default/admin/assets/js/controllers/image-upload.js');
add_admin_js('templates/default/admin/assets/js/controllers/post-blocks.js');
add_admin_css('templates/default/admin/assets/css/controllers/post-blocks.css');
?>
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'pencil-square', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_POSTS_EDIT_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/posts" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_POSTS_EDIT_BACK_BTN; ?>
        </a>
    </div>

    <form method="post" id="post-form" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
        <input type="hidden" name="uploaded_image_path" id="uploaded-image-path" value="">
        <input type="hidden" name="uploaded_image_url" id="uploaded-image-url" value="">
        <input type="hidden" name="post_blocks" id="post_blocks_data" value="<?php echo html(json_encode($preparedBlocks ?? array()), ENT_QUOTES); ?>">
        
        <div class="row">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_EDIT_TITLE_LABEL; ?></label>
                            <input type="text" class="form-control form-control-lg" name="title" value="<?php echo html($post['title']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_EDIT_SHORT_DESC_LABEL; ?></label>
                            <textarea class="form-control" name="short_description" rows="3"
                                    placeholder="<?php echo LANG_TEMPLATE_POSTS_EDIT_SHORT_DESC_PLACEHOLDER; ?>"><?php echo html($post['short_description'] ?? ''); ?></textarea>
                            <div class="form-text"><?php echo LANG_TEMPLATE_POSTS_EDIT_SHORT_DESC_HINT; ?></div>
                        </div>
                        
                        <div class="card mb-4 sticky-top" style="top: 20px; z-index: 1000;">
                            <div class="card-header bg-white py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-muted small"><?php echo LANG_TEMPLATE_POSTS_EDIT_AVAILABLE_BLOCKS; ?></h6>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm me-2" id="block-category-filter" style="width: auto;">
                                            <option value="all"><?php echo LANG_TEMPLATE_POSTS_EDIT_FILTER_ALL; ?></option>
                                            <option value="text">🖊️ <?php echo LANG_TEMPLATE_POSTS_EDIT_FILTER_TEXT; ?></option>
                                            <option value="media">🎞️ <?php echo LANG_TEMPLATE_POSTS_EDIT_FILTER_MEDIA; ?></option>
                                            <option value="layout">🔩 <?php echo LANG_TEMPLATE_POSTS_EDIT_FILTER_LAYOUT; ?></option>
                                            <option value="advanced">🧲 <?php echo LANG_TEMPLATE_POSTS_EDIT_FILTER_ADVANCED; ?></option>
                                            <option value="basic">✔️ <?php echo LANG_TEMPLATE_POSTS_EDIT_FILTER_BASIC; ?></option>
                                        </select>
                                        
                                        <div class="input-group input-group-sm" style="width: 200px;">
                                            <input type="text" class="form-control" id="block-search" placeholder="<?php echo LANG_TEMPLATE_POSTS_EDIT_SEARCH_PLACEHOLDER; ?>">
                                            <button class="btn btn-outline-secondary" type="button" id="clear-search">
                                                <?php echo bloggy_icon('bs', 'x', '16', '#000'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-2">
                                <div id="post-block-buttons" class="d-flex flex-wrap gap-1">
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-0">
                                <div id="post-blocks-container" class="min-h-100" style="min-height: 400px;">
                                    <div class="text-center text-muted py-5 empty-state">
                                        <?php echo bloggy_icon('bs', 'inbox', '48', '#6C6C6C', 'mb-3'); ?>
                                        <p class="mb-1"><?php echo LANG_TEMPLATE_POSTS_EDIT_NO_BLOCKS_TITLE; ?></p>
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_POSTS_EDIT_NO_BLOCKS_HINT; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_EDIT_FEATURED_IMAGE_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="image-upload-area border-2 border-dashed rounded-3 p-5 text-center position-relative"
                            id="imageUploadArea"
                            style="border-color: #dee2e6; border-style: dashed; background: #f8f9fa; transition: all 0.3s ease;">
                            
                            <?php if ($post['featured_image']) { ?>
                                <div class="upload-preview" id="uploadPreview">
                                    <div class="position-relative d-inline-block">
                                        <img src="<?php echo BASE_URL; ?>/uploads/images/<?php echo $post['featured_image']; ?>" 
                                             alt="<?php echo LANG_TEMPLATE_POSTS_EDIT_CURRENT_IMAGE_ALT; ?>" 
                                             class="rounded shadow-sm" 
                                             style="max-height: 200px; max-width: 100%;" 
                                             id="imagePreview">
                                        <button type="button" 
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                                onclick="removeImage()" 
                                                style="border-radius: 50%; width: 30px; height: 30px;"
                                                title="<?php echo LANG_TEMPLATE_POSTS_EDIT_REMOVE_IMAGE_TITLE; ?>">
                                            <?php echo bloggy_icon('bs', 'x', '16', '#fff'); ?>
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted" id="fileName"><?php echo LANG_TEMPLATE_POSTS_EDIT_CURRENT_IMAGE_LABEL; ?></small>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" 
                                                class="btn btn-outline-primary btn-sm" 
                                                onclick="document.getElementById('featured-image-input').click()">
                                            <?php echo bloggy_icon('bs', 'arrow-repeat', '14', '#0d6efd', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_POSTS_EDIT_REPLACE_IMAGE_BTN; ?>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="upload-default d-none" id="uploadDefault">
                                    <div class="mb-3">
                                        <?php echo bloggy_icon('bs', 'cloud-arrow-up', '48', '#6C6C6C'); ?>
                                    </div>
                                    <h5 class="text-muted mb-2"><?php echo LANG_TEMPLATE_POSTS_EDIT_DRAG_TEXT; ?></h5>
                                    <p class="text-muted small mb-3"><?php echo LANG_TEMPLATE_POSTS_EDIT_OR_TEXT; ?></p>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('featured-image-input').click()">
                                        <?php echo LANG_TEMPLATE_POSTS_EDIT_SELECT_FILE_BTN; ?>
                                    </button>
                                    <div class="mt-2">
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_POSTS_EDIT_FILE_HINT; ?></small>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="upload-default" id="uploadDefault">
                                    <div class="mb-3">
                                        <?php echo bloggy_icon('bs', 'cloud-arrow-up', '48', '#6C6C6C'); ?>
                                    </div>
                                    <h5 class="text-muted mb-2"><?php echo LANG_TEMPLATE_POSTS_EDIT_DRAG_TEXT; ?></h5>
                                    <p class="text-muted small mb-3"><?php echo LANG_TEMPLATE_POSTS_EDIT_OR_TEXT; ?></p>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('featured-image-input').click()">
                                        <?php echo LANG_TEMPLATE_POSTS_EDIT_SELECT_FILE_BTN; ?>
                                    </button>
                                    <div class="mt-2">
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_POSTS_EDIT_FILE_HINT; ?></small>
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
                            <?php } ?>

                            <div class="upload-progress mt-3 d-none" id="uploadProgress">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                        role="progressbar" style="width: 0%" id="progressBar"></div>
                                </div>
                                <small class="text-muted mt-1" id="progressText"><?php echo LANG_TEMPLATE_POSTS_EDIT_UPLOADING_TEXT; ?></small>
                            </div>

                            <input type="file" class="d-none" id="featured-image-input" name="featured_image" accept="image/*">
                            
                            <input type="hidden" name="remove_featured_image" id="removeFeaturedImage" value="0">
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
                            <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_EDIT_CUSTOM_FIELDS_TITLE; ?></h5>
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
                                    $value = $fieldModel->getFieldValue('post', $post['id'], $field['system_name']) ?? $config['default_value'] ?? '';
                                    ?>
                                    
                                    <?php echo $fieldModel->renderFieldInput($field, $value, 'post', $post['id']); ?>
                                    
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
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_EDIT_PUBLISH_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_EDIT_STATUS_LABEL; ?></label>
                            <select class="form-select" name="status">
                                <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_POSTS_EDIT_STATUS_DRAFT; ?></option>
                                <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_POSTS_EDIT_STATUS_PUBLISHED; ?></option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="password_protected" 
                                       name="password_protected"
                                       <?php echo $post['password_protected'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="password_protected">
                                    <?php echo LANG_TEMPLATE_POSTS_EDIT_PASSWORD_PROTECT_LABEL; ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4 password-field" style="display: <?php echo $post['password_protected'] ? 'block' : 'none'; ?>;">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_EDIT_PASSWORD_LABEL; ?></label>
                            <input type="text" 
                                   class="form-control" 
                                   name="password" 
                                   value="<?php echo html($post['password'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                    name="is_adult" id="is_adult" value="1" 
                                    <?php echo isset($post['is_adult']) && $post['is_adult'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_adult">
                                    <i class="bi bi-18-plus me-1"></i>
                                    <?php echo LANG_TEMPLATE_POSTS_EDIT_ADULT_CONTENT; ?>
                                </label>
                                <div class="form-text"><?php echo LANG_TEMPLATE_POSTS_EDIT_ADULT_HINT; ?></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="change_publish_date" name="change_publish_date" 
                                    <?php echo (isset($post['created_at']) && $post['created_at'] != date('Y-m-d H:i:s')) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="change_publish_date">
                                    <?php echo LANG_TEMPLATE_POSTS_EDIT_CHANGE_DATE_LABEL; ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="publish-date-field" style="display: <?php echo (isset($post['created_at']) && $post['created_at'] != date('Y-m-d H:i:s')) ? 'block' : 'none'; ?>;">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_EDIT_DATETIME_LABEL; ?></label>
                            <input type="datetime-local" class="form-control" name="publish_date" 
                                max="<?php echo date('Y-m-d\TH:i'); ?>" 
                                value="<?php echo isset($post['created_at']) ? date('Y-m-d\TH:i', strtotime($post['created_at'])) : date('Y-m-d\TH:i'); ?>">
                            <div class="form-text">
                                <?php echo sprintf(LANG_TEMPLATE_POSTS_EDIT_DATE_HINT, date('d.m.Y H:i')); ?>
                            </div>
                        </div>
                        
                        <div class="current-publish-date">
                            <small class="text-muted">
                                <?php echo LANG_TEMPLATE_POSTS_EDIT_CURRENT_DATE_LABEL; ?> 
                                <strong>
                                    <?php echo isset($post['created_at']) ? date('d.m.Y H:i', strtotime($post['created_at'])) : date('d.m.Y H:i'); ?>
                                </strong>
                            </small>
                        </div>

                    </div>
                </div>
                
                <?php if ($hasCategories) { ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_EDIT_CATEGORY_TITLE; ?></h5>
                        </div>
                        <div class="card-body">
                            <select class="form-select" name="category_id" required>
                                <option value=""><?php echo LANG_TEMPLATE_POSTS_EDIT_SELECT_CATEGORY; ?></option>
                                <?php foreach ($categories as $category) { ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $post['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo html($category['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_EDIT_TAGS_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="tags-container">
                            <?php foreach ($postTags as $tag) { ?>
                                <span class="badge bg-primary me-2 mb-2 tag-badge" data-tag-id="<?php echo $tag['id']; ?>">
                                    <?php echo html($tag['name']); ?>
                                    <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 0.7rem;" aria-label="<?php echo LANG_TEMPLATE_POSTS_EDIT_REMOVE_TAG_ARIA; ?>"></button>
                                </span>
                            <?php } ?>
                        </div>
                        
                        <input type="text" 
                            class="form-control" 
                            id="tag-search" 
                            placeholder="<?php echo LANG_TEMPLATE_POSTS_EDIT_TAGS_PLACEHOLDER; ?>"
                            autocomplete="off">
                        
                        <div class="form-text d-flex justify-content-between align-items-center mt-2">
                            <span><?php echo LANG_TEMPLATE_POSTS_EDIT_TAGS_HINT; ?></span>
                            <span class="badge bg-light text-dark" id="tags-counter">
                                <span id="current-tags-count"><?php echo count($postTags); ?></span> / 
                                <span id="max-tags-count"><?php echo \SettingsHelper::get('controller_tags', 'max_tags_per_post', 10); ?></span>
                            </span>
                        </div>
                        
                        <input type="hidden" name="tags_json" id="tags-json" value='<?php echo json_encode(array_column($postTags, 'id')); ?>'>
                        
                        <div class="dropdown">
                            <div class="dropdown-menu w-100" id="tags-suggestions" style="display: none; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'shield-lock', '16', '#000', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $userModel = new UserModel($this->db);
                        $groups = $userModel->getAllGroups();
                        $groups[] = array(
                            'id' => 'guest',
                            'name' => LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_GUEST,
                            'description' => LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_GUEST_DESC
                        );
                        
                        $showToGroups = $post['show_to_groups'] ? json_decode($post['show_to_groups'], true) : array();
                        $hideFromGroups = $post['hide_from_groups'] ? json_decode($post['hide_from_groups'], true) : array();
                        ?>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label small">
                                    <?php echo bloggy_icon('bs', 'eye', '14', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_SHOW_LABEL; ?>
                                </label>
                                <select class="form-select form-select-sm" name="show_to_groups[]" multiple size="4">
                                    <option value=""><?php echo LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_ALL_GROUPS; ?></option>
                                    <?php foreach ($groups as $group) { ?>
                                        <option value="<?php echo $group['id']; ?>" <?php echo in_array($group['id'], $showToGroups) ? 'selected' : ''; ?>>
                                            <?php echo html($group['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text small"><?php echo LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_SHOW_HINT; ?></div>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label small">
                                    <?php echo bloggy_icon('bs', 'eye-slash', '14', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_HIDE_LABEL; ?>
                                </label>
                                <select class="form-select form-select-sm" name="hide_from_groups[]" multiple size="4">
                                    <option value=""><?php echo LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_NO_HIDE; ?></option>
                                    <?php foreach ($groups as $group) { ?>
                                        <option value="<?php echo $group['id']; ?>" <?php echo in_array($group['id'], $hideFromGroups) ? 'selected' : ''; ?>>
                                            <?php echo html($group['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text small"><?php echo LANG_TEMPLATE_POSTS_EDIT_VISIBILITY_HIDE_HINT; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" 
                            value="1" <?php echo isset($post['allow_comments']) && $post['allow_comments'] == 1 ? 'checked' : 'checked'; ?>>
                        <label class="form-check-label" for="allow_comments">
                            <?php echo LANG_TEMPLATE_POSTS_EDIT_ALLOW_COMMENTS_LABEL; ?>
                        </label>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_POSTS_EDIT_SEO_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_EDIT_SEO_TITLE_LABEL; ?></label>
                            <input type="text" class="form-control" name="seo_title" 
                                value="<?php echo html($post['seo_title'] ?? ''); ?>"
                                placeholder="<?php echo LANG_TEMPLATE_POSTS_EDIT_SEO_TITLE_PLACEHOLDER; ?>">
                            <div class="form-text"><?php echo LANG_TEMPLATE_POSTS_EDIT_SEO_TITLE_HINT; ?></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label"><?php echo LANG_TEMPLATE_POSTS_EDIT_META_DESC_LABEL; ?></label>
                            <textarea class="form-control" name="meta_description" rows="2"><?php echo html($post['meta_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_POSTS_EDIT_SUBMIT_BTN; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
    window.availablePostBlocks = <?php echo json_encode($postBlockManager->getPostBlocksForJS()); ?>;
    window.initialPostBlocks = <?php echo json_encode($preparedBlocks ?? array()); ?>;
    window.isEditMode = true;
    window.MAX_TAGS_PER_POST = <?php echo \SettingsHelper::get('controller_tags', 'max_tags_per_post', 10); ?>;
</script>
<?php admin_bottom_js(ob_get_clean()); ?>