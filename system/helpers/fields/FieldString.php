<?php

/**
* Поле типа "строка" для системы полей
* @package Fields
*/
class FieldString extends Field {
    
    /**
    * Рендерит HTML-код текстового поля
    * @param mixed $currentValue Текущее значение поля
    * @return string HTML-код поля
    */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        $attributes = $this->getAttributes();
        
        $fieldHtml = "<input type=\"text\" value=\"" . htmlspecialchars($value) . "\"{$attributes}>";
        
        return $this->renderFieldGroup($fieldHtml);
    }
}