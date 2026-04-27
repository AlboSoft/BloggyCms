<?php

/**
* Поле типа "текстовая область" для системы полей
* @package Fields
*/
class FieldTextarea extends Field {
    
    /**
    * Рендерит HTML-код текстовой области
    * @param mixed $currentValue Текущее значение поля
    * @return string HTML-код поля
    */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        $attributes = $this->getAttributes();
        
        $rows = isset($this->options['rows']) ? " rows=\"{$this->options['rows']}\"" : ' rows="3"';
        
        $fieldHtml = "<textarea{$attributes}{$rows}>" . htmlspecialchars($value) . "</textarea>";
        
        return $this->renderFieldGroup($fieldHtml);
    }
}