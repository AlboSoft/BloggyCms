<?php

class TagsBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return LANG_HTMLBLOCK_TAGS_NAME;
    }

    public function getSystemName(): string {
        return "TagsBlock";
    }

    public function getDescription(): string {
        return LANG_HTMLBLOCK_TAGS_DESCRIPTION;
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

    public $tags = [];

    public function getSettingsForm($currentSettings = []): string {
        
        $settings = array_merge([], $currentSettings);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_TAGS_FIELDSET_HEADER, [
            'icon' => 'bi bi-pencil',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_BADGE,
                    'default' => $settings['badge'] ?? LANG_HTMLBLOCK_TAGS_DEFAULT_BADGE,
                    'placeholder' => LANG_HTMLBLOCK_TAGS_FIELD_BADGE_PLACEHOLDER,
                ]),
                \FieldFactory::string('title', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_TITLE,
                    'default' => $settings['title'] ?? LANG_HTMLBLOCK_TAGS_DEFAULT_TITLE,
                    'placeholder' => LANG_HTMLBLOCK_TAGS_FIELD_TITLE_PLACEHOLDER,
                ]),
                \FieldFactory::textarea('description', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_DESCRIPTION,
                    'default' => $settings['description'] ?? LANG_HTMLBLOCK_TAGS_DEFAULT_DESCRIPTION,
                    'rows' => 3,
                ]),
                \FieldFactory::select('align', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_ALIGN,
                    'options' => [
                        'left' => LANG_HTMLBLOCK_TAGS_ALIGN_LEFT,
                        'center' => LANG_HTMLBLOCK_TAGS_ALIGN_CENTER,
                    ],
                    'default' => 'center',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_TAGS_FIELDSET_DISPLAY, [
            'icon' => 'bi bi-grid-3x3',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('display_style', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_DISPLAY_STYLE,
                    'options' => [
                        'cloud' => LANG_HTMLBLOCK_TAGS_STYLE_CLOUD,
                        'cards' => LANG_HTMLBLOCK_TAGS_STYLE_CARDS,
                        'list' => LANG_HTMLBLOCK_TAGS_STYLE_LIST,
                        'compact' => LANG_HTMLBLOCK_TAGS_STYLE_COMPACT,
                        'grid' => LANG_HTMLBLOCK_TAGS_STYLE_GRID,
                    ],
                    'default' => 'cloud',
                ]),
                \FieldFactory::select('columns', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_COLUMNS,
                    'options' => [
                        '2' => LANG_HTMLBLOCK_TAGS_COLUMNS_2,
                        '3' => LANG_HTMLBLOCK_TAGS_COLUMNS_3,
                        '4' => LANG_HTMLBLOCK_TAGS_COLUMNS_4,
                    ],
                    'default' => '3',
                    'show' => 'field:display_style in cards,grid',
                ]),
                \FieldFactory::number('limit', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_LIMIT,
                    'default' => 20,
                    'min' => 1,
                    'max' => 100,
                    'hint' => LANG_HTMLBLOCK_TAGS_FIELD_LIMIT_HINT,
                ]),
                \FieldFactory::number('min_posts', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_MIN_POSTS,
                    'default' => 1,
                    'min' => 0,
                    'max' => 100,
                    'hint' => LANG_HTMLBLOCK_TAGS_FIELD_MIN_POSTS_HINT,
                ]),
                \FieldFactory::checkbox('show_post_count', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_SHOW_POST_COUNT,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_icon', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_SHOW_ICON,
                    'default' => 1,
                    'switch' => true,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_TAGS_FIELDSET_IMAGES, [
            'icon' => 'bi bi-image',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('image_style', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_IMAGE_STYLE,
                    'options' => [
                        'none' => LANG_HTMLBLOCK_TAGS_IMAGE_STYLE_NONE,
                        'icon' => LANG_HTMLBLOCK_TAGS_IMAGE_STYLE_ICON,
                        'thumbnail' => LANG_HTMLBLOCK_TAGS_IMAGE_STYLE_THUMBNAIL,
                        'cover' => LANG_HTMLBLOCK_TAGS_IMAGE_STYLE_COVER,
                        'background' => LANG_HTMLBLOCK_TAGS_IMAGE_STYLE_BACKGROUND,
                        'side' => LANG_HTMLBLOCK_TAGS_IMAGE_STYLE_SIDE,
                    ],
                    'default' => 'icon',
                ]),
                \FieldFactory::select('image_size', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_IMAGE_SIZE,
                    'options' => [
                        'sm' => LANG_HTMLBLOCK_TAGS_IMAGE_SIZE_SM,
                        'md' => LANG_HTMLBLOCK_TAGS_IMAGE_SIZE_MD,
                        'lg' => LANG_HTMLBLOCK_TAGS_IMAGE_SIZE_LG,
                    ],
                    'default' => 'md',
                    'show' => 'field:image_style != none && field:image_style != icon',
                ]),
                \FieldFactory::checkbox('image_rounded', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_IMAGE_ROUNDED,
                    'default' => 1,
                    'switch' => true,
                    'show' => 'field:image_style != none',
                ]),
                \FieldFactory::checkbox('image_shadow', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_IMAGE_SHADOW,
                    'default' => 0,
                    'switch' => true,
                    'show' => 'field:image_style != none',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_TAGS_FIELDSET_SORTING, [
            'icon' => 'bi bi-arrow-up-short',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('order_by', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_ORDER_BY,
                    'options' => [
                        'name ASC' => LANG_HTMLBLOCK_TAGS_ORDER_NAME_ASC,
                        'name DESC' => LANG_HTMLBLOCK_TAGS_ORDER_NAME_DESC,
                        'posts_count DESC' => LANG_HTMLBLOCK_TAGS_ORDER_POPULAR_DESC,
                        'posts_count ASC' => LANG_HTMLBLOCK_TAGS_ORDER_POPULAR_ASC,
                        'id DESC' => LANG_HTMLBLOCK_TAGS_ORDER_ID_DESC,
                        'id ASC' => LANG_HTMLBLOCK_TAGS_ORDER_ID_ASC,
                    ],
                    'default' => 'posts_count DESC',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_TAGS_FIELDSET_FILTER, [
            'icon' => 'bi bi-funnel',
            'columns' => '12',
            'fields' => [
                \FieldFactory::textarea('exclude_ids', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_EXCLUDE_IDS,
                    'default' => '',
                    'placeholder' => '5, 12, 8',
                    'rows' => 2,
                    'hint' => LANG_HTMLBLOCK_TAGS_FIELD_EXCLUDE_IDS_HINT,
                ]),
                \FieldFactory::textarea('include_ids', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_INCLUDE_IDS,
                    'default' => '',
                    'placeholder' => '3, 7, 15',
                    'rows' => 2,
                    'hint' => LANG_HTMLBLOCK_TAGS_FIELD_INCLUDE_IDS_HINT,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_TAGS_FIELDSET_COLORS, [
            'icon' => 'bi bi-palette',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_TAGS_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_TAGS_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_TAGS_THEME_CUSTOM,
                    ],
                    'default' => 'light',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'default' => '#2563eb',
                ]),
                \FieldFactory::color('card_background', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_CARD_BACKGROUND,
                    'preset' => 'basic',
                    'default' => $settings['card_background'] ?? '',
                    'hint' => LANG_HTMLBLOCK_TAGS_FIELD_CARD_BACKGROUND_HINT,
                ]),
                \FieldFactory::checkbox('gradient_cards', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_GRADIENT_CARDS,
                    'default' => 0,
                    'switch' => true,
                    'hint' => LANG_HTMLBLOCK_TAGS_FIELD_GRADIENT_CARDS_HINT,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_TAGS_FIELDSET_PADDING, [
            'icon' => 'bi bi-arrows-expand',
            'columns' => '12',
            'fields' => [
                \FieldFactory::number('padding_top', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_PADDING_TOP,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_PADDING_BOTTOM,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_TAGS_FIELDSET_EXTRA, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_CSS_CLASS,
                    'default' => $settings['custom_css_class'] ?? '',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => LANG_HTMLBLOCK_TAGS_FIELD_HTML_ID,
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

    private function getTags($settings) {
        try {
            if (!API::hasModel('tags')) {
                return [];
            }

            $limit = (int)($settings['limit'] ?? 20);
            $minPosts = (int)($settings['min_posts'] ?? 1);
            $orderBy = $settings['order_by'] ?? 'posts_count DESC';
            
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

            $includeIds = [];
            if (!empty($settings['include_ids'])) {
                $ids = explode(',', $settings['include_ids']);
                foreach ($ids as $id) {
                    $id = trim($id);
                    if (is_numeric($id)) {
                        $includeIds[] = (int)$id;
                    }
                }
            }

            if (!empty($includeIds)) {
                $tags = [];
                foreach ($includeIds as $id) {
                    $tag = API::tags()->getById($id);
                    if ($tag) {
                        $tags[] = $tag;
                    }
                }
            } else {
                $tags = API::tags()->getAll();
            }

            if (empty($tags)) {
                return [];
            }

            $filteredTags = [];
            foreach ($tags as $tag) {
                $tag['posts_count'] = $this->getTagPostsCount($tag['id']);
                
                if ($tag['posts_count'] >= $minPosts) {
                    if (!empty($excludeIds) && in_array($tag['id'], $excludeIds)) {
                        continue;
                    }
                    $filteredTags[] = $tag;
                }
            }

            list($field, $direction) = explode(' ', $orderBy);

            usort($filteredTags, function($a, $b) use ($field, $direction) {
                if ($field === 'posts_count') {
                    $aVal = (int)($a[$field] ?? 0);
                    $bVal = (int)($b[$field] ?? 0);
                    $result = $aVal - $bVal;
                } else {
                    $aVal = (string)($a[$field] ?? '');
                    $bVal = (string)($b[$field] ?? '');
                    $result = strcmp($aVal, $bVal);
                }

                return $direction === 'DESC' ? -$result : $result;
            });

            if ($limit > 0) {
                $filteredTags = array_slice($filteredTags, 0, $limit);
            }

            if (($settings['display_style'] ?? 'cloud') === 'cloud') {
                $filteredTags = $this->calculateTagWeights($filteredTags);
            }

            return $filteredTags;

        } catch (Exception $e) {
            return [];
        }
    }

    private function getTagPostsCount($tagId) {
        try {
            if (!API::hasModel('posts')) {
                return 0;
            }
            
            $allPosts = API::posts()->getAll();
            $count = 0;
            
            foreach ($allPosts as $post) {
                if ($post['status'] !== 'published') {
                    continue;
                }
                
                $postTags = API::posts()->getPostTags($post['id']);
                foreach ($postTags as $tag) {
                    if ($tag['id'] == $tagId) {
                        $count++;
                        break;
                    }
                }
            }
            
            return $count;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function calculateTagWeights($tags) {
        if (empty($tags)) {
            return [];
        }

        $minCount = min(array_column($tags, 'posts_count'));
        $maxCount = max(array_column($tags, 'posts_count'));
        $range = $maxCount - $minCount;

        if ($range == 0) {
            $range = 1;
        }

        foreach ($tags as &$tag) {
            $count = $tag['posts_count'] ?? 1;
            $weight = 1 + floor(($count - $minCount) / $range * 4);
            $tag['weight'] = (int)$weight;
            $tag['font_size'] = 0.8 + ($weight * 0.2);
        }

        return $tags;
    }

    public function getTagImageUrl($tag) {
        if (!empty($tag['image'])) {
            if (strpos($tag['image'], 'http') === 0 || strpos($tag['image'], '/') === 0) {
                return $tag['image'];
            }
            return '/uploads/tags/' . $tag['image'];
        }
        
        $defaultImage = \SettingsHelper::get('controller_tags', 'default_tag_image');
        if (!empty($defaultImage)) {
            return '/uploads/settings/tags/' . $defaultImage;
        }
        
        return '/uploads/default/default-tag.jpg';
    }

    public function processFrontend($settings = [], $templateName = null): string {

        $tags = $this->getTags($settings);

        $this->tags = $tags;

        return parent::processFrontend($settings, $templateName);
    }
}