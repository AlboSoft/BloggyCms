<?php

/**
* Модель для работы со страницами в базе данных
* @package Models
*/
class PageModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getAll', 
        'getById', 
        'getBySlug', 
        'create', 
        'update', 
        'delete',
        'getBlocks',
        'createWithBlocks',
        'updateWithBlocks',
        'getStats',
        'search',
        'getRecent',
        'getAvailableParents', 
        'getWithHierarchy', 
        'hasChildren', 
        'getAllDescendants', 
        'getChildren'
    ];
    
    private $db;
    private $postBlockModel;
    
    /**
    * Конструктор модели
    * @param object $db Подключение к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
        $this->postBlockModel = new PostBlockModel($db);
    }
    
    /**
    * Получает список всех страниц 
    * @return array Массив всех страниц, отсортированных по заголовку
    */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM pages ORDER BY title");
    }
    
    /**
    * Получает страницу по её ID
    * @param int $id ID страницы
    * @return array|null Данные страницы или null, если страница не найдена
    */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM pages WHERE id = ?", [$id]);
    }
    
    /**
    * Получает страницу по её URL-адресу (slug)
    * @param string $slug URL-адрес страницы
    * @return array|null Данные страницы или null, если страница не найдена
    */
    public function getBySlug($slug) {
        return $this->db->fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
    }
    
    /**
    * Создает новую страницу
    */
    public function create($data) {
        $slug = !empty($data['slug']) 
            ? $this->createUniqueSlug($data['slug']) 
            : $this->createUniqueSlug($data['title']);
        
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        if ($parentId !== null) {
            $parent = $this->getById($parentId);
            if (!$parent) {
                $parentId = null; // Родитель не найден — обнуляем
            }
        }
        
        $sql = "INSERT INTO pages (parent_id, title, slug, status) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $parentId,
            $data['title'],
            $slug,
            $data['status'] ?? 'draft'
        ]);

        $pageId = $this->db->lastInsertId();

        Event::trigger('page.created', [
            $pageId,
            $data['title'],
            $slug,
            $data
        ]);
        
        return $pageId;
    }
    
    /**
    * Обновляет существующую страницу
    * @param int $id ID страницы
    * @param array $data Данные для обновления
    * @return bool Результат выполнения запроса
    */
    public function update($id, $data) {

        $oldPage = $this->getById($id);
        
        if (!$oldPage) {
            throw new Exception('Страница не найдена');
        }
        
        $slug = !empty($data['slug']) 
            ? $this->createUniqueSlug($data['slug'], $id) 
            : $this->createUniqueSlug($data['title'], $id);
        
        // защитаа от циклических ссылок
        if (array_key_exists('parent_id', $data)) {
            if ($data['parent_id'] === '' || $data['parent_id'] === null) {
                $parentId = null;
            } else {
                $parentId = (int)$data['parent_id'];
                
                // Нельзя сделать родителем самого себя
                if ($parentId == $id) {
                    $parentId = null;
                } else {
                    // Нельзя сделать родителем своего потомка
                    $descendants = $this->getAllDescendants($id);
                    if (in_array($parentId, $descendants)) {
                        $parentId = null;
                    }
                }
            }
        } else {
            $parentId = $oldPage['parent_id'] ?? null;
        }
        
        $sql = "UPDATE pages SET parent_id = ?, title = ?, slug = ?, status = ? WHERE id = ?";
        
        $result = $this->db->query($sql, [
            $parentId,
            $data['title'],
            $slug,
            $data['status'] ?? 'draft',
            $id
        ]);

        Event::trigger('page.updated', [
            $id,
            $oldPage,
            $data
        ]);
        
        return $result;
    }
    
    /**
    * Удаляет страницу и все связанные с ней блоки
    * @param int $id ID страницы
    * @return bool Результат выполнения запроса
    */
    public function delete($id) {
        try {

            $page = $this->getById($id);
            
            if (!$page) {
                throw new Exception('Страница не найдена');
            }
            
            if ($this->hasChildren($id)) {
                throw new Exception(LANG_ACTION_PAGES_ADMINDELETE_HAS_CHILDREN);
            }
            
            $this->db->beginTransaction();
            
            $this->postBlockModel->deleteByPage($id);
            
            $this->db->query(
                "DELETE FROM field_values WHERE entity_type = 'page' AND entity_id = ?", 
                [$id]
            );
            
            $result = $this->db->query("DELETE FROM pages WHERE id = ?", [$id]);
            
            $this->db->commit();

            Event::trigger('page.deleted', [
                $id,
                $page['title'],
                $page['slug']
            ]);
            
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
    * Создает URL-адрес (slug) из заголовка
    * @param string $title Заголовок страницы
    * @return string Сгенерированный URL-адрес
    */
    private function createSlug($title) {

        $slug = mb_strtolower($title, 'UTF-8');

        $slug = $this->transliterate($slug);
        
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        
        $slug = preg_replace('/-+/', '-', $slug);
        
        $slug = trim($slug, '-');
        
        return $slug;
    }
    
    /**
    * Создает уникальный URL-адрес, добавляя числовой суффикс при необходимости
    * @param string $title Заголовок страницы
    * @param int|null $excludeId ID страницы для исключения из проверки (при обновлении)
    * @return string Уникальный URL-адрес
    */
    private function createUniqueSlug($title, $excludeId = null) {
        $baseSlug = $this->createSlug($title);
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
    * Проверяет, существует ли уже указанный URL-адрес в базе данных 
    * @param string $slug URL-адрес для проверки
    * @param int|null $excludeId ID страницы для исключения из проверки
    * @return bool true если URL уже существует, false если свободен
    */
    private function isSlugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM pages WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
    * Транслитерирует кириллические символы в латиницу 
    * @param string $string Строка для транслитерации
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
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
            
            'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
            'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
            'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
            'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
            'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
            'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
            'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya'
        );
        
        return strtr($string, $converter);
    }

    /**
    * Получает блоки контента для указанной страницы 
    * @param int $pageId ID страницы
    * @return array Массив блоков контента
    */
    public function getBlocks($pageId) {
        return $this->postBlockModel->getByPage($pageId);
    }
    
    /**
    * Создает страницу вместе с её блоками
    * @param array $data Данные страницы
    * @param array $blocks Массив блоков контента
    * @return int ID созданной страницы
    * @throws Exception При ошибке создания
    */
    public function createWithBlocks($data, $blocks) {
        try {
            $pageId = $this->create($data);
            
            foreach ($blocks as $order => $block) {
                $this->postBlockModel->createForPage(
                    $pageId,
                    $block['type'],
                    $block['content'],
                    $block['settings'] ?? [],
                    $order
                );
            }
            
            return $pageId;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
    * Обновляет страницу вместе с её блоками
    * @param int $pageId ID страницы
    * @param array $data Данные для обновления
    * @param array $blocks Массив новых блоков контента
    * @return bool true при успешном обновлении
    * @throws Exception При ошибке обновления
    */
    public function updateWithBlocks($pageId, $data, $blocks) {
        try {
            $this->update($pageId, $data);
            
            $this->postBlockModel->deleteByPage($pageId);
            
            foreach ($blocks as $order => $block) {
                $this->postBlockModel->createForPage(
                    $pageId,
                    $block['type'],
                    $block['content'],
                    $block['settings'] ?? [],
                    $order
                );
            }
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
    * Получает статистику по страницам
    * @return array Статистика с полями: общее количество страниц, количество опубликованных, количество черновиков
    */
    public function getStats() {
        return $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
            FROM pages
        ");
    }

    /**
    * Ищет страницы по заголовку или URL-адресу
    * @param string $query Поисковый запрос
    * @return array Массив найденных страниц
    */
    public function search($query) {
        return $this->db->fetchAll("
            SELECT * FROM pages 
            WHERE title LIKE ? OR slug LIKE ?
            ORDER BY title
        ", ["%$query%", "%$query%"]);
    }

    /**
    * Получает последние созданные страницы 
    * @param int $limit Максимальное количество страниц (по умолчанию 10)
    * @return array Массив последних страниц
    */
    public function getRecent($limit = 10) {
        return $this->db->fetchAll("
            SELECT * FROM pages 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$limit]);
    }

    /**
    * Получить доступные родительские страницы (для селекта в форме)
    * @param int|null $excludeId ID исключаемой страницы (при редактировании)
    * @return array Дерево страниц
    */
    public function getAvailableParents($excludeId = null) {
        $sql = "SELECT id, title, parent_id FROM pages";
        $params = [];
        
        if ($excludeId) {
            $sql .= " WHERE id != ?";
            $params[] = $excludeId;
            
            $descendants = $this->getAllDescendants($excludeId);
            if (!empty($descendants)) {
                $placeholders = implode(',', array_fill(0, count($descendants), '?'));
                $sql .= " AND id NOT IN ($placeholders)";
                $params = array_merge($params, $descendants);
            }
        }
        
        $sql .= " ORDER BY title ASC";
        $pages = $this->db->fetchAll($sql, $params);
        
        return $this->buildTree($pages);
    }
    
    /**
    * Получить все страницы с иерархией (для списка в админке)
    * @return array Дерево страниц
    */
    public function getWithHierarchy() {
        $sql = "SELECT * FROM pages ORDER BY title ASC";
        $pages = $this->db->fetchAll($sql);
        
        return $this->buildTree($pages);
    }
    
    /**
    * Проверить наличие дочерних страниц
    * @param int $id ID страницы
    * @return bool
    */
    public function hasChildren($id) {
        $sql = "SELECT COUNT(*) as count FROM pages WHERE parent_id = ?";
        $result = $this->db->fetch($sql, [$id]);
        return (int)($result['count'] ?? 0) > 0;
    }
    
    /**
    * Получить всех потомков страницы (рекурсивно)
    * @param int $id ID страницы
    * @return array Массив ID потомков
    */
    public function getAllDescendants($id) {
        $descendants = [];
        $children = $this->getChildren($id);
        
        foreach ($children as $child) {
            $descendants[] = $child['id'];
            $descendants = array_merge($descendants, $this->getAllDescendants($child['id']));
        }
        
        return $descendants;
    }
    
    /**
    * Получить прямых детей страницы
    * @param int $parentId ID родителя
    * @return array
    */
    public function getChildren($parentId) {
        $sql = "SELECT * FROM pages WHERE parent_id = ? ORDER BY title ASC";
        return $this->db->fetchAll($sql, [$parentId]);
    }
    
    /**
    * Построить дерево из плоского списка страниц
    * @param array $pages Плоский массив страниц
    * @return array Дерево
    */
    private function buildTree($pages) {
        $indexed = [];
        foreach ($pages as &$page) {
            $page['children'] = [];
            $indexed[$page['id']] = &$page;
        }
        
        $tree = [];
        foreach ($pages as &$page) {
            $parentId = $page['parent_id'] ?? null;
            if ($parentId && isset($indexed[$parentId])) {
                $indexed[$parentId]['children'][] = &$page;
            } else {
                $tree[] = &$page;
            }
        }
        
        return $tree;
    }
}