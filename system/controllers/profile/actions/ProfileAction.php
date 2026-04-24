<?php

namespace profile\actions;

/**
* Абстрактный базовый класс для всех действий модуля профиля пользователя
* @package profile\actions
*/
abstract class ProfileAction {
    
    protected $db;
    protected $params;
    protected $controller;
    protected $userModel;
    protected $postModel;
    protected $fieldModel;
    protected $breadcrumbs;
    protected $pageTitle;
    
    /**
    * Конструктор класса действия
    * @param object $db Подключение к базе данных
    * @param array $params Параметры запроса (по умолчанию [])
    */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->userModel = new \UserModel($this->db);
        $this->postModel = new \PostModel($this->db);
        $this->fieldModel = new \FieldModel($this->db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    /**
    * Устанавливает контроллер, вызывающий действие
    * @param object $controller Контроллер
    * @return void
    */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
    * Абстрактный метод выполнения действия
    * @return void
    */
    abstract public function execute();
    
    /**
    * Добавляет элемент в хлебные крошки
    * @param string $title Название элемента
    * @param string|null $url URL элемента (null для текущего элемента)
    * @return self
    */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
    * Добавляет элемент в начало хлебных крошек
    * @param string $title Название элемента
    * @param string|null $url URL элемента
    * @return self
    */
    protected function prependBreadcrumb($title, $url = null) {
        $this->breadcrumbs->prepend($title, $url);
        return $this;
    }
    
    /**
    * Очищает все хлебные крошки
    * @return self
    */
    protected function clearBreadcrumbs() {
        $this->breadcrumbs->clear();
        return $this;
    }
    
    /**
    * Устанавливает заголовок страницы
    * @param string $title Заголовок
    * @return self
    */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    /**
    * Рендерит шаблон с переданными данными 
    * @param string $template Путь к шаблону относительно папки views
    * @param array $data Данные для передачи в шаблон
    * @param int $statusCode HTTP статус код
    * @throws \Exception Если контроллер не установлен
    * @return void
    */
    protected function render($template, $data = [], $statusCode = 200) {
        if (!$this->controller) {
            throw new \Exception('Controller not set for Action');
        }
        
        if ($statusCode !== 200) {
            http_response_code($statusCode);
        }
        
        if (!isset($data['breadcrumbs'])) {
            $data['breadcrumbs'] = $this->breadcrumbs;
        }
        
        if (!isset($data['title']) && $this->pageTitle) {
            $data['title'] = $this->pageTitle;
        }

        $this->controller->render($template, $data);
    }
    
    /**
    * Выполняет перенаправление на указанный URL
    * @param string $url URL для перенаправления
    * @return void
    */
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    /**
    * Проверяет, авторизован ли пользователь
    * @return bool true если пользователь авторизован
    */
    protected function checkAuthentication() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
            exit;
        }
        return true;
    }
    
    /**
    * Генерирует или возвращает существующий CSRF-токен из сессии
    * @return string CSRF-токен
    */
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
    * Проверяет валидность CSRF-токена из POST-запроса
    * @return bool true если токен валидный
    */
    protected function validateCsrfToken() {
        return !empty($_POST['csrf_token']) && 
               !empty($_SESSION['csrf_token']) && 
               $_POST['csrf_token'] === $_SESSION['csrf_token'];
    }
    
    /**
    * Возвращает менеджер хлебных крошек 
    * @return \BreadcrumbsManager
    */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }
    
    /**
    * Перенаправляет с сообщением об ошибке
    * @param string $message Текст сообщения об ошибке
    * @param string $path URL для перенаправления
    * @return void
    */
    protected function redirectWithError($message, $path) {
        $_SESSION['error_message'] = $message;
        $this->redirect($path);
        exit;
    }
    
    /**
    * Перенаправляет с сообщением об успехе
    * @param string $path URL для перенаправления
    * @return void
    */
    protected function redirectWithSuccess($path) {
        $_SESSION['success_message'] = LANG_ACTION_PROFILE_BASE_SUCCESS_MESSAGE;
        $this->redirect($path);
        exit;
    }
}