<?php

namespace settings\actions;

/**
* Действие сброса настроек к значениям по умолчанию
* @package settings\actions
* @extends SettingsAction
*/
class AdminReset extends SettingsAction {
    
    /**
    * Метод выполнения сброса настроек
    * @return void
    */
    public function execute() {
        
        try {

            $activeTab = $_GET['tab'] ?? 'general';
            
            $defaultSettings = $this->getDefaultSettings($activeTab);
            
            $this->settingsModel->save($activeTab, $defaultSettings);
            
            \Notification::success(LANG_ACTION_SETTINGS_ADMINRESET_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_SETTINGS_ADMINRESET_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/settings?tab=' . $activeTab);
    }
}