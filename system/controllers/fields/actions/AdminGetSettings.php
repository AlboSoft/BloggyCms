<?php

namespace fields\actions;

/**
* Действие получения настроек типа поля через AJAX
*/
class AdminGetSettings extends FieldAction {
    
    public function execute() {
        
        $type = $this->params['type'] ?? null;
        
        if (!$type) {
            echo '<div class="alert alert-warning">' . LANG_ACTION_FIELDS_ADMINGETSETTINGS_TYPE_NOT_SPECIFIED . '</div>';
            exit;
        }
        
        $config = $_POST['config'] ?? [];
        
        $fieldManager = new \FieldManager($this->db);
        
        try {
            $fieldInstance = $fieldManager->getFieldInstance($type, $config);
            
            if ($fieldInstance) {
                $settingsForm = $fieldInstance->getSettingsForm();
                echo $settingsForm;
            } else {
                echo '<div class="alert alert-warning">' . LANG_ACTION_FIELDS_ADMINGETSETTINGS_NOT_FOUND . '</div>';
            }
        } catch (\Exception $e) {
            echo '<div class="alert alert-danger">' . LANG_ACTION_FIELDS_ADMINGETSETTINGS_ERROR . html($e->getMessage()) . '</div>';
        }
        
        exit;
    }
}