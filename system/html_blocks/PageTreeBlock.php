<?php

class PageTreeBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_PAGETREE_NAME;
    }

    public function getSystemName(): string {
        return "PageTreeBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_PAGETREE_DESCRIPTION;
    }

    public function getShortDescription(): string {
        return LANG_HTMLBLOCK_PAGETREE_SHORT_DESCRIPTION;
    }

    public function getIcon(): string {
        return 'bi bi-diagram-3';
    }

    public function getAuthor(): string {
        return 'BloggyCMS Team';
    }

    public function getVersion(): string {
        return '1.0.0';
    }

    public function getSettingsForm($currentSettings = []): string {
        $settings = array_merge($this->getDefaultSettings(), $currentSettings);
        
        $fieldsets = [];
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_PAGETREE_FIELDSET_MAIN, [
            'icon' => 'bi bi-gear',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_BADGE,
                    'default' => $settings['badge'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_PAGETREE_FIELD_BADGE_PLACEHOLDER,
                    'column' => '12',
                ]),
                \FieldFactory::string('title', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_TITLE,
                    'default' => $settings['title'] ?? LANG_HTMLBLOCK_PAGETREE_DEFAULT_TITLE,
                    'placeholder' => LANG_HTMLBLOCK_PAGETREE_FIELD_TITLE_PLACEHOLDER,
                    'column' => '12',
                ]),
                \FieldFactory::textarea('description', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_DESCRIPTION,
                    'default' => $settings['description'] ?? '',
                    'rows' => 3,
                    'placeholder' => LANG_HTMLBLOCK_PAGETREE_FIELD_DESCRIPTION_PLACEHOLDER,
                    'column' => '12',
                ]),
                \FieldFactory::select('align', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_ALIGN,
                    'options' => [
                        'left' => LANG_HTMLBLOCK_PAGETREE_ALIGN_LEFT,
                        'center' => LANG_HTMLBLOCK_PAGETREE_ALIGN_CENTER,
                        'right' => LANG_HTMLBLOCK_PAGETREE_ALIGN_RIGHT,
                    ],
                    'default' => $settings['align'] ?? 'left',
                    'column' => '12',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_PAGETREE_FIELDSET_DISPLAY, [
            'icon' => 'bi bi-eye',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::checkbox('show_home', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_SHOW_HOME,
                    'default' => $settings['show_home'] ?? true,
                    'switch' => true,
                    'column' => '12',
                ]),
                \FieldFactory::string('home_title', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_HOME_TITLE,
                    'default' => $settings['home_title'] ?? LANG_HTMLBLOCK_PAGETREE_DEFAULT_HOME_TITLE,
                    'placeholder' => LANG_HTMLBLOCK_PAGETREE_FIELD_HOME_TITLE_PLACEHOLDER,
                    'show' => 'field:show_home',
                    'column' => '12',
                ]),
                \FieldFactory::checkbox('show_icons', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_SHOW_ICONS,
                    'default' => $settings['show_icons'] ?? true,
                    'switch' => true,
                    'column' => '6',
                ]),
                \FieldFactory::checkbox('show_page_status', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_SHOW_STATUS,
                    'default' => $settings['show_page_status'] ?? true,
                    'switch' => true,
                    'column' => '6',
                ]),
                \FieldFactory::checkbox('show_page_descriptions', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_SHOW_DESCRIPTIONS,
                    'default' => $settings['show_page_descriptions'] ?? false,
                    'switch' => true,
                    'column' => '12',
                ]),
                \FieldFactory::checkbox('highlight_current', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_HIGHLIGHT_CURRENT,
                    'default' => $settings['highlight_current'] ?? true,
                    'switch' => true,
                    'column' => '12',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_PAGETREE_FIELDSET_FILTER, [
            'icon' => 'bi bi-funnel',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('root_page', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_ROOT_PAGE,
                    'options' => $this->getPagesSelectOptions($settings['root_page'] ?? null),
                    'default' => $settings['root_page'] ?? '',
                    'hint' => LANG_HTMLBLOCK_PAGETREE_FIELD_ROOT_PAGE_HINT,
                    'column' => '12',
                ]),
                \FieldFactory::checkbox('show_only_published', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_ONLY_PUBLISHED,
                    'default' => $settings['show_only_published'] ?? true,
                    'switch' => true,
                    'column' => '12',
                ]),
                \FieldFactory::textarea('exclude_ids', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_EXCLUDE_IDS,
                    'default' => $settings['exclude_ids'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_PAGETREE_FIELD_EXCLUDE_IDS_PLACEHOLDER,
                    'rows' => 2,
                    'hint' => LANG_HTMLBLOCK_PAGETREE_FIELD_EXCLUDE_IDS_HINT,
                    'column' => '12',
                ]),
                \FieldFactory::number('max_depth', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_MAX_DEPTH,
                    'default' => $settings['max_depth'] ?? 0,
                    'min' => 0,
                    'max' => 10,
                    'hint' => LANG_HTMLBLOCK_PAGETREE_FIELD_MAX_DEPTH_HINT,
                    'column' => '12',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_PAGETREE_FIELDSET_APPEARANCE, [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_PAGETREE_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_PAGETREE_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_PAGETREE_THEME_CUSTOM,
                    ],
                    'default' => $settings['theme'] ?? 'light',
                    'column' => '12',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'default' => $settings['accent_color'] ?? '#2563eb',
                    'column' => '12',
                ]),
                \FieldFactory::color('link_color', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_LINK_COLOR,
                    'preset' => 'basic',
                    'default' => $settings['link_color'] ?? '#3b82f6',
                    'column' => '6',
                ]),
                \FieldFactory::color('link_hover_color', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_LINK_HOVER_COLOR,
                    'preset' => 'basic',
                    'default' => $settings['link_hover_color'] ?? '#2563eb',
                    'column' => '6',
                ]),
                \FieldFactory::color('active_color', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_ACTIVE_COLOR,
                    'preset' => 'basic',
                    'default' => $settings['active_color'] ?? '#1d4ed8',
                    'column' => '12',
                ]),
                \FieldFactory::number('indent_size', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_INDENT_SIZE,
                    'default' => $settings['indent_size'] ?? 24,
                    'min' => 0,
                    'max' => 64,
                    'step' => 4,
                    'hint' => LANG_HTMLBLOCK_PAGETREE_FIELD_INDENT_SIZE_HINT,
                    'column' => '6',
                ]),
                \FieldFactory::number('padding_top', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_PADDING_TOP,
                    'default' => $settings['padding_top'] ?? 40,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                    'column' => '6',
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_PADDING_BOTTOM,
                    'default' => $settings['padding_bottom'] ?? 40,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                    'column' => '6',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_PAGETREE_FIELDSET_EXTRA, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('enable_schema', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_ENABLE_SCHEMA,
                    'default' => $settings['enable_schema'] ?? true,
                    'switch' => true,
                    'hint' => LANG_HTMLBLOCK_PAGETREE_FIELD_ENABLE_SCHEMA_HINT,
                    'column' => '12',
                ]),
                \FieldFactory::checkbox('show_on_mobile', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_SHOW_ON_MOBILE,
                    'default' => $settings['show_on_mobile'] ?? true,
                    'switch' => true,
                    'column' => '12',
                ]),
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_CSS_CLASS,
                    'default' => $settings['custom_css_class'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_PAGETREE_FIELD_CSS_CLASS_PLACEHOLDER,
                    'column' => '12',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => LANG_HTMLBLOCK_PAGETREE_FIELD_HTML_ID,
                    'default' => $settings['custom_id'] ?? '',
                    'placeholder' => LANG_HTMLBLOCK_PAGETREE_FIELD_HTML_ID_PLACEHOLDER,
                    'column' => '12',
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
            'badge' => '',
            'title' => LANG_HTMLBLOCK_PAGETREE_DEFAULT_TITLE,
            'description' => '',
            'align' => 'left',
            'show_home' => true,
            'home_title' => LANG_HTMLBLOCK_PAGETREE_DEFAULT_HOME_TITLE,
            'show_icons' => true,
            'show_page_status' => true,
            'show_page_descriptions' => false,
            'highlight_current' => true,
            'root_page' => '',
            'show_only_published' => true,
            'exclude_ids' => '',
            'max_depth' => 0,
            'theme' => 'light',
            'accent_color' => '#2563eb',
            'link_color' => '#3b82f6',
            'link_hover_color' => '#2563eb',
            'active_color' => '#1d4ed8',
            'indent_size' => 24,
            'padding_top' => 40,
            'padding_bottom' => 40,
            'enable_schema' => true,
            'show_on_mobile' => true,
            'custom_css_class' => '',
            'custom_id' => '',
        ];
    }

    private function getPagesSelectOptions($selectedId = null): array {
        $options = ['' => LANG_HTMLBLOCK_PAGETREE_ROOT_OPTION_ALL];
        
        try {
            $db = Database::getInstance();
            $pageModel = new PageModel($db);
            $pages = $pageModel->getAll();
            
            $options = $this->buildOptionsTree($pages, 0, $selectedId);
        } catch (Exception $e) {}
        
        return $options;
    }

    private function buildOptionsTree($pages, $parentId = 0, $selectedId = null, $level = 0): array {
        $options = [];
        $prefix = str_repeat('—', $level) . ($level > 0 ? ' ' : '');
        
        foreach ($pages as $page) {
            if (($page['parent_id'] ?? 0) == $parentId) {
                $options[$page['id']] = $prefix . html($page['title']);
                
                $children = $this->buildOptionsTree($pages, $page['id'], $selectedId, $level + 1);
                $options = $options + $children;
            }
        }
        
        return $options;
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return $this->getDefaultSettings();
        }
        
        $prepared = array_merge($this->getDefaultSettings(), $settings);
        
        $textFields = ['badge', 'title', 'description', 'home_title', 'custom_css_class', 'custom_id'];
        foreach ($textFields as $field) {
            if (isset($prepared[$field])) {
                $prepared[$field] = trim($prepared[$field]);
            }
        }
        
        if (!empty($prepared['exclude_ids'])) {
            $ids = explode(',', $prepared['exclude_ids']);
            $cleanIds = [];
            foreach ($ids as $id) {
                $id = trim($id);
                if (is_numeric($id) && $id > 0) {
                    $cleanIds[] = (int)$id;
                }
            }
            $prepared['exclude_ids_array'] = $cleanIds;
        } else {
            $prepared['exclude_ids_array'] = [];
        }
        
        $prepared['max_depth'] = (int)($settings['max_depth'] ?? 0);
        $prepared['indent_size'] = (int)($settings['indent_size'] ?? 24);
        $prepared['padding_top'] = (int)($settings['padding_top'] ?? 40);
        $prepared['padding_bottom'] = (int)($settings['padding_bottom'] ?? 40);
        
        $boolFields = [
            'show_home', 'show_icons', 'show_page_status', 'show_page_descriptions',
            'highlight_current', 'show_only_published', 'enable_schema', 'show_on_mobile'
        ];
        foreach ($boolFields as $field) {
            $prepared[$field] = isset($settings[$field]) && filter_var($settings[$field], FILTER_VALIDATE_BOOLEAN);
        }
        
        return $prepared;
    }

    public function validateSettings($settings): array {
        $errors = [];
        
        $maxDepth = (int)($settings['max_depth'] ?? 0);
        if ($maxDepth < 0 || $maxDepth > 10) {
            $errors[] = LANG_HTMLBLOCK_PAGETREE_VALIDATION_DEPTH;
        }
        
        $indentSize = (int)($settings['indent_size'] ?? 24);
        if ($indentSize < 0 || $indentSize > 64) {
            $errors[] = LANG_HTMLBLOCK_PAGETREE_VALIDATION_INDENT;
        }
        
        return [empty($errors), $errors];
    }

    private function getPageTree($settings): array {
        try {
            $db = Database::getInstance();
            $pageModel = new PageModel($db);
            
            $allPages = $pageModel->getAll();
            
            if (empty($allPages)) {
                return [];
            }
            
            if ($settings['show_only_published']) {
                $allPages = array_filter($allPages, function($page) {
                    return ($page['status'] ?? '') === 'published';
                });
            }
            
            if (!empty($settings['exclude_ids_array'])) {
                $allPages = array_filter($allPages, function($page) use ($settings) {
                    return !in_array($page['id'], $settings['exclude_ids_array']);
                });
            }
            
            $pagesById = [];
            foreach ($allPages as $page) {
                $page['children'] = [];
                $pagesById[$page['id']] = $page;
            }
            
            $tree = [];
            $rootPageId = !empty($settings['root_page']) ? (int)$settings['root_page'] : 0;
            
            foreach ($pagesById as $id => &$page) {
                $parentId = $page['parent_id'] ?? 0;
                
                if ($rootPageId > 0) {
                    if ($id == $rootPageId) {
                        $tree[] = &$page;
                        continue;
                    }
                    if ($parentId == 0) {
                        continue;
                    }
                }
                
                if ($parentId == 0) {
                    if ($rootPageId == 0) {
                        $tree[] = &$page;
                    }
                } elseif (isset($pagesById[$parentId])) {
                    $pagesById[$parentId]['children'][] = &$page;
                }
            }
            
            if ($settings['max_depth'] > 0) {
                $this->limitTreeDepth($tree, $settings['max_depth']);
            }
            
            return $tree;
            
        } catch (Exception $e) {
            error_log('PageTreeBlock error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function limitTreeDepth(&$tree, $maxDepth, $currentDepth = 1): void {
        if ($currentDepth >= $maxDepth) {
            foreach ($tree as &$node) {
                unset($node['children']);
                $node['children'] = [];
            }
            return;
        }
        
        foreach ($tree as &$node) {
            if (!empty($node['children'])) {
                $this->limitTreeDepth($node['children'], $maxDepth, $currentDepth + 1);
            }
        }
    }

    private function getCurrentPage(): ?array {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        if (preg_match('#/page/([^/?]+)#', $uri, $matches)) {
            $slug = $matches[1];
        } elseif (preg_match('#/([^/]+)$#', $uri, $matches) && $matches[1] !== '' && $matches[1] !== 'admin') {
            $slug = $matches[1];
        } else {
            return null;
        }
        
        try {
            $db = Database::getInstance();
            $pageModel = new PageModel($db);
            return $pageModel->getBySlug($slug);
        } catch (Exception $e) {
            return null;
        }
    }

    public function processFrontend($settings = [], $templateName = null): string {
        $pageTree = $this->getPageTree($settings);
        $currentPage = $settings['highlight_current'] ? $this->getCurrentPage() : null;
        
        $data = array_merge($settings, [
            'page_tree' => $pageTree,
            'current_page' => $currentPage,
            'home_url' => BASE_URL,
            'has_pages' => !empty($pageTree),
            'total_pages' => $this->countPagesInTree($pageTree),
            'current_page_id' => $currentPage ? $currentPage['id'] : null,
        ]);
        
        return parent::processFrontend($data, $templateName);
    }
    
    private function countPagesInTree($tree): int {
        $count = 0;
        foreach ($tree as $node) {
            $count++;
            if (!empty($node['children'])) {
                $count += $this->countPagesInTree($node['children']);
            }
        }
        return $count;
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/html_blocks/PageTreeBlock/css/page-tree.css'
        ];
    }

    public function getFrontendJs(): array {
        return [
            '/templates/default/front/assets/html_blocks/PageTreeBlock/js/page-tree.js'
        ];
    }

}