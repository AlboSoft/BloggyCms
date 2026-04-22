<?php
class DefaultLatestPostsBlock extends BaseHtmlBlock {
    public function getName(): string {
        return "Посты блога";
    }
    
    public function getSystemName(): string {
        return "DefaultLatestPostsBlock";
    }
    
    public function getDescription(): string {
        return "Блок с постами блога с различными фильтрами";
    }
    
    public function getVersion(): string {
        return '1.0.0';
    }
    
    public function getTemplate(): string {
        return 'default';
    }

    public $posts = [];
    
    private function getCategoriesOptions(): array {
        $options = ['' => '-- Все категории --'];
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
        
        if ($diff < 60) return 'только что';
        
        $minutes = round($diff / 60);
        if ($minutes < 60) return $minutes . ' мин. назад';
        
        $hours = round($diff / 3600);
        if ($hours < 24) return $hours . ' ч. назад';
        
        $days = round($diff / 86400);
        if ($days < 7) return $days . ' ' . $this->declension($days, ['день', 'дня', 'дней']) . ' назад';
        
        $weeks = round($diff / 604800);
        if ($weeks < 5) return $weeks . ' ' . $this->declension($weeks, ['неделя', 'недели', 'недель']) . ' назад';
        
        $months = round($diff / 2592000);
        if ($months < 12) return $months . ' ' . $this->declension($months, ['месяц', 'месяца', 'месяцев']) . ' назад';
        
        $years = round($diff / 31536000);
        return $years . ' ' . $this->declension($years, ['год', 'года', 'лет']) . ' назад';
    }
    
    private function declension($number, $titles) {
        $cases = [2, 0, 1, 1, 1, 2];
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
        return $minutes . ' ' . $this->declension($minutes, ['мин. на чтение', 'мин. на чтение', 'мин. на чтение']);
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
        
        $fieldsets[] = new \Fieldset('Заголовочная часть', [
            'icon' => 'bi bi-pencil',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => 'Бейдж',
                    'default' => $settings['badge'] ?? 'Блог',
                    'placeholder' => 'Например: Последние статьи',
                ]),
                \FieldFactory::string('title', [
                    'title' => 'Заголовок',
                    'default' => $settings['title'] ?? 'Читайте <span class="highlight">свежие статьи</span>',
                    'placeholder' => 'Используйте <span class="highlight"> для выделения',
                ]),
                \FieldFactory::textarea('description', [
                    'title' => 'Описание',
                    'default' => $settings['description'] ?? 'Делюсь опытом, мыслями и инсайтами о разработке, архитектуре и не только.',
                    'rows' => 3,
                ]),
                \FieldFactory::select('align', [
                    'title' => 'Выравнивание заголовка',
                    'options' => [
                        'left' => 'Слева',
                        'center' => 'По центру',
                    ],
                    'default' => 'center',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset('Настройки постов', [
            'icon' => 'bi bi-card-list',
            'columns' => '12',
            'fields' => [
                \FieldFactory::number('posts_count', [
                    'title' => 'Количество постов',
                    'default' => 3,
                    'min' => 1,
                    'max' => 6,
                    'hint' => 'Сколько постов отображать',
                ]),
                \FieldFactory::select('columns', [
                    'title' => 'Количество колонок',
                    'options' => [
                        '2' => '2 колонки',
                        '3' => '3 колонки',
                    ],
                    'default' => '3',
                    'hint' => 'Для десктопа',
                ]),
                \FieldFactory::select('date_format', [
                    'title' => 'Формат даты',
                    'options' => [
                        'full' => 'Полная дата (6 марта 2026)',
                        'relative' => 'Относительная (2 дня назад)',
                    ],
                    'default' => 'full',
                ]),
                \FieldFactory::checkbox('show_featured_image', [
                    'title' => 'Показывать изображение',
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_excerpt', [
                    'title' => 'Показывать краткое описание',
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::number('excerpt_length', [
                    'title' => 'Длина описания (символов)',
                    'default' => 120,
                    'min' => 50,
                    'max' => 300,
                    'show' => 'field:show_excerpt',
                ]),
                \FieldFactory::checkbox('show_read_time', [
                    'title' => 'Показывать время чтения',
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_date', [
                    'title' => 'Показывать дату',
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_views', [
                    'title' => 'Показывать количество просмотров',
                    'default' => 0,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_category', [
                    'title' => 'Показывать категорию',
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_author', [
                    'title' => 'Показывать автора',
                    'default' => 0,
                    'switch' => true,
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset('Фильтрация', [
            'icon' => 'bi bi-funnel',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('filter_by_category', [
                    'title' => 'Фильтровать по категории',
                    'default' => 0,
                    'switch' => true,
                ]),
                \FieldFactory::select('category_id', [
                    'title' => 'Категория',
                    'options' => $this->getCategoriesOptions(),
                    'show' => 'field:filter_by_category',
                ]),
                \FieldFactory::checkbox('exclude_current_post', [
                    'title' => 'Исключить текущий пост',
                    'default' => 1,
                    'switch' => true,
                    'hint' => 'На странице поста не показывать его же в списке',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset('Сортировка', [
            'icon' => 'bi bi-arrow-up-short',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('order_by', [
                    'title' => 'Сортировка',
                    'options' => [
                        'created_at DESC' => 'Сначала новые',
                        'created_at ASC' => 'Сначала старые',
                        'title ASC' => 'По алфавиту',
                        'views DESC' => 'По просмотрам',
                    ],
                    'default' => 'created_at DESC',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset('Цвета и фон', [
            'icon' => 'bi bi-palette',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('theme', [
                    'title' => 'Тема',
                    'options' => [
                        'light' => 'Светлая',
                        'dark' => 'Темная',
                        'custom' => 'Своя',
                    ],
                    'default' => 'light',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => 'Цвет фона',
                    'preset' => 'basic',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => 'Цвет текста',
                    'preset' => 'basic',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => 'Акцентный цвет',
                    'preset' => 'website',
                    'default' => '#2563eb',
                ]),
                \FieldFactory::color('card_background', [
                    'title' => 'Цвет карточек',
                    'preset' => 'basic',
                    'default' => $settings['card_background'] ?? '',
                    'hint' => 'Оставьте пустым для автоматического',
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset('Отступы', [
            'icon' => 'bi bi-arrows-expand',
            'columns' => '12',
            'fields' => [
                \FieldFactory::number('padding_top', [
                    'title' => 'Отступ сверху (px)',
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => 'Отступ снизу (px)',
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                ]),
            ]
        ]);
        
        $fieldsets[] = new \Fieldset('Дополнительно', [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('custom_css_class', [
                    'title' => 'CSS класс',
                    'default' => $settings['custom_css_class'] ?? '',
                ]),
                \FieldFactory::string('custom_id', [
                    'title' => 'HTML ID',
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
