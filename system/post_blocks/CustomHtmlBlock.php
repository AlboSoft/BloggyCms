<?php
class CustomHtmlBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_CUSTOMHTML_NAME;
    }

    public function getSystemName(): string {
        return 'CustomHtmlBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_CUSTOMHTML_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-code-square';
    }

    public function getCategory(): string {
        return 'advanced';
    }

    public function getTemplateWithShortcodes(): string {
        return '{html_content}';
    }

    public function getDefaultContent(): array {
        return [
            'html_content' => LANG_POSTBLOCK_CUSTOMHTML_DEFAULT_CONTENT
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'custom_class' => ''
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $htmlContent = $currentContent['html_content'] ?? '';

        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_CUSTOMHTML_FORM_LABEL; ?> *</label>
            <div id="html-editor-container" style="height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
            <textarea name="content[html_content]" 
                     id="html-editor-textarea" 
                     style="display: none;"
                     required><?= html($htmlContent) ?></textarea>
            <div class="form-text">
                <?php echo LANG_POSTBLOCK_CUSTOMHTML_FORM_HINT; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $customClass = $currentSettings['custom_class'] ?? '';

        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_CUSTOMHTML_SETTINGS_CSS_CLASS; ?></label>
            <input type="text" 
                   name="settings[custom_class]" 
                   class="form-control" 
                   value="<?= html($customClass) ?>" 
                   placeholder="my-html-block">
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $htmlContent = $content['html_content'] ?? '';
        
        return '
        <div class="custom-html-block-preview card">
            <div class="card-header py-2 bg-dark text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-code-slash me-1"></i>' . LANG_POSTBLOCK_CUSTOMHTML_EDITOR_TITLE . '</span>
                    <span class="badge bg-secondary">' . mb_strlen($htmlContent) . ' ' . LANG_POSTBLOCK_CUSTOMHTML_EDITOR_CHARS . '</span>
                </div>
            </div>
            <div class="card-body">
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>' . LANG_POSTBLOCK_CUSTOMHTML_EDITOR_HINT . '
                </div>
            </div>
        </div>';
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{html_content}' => LANG_POSTBLOCK_CUSTOMHTML_SHORTCODE_HTML,
            '{custom_class}' => LANG_POSTBLOCK_CUSTOMHTML_SHORTCODE_CUSTOM_CLASS
        ]);
    }

    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/controllers/ace.js',
            'templates/default/admin/assets/js/controllers/mode-html.js',
            'templates/default/admin/assets/js/controllers/theme-monokai.js',
            'templates/default/admin/assets/js/blocks/custom-html.js'
        ];
    }

    public function validateSettings($settings): array {
        $errors = [];
        $settings = $this->validateAndNormalizeSettings($settings);
        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_CUSTOMHTML_VALIDATION_CSS_CLASS;
        }

        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        $settings = parent::prepareSettings($settings);
        
        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
        }

        return $settings;
    }

    public function prepareContent($content): array {
        $content = parent::prepareContent($content);

        if (isset($content['html_content'])) {
            $content['html_content'] = trim($content['html_content']);
        }

        return $content;
    }

    public function extractFromHtml(string $html): ?array {
        return null;
    }

    public function canExtractFromHtml(): bool {
        return false;
    }
    
    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $htmlContent = $content['html_content'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        
        if (empty($htmlContent)) {
            return LANG_POSTBLOCK_CUSTOMHTML_EMPTY_COMMENT;
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
        $result = '';
        
        $blockClasses = trim('custom-html-block ' . $customClass . ' ' . $presetClass);
        if (!empty($blockClasses)) {
            $result .= '<div class="' . html($blockClasses) . '">';
        }
        
        $result .= $htmlContent;
        
        if (!empty($blockClasses)) {
            $result .= '</div>';
        }
        
        $templateResult = $template;
        $replacements = [
            '{html_content}' => $result,
            '{custom_class}' => $customClass,
            '{preset_id}' => $presetId ? html($presetId) : '',
            '{preset_name}' => $presetName ? html($presetName) : '',
            '{block_type}' => $this->getSystemName(),
            '{block_name}' => $this->getName()
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $templateResult = str_replace($placeholder, $value, $templateResult);
        }
        
        return $templateResult;
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            $result = is_array($decoded) ? $decoded : ['html_content' => $content];
            
            if (!isset($result['html_content'])) {
                $result['html_content'] = $content;
            }
            
            return $result;
        }
        
        if (!is_array($content)) {
            return ['html_content' => (string)$content];
        }
        
        if (!isset($content['html_content'])) {
            $content['html_content'] = '';
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

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $htmlContent = $content['html_content'] ?? LANG_POSTBLOCK_CUSTOMHTML_DEFAULT_CONTENT;
        $customClass = $settings['custom_class'] ?? '';
        
        $previewHtml = html($htmlContent);
        $htmlLength = strlen($htmlContent);
        
        if (strlen($previewHtml) > 150) {
            $previewHtml = substr($previewHtml, 0, 150) . '...';
        }
        
        $tagCount = substr_count($htmlContent, '<') - substr_count($htmlContent, '</');
        if ($tagCount < 0) $tagCount = 0;
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-CustomHtmlBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-code-square"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_TITLE; ?></strong>
                                <?php if ($customClass) { ?>
                                    <span class="badge bg-secondary badge-sm"><?= html($customClass) ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?= $htmlLength ?> <?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_CHARS; ?>
                                <?php if ($tagCount > 0) { ?>
                                    · <?= $tagCount ?> <?php echo plural_form($tagCount, explode('|', LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_TAGS)); ?>
                                <?php } ?>
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
                    <?php if (!empty(trim($htmlContent)) && trim($htmlContent) !== LANG_POSTBLOCK_CUSTOMHTML_DEFAULT_CONTENT) { ?>
                        <div class="custom-html-preview-container">
                            <div class="html-code-preview border rounded bg-dark text-light mb-3">
                                <div class="html-preview-header d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <span class="small"><i class="bi bi-code me-1"></i><?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_CODE_TITLE; ?></span>
                                    <span class="badge bg-info"><?= $htmlLength ?> <?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_CHARS; ?></span>
                                </div>
                                <div class="html-preview-content p-3">
                                    <pre class="m-0" style="font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.4; white-space: pre-wrap; word-break: break-all; color: #e9ecef;">
                                    <code><?= $previewHtml ?></code>
                                    </pre>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning p-2 small mb-0">
                                <i class="bi bi-shield-exclamation me-1"></i>
                                <strong><?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_SECURITY_TITLE; ?></strong> <?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_SECURITY_TEXT; ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-code-square"></i>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_EMPTY_TITLE; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> <?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_ADD_BTN; ?>
                            </button>
                            <div class="mt-3 small text-muted">
                                <i class="bi bi-info-circle"></i>
                                <?php echo LANG_POSTBLOCK_CUSTOMHTML_PREVIEW_EMPTY_HINT; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}