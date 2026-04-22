<?php
    add_admin_js('templates/default/admin/assets/js/controllers/ace.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-css.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-javascript.js');
    add_admin_js('templates/default/admin/assets/js/controllers/theme-monokai.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', $isEdit ? 'pencil-square' : 'plus-circle', '24', '#000', 'me-2'); ?>
            <?php echo $isEdit ? LANG_TEMPLATE_FRAGMENTS_FORM_EDIT_TITLE : LANG_TEMPLATE_FRAGMENTS_FORM_CREATE_TITLE; ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/fragments" class="btn btn-outline-secondary btn-sm">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_BACK_BTN; ?>
            </a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_MAIN_INFO_TITLE; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_NAME_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control form-control-lg" 
                                   value="<?php echo html($fragment['name'] ?? ''); ?>" 
                                   placeholder="<?php echo LANG_TEMPLATE_FRAGMENTS_FORM_NAME_PLACEHOLDER; ?>"
                                   required>
                            <div class="form-text"><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_NAME_HINT; ?></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_SYSTEM_NAME_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">{ctype:</span>
                                <input type="text" 
                                       name="system_name" 
                                       class="form-control" 
                                       value="<?php echo html($fragment['system_name'] ?? ''); ?>" 
                                       placeholder="slider_gallery"
                                       pattern="[a-z0-9_]+"
                                       required>
                                <span class="input-group-text bg-light">}</span>
                            </div>
                            <div class="form-text">
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_SYSTEM_NAME_HINT; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_DESCRIPTION_LABEL; ?></label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="<?php echo LANG_TEMPLATE_FRAGMENTS_FORM_DESCRIPTION_PLACEHOLDER; ?>"><?php echo html($fragment['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'palette', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STYLES_SCRIPTS_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <?php echo bloggy_icon('bs', 'filetype-css', '16', '#1889d0', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_CSS_FILES_LABEL; ?>
                            </label>
                            <div id="css-files-container">
                                <?php if (!empty($fragment['css_files'])) { ?>
                                    <?php foreach ($fragment['css_files'] as $cssFile) { ?>
                                        <div class="input-group mb-2 css-file-row">
                                            <input type="text"
                                                name="css_files[]"
                                                class="form-control"
                                                value="<?php echo html($cssFile); ?>"
                                                placeholder="templates/default/front/assets/css/fragment.css">
                                            <button type="button" class="btn btn-outline-danger remove-asset" data-type="css">
                                                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                            </button>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                                <div class="input-group mb-2 css-file-row">
                                    <input type="text"
                                        name="css_files[]"
                                        class="form-control"
                                        value=""
                                        placeholder="templates/default/front/assets/css/fragment.css">
                                    <button type="button" class="btn btn-outline-danger remove-asset" data-type="css">
                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-css-file">
                                <?php echo bloggy_icon('bs', 'plus', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_ADD_CSS_BTN; ?>
                            </button>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <?php echo bloggy_icon('bs', 'filetype-js', '16', '#1889d0', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_JS_FILES_LABEL; ?>
                            </label>
                            <div id="js-files-container">
                                <?php if (!empty($fragment['js_files'])) { ?>
                                    <?php foreach ($fragment['js_files'] as $jsFile) { ?>
                                        <div class="input-group mb-2 js-file-row">
                                            <input type="text"
                                                name="js_files[]"
                                                class="form-control"
                                                value="<?php echo html($jsFile); ?>"
                                                placeholder="templates/default/front/assets/js/fragment.js">
                                            <button type="button" class="btn btn-outline-danger remove-asset" data-type="js">
                                                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                            </button>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                                <div class="input-group mb-2 js-file-row">
                                    <input type="text"
                                        name="js_files[]"
                                        class="form-control"
                                        value=""
                                        placeholder="templates/default/front/assets/js/fragment.js">
                                    <button type="button" class="btn btn-outline-danger remove-asset" data-type="js">
                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-js-file">
                                <?php echo bloggy_icon('bs', 'plus', '16', '#000', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_ADD_JS_BTN; ?>
                            </button>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <?php echo bloggy_icon('bs', 'code', '16', '#1889d0', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_INLINE_CSS_LABEL; ?>
                            </label>
                            <div id="inline-css-container" class="border rounded">
                                <div id="inline-css-editor" style="height: 200px;"></div>
                            </div>
                            <textarea name="inline_css" id="inline_css" style="display: none;"><?php echo html($fragment['inline_css'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <?php echo bloggy_icon('bs', 'code', '16', '#1889d0', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_INLINE_JS_LABEL; ?>
                            </label>
                            <div id="inline-js-container" class="border rounded">
                                <div id="inline-js-editor" style="height: 200px;"></div>
                            </div>
                            <textarea name="inline_js" id="inline_js" style="display: none;"><?php echo html($fragment['inline_js'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <?php if ($isEdit && isset($stats)) { ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0">
                                <?php echo bloggy_icon('bs', 'graph-up', '20', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATS_TITLE; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <div class="h4 mb-0"><?php echo $stats['total']; ?></div>
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATS_TOTAL; ?></small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <div class="h4 mb-0"><?php echo $stats['active']; ?></div>
                                        <small class="text-muted"><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATS_ACTIVE; ?></small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="h4 mb-0"><?php echo $stats['inactive']; ?></div>
                                    <small class="text-muted"><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATS_INACTIVE; ?></small>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: <?php echo $stats['total'] > 0 ? ($stats['active'] / $stats['total'] * 100) : 0; ?>%"></div>
                                </div>
                                <div class="small text-muted mt-2 text-center">
                                    <?php echo round($stats['total'] > 0 ? ($stats['active'] / $stats['total'] * 100) : 0); ?>% <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATS_ACTIVE_PERCENT; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'gear', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_SETTINGS_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATUS_LABEL; ?></label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($fragment['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATUS_ACTIVE; ?></option>
                                <option value="inactive" <?php echo ($fragment['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATUS_INACTIVE; ?></option>
                            </select>
                            <div class="form-text">
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_STATUS_HINT; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'info-circle', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_HOWTO_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_HOWTO_TEXT; ?>
                        </p>
                        
                        <div class="bg-light p-2 rounded mb-2">
                            <code class="small">{<?php echo html($fragment['system_name'] ?? 'имя_фрагмента'); ?>}</code>
                            <div class="text-muted small mt-1">
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_HOWTO_SIMPLE; ?>
                            </div>
                        </div>
                        
                        <div class="bg-light p-2 rounded mb-2">
                            <code class="small">{ctype:<?php echo html($fragment['system_name'] ?? 'имя_фрагмента'); ?>}
    &lt;div class="item"&gt;
        &lt;img src="{field:image}" alt="{field:title}"&gt;
        &lt;h3&gt;{field:title}&lt;/h3&gt;
    &lt;/div&gt;
{/ctype:<?php echo html($fragment['system_name'] ?? 'имя_фрагмента'); ?>}</code>
                            <div class="text-muted small mt-1">
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_HOWTO_CUSTOM; ?>
                            </div>
                        </div>
                        
                        <div class="bg-light p-2 rounded">
                            <code class="small">{field:название_поля}</code>
                            <div class="text-muted small mt-1">
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_HOWTO_FIELD_VALUE; ?>
                            </div>
                            <code class="small mt-1 d-block">{field_display:название_поля}</code>
                            <div class="text-muted small mt-1">
                                <?php echo LANG_TEMPLATE_FRAGMENTS_FORM_HOWTO_FIELD_RENDERED; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>
                        <?php echo $isEdit ? LANG_TEMPLATE_FRAGMENTS_FORM_SAVE_BTN : LANG_TEMPLATE_FRAGMENTS_FORM_CREATE_BTN; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cssContainer = document.getElementById('css-files-container');
    const addCssBtn = document.getElementById('add-css-file');
    
    addCssBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 css-file-row';
        newRow.innerHTML = `
            <input type="text" name="css_files[]" class="form-control" value="" placeholder="templates/default/front/assets/css/fragment.css">
            <button type="button" class="btn btn-outline-danger remove-asset" data-type="css">
                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
            </button>
        `;
        cssContainer.appendChild(newRow);
        attachRemoveHandler(newRow.querySelector('.remove-asset'));
    });
    
    const jsContainer = document.getElementById('js-files-container');
    const addJsBtn = document.getElementById('add-js-file');
    
    addJsBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 js-file-row';
        newRow.innerHTML = `
            <input type="text" name="js_files[]" class="form-control" value="" placeholder="templates/default/front/assets/js/fragment.js">
            <button type="button" class="btn btn-outline-danger remove-asset" data-type="js">
                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
            </button>
        `;
        jsContainer.appendChild(newRow);
        attachRemoveHandler(newRow.querySelector('.remove-asset'));
    });
    
    function attachRemoveHandler(btn) {
        btn.addEventListener('click', function() {
            this.closest('.input-group').remove();
        });
    }
    
    document.querySelectorAll('.remove-asset').forEach(attachRemoveHandler);
    
    if (typeof ace !== 'undefined') {
        const cssEditor = ace.edit("inline-css-editor", {
            theme: "ace/theme/monokai",
            mode: "ace/mode/css",
            showPrintMargin: false,
            fontSize: "14px",
            tabSize: 4
        });
        
        const cssTextarea = document.getElementById('inline_css');
        if (cssTextarea.value) {
            cssEditor.setValue(cssTextarea.value);
        }
        
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                cssTextarea.value = cssEditor.getValue();
            });
        }
        
        const jsEditor = ace.edit("inline-js-editor", {
            theme: "ace/theme/monokai",
            mode: "ace/mode/javascript",
            showPrintMargin: false,
            fontSize: "14px",
            tabSize: 4
        });
        
        const jsTextarea = document.getElementById('inline_js');
        if (jsTextarea.value) {
            jsEditor.setValue(jsTextarea.value);
        }
        
        form.addEventListener('submit', function() {
            jsTextarea.value = jsEditor.getValue();
        });
    }
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>