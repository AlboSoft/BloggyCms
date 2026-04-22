<?php
    add_admin_js('templates/default/admin/assets/js/controllers/menu-builder.js');
    add_admin_js('templates/default/admin/assets/js/controllers/menu-icons.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', isset($menu['id']) ? 'pencil' : 'plus-circle', '24', '#000', 'me-2'); ?>
            <?php echo isset($menu['id']) ? LANG_TEMPLATE_MENU_FORM_EDIT_TITLE : LANG_TEMPLATE_MENU_FORM_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/menu" class="btn btn-outline-secondary">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_MENU_FORM_BACK_BTN; ?>
        </a>
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
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_MENU_FORM_STRUCTURE_TITLE; ?></h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-menu-item" data-bs-toggle="modal" data-bs-target="#menuItemModal">
                                <?php echo bloggy_icon('bs', 'plus-circle', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_ADD_ITEM_BTN; ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="expand-all">
                                <?php echo bloggy_icon('bs', 'arrows-expand', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_EXPAND_ALL_BTN; ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="collapse-all">
                                <?php echo bloggy_icon('bs', 'arrows-collapse', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_COLLAPSE_ALL_BTN; ?>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="menu-builder">
                            <div class="mb-3">
                                <div class="alert alert-light border">
                                    <div class="d-flex align-items-center">
                                        <?php echo bloggy_icon('bs', 'lightbulb', '16', '#ffc107', 'me-2'); ?>
                                        <small><?php echo LANG_TEMPLATE_MENU_FORM_DRAG_HINT; ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="menu-items-container" class="sortable-menu menu-tree">
                                <?php if (!empty($menuStructure)) { ?>
                                    <?php 
                                    function renderMenuItem($item, $index, $level = 0) {
                                        $title = html($item['title'] ?? '');
                                        $url = html($item['url'] ?? '');
                                        $class = html($item['class'] ?? '');
                                        $target = $item['target'] ?? '_self';
                                        $children = $item['children'] ?? array();
                                        $hasChildren = !empty($children);
                                        $levelClass = 'level-' . min($level, 4);
                                        $itemData = html(json_encode(array(
                                            'title' => $item['title'] ?? '',
                                            'url' => $item['url'] ?? '',
                                            'class' => $item['class'] ?? '',
                                            'target' => $item['target'] ?? '_self'
                                        )));
                                        ?>
                                        <div class="menu-item-card card mb-2 <?php echo $levelClass; ?>" 
                                             data-index="<?php echo $index; ?>" 
                                             data-level="<?php echo $level; ?>"
                                             data-item="<?php echo $itemData; ?>">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        <div class="menu-level-indicator me-3">
                                                            <?php for ($i = 0; $i < $level; $i++) { ?>
                                                                <span class="level-line"></span>
                                                            <?php } ?>
                                                            <span class="level-dot"></span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center">
                                                                <?php if ($hasChildren) { ?>
                                                                    <?php echo bloggy_icon('bs', 'folder-fill', '16', '#ffc107', 'me-2'); ?>
                                                                <?php } else { ?>
                                                                    <?php echo bloggy_icon('bs', 'link-45deg', '16', '#0d6efd', 'me-2'); ?>
                                                                <?php } ?>
                                                                <div>
                                                                    <h6 class="mb-1"><?php echo !empty($title) ? $title : LANG_TEMPLATE_MENU_FORM_NO_TITLE; ?></h6>
                                                                    <small class="text-muted"><?php echo $url; ?></small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary menu-item-handle" title="<?php echo LANG_TEMPLATE_MENU_FORM_DRAG_TITLE; ?>">
                                                            <?php echo bloggy_icon('bs', 'arrows-move', '16', '#000'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary edit-menu-item" 
                                                                title="<?php echo LANG_TEMPLATE_MENU_FORM_EDIT_TITLE; ?>"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#menuItemModal">
                                                            <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-success add-child-item" 
                                                                title="<?php echo LANG_TEMPLATE_MENU_FORM_ADD_CHILD_TITLE; ?>"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#menuItemModal"
                                                                data-parent-index="<?php echo $index; ?>">
                                                            <?php echo bloggy_icon('bs', 'patch-plus', '16', '#000'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger remove-menu-item" title="<?php echo LANG_TEMPLATE_MENU_FORM_DELETE_TITLE; ?>">
                                                            <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($hasChildren) { ?>
                                                    <div class="menu-children-container mt-3">
                                                        <div class="border-top pt-3">
                                                            <div class="menu-children sortable-menu">
                                                                <?php foreach ($children as $childIndex => $child) { ?>
                                                                    <?php renderMenuItem($child, $index . '_' . $childIndex, $level + 1); ?>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    
                                    foreach ($menuStructure as $index => $item) {
                                        renderMenuItem($item, $index, 0);
                                    }
                                    ?>
                                <?php } ?>
                            </div>
                            
                            <div id="menu-empty" class="text-center text-muted p-5 <?php echo !empty($menuStructure) ? 'd-none' : ''; ?>">
                                <div class="mb-3">
                                    <?php echo bloggy_icon('bs', 'list-ul', '48', '#6C6C6C'); ?>
                                </div>
                                <h5 class="text-muted"><?php echo LANG_TEMPLATE_MENU_FORM_EMPTY_TITLE; ?></h5>
                                <p class="text-muted mb-3"><?php echo LANG_TEMPLATE_MENU_FORM_EMPTY_TEXT; ?></p>
                                <button type="button" class="btn btn-primary" id="add-first-item" data-bs-toggle="modal" data-bs-target="#menuItemModal">
                                    <?php echo bloggy_icon('bs', 'plus-circle', '16', '#fff', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_ADD_FIRST_BTN; ?>
                                </button>
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
                            <?php echo LANG_TEMPLATE_MENU_FORM_SETTINGS_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'tag', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_NAME_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="name" 
                                   value="<?php echo html($menu['name'] ?? ''); ?>" 
                                   required
                                   placeholder="<?php echo LANG_TEMPLATE_MENU_FORM_NAME_PLACEHOLDER; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'layout-wtf', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_TEMPLATE_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="template" required <?php echo empty($availableTemplates) ? 'disabled' : ''; ?>>
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
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'power', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_STATUS_LABEL; ?>
                            </label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo ($menu['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_MENU_FORM_STATUS_ACTIVE; ?></option>
                                <option value="inactive" <?php echo ($menu['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_MENU_FORM_STATUS_INACTIVE; ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" <?php echo empty($availableTemplates) ? 'disabled' : ''; ?>>
                                <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>
                                <?php echo isset($menu['id']) ? LANG_TEMPLATE_MENU_FORM_UPDATE_BTN : LANG_TEMPLATE_MENU_FORM_CREATE_BTN; ?>
                            </button>
                            
                            <?php if (isset($menu['id'])) { ?>
                            <a href="<?php echo ADMIN_URL; ?>/menu/preview/<?php echo $menu['id']; ?>" 
                               class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_PREVIEW_BTN; ?>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'graph-up', '16', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_MENU_FORM_STATS_TITLE; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <div class="h4 mb-0" id="total-items">0</div>
                                    <small class="text-muted"><?php echo LANG_TEMPLATE_MENU_FORM_STATS_TOTAL; ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0" id="nested-items">0</div>
                                <small class="text-muted"><?php echo LANG_TEMPLATE_MENU_FORM_STATS_NESTED; ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="menu_structure" id="menu-structure" value='<?php echo json_encode($menuStructure ?? array(), JSON_UNESCAPED_UNICODE); ?>'>
    </form>
</div>

<div class="modal fade" id="menuItemModal" tabindex="-1" aria-labelledby="menuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuItemModalLabel">
                    <?php echo bloggy_icon('bs', 'plus-circle', '20', '#000', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_ADD_TITLE; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo LANG_TEMPLATE_MENU_FORM_MODAL_CLOSE; ?>"></button>
            </div>
            <div class="modal-body">
                <form id="menu-item-form">
                    <input type="hidden" id="edit-item-index">
                    <input type="hidden" id="parent-item-index">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'fonts', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_TITLE_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="item-title" 
                                   placeholder="<?php echo LANG_TEMPLATE_MENU_FORM_MODAL_TITLE_PLACEHOLDER; ?>" 
                                   maxlength="100"
                                   required>
                            <div class="form-text"><?php echo LANG_TEMPLATE_MENU_FORM_MODAL_TITLE_HINT; ?></div>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'link', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_URL_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="item-url" 
                                       placeholder="/page <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_URL_PLACEHOLDER; ?>" 
                                       maxlength="255"
                                       required>
                            </div>
                            <div class="form-text"><?php echo LANG_TEMPLATE_MENU_FORM_MODAL_URL_HINT; ?></div>
                        </div>

                        <div class="mb-2">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="form-label mb-0">
            <?php echo bloggy_icon('bs', 'code-slash', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_SHORTCODES_LABEL; ?>
        </label>
        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                data-bs-toggle="collapse" data-bs-target="#shortcodeHelp">
            <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_SHORTCODES_HELP; ?>
        </button>
    </div>
    
    <div class="d-flex flex-wrap gap-1 mb-2" id="shortcode-buttons">
        <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" 
                data-shortcode="{username}">
            {username}
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" 
                data-shortcode="{user_id}">
            {user_id}
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" 
                data-shortcode="{email}">
            {email}
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" 
                data-shortcode="{base_url}">
            {base_url}
        </button>
    </div>
    
    <div class="collapse" id="shortcodeHelp">
        <div class="card card-body bg-light p-2 small">
            <table class="table table-sm table-borderless mb-0">
                <tr><td class="p-1"><code>{username}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_USERNAME; ?></td></tr>
                <tr><td class="p-1"><code>{user_id}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_USER_ID; ?></td></tr>
                <tr><td class="p-1"><code>{email}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_EMAIL; ?></td></tr>
                <tr><td class="p-1"><code>{first_name}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_FIRST_NAME; ?></td></tr>
                <tr><td class="p-1"><code>{last_name}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_LAST_NAME; ?></td></tr>
                <tr><td class="p-1"><code>{display_name}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_DISPLAY_NAME; ?></td></tr>
                <tr><td class="p-1"><code>{slug}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_SLUG; ?></td></tr>
                <tr><td class="p-1"><code>{base_url}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_BASE_URL; ?></td></tr>
                <tr><td class="p-1"><code>{admin_url}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_ADMIN_URL; ?></td></tr>
                <tr><td class="p-1"><code>{user_field:поле}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_USER_FIELD; ?></td></tr>
            </table>
        </div>
    </div>
    
    <div class="shortcode-preview small text-muted mt-2" id="shortcode-preview" style="display: none;">
        <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-1'); ?>
        <?php echo LANG_TEMPLATE_MENU_FORM_SHORTCODE_PREVIEW; ?> <span id="preview-text"></span>
    </div>
</div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'box-arrow-up-right', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_TARGET_LABEL; ?>
                            </label>
                            <select class="form-select" id="item-target">
                                <option value="_self"><?php echo LANG_TEMPLATE_MENU_FORM_TARGET_SELF; ?></option>
                                <option value="_blank"><?php echo LANG_TEMPLATE_MENU_FORM_TARGET_BLANK; ?></option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'code-slash', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_CSS_LABEL; ?>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="item-class" 
                                   placeholder="my-class another-class" 
                                   maxlength="50">
                            <div class="form-text"><?php echo LANG_TEMPLATE_MENU_FORM_MODAL_CSS_HINT; ?></div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <h6 class="text-muted mb-3">
                                <?php echo bloggy_icon('bs', 'image', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_FORM_ICON_SETTINGS_TITLE; ?>
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'palette', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_FORM_ICON_LABEL; ?>
                                    </label>
                                    <div class="input-group mb-2">
                                        <input type="text" 
                                            class="form-control" 
                                            id="item-icon-id" 
                                            placeholder="<?php echo LANG_TEMPLATE_MENU_FORM_ICON_PLACEHOLDER; ?>"
                                            readonly>
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                id="select-icon-btn"
                                                onclick="window.menuIconManager.openIconSelector()">
                                            <?php echo bloggy_icon('bs', 'images', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_MENU_FORM_ICON_SELECT_BTN; ?>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                id="clear-icon-btn">
                                            <?php echo bloggy_icon('bs', 'x-circle', '16', '#000'); ?>
                                        </button>
                                    </div>
                                    
                                    <div id="icon-preview" class="text-center mb-3" style="display: none;">
                                        <div id="selected-icon-preview" style="font-size: 48px;"></div>
                                        <small class="text-muted" id="icon-name"></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'rulers', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_FORM_ICON_SIZE_LABEL; ?>
                                    </label>
                                    <input type="number" 
                                        class="form-control" 
                                        id="item-icon-size" 
                                        placeholder="16"
                                        min="8"
                                        max="128">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'palette', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_FORM_ICON_COLOR_LABEL; ?>
                                    </label>
                                    <input type="color" 
                                        class="form-control form-control-color" 
                                        id="item-icon-color" 
                                        value="#000000"
                                        title="<?php echo LANG_TEMPLATE_MENU_FORM_ICON_COLOR_TITLE; ?>">
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            id="item-icon-only">
                                        <label class="form-check-label" for="item-icon-only">
                                            <?php echo bloggy_icon('bs', 'fonts', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_MENU_FORM_ICON_ONLY_LABEL; ?>
                                        </label>
                                        <div class="form-text small">
                                            <?php echo LANG_TEMPLATE_MENU_FORM_ICON_ONLY_HINT; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                        <h6 class="text-muted mb-3">
                            <?php echo bloggy_icon('bs', 'shield-lock', '16', '#000', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_MENU_FORM_VISIBILITY_TITLE; ?>
                        </h6>
    
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small">
                                    <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_SHOW_TO_GROUPS; ?>
                                </label>
                                <select class="form-select form-select-sm" id="item-show-to" multiple size="4">
                                    <option value=""><?php echo LANG_TEMPLATE_MENU_FORM_ALL_GROUPS; ?></option>
                                    <?php 
                                    $groups = $this->getUserGroups();
                                    foreach ($groups as $group) { 
                                    ?>
                                        <option value="<?php echo $group['id']; ?>">
                                            <?php echo html($group['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text small"><?php echo LANG_TEMPLATE_MENU_FORM_SHOW_TO_GROUPS_HINT; ?></div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">
                                    <?php echo bloggy_icon('bs', 'eye-slash', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_FORM_HIDE_FROM_GROUPS; ?>
                                </label>
                                <select class="form-select form-select-sm" id="item-hide-from" multiple size="4">
                                    <option value=""><?php echo LANG_TEMPLATE_MENU_FORM_NO_HIDE; ?></option>
                                    <?php foreach ($groups as $group) { ?>
                                        <option value="<?php echo $group['id']; ?>">
                                            <?php echo html($group['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text small"><?php echo LANG_TEMPLATE_MENU_FORM_HIDE_FROM_GROUPS_HINT; ?></div>
                            </div>
                        </div>
    
                        <div class="alert alert-info mt-2 p-2 small">
                            <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-1'); ?>
                            <strong><?php echo LANG_TEMPLATE_MENU_FORM_PRIORITY_TITLE; ?></strong> <?php echo LANG_TEMPLATE_MENU_FORM_PRIORITY_TEXT; ?>
                        </div>
                    </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?php echo bloggy_icon('bs', 'x-circle', '16', '#000', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_CANCEL; ?>
                </button>
                <button type="button" class="btn btn-primary" id="save-menu-item">
                    <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_MENU_FORM_MODAL_SAVE; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="iconSelectorModal" class="custom-modal" style="display: none;">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="custom-modal-title">
                    <?php echo bloggy_icon('bs', 'images', '20', '#000', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_MENU_FORM_ICON_SELECTOR_TITLE; ?>
                </h5>
                <button type="button" class="custom-modal-close" onclick="window.menuIconManager.closeIconSelector()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="custom-modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light">
                            <?php echo bloggy_icon('bs', 'search', '16', '#000'); ?>
                        </span>
                        <input type="text" 
                            id="iconSearchModal" 
                            class="form-control border-0 bg-light" 
                            placeholder="<?php echo LANG_TEMPLATE_MENU_FORM_ICON_SEARCH_PLACEHOLDER; ?>"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="none"
                            spellcheck="false"
                            tabindex="0">
                    </div>
                </div>
                
                <div class="icon-selector-container">
                    <ul class="nav nav-tabs" id="iconSelectorTabs" role="tablist"></ul>
                    
                    <div class="tab-content pt-3" id="iconSelectorTabsContent"></div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="window.menuIconManager.closeIconSelector()">
                    <?php echo bloggy_icon('bs', 'x-circle', '16', '#000', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_MENU_FORM_ICON_SELECTOR_CANCEL; ?>
                </button>
                <button type="button" class="btn btn-primary" onclick="window.menuIconManager.confirmIconSelection()">
                    <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_MENU_FORM_ICON_SELECTOR_SELECT; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="iconSelectorOverlay" class="custom-modal-overlay" style="display: none;"></div>