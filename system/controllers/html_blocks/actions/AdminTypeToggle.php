<?php

namespace html_blocks\actions;

/**
* Действие переключения статуса типа HTML-блока в админ-панели
* @package html_blocks\actions
*/
class AdminTypeToggle extends HtmlBlockAction {
    
    private $systemName;
    
    /**
    * Установка системного имени типа блока
    */
    public function setSystemName($systemName) {
        $this->systemName = $systemName;
    }
    
    /**
    * Метод выполнения переключения статуса типа блока
    * @return void
    */
    public function execute() {
        
        if (!$this->systemName) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPETOGGLE_SYSTEM_NAME_REQUIRED);
            $this->redirect(ADMIN_URL . '/html-blocks/types');
            return;
        }

        if ($this->systemName === 'DefaultBlock') {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPETOGGLE_CANNOT_DISABLE_DEFAULT);
            $this->redirect(ADMIN_URL . '/html-blocks/types');
            return;
        }
        
        try {
            $isActive = $this->blockTypeManager->isBlockTypeActive($this->systemName);
            
            $newStatus = $isActive ? 0 : 1;
            
            $this->blockTypeManager->toggleBlockTypeStatus($this->systemName, $newStatus);
            
            $statusText = $newStatus ? LANG_ACTION_HTMLBLOCKS_ADMINTYPETOGGLE_ENABLED : LANG_ACTION_HTMLBLOCKS_ADMINTYPETOGGLE_DISABLED;
            \Notification::success(sprintf(LANG_ACTION_HTMLBLOCKS_ADMINTYPETOGGLE_SUCCESS, $statusText));
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPETOGGLE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/html-blocks/types');
    }

}