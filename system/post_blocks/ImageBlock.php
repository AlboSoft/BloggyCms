<?php
class ImageBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_IMAGE_NAME;
    }

    public function getSystemName(): string {
        return 'ImageBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_IMAGE_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-image';
    }

    public function getCategory(): string {
        return 'media';
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $url = $content['image_url'] ?? '';
        $alt = $content['alt_text'] ?? '';
        $caption = $content['caption'] ?? '';
        $alignment = $settings['alignment'] ?? 'center';
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-ImageBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-image"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_IMAGE_PREVIEW_TITLE; ?></strong>
                            </div>
                            <div class="preview-stats">
                                <?php if (!empty($url)) { ?>
                                    <?php echo LANG_POSTBLOCK_IMAGE_PREVIEW_STATS_UPLOADED; ?>
                                    <?php if (!empty($alt)) { ?>
                                        · <?php echo LANG_POSTBLOCK_IMAGE_PREVIEW_STATS_HAS_ALT; ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <?php echo LANG_POSTBLOCK_IMAGE_PREVIEW_STATS_NOT_UPLOADED; ?>
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
                    <?php if (!empty($url)) { ?>
                        <div class="image-content-container text-<?= html($alignment) ?>">
                            <div class="image-wrapper position-relative d-inline-block">
                                <img src="<?= html($url) ?>" 
                                     alt="<?= html($alt) ?>"
                                     class="img-fluid rounded shadow-sm"
                                     style="max-width: 100%; max-height: 300px;">
                            </div>
                            
                            <?php if (!empty($alt)) { ?>
                                <div class="image-alt mt-2">
                                    <div class="small fw-semibold"><?php echo LANG_POSTBLOCK_IMAGE_ALT_LABEL; ?></div>
                                    <div class="small text-muted"><?= html($alt) ?></div>
                                </div>
                            <?php } ?>
                            
                            <?php if (!empty($caption)) { ?>
                                <div class="image-caption mt-2">
                                    <div class="small fw-semibold"><?php echo LANG_POSTBLOCK_IMAGE_CAPTION_LABEL; ?></div>
                                    <div class="small text-muted"><?= html($caption) ?></div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-image"></i>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_IMAGE_EMPTY_TEXT; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> <?php echo LANG_POSTBLOCK_IMAGE_ADD_BTN; ?>
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
        <div class="post-block-image {alignment} {custom_class}">
            <figure class="image-figure">
                <img src="{image_url}" 
                     alt="{alt_text}" 
                     class="img-fluid {image_class}"
                     {width_attr}
                     {height_attr}
                     {loading_attr}>
                {caption_html}
            </figure>
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'image_url' => '',
            'alt_text' => '',
            'caption' => ''
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'alignment' => 'center',
            'width' => '',
            'height' => '',
            'image_class' => '',
            'custom_class' => '',
            'lazy_loading' => true
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $imageUrl = $currentContent['image_url'] ?? '';
        $altText = $currentContent['alt_text'] ?? '';
        $caption = $currentContent['caption'] ?? '';

        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_FORM_UPLOAD_LABEL; ?> *</label>
            <input type="file" 
                   name="image_file" 
                   class="form-control image-file-input" 
                   accept="image/*"
                   <?= empty($imageUrl) ? 'required' : '' ?>>
            <div class="form-text">
                <?php echo LANG_POSTBLOCK_IMAGE_FORM_UPLOAD_HINT; ?>
            </div>
        </div>

        <input type="hidden" 
               name="content[image_url]" 
               class="image-url-input" 
               value="<?= html($imageUrl) ?>">
        <?php if ($imageUrl) { ?>
            <div class="mb-4">
                <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_FORM_CURRENT_LABEL; ?></label>
                <div class="current-image-preview border rounded p-3 text-center bg-light">
                    <img src="<?= html($imageUrl) ?>" 
                        alt="<?php echo LANG_POSTBLOCK_IMAGE_FORM_CURRENT_ALT; ?>" 
                        class="img-thumbnail"
                        style="max-height: 200px; max-width: 100%;">
                    <div class="mt-2">
                        <small class="text-muted"><?= html($imageUrl) ?></small>
                    </div>
                    <div class="mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeImage">
                            <label class="form-check-label" for="removeImage">
                                <?php echo LANG_POSTBLOCK_IMAGE_FORM_REMOVE_LABEL; ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_FORM_ALT_LABEL; ?> *</label>
            <input type="text" 
                   name="content[alt_text]" 
                   class="form-control" 
                   value="<?= html($altText) ?>" 
                   placeholder="<?php echo LANG_POSTBLOCK_IMAGE_FORM_ALT_PLACEHOLDER; ?>"
                   required>
            <div class="form-text">
                <?php echo LANG_POSTBLOCK_IMAGE_FORM_ALT_HINT; ?>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_FORM_CAPTION_LABEL; ?></label>
            <textarea name="content[caption]" 
                      class="form-control" 
                      rows="2"
                      placeholder="<?php echo LANG_POSTBLOCK_IMAGE_FORM_CAPTION_PLACEHOLDER; ?>"><?= html($caption) ?></textarea>
        </div>
        <div class="new-image-preview mb-4" style="display: none;">
            <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_FORM_PREVIEW_LABEL; ?></label>
            <div class="border rounded p-3 text-center">
                <img src="" alt="<?php echo LANG_POSTBLOCK_IMAGE_FORM_PREVIEW_ALT; ?>" class="img-thumbnail preview-image" style="max-height: 200px; max-width: 100%;">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $alignment = $currentSettings['alignment'] ?? 'center';
        $width = $currentSettings['width'] ?? '';
        $height = $currentSettings['height'] ?? '';
        $imageClass = $currentSettings['image_class'] ?? '';
        $customClass = $currentSettings['custom_class'] ?? '';
        $lazyLoading = $currentSettings['lazy_loading'] ?? true;

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_SETTINGS_ALIGNMENT; ?></label>
                    <select name="settings[alignment]" class="form-select">
                        <option value="left" <?= $alignment === 'left' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_IMAGE_ALIGN_LEFT; ?></option>
                        <option value="center" <?= $alignment === 'center' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_IMAGE_ALIGN_CENTER; ?></option>
                        <option value="right" <?= $alignment === 'right' ? 'selected' : '' ?>><?php echo LANG_POSTBLOCK_IMAGE_ALIGN_RIGHT; ?></option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_SETTINGS_CUSTOM_CLASS; ?></label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= html($customClass) ?>" 
                           placeholder="my-image-block">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_SETTINGS_WIDTH; ?></label>
                    <input type="text" 
                           name="settings[width]" 
                           class="form-control" 
                           value="<?= html($width) ?>" 
                           placeholder="800px или 50%">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_SETTINGS_HEIGHT; ?></label>
                    <input type="text" 
                           name="settings[height]" 
                           class="form-control" 
                           value="<?= html($height) ?>" 
                           placeholder="600px">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_IMAGE_SETTINGS_IMAGE_CLASS; ?></label>
                    <input type="text" 
                           name="settings[image_class]" 
                           class="form-control" 
                           value="<?= html($imageClass) ?>" 
                           placeholder="rounded shadow">
                </div>
            </div>
        </div>

        <div class="form-check form-switch mb-4">
            <input class="form-check-input" 
                   type="checkbox" 
                   name="settings[lazy_loading]" 
                   id="lazy_loading"
                   value="1" 
                   <?= $lazyLoading ? 'checked' : '' ?>>
            <label class="form-check-label" for="lazy_loading">
                <?php echo LANG_POSTBLOCK_IMAGE_SETTINGS_LAZY_LOADING; ?>
            </label>
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
        
        $imageUrl = $content['image_url'] ?? '';
        $altText = $content['alt_text'] ?? '';
        $caption = $content['caption'] ?? '';
        $alignment = $settings['alignment'] ?? 'center';
        $width = $settings['width'] ?? '';
        $height = $settings['height'] ?? '';
        $imageClass = $settings['image_class'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $lazyLoading = $settings['lazy_loading'] ?? true;
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        if (empty($imageUrl)) {
            return '<!-- ImageBlock: <?php echo LANG_POSTBLOCK_IMAGE_NO_IMAGE_COMMENT; ?> -->';
        }

        if ($imageUrl[0] !== '/') {
            $imageUrl = '/' . $imageUrl;
        }

        $widthAttr = '';
        $heightAttr = '';
        if (!empty($width)) {
            $widthAttr = 'width="' . htmlspecialchars($width) . '"';
        }
        if (!empty($height)) {
            $heightAttr = 'height="' . htmlspecialchars($height) . '"';
        }
        
        $loadingAttr = $lazyLoading ? 'loading="lazy"' : '';
        
        $captionHtml = '';
        if (!empty($caption)) {
            $captionHtml = '<figcaption class="image-caption">' . nl2br(htmlspecialchars($caption)) . '</figcaption>';
        }
        
        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . (int)$presetId;
            if ($presetName) {
                $presetClass .= ' preset-' . preg_replace('/[^a-z0-9_-]/i', '-', strtolower($presetName));
            }
        }
        
        $finalCustomClass = trim($customClass . ' ' . $presetClass);
        
        $result = $template;
        
        $replacements = [
            '{image_url}' => htmlspecialchars($imageUrl),
            '{alt_text}' => htmlspecialchars($altText),
            '{caption}' => htmlspecialchars($caption),
            '{caption_html}' => $captionHtml,
            '{alignment}' => $alignment,
            '{image_class}' => $imageClass,
            '{custom_class}' => $finalCustomClass,
            '{width_attr}' => $widthAttr,
            '{height_attr}' => $heightAttr,
            '{loading_attr}' => $loadingAttr,
            '{preset_id}' => $presetId ? htmlspecialchars($presetId) : '',
            '{preset_name}' => $presetName ? htmlspecialchars($presetName) : '',
            '{block_type}' => $this->getSystemName(),
            '{block_name}' => $this->getName()
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $result = str_replace($placeholder, $value, $result);
        }
        
        $result = preg_replace('/\s+(width|height|loading)=""/', '', $result);
        
        return $result;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{image_url}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_URL,
            '{alt_text}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_ALT,
            '{caption}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_CAPTION,
            '{caption_html}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_CAPTION_HTML,
            '{alignment}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_ALIGNMENT,
            '{image_class}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_IMAGE_CLASS,
            '{custom_class}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_CUSTOM_CLASS,
            '{width_attr}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_WIDTH_ATTR,
            '{height_attr}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_HEIGHT_ATTR,
            '{loading_attr}' => LANG_POSTBLOCK_IMAGE_SHORTCODE_LOADING_ATTR
        ]);
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        $settings['lazy_loading'] = isset($_POST['settings']['lazy_loading']) && ($_POST['settings']['lazy_loading'] == '1' || $_POST['settings']['lazy_loading'] == 'on');
        
        if (isset($_POST['settings']['alignment'])) {
            $settings['alignment'] = trim($_POST['settings']['alignment']);
        }
        
        if (isset($_POST['settings']['width'])) {
            $settings['width'] = trim($_POST['settings']['width']);
        }
        
        if (isset($_POST['settings']['height'])) {
            $settings['height'] = trim($_POST['settings']['height']);
        }
        
        if (isset($_POST['settings']['image_class'])) {
            $settings['image_class'] = trim($_POST['settings']['image_class']);
        }
        
        if (isset($_POST['settings']['custom_class'])) {
            $settings['custom_class'] = trim($_POST['settings']['custom_class']);
        }
        
        return $settings;
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
        
        $settings['lazy_loading'] = isset($settings['lazy_loading']) 
            ? filter_var($settings['lazy_loading'], FILTER_VALIDATE_BOOLEAN) 
            : ($defaults['lazy_loading'] ?? true);
        
        if (!isset($settings['alignment']) || !in_array($settings['alignment'], ['left', 'center', 'right'])) {
            $settings['alignment'] = $defaults['alignment'] ?? 'center';
        }
        
        if (!isset($settings['width'])) {
            $settings['width'] = $defaults['width'] ?? '';
        }
        
        if (!isset($settings['height'])) {
            $settings['height'] = $defaults['height'] ?? '';
        }
        
        if (!isset($settings['image_class'])) {
            $settings['image_class'] = $defaults['image_class'] ?? '';
        }
        
        if (!isset($settings['custom_class'])) {
            $settings['custom_class'] = $defaults['custom_class'] ?? '';
        }
        
        return $settings;
    }

    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $uploadResult = $this->handleImageUpload($_FILES['image_file']);
                if ($uploadResult['success']) {
                    $content['image_url'] = $uploadResult['file_path'];
                } else {
                    throw new Exception($uploadResult['error'] ?? LANG_POSTBLOCK_IMAGE_UPLOAD_ERROR_DEFAULT);
                }
            } catch (Exception $e) {
                throw $e;
            }
        } elseif (isset($_POST['content']['image_url'])) {
            $existingUrl = $_POST['content']['image_url'];
            if (!empty($existingUrl) && $existingUrl[0] !== '/') {
                $content['image_url'] = '/' . $existingUrl;
            } else {
                $content['image_url'] = $existingUrl;
            }
        }
        
        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            if (!empty($content['image_url'])) {
                $filePath = ltrim($content['image_url'], '/');
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $content['image_url'] = '';
        }

        if (isset($_POST['content']['alt_text'])) {
            $content['alt_text'] = $_POST['content']['alt_text'];
        }
        
        if (isset($_POST['content']['caption'])) {
            $content['caption'] = $_POST['content']['caption'];
        }

        if (empty($content['alt_text'])) {
            $content['alt_text'] = LANG_POSTBLOCK_IMAGE_DEFAULT_ALT;
        }

        return $content;
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['image_url' => '', 'alt_text' => '', 'caption' => ''];
        }
        
        if (!is_array($content)) {
            return ['image_url' => '', 'alt_text' => '', 'caption' => ''];
        }
        
        if (!isset($content['image_url'])) {
            $content['image_url'] = '';
        }
        if (!isset($content['alt_text'])) {
            $content['alt_text'] = '';
        }
        if (!isset($content['caption'])) {
            $content['caption'] = '';
        }
        
        return $content;
    }

    public function handleImageUpload($file) {
        try {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'error' => sprintf(LANG_POSTBLOCK_IMAGE_UPLOAD_ERROR, $this->getUploadError($file['error']))];
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'error' => LANG_POSTBLOCK_IMAGE_VALIDATION_TYPE];
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                return ['success' => false, 'error' => LANG_POSTBLOCK_IMAGE_VALIDATION_SIZE];
            }

            $uploadDir = 'uploads/images/post_blocks/';
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    return ['success' => false, 'error' => LANG_POSTBLOCK_IMAGE_UPLOAD_DIR_ERROR];
                }
            }

            if (!is_writable($uploadDir)) {
                return ['success' => false, 'error' => LANG_POSTBLOCK_IMAGE_UPLOAD_WRITABLE_ERROR];
            }

            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName = 'post_block_' . uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ['success' => false, 'error' => LANG_POSTBLOCK_IMAGE_UPLOAD_SAVE_ERROR];
            }

            if (!file_exists($filePath)) {
                return ['success' => false, 'error' => LANG_POSTBLOCK_IMAGE_UPLOAD_NOT_CREATED_ERROR];
            }

            return [
                'success' => true, 
                'file_path' => '/' . $filePath,
                'file_name' => $fileName,
                'file_size' => $file['size'],
                'file_type' => $fileType
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => sprintf(LANG_POSTBLOCK_IMAGE_UPLOAD_EXCEPTION, $e->getMessage())];
        }
    }

    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => LANG_POSTBLOCK_IMAGE_ERROR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => LANG_POSTBLOCK_IMAGE_ERROR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL => LANG_POSTBLOCK_IMAGE_ERROR_PARTIAL,
            UPLOAD_ERR_NO_FILE => LANG_POSTBLOCK_IMAGE_ERROR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR => LANG_POSTBLOCK_IMAGE_ERROR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE => LANG_POSTBLOCK_IMAGE_ERROR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION => LANG_POSTBLOCK_IMAGE_ERROR_EXTENSION
        ];
        
        return $errors[$errorCode] ?? LANG_POSTBLOCK_IMAGE_ERROR_UNKNOWN;
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_IMAGE_VALIDATION_CUSTOM_CLASS;
        }

        if (!empty($settings['image_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['image_class'])) {
            $errors[] = LANG_POSTBLOCK_IMAGE_VALIDATION_IMAGE_CLASS;
        }

        return [empty($errors), $errors];
    }
}