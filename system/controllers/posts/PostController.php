<?php

/**
* Контроллер для управления постами (публичная часть и админ-панель)
* @package Controllers
* @extends Controller
*/
class PostController extends Controller {
    
    private $postModel;
    private $categoryModel;
    private $tagModel;
    private $commentModel;
    private $postBlockModel;
    private $settingsModel;
    private $fieldModel;
    private $postBlockManager;

    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_POSTS_MANIFEST_NAME,
        'author' => 'BloggyCMS',
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => LANG_CONTROLLER_POSTS_MANIFEST_DESCRIPTION
    ];
    
    /**
    * Конструктор контроллера
    * @param object $db Подключение к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        
        $this->postModel = new PostModel($db);
        $this->categoryModel = new CategoryModel($db);
        $this->tagModel = new TagModel($db);
        $this->commentModel = new CommentModel($db);
        $this->postBlockModel = new PostBlockModel($db);
        $this->settingsModel = new SettingsModel($db);
        $this->fieldModel = new FieldModel($db);
        $this->postBlockManager = new PostBlockManager($db);

    }

    /**
    * Возвращает системное имя контроллера
    * @return string
    */
    public function getSystemName() {
        return 'posts';
    }

    /**
    * Проверяет, является ли текущий запрос AJAX-запросом
    * @return bool true если запрос AJAX
    */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Создание нового поста (админ-панель) 
    * @return void
    */
    public function createAction() {
        $action = new \posts\actions\Create($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Редактирование существующего поста (админ-панель) 
    * @param int $id ID поста
    * @return void
    */
    public function editAction($id) {
        $action = new \posts\actions\Edit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Удаление поста (админ-панель) 
    * @param int $id ID поста
    * @return void
    */
    public function deleteAction($id) {
        $action = new \posts\actions\Delete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображение отдельного поста по URL-адресу (публичная часть) 
    * @param string $slug URL-адрес поста
    * @return void
    */
    public function showAction($slug) {
        $action = new \posts\actions\Show($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображение списка постов в административной панели 
    * @return void
    */
    public function adminIndexAction() {
        $action = new \posts\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображение всех постов (публичная часть) 
    * @return void
    */
    public function allAction() {
        $action = new \posts\actions\All($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображение главной страницы с постами (публичная часть) 
    * @return void
    */
    public function indexAction() {
        $action = new \posts\actions\Index($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Загрузка изображений через редактор контента 
    * @return void
    */
    public function uploadImageAction() {
        $action = new \posts\actions\UploadImage($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Проверка пароля для защищенных постов 
    * @param int $id ID поста
    * @return void
    */
    public function checkPasswordAction($id) {
        $action = new \posts\actions\CheckPassword($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Проверка возраста для контента 18+
    * @param int $id ID поста
    * @return void
    */
    public function checkAgeAction($id) {
        $action = new \posts\actions\CheckAge($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Голосование за пост (устаревший метод, для обратной совместимости) 
    * @param int $id ID поста
    * @return void
    */
    public function voteAction($id) {
        $action = new \posts\actions\Vote($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Переключение статуса поста (админ-панель)
    * @param int $id ID поста
    * @return void
    */
    public function toggleStatusAction($id) {
        $action = new \posts\actions\ToggleStatus($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Загрузка изображения для обложки поста 
    * @return void
    */
    public function uploadFeaturedImageAction() {
        $action = new \posts\actions\UploadFeaturedImage($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Загрузка изображений для галереи поста 
    * @return void
    */
    public function uploadGalleryImagesAction() {
        $action = new \posts\actions\UploadGalleryImages($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Загрузка изображений для блоков контента 
    * @return void
    */
    public function uploadBlockImageAction() {
        $action = new \posts\actions\UploadBlockImage($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Лайк/дизлайк поста 
    * @param int $id ID поста
    * @return void
    */
    public function likeAction($id) {
        $action = new \posts\actions\Like($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Добавление/удаление поста в закладки
    * @param int $id ID поста
    * @return void
    */
    public function bookmarkAction($id) {
        $action = new \posts\actions\Bookmark($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Отображение закладок пользователя 
    * @return void
    */
    public function bookmarksAction() {
        $action = new \posts\actions\Bookmarks($this->db);
        $action->setController($this);
        return $action->execute();
    }

}