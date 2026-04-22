<?php

class VideoBlock extends BasePostBlock {

    public function getName(): string {
        return 'Видео';
    }

    public function getSystemName(): string {
        return 'VideoBlock';
    }

    public function getDescription(): string {
        return 'Вставляет видео с поддержкой загрузки MP4, а также вставки с Rutube, VK Video и Youtube.';
    }

    public function getIcon(): string {
        return 'bi bi-camera-video';
    }

    public function getCategory(): string {
        return 'media';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="post-block-video {custom_class}">
            <div class="video-wrapper">
                {video_embed}
            </div>
            {caption_html}
        </div>';
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{video_embed}' => 'HTML-код вставки видео',
            '{video_url}' => 'URL видео',
            '{video_type}' => 'Тип видео (upload, rutube, vk, youtube)',
            '{caption}' => 'Подпись под видео',
            '{caption_html}' => 'HTML-код подписи с обрамлением',
            '{poster}' => 'URL постера (превью) для загруженного видео',
        ]);
    }

    public function getDefaultContent(): array {
        return [
            'video_type' => 'upload',
            'video_url' => '',
            'video_id' => '',
            'caption' => '',
            'poster' => '',
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'width' => '100%',
            'height' => 'auto',
            'aspect_ratio' => '16:9',
            'controls' => true,
            'autoplay' => false,
            'loop' => false,
            'muted' => false,
            'custom_class' => '',
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $videoType = $content['video_type'] ?? 'upload';
        $videoUrl = $content['video_url'] ?? '';
        $caption = $content['caption'] ?? '';
        $customClass = html($settings['custom_class'] ?? '');
        
        $hasVideo = !empty($videoUrl) || !empty($content['video_id']);
        
        $typeLabel = match($videoType) {
            'upload' => 'Загруженное',
            'rutube' => 'Rutube',
            'vk' => 'VK Video',
            'youtube' => 'Youtube',
            default => 'Неизвестно'
        };
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-VideoBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-camera-video"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Видео</strong>
                                <span class="badge bg-info badge-sm"><?= $typeLabel ?></span>
                                <?php if ($hasVideo) { ?>
                                    <span class="badge bg-success badge-sm">Загружено</span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?php if ($hasVideo) { ?>
                                    <?php if ($videoType === 'upload') { ?>
                                        Локальный файл
                                    <?php } else { ?>
                                        Внешняя ссылка
                                    <?php } ?>
                                <?php } else { ?>
                                    Не загружено
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
                    <?php if ($hasVideo) { ?>
                        <div class="video-preview-container">
                            <div class="video-placeholder bg-dark d-flex align-items-center justify-content-center" 
                                 style="aspect-ratio: 16/9; border-radius: 8px;">
                                <div class="text-center text-white">
                                    <i class="bi bi-play-circle display-1 mb-2"></i>
                                    <p class="mb-0">Видео: <?= $typeLabel ?></p>
                                    <small class="text-white-50">
                                        <?= $videoType === 'upload' ? basename($videoUrl) : parse_url($videoUrl, PHP_URL_HOST) ?>
                                    </small>
                                </div>
                            </div>
                            
                            <?php if (!empty($caption)) { ?>
                                <div class="video-caption mt-2 text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-camera-video me-1"></i>
                                        <?= html(mb_substr($caption, 0, 60)) ?>
                                        <?php if (mb_strlen($caption) > 60) { ?>...<?php } ?>
                                    </small>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-camera-video"></i>
                            <div class="empty-text">Видео не загружено</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Добавить видео
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
        $videoType = $content['video_type'] ?? 'upload';
        $videoUrl = $content['video_url'] ?? '';
        
        if (empty($videoUrl) && empty($content['video_id'])) {
            return '
            <div class="post-block-video-preview text-center p-3 bg-light border rounded">
                <i class="bi bi-camera-video fs-1 text-muted"></i>
                <p class="text-muted mt-2 mb-0">Видео не загружено</p>
                <small class="text-secondary">Нажмите "Редактировать", чтобы добавить видео</small>
            </div>';
        }
        
        $typeLabel = match($videoType) {
            'upload' => 'Локальное видео',
            'rutube' => 'Rutube',
            'vk' => 'VK Video',
            'youtube' => 'Youtube',
            default => 'Видео'
        };
        
        return '
        <div class="post-block-video-preview">
            <div class="video-placeholder bg-dark d-flex align-items-center justify-content-center" 
                 style="aspect-ratio: 16/9; border-radius: 8px;">
                <div class="text-center text-white">
                    <i class="bi bi-play-circle display-4 mb-2"></i>
                    <p class="mb-0">' . $typeLabel . '</p>
                </div>
            </div>
        </div>';
    }

    public function getContentForm($currentContent = []): string {
        $content = $this->validateAndNormalizeContent($currentContent);
        
        $videoType = $content['video_type'] ?? 'upload';
        $videoUrl = html($content['video_url'] ?? '');
        $videoId = html($content['video_id'] ?? '');
        $caption = html($content['caption'] ?? '');
        $poster = html($content['poster'] ?? '');
        
        $hasUploadedVideo = ($videoType === 'upload' && !empty($videoUrl));
        
        $formId = 'video_form_' . uniqid();
        
        ob_start();
        ?>
        <div class="video-block-form" id="<?= $formId ?>" data-video-block>
            <div class="mb-3">
                <label class="form-label">Источник видео</label>
                <select name="content[video_type]" class="form-select" data-video-type-select>
                    <option value="upload" <?= $videoType === 'upload' ? 'selected' : '' ?>>Загрузить файл (MP4)</option>
                    <option value="rutube" <?= $videoType === 'rutube' ? 'selected' : '' ?>>Rutube</option>
                    <option value="vk" <?= $videoType === 'vk' ? 'selected' : '' ?>>VK Video</option>
                    <option value="youtube" <?= $videoType === 'youtube' ? 'selected' : '' ?>>Youtube</option>
                </select>
            </div>

            <div class="upload-section" data-upload-section style="<?= $videoType !== 'upload' ? 'display: none;' : '' ?>">
                <div class="mb-3">
                    <label class="form-label">Видеофайл (MP4)</label>
                    <input type="file" 
                        name="video_file" 
                        class="form-control" 
                        accept=".mp4,video/mp4"
                        data-video-file>
                    <div class="form-text">
                        Поддерживается формат MP4. Максимальный размер: 100MB.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Постер (превью, опционально)</label>
                    <input type="file" 
                        name="video_poster" 
                        class="form-control" 
                        accept="image/*">
                    <div class="form-text">
                        Изображение, которое будет показываться до начала воспроизведения.
                    </div>
                </div>
                
                <?php if ($hasUploadedVideo) { ?>
                    <div class="current-file mb-2 p-2 bg-light border rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-play me-2 text-primary"></i>
                            <span class="flex-grow-1"><?= html(basename($videoUrl)) ?></span>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_video" value="1" id="remove_video_<?= $formId ?>">
                                <label class="form-check-label text-danger" for="remove_video_<?= $formId ?>">
                                    Удалить
                                </label>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (!empty($poster)) { ?>
                    <div class="current-file mb-2 p-2 bg-light border rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-image me-2 text-primary"></i>
                            <span class="flex-grow-1"><?= html(basename($poster)) ?></span>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_poster" value="1" id="remove_poster_<?= $formId ?>">
                                <label class="form-check-label text-danger" for="remove_poster_<?= $formId ?>">
                                    Удалить
                                </label>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="external-section" data-external-section style="<?= $videoType === 'upload' ? 'display: none;' : '' ?>">
                <div class="mb-3">
                    <label class="form-label" data-url-label>
                        <?php if ($videoType === 'rutube') { ?>
                            Ссылка на видео Rutube
                        <?php } elseif ($videoType === 'vk') { ?>
                            Ссылка на видео VK
                        <?php } elseif ($videoType === 'youtube') { ?>
                            Ссылка на видео Youtube
                        <?php } else { ?>
                            Ссылка на видео
                        <?php } ?>
                    </label>
                   <input type="url" 
       name="content[video_url]" 
       class="form-control" 
       data-video-url-input
       value="<?= $videoType !== 'upload' ? html($videoUrl) : '' ?>" 
       placeholder="<?= 
           $videoType === 'youtube' ? 'https://www.youtube.com/watch?v=...' : 
           ($videoType === 'rutube' ? 'https://rutube.ru/video/...' : 
           ($videoType === 'vk' ? 'https://vk.com/video...' : 'https://...')) 
       ?>">

<div class="form-text" data-url-hint>
    <?php if ($videoType === 'youtube') { ?>
        Пример: https://www.youtube.com/watch?v=xxxxxx или https://youtu.be/xxxxxx
    <?php } elseif ($videoType === 'rutube') { ?>
        Пример: https://rutube.ru/video/private/xxx/ или https://rutube.ru/video/xxx/
    <?php } elseif ($videoType === 'vk') { ?>
        Пример: https://vk.com/video-xxx_xxx или https://vk.com/video?z=video-xxx_xxx
    <?php } ?>
</div>
                </div>
                <input type="hidden" name="content[video_id]" value="<?= $videoId ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Подпись (опционально)</label>
                <input type="text" 
                    name="content[caption]" 
                    class="form-control" 
                    value="<?= $caption ?>" 
                    placeholder="Описание видео">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $settings = $this->validateAndNormalizeSettings($currentSettings);
        
        $width = html($settings['width'] ?? '100%');
        $height = html($settings['height'] ?? 'auto');
        $aspectRatio = $settings['aspect_ratio'] ?? '16:9';
        $controls = !empty($settings['controls']);
        $autoplay = !empty($settings['autoplay']);
        $loop = !empty($settings['loop']);
        $muted = !empty($settings['muted']);
        $customClass = html($settings['custom_class'] ?? '');

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Соотношение сторон</label>
                    <select name="settings[aspect_ratio]" class="form-select">
                        <option value="16:9" <?= $aspectRatio === '16:9' ? 'selected' : '' ?>>16:9 (Широкоформатное)</option>
                        <option value="4:3" <?= $aspectRatio === '4:3' ? 'selected' : '' ?>>4:3 (Стандартное)</option>
                        <option value="1:1" <?= $aspectRatio === '1:1' ? 'selected' : '' ?>>1:1 (Квадратное)</option>
                        <option value="9:16" <?= $aspectRatio === '9:16' ? 'selected' : '' ?>>9:16 (Вертикальное)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Дополнительный CSS класс</label>
                    <input type="text" name="settings[custom_class]" class="form-control" value="<?= $customClass ?>" placeholder="my-video">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Ширина</label>
                    <input type="text" name="settings[width]" class="form-control" value="<?= $width ?>" placeholder="100% или 800px">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Высота</label>
                    <input type="text" name="settings[height]" class="form-control" value="<?= $height ?>" placeholder="auto или 450px">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="settings[controls]" id="video_controls" value="1" <?= $controls ? 'checked' : '' ?>>
                    <label class="form-check-label" for="video_controls">Элементы управления</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="settings[autoplay]" id="video_autoplay" value="1" <?= $autoplay ? 'checked' : '' ?>>
                    <label class="form-check-label" for="video_autoplay">Автовоспроизведение</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="settings[loop]" id="video_loop" value="1" <?= $loop ? 'checked' : '' ?>>
                    <label class="form-check-label" for="video_loop">Зациклить</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="settings[muted]" id="video_muted" value="1" <?= $muted ? 'checked' : '' ?>>
                    <label class="form-check-label" for="video_muted">Без звука</label>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function prepareContent($content): array {
        $content = parent::prepareContent($content);
        
        $oldVideoUrl = $content['video_url'] ?? '';
        $oldPoster = $content['poster'] ?? '';
        
        $videoType = $_POST['content']['video_type'] ?? ($content['video_type'] ?? 'upload');
        $content['video_type'] = $videoType;
        
        if ($videoType === 'upload') {
            
            $newVideoUploaded = false;
            
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                try {
                    $fileName = $this->uploadVideoFile($_FILES['video_file'], 'video');
                    
                    if (!empty($oldVideoUrl) && !filter_var($oldVideoUrl, FILTER_VALIDATE_URL)) {
                        $oldFile = UPLOADS_PATH . $oldVideoUrl;
                        if (file_exists($oldFile)) {
                            @unlink($oldFile);
                        }
                    }
                    
                    $content['video_url'] = '/uploads/video/' . $fileName;
                    $newVideoUploaded = true;
                    
                } catch (Exception $e) {
                    \Notification::error('Ошибка загрузки видео: ' . $e->getMessage());
                }
            }
            
            if (!$newVideoUploaded) {
                $content['video_url'] = $oldVideoUrl;
            }
            
            if (isset($_POST['remove_video']) && $_POST['remove_video'] == '1') {
                if (!empty($oldVideoUrl) && !filter_var($oldVideoUrl, FILTER_VALIDATE_URL)) {
                    $oldFile = UPLOADS_PATH . $oldVideoUrl;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                $content['video_url'] = '';
            }
            
            $newPosterUploaded = false;
            
            if (isset($_FILES['video_poster']) && $_FILES['video_poster']['error'] === UPLOAD_ERR_OK) {
                try {
                    $fileName = $this->uploadVideoFile($_FILES['video_poster'], 'poster');
                    
                    if (!empty($oldPoster) && !filter_var($oldPoster, FILTER_VALIDATE_URL)) {
                        $oldFile = UPLOADS_PATH . $oldPoster;
                        if (file_exists($oldFile)) {
                            @unlink($oldFile);
                        }
                    }
                    
                    $content['poster'] = '/uploads/video/poster/' . $fileName;
                    $newPosterUploaded = true;
                    
                } catch (Exception $e) {
                    \Notification::error('Ошибка загрузки постера: ' . $e->getMessage());
                }
            }
            
            if (!$newPosterUploaded) {
                $content['poster'] = $oldPoster;
            }
            
            if (isset($_POST['remove_poster']) && $_POST['remove_poster'] == '1') {
                if (!empty($oldPoster) && !filter_var($oldPoster, FILTER_VALIDATE_URL)) {
                    $oldFile = UPLOADS_PATH . $oldPoster;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                $content['poster'] = '';
            }
            
        } else {
            $videoUrl = trim($_POST['content']['video_url'] ?? '');
            $content['video_url'] = $videoUrl;
            
            if (!empty($videoUrl)) {
                $videoId = $this->parseVideoId($videoUrl, $videoType);
                $content['video_id'] = $videoId;
            } else {
                $content['video_id'] = '';
            }
            
            if (!empty($oldVideoUrl) && !filter_var($oldVideoUrl, FILTER_VALIDATE_URL)) {
                $oldFile = UPLOADS_PATH . $oldVideoUrl;
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            if (!empty($oldPoster) && !filter_var($oldPoster, FILTER_VALIDATE_URL)) {
                $oldFile = UPLOADS_PATH . $oldPoster;
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            
            $content['poster'] = '';
        }
        
        if (isset($_POST['content']['caption'])) {
            $content['caption'] = trim($_POST['content']['caption']);
        }
        
        return $content;
    }

    private function parseVideoId($url, $type) {
        if (empty($url)) return '';
        
        switch ($type) {
            case 'youtube':
    // Matches standard URLs and shortened youtu.be URLs
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $matches)) {
        return $matches[1];
    }
    break;
            case 'rutube':
                if (preg_match('/rutube\.ru\/video\/(?:private\/)?([a-zA-Z0-9]+)/', $url, $matches)) {
                    return $matches[1];
                }
                break;
                
            case 'vk':
                if (preg_match('/video(-?\d+_\d+)/', $url, $matches)) {
                    return $matches[1];
                }
                if (preg_match('/[?&]oid=(-?\d+)&id=(\d+)/', $url, $matches)) {
                    return $matches[1] . '_' . $matches[2];
                }
                break;
        }
        
        return '';
    }

    private function uploadVideoFile($file, $subdir) {
        $uploadDir = UPLOADS_PATH . '/' . $subdir . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $maxSize = $subdir === 'video' ? 100 * 1024 * 1024 : 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $maxMB = $maxSize / (1024 * 1024);
            throw new Exception("Файл слишком большой. Максимум {$maxMB}MB.");
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($subdir === 'video') {
            $allowedMime = ['video/mp4'];
            if (!in_array($mime, $allowedMime)) {
                throw new Exception('Неверный формат файла. Ожидается MP4.');
            }
        } else {
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowedMime)) {
                throw new Exception('Неверный формат постера. Ожидается JPEG, PNG или WebP.');
            }
        }
        
        $ext = $subdir === 'video' ? 'mp4' : pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = uniqid() . '.' . $ext;
        $destPath = $uploadDir . $safeName;
        
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new Exception('Не удалось сохранить файл.');
        }
        
        return $safeName;
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            $settings = array_merge($settings, $_POST['settings']);
        }
        
        $settings['controls'] = !empty($settings['controls']);
        $settings['autoplay'] = !empty($settings['autoplay']);
        $settings['loop'] = !empty($settings['loop']);
        $settings['muted'] = !empty($settings['muted']);
        
        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
        }
        
        return $settings;
    }

    private function generateVideoEmbed($content, $settings) {
        $videoType = $content['video_type'] ?? 'upload';
        $videoUrl = $content['video_url'] ?? '';
        $videoId = $content['video_id'] ?? '';
        $poster = $content['poster'] ?? '';
        
        $width = $settings['width'] ?? '100%';
        $height = $settings['height'] ?? 'auto';
        $aspectRatio = $settings['aspect_ratio'] ?? '16:9';
        $controls = !empty($settings['controls']) ? 'controls' : '';
        $autoplay = !empty($settings['autoplay']) ? 'autoplay' : '';
        $loop = !empty($settings['loop']) ? 'loop' : '';
        $muted = !empty($settings['muted']) ? 'muted' : '';
        
        $ratioPadding = match($aspectRatio) {
            '4:3' => '75%',
            '1:1' => '100%',
            '9:16' => '177.78%',
            default => '56.25%'
        };
        
        $style = "width: {$width}; height: {$height};";
        if ($height === 'auto') {
            $style = "width: {$width}; position: relative; padding-bottom: {$ratioPadding};";
        }
        
        switch ($videoType) {
            case 'upload':
                $videoSrc = filter_var($videoUrl, FILTER_VALIDATE_URL) ? $videoUrl : BASE_URL . $videoUrl;
                $posterAttr = !empty($poster) ? ' poster="' . html(BASE_URL . $poster) . '"' : '';
                
                $html = '<div class="video-container" style="' . $style . '">';
                $html .= '<video class="video-player" ' . $controls . ' ' . $autoplay . ' ' . $loop . ' ' . $muted . $posterAttr . ' style="width: 100%; height: 100%; object-fit: contain;">';
                $html .= '<source src="' . html($videoSrc) . '" type="video/mp4">';
                $html .= '<p class="text-muted">Ваш браузер не поддерживает HTML5 видео. <a href="' . html($videoSrc) . '">Скачайте файл</a>.</p>';
                $html .= '</video>';
                $html .= '</div>';
                return $html;
                
            case 'youtube':
    $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
    $html = '<div class="video-container" style="' . $style . '">';
    $html .= '<iframe src="' . $embedUrl . '" ';
    $html .= 'style="width: 100%; height: 100%; border: none;" ';
    $html .= 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" ';
    $html .= 'allowfullscreen></iframe>';
    $html .= '</div>';
    return $html;

            case 'rutube':
                $embedUrl = 'https://rutube.ru/play/embed/' . $videoId;
                $html = '<div class="video-container" style="' . $style . '">';
                $html .= '<iframe src="' . $embedUrl . '" ';
                $html .= 'style="width: 100%; height: 100%; border: none;" ';
                $html .= 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" ';
                $html .= 'allowfullscreen></iframe>';
                $html .= '</div>';
                return $html;
                
            case 'vk':
                $embedUrl = 'https://vk.com/video_ext.php?oid=' . str_replace('_', '&id=', $videoId) . '&hd=2';
                $html = '<div class="video-container" style="' . $style . '">';
                $html .= '<iframe src="' . $embedUrl . '" ';
                $html .= 'style="width: 100%; height: 100%; border: none;" ';
                $html .= 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" ';
                $html .= 'allowfullscreen></iframe>';
                $html .= '</div>';
                return $html;
                
            default:
                return '<!-- Неизвестный тип видео -->';
        }
    }

    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $videoType = $content['video_type'] ?? 'upload';
        $videoUrl = $content['video_url'] ?? '';
        $videoId = $content['video_id'] ?? '';
        $caption = $content['caption'] ?? '';
        
        if ($videoType === 'upload' && empty($videoUrl)) {
            return '<!-- VideoBlock: нет видео -->';
        }
        if ($videoType !== 'upload' && empty($videoId) && empty($videoUrl)) {
            return '<!-- VideoBlock: нет ссылки -->';
        }
        
        $customClass = html($settings['custom_class'] ?? '');
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        
        $videoEmbed = $this->generateVideoEmbed($content, $settings);
        
        $captionHtml = '';
        if (!empty($caption)) {
            $captionHtml = '<figcaption class="video-caption"><i class="bi bi-camera-video me-1"></i>' . html($caption) . '</figcaption>';
        }
        
        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . (int)$presetId;
            if ($presetName) {
                $presetClass .= ' preset-' . preg_replace('/[^a-z0-9_-]/i', '-', strtolower($presetName));
            }
        }
        
        $result = $template;
        $result = str_replace('{video_embed}', $videoEmbed, $result);
        $result = str_replace('{video_url}', html($videoUrl), $result);
        $result = str_replace('{video_type}', html($videoType), $result);
        $result = str_replace('{caption}', html($caption), $result);
        $result = str_replace('{caption_html}', $captionHtml, $result);
        $result = str_replace('{poster}', html($content['poster'] ?? ''), $result);
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
            $errors[] = 'CSS класс может содержать только буквы, цифры, дефисы и подчеркивания';
        }
        return [empty($errors), $errors];
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : $this->getDefaultContent();
        }
        
        if (!is_array($content)) {
            return $this->getDefaultContent();
        }
        
        return array_merge($this->getDefaultContent(), $content);
    }

    public function validateAndNormalizeSettings($settings): array {
        if (is_string($settings)) {
            $decoded = json_decode($settings, true);
            return is_array($decoded) ? $decoded : $this->getDefaultSettings();
        }
        
        if (!is_array($settings)) {
            return $this->getDefaultSettings();
        }
        
        return array_merge($this->getDefaultSettings(), $settings);
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/videoblock/video.css',
        ];
    }

    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/blocks/video.js',
        ];
    }

}
