<?php

/**
* Модель для работы с тегами в базе данных
* @package Models
*/
class TagModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getAllPaginated',
        'getAll',
        'getFilteredTags',
        'getById',
        'getBySlug',
        'getForPost',
        'getPostsByTag',
        'searchByName'
    ];
    
    private $db;
    
    /**
    * Конструктор модели 
    * @param object $db Подключение к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
    * Получить все теги с пагинацией
    */
    public function getAllPaginated($page = 1, $perPage = 12, $orderBy = 'name') {
        $offset = ($page - 1) * $perPage;
        
        $orderClause = match($orderBy) {
            'posts_count' => 'ORDER BY posts_count DESC, t.name ASC',
            'created_at' => 'ORDER BY t.created_at DESC, t.name ASC', 
            default => 'ORDER BY t.name ASC'
        };
        
        $sql = "SELECT t.*, COUNT(pt.post_id) as posts_count 
                FROM tags t 
                LEFT JOIN post_tags pt ON t.id = pt.tag_id 
                GROUP BY t.id 
                {$orderClause}
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        $tags = $this->db->fetchAll($sql);
        
        $countSql = "SELECT COUNT(DISTINCT t.id) as total 
                    FROM tags t";
        $totalResult = $this->db->fetch($countSql);
        $total = $totalResult['total'];
        
        return [
            'tags' => $tags,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_items' => (int)$total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }
    
    /**
    * Получить все теги без пагинации
    * @return array Массив всех тегов
    */
    public function getAll($orderBy = 'name') {
        $orderClause = match($orderBy) {
            'posts_count' => 'ORDER BY posts_count DESC, t.name ASC',
            'created_at' => 'ORDER BY t.created_at DESC, t.name ASC', 
            default => 'ORDER BY t.name ASC'
        };
        
        $sql = "SELECT t.*, COUNT(pt.post_id) as posts_count 
                FROM tags t 
                LEFT JOIN post_tags pt ON t.id = pt.tag_id 
                GROUP BY t.id 
                {$orderClause}";
        
        return $this->db->fetchAll($sql);
    }

    /**
    * Получить теги с фильтрацией по минимальному количеству постов
    */
    public function getFilteredTags($minPosts = 1, $page = 1, $perPage = 12, $orderBy = 'name') {
        $offset = ($page - 1) * $perPage;
        
        $orderClause = match($orderBy) {
            'posts_count' => 'ORDER BY posts_count DESC, t.name ASC',
            'created_at' => 'ORDER BY t.created_at DESC, t.name ASC', 
            default => 'ORDER BY t.name ASC'
        };
        
        $sql = "SELECT t.*, COUNT(pt.post_id) as posts_count 
                FROM tags t 
                LEFT JOIN post_tags pt ON t.id = pt.tag_id 
                GROUP BY t.id 
                HAVING posts_count >= ?
                {$orderClause}
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        $tags = $this->db->fetchAll($sql, [$minPosts]);
        
        $countSql = "SELECT COUNT(*) as total FROM (
                        SELECT t.id, COUNT(pt.post_id) as posts_count 
                        FROM tags t 
                        LEFT JOIN post_tags pt ON t.id = pt.tag_id 
                        GROUP BY t.id 
                        HAVING posts_count >= ?
                    ) as filtered_tags";
        $totalResult = $this->db->fetch($countSql, [$minPosts]);
        $total = $totalResult['total'];
        
        return [
            'tags' => $tags,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_items' => (int)$total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }
    
    /**
    * Получить тег по ID
    * @param int $id ID тега
    * @return array|null Данные тега или null
    */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM tags WHERE id = ?", [$id]);
    }
    
    /**
    * Получить тег по URL-адресу (slug) 
    * @param string $slug URL-адрес тега
    * @return array|null Данные тега или null
    */
    public function getBySlug($slug) {
        return $this->db->fetch("SELECT * FROM tags WHERE slug = ?", [$slug]);
    }

    /**
    * Получить теги, привязанные к посту 
    * @param int $post_id ID поста
    * @return array Массив тегов
    */
    public function getForPost($post_id) {
        return $this->db->fetchAll(
            "SELECT t.* 
             FROM tags t 
             JOIN post_tags pt ON t.id = pt.tag_id 
             WHERE pt.post_id = ?",
            [$post_id]
        );
    }    
    
    /**
    * Создать новый тег 
    * @param array $data Данные тега (name, image, description)
    * @return int ID созданного тега
    */
    public function create($data) {
        $slug = $this->createUniqueSlug($data['name']);

        $sql = "INSERT INTO tags (name, slug, image, description) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $slug,
            $data['image'] ?? null,
            $data['description'] ?? null
        ]);

        $tagId = $this->db->lastInsertId();

        Event::trigger('tag.created', [
            $tagId,
            $data['name'],
            $slug,
            $data
        ]);
        
        return $tagId;
    }
    
    /**
    * Обновить существующий тег
    * @param int $id ID тега
    * @param array $data Данные для обновления
    * @return bool Результат выполнения запроса
    */
    public function update($id, $data) {

        $oldTag = $this->getById($id);
        
        if (!$oldTag) {
            throw new Exception('Тег не найден');
        }
        
        $slug = $this->createUniqueSlug($data['name'], $id);

        $sql = "UPDATE tags SET name = ?, slug = ?, image = ?, description = ? WHERE id = ?";
        
        $result = $this->db->query($sql, [
            $data['name'],
            $slug,
            $data['image'] ?? null,
            $data['description'] ?? null,
            $id
        ]);

        Event::trigger('tag.updated', [
            $id,
            $oldTag,
            $data
        ]);
        
        return $result;
    }
    
    /**
    * Удалить тег и все связи с постами 
    * @param int $id ID тега
    * @return bool Результат выполнения запроса
    */
    public function delete($id) {

        $tag = $this->getById($id);
        
        if (!$tag) {
            throw new Exception('Тег не найден');
        }
        
        $this->db->query("DELETE FROM post_tags WHERE tag_id = ?", [$id]);
        
        $result = $this->db->query("DELETE FROM tags WHERE id = ?", [$id]);

        Event::trigger('tag.deleted', [
            $id,
            $tag['name'],
            $tag['slug']
        ]);
        
        return $result;
    }
    
    /**
    * Создает уникальный URL-адрес (slug) из названия тега
    * @param string $name Название тега
    * @param int|null $excludeId ID тега для исключения из проверки
    * @return string Уникальный URL-адрес
    */
    private function createUniqueSlug($name, $excludeId = null) {
        $baseSlug = $this->generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
    * Проверяет, существует ли тег с таким названием
    * @param string $name Название для проверки
    * @param int|null $excludeId ID для исключения
    * @return bool true если существует
    */
    public function isNameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM tags WHERE name = ?";
        $params = [$name];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
    * Создает URL-адрес из названия (без проверки уникальности)
    * @param string $name Название тега
    * @return string URL-адрес
    */
    public function createSlugFromName($name) {
        return $this->generateSlug($name);
    }

    /**
    * Получает посты, привязанные к тегу, с пагинацией
    * @param int $tagId ID тега
    * @param int $page Номер страницы
    * @param int $perPage Постов на странице
    * @return array Массив с постами и информацией о пагинации
    */
    public function getPostsByTag($tagId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
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
                ORDER BY p.created_at DESC
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        $posts = $this->db->fetchAll($sql, [$tagId]);
        
        $countSql = "SELECT COUNT(DISTINCT p.id) as total
                    FROM posts p
                    LEFT JOIN post_tags pt ON p.id = pt.post_id
                    WHERE pt.tag_id = ? AND p.status = 'published'";
        
        $totalResult = $this->db->fetch($countSql, [$tagId]);
        $total = $totalResult['total'];
        
        return [
            'posts' => $posts,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    /**
    * Создает тег с автоматической генерацией URL-адреса
    * @param string $name Название тега
    * @return int ID созданного тега
    */
    public function createWithSlug($name) {
        $slug = $this->createUniqueSlug($name);
        
        $sql = "INSERT INTO tags (name, slug) VALUES (?, ?)";
        $this->db->query($sql, [$name, $slug]);
        
        return $this->db->lastInsertId();
    }
    
    /**
    * Проверяет, существует ли указанный URL-адрес
    * @param string $slug URL-адрес для проверки
    * @param int|null $excludeId ID для исключения
    * @return bool true если существует
    */
    private function isSlugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM tags WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
    * Генерирует URL-адрес из строки (транслитерация, очистка)
    * @param string $string Исходная строка
    * @return string Очищенная строка для использования в URL
    */
    private function generateSlug($string) {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
            
            'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
            'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
            'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
            'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
            'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
            'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
            'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya'
        );
        
        $string = strtr($string, $converter);
        $slug = mb_strtolower($string, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
    * Поиск тегов по названию (для автодополнения)
    * @param string $query Поисковый запрос
    * @param int $limit Максимальное количество результатов (по умолчанию 10)
    * @return array Массив найденных тегов
    */
    public function searchByName($query, $limit = 10) {
        $limit = (int)$limit;
        
        $sql = "SELECT id, name, slug 
                FROM tags 
                WHERE name LIKE ? 
                ORDER BY name 
                LIMIT " . $limit;
        
        $result = $this->db->fetchAll($sql, ['%' . $query . '%']);
        return $result;
    }
}