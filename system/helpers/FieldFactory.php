<?php

/**
* Фабрика для создания объектов полей различных типов
* @package Fields
*/
class FieldFactory {
    
    /**
    * Создает поле указанного типа 
    * @param string $type Тип поля (string, number, textarea, select, checkbox, image, color, date, repeater, alert, blockimage, icon)
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return Field Объект поля соответствующего класса
    * @throws Exception Если тип поля не существует
    */
    public static function create($type, $name, $options = []) {
        $className = 'Field' . ucfirst($type);
        if (class_exists($className)) {
            return new $className($name, $options);
        }
        throw new Exception("Поле {$type} не существует");
    }
    
    /**
    * Создает строковое поле (input type="text")
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldString
    */
    public static function string($name, $options = []) {
        $defaults = [
            'storage' => 'field',
            'db_field' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('string', $name, $options);
    }
    
    /**
    * Создает числовое поле (input type="number") 
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldNumber
    */
    public static function number($name, $options = []) {
        $defaults = [
            'storage' => 'field',
            'db_field' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('number', $name, $options);
    }
    
    /**
    * Создает текстовую область (textarea) 
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldTextarea
    */
    public static function textarea($name, $options = []) {
        $defaults = [
            'storage' => 'field',
            'db_field' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('textarea', $name, $options);
    }
    
    /**
    * Создает выпадающий список (select) 
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldSelect
    */
    public static function select($name, $options = []) {
        $defaults = [
            'storage' => 'field',
            'db_field' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('select', $name, $options);
    }
    
    /**
    * Создает чекбокс
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldCheckbox
    */
    public static function checkbox($name, $options = []) {
        $defaults = [
            'storage' => 'field',
            'db_field' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('checkbox', $name, $options);
    }

    /**
    * Создает поле для загрузки изображения 
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldImage
    */
    public static function image($name, $options = []) {
        $defaults = [
            'storage' => 'field',
            'db_field' => $name,
            'upload_path' => 'uploads/images/'
        ];
        $options = array_merge($defaults, $options);
        return self::create('image', $name, $options);
    }
    
    /**
    * Создает поле для выбора цвета
    * Использует библиотеку Pickr (https://github.com/simonwep/pickr)
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldColor
    */
    public static function color($name, $options = []) {
        $defaults = [
            'storage' => 'json',
            'json_key' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('color', $name, $options);
    }
    
    /**
    * Создает поле для выбора даты 
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldDate
    */
    public static function date($name, $options = []) {
        $defaults = [
            'storage' => 'field',
            'db_field' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('date', $name, $options);
    }
    
    /**
    * Создает поле-повторитель (repeater) 
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldRepeater
    */
    public static function repeater($name, $options = []) {
        $defaults = [
            'storage' => 'json',
            'json_key' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('repeater', $name, $options);
    }

    /**
    * Создает поле-уведомление (alert)
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldAlert
    */
    public static function alert($name, $options = []) {
        $defaults = [
            'storage' => null
        ];
        $options = array_merge($defaults, $options);
        return self::create('alert', $name, $options);
    }
    
    /**
    * Создает поле для изображения в блоке 
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldBlockImage
    */
    public static function blockImage($name, $options = []) {
        $defaults = [
            'storage' => 'field',
            'db_field' => $name,
            'upload_path' => 'uploads/images/'
        ];
        $options = array_merge($defaults, $options);
        return self::create('blockimage', $name, $options);
    }

    /**
    * Создает поле для выбора иконки 
    * @param string $name Имя поля
    * @param array $options Опции поля
    * @return FieldIcon
    */
    public static function icon($name, $options = []) {
        $defaults = [
            'storage' => 'json',
            'json_key' => $name
        ];
        $options = array_merge($defaults, $options);
        return self::create('icon', $name, $options);
    }

    /**
    * Создает поле с условием показа
    * Добавляет опцию 'show' к параметрам перед созданием 
    * @param string $type Тип поля
    * @param string $name Имя поля
    * @param string $showCondition Условие показа (например "field:parent = value")
    * @param array $options Опции поля
    * @return Field Объект поля с условием
    */
    public static function createWithCondition($type, $name, $showCondition, $options = []) {
        $options['show'] = $showCondition;
        return self::create($type, $name, $options);
    }

}