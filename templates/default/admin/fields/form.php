<?php
    add_admin_js('templates/default/admin/assets/js/controllers/fields-form.js');
    $config = isset($field) && !empty($field['config']) ? json_decode($field['config'], true) : array();
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'plus-circle', '24', '#000', 'me-2'); ?>
            <?php echo isset($field) ? LANG_TEMPLATE_FIELDS_FORM_EDIT_TITLE : LANG_TEMPLATE_FIELDS_FORM_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/fields/entity/<?php echo $entityType; ?>" class="btn btn-outline-secondary">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_FIELDS_FORM_BACK_BTN; ?>
        </a>
    </div>

    <form method="post" id="field-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_FIELDS_FORM_MAIN_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo LANG_TEMPLATE_FIELDS_FORM_NAME_LABEL; ?> *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="name" 
                                           value="<?php echo isset($field) ? html($field['name']) : (isset($data['name']) ? html($data['name']) : ''); ?>" 
                                           placeholder="<?php echo LANG_TEMPLATE_FIELDS_FORM_NAME_PLACEHOLDER; ?>"
                                           required>
                                    <div class="form-text"><?php echo LANG_TEMPLATE_FIELDS_FORM_NAME_HINT; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo LANG_TEMPLATE_FIELDS_FORM_SYSTEM_NAME_LABEL; ?> *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="system_name" 
                                           value="<?php echo isset($field) ? html($field['system_name']) : (isset($data['system_name']) ? html($data['system_name']) : ''); ?>" 
                                           placeholder="<?php echo LANG_TEMPLATE_FIELDS_FORM_SYSTEM_NAME_PLACEHOLDER; ?>"
                                           pattern="[a-z0-9_]+"
                                           required>
                                    <div class="form-text"><?php echo LANG_TEMPLATE_FIELDS_FORM_SYSTEM_NAME_HINT; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FIELDS_FORM_TYPE_LABEL; ?> *</label>
                            <select class="form-select" name="type" id="field-type" required>
                                <option value=""><?php echo LANG_TEMPLATE_FIELDS_FORM_TYPE_SELECT; ?></option>
                                <?php foreach($fieldTypes as $type => $name) { ?>
                                    <option value="<?php echo $type; ?>" 
                                        <?php echo (isset($field) && $field['type'] == $type) ? 'selected' : ((isset($data['type']) && $data['type'] == $type) ? 'selected' : ''); ?>>
                                        <?php echo html($name); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FIELDS_FORM_DESCRIPTION_LABEL; ?></label>
                            <textarea class="form-control" 
                                      name="description" 
                                      rows="2" 
                                      placeholder="<?php echo LANG_TEMPLATE_FIELDS_FORM_DESCRIPTION_PLACEHOLDER; ?>"><?php echo isset($field) ? html($field['description']) : (isset($data['description']) ? html($data['description']) : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" id="field-settings">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_FIELDS_FORM_SETTINGS_TITLE; ?></h5>
                    </div>
                    <div class="card-body" id="field-settings-content">
                        <?php if (isset($field) && !empty($field['type'])) { ?>
                            <?php
                            $fieldManager = new FieldManager($this->db);
                            $fieldInstance = $fieldManager->getFieldInstance(
                                $field['type'], 
                                $config
                            );
                            if ($fieldInstance) {
                                echo $fieldInstance->getSettingsForm();
                            }
                            ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_FIELDS_FORM_PARAMS_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FIELDS_FORM_SORT_ORDER_LABEL; ?></label>
                            <input type="number" 
                                class="form-control" 
                                name="sort_order" 
                                value="<?php echo isset($field) ? html($field['sort_order']) : (isset($data['sort_order']) ? html($data['sort_order']) : '0'); ?>" 
                                min="0">
                            <div class="form-text"><?php echo LANG_TEMPLATE_FIELDS_FORM_SORT_ORDER_HINT; ?></div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="is_required" 
                                    id="is_required"
                                    value="1"
                                    <?php echo (isset($field) && $field['is_required']) ? 'checked' : ((isset($data['is_required']) && $data['is_required']) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="is_required">
                                    <?php echo LANG_TEMPLATE_FIELDS_FORM_REQUIRED_LABEL; ?>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="is_active" 
                                    id="is_active"
                                    value="1"
                                    <?php echo (!isset($field) || $field['is_active']) ? 'checked' : ((isset($data['is_active']) && $data['is_active']) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="is_active">
                                    <?php echo LANG_TEMPLATE_FIELDS_FORM_ACTIVE_LABEL; ?>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="show_in_post" 
                                    id="show_in_post"
                                    value="1"
                                    <?php echo (!isset($field) || ($field['show_in_post'] ?? true)) ? 'checked' : ((isset($data['show_in_post']) && $data['show_in_post']) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="show_in_post">
                                    <?php echo LANG_TEMPLATE_FIELDS_FORM_SHOW_IN_POST_LABEL; ?>
                                </label>
                                <div class="form-text"><?php echo LANG_TEMPLATE_FIELDS_FORM_SHOW_IN_POST_HINT; ?></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="show_in_list" 
                                    id="show_in_list"
                                    value="1"
                                    <?php echo (isset($field) && $field['show_in_list']) ? 'checked' : ((isset($data['show_in_list']) && $data['show_in_list']) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="show_in_list">
                                    <?php echo LANG_TEMPLATE_FIELDS_FORM_SHOW_IN_LIST_LABEL; ?>
                                </label>
                                <div class="form-text"><?php echo LANG_TEMPLATE_FIELDS_FORM_SHOW_IN_LIST_HINT; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>
                        <?php echo isset($field) ? LANG_TEMPLATE_FIELDS_FORM_UPDATE_BTN : LANG_TEMPLATE_FIELDS_FORM_CREATE_BTN; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>