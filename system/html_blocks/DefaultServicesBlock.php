<?php

class DefaultServicesBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_DEFAULTSERVICES_NAME;
    }

    public function getSystemName(): string {
        return "DefaultServicesBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_DEFAULTSERVICES_DESCRIPTION;
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
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTSERVICES_FIELDSET_HEADER, [
            'icon' => 'bi bi-pencil',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_BADGE,
                    'column' => '12',
                    'default' => $settings['badge'] ?? LANG_HTMLBLOCK_DEFAULTSERVICES_DEFAULT_BADGE,
                    'placeholder' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_BADGE_PLACEHOLDER,
                ]),
                \FieldFactory::string('title', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_TITLE,
                    'column' => '6',
                    'default' => $settings['title'] ?? LANG_HTMLBLOCK_DEFAULTSERVICES_DEFAULT_TITLE,
                    'placeholder' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_TITLE_PLACEHOLDER,
                ]),
                \FieldFactory::textarea('description', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_DESCRIPTION,
                    'column' => '6',
                    'default' => $settings['description'] ?? LANG_HTMLBLOCK_DEFAULTSERVICES_DEFAULT_DESCRIPTION,
                    'rows' => 3,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTSERVICES_FIELDSET_SERVICES, [
            'icon' => 'bi bi-grid-3x3',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('columns', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_COLUMNS,
                    'column' => '12',
                    'options' => [
                        '3' => LANG_HTMLBLOCK_DEFAULTSERVICES_COLUMNS_3,
                        '2' => LANG_HTMLBLOCK_DEFAULTSERVICES_COLUMNS_2,
                        '4' => LANG_HTMLBLOCK_DEFAULTSERVICES_COLUMNS_4,
                    ],
                    'default' => '3',
                ]),
                \FieldFactory::repeater('services', [
                'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_SERVICES,
                'column' => '12',
                'repeater_columns' => 2,
                'hint' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_SERVICES_HINT,
                'min_items' => 1,
                'max_items' => 8,
                'fields' => [
                    [
                        'name' => 'image',
                        'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_SERVICE_IMAGE,
                        'type' => 'blockimage',
                        'field_column' => '12',
                    ],
                    [
                        'name' => 'title',
                        'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_SERVICE_TITLE,
                        'type' => 'string',
                        'field_column' => '12',
                    ],
                    [
                        'name' => 'description',
                        'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_SERVICE_DESCRIPTION,
                        'type' => 'textarea',
                        'field_column' => '12',
                    ],
                    [
                        'name' => 'price',
                        'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_SERVICE_PRICE,
                        'type' => 'string',
                        'field_column' => '12',
                    ],
                ]
            ])
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTSERVICES_FIELDSET_BUTTONS, [
            'icon' => 'bi bi-ui-radios',
            'columns' => '12',
            'fields' => [
                \FieldFactory::repeater('buttons', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_BUTTONS,
                    'hint' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_BUTTONS_HINT,
                    'column' => '12',
                    'repeater_columns' => 2,
                    'fields' => [
                        [
                            'name' => 'text',
                            'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_BUTTON_TEXT,
                            'type' => 'string',
                            'placeholder' => LANG_HTMLBLOCK_DEFAULTSERVICES_BUTTON_TEXT_PLACEHOLDER,
                            'field_column' => '12',
                        ],
                        [
                            'name' => 'url',
                            'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_BUTTON_URL,
                            'type' => 'string',
                            'placeholder' => '/contact',
                            'default' => '#',
                            'field_column' => '12',
                        ],
                    ]
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTSERVICES_FIELDSET_COLORS, [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_DEFAULTSERVICES_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_DEFAULTSERVICES_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_DEFAULTSERVICES_THEME_CUSTOM,
                    ],
                    'column' => '12',
                    'default' => 'light',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'column' => '6',
                    'default' => '#2563eb',
                ]),
                \FieldFactory::color('card_background', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_CARD_BACKGROUND,
                    'preset' => 'basic',
                    'column' => '6',
                    'default' => $settings['card_background'] ?? '',
                    'hint' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_CARD_BACKGROUND_HINT,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTSERVICES_FIELDSET_PADDING, [
            'icon' => 'bi bi-arrows-expand',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('align', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_ALIGN,
                    'options' => [
                        'left' => LANG_HTMLBLOCK_DEFAULTSERVICES_ALIGN_LEFT,
                        'center' => LANG_HTMLBLOCK_DEFAULTSERVICES_ALIGN_CENTER,
                    ],
                    'column' => '12',
                    'default' => 'center',
                ]),
                \FieldFactory::number('padding_top', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_PADDING_TOP,
                    'default' => 80,
                    'max' => 200,
                    'column' => '6'
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => LANG_HTMLBLOCK_DEFAULTSERVICES_FIELD_PADDING_BOTTOM,
                    'default' => 80,
                    'max' => 200,
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

        if (isset($settings['services']) && is_array($settings['services'])) {
            $repeaterName = 'services';
            $currentItems = $settings['services'];
            
            $updates = BlockImageHelper::handleRepeaterUploads(
                $repeaterName,
                $this->getSystemName(),
                $currentItems
            );
            
            $settings['services'] = BlockImageHelper::applyRepeaterUpdates($currentItems, $updates);

            foreach ($settings['services'] as &$item) {
                $textFields = ['title', 'description', 'price'];
                foreach ($textFields as $field) {
                    if (isset($item[$field])) {
                        $item[$field] = trim($item[$field]);
                    }
                }
            }
        }

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