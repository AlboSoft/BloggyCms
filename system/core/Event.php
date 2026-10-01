<?php
/**
* Система событий
* Позволяет плагинам и контроллерам реагировать на события ядра
* @package Core
*/
class Event {

    /**
    * @var array Список слушателей событий
    */
    private static $listeners = [];
    
    /**
    * @var array Отложенные слушатели (до инициализации)
    */
    private static $pendingListeners = [];
    
    /**
    * @var bool Флаг инициализации
    */
    private static $initialized = false;
    
    /**
    * Подписывает слушателя на событие
    *
    * @param string $event Название события (например, 'post.created')
    * @param callable $callback Функция-обработчик
    * @param int $priority Приоритет (чем выше, тем раньше выполнится)
    * @param int $acceptedArgs Количество аргументов, передаваемых в callback
    * @return void
    */
    public static function listen($event, $callback, $priority = 10, $acceptedArgs = 1) {
        if (!self::$initialized) {
            self::$pendingListeners[] = [
                'event' => $event,
                'callback' => $callback,
                'priority' => $priority,
                'acceptedArgs' => $acceptedArgs
            ];
            return;
        }
        
        $id = self::getListenerId($callback);
        self::$listeners[$event][$priority][$id] = [
            'callback' => $callback,
            'acceptedArgs' => $acceptedArgs
        ];
    }
    
    /**
    * Запускает событие (триггер)
    *
    * @param string $event Название события
    * @param array $args Аргументы для передачи в слушатели
    * @return mixed Результат последнего обработчика (если есть)
    */
    public static function trigger($event, $args = []) {
        if (!self::$initialized) {
            self::initialize();
        }
        
        if (!isset(self::$listeners[$event])) {
            return null;
        }
        
        krsort(self::$listeners[$event]);
        
        $result = null;
        
        foreach (self::$listeners[$event] as $priority => $listeners) {
            foreach ($listeners as $id => $listener) {
                $callback = $listener['callback'];
                $acceptedArgs = $listener['acceptedArgs'];
                
                $callbackArgs = [];
                if (is_array($args) && $acceptedArgs > 0) {
                    if ($acceptedArgs === 1 && self::isAssoc($args)) {
                        $callbackArgs = [$args];
                    } else {
                        $callbackArgs = array_slice(array_values($args), 0, $acceptedArgs);
                    }
                } elseif (!is_array($args) && $acceptedArgs > 0) {
                    $callbackArgs = [$args];
                }
                
                $callbackResult = call_user_func_array($callback, $callbackArgs);
                
                if ($callbackResult !== null) {
                    $result = $callbackResult;
                }
            }
        }
        
        return $result;
    }
    
    /**
    * Запускает событие с возможностью модификации значения
    * @param string $event Название события
    * @param mixed $value Значение для фильтрации
    * @param array $args Дополнительные аргументы
    * @return mixed Отфильтрованное значение
    */
    public static function filter($event, $value, $args = []) {
        if (!self::$initialized) {
            self::initialize();
        }
        
        if (!isset(self::$listeners[$event])) {
            return $value;
        }
        
        krsort(self::$listeners[$event]);
        
        $result = $value;
        
        foreach (self::$listeners[$event] as $priority => $listeners) {
            foreach ($listeners as $listener) {
                $callback = $listener['callback'];
                $acceptedArgs = $listener['acceptedArgs'];
                
                $callbackArgs = [$result];
                if (is_array($args) && $acceptedArgs > 1) {
                    if ($acceptedArgs === 2 && self::isAssoc($args)) {
                        $callbackArgs[] = $args;
                    } else {
                        $additionalArgs = array_slice(array_values($args), 0, $acceptedArgs - 1);
                        $callbackArgs = array_merge($callbackArgs, $additionalArgs);
                    }
                }
                
                $callbackResult = call_user_func_array($callback, $callbackArgs);
                
                if ($callbackResult !== null) {
                    $result = $callbackResult;
                }
            }
        }
        
        return $result;
    }
    
    /**
    * Отписывает слушателя от события
    * @param string $event Название события
    * @param callable $callback Функция-обработчик
    * @param int $priority Приоритет
    * @return bool Успешность удаления
    */
    public static function unlisten($event, $callback, $priority = 10) {
        $id = self::getListenerId($callback);
        if (isset(self::$listeners[$event][$priority][$id])) {
            unset(self::$listeners[$event][$priority][$id]);
            return true;
        }
        return false;
    }
    
    /**
    * Проверяет, есть ли слушатели у события
    * @param string $event Название события
    * @return bool
    */
    public static function hasListeners($event) {
        return isset(self::$listeners[$event]) && !empty(self::$listeners[$event]);
    }
    
    /**
    * Инициализирует систему событий
    * @return void
    */
    public static function initialize() {
        if (self::$initialized) {
            return;
        }
        
        foreach (self::$pendingListeners as $listener) {
            self::listen(
                $listener['event'],
                $listener['callback'],
                $listener['priority'],
                $listener['acceptedArgs']
            );
        }
        
        self::$pendingListeners = [];
        self::$initialized = true;
    }
    
    /**
    * Получает уникальный ID для callback
    * @param callable $callback
    * @return string
    */
    private static function getListenerId($callback) {
        if (is_string($callback)) {
            return $callback;
        }
        
        if (is_array($callback)) {
            return spl_object_hash($callback[0]) . '::' . $callback[1];
        }
        
        if (is_object($callback) || ($callback instanceof Closure)) {
            return spl_object_hash($callback);
        }
        
        return uniqid();
    }
    
    /**
    * Очищает все слушатели
    * @return void
    */
    public static function reset() {
        self::$listeners = [];
        self::$pendingListeners = [];
        self::$initialized = false;
    }

    /**
    * Проверяет, является ли массив ассоциативным
    * @param array $arr
    * @return bool
    */
    private static function isAssoc(array $arr) {
        if ($arr === []) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}