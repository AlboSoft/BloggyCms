<?php

class CategoriesListBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_CATEGORIESLIST_NAME;
    }

    public function getSystemName(): string {
        return "CategoriesListBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_CATEGORIESLIST_DESCRIPTION;
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

    public function getSettingsForm($currentSettings = []): string {
        
        $settings = array_merge([], $currentSettings);
        
        $fieldsets = [];
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_CATEGORIESLIST_FIELDSET_HEADER, [
            'icon' => 'bi bi-pencil',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_BADGE,
                    'default' => $settings['badge'] ?? LANG_HTMLBLOCK_CATEGORIESLIST_DEFAULT_BADGE,
                    'placeholder' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_BADGE_PLACEHOLDER,
                ]),
                \FieldFactory::string('title', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_TITLE,
                    'default' => $settings['title'] ?? LANG_HTMLBLOCK_CATEGORIESLIST_DEFAULT_TITLE,
                    'placeholder' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_TITLE_PLACEHOLDER,
                ]),
                \FieldFactory::textarea('description', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_DESCRIPTION,
                    'default' => $settings['description'] ?? LANG_HTMLBLOCK_CATEGORIESLIST_DEFAULT_DESCRIPTION,
                    'rows' => 3,
                ]),
                \FieldFactory::select('align', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_ALIGN,
                    'options' => [
                        'left' => LANG_HTMLBLOCK_CATEGORIESLIST_ALIGN_LEFT,
                        'center' => LANG_HTMLBLOCK_CATEGORIESLIST_ALIGN_CENTER,
                    ],
                    'default' => 'center',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_CATEGORIESLIST_FIELDSET_DISPLAY, [
            'icon' => 'bi bi-grid-3x3',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('display_style', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_DISPLAY_STYLE,
                    'options' => [
                        'cards' => LANG_HTMLBLOCK_CATEGORIESLIST_STYLE_CARDS,
                        'list' => LANG_HTMLBLOCK_CATEGORIESLIST_STYLE_LIST,
                        'grid' => LANG_HTMLBLOCK_CATEGORIESLIST_STYLE_GRID,
                        'compact' => LANG_HTMLBLOCK_CATEGORIESLIST_STYLE_COMPACT,
                    ],
                    'default' => 'cards',
                ]),
                \FieldFactory::select('columns', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_COLUMNS,
                    'options' => [
                        '2' => LANG_HTMLBLOCK_CATEGORIESLIST_COLUMNS_2,
                        '3' => LANG_HTMLBLOCK_CATEGORIESLIST_COLUMNS_3,
                        '4' => LANG_HTMLBLOCK_CATEGORIESLIST_COLUMNS_4,
                    ],
                    'default' => '3',
                    'show' => 'field:display_style in cards,grid',
                ]),
                \FieldFactory::number('limit', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_LIMIT,
                    'default' => 6,
                    'min' => 1,
                    'max' => 50,
                    'hint' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_LIMIT_HINT,
                ]),
                \FieldFactory::checkbox('show_hierarchy', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_SHOW_HIERARCHY,
                    'default' => 0,
                    'switch' => true,
                    'hint' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_SHOW_HIERARCHY_HINT,
                ]),
                \FieldFactory::checkbox('show_post_count', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_SHOW_POST_COUNT,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_empty', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_SHOW_EMPTY,
                    'default' => 0,
                    'switch' => true,
                    'hint' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_SHOW_EMPTY_HINT,
                ]),
                \FieldFactory::checkbox('show_icon', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_SHOW_ICON,
                    'default' => 1,
                    'switch' => true,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_CATEGORIESLIST_FIELDSET_IMAGES, [
            'icon' => 'bi bi-image',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('image_style', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_IMAGE_STYLE,
                    'options' => [
                        'none' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_STYLE_NONE,
                        'icon' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_STYLE_ICON,
                        'thumbnail' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_STYLE_THUMBNAIL,
                        'cover' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_STYLE_COVER,
                        'background' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_STYLE_BACKGROUND,
                        'side' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_STYLE_SIDE,
                    ],
                    'default' => 'icon',
                ]),
                \FieldFactory::select('image_size', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_IMAGE_SIZE,
                    'options' => [
                        'sm' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_SIZE_SM,
                        'md' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_SIZE_MD,
                        'lg' => LANG_HTMLBLOCK_CATEGORIESLIST_IMAGE_SIZE_LG,
                    ],
                    'default' => 'md',
                    'show' => 'field:image_style != none && field:image_style != icon',
                ]),
                \FieldFactory::checkbox('image_rounded', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_IMAGE_ROUNDED,
                    'default' => 1,
                    'switch' => true,
                    'show' => 'field:image_style != none',
                ]),
                \FieldFactory::checkbox('image_shadow', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_IMAGE_SHADOW,
                    'default' => 0,
                    'switch' => true,
                    'show' => 'field:image_style != none',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_CATEGORIESLIST_FIELDSET_SORTING, [
            'icon' => 'bi bi-arrow-up-short',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('order_by', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_ORDER_BY,
                    'options' => [
                        'name ASC' => LANG_HTMLBLOCK_CATEGORIESLIST_ORDER_NAME_ASC,
                        'name DESC' => LANG_HTMLBLOCK_CATEGORIESLIST_ORDER_NAME_DESC,
                        'posts_count DESC' => LANG_HTMLBLOCK_CATEGORIESLIST_ORDER_POSTS_DESC,
                        'posts_count ASC' => LANG_HTMLBLOCK_CATEGORIESLIST_ORDER_POSTS_ASC,
                        'id ASC' => LANG_HTMLBLOCK_CATEGORIESLIST_ORDER_ID_ASC,
                        'id DESC' => LANG_HTMLBLOCK_CATEGORIESLIST_ORDER_ID_DESC,
                    ],
                    'default' => 'name ASC',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_CATEGORIESLIST_FIELDSET_FILTER, [
            'icon' => 'bi bi-funnel',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('filter_by_parent', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_FILTER_BY_PARENT,
                    'default' => 0,
                    'switch' => true,
                    'show' => 'field:show_hierarchy = 0',
                ]),
                \FieldFactory::checkbox('exclude_current', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_EXCLUDE_CURRENT,
                    'default' => 0,
                    'switch' => true,
                    'hint' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_EXCLUDE_CURRENT_HINT,
                ]),
                \FieldFactory::textarea('exclude_ids', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_EXCLUDE_IDS,
                    'default' => '',
                    'placeholder' => '5, 12, 8',
                    'rows' => 2,
                    'hint' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_EXCLUDE_IDS_HINT,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_CATEGORIESLIST_FIELDSET_COLORS, [
            'icon' => 'bi bi-palette',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_CATEGORIESLIST_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_CATEGORIESLIST_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_CATEGORIESLIST_THEME_CUSTOM,
                    ],
                    'default' => 'light',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'default' => '#2563eb',
                ]),
                \FieldFactory::color('card_background', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_CARD_BACKGROUND,
                    'preset' => 'basic',
                    'default' => $settings['card_background'] ?? '',
                    'hint' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_CARD_BACKGROUND_HINT,
                ]),
                \FieldFactory::checkbox('gradient_cards', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_GRADIENT_CARDS,
                    'default' => 0,
                    'switch' => true,
                    'hint' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_GRADIENT_CARDS_HINT,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_CATEGORIESLIST_FIELDSET_PADDING, [
            'icon' => 'bi bi-arrows-expand',
            'columns' => '12',
            'fields' => [
                \FieldFactory::number('padding_top', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_PADDING_TOP,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_PADDING_BOTTOM,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_CATEGORIESLIST_FIELDSET_EXTRA, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_CSS_CLASS,
                    'default' => $settings['custom_css_class'] ?? '',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => LANG_HTMLBLOCK_CATEGORIESLIST_FIELD_HTML_ID,
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

    private function getCategories($settings) {
        try {
            if (!API::hasModel('categories')) {
                return [];
            }

            $limit = (int)($settings['limit'] ?? 6);
            $showEmpty = !empty($settings['show_empty']);
            $orderBy = $settings['order_by'] ?? 'name ASC';
            $filterParent = !empty($settings['filter_by_parent']);
            $excludeIds = [];

            if (!empty($settings['exclude_ids'])) {
                $ids = explode(',', $settings['exclude_ids']);
                foreach ($ids as $id) {
                    $id = trim($id);
                    if (is_numeric($id)) {
                        $excludeIds[] = (int)$id;
                    }
                }
            }

            if (!empty($settings['exclude_current']) && isset($_GET['id'])) {
                $excludeIds[] = (int)$_GET['id'];
            } elseif (!empty($settings['exclude_current']) && isset($_GET['slug'])) {

            }

            $categories = API::categories()->getAll();

            if (empty($categories)) {
                return [];
            }

            $filteredCategories = [];

            foreach ($categories as $category) {
                if ($filterParent && !empty($category['parent_id'])) {
                    continue;
                }

                if (in_array($category['id'], $excludeIds)) {
                    continue;
                }

                if (!$showEmpty && ($category['posts_count'] ?? 0) == 0) {
                    continue;
                }

                $filteredCategories[] = $category;
            }

            list($field, $direction) = explode(' ', $orderBy);

            usort($filteredCategories, function($a, $b) use ($field, $direction) {
                $aVal = $a[$field] ?? '';
                $bVal = $b[$field] ?? '';

                if ($field === 'posts_count') {
                    $aVal = (int)$aVal;
                    $bVal = (int)$bVal;
                    $result = $aVal - $bVal;
                } else {
                    $result = strcmp((string)$aVal, (string)$bVal);
                }

                return $direction === 'DESC' ? -$result : $result;
            });

            if ($limit > 0) {
                $filteredCategories = array_slice($filteredCategories, 0, $limit);
            }

            if (!empty($settings['show_hierarchy'])) {
                $filteredCategories = $this->buildHierarchy($filteredCategories);
            }

            return $filteredCategories;

        } catch (Exception $e) {
            return [];
        }
    }

    private function buildHierarchy($categories, $parentId = 0) {
        $result = [];

        foreach ($categories as $category) {
            if (($category['parent_id'] ?? 0) == $parentId) {
                $children = $this->buildHierarchy($categories, $category['id']);
                if (!empty($children)) {
                    $category['children'] = $children;
                }
                $result[] = $category;
            }
        }

        return $result;
    }

    public function getCategoryImageUrl($category) {
        if (!empty($category['image'])) {
            if (strpos($category['image'], 'http') === 0 || strpos($category['image'], '/') === 0) {
                return $category['image'];
            }
            return '/uploads/images/' . $category['image'];
        }
        
        return '/templates/' . DEFAULT_TEMPLATE . '/front/assets/img/default-category.jpg';
    }

    public function processFrontend($settings = [], $templateName = null): string {

        $categories = $this->getCategories($settings);

        $this->categories = $categories;

        return parent::processFrontend($settings, $templateName);
    }
}