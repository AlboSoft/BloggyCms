<?php

namespace categories\actions;

/**
* Абстрактный базовый класс для действий с категориями
* @package categories\actions
* @abstract
*/
abstract class CategoryAction {
    
    protected $db;
    protected $params;
    protected $controller;
    protected $categoryModel;
    protected $postModel;
    protected $breadcrumbs;
    protected $pageTitle;
    
    /**
    * Конструктор базового класса действий
    * @param \Database $db Объект подключения к базе данных
    * @param array $params Дополнительные параметры действия
    */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->categoryModel = new \CategoryModel($db);
        $this->postModel = new \PostModel($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    /**
    * Установка контроллера для действия
    * @param object $controller Объект контроллера
    * @return void
    */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
    * Абстрактный метод выполнения действия
    * @return mixed Результат выполнения действия
    * @abstract
    */
    abstract public function execute();
    
    /**
    * Добавляет элемент в хлебные крошки
    */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
    * Добавляет элемент в начало хлебных крошек
    */
    protected function prependBreadcrumb($title, $url = null) {
        $this->breadcrumbs->prepend($title, $url);
        return $this;
    }
    
    /**
    * Очищает все хлебные крошки
    */
    protected function clearBreadcrumbs() {
        $this->breadcrumbs->clear();
        return $this;
    }
    
    /**
    * Устанавливает заголовок страницы
    */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    /**
    * Рендеринг шаблона с данными
    * @param string $template Путь к файлу шаблона
    * @param array $data Массив данных для передачи в шаблон
    * @return void
    * @throws \Exception Если контроллер не установлен
    */
    protected function render($template, $data = []) {
        if (!$this->controller) {
            throw new \Exception('Controller not set for Action');
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
    * Перенаправление на указанный URL
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
    * Проверка типа запроса
    * @return bool true если запрос является AJAX-запросом
    */
    protected function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Возвращает менеджер хлебных крошек 
    * @return \BreadcrumbsManager
    */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }
}