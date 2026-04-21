<?php

/**
* Контроллер категорий блога
*/
class CategoryController extends Controller {

    private $categoryModel;

    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_CATEGORIES_MANIFEST_NAME,
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => LANG_CONTROLLER_CATEGORIES_MANIFEST_DESCRIPTION
    ];
    
    /**
    * Получение настроек категорий по умолчанию
    */
    public function getDefaultSettings() {
        return [
            'category_layout' => 'grid',
            'show_category_images' => true,
            'category_posts_per_page' => 12
        ];
    }
    
    /**
    * Конструктор контроллера категорий
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->categoryModel = new CategoryModel($db);
        
        $currentAction = $_GET['action'] ?? '';
    }

    /**
    * Определение типа запроса (AJAX или обычный)
    * @return bool true если запрос выполнен через AJAX
    */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Действие админ-панели категорий
    * @return mixed Результат выполнения действия
    */
    public function adminIndexAction() {
        $action = new \categories\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие создания новой категории
    * @return mixed Результат выполнения действия
    */
    public function createAction() {
        $action = new \categories\actions\Create($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие редактирования существующей категории
    * @param int $id Идентификатор редактируемой категории
    * @return mixed Результат выполнения действия
    */
    public function editAction($id) {
        $action = new \categories\actions\Edit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие удаления категории
    * @param int $id Идентификатор удаляемой категории
    * @return mixed Результат выполнения действия
    */
    public function deleteAction($id) {
        $action = new \categories\actions\Delete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие отображения категории на фронтенде
    * @param string $slug URL-идентификатор категории
    * @return mixed Результат выполнения действия
    */
    public function showAction($slug) {
        $action = new \categories\actions\Show($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие проверки пароля защищенной категории
    * @param int $id Идентификатор категории
    * @return mixed Результат выполнения действия
    */
    public function checkPasswordAction($id) {
        $action = new \categories\actions\CheckPassword($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие изменения порядка категорий
    * @return mixed Результат выполнения действия
    */
    public function reorderAction() {
        $action = new \categories\actions\Reorder($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие загрузки изображения категории
    * @return mixed Результат выполнения действия
    */
    public function uploadImageAction() {
        $action = new \categories\actions\UploadImage($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Действие отображения всех категорий на фронте
     * @return mixed Результат выполнения действия
     */
    public function indexAction() {
        $action = new \categories\actions\Index($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
}