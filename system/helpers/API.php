<?php
/**
* API Helper - Единый интерфейс для работы с моделями 
* Автоматически находит все модели, реализующие ModelAPI
*/

class API {
    
    private static $instances = [];
    private static $modelsMap = null;
    
    /**
    * Сканирует все модели в системе
    */
    private static function scanModels() {
        if (self::$modelsMap !== null) {
            return;
        }
        
        self::$modelsMap = [];
        $controllersPath = __DIR__ . '/../controllers/';
        
        if (!is_dir($controllersPath)) {
            return;
        }
        
        $items = @scandir($controllersPath) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $itemPath = $controllersPath . $item;
            
            if (!is_dir($itemPath)) {
                continue;
            }
            
            $modelFile = $itemPath . '/Model.php';
            if (!file_exists($modelFile)) {
                continue;
            }
            
            require_once $modelFile;
            
            $declaredClasses = get_declared_classes();
            foreach ($declaredClasses as $className) {
                if (!in_array('ModelAPI', class_implements($className))) {
                    continue;
                }
                
                if (method_exists($className, 'getModelInfo')) {
                    $info = $className::getModelInfo();
                    self::$modelsMap[$info['name']] = $info;
                }
            }
        }
    }
    
    /**
    * Магический вызов
    */
    public static function __callStatic($name, $arguments) {
        self::scanModels();
        
        if (strpos($name, '_') !== false) {
            $parts = explode('_', $name, 2);
            return self::call($parts[0], $parts[1], $arguments);
        }
        
        return self::model($name);
    }
    
    /**
    * Получить прокси для модели
    */
    public static function model($name) {
        self::scanModels();
        
        if (!isset(self::$modelsMap[$name])) {
            return null;
        }
        
        return new class($name) {
            private $modelName;
            
            public function __construct($name) {
                $this->modelName = $name;
            }
            
            public function __call($method, $args) {
                return API::call($this->modelName, $method, $args);
            }
        };
    }
    
    /**
    * Вызвать метод модели
    */
    public static function call($modelName, $method, $args = []) {
        self::scanModels();
        
        if (!isset(self::$modelsMap[$modelName])) {
            throw new Exception("Model '{$modelName}' not found");
        }
        
        $info = self::$modelsMap[$modelName];
        $key = $info['name'];
        
        if (isset(self::$instances[$key])) {
            $model = self::$instances[$key];
        } else {
            $db = Database::getInstance();
            $model = new $info['class']($db);
            self::$instances[$key] = $model;
        }
        
        return $model->callAPI($method, $args);
    }
    
    /**
    * Получить список всех доступных моделей
    */
    public static function getAvailableModels() {
        self::scanModels();
        return self::$modelsMap;
    }
    
    /**
    * Получить методы модели
    */
    public static function getModelMethods($modelName) {
        self::scanModels();
        
        if (!isset(self::$modelsMap[$modelName])) {
            return [];
        }
        
        $info = self::$modelsMap[$modelName];
        $db = Database::getInstance();
        $model = new $info['class']($db);
        
        return $model->getAPIMethods();
    }

    /**
    * Проверить существование модели
    */
    public static function hasModel($modelName) {
        self::scanModels();
        return isset(self::$modelsMap[$modelName]);
    }
    
    /**
    * Очистить кэш
    */
    public static function clearCache() {
        self::$instances = [];
        self::$modelsMap = null;
    }
}