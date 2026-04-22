<?php
class ButtonBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_BUTTON_NAME;
    }

    public function getSystemName(): string {
        return 'ButtonBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_BUTTON_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-link-45deg';
    }

    public function getCategory(): string {
        return 'interactive';
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/buttonblock/button.css'
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? LANG_POSTBLOCK_BUTTON_DEFAULT_TEXT;
        $url = $content['url'] ?? '#';
        $target = $content['target'] ?? '_self';
        $size = $settings['size'] ?? 'medium';
        $alignment = $settings['alignment'] ?? 'left';
        $fullWidth = $settings['full_width'] ?? false;
        $iconBefore = $settings['icon_before'] ?? '';
        $iconAfter = $settings['icon_after'] ?? '';
        $download = $settings['download'] ?? false;
        $bgColor = $settings['bg_color'] ?? '';
        $textColor = $settings['text_color'] ?? '';
        $borderColor = $settings['border_color'] ?? '';
        $iconColor = $settings['icon_color'] ?? '';
        
        $sizeClass = $this->getSizeClass($size);
        $alignmentClass = $this->getAlignmentClass($alignment);
        $fullWidthClass = $fullWidth ? 'btn-block w-100' : '';
        
        $iconBeforeName = $this->cleanIconName($iconBefore);
        $iconAfterName = $this->cleanIconName($iconAfter);
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-ButtonBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <?= bloggy_icon('bs', 'link-45deg', '20 20') ?>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_TITLE; ?></strong>
                                <?php if ($size !== 'medium') { ?>
                                    <span class="badge bg-secondary badge-sm"><?= html($size) ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?= strlen($text) ?> <?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_CHARS; ?>
                                <?php if ($target === '_blank') { ?>
                                    · <?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_NEW_WINDOW; ?>
                                <?php } ?>
                                <?php if ($download) { ?>
                                    · <?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_DOWNLOAD; ?>
                                <?php } ?>
                                <?php if ($fullWidth) { ?>
                                    · <?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_FULL_WIDTH; ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="preview-actions">
                        <button type="button" class="btn btn-xs btn-outline-secondary preview-edit-btn" 
                                onclick="postBlocksManager.editBlock('{block_id}')">
                            <?= bloggy_icon('bs', 'pencil', '14 14') ?>
                        </button>
                    </div>
                </div>
                
                <div class="preview-body">
                    <?php if (!empty(trim($text))) { ?>
                        <div class="button-preview-container <?= html($alignmentClass) ?>">
                            <a href="#" 
                               class="btn <?= $sizeClass ?> <?= $fullWidthClass ?>"
                               style="pointer-events: none; cursor: default; opacity: 0.8;<?= $bgColor ? ' background-color: ' . $bgColor . ';' : '' ?><?= $textColor ? ' color: ' . $textColor . ';' : '' ?><?= $borderColor ? ' border-color: ' . $borderColor . ';' : '' ?>">
                                <?php if ($iconBeforeName) { ?>
                                    <?= $this->renderIcon($iconBeforeName, $iconColor, 'me-1') ?>
                                <?php } ?>
                                <?= html($text) ?>
                                <?php if ($iconAfterName) { ?>
                                    <?= $this->renderIcon($iconAfterName, $iconColor, 'ms-1') ?>
                                <?php } ?>
                            </a>
                            <div class="mt-2 small text-muted">
                                <span><?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_URL_LABEL; ?> <?= html($url) ?></span>
                                <?php if ($target === '_blank') { ?>
                                    <span class="ms-2"><?= bloggy_icon('bs', 'box-arrow-up-right', '12 12', null, 'me-1') ?><?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_NEW_WINDOW; ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <?= bloggy_icon('bs', 'link-45deg', '32 32', '#6c757d', 'mb-2') ?>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_EMPTY_TITLE; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <?= bloggy_icon('bs', 'plus-circle', '14 14', null, 'me-1') ?> <?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_SETUP_BTN; ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="post-block-button {alignment} {custom_class}">
            <a href="{url}" 
               class="btn {size} {full_width}" 
               target="{target}" 
               {rel_attribute}
               {download_attribute}
               style="background-color: {bg_color}; color: {text_color}; border-color: {border_color};">
                {icon_before}
                {text}
                {icon_after}
            </a>
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'text' => LANG_POSTBLOCK_BUTTON_DEFAULT_TEXT,
            'url' => '#',
            'target' => '_self'
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'size' => 'medium',
            'alignment' => 'left',
            'full_width' => false,
            'bg_color' => '',
            'text_color' => '',
            'border_color' => '',
            'icon_before' => '',
            'icon_after' => '',
            'icon_color' => '',
            'custom_class' => '',
            'download' => false,
            'rel' => ''
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $text = $currentContent['text'] ?? '';
        $url = $currentContent['url'] ?? '';
        $target = $currentContent['target'] ?? '_self';

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_FORM_TEXT_LABEL; ?> <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="content[text]" 
                           class="form-control" 
                           value="<?= html($text) ?>" 
                           placeholder="<?php echo LANG_POSTBLOCK_BUTTON_FORM_TEXT_PLACEHOLDER; ?>"
                           required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_FORM_URL_LABEL; ?> <span class="text-danger">*</span></label>
                    <input type="url" 
                           name="content[url]" 
                           class="form-control" 
                           value="<?= html($url) ?>" 
                           placeholder="https://example.ru"
                           required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_FORM_TARGET_LABEL; ?></label>
                    <select name="content[target]" class="form-select">
                        <option value="_self" <?= $target === '_self' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_TARGET_SELF; ?></option>
                        <option value="_blank" <?= $target === '_blank' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_TARGET_BLANK; ?></option>
                        <option value="_parent" <?= $target === '_parent' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_TARGET_PARENT; ?></option>
                        <option value="_top" <?= $target === '_top' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_TARGET_TOP; ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $size = $currentSettings['size'] ?? 'medium';
        $alignment = $currentSettings['alignment'] ?? 'left';
        $fullWidth = $currentSettings['full_width'] ?? false;
        $bgColor = $currentSettings['bg_color'] ?? '';
        $textColor = $currentSettings['text_color'] ?? '';
        $borderColor = $currentSettings['border_color'] ?? '';
        $iconBefore = $currentSettings['icon_before'] ?? '';
        $iconAfter = $currentSettings['icon_after'] ?? '';
        $iconColor = $currentSettings['icon_color'] ?? '';
        $customClass = $currentSettings['custom_class'] ?? '';
        $download = $currentSettings['download'] ?? false;
        $rel = $currentSettings['rel'] ?? '';

        $icons = [
            '' => LANG_POSTBLOCK_BUTTON_ICON_NONE,
            'arrow-right' => LANG_POSTBLOCK_BUTTON_ICON_ARROW_RIGHT,
            'arrow-left' => LANG_POSTBLOCK_BUTTON_ICON_ARROW_LEFT,
            'arrow-up' => LANG_POSTBLOCK_BUTTON_ICON_ARROW_UP,
            'arrow-down' => LANG_POSTBLOCK_BUTTON_ICON_ARROW_DOWN,
            'download' => LANG_POSTBLOCK_BUTTON_ICON_DOWNLOAD,
            'box-arrow-up-right' => LANG_POSTBLOCK_BUTTON_ICON_EXTERNAL,
            'heart' => LANG_POSTBLOCK_BUTTON_ICON_HEART,
            'heart-fill' => LANG_POSTBLOCK_BUTTON_ICON_HEART_FILL,
            'star' => LANG_POSTBLOCK_BUTTON_ICON_STAR,
            'star-fill' => LANG_POSTBLOCK_BUTTON_ICON_STAR_FILL,
            'play-fill' => LANG_POSTBLOCK_BUTTON_ICON_PLAY,
            'pause-fill' => LANG_POSTBLOCK_BUTTON_ICON_PAUSE,
            'info-circle' => LANG_POSTBLOCK_BUTTON_ICON_INFO,
            'check' => LANG_POSTBLOCK_BUTTON_ICON_CHECK,
            'check-lg' => LANG_POSTBLOCK_BUTTON_ICON_CHECK_LG,
            'plus' => LANG_POSTBLOCK_BUTTON_ICON_PLUS,
            'plus-lg' => LANG_POSTBLOCK_BUTTON_ICON_PLUS_LG,
            'search' => LANG_POSTBLOCK_BUTTON_ICON_SEARCH,
            'share' => LANG_POSTBLOCK_BUTTON_ICON_SHARE,
            'envelope' => LANG_POSTBLOCK_BUTTON_ICON_ENVELOPE,
            'telephone' => LANG_POSTBLOCK_BUTTON_ICON_PHONE,
            'calendar' => LANG_POSTBLOCK_BUTTON_ICON_CALENDAR,
            'cart' => LANG_POSTBLOCK_BUTTON_ICON_CART,
            'chat' => LANG_POSTBLOCK_BUTTON_ICON_CHAT,
            'chat-dots' => LANG_POSTBLOCK_BUTTON_ICON_CHAT_DOTS,
            'eye' => LANG_POSTBLOCK_BUTTON_ICON_EYE,
            'eye-slash' => LANG_POSTBLOCK_BUTTON_ICON_EYE_SLASH,
            'file-earmark' => LANG_POSTBLOCK_BUTTON_ICON_FILE,
            'file-earmark-pdf' => LANG_POSTBLOCK_BUTTON_ICON_PDF,
            'image' => LANG_POSTBLOCK_BUTTON_ICON_IMAGE,
            'camera' => LANG_POSTBLOCK_BUTTON_ICON_CAMERA,
            'gear' => LANG_POSTBLOCK_BUTTON_ICON_GEAR,
            'person' => LANG_POSTBLOCK_BUTTON_ICON_PERSON,
            'person-plus' => LANG_POSTBLOCK_BUTTON_ICON_PERSON_PLUS,
            'trash' => LANG_POSTBLOCK_BUTTON_ICON_TRASH,
            'pencil' => LANG_POSTBLOCK_BUTTON_ICON_PENCIL,
            'bookmark' => LANG_POSTBLOCK_BUTTON_ICON_BOOKMARK,
            'bookmark-check' => LANG_POSTBLOCK_BUTTON_ICON_BOOKMARK_CHECK,
            'link' => LANG_POSTBLOCK_BUTTON_ICON_LINK,
            'link-45deg' => LANG_POSTBLOCK_BUTTON_ICON_LINK_DEG,
            'newspaper' => LANG_POSTBLOCK_BUTTON_ICON_NEWSPAPER,
            'tag' => LANG_POSTBLOCK_BUTTON_ICON_TAG
        ];

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_SIZE; ?></label>
                    <select name="settings[size]" class="form-select">
                        <option value="small" <?= $size === 'small' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_SIZE_SMALL; ?></option>
                        <option value="medium" <?= $size === 'medium' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_SIZE_MEDIUM; ?></option>
                        <option value="large" <?= $size === 'large' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_SIZE_LARGE; ?></option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_ALIGNMENT; ?></label>
                    <select name="settings[alignment]" class="form-select">
                        <option value="left" <?= $alignment === 'left' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_ALIGN_LEFT; ?></option>
                        <option value="center" <?= $alignment === 'center' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_ALIGN_CENTER; ?></option>
                        <option value="right" <?= $alignment === 'right' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_BUTTON_ALIGN_RIGHT; ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_ICON_BEFORE; ?></label>
                    <select name="settings[icon_before]" class="form-select">
                        <?php foreach($icons as $value => $name) { ?>
                            <option value="<?= $value ?>" <?= $iconBefore === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php } ?>
                    </select>
                    <div class="form-text mt-1">
                        <?php if ($iconBefore) { ?>
                            <?= $this->renderIcon($iconBefore, $iconColor, 'me-1') ?>
                            <span class="ms-2 text-muted"><?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_LABEL; ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_ICON_AFTER; ?></label>
                    <select name="settings[icon_after]" class="form-select">
                        <?php foreach($icons as $value => $name) { ?>
                            <option value="<?= $value ?>" <?= $iconAfter === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php } ?>
                    </select>
                    <div class="form-text mt-1">
                        <?php if ($iconAfter) { ?>
                            <?= $this->renderIcon($iconAfter, $iconColor, 'me-1') ?>
                            <span class="ms-2 text-muted"><?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_LABEL; ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_ICON_COLOR; ?></label>
                    <input type="color" 
                           name="settings[icon_color]" 
                           class="form-control form-control-color" 
                           value="<?= html($iconColor) ?>">
                    <div class="form-text"><?php echo LANG_POSTBLOCK_BUTTON_ICON_COLOR_HINT; ?></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_BG_COLOR; ?></label>
                    <input type="color" 
                           name="settings[bg_color]" 
                           class="form-control form-control-color" 
                           value="<?= html($bgColor) ?>">
                    <div class="form-text"><?php echo LANG_POSTBLOCK_BUTTON_BG_COLOR_HINT; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_TEXT_COLOR; ?></label>
                    <input type="color" 
                           name="settings[text_color]" 
                           class="form-control form-control-color" 
                           value="<?= html($textColor) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_BORDER_COLOR; ?></label>
                    <input type="color" 
                           name="settings[border_color]" 
                           class="form-control form-control-color" 
                           value="<?= html($borderColor) ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_CUSTOM_CLASS; ?></label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= html($customClass) ?>" 
                           placeholder="my-custom-button">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_BUTTON_SETTINGS_REL; ?></label>
                    <input type="text" 
                           name="settings[rel]" 
                           class="form-control" 
                           value="<?= html($rel) ?>" 
                           placeholder="noopener noreferrer">
                    <div class="form-text"><?php echo LANG_POSTBLOCK_BUTTON_REL_HINT; ?></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[full_width]" 
                           id="full_width"
                           value="1" 
                           <?= $fullWidth ? 'checked' : '' ?>>
                    <label class="form-check-label" for="full_width">
                        <?php echo LANG_POSTBLOCK_BUTTON_FULL_WIDTH; ?>
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[download]" 
                           id="download"
                           value="1" 
                           <?= $download ? 'checked' : '' ?>>
                    <label class="form-check-label" for="download">
                        <?php echo LANG_POSTBLOCK_BUTTON_DOWNLOAD; ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 bg-light rounded">
            <div class="small text-muted mb-2"><?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_EXAMPLE; ?></div>
            <div class="text-<?= $alignment ?>">
                <a href="#" 
                   class="btn <?= $this->getSizeClass($size) ?> <?= $fullWidth ? 'btn-block w-100' : '' ?>"
                   style="<?= $bgColor ? 'background-color: ' . $bgColor . '; ' : '' ?><?= $textColor ? 'color: ' . $textColor . '; ' : '' ?><?= $borderColor ? 'border-color: ' . $borderColor . '; ' : '' ?>">
                    <?php if ($iconBefore) { ?>
                        <?= $this->renderIcon($iconBefore, $iconColor, 'me-1') ?>
                    <?php } ?>
                    <?php echo LANG_POSTBLOCK_BUTTON_PREVIEW_BUTTON_TEXT; ?>
                    <?php if ($iconAfter) { ?>
                        <?= $this->renderIcon($iconAfter, $iconColor, 'ms-1') ?>
                    <?php } ?>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? LANG_POSTBLOCK_BUTTON_DEFAULT_TEXT;
        $url = $content['url'] ?? '#';
        $size = $settings['size'] ?? 'medium';
        $alignment = $settings['alignment'] ?? 'left';

        $sizeClass = $this->getSizeClass($size);
        $alignmentClass = $this->getAlignmentClass($alignment);

        return '
        <div class="post-block-button-preview ' . $alignmentClass . '">
            <a href="' . html($url) . '" 
               class="btn ' . $sizeClass . '" 
               style="pointer-events: none; text-decoration: none;">
                ' . html($text) . '
            </a>
        </div>';
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? LANG_POSTBLOCK_BUTTON_DEFAULT_TEXT;
        $url = $content['url'] ?? '#';
        $target = $content['target'] ?? '_self';
        $size = $settings['size'] ?? 'medium';
        $alignment = $settings['alignment'] ?? 'left';
        $fullWidth = $settings['full_width'] ?? false;
        $bgColor = $settings['bg_color'] ?? '';
        $textColor = $settings['text_color'] ?? '';
        $borderColor = $settings['border_color'] ?? '';
        $iconBefore = $settings['icon_before'] ?? '';
        $iconAfter = $settings['icon_after'] ?? '';
        $iconColor = $settings['icon_color'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $download = $settings['download'] ?? false;
        $rel = $settings['rel'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        if (empty(trim($text))) {
            return LANG_POSTBLOCK_BUTTON_EMPTY_COMMENT;
        }

        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . (int)$presetId;
            if ($presetName) {
                $presetClass .= ' preset-' . preg_replace('/[^a-z0-9_-]/i', '-', strtolower($presetName));
            }
        }

        $iconBeforeHtml = '';
        $iconAfterHtml = '';
        
        if ($iconBefore) {
            $iconBeforeHtml = $this->renderIcon($iconBefore, $iconColor, 'me-1');
        }
        
        if ($iconAfter) {
            $iconAfterHtml = $this->renderIcon($iconAfter, $iconColor, 'ms-1');
        }

        $relAttribute = !empty($rel) ? 'rel="' . html($rel) . '"' : '';
        $downloadAttribute = $download ? 'download' : '';
        
        $sizeClass = $this->getSizeClass($size);
        $alignmentClass = $this->getAlignmentClass($alignment);
        $fullWidthClass = $fullWidth ? 'btn-block w-100' : '';
        
        $customStyles = '';
        if ($bgColor || $textColor || $borderColor) {
            $styles = [];
            if ($bgColor) $styles[] = "background-color: {$bgColor}";
            if ($textColor) $styles[] = "color: {$textColor}";
            if ($borderColor) $styles[] = "border-color: {$borderColor}";
            $customStyles = implode('; ', $styles);
        }

        $result = $template;
        
        $replacements = [
            '{text}' => html($text),
            '{url}' => html($url),
            '{target}' => html($target),
            '{size}' => $sizeClass,
            '{alignment}' => $alignmentClass,
            '{full_width}' => $fullWidthClass,
            '{bg_color}' => $bgColor,
            '{text_color}' => $textColor,
            '{border_color}' => $borderColor,
            '{icon_before}' => $iconBeforeHtml,
            '{icon_after}' => $iconAfterHtml,
            '{custom_class}' => trim($customClass . ' ' . $presetClass),
            '{rel_attribute}' => $relAttribute,
            '{download_attribute}' => $downloadAttribute,
            '{preset_id}' => $presetId ? html($presetId) : '',
            '{preset_name}' => $presetName ? html($presetName) : '',
            '{block_type}' => $this->getSystemName(),
            '{block_name}' => $this->getName()
        ];
        
        foreach ($replacements as $shortcode => $replacement) {
            $result = str_replace($shortcode, $replacement, $result);
        }

        return $result;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{text}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_TEXT,
            '{url}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_URL,
            '{target}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_TARGET,
            '{size}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_SIZE,
            '{alignment}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_ALIGNMENT,
            '{full_width}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_FULL_WIDTH,
            '{bg_color}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_BG_COLOR,
            '{text_color}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_TEXT_COLOR,
            '{border_color}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_BORDER_COLOR,
            '{icon_before}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_ICON_BEFORE,
            '{icon_after}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_ICON_AFTER,
            '{custom_class}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_CUSTOM_CLASS,
            '{rel_attribute}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_REL,
            '{download_attribute}' => LANG_POSTBLOCK_BUTTON_SHORTCODE_DOWNLOAD
        ]);
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_BUTTON_VALIDATION_CSS_CLASS;
        }

        $allowedSizes = ['small', 'medium', 'large'];
        if (!empty($settings['size']) && !in_array($settings['size'], $allowedSizes)) {
            $errors[] = LANG_POSTBLOCK_BUTTON_VALIDATION_SIZE;
        }

        $allowedAlignments = ['left', 'center', 'right'];
        if (!empty($settings['alignment']) && !in_array($settings['alignment'], $allowedAlignments)) {
            $errors[] = LANG_POSTBLOCK_BUTTON_VALIDATION_ALIGNMENT;
        }

        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        $currentSettings = $this->getBlockSettings();
        
        if (isset($_POST['settings']['full_width']) && ($_POST['settings']['full_width'] == '1' || $_POST['settings']['full_width'] == 'on')) {
            $settings['full_width'] = true;
        } elseif (!isset($_POST['settings']['full_width'])) {
            $settings['full_width'] = false;
        } else {
            $settings['full_width'] = $settings['full_width'] ?? ($currentSettings['full_width'] ?? false);
        }
        
        if (isset($_POST['settings']['download']) && ($_POST['settings']['download'] == '1' || $_POST['settings']['download'] == 'on')) {
            $settings['download'] = true;
        } elseif (!isset($_POST['settings']['download'])) {
            $settings['download'] = false;
        } else {
            $settings['download'] = $settings['download'] ?? ($currentSettings['download'] ?? false);
        }
        
        if (isset($_POST['settings']['size'])) {
            $settings['size'] = trim($_POST['settings']['size']);
        }
        
        if (isset($_POST['settings']['alignment'])) {
            $settings['alignment'] = trim($_POST['settings']['alignment']);
        }
        
        if (isset($_POST['settings']['bg_color'])) {
            $settings['bg_color'] = trim($_POST['settings']['bg_color']);
        }
        
        if (isset($_POST['settings']['text_color'])) {
            $settings['text_color'] = trim($_POST['settings']['text_color']);
        }
        
        if (isset($_POST['settings']['border_color'])) {
            $settings['border_color'] = trim($_POST['settings']['border_color']);
        }
        
        if (isset($_POST['settings']['icon_before'])) {
            $settings['icon_before'] = trim($_POST['settings']['icon_before']);
        }
        
        if (isset($_POST['settings']['icon_after'])) {
            $settings['icon_after'] = trim($_POST['settings']['icon_after']);
        }
        
        if (isset($_POST['settings']['icon_color'])) {
            $settings['icon_color'] = trim($_POST['settings']['icon_color']);
        }
        
        if (isset($_POST['settings']['custom_class'])) {
            $settings['custom_class'] = trim($_POST['settings']['custom_class']);
        }
        
        if (isset($_POST['settings']['rel'])) {
            $settings['rel'] = trim($_POST['settings']['rel']);
        }

        return $settings;
    }

    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            if (isset($_POST['content']['text'])) {
                $content['text'] = trim($_POST['content']['text']);
            }
            if (isset($_POST['content']['url'])) {
                $content['url'] = trim($_POST['content']['url']);
            }
            if (isset($_POST['content']['target'])) {
                $content['target'] = trim($_POST['content']['target']);
            }
        }
        
        if (!isset($content['text'])) {
            $content['text'] = LANG_POSTBLOCK_BUTTON_DEFAULT_TEXT;
        }
        if (!isset($content['url'])) {
            $content['url'] = '#';
        }
        if (!isset($content['target'])) {
            $content['target'] = '_self';
        }

        return $content;
    }

    private function getSizeClass($size): string {
        switch ($size) {
            case 'small': return 'btn-sm';
            case 'large': return 'btn-lg';
            default: return '';
        }
    }

    private function getAlignmentClass($alignment): string {
        switch ($alignment) {
            case 'center': return 'text-center';
            case 'right': return 'text-end';
            default: return 'text-start';
        }
    }

    private function cleanIconName($icon): string {
        if (empty($icon)) return '';
        if (strpos($icon, 'bi bi-') === 0) {
            return substr($icon, 6);
        }
        return $icon;
    }

    private function renderIcon($iconName, $iconColor = '', $class = ''): string {
        $iconName = $this->cleanIconName($iconName);
        if (empty($iconName)) return '';
        
        return bloggy_icon('bs', $iconName, '16 16', $iconColor, $class);
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<a[^>]+href="([^"]*)"[^>]*>(.*?)<\/a>/is', $html, $matches)) {
            $url = $matches[1];
            $text = trim(strip_tags($matches[2]));
            
            if (!empty($text)) {
                $target = '_self';
                if (preg_match('/target="([^"]*)"/i', $html, $targetMatch)) {
                    $target = $targetMatch[1];
                }
                
                return [
                    'text' => $text,
                    'url' => $url,
                    'target' => $target
                ];
            }
        }
        
        return null;
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['text' => $content, 'url' => '#', 'target' => '_self'];
        }
        
        if (!is_array($content)) {
            return ['text' => LANG_POSTBLOCK_BUTTON_DEFAULT_TEXT, 'url' => '#', 'target' => '_self'];
        }
        
        if (!isset($content['text'])) {
            $content['text'] = LANG_POSTBLOCK_BUTTON_DEFAULT_TEXT;
        }
        if (!isset($content['url'])) {
            $content['url'] = '#';
        }
        if (!isset($content['target'])) {
            $content['target'] = '_self';
        }
        
        return $content;
    }

    public function validateAndNormalizeSettings($settings): array {
        if (is_string($settings)) {
            $decoded = json_decode($settings, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        if (!is_array($settings)) {
            return [];
        }
        
        return $settings;
    }
}