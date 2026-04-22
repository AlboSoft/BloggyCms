<?php
    add_admin_js('templates/default/admin/assets/js/controllers/fields-form.js');
    
    $config = array();
    if (isset($field) && !empty($field['config'])) {
        if (is_array($field['config'])) {
            $config = $field['config'];
        } elseif (is_string($field['config'])) {
            $config = json_decode($field['config'], true) ?: array();
        }
    }
?>
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', $isEdit ? 'pencil-square' : 'plus-circle', '24', '#000', 'me-2'); ?>
            <?php echo $isEdit ? LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_EDIT_TITLE : LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/fragments/fields/<?php echo $fragment['id']; ?>" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_BACK_BTN; ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_NAME_LABEL; ?> <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   value="<?php echo html($field['name'] ?? ''); ?>"
                                   required>
                            <div class="form-text"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_NAME_HINT; ?></div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_SYSTEM_NAME_LABEL; ?> <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="system_name" 
                                   class="form-control" 
                                   value="<?php echo html($field['system_name'] ?? ''); ?>"
                                   pattern="[a-z0-9_]+"
                                   required>
                            <div class="form-text"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_SYSTEM_NAME_HINT; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_TYPE_LABEL; ?> <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" id="field-type">
                                <?php foreach ($fieldTypes as $type => $typeName) { ?>
                                    <option value="<?php echo html($type); ?>" 
                                            <?php echo (($field['type'] ?? 'string') == $type) ? 'selected' : ''; ?>>
                                        <?php echo html($typeName); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_DESCRIPTION_LABEL; ?></label>
                            <input type="text" 
                                   name="description" 
                                   class="form-control" 
                                   value="<?php echo html($field['description'] ?? ''); ?>"
                                   placeholder="<?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_DESCRIPTION_PLACEHOLDER; ?>">
                            <div class="form-text"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_DESCRIPTION_HINT; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" 
                                       name="is_required" 
                                       class="form-check-input" 
                                       id="is_required"
                                       value="1"
                                       <?php echo (!empty($field['is_required'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_required"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_REQUIRED_LABEL; ?></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" 
                                       name="is_active" 
                                       class="form-check-input" 
                                       id="is_active"
                                       value="1"
                                       <?php echo (!isset($field['is_active']) || $field['is_active'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_ACTIVE_LABEL; ?></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" 
                                       name="show_in_list" 
                                       class="form-check-input" 
                                       id="show_in_list"
                                       value="1"
                                       <?php echo (!empty($field['show_in_list'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_in_list"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_SHOW_IN_LIST_LABEL; ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3" id="field-settings">
                    <label class="form-label"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_SETTINGS_LABEL; ?></label>
                    <div id="field-settings-content">
                        <div class="alert alert-info"><?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_SETTINGS_HINT; ?></div>
                    </div>
                </div>
                
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="<?php echo ADMIN_URL; ?>/fragments/fields/<?php echo $fragment['id']; ?>" class="btn btn-secondary">
                        <?php echo LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_CANCEL_BTN; ?>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-2'); ?>
                        <?php echo $isEdit ? LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_SAVE_BTN : LANG_TEMPLATE_FRAGMENTS_FIELD_FORM_CREATE_BTN; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>