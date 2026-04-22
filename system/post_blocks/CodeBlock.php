<?php
class CodeBlock extends BasePostBlock {
    
    public function getName(): string {
        return LANG_POSTBLOCK_CODE_NAME;
    }

    public function getSystemName(): string {
        return 'CodeBlock';
    }

    public function getDescription(): string {
        return LANG_POSTBLOCK_CODE_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-code-slash';
    }

    public function getCategory(): string {
        return 'advanced';
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $code = $content['code'] ?? LANG_POSTBLOCK_CODE_DEFAULT_CODE;
        $language = $content['language'] ?? 'javascript';
        $filename = $content['filename'] ?? '';
        $showLineNumbers = $settings['show_line_numbers'] ?? true;
        $copyButton = $settings['copy_button'] ?? true;
        $showLanguageBadge = $settings['show_language_badge'] ?? true;
        
        $languageNames = [
            'javascript' => 'JavaScript',
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'python' => 'Python',
            'java' => 'Java',
            'sql' => 'SQL',
            'json' => 'JSON',
            'xml' => 'XML',
            'bash' => 'Bash',
            'plaintext' => LANG_POSTBLOCK_CODE_LANGUAGE_TEXT
        ];
        
        $languageName = $languageNames[$language] ?? ucfirst($language);
        $previewCode = strlen($code) > 200 ? mb_substr($code, 0, 200) . '...' : $code;
        
        $html = '<div class="post-block-preview" style="border:1px solid #dee2e6;border-radius:8px;padding:12px;margin:10px 0;background:#fff;">';
        $html .= '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #dee2e6;">';
        $html .= '<div>';
        $html .= '<strong style="margin-right:8px;">' . bloggy_icon('bs', 'code-slash', '16 16', null, 'me-1') . LANG_POSTBLOCK_CODE_PREVIEW_TITLE . '</strong>';
        if ($showLanguageBadge) {
            $html .= '<span style="background:#6c757d;color:#fff;padding:2px 6px;border-radius:4px;font-size:12px;margin-right:6px;">' . htmlspecialchars($languageName) . '</span>';
        }
        if (!empty($filename)) {
            $html .= '<span style="background:#e9ecef;color:#212529;padding:2px 6px;border-radius:4px;font-size:12px;">' . htmlspecialchars($filename) . '</span>';
        }
        $html .= '</div>';
        $html .= '<div>';
        
        if ($copyButton) {
            $html .= '<span style="background:#6c757d;color:#fff;border-radius:4px;padding:4px 8px;font-size:12px;margin-right:6px;display:inline-block;">' . bloggy_icon('bs', 'clipboard', '14 14', null, 'me-1') . LANG_POSTBLOCK_CODE_COPY_BTN . '</span>';
        }
        
        $html .= '<button type="button" class="btn-edit-preview" style="background:#0d6efd;color:#fff;border:none;border-radius:4px;padding:4px 8px;font-size:12px;cursor:pointer;" onclick="postBlocksManager.editBlock(\'{block_id}\')">' . bloggy_icon('bs', 'pencil', '14 14', '#fff', 'me-1') . LANG_POSTBLOCK_CODE_EDIT_BTN . '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div style="border-radius:6px;padding:12px;overflow-x:auto;">';
        $html .= '<pre style="margin:0;font-family:Consolas,Monaco,\'Courier New\',monospace;font-size:13px;color:#000;"><code>' . htmlspecialchars($previewCode) . '</code></pre>';
        $html .= '</div>';
        $html .= '<div style="margin-top:8px;font-size:12px;color:#6c757d;">';
        $html .= bloggy_icon('bs', 'info-circle', '12 12', null, 'me-1') . strlen($code) . ' ' . LANG_POSTBLOCK_CODE_CHARS;
        if ($showLineNumbers) {
            $html .= ' · ' . bloggy_icon('bs', 'list-ol', '12 12', null, 'me-1') . LANG_POSTBLOCK_CODE_LINE_NUMBERS;
        }
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="code-block-wrapper {custom_class} theme-{theme}">
            <div class="code-header">
                <div class="code-meta">
                    {language_badge}
                    {filename}
                </div>
                <div class="code-actions">
                    {copy_button}
                </div>
            </div>
            <div class="code-container">
                <pre class="{line_numbers}" data-language="{language}"><code class="language-{language}">{code}</code></pre>
            </div>
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'code' => LANG_POSTBLOCK_CODE_DEFAULT_CODE,
            'language' => 'javascript',
            'filename' => ''
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'show_line_numbers' => true,
            'copy_button' => true,
            'theme' => 'default',
            'custom_class' => '',
            'show_language_badge' => true
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $code = $currentContent['code'] ?? '';
        $language = $currentContent['language'] ?? 'javascript';
        $filename = $currentContent['filename'] ?? '';

        $languages = [
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript',
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'scss' => 'SCSS',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++',
            'csharp' => 'C#',
            'sql' => 'SQL',
            'json' => 'JSON',
            'xml' => 'XML',
            'bash' => 'Bash',
            'markdown' => 'Markdown',
            'yaml' => 'YAML',
            'dockerfile' => 'Dockerfile',
            'nginx' => 'Nginx',
            'plaintext' => LANG_POSTBLOCK_CODE_LANGUAGE_TEXT
        ];

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_CODE_FORM_LANGUAGE_LABEL; ?></label>
                    <select name="content[language]" class="form-select" id="code-language-select">
                        <?php foreach($languages as $value => $name) { ?>
                            <option value="<?= $value ?>" <?= $language === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_CODE_FORM_FILENAME_LABEL; ?></label>
                    <input type="text" 
                           name="content[filename]" 
                           class="form-control" 
                           value="<?= html($filename) ?>" 
                           placeholder="script.js, style.css, etc.">
                    <div class="form-text">
                        <?php echo LANG_POSTBLOCK_CODE_FORM_FILENAME_HINT; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label"><?php echo LANG_POSTBLOCK_CODE_FORM_CODE_LABEL; ?></label>
            <div id="code-editor-container" style="height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
            <textarea name="content[code]" 
                     id="code-editor-textarea" 
                     style="display: none;"
                     required><?= html($code) ?></textarea>
            <div class="form-text">
                <?php echo LANG_POSTBLOCK_CODE_FORM_CODE_HINT; ?>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        const editorContainer = document.getElementById('code-editor-container');
                        const textarea = document.getElementById('code-editor-textarea');
                        
                        if (window.ace && editorContainer && textarea) {
                            const editor = ace.edit(editorContainer);
                            if (editor) {
                                textarea.value = editor.getValue();
                            }
                        }
                    });
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $showLineNumbers = $currentSettings['show_line_numbers'] ?? true;
        $copyButton = $currentSettings['copy_button'] ?? true;
        $theme = $currentSettings['theme'] ?? 'default';
        $customClass = $currentSettings['custom_class'] ?? '';
        $showLanguageBadge = $currentSettings['show_language_badge'] ?? true;

        $themes = [
            'default' => LANG_POSTBLOCK_CODE_THEME_DEFAULT,
            'dark' => LANG_POSTBLOCK_CODE_THEME_DARK,
            'material' => 'Material',
            'github' => 'GitHub',
            'coy' => 'Coy',
            'okaidia' => 'Okaidia',
            'tomorrow' => 'Tomorrow',
            'twilight' => 'Twilight'
        ];

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_CODE_SETTINGS_THEME_LABEL; ?></label>
                    <select name="settings[theme]" class="form-select">
                        <?php foreach($themes as $value => $name) { ?>
                            <option value="<?= $value ?>" <?= $theme === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_POSTBLOCK_CODE_SETTINGS_CSS_CLASS_LABEL; ?></label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= html($customClass) ?>" 
                           placeholder="my-code-block">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[show_line_numbers]" 
                           id="show_line_numbers"
                           value="1" 
                           <?= $showLineNumbers ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_line_numbers">
                        <?php echo LANG_POSTBLOCK_CODE_SETTINGS_LINE_NUMBERS; ?>
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[copy_button]" 
                           id="copy_button"
                           value="1" 
                           <?= $copyButton ? 'checked' : '' ?>>
                    <label class="form-check-label" for="copy_button">
                        <?php echo LANG_POSTBLOCK_CODE_SETTINGS_COPY_BUTTON; ?>
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[show_language_badge]" 
                           id="show_language_badge"
                           value="1" 
                           <?= $showLanguageBadge ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_language_badge">
                        <?php echo LANG_POSTBLOCK_CODE_SETTINGS_LANGUAGE_BADGE; ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <?= bloggy_icon('bs', 'info-circle', '16 16', null, 'me-2') ?>
            <?php echo LANG_POSTBLOCK_CODE_SETTINGS_PRISM_HINT; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $content = $this->validateAndNormalizeContent($content);
        
        $code = $content['code'] ?? LANG_POSTBLOCK_CODE_DEFAULT_CODE;
        $language = $content['language'] ?? 'javascript';
        $filename = $content['filename'] ?? '';
        
        $escapedCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        
        if (strlen($escapedCode) > 150) {
            $escapedCode = substr($escapedCode, 0, 150) . '...';
        }

        $languageNames = [
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript',
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++',
            'sql' => 'SQL',
            'json' => 'JSON',
            'xml' => 'XML',
            'bash' => 'Bash',
            'markdown' => 'Markdown',
            'plaintext' => LANG_POSTBLOCK_CODE_LANGUAGE_TEXT
        ];

        $languageName = $languageNames[$language] ?? ucfirst($language);

        return '
        <div class="post-block-code-preview card">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary">' . html($languageName) . '</span>
                    <small class="text-muted">' . LANG_POSTBLOCK_CODE_EDITOR_PREVIEW_LABEL . '</small>
                </div>
            </div>
            <div class="card-body">
                <pre class="m-0"><code>' . $escapedCode . '</code></pre>
            </div>
        </div>';
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{code}' => LANG_POSTBLOCK_CODE_SHORTCODE_CODE,
            '{language}' => LANG_POSTBLOCK_CODE_SHORTCODE_LANGUAGE,
            '{language_badge}' => LANG_POSTBLOCK_CODE_SHORTCODE_LANGUAGE_BADGE,
            '{filename}' => LANG_POSTBLOCK_CODE_SHORTCODE_FILENAME,
            '{copy_button}' => LANG_POSTBLOCK_CODE_SHORTCODE_COPY_BUTTON,
            '{custom_class}' => LANG_POSTBLOCK_CODE_SHORTCODE_CUSTOM_CLASS,
            '{line_numbers}' => LANG_POSTBLOCK_CODE_SHORTCODE_LINE_NUMBERS,
            '{theme}' => LANG_POSTBLOCK_CODE_SHORTCODE_THEME
        ]);
    }

    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/controllers/ace.js',
            'templates/default/admin/assets/js/controllers/mode-html.js',
            'templates/default/admin/assets/js/controllers/theme-monokai.js',
            'templates/default/admin/assets/js/blocks/code.js'
        ];
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = LANG_POSTBLOCK_CODE_VALIDATION_CSS_CLASS;
        }

        $allowedThemes = ['default', 'dark', 'material', 'github', 'coy', 'okaidia', 'tomorrow', 'twilight'];
        if (!empty($settings['theme']) && !in_array($settings['theme'], $allowedThemes)) {
            $errors[] = LANG_POSTBLOCK_CODE_VALIDATION_THEME;
        }

        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        $currentSettings = $this->getBlockSettings();
        
        if (isset($_POST['settings']['show_line_numbers'])) {
            $settings['show_line_numbers'] = true;
        } else {
            $settings['show_line_numbers'] = isset($_POST['settings']) ? false : ($settings['show_line_numbers'] ?? $currentSettings['show_line_numbers'] ?? true);
        }
        
        if (isset($_POST['settings']['copy_button'])) {
            $settings['copy_button'] = true;
        } else {
            $settings['copy_button'] = isset($_POST['settings']) ? false : ($settings['copy_button'] ?? $currentSettings['copy_button'] ?? true);
        }
        
        if (isset($_POST['settings']['show_language_badge'])) {
            $settings['show_language_badge'] = true;
        } else {
            $settings['show_language_badge'] = isset($_POST['settings']) ? false : ($settings['show_language_badge'] ?? $currentSettings['show_language_badge'] ?? true);
        }
        
        if (isset($_POST['settings']['theme'])) {
            $settings['theme'] = trim($_POST['settings']['theme']);
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
        
        $settings['show_line_numbers'] = isset($settings['show_line_numbers']) 
            ? filter_var($settings['show_line_numbers'], FILTER_VALIDATE_BOOLEAN) 
            : ($defaults['show_line_numbers'] ?? true);
        
        $settings['copy_button'] = isset($settings['copy_button']) 
            ? filter_var($settings['copy_button'], FILTER_VALIDATE_BOOLEAN) 
            : ($defaults['copy_button'] ?? true);
        
        $settings['show_language_badge'] = isset($settings['show_language_badge']) 
            ? filter_var($settings['show_language_badge'], FILTER_VALIDATE_BOOLEAN) 
            : ($defaults['show_language_badge'] ?? true);
        
        if (!isset($settings['theme']) || !in_array($settings['theme'], ['default', 'dark', 'material', 'github', 'coy', 'okaidia', 'tomorrow', 'twilight'])) {
            $settings['theme'] = $defaults['theme'] ?? 'default';
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
        
        if (isset($_POST['content']['code'])) {
            $content['code'] = $_POST['content']['code'];
        }
        
        if (isset($content['code']) && function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $content['code'] = stripslashes($content['code']);
        }
        
        if (isset($_POST['content']['language'])) {
            $allowedLanguages = [
                'javascript', 'typescript', 'php', 'html', 'css', 'scss', 
                'python', 'java', 'cpp', 'csharp', 'sql', 'json', 'xml', 
                'bash', 'markdown', 'yaml', 'dockerfile', 'nginx', 'plaintext'
            ];
            $content['language'] = in_array($_POST['content']['language'], $allowedLanguages) 
                ? $_POST['content']['language'] 
                : 'plaintext';
        }
        
        if (isset($_POST['content']['filename'])) {
            $content['filename'] = trim($_POST['content']['filename']);
        }
        
        if (!isset($content['code'])) {
            $content['code'] = LANG_POSTBLOCK_CODE_DEFAULT_CODE;
        }
        if (!isset($content['language'])) {
            $content['language'] = 'javascript';
        }
        if (!isset($content['filename'])) {
            $content['filename'] = '';
        }

        return $content;
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<pre[^>]*>\s*<code[^>]*>(.*?)<\/code>\s*<\/pre>/s', $html, $matches)) {
            $code = trim(html_entity_decode(strip_tags($matches[1])));
            if (!empty($code)) {
                return [
                    'code' => $code,
                    'language' => 'plaintext'
                ];
            }
        }
        
        $language = 'plaintext';
        if (preg_match('/class="[^"]*language-([^"\s]+)/', $html, $langMatches)) {
            $language = $langMatches[1];
        }

        $plainText = trim(strip_tags($html));
        if (!empty($plainText)) {
            return [
                'code' => $plainText,
                'language' => $language
            ];
        }

        return null;
    }

    public function canExtractFromHtml(): bool {
        return true;
    }

    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $code = $content['code'] ?? '';
        $language = $content['language'] ?? 'javascript';
        $filename = $content['filename'] ?? '';
        $showLineNumbers = $settings['show_line_numbers'] ?? true;
        $copyButton = $settings['copy_button'] ?? true;
        $theme = $settings['theme'] ?? 'default';
        $customClass = $settings['custom_class'] ?? '';
        $showLanguageBadge = $settings['show_language_badge'] ?? true;
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        $decodedCode = html_entity_decode($code, ENT_QUOTES, 'UTF-8');
        $decodedCode = stripslashes($decodedCode);
        
        $languageNames = [
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript', 
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'scss' => 'SCSS',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++',
            'csharp' => 'C#',
            'sql' => 'SQL',
            'json' => 'JSON',
            'xml' => 'XML',
            'bash' => 'Bash',
            'markdown' => 'Markdown',
            'yaml' => 'YAML',
            'dockerfile' => 'Docker',
            'nginx' => 'Nginx',
            'plaintext' => LANG_POSTBLOCK_CODE_LANGUAGE_TEXT
        ];

        $languageName = $languageNames[$language] ?? ucfirst($language);

        $copyButtonHtml = '';
        if ($copyButton) {
            $copyButtonHtml = '
            <div class="code-actions">
                <button class="btn-copy-code" type="button" title="' . LANG_POSTBLOCK_CODE_COPY_TITLE . '" data-code="' . htmlspecialchars($decodedCode, ENT_QUOTES, 'UTF-8') . '">
                    <div class="btn-copy-content">
                        <span class="btn-copy-text">
                            ' . bloggy_icon('bs', 'clipboard', '12 12', '#fff', 'me-1') . '
                            ' . LANG_POSTBLOCK_CODE_COPY_BTN . '
                        </span>
                        <span class="btn-copy-success">
                            ' . bloggy_icon('bs', 'check-circle-fill', '12 12', '#b4ffc6', 'me-1') . '
                            ' . LANG_POSTBLOCK_CODE_COPIED . '
                        </span>
                    </div>
                </button>
            </div>';
        }
        
        $languageBadgeHtml = '';
        if ($showLanguageBadge) {
            $languageBadgeHtml = '<span class="code-language">' . htmlspecialchars($languageName) . '</span>';
        }

        $lineNumbersClass = $showLineNumbers ? 'line-numbers' : '';
        $filenameHtml = $filename ? '<span class="code-filename">' . htmlspecialchars($filename) . '</span>' : '';
        
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
            '{code}' => htmlspecialchars($decodedCode, ENT_QUOTES, 'UTF-8'),
            '{language}' => $language,
            '{language_badge}' => $languageBadgeHtml,
            '{filename}' => $filenameHtml,
            '{copy_button}' => $copyButtonHtml,
            '{custom_class}' => $finalCustomClass,
            '{line_numbers}' => $lineNumbersClass,
            '{theme}' => $theme,
            '{preset_id}' => $presetId ? htmlspecialchars($presetId) : '',
            '{preset_name}' => $presetName ? htmlspecialchars($presetName) : '',
            '{block_type}' => $this->getSystemName(),
            '{block_name}' => $this->getName()
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $result = str_replace($placeholder, $value, $result);
        }
        
        return $result;
    }

    public function getFrontendJs(): array {
        return [
            '/templates/default/front/assets/postblocks/codeblock/code.js',
        ];
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/codeblock/code.css',
        ];
    }

    public function getSupportedLanguages(): array {
        return [
            'javascript' => ['name' => 'JavaScript', 'extension' => 'js'],
            'typescript' => ['name' => 'TypeScript', 'extension' => 'ts'],
            'php' => ['name' => 'PHP', 'extension' => 'php'],
            'html' => ['name' => 'HTML', 'extension' => 'html'],
            'css' => ['name' => 'CSS', 'extension' => 'css'],
            'python' => ['name' => 'Python', 'extension' => 'py'],
            'java' => ['name' => 'Java', 'extension' => 'java'],
            'sql' => ['name' => 'SQL', 'extension' => 'sql'],
            'json' => ['name' => 'JSON', 'extension' => 'json'],
            'xml' => ['name' => 'XML', 'extension' => 'xml']
        ];
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $result = $decoded;
                if (isset($result['code'])) {
                    $result['code'] = html_entity_decode($result['code'], ENT_QUOTES, 'UTF-8');
                    $result['code'] = stripslashes($result['code']);
                }
                return $result;
            } else {
                return [
                    'code' => html_entity_decode($content, ENT_QUOTES, 'UTF-8'),
                    'language' => 'plaintext',
                    'filename' => ''
                ];
            }
        }
        
        if (!is_array($content)) {
            return ['code' => LANG_POSTBLOCK_CODE_DEFAULT_CODE, 'language' => 'javascript', 'filename' => ''];
        }
        
        if (isset($content['code'])) {
            $content['code'] = html_entity_decode($content['code'], ENT_QUOTES, 'UTF-8');
            $content['code'] = stripslashes($content['code']);
        }
        
        if (!isset($content['language'])) {
            $content['language'] = 'javascript';
        }
        if (!isset($content['filename'])) {
            $content['filename'] = '';
        }
        
        return $content;
    }

}