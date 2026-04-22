<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'code-square', '24', '#000', 'me-2'); ?>
            <?php echo isset($block) ? LANG_TEMPLATE_HTMLBLOCKS_FORM_EDIT_TITLE : LANG_TEMPLATE_HTMLBLOCKS_FORM_CREATE_TITLE; ?>
            <?php if ($selectedType !== 'DefaultBlock') { ?>
                <span class="badge bg-primary ms-2">
                    <?php echo html($blockTypes[$selectedType]['name'] ?? $selectedType); ?>
                </span>
            <?php } ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/html-blocks" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_BACK_BTN; ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <form method="POST" id="blockForm" enctype="multipart/form-data">
                <input type="hidden" name="block_type" value="<?php echo $selectedType; ?>">
                
                <nav class="px-4 pt-4">
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-basic-tab" data-bs-toggle="tab" data-bs-target="#nav-basic" type="button" role="tab" aria-controls="nav-basic" aria-selected="true">
                            <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_TAB_BASIC; ?>
                        </button>
                        
                        <button class="nav-link" id="nav-settings-tab" data-bs-toggle="tab" data-bs-target="#nav-settings" type="button" role="tab" aria-controls="nav-settings" aria-selected="false">
                            <?php echo bloggy_icon('bs', 'gear', '16', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_TAB_SETTINGS; ?>
                        </button>
                        
                        <button class="nav-link" id="nav-assets-tab" data-bs-toggle="tab" data-bs-target="#nav-assets" type="button" role="tab" aria-controls="nav-assets" aria-selected="false">
                            <?php echo bloggy_icon('bs', 'palette', '16', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_TAB_ASSETS; ?>
                        </button>
                    </div>
                </nav>

                <div class="tab-content p-4" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-basic" role="tabpanel" aria-labelledby="nav-basic-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_BLOCK_NAME; ?></label>
                                    <input type="text" 
                                        name="name" 
                                        class="form-control form-control-lg" 
                                        value="<?php echo html($block['name'] ?? ''); ?>" 
                                        placeholder="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_BLOCK_NAME_PLACEHOLDER; ?>"
                                        required>
                                    <div class="form-text"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_BLOCK_NAME_HINT; ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold d-flex align-items-center">
                                        <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_NAME; ?>
                                        <span class="text-danger ms-1">*</span>
                                    </label>
                                    <input type="text" 
                                        name="slug" 
                                        class="form-control" 
                                        value="<?php echo html($block['slug'] ?? ''); ?>" 
                                        placeholder="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_NAME_PLACEHOLDER; ?>"
                                        <?php echo isset($block) ? 'disabled' : ''; ?>
                                        required>
                                    <div class="form-text"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_NAME_HINT; ?></div>
                                    <?php if (isset($block)) { ?>
                                        <input type="hidden" name="slug" value="<?php echo html($block['slug']); ?>">
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if ($selectedType !== 'DefaultBlock') { ?>
                                <div class="col-md-4">
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_TEMPLATE; ?></label>
                                        <select name="template" class="form-select" id="block-template-select">
                                            <?php foreach ($availableTemplates as $templateKey => $templateName) { ?>
                                                <option value="<?php echo html($templateKey); ?>" 
                                                    <?php echo ($selectedTemplate == $templateKey) ? 'selected' : ''; ?>>
                                                    <?php 
                                                    $displayName = preg_replace('/\s*\[.*?\]$/', '', $templateName);
                                                    echo html($displayName); 
                                                    ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div class="form-text">
                                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_TEMPLATE_HINT; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        
                        <?php if ($selectedType !== 'DefaultBlock') { ?>
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <?php echo bloggy_icon('bs', 'info-circle-fill', '16', '#000', 'me-2 mt-1'); ?>
                                    <div>
                                        <strong><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_BLOCK_TYPE; ?></strong> <?php echo html($blockTypes[$selectedType]['name'] ?? $selectedType); ?>
                                        <?php if (isset($blockTypes[$selectedType]['description'])) { ?>
                                            <br><small><?php echo html($blockTypes[$selectedType]['description']); ?></small>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="tab-pane fade" id="nav-settings" role="tabpanel" aria-labelledby="nav-settings-tab">
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_BLOCK_SETTINGS; ?></h6>
                            <div id="block-settings-container">
                                <?php 
                                if ($selectedType !== 'DefaultBlock') {
                                    $blockType = $blockTypes[$selectedType] ?? null;
                                    if ($blockType && $blockType['class']) {
                                        echo $blockType['class']->getSettingsForm($settings ?? []);
                                    }
                                } else {
                                    $html = $settings['html'] ?? '';
                                    $useFragment = !empty($settings['use_fragment']) ? true : false;
                                    $selectedFragment = $settings['selected_fragment'] ?? '';
                                    ?>
                                    <div class="mb-4 p-4 bg-light rounded border">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" 
                                                id="use_fragment" name="settings[use_fragment]" value="1"
                                                <?php echo $useFragment ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-semibold" for="use_fragment">
                                                <?php echo bloggy_icon('bs', 'puzzle', '18', '#0d6efd', 'me-2'); ?>
                                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_USE_FRAGMENT; ?>
                                            </label>
                                            <div class="form-text mt-1 ms-4"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_USE_FRAGMENT_HINT; ?></div>
                                        </div>
                                        
                                        <div id="fragment-selector" class="mt-3" style="display: <?php echo $useFragment ? 'block' : 'none'; ?>;">
                                            <div class="row mb-4">
                                                <div class="col-md-8">
                                                    <label class="form-label fw-semibold d-flex align-items-center">
                                                        <?php echo bloggy_icon('bs', 'list-ul', '16', '#0d6efd', 'me-2'); ?>
                                                        <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FRAGMENT; ?>
                                                    </label>
                                                    <select name="settings[selected_fragment]" id="selected_fragment" class="form-select" data-selected="<?php echo html($selectedFragment); ?>">
                                                        <option value=""><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FRAGMENT_OPTION; ?></option>
                                                    </select>
                                                    <div class="form-text"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FRAGMENT_HINT; ?></div>
                                                </div>
                                            </div>
                                            
                                            <div id="fragment-shortcodes" class="mt-4" style="display: <?php echo $useFragment ? 'block' : 'none'; ?>;">
                                                <div class="card border-0 bg-light">
                                                    <div class="card-header bg-white border-bottom py-2">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <?php echo bloggy_icon('bs', 'code-slash', '16', '#0d6efd', 'me-1'); ?>
                                                            <span class="fw-semibold"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_FRAGMENT_SHORTCODES; ?></span>
                                                            <span class="badge bg-secondary" id="shortcode-badge">0</span>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-3" id="shortcodes-list">
                                                        <div class="text-center text-muted py-3">
                                                            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#adb5bd', 'me-2'); ?>
                                                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FRAGMENT_PROMPT; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4" id="html-editor-container">
                                        <label class="form-label fw-semibold d-flex align-items-center">
                                            <?php echo bloggy_icon('bs', 'code', '18', '#0d6efd', 'me-2'); ?>
                                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_HTML_CODE; ?>
                                        </label>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_HTML_CODE_HINT; ?>
                                            </small>
                                        </div>
                                        <div class="border rounded overflow-hidden">
                                            <div id="default-block-html-editor" style="height: 400px; width: 100%;" class="ace-editor"><?php echo htmlspecialchars($html); ?></div>
                                        </div>
                                        <textarea name="settings[html]" id="default-block-html" style="display: none;"><?php echo htmlspecialchars($html); ?></textarea>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-assets" role="tabpanel" aria-labelledby="nav-assets-tab">
                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <?php echo bloggy_icon('bs', 'filetype-css', '16', '#1889d0', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_INLINE_CSS; ?>
                            </label>
                            <div class="mb-2">
                                <small class="text-muted"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_INLINE_CSS_HINT; ?></small>
                            </div>
                            <div id="inline-css-container" class="border rounded">
                                <div id="inline-css-editor" style="height: 200px;"></div>
                            </div>
                            <textarea name="inline_css" id="inline_css" style="display: none;"><?php echo html($inlineCss ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <?php echo bloggy_icon('bs', 'filetype-js', '16', '#1889d0', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_INLINE_JS; ?>
                            </label>
                            <div class="mb-2">
                                <small class="text-muted"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_INLINE_JS_HINT; ?></small>
                            </div>
                            <div id="inline-js-container" class="border rounded">
                                <div id="inline-js-editor" style="height: 200px;"></div>
                            </div>
                            <textarea name="inline_js" id="inline_js" style="display: none;"><?php echo html($inlineJs ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <?php echo bloggy_icon('bs', 'filetype-css', '16', '#1889d0', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_EXTERNAL_CSS; ?>
                                <?php if (!empty($systemCss)) { ?>
                                    <span class="badge bg-info ms-2" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_FILES_TOOLTIP; ?>"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_BADGE; ?></span>
                                <?php } ?>
                            </label>
                            <?php if (!empty($systemCss)) { ?>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_FILES_LABEL; ?></small>
                                <?php foreach ($systemCss as $systemCssFile) { ?>
                                    <div class="input-group mb-2">
                                        <input type="text"
                                            class="form-control system-asset"
                                            value="<?php echo html($systemCssFile); ?>"
                                            readonly
                                            placeholder="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_CSS_PLACEHOLDER; ?>">
                                        <span class="input-group-text text-muted bg-light">
                                            <?php echo bloggy_icon('bs', 'lock-fill', '16', '#6c757d', null, array('data-bs-toggle' => 'tooltip', 'title' => LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_FILE_TOOLTIP)); ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php } ?>
                            <small class="text-muted d-block mb-2"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_ADDITIONAL_CSS; ?></small>
                            <div id="css-files-container">
                                <?php if (!empty($cssFiles)) { ?>
                                    <?php foreach ($cssFiles as $index => $cssFile) { ?>
                                        <?php if (!in_array($cssFile, $systemCss)) { ?>
                                        <div class="input-group mb-2 css-file-row">
                                            <input type="text" name="css_files[]" class="form-control asset-path-input" value="<?php echo html($cssFile); ?>" placeholder="templates/default/front/assets/css/my-block.css">
                                            <button type="button" class="btn btn-outline-primary select-asset-btn" data-asset-type="css" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FROM_FOLDER; ?>">
                                                <?php echo bloggy_icon('bs', 'folder2-open', '16', '#000'); ?>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger remove-asset" data-type="css">
                                                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                            </button>
                                        </div>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <div class="input-group mb-2 css-file-row">
                                    <input type="text" name="css_files[]" class="form-control asset-path-input" value="" placeholder="templates/default/front/assets/css/my-block.css">
                                    <button type="button" class="btn btn-outline-primary select-asset-btn" data-asset-type="css" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FROM_FOLDER; ?>">
                                        <?php echo bloggy_icon('bs', 'folder2-open', '16', '#000'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger remove-asset" data-type="css">
                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-css-file">
                                <?php echo bloggy_icon('bs', 'plus', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_ADD_CSS_BTN; ?>
                            </button>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <?php echo bloggy_icon('bs', 'filetype-js', '16', '#1889d0', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_EXTERNAL_JS; ?>
                                <?php if (!empty($systemJs)) { ?>
                                    <span class="badge bg-info ms-2" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_FILES_TOOLTIP; ?>"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_BADGE; ?></span>
                                <?php } ?>
                            </label>
                            <?php if (!empty($systemJs)) { ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_FILES_LABEL; ?></small>
                                    <?php foreach ($systemJs as $systemJsFile) { ?>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control system-asset" value="<?php echo html($systemJsFile); ?>" readonly placeholder="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_JS_PLACEHOLDER; ?>">
                                            <span class="input-group-text text-muted bg-light">
                                                <?php echo bloggy_icon('bs', 'lock-fill', '16', '#6c757d', null, array('data-bs-toggle' => 'tooltip', 'title' => LANG_TEMPLATE_HTMLBLOCKS_FORM_SYSTEM_FILE_TOOLTIP)); ?>
                                            </span>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <small class="text-muted d-block mb-2"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_ADDITIONAL_JS; ?></small>
                            <div id="js-files-container">
                                <?php if (!empty($jsFiles)) { ?>
                                    <?php foreach ($jsFiles as $index => $jsFile) { ?>
                                        <?php if (!in_array($jsFile, $systemJs)) { ?>
                                            <div class="input-group mb-2 js-file-row">
                                                <input type="text" name="js_files[]" class="form-control asset-path-input" value="<?php echo html($jsFile); ?>" placeholder="templates/default/front/assets/js/my-block.js">
                                                <button type="button" class="btn btn-outline-primary select-asset-btn" data-asset-type="js" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FROM_FOLDER; ?>">
                                                    <?php echo bloggy_icon('bs', 'folder2-open', '16', '#000'); ?>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger remove-asset" data-type="js">
                                                    <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <div class="input-group mb-2 js-file-row">
                                    <input type="text"
                                        name="js_files[]"
                                        class="form-control asset-path-input"
                                        value=""
                                        placeholder="templates/default/front/assets/js/my-block.js">
                                    <button type="button" class="btn btn-outline-primary select-asset-btn" 
                                            data-asset-type="js"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FROM_FOLDER; ?>">
                                        <?php echo bloggy_icon('bs', 'folder2-open', '16', '#000'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger remove-asset" data-type="js">
                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-js-file">
                                <?php echo bloggy_icon('bs', 'plus', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_ADD_JS_BTN; ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="border-top px-4 py-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <?php echo bloggy_icon('bs', 'clock-history', '16', '#6c757d', 'me-1'); ?>
                            <?php if (isset($block['updated_at'])) { ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_LAST_MODIFIED; ?> <?php echo date('d.m.Y H:i', strtotime($block['updated_at'])); ?>
                            <?php } else { ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_CREATING_NEW; ?>
                            <?php } ?>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo ADMIN_URL; ?>/html-blocks" class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'x-lg', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_CANCEL_BTN; ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SAVE_BTN; ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="assetSelectorModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-folder2-open me-2"></i>
                                    <span id="asset-modal-title"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_SELECT_FILE; ?></span>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_BLOCK_TYPE_LABEL; ?></label>
                                    <input type="text" class="form-control" id="asset-block-type" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_ASSET_TYPE_LABEL; ?></label>
                                    <input type="text" class="form-control" id="asset-type-display" readonly>
                                </div>
                                <div id="asset-files-list" class="list-group">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2 text-muted"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_LOADING_FILES; ?></p>
                                    </div>
                                </div>
                                <div id="asset-no-files" class="alert alert-warning" style="display: none;">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_NO_FILES; ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_CANCEL_BTN; ?></button>
                                <button type="button" class="btn btn-primary" id="asset-manual-path">
                                    <i class="bi bi-pencil me-1"></i><?php echo LANG_TEMPLATE_HTMLBLOCKS_FORM_ENTER_PATH; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="current-asset-input">

            </form>
        </div>
    </div>
</div>

<?php
    add_admin_js('templates/default/admin/assets/js/controllers/ace.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-html.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-css.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-javascript.js');
    add_admin_js('templates/default/admin/assets/js/controllers/theme-monokai.js');
    add_admin_js('templates/default/admin/assets/js/controllers/conditional-fields.js');
    add_admin_js('templates/default/admin/assets/js/controllers/icon-field.js');
    add_admin_js('templates/default/admin/assets/js/controllers/html-block-form.js');
?>