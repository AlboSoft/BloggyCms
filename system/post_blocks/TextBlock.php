<?php
class TextBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_TEXT_NAME;
    }

    public function getSystemName(): string {
        return 'TextBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_TEXT_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-text-paragraph';
    }

    public function getCategory(): string {
        return 'text';
    }

    public function getTemplateWithShortcodes(): string {
        return '<div class="post-block-text {custom_class}">{content}</div>';
    }

    public function getDefaultContent(): array {
        return [
            'content' => '<p>' . LANG_POSTBLOCK_TEXT_DEFAULT_CONTENT . '</p>'
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'custom_class' => '',
            'text_align' => 'left',
            'font_size' => '',
            'line_height' => ''
        ];
    }

    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/rich-text-editor.js',
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $html = $content['content'] ?? $content['text'] ?? '';
        
        if (empty(trim(strip_tags($html)))) {
            foreach ($content as $value) {
                if (is_string($value) && trim($value) !== '') {
                    $html = $value;
                    break;
                }
            }
        }
        
        $alignment = $settings['text_align'] ?? 'left';
        $charCount = strlen(strip_tags($html));
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-TextBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-text-left"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_TEXT_PREVIEW_TITLE; ?></strong>
                                <?php if ($alignment !== 'left') { ?>
                                    <span class="badge bg-secondary badge-sm"><?= html($alignment) ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?= $charCount ?> <?php echo LANG_POSTBLOCK_TEXT_PREVIEW_CHARS; ?>
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
                
                <div class="preview-body full-text-content">
                    <?php if (!empty(trim(strip_tags($html)))) { ?>
                        <div class="text-content" style="text-align: <?= html($alignment) ?>;">
                            <?= $html ?>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-fonts"></i>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_TEXT_PREVIEW_EMPTY; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> <?php echo LANG_POSTBLOCK_TEXT_PREVIEW_ADD_BTN; ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    protected function getPreviewStats($content, $settings): string {
        $text = strip_tags($content['content'] ?? '');
        $charCount = strlen($text);
        
        $stats = [];
        $stats[] = $charCount . ' ' . LANG_POSTBLOCK_TEXT_PREVIEW_CHARS;
        
        if (!empty($settings['text_align']) && $settings['text_align'] !== 'left') {
            $stats[] = LANG_POSTBLOCK_TEXT_PREVIEW_ALIGN . $settings['text_align'];
        }
        
        return implode(' · ', $stats);
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $contentHtml = $currentContent['content'] ?? '';
        $editorId = 'rich-editor-' . uniqid();

        ob_start();
        ?>
        <div class="mb-4 rich-text-wrapper" id="<?= $editorId ?>">
            <label class="form-label"><?php echo LANG_POSTBLOCK_TEXT_FORM_CONTENT_LABEL; ?></label>
            
            <div class="rich-text-toolbar mb-2 border rounded-top p-2 bg-light">
                <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-command="bold" title="<?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_BOLD; ?>">
                    <i class="bi bi-type-bold"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-command="italic" title="<?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_ITALIC; ?>">
                    <i class="bi bi-type-italic"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-command="underline" title="<?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_UNDERLINE; ?>">
                    <i class="bi bi-type-underline"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-command="strikeThrough" title="<?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_STRIKE; ?>">
                    <i class="bi bi-type-strikethrough"></i>
                </button>
                <div class="vr mx-2"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-command="createLink" title="<?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_LINK; ?>">
                    <i class="bi bi-link-45deg"></i> <?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_LINK_BTN; ?>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger ms-1" data-command="unlink" title="<?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_UNLINK; ?>">
                    <i class="bi bi-link-45deg"></i>
                </button>
                <div class="vr mx-2"></div>
                <button type="button" class="btn btn-sm btn-outline-dark" data-command="formatCode" title="<?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_CODE; ?>">
                    <i class="bi bi-code-slash"></i> <?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_CODE_BTN; ?>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" data-command="removeFormat" title="<?php echo LANG_POSTBLOCK_TEXT_TOOLBAR_CLEAR; ?>">
                    <i class="bi bi-eraser"></i>
                </button>
            </div>

            <div class="rich-text-editor form-control border-top-0 rounded-top-0" 
                 contenteditable="true" 
                 style="min-height: 150px; font-family: inherit; outline: none;"
                 data-target="content[content]">
                <?= $contentHtml ?>
            </div>

            <textarea name="content[content]" 
                      class="d-none" 
                      id="hidden-content-<?= $editorId ?>"
                      required><?= html($contentHtml) ?></textarea>
            
            <div class="form-text"><?php echo LANG_POSTBLOCK_TEXT_FORM_HINT; ?></div>
        </div>

        <style>
            .rich-text-toolbar button.active {
                background-color: #0d6efd !important;
                color: white !important;
                border-color: #0d6efd !important;
            }
            .rich-text-toolbar button.active i {
                color: white !important;
            }
            .rich-text-editor code {
                background-color: #f4f4f4;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: 'Courier New', Courier, monospace;
                color: #d63384;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        
        $customClass = $currentSettings['custom_class'] ?? '';
        $textAlign = $currentSettings['text_align'] ?? 'left';
        $fontSize = $currentSettings['font_size'] ?? '';
        $lineHeight = $currentSettings['line_height'] ?? '';

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_TEXT_SETTINGS_ALIGN; ?></label>
                    <select name="settings[text_align]" class="form-select">
                        <option value="left" <?= $textAlign === 'left' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_TEXT_ALIGN_LEFT; ?></option>
                        <option value="center" <?= $textAlign === 'center' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_TEXT_ALIGN_CENTER; ?></option>
                        <option value="right" <?= $textAlign === 'right' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_TEXT_ALIGN_RIGHT; ?></option>
                        <option value="justify" <?= $textAlign === 'justify' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_TEXT_ALIGN_JUSTIFY; ?></option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_TEXT_SETTINGS_CSS_CLASS; ?></label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= html($customClass) ?>" 
                           placeholder="my-text-block">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_TEXT_SETTINGS_FONT_SIZE; ?></label>
                    <input type="text" 
                           name="settings[font_size]" 
                           class="form-control" 
                           value="<?= html($fontSize) ?>" 
                           placeholder="16px <?php echo LANG_POSTBLOCK_TEXT_PLACEHOLDER_OR; ?> 1rem">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_TEXT_SETTINGS_LINE_HEIGHT; ?></label>
                    <input type="text" 
                           name="settings[line_height]" 
                           class="form-control" 
                           value="<?= html($lineHeight) ?>" 
                           placeholder="1.5 <?php echo LANG_POSTBLOCK_TEXT_PLACEHOLDER_OR; ?> 24px">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        return parent::getEditorHtml($settings, $content);
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['content'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $textAlign = $settings['text_align'] ?? 'left';
        $fontSize = $settings['font_size'] ?? '';
        $lineHeight = $settings['line_height'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        if (empty(trim(strip_tags($text)))) {
            return LANG_POSTBLOCK_TEXT_EMPTY_COMMENT;
        }

        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . (int)$presetId;
            
            if (!empty($presetName)) {
                $cleanPresetName = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($presetName));
                $cleanPresetName = preg_replace('/-+/', '-', $cleanPresetName);
                $cleanPresetName = trim($cleanPresetName, '-');
                
                if (!empty($cleanPresetName)) {
                    $presetClass .= ' preset-' . $cleanPresetName;
                }
            }
        }

        $style = '';
        if ($fontSize) {
            $style .= 'font-size: ' . html($fontSize) . '; ';
        }
        if ($lineHeight) {
            $style .= 'line-height: ' . html($lineHeight) . '; ';
        }
        if ($textAlign && $textAlign !== 'left') {
            $style .= 'text-align: ' . html($textAlign) . '; ';
        }

        $result = $template;
        
        $result = str_replace('{custom_class}', trim($customClass . ' ' . $presetClass), $result);
        $safeText = strip_tags($text, '<b><i><u><s><a><br><p><div><span><strong><em><code>');
        
        $result = str_replace('{content}', $safeText, $result);
        
        if (!empty($style)) {
            $result = preg_replace(
                '/class="([^"]*)"/',
                'class="$1" style="' . $style . '"',
                $result
            );
        }
        
        $result = str_replace('{preset_id}', $presetId ? html($presetId) : '', $result);
        $result = str_replace('{preset_name}', $presetName ? html($presetName) : '', $result);
        $result = str_replace('{block_type}', $this->getSystemName(), $result);
        $result = str_replace('{block_name}', $this->getName(), $result);
        $result = str_replace('{text_align}', $textAlign, $result);
        $result = str_replace('{font_size}', html($fontSize), $result);
        $result = str_replace('{line_height}', html($lineHeight), $result);

        return $result;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{content}' => LANG_POSTBLOCK_TEXT_SHORTCODE_CONTENT,
            '{custom_class}' => LANG_POSTBLOCK_TEXT_SHORTCODE_CUSTOM_CLASS,
            '{text_align}' => LANG_POSTBLOCK_TEXT_SHORTCODE_TEXT_ALIGN,
            '{font_size}' => LANG_POSTBLOCK_TEXT_SHORTCODE_FONT_SIZE,
            '{line_height}' => LANG_POSTBLOCK_TEXT_SHORTCODE_LINE_HEIGHT
        ]);
    }

    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            if (isset($_POST['content']['content'])) {
                $content['content'] = trim($_POST['content']['content']);
            }
        }
        
        if (!isset($content['content'])) {
            $content['content'] = '<p>' . LANG_POSTBLOCK_TEXT_DEFAULT_CONTENT . '</p>';
        }

        return $content;
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            $settings = array_merge($settings, $_POST['settings']);
        }
        
        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
        }
        
        if (isset($settings['font_size'])) {
            $settings['font_size'] = trim($settings['font_size']);
        }
        
        if (isset($settings['line_height'])) {
            $settings['line_height'] = trim($settings['line_height']);
        }

        return $settings;
    }

    public function extractFromHtml(string $html): ?array {
        if (!empty(trim($html))) {
            return [
                'content' => $html
            ];
        }
        return null;
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_TEXT_VALIDATION_CSS_CLASS;
        }

        $allowedAlign = ['left', 'center', 'right', 'justify'];
        if (!empty($settings['text_align']) && !in_array($settings['text_align'], $allowedAlign)) {
            $errors[] = LANG_POSTBLOCK_TEXT_VALIDATION_TEXT_ALIGN;
        }

        return [empty($errors), $errors];
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['content' => $content];
        }
        
        if (!is_array($content)) {
            return ['content' => (string)$content];
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
        
        $defaults = $this->getDefaultSettings();
        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        
        return $settings;
    }
}