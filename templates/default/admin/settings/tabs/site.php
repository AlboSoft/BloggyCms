<div class="mb-4">
    <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_TEMPLATE_LABEL; ?></label>
    <select name="settings[site_template]" class="form-select">
        <?php
        $currentTemplate = SettingsHelper::get('site', 'site_template', DEFAULT_TEMPLATE);
        $templatesPath = TEMPLATES_PATH;
        
        if (is_dir($templatesPath)) {
            $templates = array_diff(@scandir($templatesPath) ?: [], array('.', '..'));
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
