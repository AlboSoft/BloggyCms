<?php

/**
* Контроллер управления страницами в административной панели
* @package Controllers
*/
class AdminPageController extends Controller {

    /**
    * Возвращает системное имя контроллера
    * @return string
    */
    public function getSystemName() {
        return 'pages';
    }
    
    /**
    * Отображает список всех страниц в административной панели
    * @return void
    */
    public function adminIndexAction() {
        $action = new \pages\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображает форму создания новой страницы и обрабатывает её отправку 
    * @return void
    */
    public function createAction() {
        $action = new \pages\actions\AdminCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображает форму редактирования существующей страницы и обрабатывает её отправку
    * @param int|null $id ID редактируемой страницы
    * @return void
    */
    public function editAction($id = null) {
        $action = new \pages\actions\AdminEdit($this->db);
        $action->setController($this);
        $action->setId($id);
        return $action->execute();
    }
    
    /**
    * Удаляет указанную страницу из базы данных
    * @param int|null $id ID удаляемой страницы
    * @return void
    */
    public function deleteAction($id = null) {
        $action = new \pages\actions\AdminDelete($this->db);
        $action->setController($this);
        $action->setId($id);
        return $action->execute();
    }
    
    /**
    * Обрабатывает загрузку изображений для страниц
    * @return void
    */
    public function uploadImageAction() {
        $action = new \pages\actions\AdminUploadImage($this->db);
        $action->setController($this);
        return $action->execute();
    }
}