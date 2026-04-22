<?php
class QuoteBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_QUOTE_NAME;
    }

    public function getSystemName(): string {
        return 'QuoteBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_QUOTE_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-chat-quote';
    }

    public function getCategory(): string {
        return 'text';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <blockquote class="post-block-quote {custom_class}">
            <div class="quote-text">{text}</div>
            <footer class="quote-footer">
                <cite class="quote-author">{author}</cite>
                <cite class="quote-source">{source}</cite>
            </footer>
        </blockquote>';
    }

    public function getDefaultContent(): array {
        return [
            'text' => LANG_POSTBLOCK_QUOTE_DEFAULT_TEXT,
            'author' => '',
            'source' => ''
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'alignment' => 'left',
            'custom_class' => ''
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $text = $currentContent['text'] ?? '';
        $author = $currentContent['author'] ?? '';
        $source = $currentContent['source'] ?? '';
        
        ob_start();
        ?>
        <div class="mb-3">
            <label class="form-label"><?php echo LANG_POSTBLOCK_QUOTE_FORM_TEXT_LABEL; ?> *</label>
            <textarea name="content[text]" 
                    class="form-control" 
                    rows="4" 
                    placeholder="<?php echo LANG_POSTBLOCK_QUOTE_FORM_TEXT_PLACEHOLDER; ?>"
                    required><?= html($text) ?></textarea>
        </div>
        
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label"><?php echo LANG_POSTBLOCK_QUOTE_FORM_AUTHOR_LABEL; ?></label>
                <input type="text" 
                    name="content[author]" 
                    class="form-control" 
                    value="<?= html($author) ?>" 
                    placeholder="<?php echo LANG_POSTBLOCK_QUOTE_FORM_AUTHOR_PLACEHOLDER; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label"><?php echo LANG_POSTBLOCK_QUOTE_FORM_SOURCE_LABEL; ?></label>
                <input type="text" 
                    name="content[source]" 
                    class="form-control" 
                    value="<?= html($source) ?>" 
                    placeholder="<?php echo LANG_POSTBLOCK_QUOTE_FORM_SOURCE_PLACEHOLDER; ?>">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        
        $alignment = $currentSettings['alignment'] ?? 'left';
        $customClass = $currentSettings['custom_class'] ?? '';

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_QUOTE_SETTINGS_ALIGNMENT; ?></label>
                    <select name="settings[alignment]" class="form-select">
                        <option value="left" <?= $alignment === 'left' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_QUOTE_ALIGN_LEFT; ?></option>
                        <option value="center" <?= $alignment === 'center' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_QUOTE_ALIGN_CENTER; ?></option>
                        <option value="right" <?= $alignment === 'right' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_QUOTE_ALIGN_RIGHT; ?></option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_QUOTE_SETTINGS_CUSTOM_CLASS; ?></label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= html($customClass) ?>" 
                           placeholder="my-quote">
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
        $text = $content['text'] ?? '';
        $author = $content['author'] ?? '';
        $source = $content['source'] ?? '';
        $alignment = $settings['alignment'] ?? 'left';
        $customClass = $settings['custom_class'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        
        if (empty($text)) {
            return '<!-- ' . LANG_POSTBLOCK_QUOTE_EMPTY_TEXT_COMMENT . ' -->';
        }

        $text = preg_replace('/^"(.*)"$/', '$1', $text);
        $text = trim($text);
        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . $presetId;
            if ($presetName) {
                $presetClass .= ' preset-' . preg_replace('/[^a-z0-9_-]/i', '-', strtolower($presetName));
            }
        }

        $result = $template;
        $result = str_replace('{text}', nl2br(html($text)), $result);
        $result = str_replace('{author}', html($author), $result);
        $result = str_replace('{source}', html($source), $result);
        $result = str_replace(
            'class="post-block-quote', 
            'class="post-block-quote align-' . $alignment . ' ' . $customClass . ' ' . $presetClass . ' ', 
            $result
        );
        
        $result = str_replace('{preset_id}', $presetId ? html($presetId) : '', $result);
        $result = str_replace('{preset_name}', $presetName ? html($presetName) : '', $result);
        $result = str_replace('{block_type}', $this->getSystemName(), $result);
        $result = str_replace('{block_name}', $this->getName(), $result);
        $result = str_replace('{custom_class}', $customClass . ' ' . $presetClass, $result);
        $result = str_replace('{alignment}', $alignment, $result);

        return $result;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{text}' => LANG_POSTBLOCK_QUOTE_SHORTCODE_TEXT,
            '{author}' => LANG_POSTBLOCK_QUOTE_SHORTCODE_AUTHOR,
            '{source}' => LANG_POSTBLOCK_QUOTE_SHORTCODE_SOURCE,
            '{alignment}' => LANG_POSTBLOCK_QUOTE_SHORTCODE_ALIGNMENT,
            '{custom_class}' => LANG_POSTBLOCK_QUOTE_SHORTCODE_CUSTOM_CLASS
        ]);
    }

    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            if (isset($_POST['content']['text'])) {
                $content['text'] = trim($_POST['content']['text']);
            }
            if (isset($_POST['content']['author'])) {
                $content['author'] = trim($_POST['content']['author']);
            }
            if (isset($_POST['content']['source'])) {
                $content['source'] = trim($_POST['content']['source']);
            }
        }
        if (!isset($content['text'])) {
            $content['text'] = LANG_POSTBLOCK_QUOTE_DEFAULT_TEXT;
        }
        
        if (!isset($content['author'])) {
            $content['author'] = '';
        }
        
        if (!isset($content['source'])) {
            $content['source'] = '';
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

        return $settings;
    }

    public function extractFromHtml(string $html): ?array {
        $cleanHtml = preg_replace('/\s+/', ' ', $html);
        
        if (preg_match('/<figure>.*?<blockquote[^>]*>.*?<p>"([^"]+)"<\/p>.*?<\/blockquote>.*?<figcaption[^>]*>([^,<]*)(?:, <cite[^>]*>([^<]+)<\/cite>)?.*?<\/figcaption>.*?<\/figure>/s', $cleanHtml, $matches)) {
            return [
                'text' => trim($matches[1]),
                'author' => trim($matches[2] ?? ''),
                'source' => trim($matches[3] ?? '')
            ];
        }
        
        if (preg_match('/<blockquote[^>]*>.*?<p>"([^"]+)"<\/p>.*?<\/blockquote>/s', $cleanHtml, $matches)) {
            return [
                'text' => trim($matches[1]),
                'author' => '',
                'source' => ''
            ];
        }

        if (preg_match('/<blockquote[^>]*>(.*?)<\/blockquote>/s', $cleanHtml, $matches)) {
            $text = trim(strip_tags($matches[1]));
            $text = preg_replace('/^"(.*)"$/', '$1', $text);
            return [
                'text' => $text,
                'author' => '',
                'source' => ''
            ];
        }
        
        if (preg_match('/"([^"]+)"/', $cleanHtml, $matches)) {
            return [
                'text' => trim($matches[1]),
                'author' => '',
                'source' => ''
            ];
        }
        
        return parent::extractFromHtml($html);
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_QUOTE_VALIDATION_CUSTOM_CLASS;
        }

        return [empty($errors), $errors];
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['text' => $content];
        }
        
        if (!is_array($content)) {
            return ['text' => (string)$content];
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
        
        $text = $content['text'] ?? '';
        $author = $content['author'] ?? '';
        $source = $content['source'] ?? '';
        $alignment = $settings['alignment'] ?? 'left';
        $customClass = $settings['custom_class'] ?? '';
        $previewText = preg_replace('/^"(.*)"$/', '$1', $text);
        $previewText = trim($previewText);
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-QuoteBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-chat-quote"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_QUOTE_PREVIEW_TITLE; ?></strong>
                                <?php if ($alignment !== 'left') { ?>
                                    <span class="badge bg-info badge-sm"><?= html($alignment) ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?php echo sprintf(LANG_POSTBLOCK_QUOTE_PREVIEW_CHARS, strlen($text)); ?>
                                <?php if ($author) { ?>
                                    · <?= html(mb_substr($author, 0, 15)) ?>
                                    <?php if (mb_strlen($author) > 15) { ?>...<?php } ?>
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
                    <?php if (!empty(trim($text))) { ?>
                        <div class="quote-preview-container text-<?= html($alignment) ?>">
                            <div class="quote-preview border-start border-3 border-primary ps-3 py-2 my-2" 
                                style="background: #f8f9fa; border-radius: 0 4px 4px 0;">
                                <div class="quote-text-preview" style="font-style: italic; color: #374151;">
                                    <i class="bi bi-quote me-1" style="font-size: 1.2em; color: #3b82f6;"></i>
                                    <?= html(mb_substr($previewText, 0, 100)) ?>
                                    <?php if (mb_strlen($previewText) > 100) { ?>...<?php } ?>
                                </div>
                                
                                <?php if ($author || $source) { ?>
                                    <div class="quote-meta mt-2 small text-muted">
                                        <?php if ($author) { ?>
                                            <div><i class="bi bi-person me-1"></i><?= html($author) ?></div>
                                        <?php } ?>
                                        <?php if ($source) { ?>
                                            <div><i class="bi bi-book me-1"></i><?= html($source) ?></div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="quote-preview-info mt-3 small text-muted">
                                <div class="row">
                                    <div class="col-6">
                                        <div>
                                            <i class="bi bi-align-<?= html($alignment) ?> me-1"></i>
                                            <?php echo LANG_POSTBLOCK_QUOTE_PREVIEW_INFO_ALIGN; ?> <strong><?= html($alignment) ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <?php if ($customClass) { ?>
                                            <div><i class="bi bi-tag me-1"></i><?php echo LANG_POSTBLOCK_QUOTE_PREVIEW_INFO_CLASS; ?> <strong><?= html($customClass) ?></strong></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-chat-quote"></i>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_QUOTE_PREVIEW_EMPTY_TEXT; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> <?php echo LANG_POSTBLOCK_QUOTE_PREVIEW_ADD_BTN; ?>
                            </button>
                            <div class="mt-3 small text-muted">
                                <i class="bi bi-info-circle"></i>
                                <?php echo LANG_POSTBLOCK_QUOTE_PREVIEW_INFO_TEXT; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/quoteblock/qoute.css',
        ];
    }

}