<?php

/**
* Контроллер управления контроллерами системы
* @package controllers
* @extends Controller
*/
class ControllersController extends Controller {
    
    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_CONTROLLERS_MANIFEST_NAME,
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => false,
        'description' => LANG_CONTROLLER_CONTROLLERS_MANIFEST_DESCRIPTION
    ];
    
    /**
    * Конструктор контроллера управления контроллерами
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
    }

    /**
    * Проверка типа запроса
    * @return bool true если запрос является AJAX-запросом
    */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Действие: Отображение списка контроллеров в админ-панели
    * @return mixed
    */
    public function adminIndexAction() {
        $this->pageTitle = LANG_CONTROLLER_CONTROLLERS_MANIFEST_NAME;
        $action = new \controllers\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие: Переключение статуса контроллера (AJAX)
    * @return mixed
    */
    public function toggleAction() {
        $action = new \controllers\actions\AdminToggle($this->db);
        $action->setController($this);
        return $action->execute();
    }

}