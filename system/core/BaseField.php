<?php

/**
* Абстрактный базовый класс для всех типов полей
*/
abstract class BaseField {

    /**
    * @var array Конфигурация поля
    */
    protected $config = [];
    
    /**
    * @var string Системное имя поля
    */
    protected $systemName = '';
    
    /**
    * @var string Тип сущности (post, page, user)
    */
    protected $entityType = '';
    
    /**
    * @var int ID сущности
    */
    protected $entityId = 0;
    
    /**
    * @var mixed Подключение к базе данных
    */
    protected $db;
    
    /**
    * Конструктор класса BaseField
    * @param array $config Конфигурация поля
    * @param mixed $db Подключение к базе данных
    */
    public function __construct($config = [], $db = null) {
        $this->config = $config;
        $this->db = $db;
    }
    
    /**
    * Константы для стандартных опций
    */
    const OPTION_SHOW_IN_POST = 'show_in_post';
    const OPTION_SHOW_IN_LIST = 'show_in_list';
    const OPTION_REQUIRED = 'required';
    const OPTION_PLACEHOLDER = 'placeholder';
    
    /**
    * Получить тип поля (текстовое, число, изображение и т.д.)
    * @return string Тип поля
    */
    abstract public function getType(): string;
    
    /**
    * Получить отображаемое имя поля
    * @return string Имя поля
    */
    abstract public function getName(): string;
    
    /**
    * Рендерит поле ввода для формы редактирования
    * @param mixed $value Текущее значение поля
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML код поля ввода
    */
    abstract public function renderInput($value, $entityType, $entityId): string;
    
    /**
    * Рендерит отображение значения поля на фронтенде
    * @param mixed $value Значение поля
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML код для отображения
    */
    abstract public function renderDisplay($value, $entityType, $entityId): string;
    
    /**
    * Рендерит значение поля в списке (админка)
    * @param mixed $value Значение поля
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML код для списка
    */
    abstract public function renderList($value, $entityType, $entityId): string;
    
    /**
    * Валидация значения поля
    * @param mixed $value Значение для валидации
    * @return bool Результат валидации
    */
    public function validate($value): bool {
        if ($this->isRequired() && empty($value)) {
            return false;
        }
        return true;
    }

    /**
    * Устанавливает подключение к БД
    * @param mixed $db Подключение к базе данных
    */
    public function setDatabase($db): void {
        $this->db = $db;
    }
    
    /**
    * Обрабатывает значение перед сохранением
    * @param mixed $value Исходное значение
    * @return mixed Обработанное значение
    */
    public function processValue($value) {
        return $value;
    }
    
    /**
    * Подготавливает конфигурацию для отображения в форме
    * @param array $config Конфигурация поля
    * @return array Подготовленная конфигурация
    */
    public function prepareConfigForForm(array $config): array {
        return $config;
    }

    /**
    * Обрабатывает загрузку файлов для поля
    * @param array $fileData Данные загруженного файла
    * @param mixed $currentValue Текущее значение поля
    * @return mixed Новое значение поля
    */
    public function processFileUpload($fileData, $currentValue = null) {
        return $currentValue;
    }

    /**
    * Возвращает шорткод для этого поля
    * @return string Имя шорткода
    */
    public function getShortcode(): string {
        return $this->getDefaultShortcode();
    }

    /**
    * Возвращает стандартный шорткод для поля
    * @return string Имя шорткода
    */
    protected function getDefaultShortcode(): string {
        $systemName = $this->getSystemName();
        $entityType = $this->getEntityType();
        
        $shortcodeName = $entityType . '_' . $systemName;
        
        Shortcodes::add($shortcodeName, function($attrs) use ($systemName, $entityType) {
            return $this->renderShortcode($attrs);
        });
        
        return $shortcodeName;
    }

    /**
    * Получает значения поля как массив (для итерации)
    * @param mixed $value Значение поля
    * @return array Массив значений
    */
    public function getValuesArray($value): array {
        return [$value];
    }

    /**
    * Получает данные поля для итерации
    * @param mixed $value Значение поля
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return array Структурированные данные для итерации
    */
    public function getIterableData($value, $entityType, $entityId): array {
        $values = $this->getValuesArray($value);
        $options = $this->config['options'] ?? [];
        
        $result = [];
        foreach ($values as $index => $itemValue) {
            $result[] = [
                'value' => $itemValue,
                'label' => $options[$itemValue] ?? $itemValue,
                'index' => $index + 1,
                'is_first' => $index === 0,
                'is_last' => $index === count($values) - 1,
                'is_even' => ($index + 1) % 2 === 0,
                'is_odd' => ($index + 1) % 2 !== 0,
                'total' => count($values)
            ];
        }
        
        return $result;
    }

    /**
    * Рендерит значение для шорткода
    * @param array $attrs Атрибуты шорткода
    * @return string Отрендеренное значение
    */
    public function renderShortcode($attrs): string {
        if (!$this->db) {
            return '';
        }
        
        $entityId = $attrs['id'] ?? $this->entityId;
        $value = $this->getValue($entityId);
        
        if ($value === null || $value === '') {
            return $attrs['default'] ?? '';
        }
        
        return $this->formatShortcodeValue($value, $attrs);
    }

    /**
    * Форматирует значение для шорткода
    * @param mixed $value Значение поля
    * @param array $attrs Атрибуты шорткода
    * @return string Отформатированное значение
    */
    protected function formatShortcodeValue($value, $attrs): string {
        return htmlspecialchars($value);
    }

    /**
    * Получает значение поля для сущности
    * @param int|null $entityId ID сущности
    * @return string|null Значение поля или null
    */
    public function getValue($entityId = null): ?string {
        if (!$this->systemName || !$this->entityType || !$this->db) {
            return null;
        }
        
        if (!$entityId) {
            $entityId = $this->entityId;
        }
        
        if (!$entityId) {
            return null;
        }
        
        $fieldModel = new FieldModel($this->db);
        return $fieldModel->getFieldValue($this->entityType, $entityId, $this->systemName);
    }

    /**
    * Устанавливает контекст сущности
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    */
    public function setEntityContext($entityType, $entityId): void {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }
    
    /**
    * Получает тип сущности
    * @return string Тип сущности
    */
    public function getEntityType(): string {
        return $this->entityType;
    }
    
    /**
    * Определяет, требует ли поле обработки файлов
    * @return bool Требует ли поле загрузки файлов
    */
    public function requiresFileUpload(): bool {
        return false;
    }
    
    /**
    * Обрабатывает удаление файла
    * @param mixed $currentValue Текущее значение поля
    * @return mixed|null Новое значение поля
    */
    public function handleDelete($currentValue) {
        return null;
    }
    
    /**
    * Показывать ли поле в записи (на странице поста)
    * @return bool Результат проверки
    */
    public function showInPost(): bool {
        return (bool)($this->config[self::OPTION_SHOW_IN_POST] ?? true);
    }
    
    /**
    * Показывать ли поле в списке (в архиве постов)
    * @return bool Результат проверки
    */
    public function showInList(): bool {
        return (bool)($this->config[self::OPTION_SHOW_IN_LIST] ?? false);
    }
    
    /**
    * Обязательно ли поле для заполнения
    * @return bool Результат проверки
    */
    public function isRequired(): bool {
        return (bool)($this->config[self::OPTION_REQUIRED] ?? false);
    }
    
    /**
    * Получить плейсхолдер
    * @return string Плейсхолдер поля
    */
    public function getPlaceholder(): string {
        return $this->config[self::OPTION_PLACEHOLDER] ?? '';
    }
    
    /**
    * Получить форму настроек поля
    * @return string HTML код формы настроек
    */
    public function getSettingsForm(): string {
        return $this->getStandardSettingsForm();
    }
    
    /**
    * Стандартная форма настроек с базовыми опциями
    * @return string HTML код стандартной формы
    */
    protected function getStandardSettingsForm(): string {
        $required = $this->isRequired() ? 'checked' : '';
        $showInPost = $this->showInPost() ? 'checked' : '';
        $showInList = $this->showInList() ? 'checked' : '';
        $placeholder = htmlspecialchars($this->getPlaceholder());
        
        return "
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' name='config[required]' value='1' {$required}>
                            <label class='form-check-label'>" . LANG_CORE_BASEFIELD_LABEL_REQUIRED . "</label>
                        </div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' name='config[show_in_post]' value='1' {$showInPost}>
                            <label class='form-check-label'>" . LANG_CORE_BASEFIELD_LABEL_SHOW_IN_POST . "</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' name='config[show_in_list]' value='1' {$showInList}>
                            <label class='form-check-label'>" . LANG_CORE_BASEFIELD_LABEL_SHOW_IN_LIST . "</label>
                        </div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>" . LANG_CORE_BASEFIELD_LABEL_PLACEHOLDER . "</label>
                        <input type='text' class='form-control' name='config[placeholder]' value='{$placeholder}'>
                    </div>
                </div>
            </div>
        ";
    }
    
    /**
    * Получить список колонок для админки
    * @return array Массив колонок
    */
    public function getAdminColumns(): array {
        return [];
    }
    
    /**
    * Получить системное имя поля
    * @return string Системное имя
    */
    public function getSystemName(): string {
        return $this->systemName;
    }
    
    /**
    * Получить конфигурацию поля
    * @return array Конфигурация
    */
    public function getConfig(): array {
        return $this->config;
    }
    
    /**
    * Установить системное имя поля
    * @param string $systemName Системное имя
    */
    public function setSystemName(string $systemName): void {
        $this->systemName = $systemName;
    }
    
    /**
    * Установить конфигурацию поля
    * @param array $config Конфигурация
    */
    public function setConfig(array $config): void {
        $this->config = $config;
    }
    
    /**
    * Установить сущность для поля
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    */
    public function setEntity(string $entityType, int $entityId): void {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }
}