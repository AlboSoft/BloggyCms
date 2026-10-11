<?php

/**
 * Менеджер для управления маршрутами системы
 */
class RouteManager {
    /**
     * @var array Загруженные контроллеры и их маршруты
     */
    private $controllers = [];
    
    /**
     * Конструктор RouteManager
     */
    public function __construct() {
        $this->loadAllRoutes();
    }
    
    /**
     * Загружает все маршруты из контроллеров
     */
    private function loadAllRoutes() {
        $controllersDir = __DIR__ . '/../controllers/';
        $controllerFolders = @scandir($controllersDir) ?: [];
        foreach ($controllerFolders as $folder) {
            if ($folder === '.' || $folder === '..' || !is_dir($controllersDir . $folder)) {
                continue;
            }
            
            $routesFile = $controllersDir . $folder . '/routes.php';
            if (file_exists($routesFile)) {
                $routes = include $routesFile;
                $this->processRoutes($folder, $routes);
            }
        }
    }
    
    /**
     * Обрабатывает маршруты контроллера
     *
     * @param string $controllerName Имя контроллера
     * @param array $routes Маршруты контроллера
     */
    private function processRoutes($controllerName, $routes) {
        foreach ($routes as $route => $config) {
            if (isset($config['admin']) && $config['admin'] === true) {
                continue;
            }
            
            if (strpos($route, '{') !== false) {
                continue;
            }
            
            $this->controllers[] = [
                'route' => $route,
                'controller' => $config['controller'],
                'action' => $config['action'],
                'module' => $controllerName
            ];
        }
    }
    
    /**
     * Получает все фронтенд маршруты
     *
     * @return array Маршруты фронтенда
     */
    public function getFrontendRoutes() {
        return $this->controllers;
    }
    
    /**
     * Получает все возможные маршруты (включая специальные)
     *
     * @return array Все маршруты системы
     */
    public function getAllPossibleRoutes() {
        $allRoutes = [];
        
        $allRoutes[] = [
            'route' => '*',
            'controller' => 'All',
            'action' => 'All',
            'name' => 'Все страницы'
        ];
        
        $allRoutes[] = [
            'route' => 'home',
            'controller' => 'Home',
            'action' => 'index',
            'name' => 'Главная страница'
        ];
        
        $allRoutes[] = [
            'route' => '404',
            'controller' => 'Error',
            'action' => 'notFound',
            'name' => 'Страница 404'
        ];
        
        $allRoutes[] = [
            'route' => 'search',
            'controller' => 'Search',
            'action' => 'index',
            'name' => 'Поиск'
        ];
        
        foreach ($this->controllers as $route) {
            $allRoutes[] = [
                'route' => $route['route'],
                'controller' => $route['controller'],
                'action' => $route['action'],
                'name' => $this->generateRouteName($route)
            ];
        }
        
        return $allRoutes;
    }
    
    /**
     * Генерирует читаемое имя для маршрута
     *
     * @param array $route Данные маршрута
     * @return string Имя маршрута
     */
    private function generateRouteName($route) {
        $controller = $route['controller'];
        $action = $route['action'];
        
        $names = [
            'Post' => [
                'index' => 'Список постов',
                'show' => 'Страница поста',
                'all' => 'Все посты'
            ],
            'Category' => [
                'index' => 'Список категорий',
                'show' => 'Страница категории'
            ],
            'Tag' => [
                'index' => 'Список тегов',
                'show' => 'Страница тега'
            ],
            'User' => [
                'index' => 'Список пользователей',
                'show' => 'Профиль пользователя'
            ],
            'Page' => [
                'show' => 'Страница'
            ]
        ];
        
        if (isset($names[$controller][$action])) {
            return $names[$controller][$action];
        }
        
        return $controller . '::' . $action;
    }
}