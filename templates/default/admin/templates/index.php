<div class="template-designer">
    <div class="designer-header">
        <div class="header-left">
            <div class="header-icon">
                <?php echo bloggy_icon('bs', 'palette', '24', '#0d6efd'); ?>
            </div>
            <div class="header-info">
                <h1><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_TITLE; ?></h1>
                <p><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_SUBTITLE; ?></p>
            </div>
        </div>
        <div class="header-right">
            <a href="<?php echo ADMIN_URL; ?>/settings/cleanup-backups" class="btn-header btn-danger">
                <?php echo bloggy_icon('bs', 'trash', '14', '', 'me-1'); ?>
                <?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_CLEANUP_BACKUPS_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/settings?tab=site" class="btn-header btn-secondary">
                <?php echo bloggy_icon('bs', 'gear', '14', '', 'me-1'); ?>
                <?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_SETTINGS_BTN; ?>
            </a>
        </div>
    </div>

    <div class="designer-toolbar">
        <div class="toolbar-section">
            <div class="template-badge">
                <span class="badge-label"><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_ACTIVE_TEMPLATE_LABEL; ?></span>
                <div class="template-selector-group">
                    <?php foreach ($templates as $template) { ?>
                        <button class="template-pill template-selector <?php echo $template['name'] === $currentTemplate ? 'active' : ''; ?>" 
                                data-template="<?php echo $template['name']; ?>">
                            <?php echo bloggy_icon('bs', 'folder', '12', '', 'me-1'); ?>
                            <?php echo html(ucfirst($template['name'])); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
        
        <div class="toolbar-section">
            <div class="search-box">
                <?php echo bloggy_icon('bs', 'search', '14', '#9ca3af', 'search-icon'); ?>
                <input type="text" id="searchFiles" placeholder="<?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_SEARCH_PLACEHOLDER; ?>">
                <span class="search-shortcut">Ctrl+F</span>
            </div>
            <div class="view-switcher">
                <button class="view-switch active" id="switchTree" title="<?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_TREE_VIEW_TITLE; ?>">
                    <?php echo bloggy_icon('bs', 'diagram-3', '16'); ?>
                </button>
                <button class="view-switch" id="switchList" title="<?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_LIST_VIEW_TITLE; ?>">
                    <?php echo bloggy_icon('bs', 'list-ul', '16'); ?>
                </button>
            </div>
            <button class="toolbar-action" id="uploadFileBtn">
                <?php echo bloggy_icon('bs', 'upload', '14', '', 'me-1'); ?>
                <?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_UPLOAD_BTN; ?>
            </button>
            <input type="file" id="fileUpload" style="display: none;">
            <button class="toolbar-action" id="refreshFilesBtn">
                <?php echo bloggy_icon('bs', 'arrow-clockwise', '14'); ?>
            </button>
        </div>
    </div>

    <div class="designer-main">
        <div class="explorer-panel">
            <div class="panel-header">
                <div class="panel-title">
                    <?php echo bloggy_icon('bs', 'folder2-open', '14', '#0d6efd'); ?>
                    <span><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_FILES_PANEL_TITLE; ?></span>
                    <span class="file-counter" id="fileCounter">0</span>
                </div>
                <div class="panel-actions">
                    <button class="panel-action" id="collapseAllFolders" title="<?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_COLLAPSE_ALL_TITLE; ?>">
                        <?php echo bloggy_icon('bs', 'chevron-bar-contract', '12'); ?>
                    </button>
                    <button class="panel-action" id="expandAllFolders" title="<?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_EXPAND_ALL_TITLE; ?>">
                        <?php echo bloggy_icon('bs', 'chevron-bar-expand', '12'); ?>
                    </button>
                </div>
            </div>
            <div class="explorer-content" id="fileListContainer">
                <div id="fileList" class="file-list">
                    <div class="loading-state">
                        <div class="spinner"></div>
                        <p><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_LOADING_FILES; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="editor-panel">
            <div class="panel-header">
                <div class="panel-title" id="currentFileTitle">
                    <?php echo bloggy_icon('bs', 'file-code', '14', '#0d6efd'); ?>
                    <span><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_EDITOR_PANEL_TITLE; ?></span>
                </div>
                <div class="panel-actions" id="editorActions" style="display: none;">
                    <button class="panel-action" id="refreshFile" title="<?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_REFRESH_TITLE; ?>">
                        <?php echo bloggy_icon('bs', 'arrow-clockwise', '14'); ?>
                    </button>
                    <button class="panel-action btn-save" id="saveFile" disabled title="<?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_SAVE_TITLE; ?>">
                        <?php echo bloggy_icon('bs', 'check-lg', '14'); ?>
                        <span><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_SAVE_BTN; ?></span>
                    </button>
                </div>
            </div>
            <div class="editor-content">
                <div id="editorContainer" style="display: none;">
                    <div id="codeEditor"></div>
                </div>
                <div id="editorPlaceholder" class="editor-placeholder">
                    <div class="placeholder-icon">
                        <?php echo bloggy_icon('bs', 'code-slash', '64', '#dee2e6'); ?>
                    </div>
                    <h4><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_PLACEHOLDER_TITLE; ?></h4>
                    <p><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_PLACEHOLDER_DESC; ?></p>
                </div>
            </div>
        </div>

        <div class="info-panel" id="fileInfoPanel" style="display: none;">
            <div class="panel-header">
                <div class="panel-title">
                    <?php echo bloggy_icon('bs', 'info-circle', '14', '#0d6efd'); ?>
                    <span><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_INFO_PANEL_TITLE; ?></span>
                </div>
                <button class="panel-action" id="closeInfoPanel">
                    <?php echo bloggy_icon('bs', 'x', '12'); ?>
                </button>
            </div>
            <div class="info-content">
                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label"><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_INFO_FILENAME; ?></div>
                        <div class="info-value" id="infoFileName">—</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_INFO_SIZE; ?></div>
                        <div class="info-value" id="infoFileSize">—</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_INFO_PATH; ?></div>
                        <div class="info-value mono" id="infoFilePath">—</div>
                    </div>
                    <div class="info-row" id="infoDescRow" style="display: none;">
                        <div class="info-label"><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_INFO_DESCRIPTION; ?></div>
                        <div class="info-value" id="infoFileDescription">—</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_INFO_MODIFIED; ?></div>
                        <div class="info-value" id="infoFileUpdated">—</div>
                    </div>
                </div>
                <div class="info-actions">
                    <button class="btn-download" id="downloadFileBtn">
                        <?php echo bloggy_icon('bs', 'download', '14', '', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_TEMPLATES_MANAGER_DOWNLOAD_BTN; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
    <script>
        const CURRENT_TEMPLATE = '<?php echo $currentTemplate; ?>';
    </script>
<?php admin_bottom_js(ob_get_clean()); ?>

<?php
    add_admin_js('templates/default/admin/assets/js/controllers/ace.js');
    add_admin_js('templates/default/admin/assets/js/controllers/ext-language_tools.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-php.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-html.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-css.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-javascript.js');
    add_admin_js('templates/default/admin/assets/js/controllers/templates-manager.js');
    add_admin_css('templates/default/admin/assets/css/templates.css');
?>