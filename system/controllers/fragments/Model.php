<?php

/**
* Модель для работы с фрагментами
* @package models
*/
class FragmentModel implements ModelAPI {
    
    use APIAware;
    
    protected $allowedAPIMethods = [
        'getAll',
        'getById',
        'getBySystemName',
        'getActive',
        'getEntries'
    ];
    
    /**
    * @var \Database
    */
    private $db;
    
    /**
    * @var string
    */
    private $tableName;
    
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
        $this->tableName = $this->db->getPrefix() . 'fragments';
    }
    
    /**
    * Добавляет недостающие колонки в таблицу фрагментов (для установок,
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
            
            if (!in_array('entry_template_enabled', $existing, true)) {
                $this->db->query(
                    "ALTER TABLE `{$this->tableName}` ADD COLUMN `entry_template_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `inline_js`"
                );
            }
            
            if (!in_array('entry_template', $existing, true)) {
                $this->db->query(
                    "ALTER TABLE `{$this->tableName}` ADD COLUMN `entry_template` text DEFAULT NULL AFTER `entry_template_enabled`"
                );
            }
        } catch (\Throwable $e) {
            error_log('FragmentModel schema update failed: ' . $e->getMessage());
        }
    }
    
    /**
    * Получение всех фрагментов
    * @return array
    */
    public function getAll() {
        $sql = "SELECT * FROM `{$this->tableName}` ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
    * Получение активных фрагментов
    * @return array
    */
    public function getActive() {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE status = 'active' ORDER BY name ASC";
        $fragments = $this->db->fetchAll($sql);
        
        foreach ($fragments as &$fragment) {
            $this->normalizeEntryTemplateData($fragment);
        }
        
        return $fragments;
    }
    
    /**
    * Получение фрагмента по ID 
    * @param int $id
    * @return array|null
    */
    public function getById($id) {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE id = ?";
        $fragment = $this->db->fetch($sql, [$id]);
        
        if ($fragment) {
            
            if (isset($fragment['css_files']) && is_string($fragment['css_files'])) {
                $decoded = json_decode($fragment['css_files'], true);
                $fragment['css_files'] = is_array($decoded) ? $decoded : [];
            } elseif (!isset($fragment['css_files'])) {
                $fragment['css_files'] = [];
            }
            
            if (isset($fragment['js_files']) && is_string($fragment['js_files'])) {
                $decoded = json_decode($fragment['js_files'], true);
                $fragment['js_files'] = is_array($decoded) ? $decoded : [];
            } elseif (!isset($fragment['js_files'])) {
                $fragment['js_files'] = [];
            }
            
            $this->normalizeEntryTemplateData($fragment);
        }
        
        return $fragment;
    }
    
    /**
    * Получение фрагмента по системному имени 
    * @param string $systemName
    * @return array|null
    */
    public function getBySystemName($systemName) {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE system_name = ?";
        $fragment = $this->db->fetch($sql, [$systemName]);
        
        if ($fragment) {
            $this->decodeFragmentData($fragment);
        }
        
        return $fragment;
    }
    
    /**
    * Создание фрагмента
    * @param array $data
    * @return int
    */
    public function create($data) {
        $this->ensureSchema();
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $data = $this->encodeFragmentData($data);
        
        $sql = "INSERT INTO `{$this->tableName}` (
            system_name, name, description, css_files, js_files, 
            inline_css, inline_js, entry_template_enabled, entry_template,
            status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $result = $this->db->query($sql, [
            $data['system_name'] ?? '',
            $data['name'] ?? '',
            $data['description'] ?? '',
            $data['css_files'] ?? null,
            $data['js_files'] ?? null,
            $data['inline_css'] ?? '',
            $data['inline_js'] ?? '',
            !empty($data['entry_template_enabled']) ? 1 : 0,
            $data['entry_template'] ?? '',
            $data['status'] ?? 'active',
            $data['created_at'],
            $data['updated_at']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
    * Обновление фрагмента 
    * @param int $id
    * @param array $data
    * @return bool
    */
    public function update($id, $data) {
        $this->ensureSchema();
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $data = $this->encodeFragmentData($data);
        
        $sql = "UPDATE `{$this->tableName}` SET 
            system_name = ?,
            name = ?,
            description = ?,
            css_files = ?,
            js_files = ?,
            inline_css = ?,
            inline_js = ?,
            entry_template_enabled = ?,
            entry_template = ?,
            status = ?,
            updated_at = ?
        WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['system_name'] ?? '',
            $data['name'] ?? '',
            $data['description'] ?? '',
            $data['css_files'] ?? null,
            $data['js_files'] ?? null,
            $data['inline_css'] ?? '',
            $data['inline_js'] ?? '',
            !empty($data['entry_template_enabled']) ? 1 : 0,
            $data['entry_template'] ?? '',
            $data['status'] ?? 'active',
            $data['updated_at'],
            $id
        ]);
    }
    
    /**
    * Удаление фрагмента
    * @param int $id
    * @return bool
    */
    public function delete($id) {
        $entryModel = new FragmentEntryModel($this->db);
        $entryModel->deleteByFragment($id);
        $sql = "DELETE FROM `{$this->tableName}` WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
    * Проверка существования системного имени 
    * @param string $systemName
    * @param int|null $excludeId
    * @return bool
    */
    public function isSystemNameExists($systemName, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->tableName}` WHERE system_name = ?";
        $params = [$systemName];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
    * Получение полей фрагмента
    * @param int $fragmentId
    * @return array
    */
    public function getFields($fragmentId) {
        $fieldsTable = $this->getFieldsTableName();
        
        $fields = $this->db->fetchAll(
            "SELECT * FROM `{$fieldsTable}` WHERE fragment_id = ? AND is_active = 1 ORDER BY sort_order ASC",
            [$fragmentId]
        );
        
        foreach ($fields as &$field) {
            if ($field['config']) {
                $decoded = json_decode($field['config'], true);
                $field['config'] = is_array($decoded) ? $decoded : [];
            } else {
                $field['config'] = [];
            }
        }
        
        return $fields;
    }

    public function getTableName() {
        return $this->tableName;
    }
    
    /**
    * Сохранение полей фрагмента 
    * @param int $fragmentId
    * @param array $fields
    * @return bool
    */
    public function saveFields($fragmentId, $fields) {
        $fragment = $this->getById($fragmentId);
        if (!$fragment) {
            return false;
        }
        
        return true;
    }
    
    /**
    * Получение записей фрагмента
    * @param int $fragmentId
    * @param string $status
    * @return array
    */
    public function getEntries($fragmentId, $status = 'active') {
        $entryModel = new FragmentEntryModel($this->db);
        return $entryModel->getByFragment($fragmentId, $status);
    }
    
    /**
    * Получение статистики по фрагменту
    * @param int $fragmentId
    * @return array
    */
    public function getStats($fragmentId) {
        $entryModel = new FragmentEntryModel($this->db);
        $total = $entryModel->getCountByFragment($fragmentId);
        $active = $entryModel->getCountByFragment($fragmentId, 'active');
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active
        ];
    }
    
    /**
    * Кодирование JSON полей 
    * @param array $data
    * @return array
    */
    private function encodeFragmentData($data) {
        if (isset($data['css_files']) && is_array($data['css_files'])) {
            $data['css_files'] = json_encode($data['css_files'], JSON_UNESCAPED_UNICODE);
        }
        
        if (isset($data['js_files']) && is_array($data['js_files'])) {
            $data['js_files'] = json_encode($data['js_files'], JSON_UNESCAPED_UNICODE);
        }
        
        return $data;
    }
    
    /**
    * Декодирование JSON полей
    * @param array $data
    */
    private function decodeFragmentData(&$data) {
        if (isset($data['css_files']) && is_string($data['css_files']) && !empty($data['css_files'])) {
            $decoded = json_decode($data['css_files'], true);
            $data['css_files'] = is_array($decoded) ? $decoded : [];
        } elseif (!isset($data['css_files'])) {
            $data['css_files'] = [];
        }
        
        if (isset($data['js_files']) && is_string($data['js_files']) && !empty($data['js_files'])) {
            $decoded = json_decode($data['js_files'], true);
            $data['js_files'] = is_array($decoded) ? $decoded : [];
        } elseif (!isset($data['js_files'])) {
            $data['js_files'] = [];
        }
        
        $this->normalizeEntryTemplateData($data);
    }
    
    /**
    * Приводит поля шаблона записей к ожидаемым типам
    * @param array $data
    * @return void
    */
    private function normalizeEntryTemplateData(&$data) {
        $data['entry_template_enabled'] = !empty($data['entry_template_enabled']) ? 1 : 0;
        
        if (!isset($data['entry_template']) || !is_string($data['entry_template'])) {
            $data['entry_template'] = '';
        }
    }

    /**
    * Получение имени таблицы полей
    * @return string
    */
    public function getFieldsTableName() {
        return $this->db->getPrefix() . 'fragments_fields';
    }

    /**
    * Получение полей фрагмента с пагинацией
    * @param int $fragmentId
    * @param int $page
    * @param int $perPage
    * @return array
    */
    public function getFieldsPaginated($fragmentId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $fieldsTable = $this->getFieldsTableName();
        
        $sql = "SELECT * FROM `{$fieldsTable}` WHERE fragment_id = ? ORDER BY sort_order ASC LIMIT ? OFFSET ?";
        $fields = $this->db->fetchAll($sql, [$fragmentId, $perPage, $offset]);
        
        $totalSql = "SELECT COUNT(*) as count FROM `{$fieldsTable}` WHERE fragment_id = ?";
        $totalResult = $this->db->fetch($totalSql, [$fragmentId]);
        
        foreach ($fields as &$field) {
            if ($field['config']) {
                $decoded = json_decode($field['config'], true);
                $field['config'] = is_array($decoded) ? $decoded : [];
            } else {
                $field['config'] = [];
            }
        }
        
        return [
            'fields' => $fields,
            'total' => $totalResult['count'] ?? 0,
            'pages' => ceil(($totalResult['count'] ?? 0) / $perPage),
            'current_page' => $page
        ];
    }

    /**
    * Получение поля по ID
    * @param int $fieldId
    * @return array|null
    */
    public function getFieldById($fieldId) {
        $fieldsTable = $this->getFieldsTableName();
        $sql = "SELECT * FROM `{$fieldsTable}` WHERE id = ?";
        $field = $this->db->fetch($sql, [$fieldId]);
        
        if ($field && $field['config']) {
            $decoded = json_decode($field['config'], true);
            $field['config'] = is_array($decoded) ? $decoded : [];
        }
        
        return $field;
    }

    /**
    * Создание поля фрагмента
    * @param int $fragmentId
    * @param array $data
    * @return int
    */
    public function createField($fragmentId, $data) {
        $fieldsTable = $this->getFieldsTableName();
        $config = json_encode($data['config'] ?? [], JSON_UNESCAPED_UNICODE);
        
        $sql = "INSERT INTO `{$fieldsTable}` (
            fragment_id, system_name, name, type, description, is_required, 
            is_active, show_in_list, sort_order, config
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $fragmentId,
            $data['system_name'],
            $data['name'],
            $data['type'],
            $data['description'] ?? '',
            $data['is_required'] ?? 0,
            $data['is_active'] ?? 1,
            $data['show_in_list'] ?? 0,
            $data['sort_order'] ?? 0,
            $config
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
    * Обновление поля фрагмента
    * @param int $fieldId
    * @param array $data
    * @return bool
    */
    public function updateField($fieldId, $data) {
        $fieldsTable = $this->getFieldsTableName();
        $config = json_encode($data['config'] ?? [], JSON_UNESCAPED_UNICODE);
        
        $sql = "UPDATE `{$fieldsTable}` SET 
            system_name = ?,
            name = ?,
            type = ?,
            description = ?,
            is_required = ?,
            is_active = ?,
            show_in_list = ?,
            config = ?
        WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['system_name'],
            $data['name'],
            $data['type'],
            $data['description'] ?? '',
            $data['is_required'] ?? 0,
            $data['is_active'] ?? 1,
            $data['show_in_list'] ?? 0,
            $config,
            $fieldId
        ]);
    }

    /**
    * Удаление поля фрагмента
    * @param int $fieldId
    * @return bool
    */
    public function deleteField($fieldId) {
        $fieldsTable = $this->getFieldsTableName();
        $sql = "DELETE FROM `{$fieldsTable}` WHERE id = ?";
        return $this->db->query($sql, [$fieldId]);
    }

    /**
    * Обновление порядка полей
    * @param array $order
    * @return bool
    */
    public function reorderFields($order) {
        $fieldsTable = $this->getFieldsTableName();
        $success = true;
        foreach ($order as $item) {
            $sql = "UPDATE `{$fieldsTable}` SET sort_order = ? WHERE id = ?";
            $result = $this->db->query($sql, [$item['order'], $item['id']]);
            if (!$result) $success = false;
        }
        return $success;
    }

    /**
    * Проверка существования системного имени поля
    * @param int $fragmentId
    * @param string $systemName
    * @param int|null $excludeId
    * @return bool
    */
    public function isFieldSystemNameExists($fragmentId, $systemName, $excludeId = null) {
        $fieldsTable = $this->getFieldsTableName();
        $sql = "SELECT COUNT(*) as count FROM `{$fieldsTable}` 
                WHERE fragment_id = ? AND system_name = ?";
        $params = [$fragmentId, $systemName];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

}