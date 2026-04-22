<?php

class FeedbackBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_FEEDBACK_NAME;
    }
    
    public function getSystemName(): string {
        return "FeedbackBlock";
    }
    
    public function getDescription(): string {
        return LANG_HTMLBLOCK_FEEDBACK_DESCRIPTION;
    }
    
    public function getIcon(): string {
        return 'bi bi-chat-left-text';
    }

    public function getAuthor(): string {
        return 'BloggyCMS Team';
    }
    
    public function getVersion(): string {
        return '1.0.0';
    }
    
    public function getTemplate(): string {
        return 'default';
    }
    
    private function getAvailableForms(): array {
        $forms = ['' => LANG_HTMLBLOCK_FEEDBACK_SELECT_FORM];
        try {
            $db = Database::getInstance();
            $formModel = new FormModel($db);
            $activeForms = $formModel->getAllActive();
            foreach ($activeForms as $form) {
                $forms[$form['slug']] = htmlspecialchars($form['name']);
            }
        } catch (Exception $e) {}
        return $forms;
    }
    
    private function getAvailableFormTemplates(): array {
        $templates = ['default' => LANG_HTMLBLOCK_FEEDBACK_DEFAULT_TEMPLATE];
        $systemName = $this->getSystemName();
        $currentTheme = defined('DEFAULT_TEMPLATE') ? DEFAULT_TEMPLATE : 'default';
        
        $searchPaths = [
            ROOT_PATH . "/templates/{$currentTheme}/front/assets/html_blocks/{$systemName}/forms/",
            ROOT_PATH . "/templates/default/front/assets/html_blocks/{$systemName}/forms/",
        ];
        
        foreach ($searchPaths as $basePath) {
            if (!is_dir($basePath)) continue;
            $files = glob($basePath . '*.php');
            foreach ($files as $file) {
                $templateName = pathinfo($file, PATHINFO_FILENAME);
                if ($templateName === 'default') continue;
                
                $content = file_get_contents($file);
                $description = $templateName;
                if (preg_match('/@name\s+(.+)/', $content, $matches)) {
                    $description = trim($matches[1]);
                }
                $templates[$templateName] = $description;
            }
        }
        return $templates;
    }
    
    private function findFormTemplatePath($templateName): ?string {
        $systemName = $this->getSystemName();
        $currentTheme = defined('DEFAULT_TEMPLATE') ? DEFAULT_TEMPLATE : 'default';
        
        $paths = [
            ROOT_PATH . "/templates/{$currentTheme}/front/assets/html_blocks/{$systemName}/forms/{$templateName}.php",
            ROOT_PATH . "/templates/default/front/assets/html_blocks/{$systemName}/forms/{$templateName}.php",
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) return $path;
        }
        return null;
    }
    
    private function renderFormWithTemplate($formSlug, $formTemplate, $formOptions = []): string {
        $templatePath = $this->findFormTemplatePath($formTemplate);
 
        if (!$templatePath) {
            return FormRenderer::render($formSlug, $formOptions);
        }

        $db = Database::getInstance();
        $formModel = new FormModel($db);
        $form = $formModel->getBySlug($formSlug);
        
        if (!$form || $form['status'] !== 'active') {
            return '<div class="alert alert-warning">' . LANG_HTMLBLOCK_FEEDBACK_FORM_NOT_AVAILABLE . '</div>';
        }
        
        $structure = $form['structure'] ?? [];
        $settings = $form['settings'] ?? [];
        $formId = $form['id'];
        $formName = $form['name'];
        $actionUrl = BASE_URL . '/form/' . $formSlug . '/submit';
        
        $options = array_merge([
            'ajax' => $settings['ajax_enabled'] ?? true,
            'show_labels' => $settings['show_labels'] ?? true,
            'show_descriptions' => $settings['show_descriptions'] ?? true,
            'captcha' => $settings['captcha_enabled'] ?? false,
            'csrf_protection' => $settings['csrf_protection'] ?? true,
            'class' => 'feedback-form',
            'submit_class' => 'feedback-submit-btn',
        ], $formOptions);
        
        $csrfToken = '';
        if ($options['csrf_protection']) {
            $csrfToken = FormRenderer::generateCsrfToken($formSlug);
        }
        
        $captchaHtml = '';
        $captchaData = null;
        if ($options['captcha'] && !empty($settings['captcha_enabled'])) {
            if (class_exists('CaptchaHelper')) {
                $captchaData = CaptchaHelper::generate(
                    $settings['captcha_type'] ?? 'math',
                    $settings
                );
                $captchaHtml = CaptchaHelper::render($captchaData, $settings);
            }
        }
        
        extract([
            'form' => $form,
            'formId' => $formId,
            'formSlug' => $formSlug,
            'formName' => $formName,
            'structure' => $structure,
            'settings' => $settings,
            'options' => $options,
            'actionUrl' => $actionUrl,
            'csrfToken' => $csrfToken,
            'captchaHtml' => $captchaHtml,
            'captchaData' => $captchaData,
        ], EXTR_SKIP);
        
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
    
    public function getSettingsForm($currentSettings = []): string {
        $settings = array_merge($this->getDefaultSettings(), $currentSettings);
        $availableForms = $this->getAvailableForms();
        $formTemplates = $this->getAvailableFormTemplates();
        
        $fieldsets = [];
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_FEEDBACK_FIELDSET_FORM, [
            'icon' => 'bi bi-card-list',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('form_slug', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_SELECT_FORM,
                    'options' => $availableForms,
                    'default' => $settings['form_slug'] ?? '',
                    'required' => true,
                    'hint' => LANG_HTMLBLOCK_FEEDBACK_FIELD_SELECT_FORM_HINT,
                    'column' => '12',
                ]),
                \FieldFactory::select('form_template', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_FORM_TEMPLATE,
                    'options' => $formTemplates,
                    'default' => $settings['form_template'] ?? 'default',
                    'hint' => LANG_HTMLBLOCK_FEEDBACK_FIELD_FORM_TEMPLATE_HINT,
                    'column' => '6',
                ]),
                \FieldFactory::checkbox('show_form_title', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_SHOW_FORM_TITLE,
                    'default' => $settings['show_form_title'] ?? 1,
                    'switch' => true,
                    'column' => '6',
                ]),
                \FieldFactory::string('form_title', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_FORM_TITLE,
                    'default' => $settings['form_title'] ?? LANG_HTMLBLOCK_FEEDBACK_DEFAULT_FORM_TITLE,
                    'placeholder' => LANG_HTMLBLOCK_FEEDBACK_FIELD_FORM_TITLE_PLACEHOLDER,
                    'column' => '12',
                    'show' => 'field:show_form_title',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_FEEDBACK_FIELDSET_CONTACT, [
            'icon' => 'bi bi-info-circle',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::checkbox('show_contact_info', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_SHOW_CONTACT_INFO,
                    'default' => $settings['show_contact_info'] ?? 1,
                    'switch' => true,
                    'column' => '12',
                ]),
                \FieldFactory::string('contact_title', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_CONTACT_TITLE,
                    'default' => $settings['contact_title'] ?? LANG_HTMLBLOCK_FEEDBACK_DEFAULT_CONTACT_TITLE,
                    'column' => '12',
                    'show' => 'field:show_contact_info',
                ]),
                \FieldFactory::textarea('contact_description', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_CONTACT_DESCRIPTION,
                    'default' => $settings['contact_description'] ?? '',
                    'rows' => 3,
                    'column' => '12',
                    'show' => 'field:show_contact_info',
                ]),
                \FieldFactory::string('contact_phone', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_CONTACT_PHONE,
                    'default' => $settings['contact_phone'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_FEEDBACK_PHONE_PLACEHOLDER,
                    'column' => '6',
                    'show' => 'field:show_contact_info',
                ]),
                \FieldFactory::string('contact_email', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_CONTACT_EMAIL,
                    'default' => $settings['contact_email'] ?? '',
                    'placeholder' => 'info@example.com',
                    'column' => '6',
                    'show' => 'field:show_contact_info',
                ]),
                \FieldFactory::string('contact_address', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_CONTACT_ADDRESS,
                    'default' => $settings['contact_address'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_FEEDBACK_ADDRESS_PLACEHOLDER,
                    'column' => '12',
                    'show' => 'field:show_contact_info',
                ]),
                \FieldFactory::string('contact_map_url', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_CONTACT_MAP_URL,
                    'default' => $settings['contact_map_url'] ?? '',
                    'placeholder' => 'https://maps.google.com/...',
                    'column' => '12',
                    'show' => 'field:show_contact_info',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_FEEDBACK_FIELDSET_APPEARANCE, [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('layout', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_LAYOUT,
                    'options' => [
                        'form-left' => LANG_HTMLBLOCK_FEEDBACK_LAYOUT_FORM_LEFT,
                        'form-right' => LANG_HTMLBLOCK_FEEDBACK_LAYOUT_FORM_RIGHT,
                    ],
                    'default' => $settings['layout'] ?? 'form-left',
                    'column' => '6',
                ]),
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_FEEDBACK_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_FEEDBACK_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_FEEDBACK_THEME_CUSTOM,
                    ],
                    'default' => $settings['theme'] ?? 'light',
                    'column' => '6',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'default' => '#2563eb',
                    'column' => '12',
                ]),
                \FieldFactory::number('padding_top', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_PADDING_TOP,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                    'column' => '6',
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_PADDING_BOTTOM,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                    'column' => '6',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_FEEDBACK_FIELDSET_EXTRA, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_CSS_CLASS,
                    'default' => $settings['custom_css_class'] ?? '',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => LANG_HTMLBLOCK_FEEDBACK_FIELD_HTML_ID,
                    'default' => $settings['custom_id'] ?? '',
                ]),
            ]
        ]);
        
        ob_start();
        ?>
        <div class="row g-4">
            <?php foreach ($fieldsets as $fieldset): ?>
            <div class="col-12"><?= $fieldset->render($settings) ?></div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function getDefaultSettings(): array {
        return [
            'form_slug' => '',
            'form_template' => 'default',
            'show_form_title' => 1,
            'form_title' => LANG_HTMLBLOCK_FEEDBACK_DEFAULT_FORM_TITLE,
            'show_contact_info' => 1,
            'contact_title' => LANG_HTMLBLOCK_FEEDBACK_DEFAULT_CONTACT_TITLE,
            'contact_description' => '',
            'contact_phone' => '',
            'contact_email' => '',
            'contact_address' => '',
            'contact_map_url' => '',
            'layout' => 'form-left',
            'theme' => 'light',
            'accent_color' => '#2563eb',
            'padding_top' => 80,
            'padding_bottom' => 80,
        ];
    }
    
    public function validateSettings($settings): array {
        $errors = [];
        if (empty($settings['form_slug'])) {
            $errors[] = LANG_HTMLBLOCK_FEEDBACK_VALIDATION_FORM_REQUIRED;
        }
        return [empty($errors), $errors];
    }
    
    public function prepareSettings($settings): array {
        if (!is_array($settings)) return $this->getDefaultSettings();
        
        $prepared = array_merge($this->getDefaultSettings(), $settings);
        
        $prepared['padding_top'] = (int)($settings['padding_top'] ?? 80);
        $prepared['padding_bottom'] = (int)($settings['padding_bottom'] ?? 80);
        $prepared['show_form_title'] = isset($settings['show_form_title']) ? (int)$settings['show_form_title'] : 1;
        $prepared['show_contact_info'] = isset($settings['show_contact_info']) ? (int)$settings['show_contact_info'] : 1;
        
        $textFields = ['form_slug', 'form_template', 'form_title', 'contact_title', 
                      'contact_description', 'contact_phone', 'contact_email', 
                      'contact_address', 'contact_map_url', 'custom_css_class', 'custom_id'];
        foreach ($textFields as $field) {
            $prepared[$field] = trim($settings[$field] ?? '');
        }
        
        return $prepared;
    }
    
    public function processFrontend($settings = [], $templateName = null): string {
        $formSlug = $settings['form_slug'] ?? '';
        $formTemplate = $settings['form_template'] ?? 'default';
        $formExists = false;
        
        if (!empty($formSlug)) {
            try {
                $db = Database::getInstance();
                $formModel = new FormModel($db);
                $form = $formModel->getBySlug($formSlug);
                $formExists = $form && $form['status'] === 'active';
            } catch (Exception $e) {
                $formExists = false;
            }
        }
        
        $formHtml = '';
        if ($formExists) {
            $formHtml = $this->renderFormWithTemplate($formSlug, $formTemplate, [
                'class' => 'feedback-form',
                'ajax' => true,
                'show_labels' => false,
                'show_descriptions' => false,
            ]);
        }
        
        $data = array_merge($settings, [
            'form_exists' => $formExists,
            'form_html' => $formHtml,
            'form_slug' => $formSlug,
        ]);
        
        return parent::processFrontend($data, $templateName);
    }
}