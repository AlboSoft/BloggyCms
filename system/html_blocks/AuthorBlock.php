<?php

class AuthorBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_AUTHOR_NAME;
    }

    public function getSystemName(): string {
        return "AuthorBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_AUTHOR_DESCRIPTION;
    }

    public function getShortDescription(): string {
        return LANG_HTMLBLOCK_AUTHOR_SHORT_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-person-circle';
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

    public function getSettingsForm($currentSettings = []): string {
        $settings = array_merge($this->getDefaultSettings(), $currentSettings);
        
        $fieldsets = [];
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_AUTHOR_FIELDSET_MAIN, [
            'icon' => 'bi bi-person',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::blockImage('avatar', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_AVATAR,
                    'hint' => LANG_HTMLBLOCK_AUTHOR_FIELD_AVATAR_HINT,
                    'upload_path' => 'uploads/images/html_blocks/' . $this->getSystemName() . '/',
                    'preview_size' => '100px',
                    'column' => '12',
                ]),
                \FieldFactory::string('name', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_NAME,
                    'default' => $settings['name'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_AUTHOR_FIELD_NAME_PLACEHOLDER,
                    'column' => '6',
                ]),
                \FieldFactory::string('role', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_ROLE,
                    'default' => $settings['role'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_AUTHOR_FIELD_ROLE_PLACEHOLDER,
                    'column' => '6',
                ]),
                \FieldFactory::textarea('description', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_DESCRIPTION,
                    'default' => $settings['description'] ?? '',
                    'rows' => 4,
                    'placeholder' => LANG_HTMLBLOCK_AUTHOR_FIELD_DESCRIPTION_PLACEHOLDER,
                    'column' => '12',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_AUTHOR_FIELDSET_SOCIAL, [
            'icon' => 'bi bi-share',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::repeater('social_links', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_SOCIAL_LINKS,
                    'hint' => LANG_HTMLBLOCK_AUTHOR_FIELD_SOCIAL_LINKS_HINT,
                    'column' => '12',
                    'repeater_columns' => 2,
                    'min_items' => 0,
                    'max_items' => 8,
                    'fields' => [
                        [
                            'name' => 'network',
                            'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_NETWORK,
                            'type' => 'select',
                            'options' => [
                                'telegram' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_TELEGRAM,
                                'vk' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_VK,
                                'youtube' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_YOUTUBE,
                                'github' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_GITHUB,
                                'twitter' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_TWITTER,
                                'instagram' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_INSTAGRAM,
                                'facebook' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_FACEBOOK,
                                'linkedin' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_LINKEDIN,
                                'odnoklassniki' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_OK,
                                'behance' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_BEHANCE,
                                'reddit' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_REDDIT,
                                'discord' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_DISCORD,
                                'tiktok' => LANG_HTMLBLOCK_AUTHOR_SOCIAL_TIKTOK,
                            ],
                            'default' => 'telegram',
                            'field_column' => '6',
                        ],
                        [
                            'name' => 'url',
                            'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_URL,
                            'type' => 'string',
                            'placeholder' => 'https://...',
                            'field_column' => '6',
                        ],
                    ],
                    'default' => $settings['social_links'] ?? [],
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_AUTHOR_FIELDSET_CONTACTS, [
            'icon' => 'bi bi-envelope',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::string('email', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_EMAIL,
                    'default' => $settings['email'] ?? '',
                    'placeholder' => 'ivan@example.com',
                    'column' => '6',
                ]),
                \FieldFactory::string('phone', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_PHONE,
                    'default' => $settings['phone'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_AUTHOR_FIELD_PHONE_PLACEHOLDER,
                    'column' => '6',
                ]),
                \FieldFactory::string('website', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_WEBSITE,
                    'default' => $settings['website'] ?? '',
                    'placeholder' => 'https://example.com',
                    'column' => '12',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_AUTHOR_FIELDSET_BUTTON, [
            'icon' => 'bi bi-ui-radios',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::checkbox('show_button', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_SHOW_BUTTON,
                    'default' => $settings['show_button'] ?? 0,
                    'switch' => true,
                    'column' => '12',
                ]),
                \FieldFactory::string('button_text', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_BUTTON_TEXT,
                    'default' => $settings['button_text'] ?? LANG_HTMLBLOCK_AUTHOR_DEFAULT_BUTTON_TEXT,
                    'placeholder' => LANG_HTMLBLOCK_AUTHOR_FIELD_BUTTON_TEXT_PLACEHOLDER,
                    'column' => '6',
                    'show' => 'field:show_button',
                ]),
                \FieldFactory::string('button_url', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_BUTTON_URL,
                    'default' => $settings['button_url'] ?? '/contact',
                    'placeholder' => '/contact',
                    'column' => '6',
                    'show' => 'field:show_button',
                ]),
                \FieldFactory::select('button_target', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_BUTTON_TARGET,
                    'options' => [
                        '_self' => LANG_HTMLBLOCK_AUTHOR_BUTTON_TARGET_SELF,
                        '_blank' => LANG_HTMLBLOCK_AUTHOR_BUTTON_TARGET_BLANK,
                    ],
                    'default' => $settings['button_target'] ?? '_self',
                    'column' => '12',
                    'show' => 'field:show_button',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_AUTHOR_FIELDSET_APPEARANCE, [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('avatar_style', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_AVATAR_STYLE,
                    'options' => [
                        'circle' => LANG_HTMLBLOCK_AUTHOR_AVATAR_STYLE_CIRCLE,
                        'square' => LANG_HTMLBLOCK_AUTHOR_AVATAR_STYLE_SQUARE,
                        'rounded' => LANG_HTMLBLOCK_AUTHOR_AVATAR_STYLE_ROUNDED,
                    ],
                    'default' => $settings['avatar_style'] ?? 'circle',
                    'column' => '6',
                ]),
                \FieldFactory::select('align', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_ALIGN,
                    'options' => [
                        'left' => LANG_HTMLBLOCK_AUTHOR_ALIGN_LEFT,
                        'center' => LANG_HTMLBLOCK_AUTHOR_ALIGN_CENTER,
                    ],
                    'default' => $settings['align'] ?? 'center',
                    'column' => '6',
                ]),
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_AUTHOR_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_AUTHOR_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_AUTHOR_THEME_CUSTOM,
                    ],
                    'default' => $settings['theme'] ?? 'light',
                    'column' => '12',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'default' => '#2563eb',
                    'column' => '12',
                ]),
                \FieldFactory::checkbox('show_shadow', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_SHOW_SHADOW,
                    'default' => $settings['show_shadow'] ?? 1,
                    'switch' => true,
                    'column' => '12',
                ]),
                \FieldFactory::number('padding_top', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_PADDING_TOP,
                    'default' => $settings['padding_top'] ?? 40,
                    'min' => 0,
                    'max' => 100,
                    'step' => 10,
                    'column' => '6',
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_PADDING_BOTTOM,
                    'default' => $settings['padding_bottom'] ?? 40,
                    'min' => 0,
                    'max' => 100,
                    'step' => 10,
                    'column' => '6',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_AUTHOR_FIELDSET_EXTRA, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_CSS_CLASS,
                    'default' => $settings['custom_css_class'] ?? '',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => LANG_HTMLBLOCK_AUTHOR_FIELD_HTML_ID,
                    'default' => $settings['custom_id'] ?? '',
                ]),
            ]
        ]);
        
        ob_start();
        ?>
        <div class="row g-4">
            <?php foreach ($fieldsets as $fieldset) { ?>
            <div class="col-12"><?= $fieldset->render($settings) ?></div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function getDefaultSettings(): array {
        return [
            'avatar' => '',
            'name' => '',
            'role' => '',
            'description' => '',
            'social_links' => [],
            'email' => '',
            'phone' => '',
            'website' => '',
            'show_button' => 0,
            'button_text' => LANG_HTMLBLOCK_AUTHOR_DEFAULT_BUTTON_TEXT,
            'button_url' => '/contact',
            'button_target' => '_self',
            'avatar_style' => 'circle',
            'align' => 'center',
            'theme' => 'light',
            'accent_color' => '#2563eb',
            'show_shadow' => 1,
            'padding_top' => 40,
            'padding_bottom' => 40,
        ];
    }
    
    public function validateSettings($settings): array {
        return [true, []];
    }
    
    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return $this->getDefaultSettings();
        }
        
        $prepared = array_merge($this->getDefaultSettings(), $settings);
        
        $uploadResult = BlockImageHelper::handleUpload('avatar', $this->getSystemName(), $prepared['avatar'] ?? '');
        if ($uploadResult['success']) {
            $prepared['avatar'] = $uploadResult['value'];
        }
        $prepared['avatar'] = BlockImageHelper::handleDelete('avatar', $prepared['avatar'] ?? '');
        unset($prepared['avatar_file'], $prepared['remove_avatar']);
        
        $textFields = ['name', 'role', 'description', 'email', 'phone', 'website', 
                       'button_text', 'button_url', 'custom_css_class', 'custom_id'];
        foreach ($textFields as $field) {
            if (isset($prepared[$field])) {
                $prepared[$field] = trim($prepared[$field]);
            }
        }
        
        if (isset($prepared['social_links']) && is_array($prepared['social_links'])) {
            $filteredLinks = [];
            foreach ($prepared['social_links'] as $link) {
                if (!empty(trim($link['url'] ?? ''))) {
                    $filteredLinks[] = [
                        'network' => $link['network'] ?? 'telegram',
                        'url' => trim($link['url']),
                    ];
                }
            }
            $prepared['social_links'] = $filteredLinks;
        }
        
        $prepared['show_button'] = isset($settings['show_button']) ? (int)$settings['show_button'] : 0;
        $prepared['show_shadow'] = isset($settings['show_shadow']) ? (int)$settings['show_shadow'] : 1;
        $prepared['padding_top'] = (int)($settings['padding_top'] ?? 40);
        $prepared['padding_bottom'] = (int)($settings['padding_bottom'] ?? 40);
        
        return $prepared;
    }
    
    public function getAvatarUrl($settings) {
        if (!empty($settings['avatar'])) {
            return BlockImageHelper::getImageUrl($settings['avatar']);
        }
        return '';
    }
    
    public function processFrontend($settings = [], $templateName = null): string {
        $this->avatarUrl = $this->getAvatarUrl($settings);
        $this->socialLinks = $settings['social_links'] ?? [];
        return parent::processFrontend($settings, $templateName);
    }
}