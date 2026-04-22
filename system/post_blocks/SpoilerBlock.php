<?php
class SpoilerBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_SPOILER_NAME;
    }

    public function getSystemName(): string {
        return 'SpoilerBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_SPOILER_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-eye';
    }

    public function getCategory(): string {
        return 'interactive';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="post-block-spoiler {custom_class} {show_default} {no_animation_class}" data-spoiler>
            <div class="spoiler-header">
                <button type="button" class="spoiler-toggle {icon_position}" aria-expanded="{aria_expanded}">
                    {icon_before}
                    <span class="spoiler-title">{title}</span>
                    {icon_after}
                </button>
            </div>
            <div class="spoiler-content" aria-hidden="{aria_hidden}">
                <div class="spoiler-body">
                    {content}
                </div>
            </div>
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'title' => LANG_POSTBLOCK_SPOILER_DEFAULT_TITLE,
            'content' => LANG_POSTBLOCK_SPOILER_DEFAULT_CONTENT
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'show_default' => '',
            'icon_before' => 'chevron-down',
            'icon_after' => '',
            'icon_position' => 'icon-after',
            'custom_class' => '',
            'animation' => true
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $title = $currentContent['title'] ?? '';
        $content = $currentContent['content'] ?? '';

        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_SPOILER_FORM_TITLE_LABEL; ?> *</label>
            <input type="text" 
                   name="content[title]" 
                   class="form-control" 
                   value="<?= html($title) ?>" 
                   placeholder="<?php echo LANG_POSTBLOCK_SPOILER_FORM_TITLE_PLACEHOLDER; ?>"
                   required>
            <div class="form-text"><?php echo LANG_POSTBLOCK_SPOILER_FORM_TITLE_HINT; ?></div>
        </div>

        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_SPOILER_FORM_CONTENT_LABEL; ?> *</label>
            <textarea name="content[content]" 
                     class="form-control" 
                     rows="6" 
                     placeholder="<?php echo LANG_POSTBLOCK_SPOILER_FORM_CONTENT_PLACEHOLDER; ?>"
                     required><?= html($content) ?></textarea>
            <div class="form-text"><?php echo LANG_POSTBLOCK_SPOILER_FORM_CONTENT_HINT; ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $showDefault = $currentSettings['show_default'] ?? '';
        $iconBefore = $currentSettings['icon_before'] ?? 'chevron-down';
        $iconAfter = $currentSettings['icon_after'] ?? '';
        $iconPosition = $currentSettings['icon_position'] ?? 'icon-after';
        $customClass = $currentSettings['custom_class'] ?? '';
        $animation = $currentSettings['animation'] ?? true;

        $spoilerIcons = [
            '' => LANG_POSTBLOCK_SPOILER_ICON_NONE,
            'chevron-down' => LANG_POSTBLOCK_SPOILER_ICON_CHEVRON_DOWN,
            'chevron-right' => LANG_POSTBLOCK_SPOILER_ICON_CHEVRON_RIGHT,
            'plus' => LANG_POSTBLOCK_SPOILER_ICON_PLUS,
            'dash' => LANG_POSTBLOCK_SPOILER_ICON_DASH,
            'caret-down' => LANG_POSTBLOCK_SPOILER_ICON_CARET_DOWN,
            'caret-right' => LANG_POSTBLOCK_SPOILER_ICON_CARET_RIGHT,
            'arrow-down' => LANG_POSTBLOCK_SPOILER_ICON_ARROW_DOWN,
            'arrow-right' => LANG_POSTBLOCK_SPOILER_ICON_ARROW_RIGHT,
            'eye' => LANG_POSTBLOCK_SPOILER_ICON_EYE,
            'info-circle' => LANG_POSTBLOCK_SPOILER_ICON_INFO,
            'question-circle' => LANG_POSTBLOCK_SPOILER_ICON_QUESTION,
            'chevron-double-down' => LANG_POSTBLOCK_SPOILER_ICON_DOUBLE_DOWN,
            'chevron-double-right' => LANG_POSTBLOCK_SPOILER_ICON_DOUBLE_RIGHT,
            'arrow-down-circle' => LANG_POSTBLOCK_SPOILER_ICON_ARROW_DOWN_CIRCLE,
            'arrow-right-circle' => LANG_POSTBLOCK_SPOILER_ICON_ARROW_RIGHT_CIRCLE,
            'caret-down-fill' => LANG_POSTBLOCK_SPOILER_ICON_CARET_DOWN_FILL,
            'caret-right-fill' => LANG_POSTBLOCK_SPOILER_ICON_CARET_RIGHT_FILL
        ];

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_SPOILER_SETTINGS_ICON_BEFORE; ?></label>
                    <select name="settings[icon_before]" class="form-select">
                        <?php foreach($spoilerIcons as $value => $name) { ?>
                            <option value="<?= $value ?>" <?= $iconBefore === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_SPOILER_SETTINGS_ICON_AFTER; ?></label>
                    <select name="settings[icon_after]" class="form-select">
                        <?php foreach($spoilerIcons as $value => $name) { ?>
                            <option value="<?= $value ?>" <?= $iconAfter === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_SPOILER_SETTINGS_ICON_POSITION; ?></label>
                    <select name="settings[icon_position]" class="form-select">
                        <option value="icon-before" <?= $iconPosition === 'icon-before' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_SPOILER_POSITION_BEFORE; ?></option>
                        <option value="icon-after" <?= $iconPosition === 'icon-after' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_SPOILER_POSITION_AFTER; ?></option>
                        <option value="icon-both" <?= $iconPosition === 'icon-both' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_SPOILER_POSITION_BOTH; ?></option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_SPOILER_SETTINGS_CUSTOM_CLASS; ?></label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= html($customClass) ?>" 
                           placeholder="my-spoiler">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[show_default]" 
                           id="show_default"
                           value="show" 
                           <?= $showDefault === 'show' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_default">
                        <?php echo LANG_POSTBLOCK_SPOILER_SETTINGS_OPEN_BY_DEFAULT; ?>
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[animation]" 
                           id="animation"
                           value="1" 
                           <?= $animation ? 'checked' : '' ?>>
                    <label class="form-check-label" for="animation">
                        <?php echo LANG_POSTBLOCK_SPOILER_SETTINGS_ANIMATION; ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <?= bloggy_icon('bs', 'info-circle', '16 16', null, 'me-2') ?>
            <?php echo LANG_POSTBLOCK_SPOILER_SETTINGS_BOOTSTRAP_NOTE; ?>
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
        
        $title = $content['title'] ?? '';
        $contentText = $content['content'] ?? '';

        if (empty(trim($title))) {
            return '<!-- ' . LANG_POSTBLOCK_SPOILER_EMPTY_TITLE_COMMENT . ' -->';
        }

        $showDefault = $settings['show_default'] ?? '';
        $iconBefore = $settings['icon_before'] ?? 'chevron-down';
        $iconAfter = $settings['icon_after'] ?? '';
        $iconPosition = $settings['icon_position'] ?? 'icon-after';
        $customClass = $settings['custom_class'] ?? '';
        $animation = $settings['animation'] ?? true;
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        $isOpen = $showDefault === 'show';
        $ariaExpanded = $isOpen ? 'true' : 'false';
        $ariaHidden = $isOpen ? 'false' : 'true';
        $noAnimationClass = !$animation ? 'no-animation' : '';
        
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

        $iconBeforeHtml = '';
        $iconAfterHtml = '';

        if ($iconPosition === 'icon-before' || $iconPosition === 'icon-both') {
            if (!empty($iconBefore)) {
                $iconBeforeHtml = bloggy_icon('bs', $iconBefore, '16 16', null, 'spoiler-icon me-2');
            }
        }
        
        if ($iconPosition === 'icon-after' || $iconPosition === 'icon-both') {
            if (!empty($iconAfter)) {
                $iconAfterHtml = bloggy_icon('bs', $iconAfter, '16 16', null, 'spoiler-icon ms-2');
            }
        }

        $result = $template;
        
        $result = str_replace('{custom_class}', trim($customClass . ' ' . $presetClass), $result);
        $result = str_replace('{icon_position}', html($iconPosition), $result);
        $result = str_replace('{icon_before}', $iconBeforeHtml, $result);
        $result = str_replace('{icon_after}', $iconAfterHtml, $result);
        $result = str_replace('{title}', html($title), $result);
        $result = str_replace('{show_default}', $isOpen ? 'show' : '', $result);
        $result = str_replace('{content}', $contentText, $result);
        $result = str_replace('{preset_id}', $presetId ? html($presetId) : '', $result);
        $result = str_replace('{preset_name}', $presetName ? html($presetName) : '', $result);
        $result = str_replace('{block_type}', $this->getSystemName(), $result);
        $result = str_replace('{block_name}', $this->getName(), $result);
        $result = str_replace('{aria_expanded}', $ariaExpanded, $result);
        $result = str_replace('{aria_hidden}', $ariaHidden, $result);
        $result = str_replace('{no_animation_class}', $noAnimationClass, $result);

        return $result;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{title}' => LANG_POSTBLOCK_SPOILER_SHORTCODE_TITLE,
            '{content}' => LANG_POSTBLOCK_SPOILER_SHORTCODE_CONTENT,
            '{block_id}' => LANG_POSTBLOCK_SPOILER_SHORTCODE_BLOCK_ID,
            '{show_default}' => LANG_POSTBLOCK_SPOILER_SHORTCODE_SHOW_DEFAULT,
            '{icon_before}' => LANG_POSTBLOCK_SPOILER_SHORTCODE_ICON_BEFORE,
            '{icon_after}' => LANG_POSTBLOCK_SPOILER_SHORTCODE_ICON_AFTER,
            '{icon_position}' => LANG_POSTBLOCK_SPOILER_SHORTCODE_ICON_POSITION,
            '{custom_class}' => LANG_POSTBLOCK_SPOILER_SHORTCODE_CUSTOM_CLASS
        ]);
    }

    public function getAdminCss(): array {
        return [
            'templates/default/admin/assets/css/blocks/spoiler.css'
        ];
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_SPOILER_VALIDATION_CUSTOM_CLASS;
        }

        $allowedIconPositions = ['icon-before', 'icon-after', 'icon-both'];
        if (!empty($settings['icon_position']) && !in_array($settings['icon_position'], $allowedIconPositions)) {
            $errors[] = LANG_POSTBLOCK_SPOILER_VALIDATION_ICON_POSITION;
        }

        return [empty($errors), $errors];
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<div[^>]*class="[^"]*spoiler-header[^"]*"[^>]*>.*?<button[^>]*>.*?<span[^>]*class="[^"]*spoiler-title[^"]*"[^>]*>(.*?)<\/span>.*?<\/button>.*?<\/div>.*?<div[^>]*class="[^"]*spoiler-content[^"]*"[^>]*>.*?<div[^>]*class="[^"]*spoiler-body[^"]*"[^>]*>(.*?)<\/div>.*?<\/div>/is', $html, $matches)) {
            $title = trim(strip_tags($matches[1]));
            $content = trim($matches[2]);
            
            if (!empty($title)) {
                return [
                    'title' => $title,
                    'content' => $content
                ];
            }
        }
        
        if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $html, $titleMatch)) {
            $title = trim(strip_tags($titleMatch[1]));
            $content = trim(strip_tags($html));
            $content = preg_replace('/^.*?<\/h[1-6]>/is', '', $content);
            
            if (!empty($title) && !empty($content)) {
                return [
                    'title' => $title,
                    'content' => $content
                ];
            }
        }
        
        return null;
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['title' => '', 'content' => ''];
        }
        
        if (!is_array($content)) {
            return ['title' => '', 'content' => ''];
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

    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            if (isset($_POST['content']['title'])) {
                $content['title'] = trim($_POST['content']['title']);
            }
            if (isset($_POST['content']['content'])) {
                $content['content'] = trim($_POST['content']['content']);
            }
        }
        
        if (!isset($content['title'])) {
            $content['title'] = LANG_POSTBLOCK_SPOILER_DEFAULT_TITLE;
        }
        if (!isset($content['content'])) {
            $content['content'] = LANG_POSTBLOCK_SPOILER_DEFAULT_CONTENT;
        }

        return $content;
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        if (isset($_POST['settings']['show_default']) && $_POST['settings']['show_default'] === 'show') {
            $settings['show_default'] = 'show';
        } else {
            $settings['show_default'] = '';
        }
        
        $settings['animation'] = isset($_POST['settings']['animation']) && ($_POST['settings']['animation'] == '1' || $_POST['settings']['animation'] == 'on');
        
        if (isset($_POST['settings']['icon_before'])) {
            $settings['icon_before'] = trim($_POST['settings']['icon_before']);
        }
        
        if (isset($_POST['settings']['icon_after'])) {
            $settings['icon_after'] = trim($_POST['settings']['icon_after']);
        }
        
        if (isset($_POST['settings']['icon_position'])) {
            $settings['icon_position'] = trim($_POST['settings']['icon_position']);
        }
        
        if (isset($_POST['settings']['custom_class'])) {
            $settings['custom_class'] = trim($_POST['settings']['custom_class']);
        }

        return $settings;
    }

    public function getFrontendJs(): array {
        return [
            '/templates/default/front/assets/postblocks/spoilerblock/spoiler.js',
        ];
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/spoilerblock/spoiler.css',
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $title = $content['title'] ?? LANG_POSTBLOCK_SPOILER_DEFAULT_TITLE;
        $contentText = $content['content'] ?? LANG_POSTBLOCK_SPOILER_DEFAULT_CONTENT;
        $showDefault = $settings['show_default'] ?? '';
        $iconBefore = $settings['icon_before'] ?? 'chevron-down';
        $iconAfter = $settings['icon_after'] ?? '';
        $iconPosition = $settings['icon_position'] ?? 'icon-after';
        $customClass = $settings['custom_class'] ?? '';
        $animation = $settings['animation'] ?? true;
        
        $isOpen = $showDefault === 'show';
        $contentLength = strlen($contentText);
        
        $previewIcon = 'chevron-down';
        if (!empty($iconBefore) && $iconBefore !== '' && $iconBefore !== 'null') {
            $previewIcon = $iconBefore;
        } elseif (!empty($iconAfter) && $iconAfter !== '' && $iconAfter !== 'null') {
            $previewIcon = $iconAfter;
        }
        
        $iconPositionText = match($iconPosition) {
            'icon-before' => LANG_POSTBLOCK_SPOILER_POSITION_BEFORE,
            'icon-after' => LANG_POSTBLOCK_SPOILER_POSITION_AFTER,
            'icon-both' => LANG_POSTBLOCK_SPOILER_POSITION_BOTH,
            default => LANG_POSTBLOCK_SPOILER_POSITION_AFTER
        };
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-SpoilerBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <?= bloggy_icon('bs', 'eye', '20 20') ?>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_TITLE; ?></strong>
                                <?php if ($isOpen) { ?>
                                    <span class="badge bg-success badge-sm"><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_OPEN; ?></span>
                                <?php } else { ?>
                                    <span class="badge bg-warning badge-sm"><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_CLOSED; ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?php echo sprintf(LANG_POSTBLOCK_SPOILER_PREVIEW_TITLE_STATS, strlen($title)); ?>
                                · <?php echo sprintf(LANG_POSTBLOCK_SPOILER_PREVIEW_CONTENT_STATS, $contentLength); ?>
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
                    <?php if (!empty(trim($title)) || !empty(trim($contentText))) { ?>
                        <div class="spoiler-preview-container">
                            <div class="spoiler-header-preview border rounded p-3 mb-2 bg-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <?php if (($iconPosition === 'icon-before' || $iconPosition === 'icon-both') && !empty($previewIcon)) { ?>
                                                <?= bloggy_icon('bs', $previewIcon, '16 16', null, 'me-2 text-primary') ?>
                                            <?php } ?>
                                            
                                            <span class="fw-semibold" style="color: #374151;">
                                                <?= html(mb_substr($title, 0, 40)) ?>
                                                <?php if (mb_strlen($title) > 40) { ?>...<?php } ?>
                                            </span>
                                            
                                            <?php if (($iconPosition === 'icon-after' || $iconPosition === 'icon-both') && !empty($previewIcon)) { ?>
                                                <?= bloggy_icon('bs', $previewIcon, '16 16', null, 'ms-2 text-primary') ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <span class="badge <?= $isOpen ? 'bg-success' : 'bg-warning' ?>">
                                            <?= $isOpen ? LANG_POSTBLOCK_SPOILER_PREVIEW_OPEN : LANG_POSTBLOCK_SPOILER_PREVIEW_CLOSED ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="spoiler-content-preview border rounded p-3 bg-white <?= $isOpen ? '' : 'bg-light' ?>">
                                <div class="small text-muted mb-2 d-flex justify-content-between">
                                    <span><?= bloggy_icon('bs', 'eye-slash', '12 12', null, 'me-1') ?> <?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_HIDDEN_CONTENT; ?></span>
                                    <span><?= $contentLength ?> <?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_CHARS; ?></span>
                                </div>
                                
                                <?php if (!empty(trim($contentText))) { ?>
                                    <div class="spoiler-text-preview small" style="color: #6b7280; line-height: 1.5;">
                                        <?= html(mb_substr(strip_tags($contentText), 0, 80)) ?>
                                        <?php if (mb_strlen(strip_tags($contentText)) > 80) { ?>...<?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="text-center py-2 text-muted">
                                        <?= bloggy_icon('bs', 'eye-slash', '24 24', null, 'mb-1') ?>
                                        <div class="small mt-1"><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_NO_CONTENT; ?></div>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <div class="spoiler-preview-info mt-3 small text-muted">
                                <div class="row">
                                    <div class="col-6">
                                        <div><?= bloggy_icon('bs', $isOpen ? 'unlock' : 'lock', '12 12', null, 'me-1') ?><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_INFO_DEFAULT; ?> <strong><?= $isOpen ? LANG_POSTBLOCK_SPOILER_PREVIEW_OPEN : LANG_POSTBLOCK_SPOILER_PREVIEW_CLOSED ?></strong></div>
                                        <div><?= bloggy_icon('bs', 'gear', '12 12', null, 'me-1') ?><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_INFO_ICONS; ?> <strong><?= html($iconPositionText) ?></strong></div>
                                    </div>
                                    <div class="col-6">
                                        <?php if ($customClass) { ?>
                                            <div><?= bloggy_icon('bs', 'tag', '12 12', null, 'me-1') ?><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_INFO_CLASS; ?> <strong><?= html($customClass) ?></strong></div>
                                        <?php } ?>
                                        <div><?= bloggy_icon('bs', 'play-circle', '12 12', null, 'me-1') ?><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_INFO_ANIMATION; ?> <strong><?= $animation ? LANG_POSTBLOCK_SPOILER_PREVIEW_YES : LANG_POSTBLOCK_SPOILER_PREVIEW_NO ?></strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <?= bloggy_icon('bs', 'eye', '48 48', '#6C6C6C', 'mb-3') ?>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_EMPTY_TEXT; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <?= bloggy_icon('bs', 'plus-circle', '14 14', null, 'me-1') ?>
                                <?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_ADD_BTN; ?>
                            </button>
                            <div class="mt-3 small text-muted">
                                <?= bloggy_icon('bs', 'info-circle', '14 14', null, 'me-1') ?>
                                <?php echo LANG_POSTBLOCK_SPOILER_PREVIEW_INFO_TEXT; ?>
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