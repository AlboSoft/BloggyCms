<?php

class CookieConsentBlock extends BaseHtmlBlock {

    public function getName(): string {
        return LANG_HTMLBLOCK_COOKIECONSENT_NAME;
    }

    public function getSystemName(): string {
        return "CookieConsentBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_COOKIECONSENT_DESCRIPTION;
    }

    public function getShortDescription(): string {
        return LANG_HTMLBLOCK_COOKIECONSENT_SHORT_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-cookie';
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

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_COOKIECONSENT_FIELDSET_MESSAGE, [
            'icon' => 'bi bi-pencil',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::textarea('message', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_MESSAGE,
                    'default' => $settings['message'] ?? LANG_HTMLBLOCK_COOKIECONSENT_DEFAULT_MESSAGE,
                    'rows' => 4,
                    'column' => '12',
                ]),
                \FieldFactory::string('accept_button_text', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_ACCEPT_BUTTON,
                    'default' => $settings['accept_button_text'] ?? LANG_HTMLBLOCK_COOKIECONSENT_DEFAULT_ACCEPT_BUTTON,
                    'column' => '6',
                ]),
                \FieldFactory::string('decline_button_text', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_DECLINE_BUTTON,
                    'default' => $settings['decline_button_text'] ?? LANG_HTMLBLOCK_COOKIECONSENT_DEFAULT_DECLINE_BUTTON,
                    'column' => '6',
                ]),
                \FieldFactory::string('policy_link_text', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_POLICY_LINK_TEXT,
                    'default' => $settings['policy_link_text'] ?? LANG_HTMLBLOCK_COOKIECONSENT_DEFAULT_POLICY_LINK_TEXT,
                    'column' => '6',
                ]),
                \FieldFactory::string('policy_url', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_POLICY_URL,
                    'default' => $settings['policy_url'] ?? '/privacy',
                    'placeholder' => '/privacy-policy',
                    'column' => '6',
                ]),
                \FieldFactory::checkbox('show_policy_link', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_SHOW_POLICY_LINK,
                    'default' => $settings['show_policy_link'] ?? 1,
                    'switch' => true,
                    'column' => '12',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_COOKIECONSENT_FIELDSET_APPEARANCE, [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('position', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_POSITION,
                    'options' => [
                        'bottom' => LANG_HTMLBLOCK_COOKIECONSENT_POSITION_BOTTOM,
                        'top' => LANG_HTMLBLOCK_COOKIECONSENT_POSITION_TOP,
                    ],
                    'default' => $settings['position'] ?? 'bottom',
                    'column' => '6',
                ]),
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_COOKIECONSENT_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_COOKIECONSENT_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_COOKIECONSENT_THEME_CUSTOM,
                    ],
                    'default' => $settings['theme'] ?? 'light',
                    'column' => '6',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'default' => $settings['accent_color'] ?? '#2563eb',
                    'column' => '12',
                ]),
                \FieldFactory::checkbox('show_shadow', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_SHOW_SHADOW,
                    'default' => $settings['show_shadow'] ?? 1,
                    'switch' => true,
                    'column' => '12',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_COOKIECONSENT_FIELDSET_EXTRA, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('cookie_name', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_COOKIE_NAME,
                    'default' => $settings['cookie_name'] ?? 'cookie_consent',
                    'hint' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_COOKIE_NAME_HINT,
                    'column' => '6',
                ]),
                \FieldFactory::number('cookie_expiry_days', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_COOKIE_EXPIRY,
                    'default' => $settings['cookie_expiry_days'] ?? 365,
                    'min' => 1,
                    'max' => 730,
                    'step' => 1,
                    'column' => '6',
                ]),
                \FieldFactory::checkbox('auto_show', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_AUTO_SHOW,
                    'default' => $settings['auto_show'] ?? 1,
                    'switch' => true,
                    'hint' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_AUTO_SHOW_HINT,
                    'column' => '12',
                ]),
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_CSS_CLASS,
                    'default' => $settings['custom_css_class'] ?? '',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => LANG_HTMLBLOCK_COOKIECONSENT_FIELD_HTML_ID,
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
            'message' => LANG_HTMLBLOCK_COOKIECONSENT_DEFAULT_MESSAGE,
            'accept_button_text' => LANG_HTMLBLOCK_COOKIECONSENT_DEFAULT_ACCEPT_BUTTON,
            'decline_button_text' => LANG_HTMLBLOCK_COOKIECONSENT_DEFAULT_DECLINE_BUTTON,
            'policy_link_text' => LANG_HTMLBLOCK_COOKIECONSENT_DEFAULT_POLICY_LINK_TEXT,
            'policy_url' => '/privacy',
            'show_policy_link' => 1,
            'position' => 'bottom',
            'theme' => 'light',
            'accent_color' => '#2563eb',
            'show_shadow' => 1,
            'cookie_name' => 'cookie_consent',
            'cookie_expiry_days' => 365,
            'auto_show' => 1,
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

        $textFields = ['message', 'accept_button_text', 'decline_button_text', 'policy_link_text', 'policy_url', 'cookie_name', 'custom_css_class', 'custom_id'];
        foreach ($textFields as $field) {
            if (isset($prepared[$field])) {
                $prepared[$field] = trim($prepared[$field]);
            }
        }

        $prepared['show_policy_link'] = isset($settings['show_policy_link']) ? (int)$settings['show_policy_link'] : 1;
        $prepared['show_shadow'] = isset($settings['show_shadow']) ? (int)$settings['show_shadow'] : 1;
        $prepared['auto_show'] = isset($settings['auto_show']) ? (int)$settings['auto_show'] : 1;
        $prepared['cookie_expiry_days'] = (int)($settings['cookie_expiry_days'] ?? 365);

        return $prepared;
    }

    public function processFrontend($settings = [], $templateName = null): string {
        $data = $settings;
        $data['cookie_name'] = $settings['cookie_name'] ?? 'cookie_consent';
        $data['auto_show'] = !empty($settings['auto_show']);
        $data['cookie_expiry_days'] = (int)($settings['cookie_expiry_days'] ?? 365);

        return parent::processFrontend($data, $templateName);
    }
}