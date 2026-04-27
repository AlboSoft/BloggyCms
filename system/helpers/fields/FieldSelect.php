<?php

/**
* Поле типа "выпадающий список" для системы полей
* @package Fields
*/
class FieldSelect extends Field {
    
    /**
    * Рендерит HTML-код выпадающего списка
    * @param mixed $currentValue Текущее значение поля
    * @return string HTML-код поля
    */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        $attributes = $this->getAttributes();
        
        $isMultiple = strpos($attributes, 'multiple') !== false;
        
        $optionsHtml = '';
        
        $selectedValues = [];
        if ($isMultiple) {
            if (is_string($value)) {
                $selectedValues = explode(',', $value);
                $selectedValues = array_map('trim', $selectedValues);
            } elseif (is_array($value)) {
                $selectedValues = $value;
            } elseif (!empty($value)) {
                $selectedValues = [$value];
            }
        }
        
        foreach ($this->options['options'] as $optionValue => $optionLabel) {
            if ($isMultiple) {
                $selected = in_array($optionValue, $selectedValues) ? ' selected' : '';
            } else {
                $selected = $value == $optionValue ? ' selected' : '';
            }
            $optionsHtml .= "<option value=\"" . htmlspecialchars($optionValue) . "\"{$selected}>" . htmlspecialchars($optionLabel) . "</option>";
        }
        
        $hiddenField = '';
        if ($isMultiple) {
            $baseName = $this->name;
            if (strpos($baseName, 'settings[') === 0) {
                $hiddenName = str_replace('settings[', 'settings[', $baseName);
                $hiddenField = "<input type=\"hidden\" name=\"{$hiddenName}\" value=\"\">";
            } else {
                $hiddenField = "<input type=\"hidden\" name=\"{$baseName}\" value=\"\">";
            }
        }
        
        $fieldHtml = $hiddenField . "<select{$attributes}>{$optionsHtml}</select>";
        
        if ($isMultiple && empty($this->options['hint'])) {
            $this->options['hint'] = LANG_HELPER_FIELDS_SELECT_MULTIPLE_HINT;
        }
        
        return $this->renderFieldGroup($fieldHtml);
    }
    
    /**
    * Получает атрибуты для select
    * @return string Строка с HTML-атрибутами
    */
    protected function getAttributes() {
        $attrs = [];
        
        if ($this->options['admin_mode'] ?? false) {
            $attrs['name'] = $this->name;
        } else {
            $attrs['name'] = "settings[{$this->name}]";
        }
        
        if (!empty($this->options['attributes']['multiple'])) {
            $attrs['name'] .= '[]';
        }
        
        $attrs['class'] = "form-select {$this->options['class']}";
        
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
            if (!empty($value) || $value === '0') {
                $attributesString .= " {$key}=\"" . htmlspecialchars($value) . "\"";
            }
        }
        
        return $attributesString;
    }
}