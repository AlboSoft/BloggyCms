<?php

/**
* Вспомогательный класс для безопасного получения значений констант
*/
class ConstantHelper {
    
    /**
    * Получить значение константы или имя константы, если она не определена
    * @param string $constantName Имя константы
    * @param mixed $defaultValue Значение по умолчанию (если не указано, вернется имя константы)
    * @return mixed Значение константы или имя/значение по умолчанию
    */
    public static function get($constantName, $defaultValue = null) {
        if (defined($constantName)) {
            return constant($constantName);
        }
        
        error_log("[LANG MISSING] Undefined language constant: $constantName");
        
        if ($defaultValue === null) {
            return "[{$constantName}]";
        }
        
        return $defaultValue;
    }
    
    /**
    * Проверить, определена ли константа
    * @param string $constantName Имя константы
    * @return bool
    */
    public static function isDefined($constantName) {
        return defined($constantName);
    }
    
    /**
    * Получить список всех определенных констант, начинающихся с LANG_
    * @return array
    */
    public static function getDefinedLanguageConstants() {
        $allConstants = get_defined_constants();
        $langConstants = [];
        
        foreach ($allConstants as $name => $value) {
            if (strpos($name, 'LANG_') === 0) {
                $langConstants[$name] = $value;
            }
        }
        
        return $langConstants;
    }
}

/**
* Глобальная функция для удобного получения значения константы
* @param string $constantName Имя константы
* @param mixed $defaultValue Значение по умолчанию
* @return mixed
*/
function constant_safe($constantName, $defaultValue = null) {
    return ConstantHelper::get($constantName, $defaultValue);
}