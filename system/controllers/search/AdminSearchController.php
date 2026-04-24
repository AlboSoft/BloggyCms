<?php

/**
* Контроллер управления историей поиска в административной панели
* @package Controllers
* @extends Controller
*/
class AdminSearchController extends Controller {
    
    private $searchModel;
    
    /**
    * Конструктор контроллера
    * @param object $db Подключение к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->searchModel = new SearchModel($db);

        if (!isset($_SESSION['user_id'])) {
            Notification::error(LANG_CONTROLLER_ADMINSEARCH_AUTH_REQUIRED);
            $this->redirect(ADMIN_URL . '/login');
            exit;
        }
    }
    
    /**
    * Отображает список истории поиска в административной панели 
    * @return void
    */
    public function adminIndexAction() {
        $action = new \search\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Удаляет конкретную запись из истории поиска по ID
    * @param int $id ID записи в истории поиска
    * @return void
    */
    public function deleteAction($id) {
        $action = new \search\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Очищает всю историю поиска текущего пользователя 
    * @return void
    */
    public function clearAction() {
        $action = new \search\actions\AdminClear($this->db);
        $action->setController($this);
        return $action->execute();
    }
}