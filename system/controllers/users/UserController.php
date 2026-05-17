<?php

/**
* Контроллер для управления пользователями в публичной части сайта
* @package Controllers
*/
class UserController extends Controller {
    
    private $userModel;
    private $fieldModel;

    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_USERS_MANIFEST_NAME,
        'author' => 'BloggyCMS',
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => LANG_CONTROLLER_USERS_MANIFEST_DESCRIPTION
    ];

    /**
    * Конструктор контроллера
    * @param object $db Подключение к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new UserModel($db);
        $this->fieldModel = new FieldModel($db);
    }

    /**
    * Возвращает системное имя контроллера
    * @return string
    */
    public function getSystemName() {
        return 'users';
    }

    /**
    * Отображает список пользователей (публичная часть) 
    * @return void
    */
    public function indexAction() {
        $action = new \users\actions\FrontIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Отображает публичный профиль пользователя по имени пользователя 
    * @param string $username Имя пользователя
    * @return void
    */
    public function showAction($username) {
        $action = new \users\actions\FrontShow($this->db, ['username' => $username]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображает страницу со списком всех достижений системы 
    * @return void
    */
    public function achievementsAction() {
        $action = new \users\actions\achievements\FrontIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображает страницу конкретного достижения с информацией о нем 
    * @param int $id ID достижения
    * @return void
    */
    public function achievementAction($id) {
        $action = new \users\actions\achievements\FrontShow($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
}