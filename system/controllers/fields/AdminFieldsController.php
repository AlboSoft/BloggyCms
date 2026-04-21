<?php

/**
* Контроллер управления дополнительными полями в админ-панели
* @package controllers
* @extends Controller
*/
class AdminFieldsController extends Controller {
    
    private $fieldModel;

    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_FIELDS_MANIFEST_NAME,
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => false,
        'description' => LANG_CONTROLLER_FIELDS_MANIFEST_DESCRIPTION
    ];
    
    /**
    * Конструктор контроллера управления полями
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        
        $this->fieldModel = new FieldModel($db);
    }

    /**
    * Действие: Главная страница управления полями
    * @return mixed
    */
    public function adminIndexAction() {
        $action = new \fields\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Управление полями для конкретной сущности
    * @param string $entityType Тип сущности (post, category, user и т.д.)
    * @return mixed
    */
    public function entityAction($entityType) {
        $action = new \fields\actions\AdminEntity($this->db, ['entityType' => $entityType]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Создание нового поля 
    * @param string $entityType Тип сущности, для которой создается поле
    * @return mixed
    */
    public function createAction($entityType) {
        $action = new \fields\actions\AdminCreate($this->db, ['entityType' => $entityType]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Редактирование существующего поля
    * @param int $id ID редактируемого поля
    * @return mixed
    */
    public function editAction($id) {
        $action = new \fields\actions\AdminEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие: Удаление поля
    * @param int $id ID удаляемого поля
    * @return mixed
    */
    public function deleteAction($id) {
        $action = new \fields\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие: Переключение состояния поля (включение/выключение) 
    * @param int $id ID поля, состояние которого изменяется
    * @return mixed
    */
    public function toggleAction($id) {
        $action = new \fields\actions\AdminToggle($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие: Получение настроек типа поля
    * @param string $type Тип поля (text, textarea, select, checkbox и т.д.)
    * @return mixed JSON-ответ с настройками
    */
    public function getSettingsAction($type) {
        $action = new \fields\actions\AdminGetSettings($this->db, ['type' => $type]);
        $action->setController($this);
        return $action->execute();
    }
}