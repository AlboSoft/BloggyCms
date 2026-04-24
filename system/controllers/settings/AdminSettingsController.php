<?php

/**
* Контроллер управления настройками в административной панели
* @package Controllers
*/
class AdminSettingsController extends Controller {
    
    private $settingsModel;
    
    /**
    * Конструктор контроллера
    * @param object $db Подключение к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->settingsModel = new SettingsModel($db);
        
        if (!isset($_SESSION['user_id'])) {
            Notification::error(LANG_CONTROLLER_ADMINSETTINGS_AUTH_REQUIRED);
            $this->redirect(ADMIN_URL . '/login');
            exit;
        }
        
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            Notification::error(LANG_CONTROLLER_ADMINSETTINGS_PERMISSION_DENIED);
            $this->redirect(ADMIN_URL);
            exit;
        }
    }
    
    /**
    * Отображает главную страницу управления настройками
    * @return void
    */
    public function adminIndexAction() {
        $action = new \settings\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Выполняет сброс настроек к значениям по умолчанию
    * @return void
    */
    public function resetAction() {
        $action = new \settings\actions\AdminReset($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Выполняет очистку старых резервных копий настроек
    * @return void
    */
    public function cleanupBackupsAction() {
        $action = new \settings\actions\AdminCleanupBackups($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Загружает изображения для настроек (логотипы, иконки и т.д.)
    * @return void
    */
    public function uploadImageAction() {
        header('Content-Type: application/json');
        
        try {
            if (empty($_FILES['image'])) {
                throw new Exception(LANG_CONTROLLER_ADMINSETTINGS_UPLOAD_NO_FILE);
            }
            
            $uploadPath = $_POST['upload_path'] ?? 'uploads/images/';
            $fieldName = $_POST['field_name'] ?? 'unknown';
            
            $fullUploadPath = BASE_PATH . '/' . trim($uploadPath, '/');
            
            $fileName = FileUpload::upload($_FILES['image'], $fullUploadPath, ['jpg', 'jpeg', 'png', 'gif', 'webp'], 5120);
            
            echo json_encode([
                'success' => true,
                'filename' => $fileName,
                'url' => BASE_URL . '/' . trim($uploadPath, '/') . '/' . $fileName,
                'message' => LANG_CONTROLLER_ADMINSETTINGS_UPLOAD_SUCCESS
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        
        exit;
    }
}