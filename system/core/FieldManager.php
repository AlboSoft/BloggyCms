<?php

/**
* Менеджер для управления полями в системе
*/
class FieldManager {

    /**
    * @var mixed Подключение к базе данных
    */
    private $db;
    
    /**
    * @var array Зарегистрированные классы полей
    */
    private static $fieldClasses = [];
    
    /**
    * @var array Зарегистрированные шорткоды полей
    */
    private $registeredShortcodes = [];
    
    /**
    * @var string Путь к директории с полями
    */
    private $fieldsPath;
    
    /**
    * @var bool Флаг загрузки классов полей
    */
    private static $loaded = false;
    
    /**
    * Конструктор FieldManager
    * @param mixed $db Подключение к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
        $this->fieldsPath = SYSTEM_PATH . '/fields';
        
        if (!self::$loaded) {
            $this->loadFieldClasses();
            self::$loaded = true;
        }
    }
    
    /**
    * Загружает классы полей
    */
    private function loadFieldClasses(): void {
        self::$fieldClasses = [];
        
        $this->loadCoreFields();
        
        if (empty(self::$fieldClasses)) {
            $this->createDefaultFields();
            $this->loadCoreFields();
        }
    }
    
    /**
    * Загружает базовые поля из системной папки
    */
    private function loadCoreFields(): void {
        if (!is_dir($this->fieldsPath)) {
            mkdir($this->fieldsPath, 0755, true);
        }
        
        $files = glob($this->fieldsPath . '/*.php');
        
        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            
            require_once $file;
            
            if (class_exists($className)) {
                try {
                    $instance = new $className();
                    if ($instance instanceof BaseField) {
                        $type = $instance->getType();
                        if (!isset(self::$fieldClasses[$type])) {
                            self::$fieldClasses[$type] = $className;
                        }
                    }
                } catch (Exception $e) {}
            }
        }
    }

    /**
    * Регистрирует шорткоды для всех полей
    */
    public function registerFieldShortcodes(): void {
        try {
            $fieldModel = new FieldModel($this->db);
            $allFields = $fieldModel->getAll();
            
            foreach ($allFields as $field) {
                if (!$field['is_active']) {
                    continue;
                }
                
                $this->registerFieldShortcode($field);
            }
        } catch (Exception $e) {

        }
    }

    /**
    * Регистрирует шорткод для конкретного поля
    * @param array $field Данные поля
    */
    private function registerFieldShortcode(array $field): void {
        $fieldInstance = $this->getFieldInstance($field['type'], json_decode($field['config'] ?? '{}', true));
        
        if (!$fieldInstance) {
            return;
        }
        
        $fieldInstance->setDatabase($this->db);
        $fieldInstance->setSystemName($field['system_name']);
        $fieldInstance->setEntityContext($field['entity_type'], 0);
        
        $shortcodeName = $fieldInstance->getShortcode();
        $this->registeredShortcodes[$field['id']] = $shortcodeName;
    }

    /**
    * Получает шорткод для поля по ID
    * @param int $fieldId ID поля
    * @return string|null Имя шорткода
    */
    public function getFieldShortcode($fieldId): ?string {
        return $this->registeredShortcodes[$fieldId] ?? null;
    }
    
    /**
    * Получает все зарегистрированные шорткоды полей
    * @return array Массив шорткодов
    */
    public function getRegisteredShortcodes(): array {
        return $this->registeredShortcodes;
    }

    /**
    * Обрабатывает значение поля
    * @param array $field Данные поля
    * @param array $postData POST данные
    * @param array $filesData FILES данные
    * @param array $currentValues Текущие значения
    * @return mixed Обработанное значение
    * @throws Exception При некорректных данных поля
    */
    public function processFieldValue($field, $postData, $filesData, $currentValues = []) {
        $deleteKey = 'field_' . $field['system_name'] . '_delete';

        if (!is_array($field) || !isset($field['type']) || !isset($field['system_name'])) {
            throw new Exception(LANG_CORE_FIELDMANAGER_ERROR_INVALID_FIELD_DATA);
        }
        
        $fieldType = $field['type'];
        $systemName = $field['system_name'];
        $config = is_array($field['config']) ? $field['config'] : json_decode($field['config'] ?? '{}', true);
        
        $fieldInstance = $this->getFieldInstance($fieldType, $config);
        if (!$fieldInstance) {
            return null;
        }
        
        $fieldInstance->setSystemName($systemName);
        
        $currentValue = $currentValues[$systemName] ?? null;
        
        if ($fieldInstance->requiresFileUpload()) {
            $fileKey = 'field_' . $systemName;
            $deleteKey = $fileKey . '_delete';
            
            if (isset($postData[$deleteKey])) {
                if ($postData[$deleteKey] == '1' || $postData[$deleteKey] === 'on') {
                    if (method_exists($fieldInstance, 'handleDelete')) {
                        $fieldInstance->handleDelete($currentValue);
                        return null;
                    }
                    return null;
                }
            }
            
            if (isset($filesData[$fileKey]) && !empty($filesData[$fileKey]['tmp_name'])) {
                try {
                    return $fieldInstance->processFileUpload($filesData[$fileKey], $currentValue);
                } catch (Exception $e) {
                    throw $e;
                }
            }
            
            return $currentValue;
        } else {
            $postKey = 'field_' . $systemName;

            if ($fieldType === 'flag') {
                $value = isset($postData[$postKey]) ? $postData[$postKey] : '0';
                return $fieldInstance->processValue($value);
            } 
            else if (isset($postData[$postKey])) {
                $value = $postData[$postKey];
                return $fieldInstance->processValue($value);
            }
        }
        
        return null;
    }

    /**
    * Получает текущие значения всех полей для сущности
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return array Массив значений
    */
    public function getCurrentFieldValues($entityType, $entityId) {
        $fieldModel = new Field($this->db);
        $fields = $fieldModel->getActiveByEntityType($entityType);
        $values = [];
        
        foreach ($fields as $field) {
            $values[$field['system_name']] = $fieldModel->getFieldValue($entityType, $entityId, $field['system_name']);
        }
        
        return $values;
    }

    /**
    * Обрабатывает конфигурацию поля
    * @param string $type Тип поля
    * @param array $config Конфигурация
    * @return array Обработанная конфигурация
    */
    public function processFieldConfig(string $type, array $config) {
        $field = $this->getFieldInstance($type, $config);
        if (!$field) {
            return $config;
        }

        if (method_exists($field, 'processConfig')) {
            return $field->processConfig($config);
        }

        return $config;
    }

    /**
    * Валидирует значение поля
    * @param array $field Данные поля
    * @param mixed $value Значение поля
    * @param array $postData POST данные
    * @param array $filesData FILES данные
    * @return array Результат валидации
    */
    public function validateFieldValue($field, $value, $postData, $filesData): array {
        if (!is_array($field) || !isset($field['type']) || !isset($field['system_name'])) {
            return ['is_valid' => false, 'message' => LANG_CORE_FIELDMANAGER_ERROR_INVALID_FIELD_DATA];
        }
        
        $fieldType = $field['type'];
        $systemName = $field['system_name'];
        $config = is_array($field['config']) ? $field['config'] : json_decode($field['config'] ?? '{}', true);
        
        $fieldInstance = $this->getFieldInstance($fieldType, $config);
        if (!$fieldInstance) {
            return ['is_valid' => true];
        }
        
        $fieldInstance->setSystemName($systemName);
        
        if ($fieldInstance->requiresFileUpload()) {
            $fileKey = 'field_' . $systemName;
            $fileData = $filesData[$fileKey] ?? null;
            
            if (isset($config['required']) && $config['required']) {
                $deleteKey = $fileKey . '_delete';
                $isDeleting = isset($postData[$deleteKey]) && $postData[$deleteKey];
                
                if (!$isDeleting && (empty($fileData) || empty($fileData['tmp_name'])) && empty($value)) {
                    return [
                        'is_valid' => false,
                        'message' => sprintf(LANG_CORE_FIELDMANAGER_ERROR_FIELD_REQUIRED, $field['name'])
                    ];
                }
            }
        } else {
            $postKey = 'field_' . $systemName;

            if ($fieldType === 'flag') {
                $fieldValue = isset($postData[$postKey]) ? $postData[$postKey] : '0';
            } else {
                $fieldValue = $postData[$postKey] ?? null;
            }

            if (isset($config['required']) && $config['required'] && empty($fieldValue)) {
                return [
                    'is_valid' => false,
                    'message' => sprintf(LANG_CORE_FIELDMANAGER_ERROR_FIELD_REQUIRED, $field['name'])
                ];
            }
            if (!empty($fieldValue) && !$fieldInstance->validate($fieldValue)) {
                return [
                    'is_valid' => false,
                    'message' => sprintf(LANG_CORE_FIELDMANAGER_ERROR_FIELD_INVALID, $field['name'])
                ];
            }
        }
        
        return ['is_valid' => true];
    }
    
    /**
    * Создает базовые поля по умолчанию
    */
    private function createDefaultFields(): void {
        $defaultFields = [
            'StringField' => "<?php
            class StringField extends BaseField {
                public function getType(): string { 
                    return 'string'; 
                }
                
                public function getName(): string { 
                    return '" . LANG_CORE_FIELDMANAGER_FIELD_STRING_NAME . "'; 
                }
                
                public function renderInput(\$value, \$entityType, \$entityId): string {
                    \$placeholder = \$this->config['placeholder'] ?? '';
                    \$maxlength = \$this->config['maxlength'] ?? '';
                    \$required = isset(\$this->config['required']) && \$this->config['required'] ? 'required' : '';
                    
                    return \"<input type='text' 
                                name='field_{$this->systemName}' 
                                value='\" . htmlspecialchars(\$value) . \"'
                                class='form-control form-control-sm'
                                placeholder='\" . htmlspecialchars(\$placeholder) . \"'
                                \" . (\$maxlength ? \"maxlength='{\$maxlength}'\" : \"\") . \" 
                                {\$required}>\";
                }
                
                public function renderDisplay(\$value, \$entityType, \$entityId): string {
                    return \"<span class='field-string'>\" . htmlspecialchars(\$value) . \"</span>\";
                }
                
                public function renderList(\$value, \$entityType, \$entityId): string {
                    \$truncated = mb_strlen(\$value) > 50 ? mb_substr(\$value, 0, 50) . '...' : \$value;
                    return \"<span title='\" . htmlspecialchars(\$value) . \"'>\" . htmlspecialchars(\$truncated) . \"</span>\";
                }
                
                public function validate(\$value): bool {
                    if (isset(\$this->config['required']) && \$this->config['required'] && empty(\$value)) {
                        return false;
                    }
                    
                    if (!empty(\$this->config['maxlength']) && mb_strlen(\$value) > \$this->config['maxlength']) {
                        return false;
                    }
                    
                    if (!empty(\$this->config['pattern']) && !preg_match(\$this->config['pattern'], \$value)) {
                        return false;
                    }
                    
                    return true;
                }
                
                public function getSettingsForm(): string {
                    \$placeholder = htmlspecialchars(\$this->config['placeholder'] ?? '');
                    \$maxlength = htmlspecialchars(\$this->config['maxlength'] ?? '');
                    \$pattern = htmlspecialchars(\$this->config['pattern'] ?? '');
                    \$defaultValue = htmlspecialchars(\$this->config['default_value'] ?? '');
                    
                    return \"
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_PLACEHOLDER . \"</label>
                                    <input type='text' class='form-control' name='config[placeholder]' value='{\$placeholder}'>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_MAXLENGTH . \"</label>
                                    <input type='number' class='form-control' name='config[maxlength]' value='{\$maxlength}' min='1'>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_PATTERN . \"</label>
                                    <input type='text' class='form-control' name='config[pattern]' value='{\$pattern}' placeholder='^[a-zA-Z0-9]+$'>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_DEFAULT_VALUE . \"</label>
                                    <input type='text' class='form-control' name='config[default_value]' value='{\$defaultValue}'>
                                </div>
                            </div>
                        </div>
                    \";
                }
            }",
            
            'TextField' => "<?php
            class TextField extends BaseField {
                public function getType(): string { 
                    return 'text'; 
                }
                
                public function getName(): string { 
                    return '" . LANG_CORE_FIELDMANAGER_FIELD_TEXT_NAME . "'; 
                }
                
                public function renderInput(\$value, \$entityType, \$entityId): string {
                    \$placeholder = \$this->config['placeholder'] ?? '';
                    \$rows = \$this->config['rows'] ?? 4;
                    \$required = isset(\$this->config['required']) && \$this->config['required'] ? 'required' : '';
                    
                    return \"<textarea name='field_{$this->systemName}' 
                                class='form-control form-control-sm'
                                rows='{\$rows}'
                                placeholder='\" . htmlspecialchars(\$placeholder) . \"'
                                {\$required}>\" . htmlspecialchars(\$value) . \"</textarea>\";
                }
                
                public function renderDisplay(\$value, \$entityType, \$entityId): string {
                    return \"<div class='field-text'>\" . nl2br(htmlspecialchars(\$value)) . \"</div>\";
                }
                
                public function renderList(\$value, \$entityType, \$entityId): string {
                    \$truncated = mb_strlen(\$value) > 100 ? mb_substr(\$value, 0, 100) . '...' : \$value;
                    return \"<span title='\" . htmlspecialchars(\$value) . \"'>\" . htmlspecialchars(\$truncated) . \"</span>\";
                }
                
                public function getSettingsForm(): string {
                    \$placeholder = htmlspecialchars(\$this->config['placeholder'] ?? '');
                    \$rows = htmlspecialchars(\$this->config['rows'] ?? '4');
                    \$defaultValue = htmlspecialchars(\$this->config['default_value'] ?? '');
                    
                    return \"
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_PLACEHOLDER . \"</label>
                                    <input type='text' class='form-control' name='config[placeholder]' value='{\$placeholder}'>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_ROWS . \"</label>
                                    <input type='number' class='form-control' name='config[rows]' value='{\$rows}' min='1' max='20'>
                                </div>
                            </div>
                        </div>
                        <div class='mb-3'>
                            <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_DEFAULT_VALUE . \"</label>
                            <textarea class='form-control' name='config[default_value]' rows='3'>{\$defaultValue}</textarea>
                        </div>
                    \";
                }
            }",
            
            'NumberField' => "<?php
            class NumberField extends BaseField {
                public function getType(): string { 
                    return 'number'; 
                }
                
                public function getName(): string { 
                    return '" . LANG_CORE_FIELDMANAGER_FIELD_NUMBER_NAME . "'; 
                }
                
                public function renderInput(\$value, \$entityType, \$entityId): string {
                    \$placeholder = \$this->config['placeholder'] ?? '';
                    \$min = \$this->config['min'] ?? '';
                    \$max = \$this->config['max'] ?? '';
                    \$step = \$this->config['step'] ?? '1';
                    \$required = isset(\$this->config['required']) && \$this->config['required'] ? 'required' : '';
                    
                    return \"<input type='number' 
                            name='field_{$this->systemName}' 
                            value='\" . htmlspecialchars(\$value) . \"'
                            class='form-control form-control-sm'
                            placeholder='\" . htmlspecialchars(\$placeholder) . \"'
                            \" . (\$min !== '' ? \"min='{\$min}'\" : \"\") . \"
                            \" . (\$max !== '' ? \"max='{\$max}'\" : \"\") . \"
                            step='{\$step}'
                            {\$required}>\";
                }
                
                public function renderDisplay(\$value, \$entityType, \$entityId): string {
                    return \"<span class='field-number'>\" . htmlspecialchars(\$value) . \"</span>\";
                }
                
                public function renderList(\$value, \$entityType, \$entityId): string {
                    return \"<span class='field-number'>\" . htmlspecialchars(\$value) . \"</span>\";
                }
                
                public function getSettingsForm(): string {
                    \$min = htmlspecialchars(\$this->config['min'] ?? '');
                    \$max = htmlspecialchars(\$this->config['max'] ?? '');
                    \$step = htmlspecialchars(\$this->config['step'] ?? '1');
                    \$placeholder = htmlspecialchars(\$this->config['placeholder'] ?? '');
                    \$defaultValue = htmlspecialchars(\$this->config['default_value'] ?? '');
                    
                    return \"
                        <div class='row'>
                            <div class='col-md-4'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_MIN . \"</label>
                                    <input type='number' class='form-control' name='config[min]' value='{\$min}'>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_MAX . \"</label>
                                    <input type='number' class='form-control' name='config[max]' value='{\$max}'>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_STEP . \"</label>
                                    <input type='number' class='form-control' name='config[step]' value='{\$step}' step='0.01'>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_PLACEHOLDER . \"</label>
                                    <input type='text' class='form-control' name='config[placeholder]' value='{\$placeholder}'>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>\" . LANG_CORE_FIELDMANAGER_SETTINGS_DEFAULT_VALUE . \"</label>
                                    <input type='number' class='form-control' name='config[default_value]' value='{\$defaultValue}'>
                                </div>
                            </div>
                        </div>
                    \";
                }
            }"
        ];
        
        foreach ($defaultFields as $className => $code) {
            $filePath = $this->fieldsPath . '/' . $className . '.php';
            if (!file_exists($filePath)) {
                file_put_contents($filePath, $code);
            }
        }
    }
    
    /**
    * Регистрирует тип поля
    * @param string $type Тип поля
    * @param string $className Имя класса
    */
    public function registerFieldType(string $type, string $className): void {
        self::$fieldClasses[$type] = $className;
    }
    
    /**
    * Получает экземпляр поля
    * @param string $type Тип поля
    * @param array $config Конфигурация поля
    * @return BaseField|null Экземпляр поля
    */
    public function getFieldInstance(string $type, $config = []): ?BaseField {
        if (is_string($config)) {
            $decoded = json_decode($config, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $config = $decoded;
            } else {
                $config = [];
            }
        }
        
        if (!is_array($config)) {
            $config = [];
        }
        
        if (!isset(self::$fieldClasses[$type])) {
            return null;
        }
        
        $className = self::$fieldClasses[$type];
        if (!class_exists($className)) {
            return null;
        }
        
        try {
            $instance = new $className($config);
            
            if (!($instance instanceof BaseField)) {
                return null;
            }
            
            return $instance;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
    * Получает доступные типы полей
    * @return array Массив типов полей
    */
    public function getAvailableFieldTypes(): array {
        $types = [];
        foreach (self::$fieldClasses as $type => $className) {
            $instance = $this->getFieldInstance($type);
            if ($instance) {
                $types[$type] = $instance->getName();
            }
        }
        
        asort($types);
        
        return $types;
    }

    /**
    * Создает обертку для полей старого формата
    * @param string $type Тип поля
    * @param array $config Конфигурация
    * @param string $className Имя класса
    * @return BaseField|null Экземпляр обертки
    */
    private function createWrapperInstance(string $type, array $config, string $className): ?BaseField {
        try {
            $instance = new $className($config);
            
            return new class($instance, $type, $config, $this->db) extends BaseField {
                private $wrappedInstance;
                private $typeName;
                
                public function __construct($instance, $typeName, $config, $db) {
                    parent::__construct($config, $db);
                    $this->wrappedInstance = $instance;
                    $this->typeName = $typeName;
                }
                
                public function getType(): string {
                    return $this->typeName;
                }
                
                public function getName(): string {
                    if (method_exists($this->wrappedInstance, 'getName')) {
                        return $this->wrappedInstance->getName();
                    }
                    return $this->typeName;
                }
                
                public function renderInput($value, $entityType, $entityId): string {
                    if (method_exists($this->wrappedInstance, 'renderInput')) {
                        return $this->wrappedInstance->renderInput($value, $entityType, $entityId);
                    }
                    return "<input type='text' name='field_{$this->systemName}' value='" . htmlspecialchars($value) . "'>";
                }
                
                public function renderDisplay($value, $entityType, $entityId): string {
                    if (method_exists($this->wrappedInstance, 'renderDisplay')) {
                        return $this->wrappedInstance->renderDisplay($value, $entityType, $entityId);
                    }
                    return htmlspecialchars($value);
                }
                
                public function renderList($value, $entityType, $entityId): string {
                    if (method_exists($this->wrappedInstance, 'renderList')) {
                        return $this->wrappedInstance->renderList($value, $entityType, $entityId);
                    }
                    return htmlspecialchars(mb_strlen($value) > 50 ? mb_substr($value, 0, 50) . '...' : $value);
                }
                
                public function getShortcode(): string {
                    $shortcodeName = $this->entityType . '_' . $this->systemName;
                    
                    Shortcodes::add($shortcodeName, function($attrs) {
                        return $this->renderShortcode($attrs);
                    });
                    
                    return $shortcodeName;
                }
            };
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
    * Рендерит поле ввода
    * @param string $type Тип поля
    * @param string $systemName Системное имя
    * @param mixed $value Значение
    * @param array $config Конфигурация
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML код поля ввода
    */
    public function renderFieldInput(string $type, string $systemName, $value, array $config, string $entityType, int $entityId): string {
        $field = $this->getFieldInstance($type, $config);
        if (!$field) {
            return "<input type='text' name='field_{$systemName}' value='" . htmlspecialchars($value) . "' class='form-control form-control-sm'>";
        }
        
        $field->setSystemName($systemName);
        $field->setEntity($entityType, $entityId);
        return $field->renderInput($value, $entityType, $entityId);
    }
    
    /**
    * Рендерит отображение значения поля
    * @param string $type Тип поля
    * @param mixed $value Значение
    * @param array $config Конфигурация
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML код отображения
    */
    public function renderFieldDisplay(string $type, $value, array $config, string $entityType, int $entityId): string {
        $field = $this->getFieldInstance($type, $config);
        if (!$field) {
            return htmlspecialchars($value ?? '');
        }
        
        $field->setSystemName('');
        $field->setEntity($entityType, $entityId);
        $field->setConfig($config);
        
        return $field->renderDisplay($value ?? '', $entityType, $entityId);
    }
    
    /**
    * Рендерит значение поля для списка
    * @param string $type Тип поля
    * @param mixed $value Значение
    * @param array $config Конфигурация
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML код для списка
    */
    public function renderFieldList(string $type, $value, array $config, string $entityType, int $entityId): string {
        $field = $this->getFieldInstance($type, $config);
        if (!$field) {
            $truncated = mb_strlen($value) > 50 ? mb_substr($value, 0, 50) . '...' : $value;
            return htmlspecialchars($truncated);
        }
        
        $field->setEntity($entityType, $entityId);
        return $field->renderList($value, $entityType, $entityId);
    }
    
    /**
    * Получает форму настроек поля
    * @param string $type Тип поля
    * @param array $config Конфигурация
    * @return string HTML форма настроек
    */
    public function getFieldSettingsForm(string $type, array $config = []): string {
        $field = $this->getFieldInstance($type, $config);
        if (!$field) {
            return '<div class="alert alert-warning">' . LANG_CORE_FIELDMANAGER_SETTINGS_NOT_FOUND . '</div>';
        }
        
        return $field->getSettingsForm();
    }
    
    /**
    * Получает зарегистрированные типы полей
    * @return array Массив типов полей
    */
    public function getRegisteredFieldTypes(): array {
        return array_keys(self::$fieldClasses);
    }
    
    /**
    * Проверяет, зарегистрирован ли тип поля
    * @param string $type Тип поля
    * @return bool Зарегистрирован ли тип
    */
    public function isFieldTypeRegistered(string $type): bool {
        return isset(self::$fieldClasses[$type]);
    }

    /**
    * Обрабатывает загрузку файла для поля
    * @param string $fieldType Тип поля
    * @param array $fileData Данные файла
    * @param mixed $currentValue Текущее значение
    * @param array $config Конфигурация
    * @return mixed Результат обработки
    * @throws Exception Если поле не поддерживает загрузку
    */
    public function handleFileUpload($fieldType, $fileData, $currentValue = null, $config = []) {
        $field = $this->getFieldInstance($fieldType, $config);
        if (!$field || !method_exists($field, 'handleUpload')) {
            throw new Exception(sprintf(LANG_CORE_FIELDMANAGER_ERROR_UPLOAD_NOT_SUPPORTED, $fieldType));
        }
        
        return $field->handleUpload($fileData, $currentValue);
    }

    /**
    * Обрабатывает удаление файла для поля
    * @param string $fieldType Тип поля
    * @param mixed $currentValue Текущее значение
    * @param array $config Конфигурация
    * @return mixed Результат удаления
    * @throws Exception Если поле не поддерживает удаление
    */
    public function handleFileDelete($fieldType, $currentValue, $config = []) {
        $field = $this->getFieldInstance($fieldType, $config);
        if (!$field || !method_exists($field, 'handleDelete')) {
            throw new Exception(sprintf(LANG_CORE_FIELDMANAGER_ERROR_DELETE_NOT_SUPPORTED, $fieldType));
        }
        
        return $field->handleDelete($currentValue);
    }

    /**
    * Получает поля для отображения в записи
    * @param string $entityType Тип сущности
    * @return array Массив полей
    */
    public function getFieldsForPostDisplay($entityType) {
        $fieldModel = new Field($this->db);
        $fields = $fieldModel->getActiveByEntityType($entityType);
        
        return array_filter($fields, function($field) {
            return $field['show_in_post'] == 1;
        });
    }

    /**
    * Получает поля для отображения в списке
    * @param string $entityType Тип сущности
    * @return array Массив полей
    */
    public function getFieldsForListDisplay($entityType) {
        $fieldModel = new Field($this->db);
        $fields = $fieldModel->getActiveByEntityType($entityType);
        
        return array_filter($fields, function($field) {
            return $field['show_in_list'] == 1;
        });
    }
}