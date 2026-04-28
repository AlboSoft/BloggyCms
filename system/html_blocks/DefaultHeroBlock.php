<?php

class DefaultHeroBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_DEFAULTHERO_NAME;
    }

    public function getSystemName(): string {
        return "DefaultHeroBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_DEFAULTHERO_DESCRIPTION;
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
        
        $settings = array_merge([], $currentSettings);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTHERO_FIELDSET_CONTENT, [
            'icon' => 'bi bi-pencil',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_BADGE,
                    'default' => $settings['badge'] ?? LANG_HTMLBLOCK_DEFAULTHERO_DEFAULT_BADGE,
                    'column' => '6',
                    'placeholder' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_BADGE_PLACEHOLDER
                ]),
                \FieldFactory::string('title', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_TITLE,
                    'default' => $settings['title'] ?? LANG_HTMLBLOCK_DEFAULTHERO_DEFAULT_TITLE,
                    'column' => '6',
                    'placeholder' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_TITLE_PLACEHOLDER
                ]),
                \FieldFactory::textarea('description', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_DESCRIPTION,
                    'default' => $settings['description'] ?? LANG_HTMLBLOCK_DEFAULTHERO_DEFAULT_DESCRIPTION,
                    'rows' => 4,
                    'column' => '12'
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTHERO_FIELDSET_BUTTONS, [
            'icon' => 'bi bi-ui-radios',
            'columns' => '12',
            'fields' => [
                \FieldFactory::repeater('buttons', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_BUTTONS,
                    'column' => '12',
                    'repeater_columns' => 2,
                    'hint' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_BUTTONS_HINT,
                    'min_items' => 0,
                    'max_items' => 3,
                    'fields' => [
                        [
                            'name' => 'text',
                            'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_BUTTON_TEXT,
                            'type' => 'string',
                            'placeholder' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_BUTTON_TEXT_PLACEHOLDER,
                            'default' => LANG_HTMLBLOCK_DEFAULTHERO_DEFAULT_BUTTON_TEXT
                        ],
                        [
                            'name' => 'url',
                            'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_BUTTON_URL,
                            'type' => 'string',
                            'placeholder' => '/posts',
                            'default' => '#'
                        ],
                    ]
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTHERO_FIELDSET_IMAGE, [
            'icon' => 'bi bi-image',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::blockImage('image', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_IMAGE,
                    'hint' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_IMAGE_HINT,
                    'upload_path' => 'uploads/images/html_blocks/hero/',
                    'preview_size' => '100px',
                ]),
                \FieldFactory::select('image_style', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_IMAGE_STYLE,
                    'options' => [
                        'circle' => LANG_HTMLBLOCK_DEFAULTHERO_IMAGE_STYLE_CIRCLE,
                        'square' => LANG_HTMLBLOCK_DEFAULTHERO_IMAGE_STYLE_SQUARE,
                        'rounded' => LANG_HTMLBLOCK_DEFAULTHERO_IMAGE_STYLE_ROUNDED,
                    ],
                    'column' => '6',
                    'default' => 'circle',
                ]),
                \FieldFactory::select('image_position', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_IMAGE_POSITION,
                    'options' => [
                        'right' => LANG_HTMLBLOCK_DEFAULTHERO_IMAGE_POSITION_RIGHT,
                        'left' => LANG_HTMLBLOCK_DEFAULTHERO_IMAGE_POSITION_LEFT,
                    ],
                    'column' => '6',
                    'default' => 'right'
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTHERO_FIELDSET_COLORS, [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_DEFAULTHERO_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_DEFAULTHERO_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_DEFAULTHERO_THEME_CUSTOM,
                    ],
                    'column' => '12',
                    'default' => 'light'
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom'
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom'
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'column' => '12',
                    'default' => '#2563eb'
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTHERO_FIELDSET_PADDING, [
            'icon' => 'bi bi-arrows-expand',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('align', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_ALIGN,
                    'options' => [
                        'left' => LANG_HTMLBLOCK_DEFAULTHERO_ALIGN_LEFT,
                        'center' => LANG_HTMLBLOCK_DEFAULTHERO_ALIGN_CENTER,
                        'right' => LANG_HTMLBLOCK_DEFAULTHERO_ALIGN_RIGHT,
                    ],
                    'column' => '6',
                    'default' => 'left'
                ]),
                \FieldFactory::number('padding_top', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_PADDING_TOP,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                    'column' => '6'
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => LANG_HTMLBLOCK_DEFAULTHERO_FIELD_PADDING_BOTTOM,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                    'column' => '6'
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

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return [];
        }

        if (isset($settings['remove_image']) && $settings['remove_image'] == 1) {
            $settings['image'] = '';
        } else {
            $uploadResult = BlockImageHelper::handleUpload('image', 'hero', $settings['image'] ?? '');
            if ($uploadResult['success']) {
                $settings['image'] = $uploadResult['value'];
            }
        }
        unset($settings['image_file'], $settings['remove_image']);

        if (isset($settings['buttons']) && is_array($settings['buttons'])) {
            $filteredButtons = [];
            foreach ($settings['buttons'] as $button) {
                if (!empty(trim($button['text'] ?? ''))) {
                    $filteredButtons[] = [
                        'text' => trim($button['text']),
                        'url' => trim($button['url'] ?? '#'),
                    ];
                }
            }
            $settings['buttons'] = $filteredButtons;
        }

        return $settings;
    }
}