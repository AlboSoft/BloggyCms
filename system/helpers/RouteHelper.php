<?php

/**
* Вспомогательный класс для работы с маршрутами
* @package Core
*/
class RouteHelper {
    
    /**
    * Получает ВСЕ фронтенд маршруты из всех контроллеров 
    * @return array Массив маршрутов с полями: route, controller, action, name, module
    */
    public static function getAllFrontendRoutes() {
        $routes = [];
        
        $routes[] = [
            'route' => '*',
            'controller' => 'All',
            'action' => 'All',
            'name' => LANG_HELPER_ROUTE_ALL_PAGES
        ];
        
        $routes[] = [
            'route' => 'home',
            'controller' => 'Home',
            'action' => 'index',
            'name' => LANG_HELPER_ROUTE_HOMEPAGE
        ];
        
        $routes[] = [
            'route' => '404',
            'controller' => 'Error',
            'action' => 'notFound',
            'name' => LANG_HELPER_ROUTE_PAGE_404
        ];
        
        $routes[] = [
            'route' => '500',
            'controller' => 'Error',
            'action' => 'serverError',
            'name' => LANG_HELPER_ROUTE_PAGE_500
        ];
        
        $controllersDir = __DIR__ . '/../controllers/';
        
        if (!is_dir($controllersDir)) {
            return $routes;
        }
        
        $controllerFolders = scandir($controllersDir);
        
        foreach ($controllerFolders as $folder) {
            if ($folder === '.' || $folder === '..' || !is_dir($controllersDir . $folder)) {
                continue;
            }
            
            $routesFile = $controllersDir . $folder . '/routes.php';
            
            if (file_exists($routesFile)) {
                $controllerRoutes = include $routesFile;
                
                foreach ($controllerRoutes as $routePattern => $config) {

                    if (isset($config['admin']) && $config['admin'] === true) {
                        continue;
                    }
                    
                    $name = self::generateRouteName($config['controller'], $config['action'], $folder);
                    
                    $routes[] = [
                        'route' => $routePattern,
                        'controller' => $config['controller'],
                        'action' => $config['action'],
                        'name' => $name,
                        'module' => $folder
                    ];
                }
            }
        }
        
        usort($routes, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $routes;
    }
    
    /**
    * Получает маршруты для определенного контроллера
    * 
    * @param string $controllerName Имя контроллера (например 'Post', 'User')
    * @return array Отфильтрованные маршруты
    */
    public static function getRoutesForController($controllerName) {
        $allRoutes = self::getAllFrontendRoutes();
        
        return array_filter($allRoutes, function($route) use ($controllerName) {
            return $route['controller'] === $controllerName && $route['route'] !== '*';
        });
    }
    
    /**
    * Генерирует понятное имя для маршрута 
    * @param string $controller Имя контроллера
    * @param string $action Имя действия
    * @param string $module Имя модуля (опционально)
    * @return string Понятное название маршрута
    */
    private static function generateRouteName($controller, $action, $module = '') {
        $nameMap = [
            'Post' => [
                'index' => LANG_HELPER_ROUTE_POSTS_LIST,
                'show' => LANG_HELPER_ROUTE_POST_PAGE,
                'all' => LANG_HELPER_ROUTE_ALL_POSTS
            ],
            'Category' => [
                'index' => LANG_HELPER_ROUTE_CATEGORIES_LIST,
                'show' => LANG_HELPER_ROUTE_CATEGORY_PAGE
            ],
            'Tag' => [
                'index' => LANG_HELPER_ROUTE_TAGS_LIST,
                'show' => LANG_HELPER_ROUTE_TAG_PAGE
            ],
            'Page' => [
                'index' => LANG_HELPER_ROUTE_PAGES,
                'show' => LANG_HELPER_ROUTE_PAGE
            ],
            'User' => [
                'index' => LANG_HELPER_ROUTE_USERS_LIST,
                'show' => LANG_HELPER_ROUTE_USER_PROFILE
            ],
            'Search' => [
                'index' => LANG_HELPER_ROUTE_SEARCH
            ],
            'Archive' => [
                'index' => LANG_HELPER_ROUTE_ARCHIVE
            ],
            'HtmlBlock' => [
                'show' => LANG_HELPER_ROUTE_HTML_BLOCK
            ],
            'Profile' => [
                'index' => LANG_HELPER_ROUTE_PROFILE,
                'show' => LANG_HELPER_ROUTE_USER_PROFILE,
                'edit' => LANG_HELPER_ROUTE_EDIT_PROFILE
            ],
            'Auth' => [
                'login' => LANG_HELPER_ROUTE_LOGIN,
                'register' => LANG_HELPER_ROUTE_REGISTER,
                'logout' => LANG_HELPER_ROUTE_LOGOUT
            ]
        ];
        
        if (isset($nameMap[$controller][$action])) {
            return $nameMap[$controller][$action];
        }
        
        $controllerName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $controller);
        $actionName = ucfirst($action);
        
        return $controllerName . ' - ' . $actionName;
    }
    
}