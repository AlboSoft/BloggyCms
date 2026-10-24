<?php

/**
* Модель HTML-блоков
* @package models
*/
class HtmlBlockModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getBySlug',
        'getById',
        'getAll'
    ];
    
    private $db;
    
    /**
    * Конструктор модели HTML-блоков
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
    * Получение всех HTML-блоков
    * @return array Массив всех HTML-блоков с данными о типах
    */
    public function getAll() {
        return $this->db->fetchAll("
            SELECT hb.*, 
                hbt.name as type_name, 
                hbt.system_name as block_type,
                hbt.template as block_type_template 
            FROM html_blocks hb 
            LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
            ORDER BY hb.created_at DESC
        ");
    }
    
    /**
    * Получение блока по ID
    * @param int $id ID блока
    * @return array|null Данные блока или null если не найден
    */
    public function getById($id) {
        return $this->db->fetch("
            SELECT hb.*, hbt.name as type_name, hbt.system_name as block_type 
            FROM html_blocks hb 
            LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
            WHERE hb.id = ?
        ", [$id]);
    }
    
    /**
    * Получение блока по slug
    * @param string $slug URL-идентификатор блока
    * @return array|null Данные блока или null если не найден
    */
    public function getBySlug($slug) {
        return $this->db->fetch("
            SELECT hb.*, hbt.name as type_name, hbt.system_name as block_type 
            FROM html_blocks hb 
            LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
            WHERE hb.slug = ?
        ", [$slug]);
    }
    
    /**
    * Создание нового HTML-блока
    * @param array $data Массив данных блока
    * @return int ID созданного блока
    * @throws Exception При ошибках валидации или дублировании slug
    */
    public function create($data) {
        if (!preg_match('/^[a-z0-9_-]+$/', $data['slug'])) {
            throw new Exception('Имя может содержать только латинские буквы, цифры, дефисы и нижнее подчеркивание.');
        }

        if ($this->isSlugExists($data['slug'])) {
            throw new Exception('Имя уже существует.');
        }

        $settingsJson = null;
        if (isset($data['settings'])) {
            $settingsJson = json_encode($data['settings'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($settingsJson === false) {
                throw new Exception('Ошибка кодирования настроек: ' . json_last_error_msg());
            }
        }

        $sql = "INSERT INTO html_blocks (name, slug, content, type_id, settings, css_files, js_files, inline_css, inline_js, template) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['name'],
            $data['slug'],
            '',
            $data['type_id'] ?? null,
            $settingsJson,
            isset($data['css_files']) ? json_encode($data['css_files'], JSON_UNESCAPED_UNICODE) : null,
            isset($data['js_files']) ? json_encode($data['js_files'], JSON_UNESCAPED_UNICODE) : null,
            $data['inline_css'] ?? '',
            $data['inline_js'] ?? '',
            $data['template'] ?? 'default'
        ]);

        return (int)$this->db->lastInsertId();
    }
    
    /**
    * Обновление существующего HTML-блока
    * @param int $id ID обновляемого блока
    * @param array $data Массив данных для обновления (аналогично create)
    * @return bool Результат выполнения запроса
    */
    public function update($id, $data) {
        if (empty($data['slug'])) {
            $slug = $this->createUniqueSlug($data['name'], $id);
        } else {
            $slug = $this->createUniqueSlug($data['slug'], $id);
        }

        $settingsJson = null;
        if (isset($data['settings'])) {
            $settingsJson = json_encode($data['settings'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($settingsJson === false) {
                throw new Exception('Ошибка кодирования настроек: ' . json_last_error_msg());
            }
        }

        $sql = "UPDATE html_blocks SET name = ?, slug = ?, content = '', type_id = ?, settings = ?, css_files = ?, js_files = ?, inline_css = ?, inline_js = ?, template = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['name'],
            $slug,
            $data['type_id'] ?? null,
            $settingsJson,
            isset($data['css_files']) ? json_encode($data['css_files'], JSON_UNESCAPED_UNICODE) : null,
            isset($data['js_files']) ? json_encode($data['js_files'], JSON_UNESCAPED_UNICODE) : null,
            $data['inline_css'] ?? '',
            $data['inline_js'] ?? '',
            $data['template'] ?? 'default',
            $id
        ]);
    }
    
    /**
    * Удаление HTML-блока
    * @param int $id ID удаляемого блока
    * @return bool Результат выполнения запроса
    */
    public function delete($id) {
        return $this->db->query("DELETE FROM html_blocks WHERE id = ?", [$id]);
    }
    
    /**
    * Создание slug на основе имени
    * @param string $name Исходное имя блока
    * @return string Сгенерированный slug
    */
    private function createSlug($name) {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
    * Создание уникального slug с проверкой на дубликаты
    * @param string $name Исходное имя или slug
    * @param int|null $excludeId ID блока для исключения (при обновлении)
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
    * @param int|null $excludeId ID блока для исключения из проверки
    * @return bool true если slug уже существует
    */
    private function isSlugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM html_blocks WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
}