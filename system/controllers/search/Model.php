<?php

/**
* Модель для работы с поиском и историей поисковых запросов 
* @package Models
*/
class SearchModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'searchAll',
        'searchPosts',
        'searchPages',
        'searchCategories',
        'searchTags',
        'searchUsers',
        'getSuggestedSearches',
        'saveSearchQuery'
    ];
    
    private $db;
    private $settings;
    
    /**
    * Конструктор модели
    * @param object $db Подключение к базе данных
    * @param SettingsModel|null $settings Модель настроек
    */
    public function __construct($db, SettingsModel $settings = null) {
        $this->db = $db;
        $this->settings = $settings ?? new SettingsModel($db);
    }
    
    /**
    * Поиск по всем типам контента 
    * @param string $query Поисковый запрос
    * @param string $type Тип контента (all, posts, pages, categories, tags, users)
    * @param int $page Номер страницы
    * @param int|null $perPage Количество результатов на странице
    * @return array Массив с результатами поиска
    */
    public function searchAll($query, $type = 'all', $page = 1, $perPage = null) {
        $perPage = $perPage ?? $this->settings->get('search')['search_per_page'] ?? 15;
        $offset = ($page - 1) * $perPage;
        
        $results = [];
        $total = 0;
        
        switch ($type) {
            case 'posts':
                return $this->searchPosts($query, $page, $perPage);
            case 'pages':
                return $this->searchPages($query, $page, $perPage);
            case 'categories':
                return $this->searchCategories($query, $page, $perPage);
            case 'tags':
                return $this->searchTags($query, $page, $perPage);
            case 'users':
                return $this->searchUsers($query, $page, $perPage);
            case 'all':
            default:
                $posts = $this->searchPosts($query, 1, 5);
                $pages = $this->searchPages($query, 1, 5);
                $categories = $this->searchCategories($query, 1, 5);
                $tags = $this->searchTags($query, 1, 5);
                $users = $this->searchUsers($query, 1, 5);
                
                $total = $posts['total'] + $pages['total'] + $categories['total'] + $tags['total'] + $users['total'];
                
                $items = [];
                
                foreach ($posts['items'] as $item) {
                    $item['type'] = 'post';
                    $items[] = $item;
                }
                
                foreach ($pages['items'] as $item) {
                    $item['type'] = 'page';
                    $items[] = $item;
                }
                
                foreach ($categories['items'] as $item) {
                    $item['type'] = 'category';
                    $items[] = $item;
                }
                
                foreach ($tags['items'] as $item) {
                    $item['type'] = 'tag';
                    $items[] = $item;
                }
                
                foreach ($users['items'] as $item) {
                    $item['type'] = 'user';
                    $items[] = $item;
                }
                
                usort($items, function($a, $b) {
                    $dateA = isset($a['created_at']) ? $a['created_at'] : (isset($a['registered_at']) ? $a['registered_at'] : '');
                    $dateB = isset($b['created_at']) ? $b['created_at'] : (isset($b['registered_at']) ? $b['registered_at'] : '');
                    return strtotime($dateB) - strtotime($dateA);
                });

                $paginatedItems = array_slice($items, $offset, $perPage);
                
                return [
                    'items' => $paginatedItems,
                    'total' => $total,
                    'pages' => ceil($total / $perPage),
                    'current_page' => $page,
                    'query' => $query,
                    'type' => $type
                ];
        }
    }
    
    /**
    * Поиск по постам
    * @param string $query Поисковый запрос
    * @param int $page Номер страницы
    * @param int $perPage Количество результатов на странице
    * @return array Массив с результатами
    */
    public function searchPosts($query, $page = 1, $perPage = 15) {
        $searchQuery = '%' . $query . '%';
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(DISTINCT p.id) as count 
                     FROM posts p 
                     LEFT JOIN post_blocks pb ON p.id = pb.post_id
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE (p.title LIKE ? 
                            OR p.short_description LIKE ? 
                            OR pb.content LIKE ?
                            OR c.name LIKE ?) 
                     AND p.status = 'published'";
        
        $countResult = $this->db->fetch($countSql, [
            $searchQuery, $searchQuery, $searchQuery, $searchQuery
        ]);
        $total = isset($countResult['count']) ? $countResult['count'] : 0;
        $sql = "SELECT DISTINCT 
                    p.id,
                    p.title,
                    p.slug,
                    p.short_description as description,
                    p.featured_image as image,
                    p.show_cover_in_list,
                    p.created_at,
                    p.views,
                    c.name as category_name,
                    c.slug as category_slug,
                    'post' as content_type
                FROM posts p 
                LEFT JOIN post_blocks pb ON p.id = pb.post_id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE (p.title LIKE ? 
                       OR p.short_description LIKE ? 
                       OR pb.content LIKE ?
                       OR c.name LIKE ?) 
                AND p.status = 'published'
                GROUP BY p.id
                ORDER BY 
                    CASE 
                        WHEN p.title LIKE ? THEN 1
                        WHEN p.short_description LIKE ? THEN 2
                        ELSE 3
                    END,
                    p.created_at DESC
                LIMIT " . (int)$perPage . " 
                OFFSET " . (int)$offset;
        
        $items = $this->db->fetchAll($sql, [
            $searchQuery, $searchQuery, $searchQuery, $searchQuery,
            $searchQuery, $searchQuery
        ]);
        foreach ($items as &$item) {
            $tagsSql = "SELECT t.name, t.slug 
                       FROM tags t
                       JOIN post_tags pt ON t.id = pt.tag_id
                       WHERE pt.post_id = ?";
            $item['tags'] = $this->db->fetchAll($tagsSql, [$item['id']]);
            $item['url'] = '/post/' . $item['slug'];
        }
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'query' => $query
        ];
    }
    
    /**
    * Поиск по страницам 
    * @param string $query Поисковый запрос
    * @param int $page Номер страницы
    * @param int $perPage Количество результатов на странице
    * @return array Массив с результатами
    */
    public function searchPages($query, $page = 1, $perPage = 15) {
        $searchQuery = '%' . $query . '%';
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(DISTINCT p.id) as count 
                     FROM pages p 
                     LEFT JOIN page_blocks pb ON p.id = pb.page_id
                     WHERE (p.title LIKE ? OR pb.content LIKE ?) 
                     AND p.status = 'published'";
        
        $countResult = $this->db->fetch($countSql, [$searchQuery, $searchQuery]);
        $total = isset($countResult['count']) ? $countResult['count'] : 0;
        $sql = "SELECT DISTINCT 
                    p.id,
                    p.title,
                    p.slug,
                    '' as description,
                    NULL as image,
                    p.created_at,
                    0 as views,
                    '' as category_name,
                    '' as category_slug,
                    'page' as content_type
                FROM pages p 
                LEFT JOIN page_blocks pb ON p.id = pb.page_id
                WHERE (p.title LIKE ? OR pb.content LIKE ?) 
                AND p.status = 'published'
                ORDER BY 
                    CASE 
                        WHEN p.title LIKE ? THEN 1
                        ELSE 2
                    END,
                    p.created_at DESC
                LIMIT " . (int)$perPage . " 
                OFFSET " . (int)$offset;
        
        $items = $this->db->fetchAll($sql, [
            $searchQuery, $searchQuery,
            $searchQuery
        ]);
        
        foreach ($items as &$item) {
            $item['url'] = '/page/' . $item['slug'];
        }
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'query' => $query
        ];
    }
    
    /**
    * Поиск по категориям 
    * @param string $query Поисковый запрос
    * @param int $page Номер страницы
    * @param int $perPage Количество результатов на странице
    * @return array Массив с результатами
    */
    public function searchCategories($query, $page = 1, $perPage = 15) {
        $searchQuery = '%' . $query . '%';
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as count 
                     FROM categories 
                     WHERE name LIKE ? OR description LIKE ?";
        
        $countResult = $this->db->fetch($countSql, [$searchQuery, $searchQuery]);
        $total = isset($countResult['count']) ? $countResult['count'] : 0;
        $sql = "SELECT 
                    id,
                    name as title,
                    slug,
                    description,
                    image,
                    created_at,
                    'category' as content_type
                FROM categories 
                WHERE name LIKE ? OR description LIKE ?
                ORDER BY name ASC
                LIMIT " . (int)$perPage . " 
                OFFSET " . (int)$offset;
        
        $items = $this->db->fetchAll($sql, [$searchQuery, $searchQuery]);
        
        foreach ($items as &$item) {
            $countSql = "SELECT COUNT(*) as count FROM posts WHERE category_id = ? AND status = 'published'";
            $countResult = $this->db->fetch($countSql, [$item['id']]);
            $item['posts_count'] = isset($countResult['count']) ? $countResult['count'] : 0;
            $item['url'] = '/category/' . $item['slug'];
        }
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'query' => $query
        ];
    }
    
    /**
    * Поиск по тегам 
    * @param string $query Поисковый запрос
    * @param int $page Номер страницы
    * @param int $perPage Количество результатов на странице
    * @return array Массив с результатами
    */
    public function searchTags($query, $page = 1, $perPage = 15) {
        $searchQuery = '%' . $query . '%';
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as count FROM tags WHERE name LIKE ?";
        $countResult = $this->db->fetch($countSql, [$searchQuery]);
        $total = isset($countResult['count']) ? $countResult['count'] : 0;
        $sql = "SELECT 
                    id,
                    name as title,
                    slug,
                    NULL as description,
                    image,
                    created_at,
                    'tag' as content_type
                FROM tags 
                WHERE name LIKE ?
                ORDER BY name ASC
                LIMIT " . (int)$perPage . " 
                OFFSET " . (int)$offset;
        
        $items = $this->db->fetchAll($sql, [$searchQuery]);

        foreach ($items as &$item) {
            $countSql = "SELECT COUNT(*) as count FROM post_tags WHERE tag_id = ?";
            $countResult = $this->db->fetch($countSql, [$item['id']]);
            $item['posts_count'] = isset($countResult['count']) ? $countResult['count'] : 0;
            $item['url'] = '/tag/' . $item['slug'];
        }
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'query' => $query
        ];
    }
    
    /**
    * Поиск по пользователям 
    * @param string $query Поисковый запрос
    * @param int $page Номер страницы
    * @param int $perPage Количество результатов на странице
    * @return array Массив с результатами
    */
    public function searchUsers($query, $page = 1, $perPage = 15) {
        $searchQuery = '%' . $query . '%';
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as count 
                     FROM users 
                     WHERE username LIKE ? OR display_name LIKE ? OR bio LIKE ?";
        
        $countResult = $this->db->fetch($countSql, [$searchQuery, $searchQuery, $searchQuery]);
        $total = isset($countResult['count']) ? $countResult['count'] : 0;
        $sql = "SELECT 
                    id,
                    COALESCE(display_name, username) as title,
                    username as slug,
                    bio as description,
                    avatar as image,
                    created_at as registered_at,
                    'user' as content_type
                FROM users 
                WHERE username LIKE ? OR display_name LIKE ? OR bio LIKE ?
                ORDER BY created_at DESC
                LIMIT " . (int)$perPage . " 
                OFFSET " . (int)$offset;
        
        $items = $this->db->fetchAll($sql, [$searchQuery, $searchQuery, $searchQuery]);
        
        foreach ($items as &$item) {
            $countSql = "SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND status = 'published'";
            $countResult = $this->db->fetch($countSql, [$item['id']]);
            $item['posts_count'] = isset($countResult['count']) ? $countResult['count'] : 0;
            $item['url'] = '/user/' . $item['slug'];
        }
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'query' => $query
        ];
    }
    
    /**
    * Получение популярных поисковых запросов (через теги) 
    * @param int $limit Количество запросов (по умолчанию 6)
    * @return array Массив с популярными поисковыми запросами
    */
    public function getSuggestedSearches($limit = 6) {
        $sql = "SELECT t.name as query, t.slug, COUNT(pt.post_id) as count
                FROM tags t
                JOIN post_tags pt ON t.id = pt.tag_id
                GROUP BY t.id
                ORDER BY count DESC
                LIMIT " . (int)$limit;
        
        return $this->db->fetchAll($sql);
    }
    
    /**
    * Получение популярных поисковых запросов из истории
    * @param int $limit Количество запросов
    * @return array Массив с популярными запросами
    */
    public function getPopularSearchQueries($limit = 10) {
        $limit = (int)$limit;
        return $this->db->fetchAll(
            "SELECT id, query, count, last_searched_at, created_at 
             FROM search_queries 
             ORDER BY count DESC, last_searched_at DESC 
             LIMIT $limit"
        );
    }
    
    /**
    * Получение последних поисковых запросов
    * @param int $limit Количество запросов
    * @return array Массив с последними запросами
    */
    public function getRecentSearchQueries($limit = 10) {
        $limit = (int)$limit;
        return $this->db->fetchAll(
            "SELECT id, query, count, last_searched_at, created_at 
             FROM search_queries 
             ORDER BY last_searched_at DESC 
             LIMIT $limit"
        );
    }
    
    /**
    * Сохранение поискового запроса в историю 
    * @param string $query Поисковый запрос
    * @return void
    */
    public function saveSearchQuery($query) {
        $query = trim($query);
        if (empty($query) || mb_strlen($query) < 2) {
            return;
        }
        
        $existingQuery = $this->db->fetch(
            "SELECT id, count FROM search_queries WHERE query = ?", 
            [$query]
        );
        
        if ($existingQuery) {
            $this->db->query(
                "UPDATE search_queries SET count = count + 1, last_searched_at = NOW() WHERE id = ?", 
                [$existingQuery['id']]
            );
        } else {
            $this->db->query(
                "INSERT INTO search_queries (query, count) VALUES (?, 1)", 
                [$query]
            );
        }
    }
    
    /**
    * Получение всех поисковых запросов с пагинацией 
    * @param int $page Номер страницы
    * @param int $perPage Количество запросов на странице
    * @return array Массив с запросами
    */
    public function getAllSearchQueries($page = 1, $perPage = 20) {
        $page = (int)$page;
        $perPage = (int)$perPage;
    
        $totalQueries = $this->db->fetch(
            "SELECT COUNT(*) as count FROM search_queries"
        );
        $total = isset($totalQueries['count']) ? $totalQueries['count'] : 0;
    
        $offset = ($page - 1) * $perPage;
    
        $sql = "SELECT id, query, count, last_searched_at, created_at 
                FROM search_queries 
                ORDER BY last_searched_at DESC 
                LIMIT $perPage OFFSET $offset";
    
        $queries = $this->db->fetchAll($sql);
    
        return [
            'queries' => $queries,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }
    
    /**
    * Удаление конкретного поискового запроса 
    * @param int $id ID запроса
    * @return bool Результат операции
    */
    public function deleteSearchQuery($id) {
        return $this->db->query(
            "DELETE FROM search_queries WHERE id = ?", 
            [$id]
        );
    }
    
    /**
    * Очистка всей истории поисковых запросов 
    * @return bool Результат операции
    */
    public function clearSearchHistory() {
        return $this->db->query("TRUNCATE TABLE search_queries");
    }
}