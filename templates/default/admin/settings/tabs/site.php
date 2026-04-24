<div class="mb-4">
    <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_TEMPLATE_LABEL; ?></label>
    <select name="settings[site_template]" class="form-select">
        <?php
        $currentTemplate = SettingsHelper::get('site', 'site_template', DEFAULT_TEMPLATE);
        $templatesPath = TEMPLATES_PATH;
        
        if (is_dir($templatesPath)) {
            $templates = array_diff(scandir($templatesPath), array('.', '..'));
            foreach ($templates as $template) {
                if (is_dir($templatesPath . '/' . $template)) {
                    $selected = ($currentTemplate === $template) ? 'selected' : '';
                    echo '<option value="' . html($template) . '" ' . $selected . '>' 
                        . html(ucfirst($template)) . '</option>';
                }
            }
        }
        ?>
    </select>
</div>
<div class="mb-4">
    <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_BASE_URL_LABEL; ?></label>
    <input type="text" name="settings[base_url]" value="<?php echo SettingsHelper::get('site', 'base_url', BASE_URL); ?>" class="form-control" placeholder="https://ваш-сайт.ru">
    <div class="form-text">
        <small class="text-muted">
            <?php echo bloggy_icon('bs', 'info-circle', '12', '#000', 'controller-svg'); ?> <?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_BASE_URL_HINT; ?>
            <br><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_BASE_URL_CURRENT; ?> <code><?php echo BASE_URL; ?></code>
        </small>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'files', '16', '#000', 'me-2 controller-svg'); ?><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_TITLE; ?>
        </h6>
        
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="template_backups_enabled" name="settings[template_backups_enabled]" value="1" <?php echo isset($settings['template_backups_enabled']) && $settings['template_backups_enabled'] ? 'checked' : ''; ?>>
                <label class="form-check-label fw-medium" for="template_backups_enabled"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_ENABLE_LABEL; ?></label>
            </div>
            <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_ENABLE_HINT; ?></div>
        </div>

        <div class="row ps-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_MAX_LABEL; ?></label>
                    <input type="number" name="settings[template_backups_count]" value="<?php echo $settings['template_backups_count'] ?? 5; ?>" class="form-control" min="1" max="20" <?php echo !isset($settings['template_backups_enabled']) || !$settings['template_backups_enabled'] ? 'disabled' : ''; ?>>
                    <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_MAX_HINT; ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_CLEANUP_LABEL; ?></label>
                    <select name="settings[template_backups_cleanup]" class="form-select"
                        <?php echo !isset($settings['template_backups_enabled']) || !$settings['template_backups_enabled'] ? 'disabled' : ''; ?>>
                        <option value="auto" <?php echo ($settings['template_backups_cleanup'] ?? 'auto') === 'auto' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_CLEANUP_AUTO; ?></option>
                        <option value="manual" <?php echo ($settings['template_backups_cleanup'] ?? '') === 'manual' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_CLEANUP_MANUAL; ?></option>
                        <option value="never" <?php echo ($settings['template_backups_cleanup'] ?? '') === 'never' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_CLEANUP_NEVER; ?></option>
                    </select>
                    <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_CLEANUP_HINT; ?></div>
                </div>
            </div>
        </div>

        <?php $backupStats = BackupHelper::getBackupStats();
            
            if ($backupStats['total_files'] > 0) { ?> 
            
                <div class="mt-3 p-3 bg-white rounded border">
                    <h6 class="mb-2"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_STATS_TITLE; ?></h6>
                    <div class="row small text-muted">
                        <div class="col-md-4">
                            <?php echo bloggy_icon('bs', 'folder', '14', '#6C6C6C', 'me-1'); ?> <?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_STATS_FILES; ?> <?php echo $backupStats['total_files']; ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo bloggy_icon('bs', 'hdd', '14', '#6C6C6C', 'me-1'); ?> <?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_STATS_SIZE; ?> <?php echo $backupStats['total_size']; ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo bloggy_icon('bs', 'clock', '14', '#6C6C6C', 'me-1'); ?> <?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_STATS_OLDEST; ?> <?php echo $backupStats['oldest_backup']; ?>
                        </div>
                    </div>
                    <?php if ($backupStats['total_files'] > 0) { ?>
                    <div class="mt-2">
                        <a href="<?php echo ADMIN_URL; ?>/settings/cleanup-backups" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_CLEANUP_CONFIRM; ?>')">
                            <?php echo bloggy_icon('bs', 'files', '18', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_TEMPLATE_BACKUPS_CLEANUP_BTN; ?>
                        </a>
                    </div>
                    <?php } ?>
                </div>
        <?php } ?>
    </div>
</div>