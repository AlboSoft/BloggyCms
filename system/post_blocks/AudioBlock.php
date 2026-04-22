<?php

/**
* Аудио-блок для вставки аудиофайлов в пост или страницу
* @package PostBlocks
*/
class AudioBlock extends BasePostBlock {

    public function getName(): string {
        return LANG_POSTBLOCK_AUDIO_NAME;
    }


    public function getSystemName(): string {
        return 'AudioBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_AUDIO_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-file-music';
    }

    public function getCategory(): string {
        return 'media';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="post-block-audio {custom_class}">
            <div class="custom-audio-player" data-audio-player>
                <audio class="audio-source" preload="metadata">
                    <source src="{src_mp3}" type="audio/mpeg">
                </audio>
                <div class="audio-player-container">
                    <button type="button" class="play-pause-btn" data-play-pause>
                        <svg class="play-icon" viewBox="0 0 24 24" width="24" height="24">
                            <path fill="currentColor" d="M8 5v14l11-7z"/>
                        </svg>
                        <svg class="pause-icon" viewBox="0 0 24 24" width="24" height="24" style="display: none;">
                            <path fill="currentColor" d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </button>
                    <div class="audio-info">
                        <div class="audio-progress-container" data-progress-container>
                            <div class="audio-progress" data-progress></div>
                            <div class="audio-progress-buffered" data-buffered></div>
                            <input type="range" class="audio-progress-slider" data-progress-slider min="0" max="100" step="0.1" value="0">
                        </div>
                        <div class="audio-time">
                            <span class="current-time" data-current-time>0:00</span>
                            <span class="separator">/</span>
                            <span class="duration" data-duration>0:00</span>
                        </div>
                    </div>
                    <div class="audio-volume-container" data-volume-container>
                        <button type="button" class="volume-btn" data-volume-btn>
                            <svg class="volume-high-icon" viewBox="0 0 24 24" width="20" height="20">
                                <path fill="currentColor" d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
                            </svg>
                            <svg class="volume-low-icon" viewBox="0 0 24 24" width="20" height="20" style="display: none;">
                                <path fill="currentColor" d="M5 9v6h4l5 5V4L9 9H5z"/>
                            </svg>
                            <svg class="volume-muted-icon" viewBox="0 0 24 24" width="20" height="20" style="display: none;">
                                <path fill="currentColor" d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
                            </svg>
                        </button>
                        <div class="volume-slider-container">
                            <input type="range" class="volume-slider" data-volume-slider min="0" max="100" value="100">
                        </div>
                    </div>
                </div>
            </div>
            {caption_html}
        </div>';
    }

    public function getShortcodes(): array
    {
        return array_merge(parent::getShortcodes(), [
            '{src_mp3}' => LANG_POSTBLOCK_AUDIO_SHORTCODE_SRC,
            '{caption}' => LANG_POSTBLOCK_AUDIO_SHORTCODE_CAPTION,
            '{fallback_text}' => LANG_POSTBLOCK_AUDIO_SHORTCODE_FALLBACK,
            '{caption_html}' => LANG_POSTBLOCK_AUDIO_SHORTCODE_CAPTION_HTML,
        ]);
    }

    public function getDefaultContent(): array {
        return [
            'src_mp3' => '',
            'caption' => '',
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'custom_class' => '',
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $srcMp3 = $content['src_mp3'] ?? '';
        $caption = $content['caption'] ?? '';
        $customClass = html($settings['custom_class'] ?? '');
        
        $hasAudio = !empty($srcMp3);
        
        $mp3Url = '';
        if ($hasAudio) {
            if (filter_var($srcMp3, FILTER_VALIDATE_URL)) {
                $mp3Url = $srcMp3;
            } else {
                $mp3Url = BASE_URL . $srcMp3;
            }
        }
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-AudioBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-file-music"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_AUDIO_PREVIEW_TITLE; ?></strong>
                                <?php if ($hasAudio) { ?>
                                    <span class="badge bg-success badge-sm"><?php echo LANG_POSTBLOCK_AUDIO_PREVIEW_UPLOADED; ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?php if ($hasAudio) { ?>
                                    <?php
                                    $fileName = basename($srcMp3);
                                    if (filter_var($srcMp3, FILTER_VALIDATE_URL)) {
                                        $fileName = LANG_POSTBLOCK_AUDIO_PREVIEW_EXTERNAL;
                                    }
                                    ?>
                                    <?= html($fileName) ?>
                                <?php } else { ?>
                                    <?php echo LANG_POSTBLOCK_AUDIO_PREVIEW_NOT_UPLOADED; ?>
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
                    <?php if ($hasAudio) { ?>
                        <div class="audio-preview-container">
                            <div class="audio-player-preview border rounded p-3 bg-light">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="text-center">
                                        <i class="bi bi-file-music display-4 text-primary mb-2"></i>
                                        <div class="audio-player-mockup">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-secondary btn-sm" disabled>
                                                    <i class="bi bi-play-fill"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm" disabled>
                                                    <i class="bi bi-pause-fill"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2">
                                                <div class="progress" style="height: 5px; width: 200px;">
                                                    <div class="progress-bar" style="width: 30%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($caption)) { ?>
                                <div class="audio-caption mt-2 text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-music-note me-1"></i>
                                        <?= html(mb_substr($caption, 0, 60)) ?>
                                        <?php if (mb_strlen($caption) > 60) { ?>...<?php } ?>
                                    </small>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-file-music"></i>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_AUDIO_PREVIEW_EMPTY_TITLE; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> <?php echo LANG_POSTBLOCK_AUDIO_PREVIEW_ADD_BTN; ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $srcMp3 = $content['src_mp3'] ?? '';
        $caption = $content['caption'] ?? '';
        $customClass = html($settings['custom_class'] ?? '');
        
        if (empty($srcMp3)) {
            return '
            <div class="post-block-audio-preview text-center p-3 bg-light border rounded">
                <i class="bi bi-file-music fs-1 text-muted"></i>
                <p class="text-muted mt-2 mb-0">' . LANG_POSTBLOCK_AUDIO_EDITOR_EMPTY . '</p>
                <small class="text-secondary">' . LANG_POSTBLOCK_AUDIO_EDITOR_HINT . '</small>
            </div>';
        }
        
        $mp3Url = filter_var($srcMp3, FILTER_VALIDATE_URL) ? $srcMp3 : BASE_URL . $srcMp3;
        
        ob_start();
        ?>
        <div class="post-block-audio-preview <?= $customClass ?>">
            <?php if (!empty($caption)) { ?>
                <figure>
            <?php } ?>
            
            <audio class="audio-player" controls style="max-width: 100%;">
                <source src="<?= html($mp3Url) ?>" type="audio/mpeg">
                <p class="text-muted"><?php echo LANG_POSTBLOCK_AUDIO_EDITOR_FALLBACK; ?></p>
            </audio>
            
            <?php if (!empty($caption)) { ?>
                    <figcaption class="audio-caption mt-2 small text-muted">
                        <i class="bi bi-music-note me-1"></i>
                        <?= html($caption) ?>
                    </figcaption>
                </figure>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getContentForm($currentContent = []): string {
        $content = $this->validateAndNormalizeContent($currentContent);

        $srcMp3 = $content['src_mp3'] ?? '';
        $caption = html($content['caption'] ?? '');
        
        $hasFile = !empty($srcMp3);
        $isExternal = $hasFile && filter_var($srcMp3, FILTER_VALIDATE_URL);
        $fileName = '';
        if ($hasFile && !$isExternal) {
            $fileName = basename($srcMp3);
        }

        ob_start();
        ?>
        <div class="audio-block-form">
            <div class="mb-3">
                <label class="form-label"><?php echo LANG_POSTBLOCK_AUDIO_FORM_FILE_LABEL; ?> *</label>
                
                <?php if ($hasFile && !$isExternal) { ?>
                    <div class="current-file mb-2 p-2 bg-light border rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-music me-2 text-primary"></i>
                            <span class="flex-grow-1"><?= html($fileName) ?></span>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_audio" value="1" id="removeAudio">
                                <label class="form-check-label text-danger" for="removeAudio">
                                    <?php echo LANG_POSTBLOCK_AUDIO_FORM_DELETE; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                
                <input type="file" 
                       name="audio_file" 
                       class="form-control" 
                       accept=".mp3,audio/mpeg"
                       <?= (!$hasFile || $isExternal) ? 'required' : '' ?>>
                <div class="form-text">
                    <?php echo LANG_POSTBLOCK_AUDIO_FORM_FILE_HINT; ?>
                </div>
                
                <input type="hidden" name="content[src_mp3]" value="<?= html($srcMp3) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label"><?php echo LANG_POSTBLOCK_AUDIO_FORM_EXTERNAL_LABEL; ?></label>
                <input type="url" 
                       name="external_url" 
                       class="form-control" 
                       value="<?= $isExternal ? html($srcMp3) : '' ?>" 
                       placeholder="https://example.ru/audio.mp3"
                       <?= ($hasFile && !$isExternal) ? 'disabled' : '' ?>>
                <div class="form-text">
                    <?php echo LANG_POSTBLOCK_AUDIO_FORM_EXTERNAL_HINT; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><?php echo LANG_POSTBLOCK_AUDIO_FORM_CAPTION_LABEL; ?></label>
                <input type="text" 
                       name="content[caption]" 
                       class="form-control" 
                       value="<?= $caption ?>" 
                       placeholder="<?php echo LANG_POSTBLOCK_AUDIO_FORM_CAPTION_PLACEHOLDER; ?>">
            </div>
        </div>

        <script>
            (function() {
                const form = document.currentScript.closest('form');
                if (!form) return;
                
                const fileInput = form.querySelector('input[name="audio_file"]');
                const urlInput = form.querySelector('input[name="external_url"]');
                const hiddenInput = form.querySelector('input[name="content[src_mp3]"]');
                const removeCheckbox = form.querySelector('input[name="remove_audio"]');
                
                if (fileInput) {
                    fileInput.addEventListener('change', function() {
                        if (this.value) {
                            if (urlInput) {
                                urlInput.value = '';
                                urlInput.disabled = true;
                            }
                            if (removeCheckbox) {
                                removeCheckbox.checked = false;
                            }
                            this.required = true;
                        } else {
                            if (urlInput) {
                                urlInput.disabled = false;
                            }
                        }
                    });
                }
                
                if (urlInput) {
                    urlInput.addEventListener('input', function() {
                        if (this.value) {
                            if (fileInput) {
                                fileInput.required = false;
                                fileInput.value = '';
                            }
                        } else {
                            if (fileInput && !hiddenInput.value) {
                                fileInput.required = true;
                            }
                        }
                    });
                }

                form.addEventListener('submit', function() {
                    if (urlInput && urlInput.value) {
                        hiddenInput.value = urlInput.value;
                    }
                });
            })();
        </script>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $settings = $this->validateAndNormalizeSettings($currentSettings);
        $customClass = html($settings['custom_class'] ?? '');

        ob_start();
        ?>
        <div class="mb-3">
            <label class="form-label"><?php echo LANG_POSTBLOCK_AUDIO_SETTINGS_CSS_CLASS; ?></label>
            <input type="text" name="settings[custom_class]" class="form-control" value="<?= $customClass ?>" placeholder="my-audio">
        </div>
        <?php
        return ob_get_clean();
    }

    public function prepareContent($content): array {
        $content = parent::prepareContent($content);
        
        $oldSrcMp3 = $content['src_mp3'] ?? '';

        if (isset($_POST['remove_audio']) && $_POST['remove_audio'] == '1') {
            if (!empty($oldSrcMp3) && !filter_var($oldSrcMp3, FILTER_VALIDATE_URL)) {
                $oldFile = UPLOADS_PATH . $oldSrcMp3;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $content['src_mp3'] = '';
            return $content;
        }

        if (!empty($_POST['external_url'])) {
            $externalUrl = trim($_POST['external_url']);
            if (filter_var($externalUrl, FILTER_VALIDATE_URL)) {
                if (!empty($oldSrcMp3) && !filter_var($oldSrcMp3, FILTER_VALIDATE_URL)) {
                    $oldFile = UPLOADS_PATH . $oldSrcMp3;
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $content['src_mp3'] = $externalUrl;
                return $content;
            }
        }

        if (!empty($_FILES['audio_file']['tmp_name']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $uploadDir = UPLOADS_PATH . '/audio/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $file = $_FILES['audio_file'];
                
                if ($file['size'] > 20 * 1024 * 1024) {
                    throw new Exception(LANG_POSTBLOCK_AUDIO_ERROR_FILE_TOO_LARGE);
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMime = ['audio/mpeg', 'audio/mp3'];
                if (!in_array($mime, $allowedMime)) {
                    throw new Exception(LANG_POSTBLOCK_AUDIO_ERROR_INVALID_FORMAT);
                }

                $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
                $destPath = $uploadDir . $safeName;

                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    throw new Exception(LANG_POSTBLOCK_AUDIO_ERROR_SAVE_FAILED);
                }

                if (!empty($oldSrcMp3) && !filter_var($oldSrcMp3, FILTER_VALIDATE_URL)) {
                    $oldFile = UPLOADS_PATH . $oldSrcMp3;
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $content['src_mp3'] = '/uploads/audio/' . $safeName;
                
            } catch (Exception $e) {
                \Notification::error(LANG_POSTBLOCK_AUDIO_ERROR_UPLOAD . $e->getMessage());
                $content['src_mp3'] = $oldSrcMp3;
            }
        } else {
            $content['src_mp3'] = $oldSrcMp3;
        }

        if (isset($_POST['content']['caption'])) {
            $content['caption'] = trim($_POST['content']['caption']);
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

    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);

        $srcMp3 = $content['src_mp3'] ?? '';
        $caption = $content['caption'] ?? '';

        if (empty($srcMp3)) {
            return LANG_POSTBLOCK_AUDIO_EMPTY_COMMENT;
        }

        $customClass = html($settings['custom_class'] ?? '');
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        $mp3Url = filter_var($srcMp3, FILTER_VALIDATE_URL) ? $srcMp3 : BASE_URL . $srcMp3;

        $fallback = '<p class="text-muted">' . sprintf(LANG_POSTBLOCK_AUDIO_FALLBACK, html($mp3Url)) . '</p>';

        $captionHtml = '';
        if (!empty($caption)) {
            $captionHtml = '<figcaption class="audio-caption"><i class="bi bi-music-note me-1"></i>' . html($caption) . '</figcaption>';
        }

        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . (int)$presetId;
            if ($presetName) {
                $presetClass .= ' preset-' . preg_replace('/[^a-z0-9_-]/i', '-', strtolower($presetName));
            }
        }

        $result = $template;
        $result = str_replace('{src_mp3}', html($mp3Url), $result);
        $result = str_replace('{caption}', html($caption), $result);
        $result = str_replace('{fallback_text}', $fallback, $result);
        $result = str_replace('{caption_html}', $captionHtml, $result);
        $result = str_replace('{custom_class}', $customClass . ' ' . $presetClass, $result);
        $result = str_replace('{preset_id}', $presetId ? html($presetId) : '', $result);
        $result = str_replace('{preset_name}', $presetName ? html($presetName) : '', $result);
        $result = str_replace('{block_type}', $this->getSystemName(), $result);
        $result = str_replace('{block_name}', $this->getName(), $result);

        return $result;
    }

    public function validateSettings($settings): array {
        $errors = [];
        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_AUDIO_VALIDATION_CSS_CLASS;
        }
        return [empty($errors), $errors];
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['src_mp3' => '', 'caption' => ''];
        }
        
        if (!is_array($content)) {
            return ['src_mp3' => '', 'caption' => ''];
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

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/audioblock/audio.css',
        ];
    }

    public function getFrontendJs(): array {
        return [
            '/templates/default/front/assets/postblocks/audioblock/audio.js',
        ];
    }

}