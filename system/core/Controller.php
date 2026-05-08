<?php

/**
* Базовый контроллер для всех контроллеров приложения
*/
class Controller {

    /**
    * @var mixed Подключение к базе данных
    */
    protected $db;
    
    /**
    * @var mixed Объект приложения
    */
    protected $app;
    
    /**
    * @var string Заголовок страницы
    */
    protected $pageTitle = LANG_CORE_CONTROLLER_DEFAULT_PAGE_TITLE;
    
    /**
    * @var string Имя контроллера
    */
    protected $controllerName;
    
    /**
    * @var array Метаинформация о контроллере
    */
    protected $controllerInfo = [
        'name' => LANG_CORE_CONTROLLER_BASE_NAME,
        'author' => 'BloggyCMS',
        'version' => '1.0.0',
        'has_settings' => false,
        'description' => ''
    ];
    
    /**
    * @var array Загруженные модели
    */
    protected $models = [];

    /**
    * @var \BreadcrumbsManager Менеджер хлебных крошек
    */
    protected $breadcrumbs;
    
    /**
    * Конструктор контроллера
    * @param mixed $db Подключение к базе данных
    * @param mixed $app Объект приложения
    */
    public function __construct($db, $app = null) {
        $this->db = $db;
        $this->app = $app;
        
        $className = get_class($this);
        $this->controllerName = str_replace('Controller', '', $className);
        
        if (!defined('CURRENT_CONTROLLER')) {
            define('CURRENT_CONTROLLER', $this->controllerName);
        }
    }
    
    /**
    * Получить метаинформацию о контроллере
    * @return array Метаинформация
    */
    public function getControllerInfo() {
        return $this->controllerInfo;
    }
    
    /**
    * Получить настройки по умолчанию
    * @return array Настройки по умолчанию
    */
    public function getDefaultSettings() {
        return [];
    }
    
    /**
    * Получить путь к файлу настроек
    * @return string Путь к файлу настроек
    */
    public function getSettingsPath() {
        $reflection = new ReflectionClass($this);
        return dirname($reflection->getFileName()) . '/Settings.php';
    }
    
    /**
    * Проверить наличие настроек у контроллера
    * @return bool Есть ли настройки
    */
    public function hasSettings() {
        return file_exists($this->getSettingsPath());
    }
    
    /**
    * Рендерит шаблон с данными
    * @param string $template Имя шаблона
    * @param array $data Данные для передачи в шаблон
    * @throws Exception Если файл шаблона не найден
    */
    public function render($template, $data = []) {

        Event::trigger('controller.render.before', [
            'template' => $template,
            'data' => $data,
            'controller' => $this
        ]);

        if (!isset($data['pageTitle'])) {
            $data['pageTitle'] = $this->pageTitle;
        }

        if (!isset($data['breadcrumbs']) && isset($this->breadcrumbs)) {
            $data['breadcrumbs'] = $this->breadcrumbs;
        }
        
        $data['app'] = $this->app;
        $data['db'] = $this->db;
        $data['userModel'] = new UserModel($this->db);
        
        extract($data);
        
        $isAdmin = strpos($template, 'admin/') === 0;
        $templateBase = $isAdmin ? 'default' : DEFAULT_TEMPLATE;
        $templateFile = TEMPLATES_PATH . '/' . $templateBase . '/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            $fallbackFile = TEMPLATES_PATH . '/default/' . $template . '.php';
            if (file_exists($fallbackFile)) {
                $templateFile = $fallbackFile;
            } else {
                throw new Exception('Template file not found: ' . $templateFile);
            }
        }
        
        ob_start();
        include $templateFile;
        $content = ob_get_clean();

        $content = Event::filter('controller.render.content', $content, [
            'template' => $template,
            'data' => $data,
            'controller' => $this
        ]);
        
        if ($isAdmin) {
            $layoutFile = TEMPLATES_PATH . '/default/admin/layout.php';
        } else {
            $layoutFile = TEMPLATES_PATH . '/' . DEFAULT_TEMPLATE . '/front/layout.php';
        }
        
        if (!file_exists($layoutFile)) {
            throw new Exception('Layout file not found: ' . $layoutFile);
        }
        
        extract($data);

        Event::trigger('controller.render.after', [
            'template' => $template,
            'content' => &$content,
            'data' => $data
        ]);

        include $layoutFile;
    }
    
    /**
    * Перенаправляет на указанный URL
    * @param string $url URL для перенаправления
    */
    public function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    /**
    * Обрабатывает контент с шорткодами
    * @param string $content Контент для обработки
    * @return string Обработанный контент
    */
    public function processContent(string $content): string {
        if (class_exists('Shortcodes')) {
            return Shortcodes::process($content);
        }
        return $content;
    }

    /**
    * Установить параметры маршрута
    * @param array $params Параметры маршрута
    */
    public function setRouteParams($params) {
        $this->routeParams = $params;
    }

    /**
    * Получить параметр маршрута по индексу
    * @param int|string $index Индекс параметра
    * @return mixed Значение параметра
    */
    protected function getRouteParam($index) {
        return $this->routeParams[$index] ?? null;
    }
    
    /**
    * Получить все параметры маршрута
    * @return array Параметры маршрута
    */
    protected function getRouteParams() {
        return $this->routeParams;
    }

    /**
    * Загружает модель по имени
    * @param string $modelName Имя модели
    * @param string|null $alias Псевдоним для модели
    * @return object|null Загруженная модель
    */
    public function loadModel($modelName, $alias = null) {
        if (substr($modelName, -5) !== 'Model') {
            $modelName .= 'Model';
        }
        
        if (isset($this->models[$modelName])) {
            return $this->models[$modelName];
        }
        
        try {
            if (class_exists($modelName)) {
                $model = new $modelName($this->db);
                $alias = $alias ?: lcfirst(str_replace('Model', '', $modelName));
                $this->models[$alias] = $model;
                return $model;
            }
        } catch (Exception $e) {

        }
        
        return null;
    }
    
    /**
    * Магический геттер для доступа к моделям
    * @param string $name Имя свойства
    * @return mixed Значение свойства
    * @throws Exception Если свойство не найдено
    */
    public function __get($name) {
        if (preg_match('/(.*)Model$/', $name, $matches)) {
            return $this->loadModel($name);
        }
        
        $modelName = ucfirst($name) . 'Model';
        $model = $this->loadModel($modelName, $name);
        if ($model) {
            return $model;
        }
        
        throw new Exception(sprintf(LANG_CORE_CONTROLLER_PROPERTY_NOT_FOUND, $name));
    }
    
    /**
    * Проверяет, загружена ли модель
    * @param string $name Имя модели
    * @return bool Загружена ли модель
    */
    public function hasModel($name) {
        $modelName = ucfirst($name) . 'Model';
        return isset($this->models[$name]) || isset($this->models[$modelName]);
    }

    /**
    * Инициализация хлебных крошек для админ-панели
    * @return void
    */
    protected function initAdminBreadcrumbs() {
        if (strpos($_SERVER['REQUEST_URI'], ADMIN_URL) !== false && !$this->breadcrumbs) {
            $this->breadcrumbs = new \BreadcrumbsManager($this->db);
            \BreadcrumbsHelper::setManager($this->breadcrumbs);
        }
    }

    /**
    * Добавляет элемент в хлебные крошки
    * @param string $title Название элемента
    * @param string|null $url URL элемента
    * @return self
    */
    protected function addBreadcrumb($title, $url = null) {
        if (!$this->breadcrumbs) {
            $this->breadcrumbs = new \BreadcrumbsManager($this->db);
            \BreadcrumbsHelper::setManager($this->breadcrumbs);
        }
        $this->breadcrumbs->add($title, $url);
        return $this;
    }

    /**
    * Очищает хлебные крошки 
    * @return self
    */
    protected function clearBreadcrumbs() {
        if ($this->breadcrumbs) {
            $this->breadcrumbs->clear();
        }
        return $this;
    }

}