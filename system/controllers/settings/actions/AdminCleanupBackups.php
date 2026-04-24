<?php

namespace settings\actions;

/**
* Действие очистки старых резервных копий настроек 
* @package settings\actions
* @extends SettingsAction
*/
class AdminCleanupBackups extends SettingsAction {
    
    /**
    * Метод выполнения очистки резервных копий
    * @return void
    */
    public function execute() {
        
        $deletedCount = \BackupHelper::cleanupAllBackups();
        
        \Notification::success(sprintf(LANG_ACTION_SETTINGS_ADMINCLEANUPBACKUPS_SUCCESS, $deletedCount));
        
        $this->redirect(ADMIN_URL . '/settings?tab=site');
    }
}