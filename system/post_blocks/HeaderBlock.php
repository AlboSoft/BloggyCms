<?php
class HeaderBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_HEADER_NAME;
    }

    public function getSystemName(): string {
        return 'HeaderBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_HEADER_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-type-h1';
    }

    public function getCategory(): string {
        return 'text';
    }

    public function getTemplateWithShortcodes(): string {
        return '<{level} class="post-block-header {alignment} {custom_class}"{style}>{text}</{level}>';
    }

    public function getDefaultContent(): array {
        return [
            'text' => LANG_POSTBLOCK_HEADER_DEFAULT_TEXT
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'level' => 'h2',
            'alignment' => 'left',
            'text_color' => '',
            'custom_class' => ''
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? LANG_POSTBLOCK_HEADER_DEFAULT_TEXT;
        $level = $settings['level'] ?? 'h2';
        $alignment = $settings['alignment'] ?? 'left';
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-HeaderBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-type-h1"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_HEADER_PREVIEW_TITLE; ?></strong>
                                <span class="badge bg-secondary badge-sm"><?= strtoupper($level) ?></span>
                            </div>
                            <div class="preview-stats">
                                <?= strlen($text) ?> <?php echo LANG_POSTBLOCK_HEADER_PREVIEW_CHARS; ?>
                            </div>
                        </div>
                    </div>
                    <div class="preview-actions">
                        <button type="button" class="btn btn-xs btn-outline-secondary preview-edit-btn" 
                                onclick="postBlocksManager.editBlock('{block_id}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
                
                <div class="preview-body">
                    <?php if (!empty(trim($text))) { ?>
                        <<?= $level ?> class="header-content" style="text-align: <?= html($alignment) ?>; margin: 0;">
                            <?= html($text) ?>
                        </<?= $level ?>>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-type-h1"></i>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_HEADER_PREVIEW_EMPTY_TITLE; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> <?php echo LANG_POSTBLOCK_HEADER_PREVIEW_ADD_BTN; ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function renderPreviewContent($content, $settings): string {
        $url = $content['url'] ?? '';
        $alt = $content['alt'] ?? '';
        $alignment = $settings['alignment'] ?? 'center';
        $size = $settings['size'] ?? 'medium';
        
        if (empty($url)) {
            return '';
        }
        
        $sizeClass = $this->getSizeClass($size);
        
        ob_start();
        ?>
        <div class="image-preview-container text-<?= html($alignment) ?>">
            <div class="position-relative d-inline-block <?= $sizeClass ?>">
                <img src="<?= html($url) ?>" 
                     alt="<?= html($alt) ?>"
                     class="preview-image"
                     onerror="this.onerror=null; this.classList.add('image-error')">
                <?php if (!empty($size) && $size !== 'medium') { ?>
                    <span class="badge bg-dark position-absolute top-0 end-0 m-1">
                        <?= html($size) ?>
                    </span>
                <?php } ?>
            </div>
            <?php if (!empty($alt)) { ?>
                <div class="mt-2 small text-muted">
                    <i class="bi bi-card-text me-1"></i>
                    <?= html(mb_substr($alt, 0, 60)) ?>
                    <?php if (mb_strlen($alt) > 60) { ?>...<?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function getSizeClass($size): string {
        $sizes = [
            'small' => 'preview-image-sm',
            'medium' => 'preview-image-md',
            'large' => 'preview-image-lg',
            'full' => 'preview-image-full'
        ];
        
        return $sizes[$size] ?? 'preview-image-md';
    }
    
    protected function getPreviewStats($content, $settings): string {
        $stats = [];
        
        if (!empty($content['url'])) {
            $stats[] = LANG_POSTBLOCK_HEADER_STATS_IMAGE_UPLOADED;
        }
        
        if (!empty($content['alt'])) {
            $stats[] = LANG_POSTBLOCK_HEADER_STATS_HAS_ALT;
        }
        
        if (!empty($settings['size']) && $settings['size'] !== 'medium') {
            $stats[] = $settings['size'];
        }
        
        return implode(' · ', $stats);
    }
    
    protected function getEmptyIcon(): string {
        return 'bi bi-image';
    }
    
    protected function getEmptyText(): string {
        return LANG_POSTBLOCK_HEADER_EMPTY_TEXT;
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $text = $currentContent['text'] ?? '';
        
        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_HEADER_FORM_TEXT_LABEL; ?></label>
            <input type="text" 
                   name="content[text]" 
                   class="form-control form-control-lg" 
                   value="<?= html($text) ?>" 
                   placeholder="<?php echo LANG_POSTBLOCK_HEADER_FORM_TEXT_PLACEHOLDER; ?>"
                   required>
            <div class="form-text"><?php echo LANG_POSTBLOCK_HEADER_FORM_TEXT_HINT; ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $level = $currentSettings['level'] ?? 'h2';
        $alignment = $currentSettings['alignment'] ?? 'left';
        $textColor = $currentSettings['text_color'] ?? '';
        $customClass = $currentSettings['custom_class'] ?? '';

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_HEADER_SETTINGS_LEVEL; ?></label>
                    <select name="settings[level]" class="form-select">
                        <option value="h1" <?= $level === 'h1' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_LEVEL_H1; ?></option>
                        <option value="h2" <?= $level === 'h2' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_LEVEL_H2; ?></option>
                        <option value="h3" <?= $level === 'h3' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_LEVEL_H3; ?></option>
                        <option value="h4" <?= $level === 'h4' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_LEVEL_H4; ?></option>
                        <option value="h5" <?= $level === 'h5' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_LEVEL_H5; ?></option>
                        <option value="h6" <?= $level === 'h6' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_LEVEL_H6; ?></option>
                    </select>
                    <div class="form-text"><?php echo LANG_POSTBLOCK_HEADER_SETTINGS_LEVEL_HINT; ?></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_HEADER_SETTINGS_ALIGNMENT; ?></label>
                    <select name="settings[alignment]" class="form-select">
                        <option value="left" <?= $alignment === 'left' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_ALIGN_LEFT; ?></option>
                        <option value="center" <?= $alignment === 'center' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_ALIGN_CENTER; ?></option>
                        <option value="right" <?= $alignment === 'right' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_ALIGN_RIGHT; ?></option>
                        <option value="justify" <?= $alignment === 'justify' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_HEADER_ALIGN_JUSTIFY; ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_HEADER_SETTINGS_TEXT_COLOR; ?></label>
                    <input type="color" 
                           name="settings[text_color]" 
                           class="form-control form-control-color" 
                           value="<?= html($textColor) ?>" 
                           title="<?php echo LANG_POSTBLOCK_HEADER_COLOR_TITLE; ?>">
                    <div class="form-text"><?php echo LANG_POSTBLOCK_HEADER_TEXT_COLOR_HINT; ?></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_HEADER_SETTINGS_CSS_CLASS; ?></label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= html($customClass) ?>" 
                           placeholder="my-custom-class"
                           pattern="[a-zA-Z0-9-_ ]+">
                    <div class="form-text"><?php echo LANG_POSTBLOCK_HEADER_CSS_CLASS_HINT; ?></div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong><?php echo LANG_POSTBLOCK_HEADER_SEO_TIP_TITLE; ?></strong> <?php echo LANG_POSTBLOCK_HEADER_SEO_TIP_TEXT; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $settings = $this->validateAndNormalizeSettings($settings);
        $content = $this->validateAndNormalizeContent($content);
        
        $level = $settings['level'] ?? 'h2';
        $text = $content['text'] ?? LANG_POSTBLOCK_HEADER_DEFAULT_TEXT;
        $alignment = $settings['alignment'] ?? 'left';
        $textColor = $settings['text_color'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $class = trim("post-block-header {$alignment} {$customClass}");
        $style = $textColor ? " style=\"color: {$textColor}\"" : '';

        return "<{$level} class=\"{$class}\"{$style}>" . html($text) . "</{$level}>";
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{text}' => LANG_POSTBLOCK_HEADER_SHORTCODE_TEXT,
            '{level}' => LANG_POSTBLOCK_HEADER_SHORTCODE_LEVEL,
            '{alignment}' => LANG_POSTBLOCK_HEADER_SHORTCODE_ALIGNMENT,
            '{custom_class}' => LANG_POSTBLOCK_HEADER_SHORTCODE_CUSTOM_CLASS,
            '{style}' => LANG_POSTBLOCK_HEADER_SHORTCODE_STYLE,
            '{text_color}' => LANG_POSTBLOCK_HEADER_SHORTCODE_TEXT_COLOR
        ]);
    }

    public function validateSettings($settings): array {
        $errors = [];
        $settings = $this->validateAndNormalizeSettings($settings);

        if (!empty($settings['level']) && !in_array($settings['level'], ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
            $errors[] = LANG_POSTBLOCK_HEADER_VALIDATION_LEVEL;
        }

        if (!empty($settings['alignment']) && !in_array($settings['alignment'], ['left', 'center', 'right', 'justify'])) {
            $errors[] = LANG_POSTBLOCK_HEADER_VALIDATION_ALIGNMENT;
        }

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_HEADER_VALIDATION_CSS_CLASS;
        }

        if (!empty($settings['text_color']) && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $settings['text_color'])) {
            $errors[] = LANG_POSTBLOCK_HEADER_VALIDATION_TEXT_COLOR;
        }

        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        $settings = parent::prepareSettings($settings);
        if (isset($settings['level'])) {
            $settings['level'] = strtolower($settings['level']);
            $validLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
            if (!in_array($settings['level'], $validLevels)) {
                $settings['level'] = 'h2';
            }
        }
        
        if (isset($settings['alignment'])) {
            $validAlignments = ['left', 'center', 'right', 'justify'];
            if (!in_array($settings['alignment'], $validAlignments)) {
                $settings['alignment'] = 'left';
            }
        }
        
        if (isset($settings['text_color'])) {
            $settings['text_color'] = trim($settings['text_color']);
            if (empty($settings['text_color']) || $settings['text_color'] === '#000000') {
                unset($settings['text_color']);
            }
        }
        
        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
            if (empty($settings['custom_class'])) {
                unset($settings['custom_class']);
            }
        }

        return $settings;
    }

    public function prepareContent($content): array {
        $content = parent::prepareContent($content);
        if (isset($content['text'])) {
            $content['text'] = trim($content['text']);
            if (mb_strlen($content['text']) > 200) {
                $content['text'] = mb_substr($content['text'], 0, 200) . '...';
            }
        } else {
            $content['text'] = LANG_POSTBLOCK_HEADER_DEFAULT_TEXT;
        }

        return $content;
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<h([1-6])[^>]*>(.*?)<\/h\1>/i', $html, $matches)) {
            $text = trim(strip_tags($matches[2]));
            if (!empty($text)) {
                return [
                    'text' => $text
                ];
            }
        }
        
        $plainText = trim(strip_tags($html));
        if (!empty($plainText) && strlen($plainText) < 200) {
            return ['text' => $plainText];
        }
        
        return null;
    }

    public function canExtractFromHtml(): bool {
        return true;
    }

    public function getSeoRecommendations(): array {
        return [
            LANG_POSTBLOCK_HEADER_SEO_RECOMMENDATION_1,
            LANG_POSTBLOCK_HEADER_SEO_RECOMMENDATION_2,
            LANG_POSTBLOCK_HEADER_SEO_RECOMMENDATION_3,
            LANG_POSTBLOCK_HEADER_SEO_RECOMMENDATION_4,
            LANG_POSTBLOCK_HEADER_SEO_RECOMMENDATION_5
        ];
    }

    public function checkSeoOptimization($text, $level): array {
        $warnings = [];
        $textLength = mb_strlen($text);

        if ($level === 'h1') {
            if ($textLength < 20) {
                $warnings[] = LANG_POSTBLOCK_HEADER_SEO_H1_SHORT;
            }
            if ($textLength > 70) {
                $warnings[] = LANG_POSTBLOCK_HEADER_SEO_H1_LONG;
            }
        } else {
            if ($textLength > 150) {
                $warnings[] = sprintf(LANG_POSTBLOCK_HEADER_SEO_OTHER_LONG, strtoupper($level));
            }
        }

        return $warnings;
    }
    
    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? '';
        $level = $settings['level'] ?? 'h2';
        $alignment = $settings['alignment'] ?? 'left';
        $textColor = $settings['text_color'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        $style = '';
        if (!empty($textColor)) {
            $style = ' style="color: ' . html($textColor) . '"';
        }
        
        $result = $template;
        $replacements = [
            '{text}' => html($text),
            '{level}' => $level,
            '{alignment}' => $alignment,
            '{custom_class}' => $customClass,
            '{style}' => $style,
            '{text_color}' => $textColor,
            '{preset_id}' => $presetId ? html($presetId) : '',
            '{preset_name}' => $presetName ? html($presetName) : '',
            '{block_type}' => $this->getSystemName(),
            '{block_name}' => $this->getName()
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $result = str_replace($placeholder, $value, $result);
        }
        
        return $result;
    }
}