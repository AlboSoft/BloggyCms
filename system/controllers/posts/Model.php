<?php

/**
* Модель для работы с постами в базе данных
* @package Models
*/
class PostModel implements ModelAPI {
    use APIAware;

    protected $allowedAPIMethods = [
        'getAll',
        'getPublished',
        'getById',
        'getBySlug',
        'getLatest',
        'getPostTags',
        'hasUserLiked',
        'getLikesCount',
        'hasBookmark',
        'getBookmarksCount',
        'checkPostVisibility',
        'getPostsByCategory',
        'search',
        'getRelatedPosts',
        'getArchive'
    ];
    
    private $db;
    private $settings;
    private $fieldManager;
    
    /**
    * Конструктор модели
    * @param object $db Подключение к базе данных
    * @param Settings|null $settings Модель настроек (опционально)
    */
    public function __construct($db, Settings $settings = null) {
        $this->db = $db;
        $this->settings = $settings ?? new SettingsModel($db);
        $this->fieldManager = new FieldManager($db);
    }
    
    /**
    * Получает все посты с информацией о категориях и авторах 
    * @param int|null $limit Ограничение количества записей
    * @return array Массив постов
    */
    public function getAll($limit = null) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                    u.username as author_name, u.display_name as author_display_name, u.avatar as author_avatar
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC";
                
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $posts = $this->db->fetchAll($sql);
        
        foreach ($posts as &$post) {
            $post['author_slug'] = $post['author_name'] ?? 'author';
            $post['allow_comments'] = $post['allow_comments'] ?? 1;
        }
        
        return $posts;
    }
    
    /**
    * Получает пост по ID 
    * @param int $id ID поста
    * @return array|null Данные поста или null
    */
    public function getById($id) {
        $post = $this->db->fetch(
            "SELECT p.*, c.name as category_name 
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?", 
            [$id]
        );
        
        if ($post) {
            $post['allow_comments'] = $post['allow_comments'] ?? 1;
        }
        
        return $post;
    }
    
    /**
    * Получает пост по URL-адресу (slug) с полной информацией об авторе
    * @param string $slug URL-адрес поста
    * @return array|null Данные поста или null
    */
    public function getBySlug($slug) {
        $post = $this->db->fetch(
            "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                    c.password_protected as category_password_protected, c.id as category_id,
                    u.username as author_name, u.display_name as author_display_name, 
                    u.avatar as author_avatar, u.bio as author_bio, u.website as author_website,
                    u.email as author_email, u.role as author_role, u.status as author_status,
                    u.created_at as author_created_at
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.slug = ? AND p.status = 'published'",
            [$slug]
        );
        
        if ($post) {
            $post['author_slug'] = $post['author_name'] ?? 'author';
            $post['author_bio'] = $post['author_bio'] ?? '';
            $post['author_website'] = $post['author_website'] ?? '';
            $post['allow_comments'] = $post['allow_comments'] ?? 1;
        }
        
        return $post;
    }
    
    /**
    * Получает теги поста
    * @param int $post_id ID поста
    * @return array Массив тегов
    */
    public function getPostTags($post_id) {
        return $this->db->fetchAll(
            "SELECT t.* 
             FROM tags t 
             JOIN post_tags pt ON t.id = pt.tag_id 
             WHERE pt.post_id = ?", 
            [$post_id]
        );
    }
    
    /**
    * Создает новый пост
    * @param array $data Данные поста
    * @return int ID созданного поста
    * @throws Exception При ошибке создания
    */
    public function create($data) {
        try {
            $sql = "INSERT INTO posts (
                title, 
                short_description,
                slug, 
                category_id, 
                user_id, 
                status, 
                featured_image,
                meta_description,
                seo_title,
                meta_keywords,
                password_protected,
                password,
                show_to_groups,
                hide_from_groups,
                allow_comments,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $this->db->query($sql, [
                $data['title'],
                $data['short_description'] ?? null,
                $data['slug'],
                $data['category_id'],
                $data['user_id'],
                $data['status'] ?? 'draft',
                $data['featured_image'] ?? null,
                $data['meta_description'] ?? null,
                $data['seo_title'] ?? null,
                $data['meta_keywords'] ?? null,
                $data['password_protected'] ?? 0,
                $data['password'] ?? null,
                $data['show_to_groups'] ?? null,
                $data['hide_from_groups'] ?? null,
                $data['allow_comments'] ?? 1,
                $data['created_at'] ?? date('Y-m-d H:i:s')
            ]);

            $postId = $this->db->lastInsertId();

            Event::trigger('post.created', [
                $postId,
                $data
            ]);

            return $postId;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
    * Обновляет существующий пост
    * @param int $id ID поста
    * @param array $data Данные для обновления
    * @return bool Результат выполнения запроса
    * @throws Exception Если нет данных для обновления
    */
    public function update($id, $data) {
        try {

            $oldPost = $this->getById($id);
            
            if (!$oldPost) {
                throw new Exception('Пост не найден');
            }
            
            $updates = [];
            $params = [];
            
            $allowedFields = [
                'title', 'short_description', 'slug', 'category_id', 'status', 'featured_image', 
                'meta_description', 'seo_title', 'password_protected', 'password',
                'show_to_groups', 'hide_from_groups', 'allow_comments', 'created_at'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }
            
            $updates[] = "updated_at = CURRENT_TIMESTAMP";
            
            if (empty($updates)) {
                throw new Exception('Нет данных для обновления');
            }
            
            $params[] = $id;
            
            $sql = "UPDATE posts SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $result = $this->db->query($sql, $params);
            
            Event::trigger('post.updated', [
                $id,
                $oldPost,
                $data
            ]);
            
            if (isset($data['status']) && $data['status'] != $oldPost['status']) {
                Event::trigger('post.status_changed', [
                    $id,
                    $oldPost['status'],
                    $data['status']
                ]);
            }
            
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
    * Удаляет пост и все связанные с ним данные (теги, блоки) 
    * @param int $id ID поста
    * @return bool Результат выполнения запроса
    * @throws Exception При ошибке удаления
    */
    public function delete($id) {
        try {

            $post = $this->getById($id);
            
            if (!$post) {
                throw new Exception('Пост не найден');
            }
            
            $this->db->beginTransaction();
            $this->db->query("DELETE FROM post_blocks WHERE post_id = ?", [$id]);
            $this->db->query("DELETE FROM post_likes WHERE post_id = ?", [$id]);
            $this->db->query("DELETE FROM bookmarks WHERE post_id = ?", [$id]);
            $this->db->query("DELETE FROM comments WHERE post_id = ?", [$id]);
            $this->db->query("DELETE FROM post_tags WHERE post_id = ?", [$id]);
            $this->db->query("DELETE FROM field_values WHERE entity_type = 'post' AND entity_id = ?", [$id]);
            $result = $this->db->query("DELETE FROM posts WHERE id = ?", [$id]);
            $this->db->commit();

            Event::trigger('post.deleted', [
                $id,
                $post['title'],
                $post['slug']
            ]);
            
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
    * Создает уникальный URL-адрес (slug) из заголовка
    * @param string $title Заголовок поста
    * @param int|null $excludeId ID поста для исключения из проверки
    * @return string Уникальный URL-адрес
    */
    public function createSlug($title, $excludeId = null) {
        $baseSlug = $this->transliterate(mb_strtolower($title));
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
    * Проверяет существование URL-адреса 
    * @param string $slug URL-адрес
    * @param int|null $excludeId ID для исключения
    * @return bool true если существует
    */
    private function isSlugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM posts WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
    * Транслитерирует строку в латиницу и очищает для использования в URL 
    * @param string $string Исходная строка
    * @return string Транслитерированная строка
    */
    private function transliterate($string) {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya'
        );
        
        $string = strtr($string, $converter);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }

    /**
    * Получает посты с пагинацией и фильтрацией по видимости 
    * @param int $page Номер страницы
    * @param int|null $perPage Постов на странице
    * @param array $userGroups Группы пользователя
    * @return array Массив с постами и информацией о пагинации
    */
    public function getAllPaginated($page = 1, $perPage = null, $userGroups = []) {
        $page = (int)$page;
        
        if ($perPage === null) {
            $blogSettings = $this->settings->get('blog') ?? [];
            $perPage = isset($blogSettings['posts_per_page']) ? (int)$blogSettings['posts_per_page'] : 15;
        } else {
            $perPage = (int)$perPage;
        }
        
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql = "
            SELECT 
                p.*, 
                c.name as category_name, 
                c.slug as category_slug,
                u.username as author_name,
                u.display_name as author_display_name,
                u.avatar as author_avatar,
                u.bio as author_bio,
                u.website as author_website,
                GROUP_CONCAT(
                    CONCAT(t.name, ':::', t.slug) 
                    SEPARATOR '|||'
                ) as tag_data,
                COALESCE(cm.comments_count, 0) as comments_count
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as comments_count
                FROM comments 
                WHERE status = 'approved'
                GROUP BY post_id
            ) cm ON p.id = cm.post_id
            WHERE p.status = 'published'
            GROUP BY p.id, c.name, c.slug, u.username, u.display_name, 
                    u.avatar, u.bio, u.website, cm.comments_count
            ORDER BY p.created_at DESC";
        
        $allPosts = $this->db->fetchAll($sql);
        
        $visiblePosts = [];
        foreach ($allPosts as $post) {
            if ($this->checkPostVisibility($post['id'], $userGroups)) {
                $visiblePosts[] = $post;
            }
        }
        
        $totalPosts = count($visiblePosts);
        $posts = array_slice($visiblePosts, $offset, $perPage);
        
        foreach ($posts as &$post) {
            $tags = [];
            if (!empty($post['tag_data'])) {
                $tagItems = explode('|||', $post['tag_data']);
                foreach ($tagItems as $tagItem) {
                    if (strpos($tagItem, ':::') !== false) {
                        list($name, $slug) = explode(':::', $tagItem);
                        $tags[] = [
                            'name' => $name,
                            'slug' => $slug
                        ];
                    }
                }
            }
            $post['tags'] = $tags;
            unset($post['tag_data']);
            
            $post['comments_count'] = (int)($post['comments_count'] ?? 0);
            
            $post['author_slug'] = $post['author_name'] ?? 'author';
        }
        
        return [
            'posts' => $posts,
            'total' => $totalPosts,
            'pages' => $totalPosts > 0 ? ceil($totalPosts / $perPage) : 1,
            'current_page' => $page
        ];
    }

    /**
    * Получить количество комментариев для списка постов 
    * @param array $postIds Массив ID постов
    * @return array Массив с количеством комментариев для каждого поста
    */
    public function getCommentsCountForPosts(array $postIds) {
        if (empty($postIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $sql = "SELECT post_id, COUNT(*) as count 
                FROM comments 
                WHERE post_id IN ($placeholders) 
                AND status = 'approved'
                GROUP BY post_id";
        
        $result = $this->db->fetchAll($sql, $postIds);
        
        $counts = [];
        foreach ($result as $row) {
            $counts[$row['post_id']] = (int)$row['count'];
        }
        
        return $counts;
    }

    /**
    * Получить количество комментариев для поста
    * @param int $postId ID поста
    * @return int Количество комментариев
    */
    public function getCommentsCountByPost($postId) {
        $sql = "SELECT COUNT(*) as count FROM comments 
                WHERE post_id = ? AND status = 'approved'";
        
        $result = $this->db->fetch($sql, [$postId]);
        return $result['count'] ?? 0;
    }

    /**
    * Строит условие для фильтрации по видимости постов
    * @param array $userGroups Группы пользователя
    * @return array Массив с условием WHERE и параметрами
    */
    private function buildVisibilityCondition($userGroups = []) {
        $conditions = [];
        $params = [];
        
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            return ['where' => '', 'params' => []];
        }
        
        if (empty($userGroups)) {
            $userGroups = ['guest'];
        } else {
            $userGroups = array_unique($userGroups);
        }
        
        $showCondition = "(";
        $showCondition .= "show_to_groups IS NULL OR show_to_groups = '' OR show_to_groups = '[]' OR show_to_groups = '[\"\"]'";
        
        if (!empty($userGroups)) {
            foreach ($userGroups as $groupId) {
                $showCondition .= " OR JSON_CONTAINS(show_to_groups, ?)";
                $params[] = json_encode($groupId);
            }
        }
        $showCondition .= ")";
        
        $hideCondition = "(";
        $hideCondition .= "hide_from_groups IS NULL OR hide_from_groups = '' OR hide_from_groups = '[]' OR hide_from_groups = '[\"\"]'";
        
        if (!empty($userGroups)) {
            foreach ($userGroups as $groupId) {
                $hideCondition .= " AND NOT JSON_CONTAINS(hide_from_groups, ?)";
                $params[] = json_encode($groupId);
            }
        }
        $hideCondition .= ")";
        
        $where = "AND (" . $showCondition . " AND " . $hideCondition . ")";
        
        return ['where' => $where, 'params' => $params];
    }
    
    /**
    * Проверяет пароль для защищенного поста 
    * @param int $postId ID поста
    * @param string $password Введенный пароль
    * @return bool true если пароль верный
    */
    public function checkPassword($postId, $password) {
        $post = $this->getById($postId);
        
        if (!$post || !$post['password_protected']) {
            return true;
        }
        
        return $post['password'] === $password;
    }

    /**
    * Увеличивает счетчик просмотров поста
    * @param int $postId ID поста
    * @return void
    */
    public function incrementViews($postId) {
        $this->db->query(
            "UPDATE posts SET views = views + 1 WHERE id = ?",
            [$postId]
        );
    }
    
    /**
    * Получает количество просмотров поста 
    * @param int $postId ID поста
    * @return int Количество просмотров
    */
    public function getViews($postId) {
        $post = $this->getById($postId);
        return $post ? $post['views'] : 0;
    }

    /**
    * Получает рейтинг поста (количество лайков) 
    * @param int $postId ID поста
    * @return int Количество лайков
    */
    public function getRating($postId): int {
        $result = $this->db->fetch(
            "SELECT likes_count FROM posts WHERE id = ?",
            [$postId]
        );
        return $result ? (int)$result['likes_count'] : 0;
    }

    /**
    * Получает голос пользователя (для обратной совместимости)
    * @param int $postId ID поста
    * @param string $ipAddress IP-адрес
    * @return int|null Всегда null для новой системы
    */
    public function getUserVote($postId, $ipAddress): ?int {
        return null;
    }

    /**
    * Обрабатывает контент, заменяя шорткоды 
    * @param string $content Исходный контент
    * @return string Обработанный контент
    */
    public function processContent($content) {
        $htmlBlocks = $this->db->fetchAll("SELECT slug, content FROM html_blocks");
        $recentPosts = $this->getAll(10);
        
        return process_shortcodes($content, $recentPosts, $htmlBlocks);
    }

    /**
    * Создает пост с блоками контента 
    * @param array $data Данные поста
    * @param array $blocks Массив блоков
    * @return int ID созданного поста
    * @throws Exception При ошибке
    */
    public function createWithBlocks($data, $blocks) {
        try {
            $postId = $this->create($data);
            
            $postBlockModel = new PostBlock($this->db);
            foreach ($blocks as $order => $block) {
                $postBlockModel->create($postId, $block['type'], $block['content'], $order);
            }
            
            return $postId;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
    * Получает пользовательские поля для поста
    * @param int $postId ID поста
    * @return array Массив значений полей
    */
    public function getCustomFields($postId) {
        return $this->fieldManager->getFieldsForEntity('post', $postId);
    }
    
    /**
    * Сохраняет пользовательские поля для поста 
    * @param int $postId ID поста
    * @param array $fieldData Данные полей
    * @return void
    */
    public function saveCustomFields($postId, $fieldData) {
        foreach ($fieldData as $fieldId => $value) {
            $this->fieldManager->saveFieldValue($fieldId, 'post', $postId, $value);
        }
    }
    
    /**
    * Получает значение конкретного поля поста
    * @param int $postId ID поста
    * @param string $fieldSystemName Системное имя поля
    * @return mixed Значение поля
    */
    public function getFieldValue($postId, $fieldSystemName) {
        return $this->fieldManager->getFieldValue('post', $postId, $fieldSystemName);
    }

    /**
    * Получает посты с фильтрацией по категории и статусу 
    * @param int|null $categoryId ID категории
    * @param string|null $status Статус поста
    * @return array Массив постов
    */
    public function getAllWithFilters($categoryId = null, $status = null) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
    * Получает все посты пользователя 
    * @param int $userId ID пользователя
    * @return array Массив постов
    */
    public function getByUserId($userId) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.user_id = ? 
            ORDER BY p.created_at DESC",
            [$userId]
        );
    }

    /**
    * Получает опубликованные посты пользователя
    * @param int $userId ID пользователя
    * @return array Массив постов
    */
    public function getPublishedByUserId($userId) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.user_id = ? AND p.status = 'published'
            ORDER BY p.created_at DESC",
            [$userId]
        );
    }

    /**
    * Получает посты по тегу с пагинацией 
    * @param int $tagId ID тега
    * @param int $page Номер страницы
    * @param int $perPage Постов на странице
    * @param array $userGroups Группы пользователя
    * @return array Массив с постами и информацией о пагинации
    */
    public function getPostsByTag($tagId, $page = 1, $perPage = 10, $userGroups = []) {
        $offset = ($page - 1) * $perPage;
        
        $visibilityCondition = $this->buildVisibilityCondition($userGroups);
        
        $sql = "SELECT DISTINCT p.*, 
                        c.name as category_name,
                        c.slug as category_slug,
                        u.username as author_username,
                        u.display_name as author_display_name
                FROM posts p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN post_tags pt ON p.id = pt.post_id
                WHERE pt.tag_id = ? 
                AND p.status = 'published'
                {$visibilityCondition['where']}
                ORDER BY p.created_at DESC
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        $params = array_merge([$tagId], $visibilityCondition['params']);
        $posts = $this->db->fetchAll($sql, $params);
        
        $countSql = "SELECT COUNT(DISTINCT p.id) as total
                    FROM posts p
                    LEFT JOIN post_tags pt ON p.id = pt.post_id
                    WHERE pt.tag_id = ? 
                    AND p.status = 'published'
                    {$visibilityCondition['where']}";
        
        $totalResult = $this->db->fetch($countSql, $params);
        $total = $totalResult['total'];
        
        return [
            'posts' => $posts,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }
    
    /**
    * Получает статистику по постам для админ-панели
    * @return array Статистика по статусам
    */
    public function getAdminStats() {
        $stats = $this->db->fetchAll("
            SELECT 
                status,
                COUNT(*) as count
            FROM posts 
            GROUP BY status
        ");
        
        $total = $this->db->fetch("SELECT COUNT(*) as total FROM posts");
        
        return [
            'by_status' => $stats,
            'total' => $total['total'] ?? 0
        ];
    }
    
    /**
    * Получает посты для RSS-ленты 
    * @param int $limit Количество постов
    * @return array Массив постов
    */
    public function getForRss($limit = 20) {
        return $this->db->fetchAll("
            SELECT 
                p.*,
                c.name as category_name,
                c.slug as category_slug,
                u.username as author_name
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.status = 'published'
            ORDER BY p.created_at DESC
            LIMIT ?
        ", [$limit]);
    }
    
    /**
    * Поиск постов по заголовку и содержимому
    * @param string $query Поисковый запрос
    * @param int $page Номер страницы
    * @param int $perPage Постов на странице
    * @return array Массив с результатами поиска
    */
    public function search($query, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%{$query}%";
        
        $posts = $this->db->fetchAll("
            SELECT 
                p.*,
                c.name as category_name,
                c.slug as category_slug,
                MATCH(p.title, p.content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE (p.title LIKE ? OR p.content LIKE ?) 
            AND p.status = 'published'
            ORDER BY relevance DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ", [$query, $searchTerm, $searchTerm, $perPage, $offset]);
        
        $total = $this->db->fetch("
            SELECT COUNT(*) as total 
            FROM posts 
            WHERE (title LIKE ? OR content LIKE ?) 
            AND status = 'published'
        ", [$searchTerm, $searchTerm]);
        
        return [
            'posts' => $posts,
            'total' => $total['total'] ?? 0,
            'pages' => ceil(($total['total'] ?? 0) / $perPage),
            'current_page' => $page
        ];
    }
    
    /**
    * Получает похожие посты по тегам 
    * @param int $postId ID поста
    * @param int $limit Количество постов
    * @return array Массив похожих постов
    */
    public function getRelatedPosts($postId, $limit = 5) {
        return $this->db->fetchAll("
            SELECT DISTINCT p.*, c.name as category_name, c.slug as category_slug
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            INNER JOIN post_tags pt ON p.id = pt.post_id
            WHERE pt.tag_id IN (
                SELECT tag_id FROM post_tags WHERE post_id = ?
            )
            AND p.id != ?
            AND p.status = 'published'
            ORDER BY p.created_at DESC
            LIMIT ?
        ", [$postId, $postId, $limit]);
    }
    
    /**
    * Получает архив по годам и месяцам 
    * @return array Массив с информацией об архиве
    */
    public function getArchive() {
        return $this->db->fetchAll("
            SELECT 
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as post_count
            FROM posts 
            WHERE status = 'published'
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY year DESC, month DESC
        ");
    }
    
    /**
    * Получает посты за указанный месяц и год 
    * @param int $year Год
    * @param int $month Месяц
    * @return array Массив постов
    */
    public function getPostsByArchive($year, $month) {
        $year = (int)$year;
        $month = (int)$month;
        
        $posts = $this->db->fetchAll("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE YEAR(p.created_at) = ? 
            AND MONTH(p.created_at) = ?
            AND p.status = 'published'
            ORDER BY p.created_at DESC
        ", [$year, $month]);
        
        return $posts;
    }

    /**
    * Проверяет видимость поста для пользователя
    * @param int $postId ID поста
    * @param array $userGroups Группы пользователя
    * @return bool true если пост виден
    */
    public function checkPostVisibility($postId, $userGroups = []) {
        $post = $this->getById($postId);
        
        if (!$post) {
            return false;
        }
        
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            return true;
        }
        
        if (empty($userGroups)) {
            $userGroups = ['guest'];
        }

        $userGroups = array_map('strval', $userGroups);
        
        $showToGroups = [];
        $hideFromGroups = [];
        
        if (!empty($post['show_to_groups']) && $post['show_to_groups'] !== '[]' && $post['show_to_groups'] !== '[""]') {
            $decoded = json_decode($post['show_to_groups'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $showToGroups = array_filter($decoded, function($value) {
                    return !empty($value);
                });
            }
        }
        
        if (!empty($post['hide_from_groups']) && $post['hide_from_groups'] !== '[]' && $post['hide_from_groups'] !== '[""]') {
            $decoded = json_decode($post['hide_from_groups'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $hideFromGroups = array_filter($decoded, function($value) {
                    return !empty($value);
                });
            }
        }

        if (!empty($showToGroups)) {
            $intersection = array_intersect($showToGroups, $userGroups);
            if (empty($intersection)) {
                return false;
            }
        }
        
        if (!empty($hideFromGroups)) {
            $intersection = array_intersect($hideFromGroups, $userGroups);
            if (!empty($intersection)) {
                return false;
            }
        }
        
        return true;
    }

    /**
    * Проверяет, поставил ли пользователь лайк посту 
    * @param int $postId ID поста
    * @param int $userId ID пользователя
    * @return bool true если лайк есть
    */
    public function hasUserLiked($postId, $userId): bool {
        $like = $this->db->fetch(
            "SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?",
            [$postId, $userId]
        );
        return !empty($like);
    }

    /**
    * Переключает лайк поста (добавляет или удаляет)
    * @param int $postId ID поста
    * @param int $userId ID пользователя
    * @return array Массив с новым статусом и количеством лайков
    * @throws Exception При ошибке
    */
    public function toggleLike($postId, $userId): array {
        try {
            $hasLiked = $this->hasUserLiked($postId, $userId);
            
            if ($hasLiked) {
                $this->db->query(
                    "DELETE FROM post_likes WHERE post_id = ? AND user_id = ?",
                    [$postId, $userId]
                );
                
                $this->db->query(
                    "UPDATE posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?",
                    [$postId]
                );
                
                return [
                    'liked' => false,
                    'likes_count' => $this->getRating($postId)
                ];
            } else {
                $this->db->query(
                    "INSERT INTO post_likes (post_id, user_id, created_at) VALUES (?, ?, NOW())",
                    [$postId, $userId]
                );
                
                $this->db->query(
                    "UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?",
                    [$postId]
                );
                
                return [
                    'liked' => true,
                    'likes_count' => $this->getRating($postId)
                ];
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при обработке лайка: ' . $e->getMessage());
        }
    }

    /**
    * Получает количество лайков поста
    * @param int $postId ID поста
    * @return int Количество лайков
    */
    public function getLikesCount($postId): int {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM post_likes WHERE post_id = ?",
            [$postId]
        );
        return (int)($result['count'] ?? 0);
    }

    /**
    * Проверяет, добавлен ли пост в закладки пользователем 
    * @param int $postId ID поста
    * @param int $userId ID пользователя
    * @return bool true если в закладках
    */
    public function hasBookmark($postId, $userId): bool {
        $bookmark = $this->db->fetch(
            "SELECT id FROM bookmarks WHERE post_id = ? AND user_id = ?",
            [$postId, $userId]
        );
        return !empty($bookmark);
    }

    /**
    * Переключает закладку поста (добавляет или удаляет) 
    * @param int $postId ID поста
    * @param int $userId ID пользователя
    * @return array Массив с новым статусом и сообщением
    * @throws Exception При ошибке
    */
    public function toggleBookmark($postId, $userId): array {
        try {
            $hasBookmark = $this->hasBookmark($postId, $userId);
            
            if ($hasBookmark) {
                $this->db->query(
                    "DELETE FROM bookmarks WHERE post_id = ? AND user_id = ?",
                    [$postId, $userId]
                );
                
                return [
                    'bookmarked' => false,
                    'message' => 'Пост удален из закладок'
                ];
            } else {
                $this->db->query(
                    "INSERT INTO bookmarks (post_id, user_id, created_at) VALUES (?, ?, NOW())",
                    [$postId, $userId]
                );
                
                return [
                    'bookmarked' => true,
                    'message' => 'Пост добавлен в закладки'
                ];
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при обработке закладки: ' . $e->getMessage());
        }
    }

    /**
    * Получает закладки пользователя с пагинацией
    * @param int $userId ID пользователя
    * @param int $page Номер страницы
    * @param int $perPage Закладок на странице
    * @return array Массив с постами и информацией о пагинации
    */
    public function getUserBookmarks($userId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                        b.created_at as bookmarked_at
                FROM bookmarks b
                JOIN posts p ON b.post_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE b.user_id = ? AND p.status = 'published'
                ORDER BY b.created_at DESC
                LIMIT ? OFFSET ?";
        
        $posts = $this->db->fetchAll($sql, [$userId, $perPage, $offset]);
        
        $total = $this->db->fetch(
            "SELECT COUNT(*) as total 
            FROM bookmarks b
            JOIN posts p ON b.post_id = p.id
            WHERE b.user_id = ? AND p.status = 'published'",
            [$userId]
        );
        
        return [
            'posts' => $posts,
            'total' => $total['total'] ?? 0,
            'pages' => ceil(($total['total'] ?? 0) / $perPage),
            'current_page' => $page
        ];
    }

    /**
    * Получает количество закладок пользователя 
    * @param int $userId ID пользователя
    * @return int Количество закладок
    */
    public function getBookmarksCount($userId): int {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ?",
            [$userId]
        );
        return (int)($result['count'] ?? 0);
    }

    /**
    * Получает только опубликованные посты 
    * @param int|null $limit Ограничение количества
    * @return array Массив опубликованных постов
    */
    public function getPublished($limit = null) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                    u.username as author_name, u.display_name as author_display_name,
                    u.avatar as author_avatar
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.status = 'published'
                ORDER BY p.created_at DESC";
                
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        return $this->db->fetchAll($sql);
    }
    
}