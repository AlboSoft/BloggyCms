<?php
    add_admin_js('templates/default/admin/assets/js/controllers/form-builder.js');
    add_admin_css('templates/default/admin/assets/css/form-builder.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', ($isEdit ? 'pencil' : 'plus-circle'), '20', '#000', 'me-2'); ?>
            <?php echo $isEdit ? LANG_TEMPLATE_FORMS_FORM_EDIT_TITLE : LANG_TEMPLATE_FORMS_FORM_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/forms" class="btn btn-outline-secondary">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_FORMS_FORM_BACK_BTN; ?>
        </a>
    </div>

    <form method="POST" id="form-builder-form" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'puzzle', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_FORM_BUILDER_TITLE; ?>
                        </h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    data-bs-toggle="modal" data-bs-target="#addFieldModal">
                                <?php echo bloggy_icon('bs', 'plus-circle', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_ADD_FIELD_BTN; ?>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="form-builder">
                            <div id="form-fields-container" class="sortable-fields">
                                <?php if (!empty($formStructure)) { ?>
                                    <?php foreach ($formStructure as $index => $field) { ?>
                                        <?php echo $this->renderFormField($field, $index); ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <div id="form-empty" class="text-center text-muted p-5">
                                        <div class="mb-3">
                                            <?php echo bloggy_icon('bs', 'ui-checks', '48', '#6C6C6C'); ?>
                                        </div>
                                        <h5 class="text-muted"><?php echo LANG_TEMPLATE_FORMS_FORM_EMPTY_TITLE; ?></h5>
                                        <p class="text-muted mb-3"><?php echo LANG_TEMPLATE_FORMS_FORM_EMPTY_TEXT; ?></p>
                                        <button type="button" class="btn btn-primary" 
                                                data-bs-toggle="modal" data-bs-target="#addFieldModal">
                                            <?php echo bloggy_icon('bs', 'plus-circle', '16', '#fff', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_FORM_ADD_FIELD_BTN; ?>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'gear', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_FORM_MAIN_SETTINGS_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'tag', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_NAME_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="name" 
                                   value="<?php echo html($form['name'] ?? ''); ?>" 
                                   required
                                   placeholder="<?php echo LANG_TEMPLATE_FORMS_FORM_NAME_PLACEHOLDER; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'card-text', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_DESCRIPTION_LABEL; ?>
                            </label>
                            <textarea class="form-control" 
                                      name="description" 
                                      rows="2"
                                      placeholder="<?php echo LANG_TEMPLATE_FORMS_FORM_DESCRIPTION_PLACEHOLDER; ?>"><?php echo html($form['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'check-circle', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_SUCCESS_MESSAGE_LABEL; ?>
                            </label>
                            <textarea class="form-control" 
                                      name="success_message" 
                                      rows="2"
                                      placeholder="<?php echo LANG_TEMPLATE_FORMS_FORM_SUCCESS_MESSAGE_PLACEHOLDER; ?>"><?php echo html($form['success_message'] ?? LANG_TEMPLATE_FORMS_FORM_DEFAULT_SUCCESS_MESSAGE); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_ERROR_MESSAGE_LABEL; ?>
                            </label>
                            <textarea class="form-control" 
                                      name="error_message" 
                                      rows="2"
                                      placeholder="<?php echo LANG_TEMPLATE_FORMS_FORM_ERROR_MESSAGE_PLACEHOLDER; ?>"><?php echo html($form['error_message'] ?? LANG_TEMPLATE_FORMS_FORM_DEFAULT_ERROR_MESSAGE); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'power', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_STATUS_LABEL; ?>
                            </label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo ($form['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_FORMS_FORM_STATUS_ACTIVE; ?></option>
                                <option value="inactive" <?php echo ($form['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_FORMS_FORM_STATUS_INACTIVE; ?></option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'layout-wtf', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_TEMPLATE_LABEL; ?>
                            </label>
                            <select class="form-select" name="template">
                                <?php if (!empty($templates)) { ?>
                                <?php foreach ($templates as $templateKey => $templateName) { ?>
                                    <option value="<?php echo $templateKey; ?>"
                                        <?php echo ($form['template'] ?? 'default') === $templateKey ? 'selected' : ''; ?>>
                                        <?php echo html($templateName); ?>
                                    </option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                            <div class="form-text">
                                <?php echo bloggy_icon('bs', 'folder', '16', '#000', 'me-1'); ?>
                                <?php echo sprintf(LANG_TEMPLATE_FORMS_FORM_TEMPLATE_HINT, html($currentTheme)); ?>
                            </div>
                            <?php if (empty($templates) || count($templates) <= 1) { ?>
                            <div class="alert alert-info mt-2 p-2 small">
                                <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_TEMPLATE_INFO; ?>
                            </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'toggle-on', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_FORM_EXTRA_SETTINGS_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="show_labels" 
                                   id="show_labels"
                                   <?php echo !empty($form['settings']['show_labels'] ?? true) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="show_labels">
                                <?php echo LANG_TEMPLATE_FORMS_FORM_SHOW_LABELS; ?>
                            </label>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="show_descriptions" 
                                   id="show_descriptions"
                                   <?php echo !empty($form['settings']['show_descriptions'] ?? true) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="show_descriptions">
                                <?php echo LANG_TEMPLATE_FORMS_FORM_SHOW_DESCRIPTIONS; ?>
                            </label>
                        </div>
                        
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>
                                <?php echo $isEdit ? LANG_TEMPLATE_FORMS_FORM_UPDATE_BTN : LANG_TEMPLATE_FORMS_FORM_CREATE_BTN; ?>
                            </button>
                            
                            <?php if ($isEdit) { ?>
                            <a href="<?php echo ADMIN_URL; ?>/forms/preview/<?php echo $form['id']; ?>" 
                               class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_PREVIEW_BTN; ?>
                            </a>
                            <a href="<?php echo ADMIN_URL; ?>/forms/settings/<?php echo $form['id']; ?>" 
                               class="btn btn-outline-info">
                                <?php echo bloggy_icon('bs', 'gear', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_FORM_EXTRA_SETTINGS_BTN; ?>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($isEdit) { ?>
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'graph-up', '16', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_FORM_STATS_TITLE; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $fieldsCount = count($formStructure);
                        $submissionsCount = $formModel->getSubmissionsCount($form['id']);
                        ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <div class="h4 mb-0"><?php echo $fieldsCount; ?></div>
                                    <small class="text-muted"><?php echo LANG_TEMPLATE_FORMS_FORM_STATS_FIELDS; ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0"><?php echo $submissionsCount; ?></div>
                                <small class="text-muted"><?php echo LANG_TEMPLATE_FORMS_FORM_STATS_SUBMISSIONS; ?></small>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted"><?php echo LANG_TEMPLATE_FORMS_FORM_SLUG_LABEL; ?></small>
                            <div class="input-group input-group-sm mt-1">
                                <input type="text" class="form-control" value="<?php echo html($form['slug']); ?>" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="copySlug()">
                                    <?php echo bloggy_icon('bs', 'copy', '16', '#000'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        
        <input type="hidden" name="form_structure" id="form-structure" value='<?php echo json_encode($formStructure ?? [], JSON_UNESCAPED_UNICODE); ?>'>
    </form>
</div>

<div class="modal fade" id="addFieldModal" tabindex="-1" aria-labelledby="addFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFieldModalLabel">
                    <?php echo bloggy_icon('bs', 'plus-circle', '20', '#000', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_MODAL_ADD_FIELD_TITLE; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo LANG_TEMPLATE_FORMS_MODAL_CLOSE; ?>"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">
                            <?php echo bloggy_icon('bs', 'list-ul', '16', '#000', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_TYPE; ?>
                            <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2" id="field-type-selector">
                            <?php foreach ($fieldTypes as $type => $typeInfo) { ?>
                                <div class="col-4 col-md-3">
                                    <div class="field-type-card card h-100" 
                                         data-type="<?php echo $type; ?>"
                                         data-has-options="<?php echo $typeInfo['has_options'] ? '1' : '0'; ?>"
                                         data-has-placeholder="<?php echo $typeInfo['has_placeholder'] ? '1' : '0'; ?>">
                                        <div class="card-body text-center p-2">
                                            <div class="mb-2">
                                                <?php echo bloggy_icon('bs', $typeInfo['icon'], '24', '#000'); ?>
                                            </div>
                                            <div class="small"><?php echo html($typeInfo['label']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mt-3" id="field-settings" style="display: none;">
                        <form id="field-settings-form">
                            <input type="hidden" id="field-type" name="type">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'fonts', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_LABEL; ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="field-label" 
                                           name="label"
                                           placeholder="<?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_LABEL_PLACEHOLDER; ?>"
                                           required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'code-slash', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_NAME; ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="field-name" 
                                           name="name"
                                           placeholder="<?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_NAME_PLACEHOLDER; ?>"
                                           required
                                           pattern="[a-zA-Z0-9_]+">
                                    <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_NAME_HINT; ?></div>
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'card-text', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_DESCRIPTION; ?>
                                    </label>
                                    <textarea class="form-control" 
                                              id="field-description" 
                                              name="description"
                                              rows="2"
                                              placeholder="<?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_DESCRIPTION_PLACEHOLDER; ?>"></textarea>
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'palette', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_CSS_CLASS; ?>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="field-class" 
                                           name="class"
                                           placeholder="<?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_CSS_PLACEHOLDER; ?>">
                                    <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_CSS_HINT; ?></div>
                                </div>
                                
                                <div id="dynamic-settings"></div>
                                
                                <div class="col-md-12 mt-3">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <?php echo bloggy_icon('bs', 'shield-check', '16', '#000', 'me-2'); ?>
                                        <?php echo LANG_TEMPLATE_FORMS_MODAL_VALIDATION_TITLE; ?>
                                    </h6>
                                    
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="field-required" 
                                                       name="required">
                                                <label class="form-check-label" for="field-required">
                                                    <?php echo LANG_TEMPLATE_FORMS_MODAL_FIELD_REQUIRED; ?>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label small"><?php echo LANG_TEMPLATE_FORMS_MODAL_VALIDATION_RULES; ?></label>
                                            <div id="validation-rules"></div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" 
                                                    onclick="addValidationRule()">
                                                <?php echo bloggy_icon('bs', 'plus-circle', '16', '#000', 'me-1'); ?>
                                                <?php echo LANG_TEMPLATE_FORMS_MODAL_ADD_RULE; ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?php echo bloggy_icon('bs', 'x-circle', '16', '#000', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_MODAL_CANCEL; ?>
                </button>
                <button type="button" class="btn btn-primary" id="save-field-btn" disabled>
                    <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_MODAL_ADD_FIELD; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    function copySlug() {
        const slugInput = document.querySelector('input[value="<?php echo html($form['slug'] ?? ''); ?>"]');
        if (slugInput) {
            slugInput.select();
            document.execCommand('copy');
            alert('<?php echo LANG_TEMPLATE_FORMS_FORM_SLUG_COPIED; ?>');
        }
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>