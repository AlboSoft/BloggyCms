<?php
    add_admin_js('templates/default/admin/assets/js/controllers/ace.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-html.js');
    add_admin_js('templates/default/admin/assets/js/controllers/theme-monokai.js');
    add_admin_js('templates/default/admin/assets/js/controllers/postblocks.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'gear', '24', '#000', 'me-2'); ?>
            <?php echo sprintf(LANG_TEMPLATE_POSTBLOCKS_EDIT_TITLE, html($postBlock['name'])); ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/post-blocks" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_BACK_BTN; ?>
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_MAIN_SETTINGS_TITLE; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="blockSettingsForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enable_in_posts" 
                                           id="enable_in_posts" <?php echo $settings['enable_in_posts'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_in_posts">
                                        <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_ENABLE_IN_POSTS; ?>
                                    </label>
                                </div>
                                <div class="form-text">
                                    <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_ENABLE_IN_POSTS_HINT; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enable_in_pages" 
                                           id="enable_in_pages" <?php echo $settings['enable_in_pages'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_in_pages">
                                        <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_ENABLE_IN_PAGES; ?>
                                    </label>
                                </div>
                                <div class="form-text">
                                    <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_ENABLE_IN_PAGES_HINT; ?>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_BLOCK_TEMPLATE; ?></label>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_TEMPLATE_HINT; ?></small>
                                    </div>
                                    <button type="button" id="load-template" class="btn btn-sm btn-outline-secondary">
                                        <?php echo bloggy_icon('bs', 'arrow-clockwise', '14', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_LOAD_TEMPLATE_BTN; ?>
                                    </button>
                                </div>
                                <div id="template-editor" style="height: 400px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                                <textarea name="template" id="template" style="display: none;"><?php echo html($settings['template']); ?></textarea>
                                <div class="form-text">
                                    <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_TEMPLATE_DESC; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_SAVE_BTN; ?>
                            </button>
                        </div>
                    </form>

                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESETS_TITLE; ?></h5>
                            <button type="button" class="btn btn-sm btn-success" id="add-preset-btn">
                                <?php echo bloggy_icon('bs', 'plus-circle', '14', '#fff', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_ADD_PRESET_BTN; ?>
                            </button>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div id="presets-container">
                                    <div class="text-center text-muted py-4" id="no-presets-message">
                                        <?php echo bloggy_icon('bs', 'pencil', '32', '#6C6C6C', 'mb-2'); ?>
                                        <p class="mb-0"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_NO_PRESETS; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_BLOCK_INFO_TITLE; ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="post-block-icon me-3">
                            <?php 
                            $iconClass = $postBlock['icon'] ?? 'bi bi-puzzle';
                            $iconName = str_replace('bi bi-', '', $iconClass);
                            echo bloggy_icon('bs', $iconName, '24', '#000');
                            ?>
                        </div>
                        <div>
                            <h6 class="mb-1"><?php echo html($postBlock['name']); ?></h6>
                            <p class="text-muted small mb-0"><?php echo html($postBlock['description']); ?></p>
                        </div>
                    </div>
                    
                    <div class="small">
                        <div class="mb-2">
                            <strong><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_SYSTEM_NAME_LABEL; ?></strong>
                            <code class="d-block mt-1"><?php echo $postBlock['system_name']; ?></code>
                        </div>
                        <div class="mb-2">
                            <strong><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_CATEGORY_LABEL; ?></strong>
                            <span class="badge bg-secondary"><?php echo $postBlock['category']; ?></span>
                        </div>
                        <div class="mb-2">
                            <strong><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_VERSION_LABEL; ?></strong> <?php echo $postBlock['version']; ?>
                        </div>
                        <div class="mb-2">
                            <strong><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_AUTHOR_LABEL; ?></strong> <?php echo $postBlock['author']; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_SHORTCODES_TITLE; ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($shortcodes)) { ?>
                        <p class="text-muted mb-0"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_NO_SHORTCODES; ?></p>
                    <?php } else { ?>
                        <div class="shortcodes-list">
                            <?php foreach ($shortcodes as $shortcode => $description) { ?>
                                <div class="shortcode-item mb-3 p-3 border rounded">
                                    <code class="text-primary d-block mb-1 shortcode-insert" data-shortcode="<?php echo html($shortcode); ?>"><?php echo html($shortcode); ?></code>
                                    <div class="text-muted small"><?php echo html($description); ?></div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="presetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="presetModalLabel"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESET_MODAL_TITLE; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="presetForm">
                    <input type="hidden" name="preset_id" id="preset_id">
                    <input type="hidden" name="system_name" id="preset_system_name" value="<?php echo $postBlock['system_name']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESET_NAME_LABEL; ?></label>
                        <input type="text" class="form-control" name="preset_name" id="preset_name" required>
                        <div class="form-text"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESET_NAME_HINT; ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESET_TEMPLATE_LABEL; ?></label>
                        <div id="preset-template-editor" style="height: 300px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                        <textarea name="preset_template" id="preset_template" style="display: none;"></textarea>
                        <div class="form-text">
                            <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESET_TEMPLATE_HINT; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESET_MODAL_CANCEL; ?></button>
                <button type="button" class="btn btn-danger" id="delete-preset-btn" style="display: none;">
                    <?php echo bloggy_icon('bs', 'trash', '14', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESET_DELETE_BTN; ?>
                </button>
                <button type="button" class="btn btn-primary" id="save-preset-btn">
                    <?php echo bloggy_icon('bs', 'check-lg', '14', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_POSTBLOCKS_EDIT_PRESET_SAVE_BTN; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="postblock-data" 
     data-system-name="<?php echo $postBlock['system_name']; ?>"
     data-admin-url="<?php echo ADMIN_URL; ?>"
     style="display: none;">
</div>