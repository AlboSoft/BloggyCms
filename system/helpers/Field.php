<?php

/**
* Абстрактный базовый класс для всех типов полей в системе управления формами
* @package Fields
*/
abstract class Field {
    
    /** @var string Имя поля */
    protected $name;
    
    /** @var array Опции и конфигурация поля */
    protected $options;
    
    /**
    * Конструктор поля
    * @param string $name Имя поля
    * @param array $options Опции поля
    */
    public function __construct($name, $options = []) {
        $this->name = $name;
        $this->options = array_merge([
            'title' => '',
            'hint' => '',
            'default' => '',
            'required' => false,
            'placeholder' => '',
            'class' => '',
            'attributes' => [],
            'show' => null,
            'show_class' => 'field-conditional',
            'column' => null,
            'full_width' => false,
            'storage' => 'field',
            'db_field' => null,
            'json_key' => null
        ], $options);
        
        if ($this->options['db_field'] === null) {
            $this->options['db_field'] = $name;
        }
    }
    
    /**
    * Получает имя поля
    * @return string
    */
    public function getName() {
        return $this->name;
    }
    
    /**
    * Получает имя поля в БД (для storage=field)
    * @return string
    */
    public function getDbField() {
        return $this->options['db_field'];
    }
    
    /**
    * Получает тип хранения
    * @return string
    */
    public function getStorage() {
        return $this->options['storage'];
    }
    
    /**
    * Получает ключ в JSON (для storage=json)
    * @return string|null
    */
    public function getJsonKey() {
        return $this->options['json_key'];
    }
    
    /**
    * Проверяет, обязательно ли поле
    * @return bool
    */
    public function isRequired() {
        return $this->options['required'];
    }
    
    /**
    * Получает все опции поля 
    * @return array Массив опций
    */
    public function getOptions() {
        return $this->options;
    }
    
    /**
    * Получает ширину колонки для поля
    * @return int|null Ширина колонки (от 1 до 12) или null если не задана
    */
    public function getColumnWidth() {
        return $this->options['column'] ?? null;
    }
    
    /**
    * Проверяет, должно ли поле занимать всю ширину
    * @return bool
    */
    public function isFullWidth() {
        if (isset($this->options['full_width']) && $this->options['full_width'] === true) {
            return true;
        }
        
        if ($this instanceof FieldAlert) {
            return true;
        }
        
        if (isset($this->options['column']) && $this->options['column'] == 12) {
            return true;
        }
        
        return false;
    }
    
    /**
    * Абстрактный метод для рендеринга поля 
    * @param mixed $currentValue Текущее значение поля
    * @return string HTML-код поля
    */
    abstract public function render($currentValue = null);
    
    /**
    * Генерирует строку с HTML-атрибутами для поля ввода 
    * @return string Строка с HTML-атрибутами
    */
    protected function getAttributes() {
        $attrs = [];
        
        if ($this->options['admin_mode'] ?? false) {
            $attrs['name'] = $this->name;
        } else {
            $attrs['name'] = "settings[{$this->name}]";
        }
        
        $isMultiple = !empty($this->options['attributes']['multiple']) && $this->options['attributes']['multiple'] === true;
        
        if ($isMultiple) {
            $attrs['name'] .= '[]';
        }

        if ($this instanceof FieldSelect) {
            $attrs['class'] = "form-select {$this->options['class']}";
        } else {
            $attrs['class'] = "form-control {$this->options['class']}";
        }
        
        $attrs['placeholder'] = $this->options['placeholder'];
        
        if ($this->options['required']) {
            $attrs['required'] = 'required';
        }
        
        foreach ($this->options['attributes'] as $key => $value) {
            if ($key === 'multiple' && $value === true) {
                $attrs['multiple'] = 'multiple';
            } elseif ($key === 'size') {
                $attrs['size'] = (int)$value;
            } else {
                $attrs[$key] = $value;
            }
        }
        
        $attributesString = '';
        foreach ($attrs as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            if ($value === true) {
                $attributesString .= " {$key}";
            } else {
                $attributesString .= " {$key}=\"" . htmlspecialchars($value) . "\"";
            }
        }
        
        return $attributesString;
    }
    
    /**
    * Оборачивает HTML поля в стандартную группу с заголовком и подсказкой
    * @param string $fieldHtml HTML-код поля
    * @param array $formData Данные формы для условного отображения
    * @return string Полный HTML группы поля
    */
    protected function renderFieldGroup($fieldHtml, $formData = []) {
        $requiredBadge = $this->options['required'] ? ' <span class="text-danger">*</span>' : '';
        $hint = $this->options['hint'] ? '<div class="form-text">' . htmlspecialchars($this->options['hint']) . '</div>' : '';
        
        $conditionalAttrs = $this->getConditionalAttributes($formData);
        
        $dependencyIndicator = '';
        if ($this->isConditional()) {
            $dependencyIndicator = $this->renderDependencyIndicator($formData);
        }
        
        $conditionalClass = $this->isConditional() ? 'field-dependent' : '';
        $columnDataAttr = $this->getColumnWidth() ? ' data-column="' . $this->getColumnWidth() . '"' : '';
        
        return "
        <div class=\"mb-3 {$conditionalClass}\" data-field-name=\"{$this->name}\"{$columnDataAttr} {$conditionalAttrs}>
            {$dependencyIndicator}
            <label class=\"form-label\">{$this->options['title']}{$requiredBadge}</label>
            {$fieldHtml}
            {$hint}
        </div>";
    }

    /**
    * Рендерит индикатор зависимости для условного поля 
    * @param array $formData Данные формы
    * @return string HTML индикатора
    */
    protected function renderDependencyIndicator($formData) {
        $showCondition = $this->options['show'] ?? null;
        if (empty($showCondition)) {
            return '';
        }
        
        $parentField = $this->extractParentFieldFromCondition($showCondition);
        if (!$parentField) {
            return '';
        }
        
        $isVisible = $this->shouldShow($formData);
        $visibilityClass = $isVisible ? '' : 'dependency-hidden';
        
        return '';
    }

    /**
    * Извлекает имя родительского поля из условия
    * @param string $condition Условие показа
    * @return string|null Имя поля или null
    */
    protected function extractParentFieldFromCondition($condition) {
        if (preg_match('/^field:(\w+)/', $condition, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
    * Получает заголовок родительского поля
    * @param string $parentFieldName Имя родительского поля
    * @return string Заголовок поля
    */
    protected function getParentFieldTitle($parentFieldName) {
        return $parentFieldName;
    }

    /**
    * Получает заголовок поля 
    * @return string
    */
    public function getTitle() {
        return $this->options['title'] ?? '';
    }
    
    /**
    * Получает подсказку поля
    * @return string
    */
    public function getHint() {
        return $this->options['hint'] ?? '';
    }
    
    /**
    * Получает placeholder поля 
    * @return string
    */
    public function getPlaceholder() {
        return $this->options['placeholder'] ?? '';
    }
    
    /**
    * Получает минимальное значение (для числовых полей)
    * @return mixed
    */
    public function getMin() {
        return $this->options['min'] ?? null;
    }
    
    /**
    * Получает максимальное значение (для числовых полей)
    * @return mixed
    */
    public function getMax() {
        return $this->options['max'] ?? null;
    }
    
    /**
    * Получает список опций (для select и multiselect)
    * @return array
    */
    public function getSelectOptions() {
        return $this->options['options'] ?? [];
    }

    /**
    * Проверяет, должно ли поле отображаться на основе условия 
    * @param array $formData Данные формы
    * @return bool true если поле должно быть видимо
    */
    public function shouldShow($formData) {
        $showCondition = $this->options['show'] ?? null;
        
        if (empty($showCondition)) {
            return true;
        }
        
        return $this->evaluateShowCondition($showCondition, $formData);
    }

    /**
    * Парсит и вычисляет условие показа поля
    * @param string $condition Условие
    * @param array $formData Данные формы
    * @return bool Результат проверки
    */
    protected function evaluateShowCondition($condition, $formData) {
        if (preg_match('/^field:(\w+)\s*([!=<>]+)\s*(.+)$/', $condition, $matches)) {
            $targetField = $matches[1];
            $operator = $matches[2];
            $expectedValue = $matches[3];
            $actualValue = $this->getFieldValue($targetField, $formData);
            return $this->compareValues($actualValue, $operator, $expectedValue);
        }
        
        if (preg_match('/^field:(\w+)$/', $condition, $matches)) {
            $targetField = $matches[1];
            $actualValue = $this->getFieldValue($targetField, $formData);
            return (bool)$actualValue;
        }
        
        if (preg_match('/^field:(\w+)\s+in\s+(.+)$/', $condition, $matches)) {
            $targetField = $matches[1];
            $expectedValues = array_map('trim', explode(',', $matches[2]));
            $actualValue = $this->getFieldValue($targetField, $formData);
            return in_array($actualValue, $expectedValues);
        }
        
        return true;
    }

    /**
    * Получает значение поля из данных формы 
    * @param string $fieldName Имя поля
    * @param array $formData Данные формы
    * @return mixed Значение поля или null
    */
    protected function getFieldValue($fieldName, $formData) {
        if (isset($formData['settings'][$fieldName])) {
            return $formData['settings'][$fieldName];
        }
        if (isset($formData[$fieldName])) {
            return $formData[$fieldName];
        }
        return null;
    }

    /**
    * Сравнивает значения по оператору
    * @param mixed $actual Фактическое значение
    * @param string $operator Оператор сравнения
    * @param mixed $expected Ожидаемое значение
    * @return bool Результат сравнения
    */
    protected function compareValues($actual, $operator, $expected) {
        if ($expected === 'true') $expected = true;
        if ($expected === 'false') $expected = false;
        if (is_numeric($expected)) $expected = (float)$expected;
        if (is_numeric($actual)) $actual = (float)$actual;
        
        switch ($operator) {
            case '=': case '==': return $actual == $expected;
            case '!=': return $actual != $expected;
            case '>': return $actual > $expected;
            case '<': return $actual < $expected;
            case '>=': return $actual >= $expected;
            case '<=': return $actual <= $expected;
            default: return false;
        }
    }

    /**
    * Получает HTML-атрибуты для условного отображения через JavaScript 
    * @param array $formData Данные формы
    * @return string Строка с data-атрибутами
    */
    public function getConditionalAttributes($formData) {
        $showCondition = $this->options['show'] ?? null;
        
        if (empty($showCondition)) {
            return '';
        }
        
        $isVisible = $this->shouldShow($formData);
        $hiddenClass = $isVisible ? '' : 'd-none';
        
        return " data-conditional=\"true\" data-condition=\"" . htmlspecialchars($showCondition) . "\" class=\"field-conditional {$hiddenClass}\"";
    }

    /**
    * Получает условие показа поля 
    * @return string|null Условие или null
    */
    public function getShowCondition() {
        return $this->options['show'] ?? null;
    }

    /**
    * Проверяет, является ли поле условным (зависит от другого поля) 
    * @return bool true если поле условное
    */
    public function isConditional() {
        return !empty($this->options['show']);
    }
}