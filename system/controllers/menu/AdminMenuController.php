<?php

/**
* Контроллер управления меню в админ-панели
* @package controllers
*/
class AdminMenuController extends Controller {
    
    private $menuModel;
    
    /**
    * Конструктор контроллера меню
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->menuModel = new MenuModel($db);
        
        if (!isset($_SESSION['user_id'])) {
            \Notification::error(LANG_CONTROLLER_ADMINMENU_AUTH_REQUIRED);
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            \Notification::error(LANG_CONTROLLER_ADMINMENU_ACCESS_DENIED);
            $this->redirect(BASE_URL);
            return;
        }
    }
    
    /**
    * Действие: Главная страница управления меню 
    * @return mixed
    */
    public function adminIndexAction() {
        $action = new \menu\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Создание нового меню
    * @return mixed
    */
    public function createAction() {
        $action = new \menu\actions\AdminCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Редактирование основных настроек меню
    * @param int $id ID редактируемого меню
    * @return mixed
    */
    public function editAction($id) {
        $action = new \menu\actions\AdminEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Управление пунктами меню
    * @param int $id ID меню
    * @return mixed
    */
    public function itemsAction($id) {
        $action = new \menu\actions\AdminItems($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Создание пункта меню
    * @param int $menuId ID меню
    * @return mixed
    */
    public function itemCreateAction($menuId) {
        $parentId = $_GET['parent_id'] ?? null;
        $action = new \menu\actions\AdminItemCreate($this->db, ['menuId' => $menuId, 'parent_id' => $parentId]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Редактирование пункта меню
    * @param string $id ID пункта меню (UUID-like)
    * @return mixed
    */
    public function itemEditAction($id) {
        $action = new \menu\actions\AdminItemEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Удаление пункта меню
    * @param string $id ID пункта меню
    * @return mixed
    */
    public function itemDeleteAction($id) {
        $action = new \menu\actions\AdminItemDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Сортировка пунктов меню (AJAX)
    * @return mixed
    */
    public function reorderAction() {
        $action = new \menu\actions\AdminReorder($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Удаление меню
    * @param int $id ID удаляемого меню
    * @return mixed
    */
    public function deleteAction($id) {
        $action = new \menu\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Получение структуры меню через AJAX
    * @param int $id ID меню
    * @return mixed JSON-ответ со структурой меню
    */
    public function getStructureAction($id) {
        $action = new \menu\actions\AdminGetStructure($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Предварительный просмотр меню
    * @param int $id ID меню для предпросмотра
    * @return mixed
    */
    public function previewAction($id) {
        $action = new \menu\actions\AdminPreview($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Получение всех групп пользователей
    * @return array Массив групп пользователей
    */
    public function getUserGroups() {
        $userModel = new UserModel($this->db);
        $groups = $userModel->getAllGroups();

        $groups[] = [
            'id' => 'guest',
            'name' => LANG_CONTROLLER_ADMINMENU_GROUP_GUEST,
            'description' => LANG_CONTROLLER_ADMINMENU_GROUP_GUEST_DESC
        ];
        
        return $groups;
    }
}