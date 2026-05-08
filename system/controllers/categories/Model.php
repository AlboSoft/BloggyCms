<?php

/**
* Модель категорий блога
*/
class CategoryModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getAll',
        'getById',
        'getBySlug',
        'getAllOrdered',
        'getPostsCount',
        'getPostsPaginated',
        'checkPassword'
    ];

    private $db;
    
    /**
    * Конструктор модели категорий
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
    * Получение всех категорий с количеством постов
    * @return array Массив категорий с информацией о количестве постов
    */
    public function getAll() {
        return $this->db->fetchAll("
            SELECT 
                c.*,
                COUNT(p.id) as posts_count
            FROM categories c
            LEFT JOIN posts p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ");
    }    
    
    /**
    * Получение категории по ID
    * @param int $id Идентификатор категории
    * @return array|null Данные категории или null если не найдена
    */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
    }    
    
    /**
    * Получение категории по URL-идентификатору (slug)
    * @param string $slug URL-идентификатор категории
    * @return array|null Данные категории или null если не найдена
    */
    public function getBySlug($slug) {
        $result = $this->db->fetch("SELECT * FROM categories WHERE slug = ?", [$slug]);
        return $result;
    }
       
    /**
    * Создание новой категории
    * @param array $data Массив данных категории:
    * @return int ID созданной категории
    * @throws Exception При ошибках
    */
    public function create($data) {
        $slug = !empty($data['slug']) ? $this->createUniqueSlug($data['slug'], null) : $this->createUniqueSlug($data['name']);
        
        $sql = "INSERT INTO categories 
                (name, slug, description, meta_title, meta_description, canonical_url, noindex, 
                image, sort_order, password_protected, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $result = $this->db->query($sql, [
            $data['name'],
            $slug,
            $data['description'] ?? '',
            $data['meta_title'] ?? '',
            $data['meta_description'] ?? '',
            $data['canonical_url'] ?? '',
            $data['noindex'] ?? 0,
            $data['image'] ?? '',
            $data['sort_order'] ?? 0,
            $data['password_protected'] ?? 0,
            $data['password'] ?? null
        ]);
        
        if (!$result) {
            throw new Exception('Ошибка при создании категории в базе данных');
        }

        $categoryId = $this->db->lastInsertId();

        Event::trigger('category.created', [
            $categoryId,
            $data['name'],
            $slug,
            $data
        ]);
        
        return $categoryId;
    }
    
    /**
    * Обновление существующей категории
    * @param int $id Идентификатор обновляемой категории
    * @param array $data Массив данных для обновления
    * @return bool Результат выполнения запроса
    */
    public function update($id, $data) {
        $oldCategory = $this->getById($id);
        
        if (!$oldCategory) {
            throw new Exception('Категория не найдена');
        }
        
        $slug = !empty($data['slug']) ? $this->createUniqueSlug($data['slug'], $id) : $this->createUniqueSlug($data['name'], $id);
        
        $sql = "UPDATE categories SET 
                name = ?, 
                slug = ?, 
                description = ?,
                meta_title = ?,
                meta_description = ?,
                canonical_url = ?,
                noindex = ?,
                image = ?,
                sort_order = ?,
                password_protected = ?,
                password = ?
                WHERE id = ?";
        
        $result = $this->db->query($sql, [
            $data['name'],
            $slug,
            $data['description'] ?? '',
            $data['meta_title'] ?? '',
            $data['meta_description'] ?? '',
            $data['canonical_url'] ?? '',
            $data['noindex'] ?? 0,
            $data['image'] ?? '',
            $data['sort_order'] ?? 0,
            $data['password_protected'] ?? 0,
            $data['password'],
            $id
        ]);

        Event::trigger('category.updated', [
            $id,
            $oldCategory,
            $data
        ]);
        
        return $result;
    }
    
    /**
    * Удаление категории
    * @param int $id Идентификатор удаляемой категории
    * @return bool Результат выполнения запроса
    * @throws Exception Если в категории есть посты
    */
    public function delete($id) {
        try {

            $category = $this->getById($id);
            
            if (!$category) {
                throw new Exception('Категория не найдена');
            }
            
            $postsCount = $this->getPostsCount($id);
            
            if ($postsCount > 0) {
                throw new Exception("Невозможно удалить категорию. В ней содержится {$postsCount} постов. Сначала удалите или переместите посты.");
            }
            
            $result = $this->db->query("DELETE FROM categories WHERE id = ?", [$id]);

            Event::trigger('category.deleted', [
                $id,
                $category['name'],
                $category['slug']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
    * Перемещение всех постов из одной категории в другую
    * @param int $fromCategoryId Идентификатор исходной категории
    * @param int $toCategoryId Идентификатор целевой категории
    * @return bool Результат выполнения запроса
    */
    public function movePostsToCategory($fromCategoryId, $toCategoryId) {
        return $this->db->query(
            "UPDATE posts SET category_id = ? WHERE category_id = ?",
            [$toCategoryId, $fromCategoryId]
        );
    }

    /**
    * Удаление категории вместе со всеми постами
    * @param int $id Идентификатор удаляемой категории
    * @return bool Результат выполнения запроса
    * @throws Exception При ошибках удаления
    */
    public function deleteWithPosts($id) {
        try {

            $postModel = new PostModel($this->db);
            $posts = $this->db->fetchAll("SELECT id FROM posts WHERE category_id = ?", [$id]);
            
            foreach ($posts as $post) {
                $postModel->delete($post['id']);
            }
            
            return $this->db->query("DELETE FROM categories WHERE id = ?", [$id]);
            
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
    * Проверка пароля для защищенной категории
    * @param int $categoryId Идентификатор категории
    * @param string $password Введенный пароль
    * @return bool true если пароль верный или категория не защищена
    */
    public function checkPassword($categoryId, $password) {
        $category = $this->getById($categoryId);
        
        if (!$category || !$category['password_protected']) {
            return true;
        }

        if (!isset($category['password']) || strlen($category['password']) < 32) {
            return false;
        }

        return password_verify($password, $category['password']);
    }
    
    /**
    * Создание базового slug из названия
    * @param string $name Исходное название категории
    * @return string Сгенерированный slug
    */
    private function createSlug($name) {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = $this->transliterate($slug);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
    * Создание уникального slug
    * @param string $name Исходное название или slug
    * @param int|null $excludeId ID категории для исключения (при обновлении)
    * @return string Уникальный slug
    */
    private function createUniqueSlug($name, $excludeId = null) {
        $baseSlug = $this->createSlug($name);
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
    * Проверка существования slug в базе данных
    * @param string $slug Проверяемый slug
    * @param int|null $excludeId ID категории для исключения из проверки
    * @return bool true если slug уже существует
    */
    private function isSlugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM categories WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
    * Транслитерация русских символов в латинские
    * @param string $string Исходная строка с русскими символами
    * @return string Транслитерированная строка
    */
    private function transliterate($string) {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
        );
        
        return strtr($string, $converter);
    }
    
    /**
    * Получение количества постов в категории
    * @param int $categoryId Идентификатор категории
    * @return int Количество постов
    */
    public function getPostsCount($categoryId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM posts WHERE category_id = ?", 
            [$categoryId]
        );
        return $result['count'];
    }

    /**
    * Получение постов категории с пагинацией
    */
    public function getPostsPaginated($categoryId, $page = 1, $perPage = 15) {
        $offset = ($page - 1) * $perPage;
        
        $totalPosts = $this->db->fetch(
            "SELECT COUNT(*) as count FROM posts WHERE category_id = ? AND status = 'published'",
            [$categoryId]
        )['count'];
        
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   GROUP_CONCAT(
                       CONCAT(t.name, ':::', t.slug) 
                       SEPARATOR '|||'
                   ) as tag_data
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            WHERE p.category_id = ? AND p.status = 'published'
            GROUP BY p.id, c.name, c.slug
            ORDER BY p.created_at DESC
            LIMIT " . (int)$perPage . " 
            OFFSET " . (int)$offset;
        
        $posts = $this->db->fetchAll($sql, [$categoryId]);
        
        foreach ($posts as &$post) {
            $tags = [];
            if (!empty($post['tag_data'])) {
                $tagItems = explode('|||', $post['tag_data']);
                foreach ($tagItems as $tagItem) {
                    if (empty($tagItem)) continue;
                    $parts = explode(':::', $tagItem);
                    if (count($parts) >= 2) {
                        list($name, $slug) = $parts;
                        $tags[] = [
                            'name' => $name,
                            'slug' => $slug
                        ];
                    }
                }
            }
            $post['tags'] = $tags;
            unset($post['tag_data']);
        }
        
        return [
            'posts' => $posts,
            'total' => $totalPosts,
            'pages' => ceil($totalPosts / $perPage),
            'current_page' => $page
        ];
    }

    /**
    * Обновление порядка сортировки категории
    * @param int $categoryId Идентификатор категории
    * @param int $sortOrder Новый порядок сортировки
    * @return bool Результат выполнения запроса
    */
    public function updateOrder($categoryId, $sortOrder) {
        return $this->db->query(
            "UPDATE categories SET sort_order = ? WHERE id = ?",
            [$sortOrder, $categoryId]
        );
    }

    /**
    * Получение всех категорий с количеством опубликованных постов
    * @return array Массив категорий с количеством постов
    */
    public function getAllOrdered() {
        return $this->db->fetchAll("
            SELECT c.*, 
                COUNT(p.id) as posts_count
            FROM categories c 
            LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
            GROUP BY c.id 
            ORDER BY c.sort_order ASC, c.name ASC
        ");
    }

}