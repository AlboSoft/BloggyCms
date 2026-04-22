<?php

class DefaultHeaderBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_DEFAULTHEADER_NAME;
    }

    public function getSystemName(): string {
        return "DefaultHeaderBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_DEFAULTHEADER_DESCRIPTION;
    }

    public function getAuthor(): string {
        return 'BloggyCMS Team';
    }

    public function getVersion(): string {
        return '2.0.0';
    }

    public function getTemplate(): string {
        return 'default';
    }

    public function getSettingsForm($currentSettings = []): string {
        $allMenus = MenuRenderer::getAllMenusForSelect();
        
        $settings = array_merge([
            'theme' => 'dark',
            'logo_path' => '',
            'logo_alt' => LANG_HTMLBLOCK_DEFAULTHEADER_DEFAULT_LOGO_ALT,
            'site_name' => SettingsHelper::get('general', 'site_name', 'BloggyCMS'),
            'logo_link' => '/',
            'show_site_name' => 1,
            'main_menu_id' => '',
            'profile_menu_id' => '',
            'show_search' => 1,
            'search_placeholder' => LANG_HTMLBLOCK_DEFAULTHEADER_DEFAULT_SEARCH_PLACEHOLDER,
            'search_page' => '/search',
            'sticky_header' => 1,
            'show_shadow' => 1,
            'container_type' => 'container',
            'header_height' => 'md',
            'mobile_breakpoint' => 992,
        ], $currentSettings);
        
        $fieldsets = [
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTHEADER_FIELDSET_THEME, [
                'icon' => 'bi bi-palette',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::select('theme', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_THEME,
                        'options' => [
                            'dark' => LANG_HTMLBLOCK_DEFAULTHEADER_THEME_DARK,
                            'light' => LANG_HTMLBLOCK_DEFAULTHEADER_THEME_LIGHT
                        ],
                        'default' => $settings['theme'],
                        'column' => '12',
                        'hint' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_THEME_HINT
                    ])
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTHEADER_FIELDSET_BRANDING, [
                'icon' => 'bi bi-brush',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::blockImage('logo_path', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_LOGO,
                        'hint' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_LOGO_HINT,
                        'default' => $settings['logo_path'],
                        'upload_path' => 'uploads/images/html_blocks/' . $this->getSystemName() . '/',
                        'preview_size' => '80px'
                    ]),
                    \FieldFactory::string('logo_alt', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_LOGO_ALT,
                        'default' => $settings['logo_alt'],
                        'column' => '6',
                        'placeholder' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_LOGO_ALT_PLACEHOLDER
                    ]),
                    \FieldFactory::string('logo_link', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_LOGO_LINK,
                        'default' => $settings['logo_link'],
                        'placeholder' => '/',
                        'column' => '6',
                        'hint' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_LOGO_LINK_HINT
                    ]),
                    \FieldFactory::checkbox('show_site_name', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SHOW_SITE_NAME,
                        'default' => $settings['show_site_name'],
                        'switch' => true,
                        'column' => '12',
                        'hint' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SHOW_SITE_NAME_HINT
                    ]),
                    \FieldFactory::string('site_name', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SITE_NAME,
                        'default' => $settings['site_name'],
                        'placeholder' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SITE_NAME_PLACEHOLDER,
                        'column' => '12',
                        'show' => 'field:show_site_name'
                    ])
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTHEADER_FIELDSET_NAVIGATION, [
                'icon' => 'bi bi-menu-button-wide',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::select('main_menu_id', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_MAIN_MENU,
                        'options' => ['' => LANG_HTMLBLOCK_DEFAULTHEADER_OPTION_SELECT_MENU] + $allMenus,
                        'default' => $settings['main_menu_id'],
                        'required' => true,
                        'hint' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_MAIN_MENU_HINT
                    ]),
                    \FieldFactory::select('profile_menu_id', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_PROFILE_MENU,
                        'options' => ['' => LANG_HTMLBLOCK_DEFAULTHEADER_OPTION_NOT_SHOW] + $allMenus,
                        'default' => $settings['profile_menu_id'],
                        'hint' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_PROFILE_MENU_HINT
                    ])
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTHEADER_FIELDSET_SEARCH, [
                'icon' => 'bi bi-search',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::checkbox('show_search', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SHOW_SEARCH,
                        'default' => $settings['show_search'],
                        'switch' => true
                    ]),
                    \FieldFactory::string('search_placeholder', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SEARCH_PLACEHOLDER,
                        'default' => $settings['search_placeholder'],
                        'placeholder' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SEARCH_PLACEHOLDER_PLACEHOLDER,
                        'column' => '6',
                        'show' => 'field:show_search'
                    ]),
                    \FieldFactory::select('search_page', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SEARCH_PAGE,
                        'options' => [
                            '/search' => LANG_HTMLBLOCK_DEFAULTHEADER_SEARCH_PAGE_STANDARD,
                            '/search/posts' => LANG_HTMLBLOCK_DEFAULTHEADER_SEARCH_PAGE_POSTS,
                            '/search/users' => LANG_HTMLBLOCK_DEFAULTHEADER_SEARCH_PAGE_USERS
                        ],
                        'default' => $settings['search_page'],
                        'column' => '6',
                        'show' => 'field:show_search'
                    ])
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTHEADER_FIELDSET_APPEARANCE, [
                'icon' => 'bi bi-sliders',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::checkbox('sticky_header', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_STICKY_HEADER,
                        'default' => $settings['sticky_header'],
                        'switch' => true,
                        'column' => '6'
                    ]),
                    \FieldFactory::checkbox('show_shadow', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_SHOW_SHADOW,
                        'default' => $settings['show_shadow'],
                        'switch' => true,
                        'column' => '6'
                    ]),
                    \FieldFactory::select('container_type', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_CONTAINER_TYPE,
                        'options' => [
                            'container' => LANG_HTMLBLOCK_DEFAULTHEADER_CONTAINER_FIXED,
                            'container-fluid' => LANG_HTMLBLOCK_DEFAULTHEADER_CONTAINER_FLUID
                        ],
                        'column' => '6',
                        'default' => $settings['container_type']
                    ]),
                    \FieldFactory::select('header_height', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_HEADER_HEIGHT,
                        'options' => [
                            'sm' => LANG_HTMLBLOCK_DEFAULTHEADER_HEIGHT_SM,
                            'md' => LANG_HTMLBLOCK_DEFAULTHEADER_HEIGHT_MD,
                            'lg' => LANG_HTMLBLOCK_DEFAULTHEADER_HEIGHT_LG
                        ],
                        'column' => '6',
                        'default' => $settings['header_height']
                    ]),
                    \FieldFactory::number('mobile_breakpoint', [
                        'title' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_MOBILE_BREAKPOINT,
                        'default' => $settings['mobile_breakpoint'],
                        'min' => 576,
                        'max' => 1200,
                        'column' => '6',
                        'hint' => LANG_HTMLBLOCK_DEFAULTHEADER_FIELD_MOBILE_BREAKPOINT_HINT
                    ])
                ]
            ])
        ];
        
        ob_start();
        ?>
        <div class="row">
            <?php foreach ($fieldsets as $fieldset): ?>
            <div class="col-md-12"><?= $fieldset->render($settings) ?></div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validateSettings($settings): array {
        if (!is_array($settings)) {
            return [false, [LANG_HTMLBLOCK_DEFAULTHEADER_VALIDATION_ERROR]];
        }
        
        $errors = [];
        if (empty($settings['main_menu_id'])) {
            $errors[] = LANG_HTMLBLOCK_DEFAULTHEADER_VALIDATION_MAIN_MENU_REQUIRED;
        }
        
        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return [];
        }
        
        $uploadResult = BlockImageHelper::handleUpload('logo_path', $this->getSystemName(), $settings['logo_path'] ?? '');
        if ($uploadResult['success']) {
            $settings['logo_path'] = $uploadResult['value'];
        }
        $settings['logo_path'] = BlockImageHelper::handleDelete('logo_path', $settings['logo_path'] ?? '');
        unset($settings['logo_path_file'], $settings['remove_logo_path']);
        
        $settings['theme'] = in_array($settings['theme'] ?? '', ['dark', 'light']) ? $settings['theme'] : 'dark';
        $settings['logo_alt'] = trim($settings['logo_alt'] ?? LANG_HTMLBLOCK_DEFAULTHEADER_DEFAULT_LOGO_ALT);
        $settings['site_name'] = trim($settings['site_name'] ?? SettingsHelper::get('general', 'site_name', 'BloggyCMS'));
        $settings['logo_link'] = trim($settings['logo_link'] ?? '/');
        $settings['show_site_name'] = isset($settings['show_site_name']) ? (int)$settings['show_site_name'] : 1;
        $settings['main_menu_id'] = $settings['main_menu_id'] ?? '';
        $settings['profile_menu_id'] = $settings['profile_menu_id'] ?? '';
        $settings['show_search'] = isset($settings['show_search']) ? (int)$settings['show_search'] : 1;
        $settings['search_placeholder'] = trim($settings['search_placeholder'] ?? LANG_HTMLBLOCK_DEFAULTHEADER_DEFAULT_SEARCH_PLACEHOLDER);
        $settings['search_page'] = $settings['search_page'] ?? '/search';
        $settings['sticky_header'] = isset($settings['sticky_header']) ? (int)$settings['sticky_header'] : 1;
        $settings['show_shadow'] = isset($settings['show_shadow']) ? (int)$settings['show_shadow'] : 1;
        $settings['container_type'] = $settings['container_type'] ?? 'container';
        $settings['header_height'] = in_array($settings['header_height'] ?? '', ['sm', 'md', 'lg']) ? $settings['header_height'] : 'md';
        $settings['mobile_breakpoint'] = (int)($settings['mobile_breakpoint'] ?? 992);
        
        return $settings;
    }
}