<?php
class DefaultLatestPostsBlock extends BaseHtmlBlock {
    public function getName(): string {
        return LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_NAME;
    }
    
    public function getSystemName(): string {
        return "DefaultLatestPostsBlock";
    }
    
    public function getDescription(): string {
        return LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_DESCRIPTION;
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

    public $posts = [];
    
    private function getCategoriesOptions(): array {
        $options = ['' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_CATEGORY_ALL];
        try {
            if (API::hasModel('categories')) {
                $categories = API::categories()->getAll();
                foreach ($categories as $category) {
                    $options[$category['id']] = htmlspecialchars($category['name']);
                }
            }
        } catch (Exception $e) {}
        return $options;
    }
    
    private function getCurrentPostId(): ?int {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            return (int)$_GET['id'];
        }
        if (isset($_GET['slug']) && API::hasModel('posts')) {
            $post = API::posts()->getBySlug($_GET['slug']);
            return $post ? (int)$post['id'] : null;
        }
        return null;
    }
    
    private function getPosts($settings, $currentPostId = null) {
        try {
            if (!API::hasModel('posts')) {
                return [];
            }
            
            $limit = (int)($settings['posts_count'] ?? 3);
            $posts = API::posts()->getPublished($limit);
            
            if (empty($posts)) {
                return [];
            }
            
            $posts = array_filter($posts, function($post) {
                return ($post['status'] ?? '') === 'published';
            });
            
            if (!empty($settings['filter_by_category']) && !empty($settings['category_id'])) {
                $categoryId = (int)$settings['category_id'];
                $posts = array_filter($posts, function($post) use ($categoryId) {
                    return ($post['category_id'] ?? 0) == $categoryId;
                });
            }
            
            if (!empty($settings['exclude_current_post']) && $currentPostId) {
                $posts = array_filter($posts, function($post) use ($currentPostId) {
                    return ($post['id'] ?? 0) != $currentPostId;
                });
            }

            if (!empty($settings['order_by']) && $settings['order_by'] !== 'created_at DESC') {
                list($field, $direction) = explode(' ', $settings['order_by']);
                usort($posts, function($a, $b) use ($field, $direction) {
                    $aVal = $a[$field] ?? '';
                    $bVal = $b[$field] ?? '';
                    if (is_numeric($aVal) && is_numeric($bVal)) {
                        $result = $aVal - $bVal;
                    } else {
                        $result = strcmp((string)$aVal, (string)$bVal);
                    }
                    return $direction === 'DESC' ? -$result : $result;
                });
            }
            
            $posts = array_slice($posts, 0, $limit);
            
            foreach ($posts as &$post) {
                $excerptLength = (int)($settings['excerpt_length'] ?? 120);
                
                if (!empty($post['short_description'])) {
                    $post['excerpt'] = truncate_text(strip_tags($post['short_description']), $excerptLength, '...');
                } elseif (!empty($post['content'])) {
                    $text = strip_tags($post['content']);
                    $text = preg_replace('/\s+/', ' ', $text);
                    $post['excerpt'] = truncate_text($text, $excerptLength, '...');
                } else {
                    $post['excerpt'] = '';
                }
                
                if (empty($post['author_name']) && !empty($post['user_id']) && API::hasModel('users')) {
                    try {
                        $author = API::users()->getById($post['user_id']);
                        $post['author_name'] = $author['display_name'] ?? $author['username'] ?? '';
                    } catch (Exception $e) {
                        $post['author_name'] = '';
                    }
                }
                
                if (empty($post['category_name']) && !empty($post['category_id']) && API::hasModel('categories')) {
                    try {
                        $category = API::categories()->getById($post['category_id']);
                        $post['category_name'] = $category['name'] ?? '';
                    } catch (Exception $e) {
                        $post['category_name'] = '';
                    }
                }
                
                if (!empty($post['content'])) {
                    $post['read_time'] = $this->calculateReadTime($post['content']);
                }
                
                if (!empty($post['created_at'])) {
                    $dateFormat = $settings['date_format'] ?? 'full';
                    if ($dateFormat === 'relative') {
                        $post['formatted_date'] = $this->getRelativeDate($post['created_at']);
                    } else {
                        $post['formatted_date'] = format_date($post['created_at']);
                    }
                }
                
                $post['views'] = $post['views'] ?? 0;
            }
            
            return $posts;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getRelativeDate($dateString) {
        $timestamp = strtotime($dateString);
        $diff = time() - $timestamp;
        
        if ($diff < 60) return LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_JUST_NOW;
        
        $minutes = round($diff / 60);
        if ($minutes < 60) return $minutes . ' ' . LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_MINUTES_AGO;
        
        $hours = round($diff / 3600);
        if ($hours < 24) return $hours . ' ' . LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_HOURS_AGO;
        
        $days = round($diff / 86400);
        if ($days < 7) return $days . ' ' . $this->declension($days, explode('|', LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_DAYS_DECLENSION)) . ' ' . LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_AGO;
        
        $weeks = round($diff / 604800);
        if ($weeks < 5) return $weeks . ' ' . $this->declension($weeks, explode('|', LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_WEEKS_DECLENSION)) . ' ' . LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_AGO;
        
        $months = round($diff / 2592000);
        if ($months < 12) return $months . ' ' . $this->declension($months, explode('|', LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_MONTHS_DECLENSION)) . ' ' . LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_AGO;
        
        $years = round($diff / 31536000);
        return $years . ' ' . $this->declension($years, explode('|', LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_YEARS_DECLENSION)) . ' ' . LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_AGO;
    }
    
    private function declension($number, $titles) {
        $cases = [2, 0, 1, 1, 1, 2];
        $titles = is_array($titles) ? $titles : explode('|', $titles);
        return $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
    
    public function calculateReadTime($content) {
        if (empty($content)) {
            return 1;
        }
        $wordCount = str_word_count(strip_tags($content));
        $minutes = ceil($wordCount / 200);
        return max(1, $minutes);
    }
    
    public function formatReadTime($minutes) {
        $minutes = (int)$minutes;
        return $minutes . ' ' . $this->declension($minutes, explode('|', LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_READ_TIME_DECLENSION));
    }
    
    public function getPostImageUrl($post) {
        if (!empty($post['featured_image'])) {
            if (strpos($post['featured_image'], 'http') === 0 || strpos($post['featured_image'], '/') === 0) {
                return $post['featured_image'];
            }
            return '/uploads/images/' . $post['featured_image'];
        }
        return '/templates/' . DEFAULT_TEMPLATE . '/front/assets/img/default-post.jpg';
    }
    
    public function getPostUrl($post) {
        if (!empty($post['slug'])) {
            return '/post/' . $post['slug'];
        }
        return '/post/' . ($post['id'] ?? '');
    }
    
    public function processFrontend($settings = [], $templateName = null): string {
        $currentPostId = $this->getCurrentPostId();
        $posts = $this->getPosts($settings, $currentPostId);
        $this->posts = $posts;
        return parent::processFrontend($settings, $templateName);
    }
    
    public function getSettingsForm($currentSettings = []): string {
        $settings = array_merge([], $currentSettings);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELDSET_HEADER, [
            'icon' => 'bi bi-pencil',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_BADGE,
                    'default' => $settings['badge'] ?? LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_DEFAULT_BADGE,
                    'placeholder' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_BADGE_PLACEHOLDER,
                ]),
                \FieldFactory::string('title', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_TITLE,
                    'default' => $settings['title'] ?? LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_DEFAULT_TITLE,
                    'placeholder' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_TITLE_PLACEHOLDER,
                ]),
                \FieldFactory::textarea('description', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_DESCRIPTION,
                    'default' => $settings['description'] ?? LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_DEFAULT_DESCRIPTION,
                    'rows' => 3,
                ]),
                \FieldFactory::select('align', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_ALIGN,
                    'options' => [
                        'left' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_ALIGN_LEFT,
                        'center' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_ALIGN_CENTER,
                    ],
                    'default' => 'center',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELDSET_POSTS, [
            'icon' => 'bi bi-card-list',
            'columns' => '12',
            'fields' => [
                \FieldFactory::number('posts_count', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_POSTS_COUNT,
                    'default' => 3,
                    'min' => 1,
                    'max' => 6,
                    'hint' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_POSTS_COUNT_HINT,
                ]),
                \FieldFactory::select('columns', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_COLUMNS,
                    'options' => [
                        '2' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_COLUMNS_2,
                        '3' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_COLUMNS_3,
                    ],
                    'default' => '3',
                    'hint' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_COLUMNS_HINT,
                ]),
                \FieldFactory::select('date_format', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_DATE_FORMAT,
                    'options' => [
                        'full' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_DATE_FORMAT_FULL,
                        'relative' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_DATE_FORMAT_RELATIVE,
                    ],
                    'default' => 'full',
                ]),
                \FieldFactory::checkbox('show_featured_image', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_SHOW_FEATURED_IMAGE,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_excerpt', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_SHOW_EXCERPT,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::number('excerpt_length', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_EXCERPT_LENGTH,
                    'default' => 120,
                    'min' => 50,
                    'max' => 300,
                    'show' => 'field:show_excerpt',
                ]),
                \FieldFactory::checkbox('show_read_time', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_SHOW_READ_TIME,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_date', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_SHOW_DATE,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_views', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_SHOW_VIEWS,
                    'default' => 0,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_category', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_SHOW_CATEGORY,
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_author', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_SHOW_AUTHOR,
                    'default' => 0,
                    'switch' => true,
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELDSET_FILTER, [
            'icon' => 'bi bi-funnel',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('filter_by_category', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_FILTER_BY_CATEGORY,
                    'default' => 0,
                    'switch' => true,
                ]),
                \FieldFactory::select('category_id', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_CATEGORY_ID,
                    'options' => $this->getCategoriesOptions(),
                    'show' => 'field:filter_by_category',
                ]),
                \FieldFactory::checkbox('exclude_current_post', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_EXCLUDE_CURRENT_POST,
                    'default' => 1,
                    'switch' => true,
                    'hint' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_EXCLUDE_CURRENT_POST_HINT,
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELDSET_SORTING, [
            'icon' => 'bi bi-arrow-up-short',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('order_by', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_ORDER_BY,
                    'options' => [
                        'created_at DESC' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_ORDER_NEWEST,
                        'created_at ASC' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_ORDER_OLDEST,
                        'title ASC' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_ORDER_ALPHABETICAL,
                        'views DESC' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_ORDER_VIEWS,
                    ],
                    'default' => 'created_at DESC',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELDSET_COLORS, [
            'icon' => 'bi bi-palette',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('theme', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_THEME,
                    'options' => [
                        'light' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_THEME_LIGHT,
                        'dark' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_THEME_DARK,
                        'custom' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_THEME_CUSTOM,
                    ],
                    'default' => 'light',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_BACKGROUND_COLOR,
                    'preset' => 'basic',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_TEXT_COLOR,
                    'preset' => 'basic',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_ACCENT_COLOR,
                    'preset' => 'website',
                    'default' => '#2563eb',
                ]),
                \FieldFactory::color('card_background', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_CARD_BACKGROUND,
                    'preset' => 'basic',
                    'default' => $settings['card_background'] ?? '',
                    'hint' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_CARD_BACKGROUND_HINT,
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELDSET_PADDING, [
            'icon' => 'bi bi-arrows-expand',
            'columns' => '12',
            'fields' => [
                \FieldFactory::number('padding_top', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_PADDING_TOP,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_PADDING_BOTTOM,
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset(LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELDSET_EXTRA, [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('custom_css_class', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_CSS_CLASS,
                    'default' => $settings['custom_css_class'] ?? '',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => LANG_HTMLBLOCK_DEFAULTLATESTPOSTS_FIELD_HTML_ID,
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
}