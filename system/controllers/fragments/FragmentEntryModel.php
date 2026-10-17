<?php

/**
* Модель для работы с записями фрагментов
* 
* @package models
*/
class FragmentEntryModel {
    
    /**
    * @var \Database
    */
    private $db;
    
    /**
    * @var string
    */
    private $tableName;
    
    /**
    * @var string
    */
    private $fieldsTableName;
    
    /**
    * @var bool Флаг, что структура таблицы уже проверена в текущем запросе
    */
    private static $schemaChecked = false;
    
    /**
    * Конструктор
    * @param \Database $db
    */
    public function __construct($db) {
        $this->db = $db;
        $this->tableName = $this->db->getPrefix() . 'fragment_entries';
        $this->fieldsTableName = $this->db->getPrefix() . 'fragments_fields';
    }
    
    /**
    * Добавляет колонку slug в таблицу записей (для установок,
    * обновлённых без переустановки).
    * @return void
    */
    private function ensureSchema() {
        if (self::$schemaChecked) {
            return;
        }
        
        self::$schemaChecked = true;
        
        try {
            $columns = $this->db->fetchAll("SHOW COLUMNS FROM `{$this->tableName}`");
            $existing = array_column($columns, 'Field');
            
            if (!in_array('slug', $existing, true)) {
                $this->db->query(
                    "ALTER TABLE `{$this->tableName}` ADD COLUMN `slug` varchar(255) DEFAULT NULL AFTER `fragment_id`"
                );
                $this->db->query(
                    "ALTER TABLE `{$this->tableName}` ADD UNIQUE KEY `unique_slug_per_fragment` (`fragment_id`, `slug`)"
                );
            }
        } catch (\Throwable $e) {
            error_log('FragmentEntryModel schema update failed: ' . $e->getMessage());
        }
    }
    
    /**
    * Получение записей фрагмента
    * @param int $fragmentId
    * @param string $status
    * @return array
    */
    public function getByFragment($fragmentId, $status = null) {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE fragment_id = ?";
        $params = [$fragmentId];
        
        if ($status !== null) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY sort_order ASC, created_at DESC";
        
        $entries = $this->db->fetchAll($sql, $params);
        
        foreach ($entries as &$entry) {
            if ($entry['data']) {
                $decoded = json_decode($entry['data'], true);
                $entry['data'] = is_array($decoded) ? $decoded : [];
            } else {
                $entry['data'] = [];
            }
        }
        
        return $entries;
    }
    
    /**
    * Получение записи по ID
    * @param int $id
    * @return array|null
    */
    public function getById($id) {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE id = ?";
        $entry = $this->db->fetch($sql, [$id]);
        
        if ($entry) {
            $decoded = json_decode($entry['data'] ?? '', true);
            $entry['data'] = is_array($decoded) ? $decoded : [];
        }
        
        return $entry;
    }
    
    /**
    * Получение записи фрагмента по URL-адресу (slug)
    * @param int $fragmentId
    * @param string $slug
    * @return array|null
    */
    public function getBySlug($fragmentId, $slug) {
        $this->ensureSchema();
        
        $sql = "SELECT * FROM `{$this->tableName}` WHERE fragment_id = ? AND slug = ? LIMIT 1";
        $entry = $this->db->fetch($sql, [$fragmentId, $slug]);
        
        if ($entry) {
            $decoded = json_decode($entry['data'] ?? '', true);
            $entry['data'] = is_array($decoded) ? $decoded : [];
        }
        
        return $entry;
    }
    
    /**
    * Создание записи 
    * @param int $fragmentId
    * @param array $data
    * @param int $sortOrder
    * @param string $status
    * @param string|null $slug Желаемый URL записи (null или пустая строка - сгенерировать)
    * @return int
    */
    public function create($fragmentId, $data, $sortOrder = 0, $status = 'active', $slug = null) {
        $this->ensureSchema();
        
        $slug = $this->resolveSlugForCreate($fragmentId, $data, $slug);
        
        $entryData = [
            'fragment_id' => $fragmentId,
            'slug' => $slug,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'sort_order' => $sortOrder,
            'status' => $status === 'inactive' ? 'inactive' : 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $sql = "INSERT INTO `{$this->tableName}` (
            fragment_id, slug, data, sort_order, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $entryData['fragment_id'],
            $entryData['slug'],
            $entryData['data'],
            $entryData['sort_order'],
            $entryData['status'],
            $entryData['created_at'],
            $entryData['updated_at']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
    * Обновление записи
    * @param int $id
    * @param array $data
    * @param string|null $status
    * @param string|null $slug Новый URL записи (null — оставить текущий, пустая строка — сгенерировать заново)
    * @return bool
    */
    public function update($id, $data, $status = null, $slug = null) {
        $this->ensureSchema();
        
        $entry = $this->getById($id);
        
        if (!$entry) {
            return false;
        }
        
        $currentSlug = (string)($entry['slug'] ?? '');
        $newSlug = $currentSlug;
        
        if ($slug !== null && trim((string)$slug) !== '') {
            $newSlug = $this->makeUniqueSlug((int)$entry['fragment_id'], $slug, (int)$id);
        } elseif ($slug === '' || $currentSlug === '') {
            $fields = $this->getSlugFields((int)$entry['fragment_id']);
            $newSlug = $this->makeUniqueSlug(
                (int)$entry['fragment_id'],
                $this->buildBaseSlug($fields, $data),
                (int)$id
            );
        }
        
        $updateData = [
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'slug' => $newSlug,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $setParts = ['data = ?', 'slug = ?', 'updated_at = ?'];
        $params = [$updateData['data'], $updateData['slug'], $updateData['updated_at']];
        
        if ($status !== null) {
            $updateData['status'] = $status === 'inactive' ? 'inactive' : 'active';
            $setParts[] = 'status = ?';
            $params[] = $updateData['status'];
        }
        
        $params[] = $id;
        
        $sql = "UPDATE `{$this->tableName}` SET " . implode(', ', $setParts) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
    * Удаление записи
    * @param int $id
    * @return bool
    */
    public function delete($id) {
        $sql = "DELETE FROM `{$this->tableName}` WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
    * Удаление всех записей фрагмента
    * @param int $fragmentId
    * @return bool
    */
    public function deleteByFragment($fragmentId) {
        $sql = "DELETE FROM `{$this->tableName}` WHERE fragment_id = ?";
        return $this->db->query($sql, [$fragmentId]);
    }
    
    /**
    * Изменение статуса записи 
    * @param int $id
    * @param string $status
    * @return bool
    */
    public function setStatus($id, $status) {
        $sql = "UPDATE `{$this->tableName}` SET status = ?, updated_at = ? WHERE id = ?";
        return $this->db->query($sql, [$status, date('Y-m-d H:i:s'), $id]);
    }
    
    /**
    * Обновление порядка записей 
    * @param array $order
    * @return bool
    */
    public function updateOrder($order) {
        $success = true;
        foreach ($order as $item) {
            $sql = "UPDATE `{$this->tableName}` SET sort_order = ? WHERE id = ?";
            $result = $this->db->query($sql, [$item['order'], $item['id']]);
            if (!$result) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
    * Получение количества записей фрагмента 
    * @param int $fragmentId
    * @param string|null $status
    * @return int
    */
    public function getCountByFragment($fragmentId, $status = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->tableName}` WHERE fragment_id = ?";
        $params = [$fragmentId];
        
        if ($status !== null) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $result = $this->db->fetch($sql, $params);
        return (int)($result['count'] ?? 0);
    }
    
    /**
    * Генерирует URL (slug) для всех записей фрагмента, у которых он ещё не задан
    * @param int $fragmentId
    * @return void
    */
    public function ensureSlugs($fragmentId) {
        $this->ensureSchema();
        
        $entries = $this->db->fetchAll(
            "SELECT id, data, slug FROM `{$this->tableName}` WHERE fragment_id = ? ORDER BY sort_order ASC, id ASC",
            [$fragmentId]
        );
        
        $missing = [];
        foreach ($entries as $entry) {
            if (!isset($entry['slug']) || trim((string)$entry['slug']) === '') {
                $missing[] = $entry;
            }
        }
        
        if (empty($missing)) {
            return;
        }
        
        $fields = $this->getSlugFields($fragmentId);
        
        foreach ($missing as $entry) {
            $data = json_decode($entry['data'] ?? '', true);
            $baseSlug = $this->buildBaseSlug($fields, is_array($data) ? $data : []);
            $slug = $this->makeUniqueSlug($fragmentId, $baseSlug, (int)$entry['id']);
            
            $this->db->query(
                "UPDATE `{$this->tableName}` SET slug = ? WHERE id = ?",
                [$slug, $entry['id']]
            );
        }
    }
    
    /**
    * Определяет slug при создании записи
    * @param int $fragmentId
    * @param array $data
    * @param string|null $slug
    * @return string
    */
    private function resolveSlugForCreate($fragmentId, $data, $slug = null) {
        if ($slug !== null && trim((string)$slug) !== '') {
            return $this->makeUniqueSlug($fragmentId, $slug);
        }
        
        $fields = $this->getSlugFields($fragmentId);
        $baseSlug = $this->buildBaseSlug($fields, $data);
        
        return $this->makeUniqueSlug($fragmentId, $baseSlug);
    }
    
    /**
    * Возвращает поля фрагмента, подходящие для формирования URL
    * @param int $fragmentId
    * @return array
    */
    private function getSlugFields($fragmentId) {
        return $this->db->fetchAll(
            "SELECT system_name, type FROM `{$this->fieldsTableName}` 
             WHERE fragment_id = ? AND is_active = 1 
             ORDER BY sort_order ASC, id ASC",
            [$fragmentId]
        );
    }
    
    /**
    * Формирует базовый slug из данных записи (первое непустое текстовое поле)
    * @param array $fields
    * @param array $data
    * @return string
    */
    private function buildBaseSlug($fields, $data) {
        foreach ($fields as $field) {
            if (!in_array($field['type'], ['string', 'text'], true)) {
                continue;
            }
            
            $value = $data[$field['system_name']] ?? '';
            
            if (!is_scalar($value)) {
                continue;
            }
            
            $value = trim(strip_tags((string)$value));
            
            if ($value === '') {
                continue;
            }
            
            $slug = $this->sanitizeSlug($value);
            
            if ($slug !== '') {
                return $slug;
            }
        }
        
        return 'entry';
    }
    
    /**
    * Делает slug уникальным внутри фрагмента
    * @param int $fragmentId
    * @param string $baseSlug
    * @param int|null $excludeId
    * @return string
    */
    private function makeUniqueSlug($fragmentId, $baseSlug, $excludeId = null) {
        $baseSlug = $this->sanitizeSlug($baseSlug);
        
        if ($baseSlug === '') {
            $baseSlug = 'entry';
        }
        
        $baseSlug = mb_substr($baseSlug, 0, 200, 'UTF-8');
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->isSlugExists($fragmentId, $slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
    * Проверяет, занят ли slug внутри фрагмента
    * @param int $fragmentId
    * @param string $slug
    * @param int|null $excludeId
    * @return bool
    */
    private function isSlugExists($fragmentId, $slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->tableName}` WHERE fragment_id = ? AND slug = ?";
        $params = [$fragmentId, $slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
    * Очищает строку для использования в URL
    * @param string $value
    * @return string
    */
    private function sanitizeSlug($value) {
        $value = preg_replace('/\.html?$/i', '', trim((string)$value));
        $value = strip_tags($value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = $this->transliterate($value);
        $value = preg_replace('/[^a-z0-9-]+/', '-', $value);
        $value = preg_replace('/-+/', '-', $value);
        
        return trim($value, '-');
    }
    
    /**
    * Транслитерирует кириллицу в латиницу
    * @param string $string
    * @return string
    */
    private function transliterate($string) {
        $converter = [
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
        ];
        
        return strtr($string, $converter);
    }
}
