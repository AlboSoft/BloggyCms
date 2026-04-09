<?php

/**
* Основной класс приложения
* Управляет инициализацией, маршрутизацией и выполнением запросов
*/
class App {

    /**
    * @var Router Объект маршрутизатора
    */
    private $router;
    
    /**
    * @var Database Объект подключения к базе данных
    */
    private $db;

    /**
    * @var array Загруженные хуки
    */
    private $hooks = [];
    
    /**
    * Конструктор класса App
    * Инициализирует сессию, подключение к БД и middleware
    */
    public function __construct() {
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = new Database();
        $this->router = new Router();
        
        AdminAuthMiddleware::handle();
        
        $this->initFieldShortcodes();
        $this->loadControllerHooks();
    }

    /**
    * Загружает все хуки из папок hooks контроллеров
    */
    private function loadControllerHooks() {
        $controllersPath = __DIR__ . '/../controllers';
        
        if (!is_dir($controllersPath)) {
            return;
        }
        
        $controllers = scandir($controllersPath);
        
        foreach ($controllers as $controller) {
            if ($controller === '.' || $controller === '..') {
                continue;
            }
            
            $hooksPath = $controllersPath . '/' . $controller . '/hooks';
            
            if (is_dir($hooksPath)) {
                $hookFiles = glob($hooksPath . '/*.php');
                foreach ($hookFiles as $hookFile) {
                    try {
                        require_once $hookFile;
                    } catch (Exception $e) {
                        error_log("Ошибка загрузки хука {$hookFile}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
    * Загружает все хуки из указанной директории 
    * @param string $hooksPath Путь к папке hooks
    * @param string $controllerName Имя контроллера
    */
    private function loadHooksFromDirectory($hooksPath, $controllerName) {
        $hookFiles = glob($hooksPath . '/*.php');
        
        foreach ($hookFiles as $hookFile) {
            try {
                require_once $hookFile;
                $hookName = basename($hookFile, '.php');
                $this->hooks[$controllerName][$hookName] = $hookFile;
            } catch (Exception $e) {
                error_log("Ошибка загрузки хука {$hookFile}: " . $e->getMessage());
            }
        }
    }
    
    /**
    * Инициализация шорткодов для работы с полями
    * Регистрирует базовые шорткоды и делает подключение к БД глобально доступным
    */
    private function initFieldShortcodes() {
        global $db;
        $db = $this->db;
        
        try {
            $fieldManager = new FieldManager($this->db);
            $fieldManager->registerFieldShortcodes();
            
            $this->registerBaseShortcodes();
            
        } catch (Exception $e) {
            error_log("Ошибка инициализации шорткодов полей: " . $e->getMessage());
        }
    }
    
    /**
    * Регистрация базовых шорткодов для работы с полями
    */
    private function registerBaseShortcodes() {
        global $db;
        
        Shortcodes::add('field', function($attrs) use ($db) {
            $fieldName = $attrs['name'] ?? '';
            $entityType = $attrs['type'] ?? '';
            $entityId = isset($attrs['id']) ? (int)$attrs['id'] : 0;
            
            if (empty($fieldName) || empty($entityType) || $entityId <= 0) {
                return $attrs['default'] ?? '';
            }
            
            try {
                $fieldModel = new FieldModel($db);
                $value = $fieldModel->getFieldValue($entityType, $entityId, $fieldName);
                
                return $value !== null ? htmlspecialchars($value) : ($attrs['default'] ?? '');
                
            } catch (Exception $e) {
                return $attrs['default'] ?? '';
            }
        });
        
        Shortcodes::add('field_display', function($attrs) use ($db) {
            $fieldName = $attrs['name'] ?? '';
            $entityType = $attrs['type'] ?? '';
            $entityId = isset($attrs['id']) ? (int)$attrs['id'] : 0;
            
            if (empty($fieldName) || empty($entityType) || $entityId <= 0) {
                return $attrs['default'] ?? '';
            }
            
            try {
                $fieldModel = new FieldModel($db);
                $fieldManager = new FieldManager($db);
                
                $field = $fieldModel->getFieldBySystemName($fieldName, $entityType);
                
                if (!$field) {
                    return $attrs['default'] ?? '';
                }
                
                $value = $fieldModel->getFieldValue($entityType, $entityId, $fieldName);
                
                if ($value === null || $value === '') {
                    return $attrs['default'] ?? '';
                }
                
                $config = json_decode($field['config'] ?? '{}', true);
                return $fieldManager->renderFieldDisplay(
                    $field['type'],
                    $value,
                    $config,
                    $entityType,
                    $entityId
                );
                
            } catch (Exception $e) {
                return $attrs['default'] ?? '';
            }
        });
        
        if (class_exists('FragmentHelper')) {
            if (method_exists('FragmentHelper', 'registerShortcodes')) {
                FragmentHelper::registerShortcodes();
            }
        }
    }
    
    /**
    * Основной метод запуска приложения
    * Определяет маршрут и вызывает соответствующий контроллер
    * @throws Exception Если контроллер или действие не найдены
    */
    public function run() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $route = $this->router->match($uri);
    
        if ($route) {
            $controllerName = $route['controller'] . 'Controller';
            $actionName = $route['action'] . 'Action';
            
            if (!class_exists($controllerName)) {
                throw new Exception("Controller class {$controllerName} not found");
            }
            
            $controller = new $controllerName($this->db);

            $params = $route['params'] ?? [];


            $this->callControllerAction($controller, $actionName, $params);
        } else {
            header("HTTP/1.0 404 Not Found");
            require TEMPLATES_PATH . '/' . DEFAULT_TEMPLATE . '/404.php';
        }
    }
    
    /**
    * Вызов действия контроллера с передачей параметров
    * @param object $controller Объект контроллера
    * @param string $action Название метода действия
    * @param array $params Параметры для передачи в метод
    * @throws Exception Если метод не найден или не хватает параметров
    */
    private function callControllerAction($controller, $action, $params) {
        if (!method_exists($controller, $action)) {
            throw new Exception("Action {$action} not found in controller " . get_class($controller));
        }
    
        $method = new ReflectionMethod($controller, $action);
        $args = [];
        
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new Exception("Missing required parameter: {$name}");
            }
        }
    
        call_user_func_array([$controller, $action], $args);
    }

    /**
    * Получение объекта маршрутизатора
    * @return Router Объект маршрутизатора
    */
    public function getRouter() {
        return $this->router;
    }

    /**
    * Получение объекта подключения к базе данных
    * @return Database Объект подключения к БД
    */
    public function getDb() {
        return $this->db;
    }
}