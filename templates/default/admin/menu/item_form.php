<?php
    add_admin_js('templates/default/admin/assets/js/controllers/menu-icons.js');
    add_admin_js('templates/default/admin/assets/js/controllers/shortcode-preview.js');
    
    if (!isset($parentItem)) {
        $parentItem = null;
    }
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', $isEdit ? 'pencil-square' : 'plus-circle', '24', '#000', 'me-2'); ?>
            <?php echo $isEdit ? LANG_TEMPLATE_MENU_ITEM_FORM_EDIT_TITLE : LANG_TEMPLATE_MENU_ITEM_FORM_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/menu/items/<?php echo $menu['id']; ?>" class="btn btn-outline-secondary">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_BACK_BTN; ?>
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_MAIN_INFO_TITLE; ?></h5>
                </div>
                <div class="card-body">
                    <?php if (isset($parentItem) && $parentItem) { ?>
                        <div class="alert alert-info mb-4">
                            <?php echo bloggy_icon('bs', 'link-45deg', '16', '#000', 'me-2'); ?>
                            <?php echo sprintf(LANG_TEMPLATE_MENU_ITEM_FORM_PARENT_INFO, html($parentItem['title'])); ?>
                        </div>
                    <?php } ?>
                    
                    <form method="POST" id="item-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'fonts', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_TITLE_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="title" 
                                   value="<?php echo html($formData['title'] ?? ''); ?>" 
                                   placeholder="<?php echo LANG_TEMPLATE_MENU_ITEM_FORM_TITLE_PLACEHOLDER; ?>"
                                   maxlength="100"
                                   required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'chat-text', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_DESCRIPTION_LABEL; ?>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="description" 
                                   value="<?php echo html($formData['description'] ?? ''); ?>" 
                                   placeholder="<?php echo LANG_TEMPLATE_MENU_ITEM_FORM_DESCRIPTION_PLACEHOLDER; ?>"
                                   maxlength="255">
                            <div class="form-text"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_DESCRIPTION_HINT; ?></div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'link', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_URL_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       name="url" 
                                       id="item-url"
                                       value="<?php echo html($formData['url'] ?? ''); ?>" 
                                       placeholder="/page <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_URL_PLACEHOLDER; ?>"
                                       maxlength="255"
                                       required>
                            </div>
                            <div class="form-text"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_URL_HINT; ?></div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">
                                    <?php echo bloggy_icon('bs', 'code-slash', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODES_LABEL; ?>
                                </label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                        data-bs-toggle="collapse" data-bs-target="#shortcodeHelp">
                                    <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODES_HELP; ?>
                                </button>
                            </div>
                            
                            <div class="d-flex flex-wrap gap-1 mb-2" id="shortcode-buttons">
                                <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" data-shortcode="{username}">{username}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" data-shortcode="{user_id}">{user_id}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" data-shortcode="{email}">{email}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" data-shortcode="{base_url}">{base_url}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" data-shortcode="{admin_url}">{admin_url}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" data-shortcode="{year}">{year}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" data-shortcode="{month}">{month}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" data-shortcode="{day}">{day}</button>
                            </div>
                            
                            <div class="collapse" id="shortcodeHelp">
                                <div class="card card-body bg-light p-2 small">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td class="p-1"><code>{username}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_USERNAME; ?></td></tr>
                                        <tr><td class="p-1"><code>{user_id}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_USER_ID; ?></td></tr>
                                        <tr><td class="p-1"><code>{email}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_EMAIL; ?></td></tr>
                                        <tr><td class="p-1"><code>{first_name}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_FIRST_NAME; ?></td></tr>
                                        <tr><td class="p-1"><code>{last_name}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_LAST_NAME; ?></td></tr>
                                        <tr><td class="p-1"><code>{display_name}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_DISPLAY_NAME; ?></td></tr>
                                        <tr><td class="p-1"><code>{slug}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_SLUG; ?></td></tr>
                                        <tr><td class="p-1"><code>{base_url}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_BASE_URL; ?></td></tr>
                                        <tr><td class="p-1"><code>{admin_url}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_ADMIN_URL; ?></td></tr>
                                        <tr><td class="p-1"><code>{user_field:поле}</code></td><td class="p-1"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_USER_FIELD; ?></td></tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="shortcode-preview small text-muted mt-2" id="shortcode-preview" style="display: none;">
                                <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHORTCODE_PREVIEW; ?> <span id="preview-text"></span>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'box-arrow-up-right', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_TARGET_LABEL; ?>
                                    </label>
                                    <select class="form-select" name="target">
                                        <option value="_self" <?php echo ($formData['target'] ?? '_self') === '_self' ? 'selected' : ''; ?>>
                                            <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_TARGET_SELF; ?>
                                        </option>
                                        <option value="_blank" <?php echo ($formData['target'] ?? '') === '_blank' ? 'selected' : ''; ?>>
                                            <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_TARGET_BLANK; ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'code-slash', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_CSS_CLASS_LABEL; ?>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="class" 
                                           value="<?php echo html($formData['class'] ?? ''); ?>" 
                                           placeholder="my-class another-class" 
                                           maxlength="50">
                                    <div class="form-text"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_CSS_CLASS_HINT; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-top pt-4 mt-2">
                            <h6 class="text-muted mb-3">
                                <?php echo bloggy_icon('bs', 'image', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_SETTINGS_TITLE; ?>
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'palette', '16', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_LABEL; ?>
                                    </label>
                                    <div class="input-group mb-2">
                                        <input type="text" 
                                               class="form-control" 
                                               id="item-icon-id" 
                                               name="icon_id"
                                               value="<?php echo html($formData['icon']['id'] ?? ''); ?>"
                                               placeholder="<?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_PLACEHOLDER; ?>"
                                               readonly>
                                        <input type="hidden" name="icon_set" id="item-icon-set" value="<?php echo html($formData['icon']['set'] ?? 'bs'); ?>">
                                        <input type="hidden" name="icon_size" id="item-icon-size-hidden" value="<?php echo html($formData['icon']['size'] ?? 20); ?>">
                                        <input type="hidden" name="icon_color" id="item-icon-color-hidden" value="<?php echo html($formData['icon']['color'] ?? '#000000'); ?>">
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                id="select-icon-btn"
                                                onclick="window.menuIconManager.openIconSelector()">
                                            <?php echo bloggy_icon('bs', 'images', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_SELECT_BTN; ?>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                id="clear-icon-btn">
                                            <?php echo bloggy_icon('bs', 'x-circle', '16', '#000'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="icon-preview" class="text-center mb-3" style="display: <?php echo !empty($formData['icon']['id']) ? 'block' : 'none'; ?>;">
                                <div id="selected-icon-preview" style="font-size: 48px;">
                                    <?php if (!empty($formData['icon']['id'])) {
                                        echo bloggy_icon($formData['icon']['set'] ?? 'bs', $formData['icon']['id'], '48 48', $formData['icon']['color'] ?? '#000000');
                                    } ?>
                                </div>
                                <small class="text-muted" id="icon-name"><?php echo html($formData['icon']['id'] ?? ''); ?></small>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label class="form-label small">
                                        <?php echo bloggy_icon('bs', 'rulers', '14', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_SIZE_LABEL; ?>
                                    </label>
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           id="item-icon-size" 
                                           value="<?php echo html($formData['icon']['size'] ?? 20); ?>"
                                           min="8"
                                           max="128">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label small">
                                        <?php echo bloggy_icon('bs', 'palette', '14', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_COLOR_LABEL; ?>
                                    </label>
                                    <input type="color" 
                                           class="form-control form-control-color" 
                                           id="item-icon-color" 
                                           value="<?php echo html($formData['icon']['color'] ?? '#000000'); ?>"
                                           title="<?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_COLOR_TITLE; ?>">
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            role="switch"
                                            id="item-icon-only"
                                            name="icon_only"
                                            value="1"
                                            <?php echo ($formData['icon_only'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-semibold" for="item-icon-only">
                                            <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_ONLY_LABEL; ?>
                                        </label>
                                        <div class="form-text text-muted small mt-1">
                                            <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_ONLY_HINT; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-top pt-4 mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    role="switch"
                                    id="item-extra" 
                                    name="is_extra"
                                    value="1"
                                    <?php echo ($formData['is_extra'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-semibold" for="item-extra">
                                    <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_EXTRA_MENU_ITEM; ?>
                                </label>
                                <div class="form-text text-muted small mt-1">
                                    <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_EXTRA_MENU_HINT; ?> 
                                    <code class="bg-light px-1 rounded">{li-extra}</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-top pt-4 mt-2">
                            <h6 class="text-muted mb-3">
                                <?php echo bloggy_icon('bs', 'shield-lock', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_VISIBILITY_TITLE; ?>
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label small">
                                        <?php echo bloggy_icon('bs', 'eye', '14', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHOW_TO_GROUPS; ?>
                                    </label>
                                    <input type="hidden" name="show_to_groups[]" value="">
                                    <select class="form-select form-select-sm" id="item-show-to" name="show_to_groups[]" multiple size="4">
                                        <option value=""><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ALL_GROUPS; ?></option>
                                        <?php foreach ($groups as $group) { ?>
                                            <option value="<?php echo $group['id']; ?>" 
                                                <?php echo in_array($group['id'], $formData['show_to_groups'] ?? []) ? 'selected' : ''; ?>>
                                                <?php echo html($group['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="form-text small"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_SHOW_TO_GROUPS_HINT; ?></div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label small">
                                        <?php echo bloggy_icon('bs', 'eye-slash', '14', '#000', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_HIDE_FROM_GROUPS; ?>
                                    </label>
                                    <input type="hidden" name="hide_from_groups[]" value="">
                                    <select class="form-select form-select-sm" id="item-hide-from" name="hide_from_groups[]" multiple size="4">
                                        <option value=""><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_NO_HIDE; ?></option>
                                        <?php foreach ($groups as $group) { ?>
                                            <option value="<?php echo $group['id']; ?>" 
                                                <?php echo in_array($group['id'], $formData['hide_from_groups'] ?? []) ? 'selected' : ''; ?>>
                                                <?php echo html($group['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="form-text small"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_HIDE_FROM_GROUPS_HINT; ?></div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-2 p-2 small">
                                <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-1'); ?>
                                <strong><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_PRIORITY_TITLE; ?></strong> <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_PRIORITY_TEXT; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_INFO_TITLE; ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_MENU_LABEL; ?></label>
                        <div>
                            <strong><?php echo html($menu['name']); ?></strong>
                            <br>
                            <small class="text-muted">ID: <?php echo $menu['id']; ?></small>
                        </div>
                    </div>
                    
                    <?php if (isset($parentItem) && $parentItem) { ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_PARENT_LABEL; ?></label>
                            <div>
                                <strong><?php echo html($parentItem['title']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo LANG_TEMPLATE_MENU_ITEM_FORM_LEVEL_LABEL; ?> <?php echo $parentItem['level']; ?></small>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" form="item-form" class="btn btn-primary">
                            <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>
                            <?php echo $isEdit ? LANG_TEMPLATE_MENU_ITEM_FORM_SAVE_BTN : LANG_TEMPLATE_MENU_ITEM_FORM_CREATE_BTN; ?>
                        </button>
                        <a href="<?php echo ADMIN_URL; ?>/menu/items/<?php echo $menu['id']; ?>" class="btn btn-outline-secondary">
                            <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_CANCEL_BTN; ?>
                        </a>
                    </div>
                </div>
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
                    <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_SELECTOR_TITLE; ?>
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
                               placeholder="<?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_SEARCH_PLACEHOLDER; ?>"
                               autocomplete="off">
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
                    <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_SELECTOR_CANCEL; ?>
                </button>
                <button type="button" class="btn btn-primary" onclick="window.menuIconManager.confirmIconSelection()">
                    <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_MENU_ITEM_FORM_ICON_SELECTOR_SELECT; ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div id="iconSelectorOverlay" class="custom-modal-overlay" style="display: none;"></div>

<?php ob_start(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.menuIconManager = new MenuIconManager();
            
            const urlInput = document.getElementById('item-url');
            const previewElement = document.getElementById('shortcode-preview');
            const previewTextElement = document.getElementById('preview-text');
            
            function updateShortcodePreview() {
                if (!urlInput || !previewElement || !previewTextElement) return;
                
                let url = urlInput.value;
                
                if (!url.trim()) {
                    previewElement.style.display = 'none';
                    return;
                }
                
                let preview = url
                    .replace(/\{username\}/g, 'vasya')
                    .replace(/\{user_id\}/g, '123')
                    .replace(/\{email\}/g, 'user@example.com')
                    .replace(/\{first_name\}/g, 'John')
                    .replace(/\{last_name\}/g, 'Doe')
                    .replace(/\{display_name\}/g, 'John Doe')
                    .replace(/\{slug\}/g, 'john-doe')
                    .replace(/\{base_url\}/g, window.location.origin)
                    .replace(/\{admin_url\}/g, window.location.origin + '/admin')
                    .replace(/\{year\}/g, new Date().getFullYear())
                    .replace(/\{month\}/g, String(new Date().getMonth() + 1).padStart(2, '0'))
                    .replace(/\{day\}/g, String(new Date().getDate()).padStart(2, '0'));
                
                preview = preview.replace(/\{user_field:([^}]+)\}/g, 'значение_поля');
                
                previewTextElement.textContent = preview;
                previewElement.style.display = 'block';
            }
            
            if (urlInput) {
                urlInput.addEventListener('input', updateShortcodePreview);
                urlInput.addEventListener('focus', updateShortcodePreview);
                updateShortcodePreview();
            }
            
            document.querySelectorAll('.shortcode-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const shortcode = btn.dataset.shortcode;
                    if (urlInput) {
                        const start = urlInput.selectionStart;
                        const end = urlInput.selectionEnd;
                        const text = urlInput.value;
                        urlInput.value = text.substring(0, start) + shortcode + text.substring(end);
                        urlInput.focus();
                        urlInput.setSelectionRange(start + shortcode.length, start + shortcode.length);
                        updateShortcodePreview();
                    }
                });
            });
            
            const iconIdInput = document.getElementById('item-icon-id');
            const iconSetInput = document.getElementById('item-icon-set');
            const iconSizeInput = document.getElementById('item-icon-size');
            const iconSizeHidden = document.getElementById('item-icon-size-hidden');
            const iconColorInput = document.getElementById('item-icon-color');
            const iconColorHidden = document.getElementById('item-icon-color-hidden');
            const iconPreview = document.getElementById('selected-icon-preview');
            const iconNameSpan = document.getElementById('icon-name');
            const clearIconBtn = document.getElementById('clear-icon-btn');
            const iconOnlyCheckbox = document.getElementById('item-icon-only');
            
            if (clearIconBtn) {
                clearIconBtn.addEventListener('click', () => {
                    iconIdInput.value = '';
                    iconSetInput.value = 'bs';
                    iconPreview.innerHTML = '';
                    iconNameSpan.textContent = '';
                    document.getElementById('icon-preview').style.display = 'none';
                });
            }
            
            if (iconSizeInput && iconSizeHidden) {
                iconSizeInput.addEventListener('change', () => {
                    iconSizeHidden.value = iconSizeInput.value;
                    if (iconIdInput.value) {
                        const iconSet = iconSetInput.value;
                        const iconId = iconIdInput.value;
                        const size = iconSizeInput.value;
                        const color = iconColorInput.value;
                        iconPreview.innerHTML = bloggyIcon(iconSet, iconId, `${size} ${size}`, color);
                    }
                });
            }
            
            if (iconColorInput && iconColorHidden) {
                iconColorInput.addEventListener('change', () => {
                    iconColorHidden.value = iconColorInput.value;
                    if (iconIdInput.value) {
                        const iconSet = iconSetInput.value;
                        const iconId = iconIdInput.value;
                        const size = iconSizeInput.value;
                        const color = iconColorInput.value;
                        iconPreview.innerHTML = bloggyIcon(iconSet, iconId, `${size} ${size}`, color);
                    }
                });
            }
        });
    </script>
<?php admin_bottom_js(ob_get_clean()); ?>

<?php ob_start(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('item-form');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                const showToHidden = document.querySelector('input[name="show_to_groups[]"][type="hidden"]');
                const hideFromHidden = document.querySelector('input[name="hide_from_groups[]"][type="hidden"]');
                if (showToHidden) showToHidden.value = '';
                if (hideFromHidden) hideFromHidden.value = '';
            });
        });
    </script>
<?php admin_bottom_js(ob_get_clean()); ?>