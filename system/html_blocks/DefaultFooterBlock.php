<?php
class DefaultFooterBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_DEFAULTFOOTER_NAME;
    }

    public function getSystemName(): string {
        return "DefaultFooterBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_DEFAULTFOOTER_DESCRIPTION;
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

    public $categories = [];
    public $recentPosts = [];
    public $recentTags = [];

    public function getSettingsForm($currentSettings = []): string {
        $allMenus = MenuRenderer::getAllMenusForSelect();
        $settings = array_merge([], $currentSettings);
        
        $fieldsets = [
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTFOOTER_FIELDSET_BRANDING, [
                'icon' => 'bi bi-brush',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::blockImage('logo_path', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_LOGO,
                        'hint' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_LOGO_HINT,
                        'default' => $settings['logo_path'] ?? '',
                        'upload_path' => 'uploads/images/html_blocks/' . $this->getSystemName() . '/',
                        'preview_size' => '80px',
                        'column' => '12'
                    ]),
                    \FieldFactory::string('logo_alt', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_LOGO_ALT,
                        'default' => $settings['logo_alt'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_LOGO_ALT,
                        'column' => '6'
                    ]),
                    \FieldFactory::string('site_name', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SITE_NAME,
                        'default' => $settings['site_name'] ?? SettingsHelper::get('general', 'site_name', 'BloggyCMS'),
                        'column' => '6'
                    ]),
                    \FieldFactory::textarea('site_description', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SITE_DESCRIPTION,
                        'default' => $settings['site_description'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_SITE_DESCRIPTION,
                        'rows' => 2,
                        'hint' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SITE_DESCRIPTION_HINT,
                        'column' => '12'
                    ]),
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTFOOTER_FIELDSET_NAVIGATION, [
                'icon' => 'bi bi-menu-button-wide',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::select('footer_menu_1', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_MAIN_MENU,
                        'options' => ['' => LANG_HTMLBLOCK_DEFAULTFOOTER_OPTION_NOT_SHOW] + $allMenus,
                        'hint' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_MAIN_MENU_HINT,
                        'column' => '6'
                    ]),
                    \FieldFactory::select('footer_menu_2', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_EXTRA_MENU,
                        'options' => ['' => LANG_HTMLBLOCK_DEFAULTFOOTER_OPTION_NOT_SHOW] + $allMenus,
                        'hint' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_EXTRA_MENU_HINT,
                        'column' => '6'
                    ]),
                    \FieldFactory::string('menu_1_title', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_MENU_1_TITLE,
                        'default' => $settings['menu_1_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_MENU_1_TITLE,
                        'column' => '6',
                        'placeholder' => LANG_HTMLBLOCK_DEFAULTFOOTER_PLACEHOLDER_MENU
                    ]),
                    \FieldFactory::string('menu_2_title', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_MENU_2_TITLE,
                        'default' => $settings['menu_2_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_MENU_2_TITLE,
                        'column' => '6',
                        'placeholder' => LANG_HTMLBLOCK_DEFAULTFOOTER_PLACEHOLDER_ABOUT
                    ]),
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTFOOTER_FIELDSET_WIDGETS, [
                'icon' => 'bi bi-grid-3x3-gap',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::checkbox('show_recent_posts', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SHOW_RECENT_POSTS,
                        'default' => $settings['show_recent_posts'] ?? 0,
                        'switch' => true,
                        'column' => '12'
                    ]),
                    \FieldFactory::string('recent_posts_title', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_RECENT_POSTS_TITLE,
                        'default' => $settings['recent_posts_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_RECENT_POSTS_TITLE,
                        'column' => '6',
                        'show' => 'field:show_recent_posts'
                    ]),
                    \FieldFactory::number('recent_posts_count', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_RECENT_POSTS_COUNT,
                        'default' => $settings['recent_posts_count'] ?? 3,
                        'min' => 1,
                        'max' => 10,
                        'column' => '6',
                        'show' => 'field:show_recent_posts'
                    ]),
                    \FieldFactory::checkbox('show_recent_tags', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SHOW_RECENT_TAGS,
                        'default' => $settings['show_recent_tags'] ?? 0,
                        'switch' => true,
                        'column' => '12'
                    ]),
                    \FieldFactory::string('recent_tags_title', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_RECENT_TAGS_TITLE,
                        'default' => $settings['recent_tags_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_RECENT_TAGS_TITLE,
                        'column' => '6',
                        'show' => 'field:show_recent_tags'
                    ]),
                    \FieldFactory::number('recent_tags_count', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_RECENT_TAGS_COUNT,
                        'default' => $settings['recent_tags_count'] ?? 5,
                        'min' => 1,
                        'max' => 20,
                        'column' => '6',
                        'show' => 'field:show_recent_tags'
                    ]),
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTFOOTER_FIELDSET_CATEGORIES, [
                'icon' => 'bi bi-folder',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::checkbox('show_categories', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SHOW_CATEGORIES,
                        'default' => $settings['show_categories'] ?? 0,
                        'switch' => true,
                        'column' => '12'
                    ]),
                    \FieldFactory::string('categories_title', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_CATEGORIES_TITLE,
                        'default' => $settings['categories_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_CATEGORIES_TITLE,
                        'column' => '6',
                        'show' => 'field:show_categories'
                    ]),
                    \FieldFactory::number('categories_count', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_CATEGORIES_COUNT,
                        'default' => $settings['categories_count'] ?? 8,
                        'min' => 1,
                        'max' => 20,
                        'column' => '6',
                        'show' => 'field:show_categories'
                    ]),
                    \FieldFactory::select('categories_style', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_CATEGORIES_STYLE,
                        'options' => [
                            'pills' => LANG_HTMLBLOCK_DEFAULTFOOTER_CATEGORIES_STYLE_PILLS,
                            'links' => LANG_HTMLBLOCK_DEFAULTFOOTER_CATEGORIES_STYLE_LINKS,
                            'chips' => LANG_HTMLBLOCK_DEFAULTFOOTER_CATEGORIES_STYLE_CHIPS
                        ],
                        'default' => $settings['categories_style'] ?? 'pills',
                        'column' => '6',
                        'show' => 'field:show_categories'
                    ]),
                    \FieldFactory::checkbox('categories_show_count', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_CATEGORIES_SHOW_COUNT,
                        'default' => $settings['categories_show_count'] ?? 1,
                        'switch' => true,
                        'column' => '6',
                        'show' => 'field:show_categories'
                    ])
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTFOOTER_FIELDSET_CONTACTS, [
                'icon' => 'bi bi-telephone',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::checkbox('show_contacts', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SHOW_CONTACTS,
                        'default' => $settings['show_contacts'] ?? 0,
                        'switch' => true,
                        'column' => '12'
                    ]),
                    \FieldFactory::string('contacts_title', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_CONTACTS_TITLE,
                        'default' => $settings['contacts_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_CONTACTS_TITLE,
                        'column' => '3',
                        'show' => 'field:show_contacts'
                    ]),
                    \FieldFactory::string('contact_email', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_CONTACT_EMAIL,
                        'default' => $settings['contact_email'] ?? '',
                        'column' => '3',
                        'placeholder' => 'info@example.com',
                        'show' => 'field:show_contacts'
                    ]),
                    \FieldFactory::string('contact_phone', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_CONTACT_PHONE,
                        'default' => $settings['contact_phone'] ?? '',
                        'column' => '3',
                        'placeholder' => LANG_HTMLBLOCK_DEFAULTFOOTER_PHONE_PLACEHOLDER,
                        'show' => 'field:show_contacts'
                    ]),
                    \FieldFactory::string('contact_address', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_CONTACT_ADDRESS,
                        'default' => $settings['contact_address'] ?? '',
                        'column' => '3',
                        'placeholder' => LANG_HTMLBLOCK_DEFAULTFOOTER_ADDRESS_PLACEHOLDER,
                        'show' => 'field:show_contacts'
                    ]),
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTFOOTER_FIELDSET_SOCIAL, [
                'icon' => 'bi bi-share',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::repeater('social_links', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SOCIAL_LINKS,
                        'hint' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_SOCIAL_LINKS_HINT,
                        'column' => '12',
                        'repeater_columns' => 4,
                        'fields' => [
                            [
                                'name' => 'network',
                                'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_NETWORK,
                                'type' => 'select',
                                'options' => [
                                    'telegram' => 'Telegram',
                                    'vk' => 'ВКонтакте',
                                    'youtube' => 'YouTube',
                                    'github' => 'GitHub',
                                    'twitter' => 'Twitter/X',
                                    'instagram' => 'Instagram',
                                    'facebook' => 'Facebook',
                                    'linkedin' => 'LinkedIn',
                                    'odnoklassniki' => 'Одноклассники',
                                    'behance' => 'Behance',
                                    'reddit'  => 'Reddit'
                                ],
                                'default' => 'telegram',
                            ],
                            [
                                'name' => 'url',
                                'title' => 'URL',
                                'type' => 'string',
                                'placeholder' => 'https://...',
                                'attributes' => ['pattern' => 'https?://.+']
                            ],
                        ],
                        'default' => $settings['social_links'] ?? [],
                    ]),
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTFOOTER_FIELDSET_BOTTOM, [
                'icon' => 'bi bi-file-text',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::string('copyright_text', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_COPYRIGHT,
                        'default' => $settings['copyright_text'] ?? '© ' . date('Y') . ' ' . SettingsHelper::get('general', 'site_name', 'BloggyCMS'),
                        'hint' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_COPYRIGHT_HINT,
                        'column' => '12'
                    ]),
                    \FieldFactory::repeater('footer_links', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_FOOTER_LINKS,
                        'hint' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_FOOTER_LINKS_HINT,
                        'column' => '12',
                        'repeater_columns' => 2,
                        'fields' => [
                            [
                                'name' => 'title',
                                'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_LINK_TITLE,
                                'type' => 'string',
                                'placeholder' => LANG_HTMLBLOCK_DEFAULTFOOTER_PLACEHOLDER_PRIVACY
                            ],
                            [
                                'name' => 'url',
                                'title' => 'URL',
                                'type' => 'string',
                                'placeholder' => '/privacy'
                            ],
                            [
                                'name' => 'target',
                                'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_LINK_TARGET,
                                'type' => 'select',
                                'options' => [
                                    '_self' => LANG_HTMLBLOCK_DEFAULTFOOTER_TARGET_SELF,
                                    '_blank' => LANG_HTMLBLOCK_DEFAULTFOOTER_TARGET_BLANK,
                                ],
                                'default' => '_self'
                            ],
                        ],
                        'default' => $settings['footer_links'] ?? [
                            ['title' => LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_PRIVACY_LINK, 'url' => '/privacy', 'target' => '_self']
                        ],
                    ]),
                ]
            ]),
            
            new \Fieldset(LANG_HTMLBLOCK_DEFAULTFOOTER_FIELDSET_APPEARANCE, [
                'icon' => 'bi bi-palette',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::color('background_color', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_BACKGROUND_COLOR,
                        'preset' => 'basic',
                        'column' => '3',
                        'default' => $settings['background_color'] ?? '#111827'
                    ]),
                    \FieldFactory::color('text_color', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_TEXT_COLOR,
                        'preset' => 'basic',
                        'column' => '3',
                        'default' => $settings['text_color'] ?? '#9ca3af'
                    ]),
                    \FieldFactory::color('accent_color', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_ACCENT_COLOR,
                        'preset' => 'website',
                        'column' => '3',
                        'default' => $settings['accent_color'] ?? '#2563eb'
                    ]),
                    \FieldFactory::color('heading_color', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_HEADING_COLOR,
                        'preset' => 'basic',
                        'column' => '3',
                        'default' => $settings['heading_color'] ?? '#f9fafb'
                    ]),
                    \FieldFactory::number('padding_top', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_PADDING_TOP,
                        'default' => $settings['padding_top'] ?? 80,
                        'min' => 40,
                        'max' => 160,
                        'step' => 10,
                        'column' => '6'
                    ]),
                    \FieldFactory::number('padding_bottom', [
                        'title' => LANG_HTMLBLOCK_DEFAULTFOOTER_FIELD_PADDING_BOTTOM,
                        'default' => $settings['padding_bottom'] ?? 40,
                        'min' => 20,
                        'max' => 100,
                        'step' => 10,
                        'column' => '6'
                    ]),
                ]
            ]),
        ];
        
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

    public function validateSettings($settings): array {
        if (!is_array($settings)) {
            return [false, [LANG_HTMLBLOCK_DEFAULTFOOTER_VALIDATION_ERROR]];
        }
        return [true, []];
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
        
        $settings['logo_alt'] = trim($settings['logo_alt'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_LOGO_ALT);
        $settings['site_name'] = trim($settings['site_name'] ?? SettingsHelper::get('general', 'site_name', 'BloggyCMS'));
        $settings['site_description'] = trim($settings['site_description'] ?? '');
        $settings['footer_menu_1'] = $settings['footer_menu_1'] ?? '';
        $settings['footer_menu_2'] = $settings['footer_menu_2'] ?? '';
        $settings['menu_1_title'] = trim($settings['menu_1_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_MENU_1_TITLE);
        $settings['menu_2_title'] = trim($settings['menu_2_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_MENU_2_TITLE);
        $settings['show_recent_posts'] = isset($settings['show_recent_posts']) ? (int)$settings['show_recent_posts'] : 0;
        $settings['recent_posts_title'] = trim($settings['recent_posts_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_RECENT_POSTS_TITLE);
        $settings['recent_posts_count'] = (int)($settings['recent_posts_count'] ?? 3);
        $settings['show_recent_tags'] = isset($settings['show_recent_tags']) ? (int)$settings['show_recent_tags'] : 0;
        $settings['recent_tags_title'] = trim($settings['recent_tags_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_RECENT_TAGS_TITLE);
        $settings['recent_tags_count'] = (int)($settings['recent_tags_count'] ?? 5);
        $settings['show_categories'] = isset($settings['show_categories']) ? (int)$settings['show_categories'] : 0;
        $settings['categories_title'] = trim($settings['categories_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_CATEGORIES_TITLE);
        $settings['categories_count'] = (int)($settings['categories_count'] ?? 8);
        $settings['categories_show_count'] = isset($settings['categories_show_count']) ? (int)$settings['categories_show_count'] : 1;
        $settings['categories_style'] = $settings['categories_style'] ?? 'pills';
        $settings['show_contacts'] = isset($settings['show_contacts']) ? (int)$settings['show_contacts'] : 0;
        $settings['contacts_title'] = trim($settings['contacts_title'] ?? LANG_HTMLBLOCK_DEFAULTFOOTER_DEFAULT_CONTACTS_TITLE);
        $settings['contact_email'] = trim($settings['contact_email'] ?? '');
        $settings['contact_phone'] = trim($settings['contact_phone'] ?? '');
        $settings['contact_address'] = trim($settings['contact_address'] ?? '');
        $settings['social_links'] = $settings['social_links'] ?? [];
        if (!is_array($settings['social_links'])) {
            $settings['social_links'] = [];
        }
        
        $settings['copyright_text'] = $settings['copyright_text'] ?? '© ' . date('Y') . ' ' . SettingsHelper::get('general', 'site_name', 'BloggyCMS');
        $settings['footer_links'] = $settings['footer_links'] ?? [];
        if (!is_array($settings['footer_links'])) {
            $settings['footer_links'] = [];
        }
        
        $settings['background_color'] = $settings['background_color'] ?? '#111827';
        $settings['text_color'] = $settings['text_color'] ?? '#9ca3af';
        $settings['accent_color'] = $settings['accent_color'] ?? '#2563eb';
        $settings['heading_color'] = $settings['heading_color'] ?? '#f9fafb';
        $settings['padding_top'] = (int)($settings['padding_top'] ?? 80);
        $settings['padding_bottom'] = (int)($settings['padding_bottom'] ?? 40);
        
        return $settings;
    }

    private function getRecentPosts($limit = 3) {
        try {
            if (!API::hasModel('posts')) return [];
            $posts = API::posts()->getAll($limit);
            $posts = array_filter($posts, fn($p) => ($p['status'] ?? '') === 'published');
            return array_slice($posts, 0, $limit);
        } catch (Exception $e) { return []; }
    }

    private function getRecentTags($limit = 5) {
        try {
            if (!API::hasModel('tags')) return [];
            $tags = API::tags()->getAll();
            usort($tags, fn($a, $b) => ($b['posts_count'] ?? 0) - ($a['posts_count'] ?? 0));
            return array_slice($tags, 0, $limit);
        } catch (Exception $e) { return []; }
    }

    private function getCategories($limit = 8) {
        try {
            if (!API::hasModel('categories')) return [];
            $categories = API::categories()->getAll();
            return array_slice($categories, 0, $limit);
        } catch (Exception $e) { return []; }
    }

    public function getLogoUrl($settings) {
        if (!empty($settings['logo_path'])) {
            return BlockImageHelper::getImageUrl($settings['logo_path']);
        }
        return '';
    }

    public function processFrontend($settings = [], $templateName = null): string {
        $this->recentPosts = $settings['show_recent_posts'] ? $this->getRecentPosts($settings['recent_posts_count'] ?? 3) : [];
        $this->recentTags = $settings['show_recent_tags'] ? $this->getRecentTags($settings['recent_tags_count'] ?? 5) : [];
        $this->categories = $settings['show_categories'] ? $this->getCategories($settings['categories_count'] ?? 8) : [];
        return parent::processFrontend($settings, $templateName);
    }
}