<?php

class ScrollToTopBlock extends BaseHtmlBlock {

    public function getName(): string {
        return LANG_HTMLBLOCK_SCROLLTOTOP_NAME;
    }

    public function getSystemName(): string {
        return "ScrollToTopBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_SCROLLTOTOP_DESCRIPTION;
    }

    public function getShortDescription(): string {
        return LANG_HTMLBLOCK_SCROLLTOTOP_SHORT_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-arrow-up-circle';
    }

    public function getAuthor(): string {
        return 'BloggyCMS Team';
    }

    public function getVersion(): string {
        return '1.0.0';
    }

    public function getTemplate(): string {
        return 'all';
    }

    public function getSettingsForm($currentSettings = []): string {
        $settings = array_merge($this->getDefaultSettings(), $currentSettings);

        $fieldsets = [];

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_SCROLLTOTOP_FIELDSET_MAIN, [
            'icon' => 'bi bi-gear',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::number('scroll_threshold', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_SCROLL_THRESHOLD,
                    'hint' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_SCROLL_THRESHOLD_HINT,
                    'default' => $settings['scroll_threshold'] ?? 300,
                    'min' => 0,
                    'max' => 1000,
                    'step' => 10,
                    'column' => '6',
                ]),
                \FieldFactory::number('animation_duration', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_ANIMATION_DURATION,
                    'hint' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_ANIMATION_DURATION_HINT,
                    'default' => $settings['animation_duration'] ?? 500,
                    'min' => 100,
                    'max' => 2000,
                    'step' => 50,
                    'column' => '6',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_SCROLLTOTOP_FIELDSET_APPEARANCE, [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('position', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_POSITION,
                    'options' => [
                        'bottom-right' => LANG_HTMLBLOCK_SCROLLTOTOP_POSITION_BOTTOM_RIGHT,
                        'bottom-left' => LANG_HTMLBLOCK_SCROLLTOTOP_POSITION_BOTTOM_LEFT,
                    ],
                    'default' => $settings['position'] ?? 'bottom-right',
                    'column' => '6',
                ]),
                \FieldFactory::number('offset_bottom', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_OFFSET_BOTTOM,
                    'default' => $settings['offset_bottom'] ?? 20,
                    'min' => 0,
                    'max' => 100,
                    'step' => 5,
                    'column' => '6',
                ]),
                \FieldFactory::number('offset_side', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_OFFSET_SIDE,
                    'default' => $settings['offset_side'] ?? 20,
                    'min' => 0,
                    'max' => 100,
                    'step' => 5,
                    'column' => '6',
                ]),
                \FieldFactory::select('size', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_SIZE,
                    'options' => [
                        'sm' => LANG_HTMLBLOCK_SCROLLTOTOP_SIZE_SM,
                        'md' => LANG_HTMLBLOCK_SCROLLTOTOP_SIZE_MD,
                        'lg' => LANG_HTMLBLOCK_SCROLLTOTOP_SIZE_LG,
                    ],
                    'default' => $settings['size'] ?? 'md',
                    'column' => '6',
                ]),
                \FieldFactory::select('shape', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_SHAPE,
                    'options' => [
                        'circle' => LANG_HTMLBLOCK_SCROLLTOTOP_SHAPE_CIRCLE,
                        'rounded' => LANG_HTMLBLOCK_SCROLLTOTOP_SHAPE_ROUNDED,
                    ],
                    'default' => $settings['shape'] ?? 'circle',
                    'column' => '6',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_BACKGROUND_COLOR,
                    'preset' => 'website',
                    'default' => $settings['background_color'] ?? '#2563eb',
                    'column' => '6',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'default' => $settings['text_color'] ?? '#ffffff',
                    'column' => '6',
                ]),
                \FieldFactory::checkbox('show_shadow', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_SHOW_SHADOW,
                    'default' => $settings['show_shadow'] ?? 1,
                    'switch' => true,
                    'column' => '12',
                ]),
                \FieldFactory::icon('custom_icon', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_CUSTOM_ICON,
                    'hint' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_CUSTOM_ICON_HINT,
                    'default' => $settings['custom_icon'] ?? '',
                    'column' => '12',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_SCROLLTOTOP_FIELDSET_EXTRA, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_CSS_CLASS,
                    'default' => $settings['custom_css_class'] ?? '',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => LANG_HTMLBLOCK_SCROLLTOTOP_FIELD_HTML_ID,
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
            'scroll_threshold' => 300,
            'animation_duration' => 500,
            'position' => 'bottom-right',
            'offset_bottom' => 20,
            'offset_side' => 20,
            'size' => 'md',
            'shape' => 'circle',
            'background_color' => '#2563eb',
            'text_color' => '#ffffff',
            'show_shadow' => 1,
            'custom_icon' => '',
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
        $prepared['scroll_threshold'] = (int)($settings['scroll_threshold'] ?? 300);
        $prepared['animation_duration'] = (int)($settings['animation_duration'] ?? 500);
        $prepared['offset_bottom'] = (int)($settings['offset_bottom'] ?? 20);
        $prepared['offset_side'] = (int)($settings['offset_side'] ?? 20);
        $prepared['show_shadow'] = isset($settings['show_shadow']) ? (int)$settings['show_shadow'] : 1;
        $prepared['custom_icon'] = trim($settings['custom_icon'] ?? '');

        return $prepared;
    }

}