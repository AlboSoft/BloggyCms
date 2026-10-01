<?php

/**
* Менеджер для управления контроллерами приложения
*/
class ControllerManager {

    /**
    * @var mixed Подключение к базе данных
    */
    private $db;
    
    /**
    * @var array Массив обнаруженных контроллеров
    */
    private $controllers = [];
    
    /**
    * Конструктор ControllerManager
    * @param mixed $db Подключение к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
        $this->discoverControllers();
    }
    
    /**
    * Обнаруживает все контроллеры в системе
    */
    private function discoverControllers() {
        $controllersPath = BASE_PATH . '/system/controllers';
        $this->scanControllersDirectory($controllersPath);
    }
    
    /**
    * Рекурсивно сканирует директорию с контроллерами
    * @param string $path Путь к директории
    */
    private function scanControllersDirectory($path) {
        if (!is_dir($path)) return;
        
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $path . '/' . $item;
            
            if (is_dir($fullPath)) {
                $this->findControllersInDirectory($fullPath, $item);
                $this->scanControllersDirectory($fullPath);
            }
        }
    }

    /**
    * Ищет контроллеры в директории
    * @param string $dirPath Путь к директории
    * @param string $dirName Имя директории
    */
    private function findControllersInDirectory($dirPath, $dirName) {
        $phpFiles = glob($dirPath . '/*.php');
        
        foreach ($phpFiles as $phpFile) {
            $fileName = basename($phpFile, '.php');
            
            if (preg_match('/(.*)Controller$/', $fileName, $matches)) {
                $controllerName = $matches[1];
                $this->loadControllerInfo($dirName, $controllerName, $phpFile);
            }
        }
    }
    
    /**
    * Загружает информацию о контроллере
    * @param string $dirName Имя директории
    * @param string $controllerName Имя контроллера
    * @param string $controllerFile Путь к файлу контроллера
    */
    private function loadControllerInfo($dirName, $controllerName, $controllerFile) {

        if (!file_exists($controllerFile)) return;
        
        try {
            if (class_exists('ConstantHelper')) {
                ConstantHelper::ensureFileConstants($controllerFile);
            }
            require_once $controllerFile;
        } catch (\Throwable $e) {
            if (class_exists('ConstantHelper') && ConstantHelper::handleUndefinedConstantError($e)) {
                try {
                    require_once $controllerFile;
                } catch (\Throwable $retryError) {
                    return;
                }
            } else {
                return;
            }
        }
        
        $className = basename($controllerFile, '.php');
        
        if (class_exists($className)) {
            $attempts = 0;
            while (true) {
                try {
                    $controller = new $className($this->db);
                    
                    $info = [
                        'name' => $this->getControllerDisplayName($controllerName),
                        'author' => 'BloggyCMS',
                        'version' => '1.0.0',
                        'has_settings' => false,
                        'description' => '',
                        'class' => $className,
                        'key' => strtolower($dirName),
                        'directory' => $dirName
                    ];
                    
                    if (method_exists($controller, 'getControllerInfo')) {
                        $controllerInfo = $controller->getControllerInfo();
                        $info = array_merge($info, $controllerInfo);
                    }
                    
                    $info['has_settings'] = $this->checkControllerHasSettings($dirName, $controllerFile);
                    
                    $this->controllers[$info['key']] = $info;
                    break;
                } catch (\Throwable $e) {
                    if ($attempts < 10 && class_exists('ConstantHelper') && ConstantHelper::handleUndefinedConstantError($e)) {
                        $attempts++;
                        continue;
                    }
                    break;
                }
            }
        }
    }
    
    /**
    * Преобразует имя контроллера в отображаемое имя
    * @param string $controllerName Имя контроллера
    * @return string Отображаемое имя
    */
    private function getControllerDisplayName($controllerName) {
        $name = preg_replace('/^Admin/', '', $controllerName);
        $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);
        return ucfirst($name);
    }
    
    /**
    * Проверяет наличие настроек у контроллера
    * @param string $dirName Имя директории
    * @param string $controllerFile Путь к файлу контроллера
    * @return bool Есть ли настройки
    */
    private function checkControllerHasSettings($dirName, $controllerFile) {
        $controllerDir = dirname($controllerFile);
        $settingsFile = $controllerDir . '/Settings.php';

        if (!file_exists($settingsFile)) {
            return false;
        }
        
        $fileContent = file_get_contents($settingsFile);
        
        if (!preg_match('/class\s+(\w+)/', $fileContent, $matches)) {
            return false;
        }
        
        $className = $matches[1];
        
        $namespace = '';
        if (preg_match('/namespace\s+([^;]+);/', $fileContent, $nsMatches)) {
            $namespace = $nsMatches[1];
        }
        
        $hasGetForm = strpos($fileContent, 'static function getForm') !== false || 
                     strpos($fileContent, 'public static function getForm') !== false ||
                     strpos($fileContent, 'function getForm') !== false;
        return $hasGetForm;
    }
    
    /**
    * Получить контроллеры с настройками
    * @return array Массив контроллеров с настройками
    */
    public function getControllersWithSettings() {
        return array_filter($this->controllers, function($controller) {
            return $controller['has_settings'];
        });
    }
    
    /**
    * Получить контроллер по ключу
    * @param string $key Ключ контроллера
    * @return array|null Данные контроллера
    */
    public function getController($key) {
        return $this->controllers[$key] ?? null;
    }
    
    /**
    * Получить настройки контроллера
    * @param string $key Ключ контроллера
    * @return mixed Настройки контроллера
    */
    public function getControllerSettings($key) {
        $settingsModel = new SettingsModel($this->db);
        return $settingsModel->get('controller_' . $key);
    }
    
    /**
    * Сохранить настройки контроллера
    * @param string $key Ключ контроллера
    * @param mixed $settings Настройки для сохранения
    * @return bool Результат сохранения
    */
    public function saveControllerSettings($key, $settings) {
        $settingsModel = new SettingsModel($this->db);
        return $settingsModel->save('controller_' . $key, $settings);
    }
    
    /**
    * Получить HTML форму настроек контроллера
    * @param string $key Ключ контроллера
    * @param mixed $currentSettings Текущие настройки
    * @return string HTML форма настроек
    */
    public function getControllerSettingsForm($key, $currentSettings) {
        $controller = $this->getController($key);
        if (!$controller || !$controller['has_settings']) {
            return '';
        }

        $controllerDir = BASE_PATH . '/system/controllers/' . $controller['directory'];
        $settingsFile = $controllerDir . '/Settings.php';
        
        if (!file_exists($settingsFile)) {
            return '';
        }

        $fileContent = file_get_contents($settingsFile);
        
        if (preg_match('/class\s+(\w+)/', $fileContent, $matches)) {
            $className = $matches[1];
            
            $namespace = '';
            if (preg_match('/namespace\s+([^;]+);/', $fileContent, $nsMatches)) {
                $namespace = $nsMatches[1] . '\\';
            }
            
            $fullClassName = $namespace . $className;
            
            if (class_exists('ConstantHelper')) {
                ConstantHelper::ensureFileConstants($settingsFile);
            }

            try {
                require_once $settingsFile;
            } catch (\Throwable $e) {
                if (class_exists('ConstantHelper') && ConstantHelper::handleUndefinedConstantError($e)) {
                    require_once $settingsFile;
                } else {
                    return '';
                }
            }
            
            if (class_exists($fullClassName) && method_exists($fullClassName, 'getForm')) {
                $attempts = 0;
                while (true) {
                    try {
                        return $fullClassName::getForm($currentSettings);
                    } catch (\Throwable $e) {
                        if ($attempts < 10 && class_exists('ConstantHelper') && ConstantHelper::handleUndefinedConstantError($e)) {
                            $attempts++;
                            continue;
                        }
                        break;
                    }
                }
            }
        }
        
        return '';
    }
}