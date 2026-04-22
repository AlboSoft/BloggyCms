<?php
class ListBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_LIST_NAME;
    }

    public function getSystemName(): string {
        return 'ListBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_LIST_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-list-ul';
    }

    public function getCategory(): string {
        return 'text';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <{list_type} class="post-block-list {custom_class}">
            {list_items}
            <li>{item_text}</li>
            {/list_items}
        </{list_type}>';
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        
        $listType = $currentContent['list_type'] ?? 'ul';
        $items = $currentContent['items'] ?? [['text' => '']];
        
        if (!is_array($items)) {
            $items = [['text' => (string)$items]];
        }
        
        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_LIST_FORM_TYPE_LABEL; ?></label>
            <select name="content[list_type]" class="form-select" id="list-type-select">
                <option value="ul" <?= $listType === 'ul' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_LIST_TYPE_UL; ?></option>
                <option value="ol" <?= $listType === 'ol' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_LIST_TYPE_OL; ?></option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label"><?php echo LANG_POSTBLOCK_LIST_FORM_ITEMS_LABEL; ?></label>
            <div id="list-items-container">
                <?php foreach($items as $index => $item) { ?>
                    <div class="list-item card mb-2">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-1 text-center">
                                    <span class="list-item-handle text-muted">
                                        <i class="bi bi-grip-vertical"></i>
                                    </span>
                                </div>
                                <div class="col-9">
                                    <input type="text" 
                                        name="content[items][]" 
                                        class="form-control" 
                                        value="<?= html(is_array($item) ? ($item['text'] ?? '') : (string)$item) ?>" 
                                        placeholder="<?php echo LANG_POSTBLOCK_LIST_FORM_ITEM_PLACEHOLDER; ?>">
                                </div>
                                <div class="col-2 text-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-list-item" <?= count($items) === 1 ? 'disabled' : '' ?>>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <button type="button" class="btn btn-secondary mt-2" id="add-list-item">
                <i class="bi bi-plus"></i> <?php echo LANG_POSTBLOCK_LIST_ADD_ITEM_BTN; ?>
            </button>
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
            <label class="form-label"><?php echo LANG_POSTBLOCK_LIST_SETTINGS_CUSTOM_CLASS; ?></label>
            <input type="text" 
                   name="settings[custom_class]" 
                   class="form-control" 
                   value="<?= html($customClass) ?>" 
                   placeholder="my-list">
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
        
        $listType = $content['list_type'] ?? 'ul';
        $items = $content['items'] ?? [];
        $customClass = $settings['custom_class'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        if (!is_array($items)) {
            $items = [];
        }

        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . $presetId;
            if ($presetName) {
                $presetClass .= ' preset-' . preg_replace('/[^a-z0-9_-]/i', '-', strtolower($presetName));
            }
        }
        
        $result = $template;
        $result = str_replace('{list_type}', $listType, $result);
        $result = str_replace('{custom_class}', $customClass . ' ' . $presetClass, $result);
        
        if (strpos($result, '{list_items}') !== false && strpos($result, '{/list_items}') !== false) {
            $itemsHtml = '';
            
            preg_match('/\{list_items\}(.*?)\{\/list_items\}/s', $result, $itemTemplateMatch);
            $itemTemplate = $itemTemplateMatch[1] ?? '<li>{item_text}</li>';
            
            foreach ($items as $index => $item) {
                $text = '';
                if (is_array($item)) {
                    $text = $item['text'] ?? '';
                } else {
                    $text = (string)$item;
                }
                
                if (!empty($text)) {
                    $itemHtml = str_replace('{item_text}', html($text), $itemTemplate);
                    $itemsHtml .= $itemHtml;
                }
            }
            
            $result = preg_replace('/\{list_items\}.*?\{\/list_items\}/s', $itemsHtml, $result);
        }
        
        $result = str_replace('{preset_id}', $presetId ? html($presetId) : '', $result);
        $result = str_replace('{preset_name}', $presetName ? html($presetName) : '', $result);
        $result = str_replace('{block_type}', $this->getSystemName(), $result);
        $result = str_replace('{block_name}', $this->getName(), $result);

        return $result;
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<(ul|ol)[^>]*>(.*?)<\/\1>/s', $html, $listMatches)) {
            $listType = $listMatches[1];
            $listContent = $listMatches[2];
            
            preg_match_all('/<li[^>]*>(.*?)<\/li>/s', $listContent, $itemMatches);
            
            $items = [];
            foreach ($itemMatches[1] as $itemContent) {
                $text = trim(strip_tags($itemContent));
                if (!empty($text)) {
                    $items[] = ['text' => $text];
                }
            }
            
            if (!empty($items)) {
                return [
                    'list_type' => $listType,
                    'items' => $items
                ];
            }
        }
        
        return null;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{list_type}' => LANG_POSTBLOCK_LIST_SHORTCODE_LIST_TYPE,
            '{custom_class}' => LANG_POSTBLOCK_LIST_SHORTCODE_CUSTOM_CLASS,
            '{list_items}...{/list_items}' => LANG_POSTBLOCK_LIST_SHORTCODE_LIST_ITEMS,
            '{item_text}' => LANG_POSTBLOCK_LIST_SHORTCODE_ITEM_TEXT
        ]);
    }

    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/blocks/list.js'
        ];
    }

    public function getAdminCss(): array {
        return [
            'templates/default/admin/assets/css/blocks/list.css'
        ];
    }

    public function getInlineAdminJs(): string {
        return "";
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_LIST_VALIDATION_CUSTOM_CLASS;
        }

        return [empty($errors), $errors];
    }

    public function validateAndNormalizeContent($content): array {
        
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            $result = is_array($decoded) ? $decoded : ['text' => $content];
            return $result;
        }
        
        if (!is_array($content)) {
            $result = ['text' => (string)$content];
            return $result;
        }
        
        if (isset($content['items'])) {
            if (!is_array($content['items'])) {
                $content['items'] = [['text' => (string)$content['items']]];
            } else {
                $normalizedItems = [];
                foreach ($content['items'] as $index => $item) {
                    if (is_array($item) && isset($item['text'])) {
                        $normalizedItems[] = $item;
                    } elseif (is_array($item)) {
                        $text = '';
                        foreach ($item as $key => $value) {
                            if (is_string($value) && !empty(trim($value))) {
                                $text = $value;
                                break;
                            }
                        }
                        $normalizedItems[] = ['text' => $text ?: sprintf(LANG_POSTBLOCK_LIST_DEFAULT_ITEM, $index + 1)];
                    } else {
                        $normalizedItems[] = ['text' => (string)$item];
                    }
                }
                $content['items'] = $normalizedItems;
            }
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
            if (isset($_POST['content']['list_type'])) {
                $content['list_type'] = $_POST['content']['list_type'];
            }
            
            if (isset($_POST['content']['items']) && is_array($_POST['content']['items'])) {
                $items = [];
                foreach ($_POST['content']['items'] as $itemText) {
                    if (!empty(trim($itemText))) {
                        $items[] = ['text' => trim($itemText)];
                    }
                }
                if (empty($items)) {
                    $items[] = ['text' => ''];
                }
                $content['items'] = $items;
            }
        }
        
        if (!isset($content['list_type'])) {
            $content['list_type'] = 'ul';
        }
        
        if (!isset($content['items']) || !is_array($content['items'])) {
            $content['items'] = [['text' => '']];
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

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $listType = $content['list_type'] ?? 'ul';
        $items = $content['items'] ?? [];
        $customClass = $settings['custom_class'] ?? '';
        
        $validItems = array_filter($items, function($item) {
            if (is_array($item)) {
                return !empty(trim($item['text'] ?? ''));
            }
            return !empty(trim((string)$item));
        });
        
        $itemCount = count($validItems);
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-ListBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-list-ul"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_LIST_PREVIEW_TITLE; ?></strong>
                                <?php if ($listType === 'ol') { ?>
                                    <span class="badge bg-primary badge-sm"><?php echo LANG_POSTBLOCK_LIST_PREVIEW_ORDERED; ?></span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary badge-sm"><?php echo LANG_POSTBLOCK_LIST_PREVIEW_UNORDERED; ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?php echo sprintf(LANG_POSTBLOCK_LIST_PREVIEW_ITEMS_COUNT, $itemCount); ?>
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
                    <?php if ($itemCount > 0) { ?>
                        <div class="list-preview-container">
                            <div class="list-preview">
                                <<?= $listType ?> class="list-preview-items mb-0 ps-0" style="list-style: <?= $listType === 'ol' ? 'decimal' : 'disc' ?> inside;">
                                    <?php 
                                    $previewItems = array_slice($validItems, 0, 5);
                                    foreach ($previewItems as $index => $item) { 
                                        $text = '';
                                        if (is_array($item)) {
                                            $text = $item['text'] ?? '';
                                        } else {
                                            $text = (string)$item;
                                        }
                                    ?>
                                        <li class="mb-1" style="font-size: 14px; line-height: 1.4;">
                                            <?= html(mb_substr($text, 0, 60)) ?>
                                            <?php if (mb_strlen($text) > 60) { ?>...<?php } ?>
                                        </li>
                                    <?php } ?>
                                    
                                    <?php if ($itemCount > 5) { ?>
                                        <li class="text-muted small">
                                            <?php echo sprintf(LANG_POSTBLOCK_LIST_PREVIEW_MORE_ITEMS, $itemCount - 5); ?>
                                        </li>
                                    <?php } ?>
                                </<?= $listType ?>>
                            </div>
                            
                            <div class="list-preview-info mt-3 small text-muted">
                                <div class="row">
                                    <div class="col-6">
                                        <div>
                                            <i class="bi bi-<?= $listType === 'ol' ? '123' : 'dot' ?> me-1"></i>
                                            <?php echo LANG_POSTBLOCK_LIST_PREVIEW_INFO_TYPE; ?> <strong><?= $listType === 'ol' ? LANG_POSTBLOCK_LIST_PREVIEW_ORDERED : LANG_POSTBLOCK_LIST_PREVIEW_UNORDERED ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <?php if ($customClass) { ?>
                                            <div><i class="bi bi-tag me-1"></i><?php echo LANG_POSTBLOCK_LIST_PREVIEW_INFO_CLASS; ?> <strong><?= html($customClass) ?></strong></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-list-ul"></i>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_LIST_PREVIEW_EMPTY_TEXT; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <?php echo bloggy_icon('bs', 'plus-circle', '16', '#000', 'me-1'); ?> <?php echo LANG_POSTBLOCK_LIST_PREVIEW_ADD_BTN; ?>
                            </button>
                            <div class="mt-3 small text-muted">
                                <i class="bi bi-info-circle"></i>
                                <?php echo LANG_POSTBLOCK_LIST_PREVIEW_INFO_TEXT; ?>
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