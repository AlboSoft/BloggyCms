<?php

class DefaultBreadcrumbsBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_NAME;
    }

    public function getSystemName(): string {
        return "DefaultBreadcrumbsBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_DESCRIPTION;
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
        
        $fieldsets = [];
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELDSET_MAIN, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('show_home', [
                    'title' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_SHOW_HOME,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_current', [
                    'title' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_SHOW_CURRENT,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('hide_on_front', [
                    'title' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_HIDE_ON_FRONT,
                    'default' => 1,
                    'switch' => true,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELDSET_APPEARANCE, [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('separator', [
                    'title' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_SEPARATOR,
                    'options' => [
                        'chevron' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_SEPARATOR_CHEVRON,
                        'slash' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_SEPARATOR_SLASH,
                        'arrow' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_SEPARATOR_ARROW,
                        'dot' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_SEPARATOR_DOT,
                    ],
                    'column' => '6',
                    'default' => 'chevron'
                ]),
                \FieldFactory::select('alignment', [
                    'title' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_ALIGNMENT,
                    'options' => [
                        'left' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_ALIGN_LEFT,
                        'center' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_ALIGN_CENTER,
                        'right' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_ALIGN_RIGHT,
                    ],
                    'column' => '6',
                    'default' => 'left'
                ]),
                \FieldFactory::icon('home_icon', [
                    'title' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_HOME_ICON,
                    'hint' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_HOME_ICON_HINT,
                    'default' => '',
                    'column' => '12'
                ])
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELDSET_EXTRA, [
            'icon' => 'bi bi-three-dots',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('enable_schema', [
                    'title' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_ENABLE_SCHEMA,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_DEFAULTBREADCRUMBS_FIELD_CSS_CLASS,
                    'default' => '',
                    'placeholder' => 'my-breadcrumbs'
                ]),
            ]
        ]);

        ob_start();
        ?>
        <div class="row">
            <?php foreach ($fieldsets as $fieldset) { ?>
            <div class="col-md-12">
                <?= $fieldset->render($settings) ?>
            </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validateSettings($settings): array {
        return [true, []];
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return [];
        }

        return [
            'show_home' => isset($settings['show_home']) ? (int)$settings['show_home'] : 1,
            'show_current' => isset($settings['show_current']) ? (int)$settings['show_current'] : 1,
            'hide_on_front' => isset($settings['hide_on_front']) ? (int)$settings['hide_on_front'] : 1,
            'separator' => $settings['separator'] ?? 'chevron',
            'home_icon' => trim($settings['home_icon'] ?? ''),
            'alignment' => $settings['alignment'] ?? 'left',
            'enable_schema' => isset($settings['enable_schema']) ? (int)$settings['enable_schema'] : 1,
            'custom_css_class' => trim($settings['custom_css_class'] ?? '')
        ];
    }

    public function processFrontend($settings = [], $templateName = null): string {
        $breadcrumbs = null;
        if (class_exists('BreadcrumbsHelper')) {
            $breadcrumbs = BreadcrumbsHelper::getManager();
        }
        
        if (!$breadcrumbs || $breadcrumbs->isEmpty()) {
            return '';
        }
        
        if (!empty($settings['hide_on_front']) && $this->isFrontPage()) {
            return '';
        }
        
        $items = $breadcrumbs->getAll();
        
        if (empty($settings['show_home'])) {
            array_shift($items);
        }
        
        if (empty($settings['show_current'])) {
            array_pop($items);
        }
        
        if (empty($items)) {
            return '';
        }
        
        $data = $settings;
        $data['items'] = $items;
        $data['separator_char'] = $this->getSeparatorChar($settings);
        $data['container_class'] = $this->getContainerClass($settings);
        
        if (!empty($settings['enable_schema'])) {
            $data['schema_markup'] = $this->generateSchemaMarkup($items);
        }
        
        return parent::processFrontend($data, $templateName);
    }
    
    private function getSeparatorChar($settings) {
        $map = [
            'chevron' => '›',
            'slash' => '/',
            'arrow' => '→',
            'dot' => '•',
        ];
        
        return $map[$settings['separator']] ?? '›';
    }
    
    private function getContainerClass($settings) {
        $classes = ['tg-breadcrumbs'];
        
        if (!empty($settings['alignment'])) {
            $classes[] = 'text-' . $settings['alignment'];
        }
        
        if (!empty($settings['custom_css_class'])) {
            $classes[] = $settings['custom_css_class'];
        }
        
        return implode(' ', $classes);
    }
    
    private function generateSchemaMarkup($items) {
        $schemaItems = [];
        
        foreach ($items as $index => $item) {
            if (empty($item['url'])) {
                continue;
            }
            
            $schemaItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['title'],
                'item' => $this->getFullUrl($item['url'])
            ];
        }
        
        if (empty($schemaItems)) {
            return '';
        }
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $schemaItems
        ];
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    private function getFullUrl($path) {
        if (strpos($path, 'http') === 0) {
            return $path;
        }
        
        $base = defined('BASE_URL') ? BASE_URL : '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
    
    private function isFrontPage() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?');
        return $uri === '/' || $uri === '';
    }
}