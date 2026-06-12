<?php
    add_admin_js('templates/default/admin/assets/js/controllers/menu-form.js');
    add_admin_js('templates/default/admin/assets/js/controllers/ace.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-html.js');
    add_admin_js('templates/default/admin/assets/js/controllers/theme-monokai.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', isset($menu['id']) ? 'gear' : 'plus-circle', '24', '#000', 'me-2'); ?>
            <?php echo isset($menu['id']) ? sprintf(LANG_TEMPLATE_MENU_FORM_EDIT_TITLE, html($menu['name'])) : LANG_TEMPLATE_MENU_FORM_CREATE_TITLE; ?>
        </h4>
        <div>
            <?php if (isset($menu['id'])) { ?>
                <a href="<?php echo ADMIN_URL; ?>/menu/items/<?php echo $menu['id']; ?>" class="btn btn-primary me-2">
                    <?php echo bloggy_icon('bs', 'list-ul', '16', '#fff', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_MENU_FORM_MANAGE_ITEMS_BTN; ?>
                </a>
            <?php } ?>
            <a href="<?php echo ADMIN_URL; ?>/menu" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_MENU_FORM_BACK_BTN; ?>
            </a>
        </div>
    </div>

    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-2'); ?>
            <div>
                <strong><?php echo LANG_TEMPLATE_MENU_FORM_CURRENT_THEME; ?></strong> <?php echo html($currentTheme); ?>
                <div class="small"><?php echo LANG_TEMPLATE_MENU_FORM_TEMPLATES_PATH; ?> <code>templates/<?php echo html($currentTheme); ?>/front/assets/menu/</code></div>
            </div>
        </div>
    </div>

    <?php if (empty($availableTemplates)) { ?>
    <div class="alert alert-warning">
        <div class="d-flex align-items-center">
            <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-2'); ?>
            <div>
                <strong><?php echo LANG_TEMPLATE_MENU_FORM_NO_TEMPLATES_TITLE; ?></strong>
                <div class="small">
                    <?php echo LANG_TEMPLATE_MENU_FORM_NO_TEMPLATES_TEXT; ?>
                    <br><?php echo LANG_TEMPLATE_MENU_FORM_NO_TEMPLATES_EXAMPLE; ?>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <form method="POST" id="menu-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'tag', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_NAME_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="name" 
                                   value="<?php echo html($menu['name'] ?? ''); ?>" 
                                   required
                                   placeholder="<?php echo LANG_TEMPLATE_MENU_FORM_NAME_PLACEHOLDER; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       name="use_custom_template" 
                                       id="use_custom_template" 
                                       value="1"
                                       <?php echo ($useCustomTemplate ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-semibold" for="use_custom_template">
                                    <?php echo bloggy_icon('bs', 'code-square', '16', '#0d6efd', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_USE_CUSTOM_TEMPLATE; ?>
                                </label>
                                <div class="form-text">
                                    <?php echo LANG_TEMPLATE_MENU_FORM_USE_CUSTOM_TEMPLATE_HINT; ?>
                                </div>
                            </div>
                        </div>

                        <div id="custom-template-container" style="<?php echo ($useCustomTemplate ?? false) ? '' : 'display: none;'; ?>">
                            <div class="mb-4">
                                <label class="form-label fw-semibold d-flex align-items-center">
                                    <?php echo bloggy_icon('bs', 'code', '16', '#0d6efd', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_LABEL; ?>
                                </label>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_HINT; ?><br>
                                        <code>{li}...{/li}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_NORMAL; ?><br>
                                        <code>{li=sub}...{/li=sub}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SUB; ?><br>
                                        <code>{li-extra}...{/li-extra}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_EXTRA; ?><br>
                                        <code>{li=children}...{/li=children}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_CHILDREN; ?><br>
                                        <code>{li=children-item}...{/li=children-item}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_CHILDREN_ITEM; ?>
                                    </small>
                                </div>
                                
                                <div id="custom-template-editor" style="height: 400px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                                <textarea name="custom_template" id="custom_template" style="display: none;"><?php echo html($customTemplate ?? ''); ?></textarea>
                                
                                <div class="form-text mt-2">
                                    <strong><?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODES_TITLE; ?></strong>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <code>{url}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_URL; ?><br>
                                            <code>{title}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_TITLE; ?><br>
                                            <code>{desc}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_DESC; ?><br>
                                            <code>{target}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_TARGET; ?><br>
                                            <code>{class}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_CLASS; ?><br>
                                            <code>{icon}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_ICON; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <code>{active_class}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_ACTIVE; ?><br>
                                            <code>{has_children}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_HAS_CHILDREN; ?><br>
                                            <code>{level}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_LEVEL; ?><br>
                                            <code>{children}</code> - <?php echo LANG_TEMPLATE_MENU_FORM_CUSTOM_TEMPLATE_SHORTCODE_CHILDREN; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="template-select-container" style="<?php echo ($useCustomTemplate ?? false) ? 'display: none;' : ''; ?>">
                            <div class="mb-4">
                                <label class="form-label">
                                    <?php echo bloggy_icon('bs', 'layout-wtf', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_TEMPLATE_LABEL; ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="template" <?php echo ($useCustomTemplate ?? false) ? 'disabled' : ''; ?>>
                                    <option value=""><?php echo LANG_TEMPLATE_MENU_FORM_SELECT_TEMPLATE; ?></option>
                                    <?php foreach ($availableTemplates as $templateKey => $templateName) { ?>
                                        <option value="<?php echo $templateKey; ?>" 
                                            <?php echo ($menu['template'] ?? '') === $templateKey ? 'selected' : ''; ?>>
                                            <?php echo html($templateName); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text">
                                    <?php echo bloggy_icon('bs', 'folder', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_TEMPLATE_PATH; ?> <code>templates/<?php echo html($currentTheme); ?>/front/assets/menu/</code>
                                </div>
                                <?php if (empty($availableTemplates)) { ?>
                                <div class="alert alert-warning mt-2 p-2 small">
                                    <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_NO_TEMPLATES_WARNING; ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'power', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_STATUS_LABEL; ?>
                            </label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo ($menu['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_STATUS_ACTIVE; ?>
                                </option>
                                <option value="inactive" <?php echo ($menu['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_STATUS_INACTIVE; ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>
                                <?php echo isset($menu['id']) ? LANG_TEMPLATE_MENU_FORM_UPDATE_BTN : LANG_TEMPLATE_MENU_FORM_CREATE_BTN; ?>
                            </button>
                            
                            <?php if (isset($menu['id'])) { ?>
                                <a href="<?php echo ADMIN_URL; ?>/menu/items/<?php echo $menu['id']; ?>" class="btn btn-outline-primary">
                                    <?php echo bloggy_icon('bs', 'list-ul', '16', '#000', 'me-2'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_MANAGE_ITEMS_BTN; ?>
                                </a>
                                
                                <a href="<?php echo ADMIN_URL; ?>/menu/preview/<?php echo $menu['id']; ?>" class="btn btn-outline-secondary">
                                    <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-2'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_PREVIEW_BTN; ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>