<div class="row">
    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_NAME_LABEL; ?> <span class="text-danger">*</span></label>
            <input type="text" name="settings[site_name]" value="<?php echo $settings['site_name'] ?? ''; ?>" class="form-control" placeholder="<?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_NAME_PLACEHOLDER; ?>" required>
            <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_NAME_HINT; ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_TAGLINE_LABEL; ?></label>
            <input type="text" name="settings[site_tagline]" value="<?php echo $settings['site_tagline'] ?? ''; ?>" class="form-control" placeholder="<?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_TAGLINE_PLACEHOLDER; ?>">
            <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_TAGLINE_HINT; ?></div>
        </div>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'image', '16', '#000', 'me-2 controller-svg'); ?><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_FAVICON_TITLE; ?>
        </h6>
        
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_FAVICON_UPLOAD_LABEL; ?></label>
                    <input type="file" 
                           name="favicon_file" 
                           class="form-control" 
                           id="faviconInput"
                           accept=".ico,.png,.svg,.jpg,.jpeg,.gif,.webp,image/x-icon,image/png,image/svg+xml,image/jpeg,image/gif,image/webp">
                    <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_FAVICON_HINT; ?></div>
                </div>
                
                <?php if (!empty($settings['favicon'])) { ?>
                <div class="mb-2">
                    <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_FAVICON_CURRENT_LABEL; ?></label>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <img src="<?php echo BASE_URL . '/' . $settings['favicon']; ?>" 
                                 alt="Favicon" 
                                 style="max-width: 32px; max-height: 32px;" 
                                 id="currentFavicon"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'32\' height=\'32\' viewBox=\'0 0 32 32\'%3E%3Crect width=\'32\' height=\'32\' fill=\'%23f0f0f0\'/%3E%3Ctext x=\'16\' y=\'22\' font-size=\'14\' text-anchor=\'middle\' fill=\'%23999\' font-family=\'Arial\'%3E?%3C/text%3E%3C/svg%3E'">
                        </div>
                        <div>
                            <a href="<?php echo BASE_URL . '/' . $settings['favicon']; ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                <?php echo bloggy_icon('bs', 'eye', '14', '#0d6efd', 'me-1'); ?><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_FAVICON_VIEW_BTN; ?>
                            </a>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="remove_favicon" id="remove_favicon" value="1">
                                <label class="form-check-label text-danger" for="remove_favicon">
                                    <?php echo bloggy_icon('bs', 'trash', '14', '#dc3545', 'me-1'); ?><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_FAVICON_REMOVE_BTN; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <div class="col-md-4">
                <div class="favicon-preview border rounded p-3 text-center bg-white">
                    <label class="form-label text-muted small"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_FAVICON_PREVIEW_LABEL; ?></label>
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 64px;">
                        <img src="<?php echo !empty($settings['favicon']) ? BASE_URL . '/' . $settings['favicon'] : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'32\' height=\'32\' viewBox=\'0 0 32 32\'%3E%3Crect width=\'32\' height=\'32\' fill=\'%23f0f0f0\'/%3E%3Ctext x=\'16\' y=\'22\' font-size=\'14\' text-anchor=\'middle\' fill=\'%23999\' font-family=\'Arial\'%3E?%3C/text%3E%3C/svg%3E'; ?>" 
                             alt="Favicon preview" 
                             id="faviconPreview"
                             style="max-width: 64px; max-height: 64px; image-rendering: pixelated;">
                    </div>
                    <div class="mt-2 text-muted small">
                        <span id="faviconFormat">
                            <?php 
                            if (!empty($settings['favicon'])) {
                                $ext = pathinfo($settings['favicon'], PATHINFO_EXTENSION);
                                echo strtoupper($ext);
                            } else {
                                echo LANG_TEMPLATE_SETTINGS_SITE_TAB_FAVICON_NOT_SELECTED;
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_DESCRIPTION_LABEL; ?></label>
    <textarea name="settings[site_description]" class="form-control" rows="3" placeholder="<?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_DESCRIPTION_PLACEHOLDER; ?>"><?php echo html($settings['site_description'] ?? ''); ?></textarea>
    <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_DESCRIPTION_HINT; ?></div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_CONTACT_EMAIL_LABEL; ?></label>
            <input type="email" name="settings[contact_email]" value="<?php echo $settings['contact_email'] ?? ''; ?>" class="form-control" placeholder="contact@example.com">
            <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_CONTACT_EMAIL_HINT; ?></div>
        </div>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'translate', '16', '#000', 'me-2 controller-svg'); ?><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_LANGUAGE_REGION_TITLE; ?>
        </h6>
        
        <div class="mb-3">
            <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_DATE_TIME_FORMAT_LABEL; ?></label>
            <div class="row">
                <div class="col-md-6">
                    <select name="settings[date_format]" class="form-select mb-2">
                        <option value="d.m.Y" <?php echo ($settings['date_format'] ?? 'd.m.Y') === 'd.m.Y' ? 'selected' : ''; ?>>31.12.2025</option>
                        <option value="Y-m-d" <?php echo ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : ''; ?>>2025-12-31</option>
                        <option value="m/d/Y" <?php echo ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : ''; ?>>12/31/2025</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <select name="settings[time_format]" class="form-select">
                        <option value="H:i" <?php echo ($settings['time_format'] ?? 'H:i') === 'H:i' ? 'selected' : ''; ?>>23:59 (24ч)</option>
                        <option value="h:i A" <?php echo ($settings['time_format'] ?? '') === 'h:i A' ? 'selected' : ''; ?>>11:59 PM (12ч)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'search', '16', '#000', 'me-2 controller-svg'); ?><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SEO_SETTINGS_TITLE; ?>
        </h6>
        
        <div class="mb-3">
            <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_KEYWORDS_LABEL; ?></label>
            <textarea name="settings[meta_keywords]" class="form-control" rows="2" placeholder="<?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_KEYWORDS_PLACEHOLDER; ?>"><?php echo html($settings['meta_keywords'] ?? ''); ?></textarea>
            <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_KEYWORDS_HINT; ?></div>
        </div>
        
        <div class="mb-3">
            <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_AUTHOR_LABEL; ?></label>
            <input type="text" name="settings[site_author]" value="<?php echo $settings['site_author'] ?? ''; ?>" class="form-control" placeholder="<?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SITE_AUTHOR_PLACEHOLDER; ?>">
        </div>
        
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'gear', '16', '#000', 'me-2 controller-svg'); ?><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_SYSTEM_SETTINGS_TITLE; ?>
        </h6>
        
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="maintenance_mode" name="settings[maintenance_mode]" value="1" <?php echo isset($settings['maintenance_mode']) && $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="maintenance_mode"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_MAINTENANCE_MODE_LABEL; ?></label>
            </div>
            <div class="form-text"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_MAINTENANCE_MODE_HINT; ?></div>
        </div>
        
        <div class="mb-3">
            <label class="form-label"><?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_MAINTENANCE_MESSAGE_LABEL; ?></label>
            <textarea name="settings[maintenance_message]" class="form-control" rows="2" placeholder="<?php echo LANG_TEMPLATE_SETTINGS_SITE_TAB_MAINTENANCE_MESSAGE_PLACEHOLDER; ?>"><?php echo html($settings['maintenance_message'] ?? ''); ?></textarea>
        </div>
        
    </div>
</div>