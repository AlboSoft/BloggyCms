<?php
class PostBlockPostBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_POSTBLOCK_NAME;
    }

    public function getSystemName(): string {
        return 'PostBlockPostBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_POSTBLOCK_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-file-post';
    }

    public function getCategory(): string {
        return 'advanced';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="card post-block-post">
            <div class="row g-0">
                {if-image}
                <div class="col-md-4">
                    <img src="{featured_image}" class="img-fluid rounded-start" alt="{post_title}">
                </div>
                <div class="col-md-8">
                {/if-image}
                {if-not-image}
                <div class="col-12">
                {/if-not-image}
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="{post_url}" class="text-decoration-none">{post_title}</a>
                        </h5>
                        <div class="card-text mb-2">
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>{author_name} | 
                                <i class="bi bi-calendar me-1"></i>{post_date} | 
                                <i class="bi bi-eye me-1"></i>{views_count}
                            </small>
                        </div>
                        <div class="card-text">
                            <span class="badge bg-secondary">{category_name}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        
        $selectedPostId = $currentContent['post_id'] ?? '';
        $selectedPostTitle = $currentContent['post_title'] ?? '';
        $posts = $this->getAvailablePosts();
        
        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_POSTBLOCK_FORM_POST_LABEL; ?></label>
            
            <?php if (empty($posts)) { ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo LANG_POSTBLOCK_POSTBLOCK_NO_POSTS_TEXT; ?>
                    <a href="<?= ADMIN_URL ?>/posts/create" target="_blank" class="alert-link">
                        <?php echo LANG_POSTBLOCK_POSTBLOCK_NO_POSTS_LINK; ?>
                    </a>
                </div>
            <?php } else { ?>
                <select name="content[post_id]" class="form-select" id="post-select" required>
                    <option value=""><?php echo LANG_POSTBLOCK_POSTBLOCK_SELECT_POST; ?></option>
                    <?php foreach ($posts as $post) { ?>
                        <option value="<?= $post['id'] ?>" 
                                data-title="<?= html($post['title']) ?>"
                                <?= $selectedPostId == $post['id'] ? 'selected' : '' ?>>
                            <?= html($post['title']) ?> 
                            (ID: <?= $post['id'] ?>, <?php echo LANG_POSTBLOCK_POSTBLOCK_VIEWS; ?>: <?= $post['views'] ?? 0 ?>)
                        </option>
                    <?php } ?>
                </select>
                <div class="form-text">
                    <?php echo LANG_POSTBLOCK_POSTBLOCK_FORM_POST_HINT; ?>
                </div>
                <input type="hidden" name="content[post_title]" id="post-title" value="<?= html($selectedPostTitle) ?>">
                <div id="post-preview" class="mt-3 p-3 border rounded bg-light" style="<?= empty($selectedPostId) ? 'display:none;' : '' ?>">
                    <?php if (!empty($selectedPostId)) { ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong><?php echo LANG_POSTBLOCK_POSTBLOCK_SELECTED_POST; ?></strong>
                            <span class="badge bg-primary" id="preview-post-title"><?= html($selectedPostTitle) ?></span>
                        </div>
                        <div class="text-muted small">
                            ID: <span id="preview-post-id"><?= $selectedPostId ?></span> | 
                            <?php echo LANG_POSTBLOCK_POSTBLOCK_VIEWS; ?>: <span id="preview-post-views"><?= $currentContent['views'] ?? 0 ?></span>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
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
            <label class="form-label"><?php echo LANG_POSTBLOCK_POSTBLOCK_SETTINGS_CUSTOM_CLASS; ?></label>
            <input type="text" 
                   name="settings[custom_class]" 
                   class="form-control" 
                   value="<?= html($customClass) ?>" 
                   placeholder="my-post-block">
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
        
        $postId = $content['post_id'] ?? '';
        
        if (empty($postId)) {
            return '<!-- ' . LANG_POSTBLOCK_POSTBLOCK_NO_POST_SELECTED . ' -->';
        }
        
        $postData = $this->getPostData($postId);
        
        if (empty($postData)) {
            return '<!-- ' . LANG_POSTBLOCK_POSTBLOCK_POST_NOT_FOUND . ' -->';
        }

        $customClass = $settings['custom_class'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . $presetId;
            if ($presetName) {
                $presetClass .= ' preset-' . preg_replace('/[^a-z0-9_-]/i', '-', strtolower($presetName));
            }
        }

        $postUrl = BASE_URL . '/post/' . ($postData['slug'] ?? $postData['id']);
        $categoryUrl = !empty($postData['category_slug']) ? BASE_URL . '/category/' . $postData['category_slug'] : '';
        $hasImage = !empty($postData['featured_image']);
        $imageUrl = $hasImage ? BASE_URL . '/uploads/images/' . $postData['featured_image'] : '';
        $defaultImage = BASE_URL . '/assets/images/default-post-image.jpg';
        $replacements = [
            '{post_id}' => html($postData['id'] ?? ''),
            '{post_title}' => html($postData['title'] ?? ''),
            '{post_url}' => html($postUrl),
            '{featured_image}' => html($imageUrl),
            '{default_image}' => html($defaultImage),
            '{views_count}' => $postData['views'] ?? 0,
            '{post_date}' => date('d.m.Y', strtotime($postData['created_at'] ?? '')),
            '{author_name}' => html($postData['author_name'] ?? ''),
            '{category_name}' => html($postData['category_name'] ?? ''),
            '{category_url}' => html($categoryUrl),
            '{custom_class}' => trim($customClass . ' ' . $presetClass),
            '{preset_id}' => $presetId ? html($presetId) : '',
            '{preset_name}' => $presetName ? html($presetName) : '',
            '{block_type}' => $this->getSystemName(),
            '{block_name}' => $this->getName()
        ];
        
        $output = $template;
        $output = $this->processConditionalBlocks($output, $hasImage);
        foreach ($replacements as $shortcode => $replacement) {
            $output = str_replace($shortcode, $replacement, $output);
        }
        
        return $output;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{post_id}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_POST_ID,
            '{post_title}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_POST_TITLE,
            '{post_url}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_POST_URL,
            '{featured_image}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_FEATURED_IMAGE,
            '{default_image}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_DEFAULT_IMAGE,
            '{views_count}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_VIEWS_COUNT,
            '{post_date}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_POST_DATE,
            '{author_name}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_AUTHOR_NAME,
            '{category_name}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_CATEGORY_NAME,
            '{category_url}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_CATEGORY_URL,
            '{if-image}...{/if-image}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_IF_IMAGE,
            '{if-not-image}...{/if-not-image}' => LANG_POSTBLOCK_POSTBLOCK_SHORTCODE_IF_NOT_IMAGE
        ]);
    }


    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/blocks/post-block.js'
        ];
    }

    private function getAvailablePosts(): array {
        try {
            $db = Database::getInstance();
            
            return $db->fetchAll("
                SELECT 
                    id, 
                    title, 
                    views,
                    created_at
                FROM posts 
                WHERE status = 'published'
                ORDER BY created_at DESC
                LIMIT 100
            ");
        } catch (Exception $e) {
            return [];
        }
    }

    private function getPostData($postId): ?array {
        try {
            $db = Database::getInstance();
            
            $post = $db->fetch("
                SELECT 
                    p.*,
                    u.username as author_name,
                    u.display_name as author_display_name,
                    c.name as category_name,
                    c.slug as category_slug
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ? AND p.status = 'published'
            ", [$postId]);
            
            return $post ?: null;
            
        } catch (Exception $e) {
            return null;
        }
    }

    private function processConditionalBlocks(string $template, bool $hasImage): string {
        if (preg_match_all('/\{if-image\}(.*?)\{\/if-image\}/s', $template, $matches)) {
            foreach ($matches[0] as $index => $fullMatch) {
                $content = $matches[1][$index];
                if ($hasImage) {
                    $template = str_replace($fullMatch, $content, $template);
                } else {
                    $template = str_replace($fullMatch, '', $template);
                }
            }
        }
        
        if (preg_match_all('/\{if-not-image\}(.*?)\{\/if-not-image\}/s', $template, $matches)) {
            foreach ($matches[0] as $index => $fullMatch) {
                $content = $matches[1][$index];
                if (!$hasImage) {
                    $template = str_replace($fullMatch, $content, $template);
                } else {
                    $template = str_replace($fullMatch, '', $template);
                }
            }
        }
        
        return $template;
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_POSTBLOCK_VALIDATION_CUSTOM_CLASS;
        }

        return [empty($errors), $errors];
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['post_id' => '', 'post_title' => ''];
        }
        
        if (!is_array($content)) {
            return ['post_id' => '', 'post_title' => ''];
        }
        
        if (!isset($content['post_id'])) {
            $content['post_id'] = '';
        }
        
        if (!isset($content['post_title'])) {
            $content['post_title'] = '';
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
            if (isset($_POST['content']['post_id'])) {
                $content['post_id'] = $_POST['content']['post_id'];
            }
            
            if (isset($_POST['content']['post_title'])) {
                $content['post_title'] = $_POST['content']['post_title'];
            }
        }
        
        if (!empty($content['post_id'])) {
            $postData = $this->getPostData($content['post_id']);
            if ($postData) {
                $content['post_title'] = $postData['title'] ?? '';
                $content['views'] = $postData['views'] ?? 0;
            }
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
        return null;
    }

    public function canExtractFromHtml(): bool {
        return false;
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $postId = $content['post_id'] ?? '';
        $postTitle = $content['post_title'] ?? '';
        $views = $content['views'] ?? 0;
        $customClass = $settings['custom_class'] ?? '';
        $postData = null;
        if (!empty($postId)) {
            $postData = $this->getPostData($postId);
        }
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-PostBlockPostBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-file-post"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_TITLE; ?></strong>
                                <?php if ($postData && !empty($postData['category_name'])) { ?>
                                    <span class="badge bg-secondary badge-sm"><?= html($postData['category_name']) ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?php if ($postData) { ?>
                                    ID: <?= $postId ?>
                                    · <?php echo LANG_POSTBLOCK_POSTBLOCK_VIEWS; ?> <?= $views ?>
                                <?php } else { ?>
                                    <?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_NO_POST; ?>
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
                    <?php if ($postData) { ?>
                        <div class="post-preview-container">
                            <div class="post-card-preview border rounded overflow-hidden">
                                <div class="row g-0">
                                    <div class="col-md-4 bg-light d-flex align-items-center justify-content-center" style="min-height: 120px;">
                                        <?php if (!empty($postData['featured_image'])) { ?>
                                            <div class="position-relative w-100 h-100">
                                                <div class="w-100 h-100 bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-image text-info display-4"></i>
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-card-image text-muted display-4"></i>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    
                                    <div class="col-md-8">
                                        <div class="p-3">
                                            <div class="h6 mb-2" style="color: #374151;">
                                                <?= html(mb_substr($postData['title'] ?? '', 0, 50)) ?>
                                                <?php if (mb_strlen($postData['title'] ?? '') > 50) { ?>...<?php } ?>
                                            </div>
                                            
                                            <div class="small text-muted mb-2">
                                                <i class="bi bi-person me-1"></i>
                                                <?= html($postData['author_name'] ?? LANG_POSTBLOCK_POSTBLOCK_DEFAULT_AUTHOR) ?>
                                                <i class="bi bi-calendar ms-3 me-1"></i>
                                                <?= !empty($postData['created_at']) ? date('d.m.Y', strtotime($postData['created_at'])) : LANG_POSTBLOCK_POSTBLOCK_DEFAULT_DATE ?>
                                                <i class="bi bi-eye ms-3 me-1"></i>
                                                <?= $views ?>
                                            </div>
                                            
                                            <div class="small">
                                                <?php if (!empty($postData['category_name'])) { ?>
                                                    <span class="badge bg-light text-dark border"><?= html($postData['category_name']) ?></span>
                                                <?php } else { ?>
                                                    <span class="text-muted"><?php echo LANG_POSTBLOCK_POSTBLOCK_NO_CATEGORY; ?></span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="post-preview-info mt-3 small text-muted">
                                <div class="row">
                                    <div class="col-6">
                                        <div><i class="bi bi-hash me-1"></i><?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_INFO_POST_ID; ?> <strong><?= $postId ?></strong></div>
                                        <div><i class="bi bi-eye me-1"></i><?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_INFO_VIEWS; ?> <strong><?= $views ?></strong></div>
                                    </div>
                                    <div class="col-6">
                                        <?php if ($customClass) { ?>
                                            <div><i class="bi bi-tag me-1"></i><?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_INFO_CSS; ?> <strong><?= html($customClass) ?></strong></div>
                                        <?php } ?>
                                        <div><i class="bi bi-file-earmark-text me-1"></i><?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_INFO_TYPE; ?> <strong><?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_INFO_CARD; ?></strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-file-post"></i>
                            <div class="empty-text"><?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_EMPTY_TEXT; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> <?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_ADD_BTN; ?>
                            </button>
                            <div class="mt-3 small text-muted">
                                <i class="bi bi-info-circle"></i>
                                <?php echo LANG_POSTBLOCK_POSTBLOCK_PREVIEW_INFO_TEXT; ?>
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