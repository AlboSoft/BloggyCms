<?php

/**
* Контроллер управления постблоками в административной панели 
* @package Controllers
*/
class AdminPostBlockController extends Controller {
    
    /**
    * Отображает список всех доступных постблоков
    * @return void
    */
    public function adminIndexAction() {
        $action = new \postblocks\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Отображает форму редактирования постблока
    * @param string|null $systemName Системное имя постблока
    * @return void
    */
    public function editAction($systemName = null) {
        $action = new \postblocks\actions\AdminEdit($this->db);
        $action->setController($this);
        $action->setSystemName($systemName);
        return $action->execute();
    }
    
    /**
    * Получает HTML-форму настроек для конкретного типа постблока
    * @return void
    */
    public function getSettingsFormAction() {
        $action = new \postblocks\actions\AdminGetSettingsForm($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Сохраняет постблок в базу данных
    * @return void
    */
    public function saveBlockAction() {
        $action = new \postblocks\actions\AdminSaveBlock($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Загружает файлы, связанные с постблоком (изображения, стили, скрипты)
    * @return void
    */
    public function uploadBlockFilesAction() {
        $action = new \postblocks\actions\AdminUploadBlockFiles($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Получает контент по умолчанию для указанного типа постблока 
    * @return void
    */
    public function getDefaultContentAction() {
        $action = new \postblocks\actions\AdminGetDefaultContent($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Получает настройки по умолчанию для указанного типа постблока
    * @return void
    */
    public function getDefaultSettingsAction() {
        $action = new \postblocks\actions\AdminGetDefaultSettings($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Получает HTML-шаблон для указанного типа постблока
    * @return void
    */
    public function getTemplateAction() {
        $action = new \postblocks\actions\AdminGetTemplate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Сохраняет данные постблока (составной метод) 
    * @return void
    */
    public function saveBlockDataAction() {
        $action = new \postblocks\actions\AdminSaveBlockData($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Получает список пресетов для постблоков
    * @return void
    */
    public function getPresetsAction() {
        $action = new \postblocks\actions\AdminGetPresets($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Создает новый пресет для постблока
    * @return void
    */
    public function createPresetAction() {
        $action = new \postblocks\actions\AdminPresetCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Обновляет существующий пресет постблока 
    * @return void
    */
    public function updatePresetAction() {
        $action = new \postblocks\actions\AdminPresetUpdate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Удаляет пресет постблока 
    * @return void
    */
    public function deletePresetAction() {
        $action = new \postblocks\actions\AdminPresetDelete($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Получает HTML-предпросмотр постблока для административной панели
    * @return void
    */
    public function adminGetPreviewAction() {
        $action = new \postblocks\actions\AdminGetPreview($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Пакетно получает HTML-предпросмотр нескольких постблоков
    * @return void
    */
    public function adminGetPreviewsAction() {
        $action = new \postblocks\actions\AdminGetPreviews($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Получает контент и настройки по умолчанию для постблока одним запросом
    * @return void
    */
    public function getBlockBlueprintAction() {
        $action = new \postblocks\actions\AdminGetBlockBlueprint($this->db);
        $action->setController($this);
        return $action->execute();
    }
}