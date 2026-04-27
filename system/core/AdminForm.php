<?php

/**
* Базовый класс для админских CRUD-форм
* @package Core
*/
abstract class AdminForm {
    
    /**
    * @var object|null Подключение к базе данных
    */
    protected $db;
    
    /**
    * @var array Обработанные данные формы
    */
    protected $data = [];
    
    /**
    * @var array Ошибки валидации
    */
    protected $errors = [];
    
    /**
    * @var bool Был ли отправлена форма
    */
    protected $isSubmitted = false;
    
    /**
    * @var array Исходные данные (из $_POST)
    */
    protected $rawData = [];
    
    /**
    * @var array Загруженные файлы (из $_FILES)
    */
    protected $files = [];
    
    /**
    * Конструктор формы
    * @param object|null $db Подключение к БД
    */
    public function __construct($db = null) {
        $this->db = $db ?: (Database::getInstance() ?? null);
    }
    
    /**
    * Возвращает заголовок формы
    * @return string
    */
    public function getTitle(): string {
        return '';
    }
    
    /**
    * Возвращает массив Fieldset для рендеринга формы
    * Должен быть переопределен в дочерних классах
    * @return array
    */
    abstract public function getFieldsets(): array;
    
    /**
    * Возвращает маппинг полей для сохранения
    * По умолчанию собирается из Fieldset, но может быть переопределен
    * @return array
    */
    public function getFieldMapping(): array {
        $mapping = [];
        $fieldsets = $this->getFieldsets();
        
        foreach ($fieldsets as $fieldset) {
            // Получаем поля через рефлексию (нужно будет добавить метод в Fieldset)
            // Пока оставляем пустым, дочерние классы будут определять маппинг явно
        }
        
        return $mapping;
    }
    
    /**
    * Рендерит форму
    * @param array $currentData Текущие данные для заполнения формы
    * @return string HTML формы
    */
    public function render(array $currentData = []): string {
        $fieldsets = $this->getFieldsets();
        $html = '';
        
        foreach ($fieldsets as $fieldset) {
            $html .= $fieldset->render($currentData);
        }
        
        return $html;
    }
    
    /**
    * Обрабатывает отправку формы
    * @param array $postData Данные из $_POST
    * @param array $files Данные из $_FILES
    * @return bool true если данные валидны и обработаны
    */
    public function handleRequest(array $postData, array $files = []): bool {
        $this->isSubmitted = true;
        $this->rawData = $postData;
        $this->files = $files;
        
        $this->data = $this->prepareData($postData, $files);
        $this->validate();
        
        return $this->isValid();
    }
    
    /**
    * Подготавливает данные перед сохранением
    * Разделяет данные на поля БД и JSON
    * @param array $postData Данные из $_POST
    * @param array $files Данные из $_FILES
    * @return array Массив с ключами 'fields' и 'json'
    */
    public function prepareData(array $postData, array $files): array {
        $result = [
            'fields' => [],
            'json' => []
        ];
        
        $mapping = $this->getFieldMapping();
        
        foreach ($mapping as $fieldName => $config) {
            $value = $postData[$fieldName] ?? null;
            $storage = $config['storage'] ?? 'field';
            
            switch ($storage) {
                case 'field':
                    $dbField = $config['db_field'] ?? $fieldName;
                    $result['fields'][$dbField] = $value;
                    break;
                    
                case 'json':
                    $jsonKey = $config['json_key'] ?? $fieldName;
                    $result['json'][$jsonKey] = $value;
                    break;
            }
        }
        
        return $result;
    }
    
    /**
    * Валидация данных
    * Может быть переопределена в дочерних классах
    */
    public function validate(): void {
        $fieldsets = $this->getFieldsets();
    }
    
    /**
    * Проверяет, валидна ли форма
    * @return bool
    */
    public function isValid(): bool {
        return empty($this->errors);
    }
    
    /**
    * Возвращает обработанные данные
    * @return array
    */
    public function getData(): array {
        return $this->data;
    }
    
    /**
    * Возвращает поля для сохранения в БД
    * @return array
    */
    public function getFieldsData(): array {
        return $this->data['fields'] ?? [];
    }
    
    /**
    * Возвращает JSON-данные для сохранения
    * @return array
    */
    public function getJsonData(): array {
        return $this->data['json'] ?? [];
    }
    
    /**
    * Возвращает ошибки валидации
    * @return array
    */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
    * Добавляет ошибку валидации
    * @param string $field Поле
    * @param string $message Сообщение об ошибке
    */
    protected function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
    * Проверяет, была ли отправлена форма
    * @return bool
    */
    public function isSubmitted(): bool {
        return $this->isSubmitted;
    }
    
    /**
    * Возвращает CSRF-токен для формы
    * @return string
    */
    public function getCsrfToken(): string {
        return CsrfToken::generate(get_class($this));
    }
    
    /**
    * Проверяет CSRF-токен
    * @param string $token
    * @return bool
    */
    public function verifyCsrfToken(string $token): bool {
        return CsrfToken::verify($token, get_class($this));
    }
    
    /**
    * Заполняет форму данными из массива (для редактирования)
    * @param array $data Данные для заполнения
    * @return array Данные в формате для рендеринга
    */
    public function populate(array $data): array {
        $formData = [];
        $mapping = $this->getFieldMapping();
        
        foreach ($mapping as $fieldName => $config) {
            $storage = $config['storage'] ?? 'field';
            
            if ($storage === 'field') {
                $dbField = $config['db_field'] ?? $fieldName;
                $formData[$fieldName] = $data[$dbField] ?? null;
            } elseif ($storage === 'json') {
                $jsonKey = $config['json_key'] ?? $fieldName;
                $jsonData = $data['settings'] ?? [];
                if (is_string($jsonData)) {
                    $jsonData = json_decode($jsonData, true) ?: [];
                }
                $formData[$fieldName] = $jsonData[$jsonKey] ?? null;
            }
        }
        
        return $formData;
    }
}