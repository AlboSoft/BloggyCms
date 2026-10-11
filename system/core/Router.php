<?php

/**
* Маршрутизатор для обработки URL запросов
*/
class Router {

    /**
    * @var array Маршруты системы
    */
    private $routes = [];
    
    /**
    * @var array Маршруты модулей
    */
    private $moduleRoutes = [];
    
    /**
    * Конструктор Router
    */
    public function __construct() {
        $this->loadModuleRoutes();
    }

    /**
    * Загружает маршруты модулей
    */
    private function loadModuleRoutes() {
        static $loaded = false;

        if ($loaded) {
            return;
        }
        
        $loaded = true;

        $controllersDir = dirname(__DIR__) . '/controllers/';
        
        if (!is_dir($controllersDir)) {
            return;
        }
        
        $modules = glob($controllersDir . '*', GLOB_ONLYDIR) ?: [];
        
        $allRoutes = [];
        
        foreach ($modules as $moduleDir) {
            $routesFile = $moduleDir . '/routes.php';
            if (file_exists($routesFile)) {
                $moduleRoutes = require $routesFile;
                
                foreach ($moduleRoutes as $pattern => $route) {
                    if (isset($allRoutes[$pattern])) {
                        continue;
                    }
                    $allRoutes[$pattern] = $route;
                }
            }
        }
        
        uksort($allRoutes, function($a, $b) {
            $aHasDynamic = strpos($a, '{') !== false;
            $bHasDynamic = strpos($b, '{') !== false;
            
            if (!$aHasDynamic && $bHasDynamic) {
                return -1;
            }
            if ($aHasDynamic && !$bHasDynamic) {
                return 1;
            }
            
            $aParts = count(explode('/', $a));
            $bParts = count(explode('/', $b));
            
            if ($aParts !== $bParts) {
                return $bParts - $aParts;
            }
            
            return strlen($b) - strlen($a);
        });
        
        foreach ($allRoutes as $pattern => $route) {
            $this->moduleRoutes[$pattern] = $route;
        }
    }
    
    /**
    * Сопоставляет URI с маршрутом
    * @param string $uri URI для сопоставления
    * @return array|false Данные маршрута или false
    */
    public function match($uri) {
        $uri = $this->cleanUri($uri);
        
        if ($this->isStaticFile($uri)) {
            return false;
        }

        if ($uri === '') {
            foreach ($this->moduleRoutes as $pattern => $route) {
                if ($pattern === '') {
                    return $route;
                }
            }
        }

        foreach ($this->moduleRoutes as $pattern => $route) {
            if ($pattern === '') {
                continue;
            }
            
            if ($this->checkPattern($pattern, $uri, $route)) {
                return $route;
            }
        }
        
        return false;
    }

    /**
    * Проверяет соответствие паттерна URI
    * @param string $pattern Паттерн маршрута
    * @param string $uri URI для проверки
    * @param array $route Данные маршрута
    * @return bool Соответствует ли URI паттерну
    */
    private function checkPattern($pattern, $uri, &$route) {
        if ($pattern === '' && $uri === '') {
            return true;
        }
        
        $pattern = trim($pattern, '/');
        $uri = trim($uri, '/');
        
        if (strpos($pattern, '{') === false) {
            if ($pattern === $uri) {
                return true;
            }
            return false;
        }
        
        $regex = '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?<$1>[^/]+)', $pattern) . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            
            if (!isset($route['params'])) {
                $route['params'] = [];
            }
            $route['params'] = array_merge($route['params'], $params);
            
            return true;
        }
        
        return false;
    }
    
    /**
    * Очищает URI от базового URL
    * @param string $uri Исходный URI
    * @return string Очищенный URI
    */
    private function cleanUri($uri) {
        $baseUrl = BASE_URL;
        if (!empty($baseUrl) && strpos($uri, $baseUrl) === 0) {
            $uri = substr($uri, strlen($baseUrl));
        }
        
        $uri = trim($uri, '/');
        $uri = preg_replace('#/{2,}#', '/', $uri);
        
        return $uri;
    }

    /**
    * Проверяет, является ли URI статическим файлом
    * @param string $uri URI для проверки
    * @return bool Является ли статическим файлом
    */
    private function isStaticFile($uri) {
        $staticExtensions = [
            'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg',
            'woff', 'woff2', 'ttf', 'eot', 'map', 'txt', 'xml'
        ];
        
        $extension = pathinfo($uri, PATHINFO_EXTENSION);
        return in_array(strtolower($extension), $staticExtensions);
    }
}