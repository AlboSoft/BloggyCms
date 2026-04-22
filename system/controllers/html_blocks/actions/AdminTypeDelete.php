<?php

namespace html_blocks\actions;

/**
* Действие удаления типа HTML-блока в админ-панели
* @package html_blocks\actions
*/
class AdminTypeDelete extends HtmlBlockAction {
    
    private $systemName;
    
    /**
    * Установка системного имени типа блока
    */
    public function setSystemName($systemName) {
        $this->systemName = $systemName;
    }
    
    /**
    * Метод выполнения удаления типа блока
    * @return void
    */
    public function execute() {
        
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_BREADCRUMB_BLOCKS, ADMIN_URL . '/html-blocks');
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_BREADCRUMB_TYPES, ADMIN_URL . '/html-blocks/types');
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_BREADCRUMB_DELETE . $this->systemName);
        
        if (!$this->systemName) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_SYSTEM_NAME_REQUIRED);
            $this->redirect(ADMIN_URL . '/html-blocks/types');
            return;
        }
        
        if ($this->systemName === 'DefaultBlock') {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_CANNOT_DELETE_DEFAULT);
            $this->redirect(ADMIN_URL . '/html-blocks/types');
            return;
        }
        
        try {
            if ($this->blockTypeManager->hasBlocks($this->systemName)) {
                \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_HAS_BLOCKS);
                $this->redirect(ADMIN_URL . '/html-blocks/types');
                return;
            }
            
            $blockFile = __DIR__ . '/../../../html_blocks/' . $this->systemName . '.php';
            
            if (file_exists($blockFile)) {
                if (!unlink($blockFile)) {
                    \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_FILE_DELETE_ERROR);
                    $this->redirect(ADMIN_URL . '/html-blocks/types');
                    return;
                }
                \Notification::success(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_FILE_DELETED);
            } else {
                \Notification::warning(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_FILE_NOT_FOUND);
            }
            
            $this->blockTypeManager->deleteBlockType($this->systemName);
            
            \Notification::success(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPEDELETE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/html-blocks/types');
    }
}