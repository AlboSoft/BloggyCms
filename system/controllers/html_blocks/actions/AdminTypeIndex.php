<?php

namespace html_blocks\actions;

/**
* Действие отображения списка типов HTML-блоков в админ-панели
* @package html_blocks\actions
*/
class AdminTypeIndex extends HtmlBlockAction {
    
    /**
    * Метод выполнения отображения списка типов блоков
    * @return void
    */
    public function execute() {
        
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINTYPEINDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINTYPEINDEX_BREADCRUMB_BLOCKS, ADMIN_URL . '/html-blocks');
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINTYPEINDEX_BREADCRUMB_TYPES);
        
        try {
            $allBlockTypes = $this->blockTypeManager->getAllBlockTypes();
            
            $activeBlockTypes = $this->blockTypeManager->getBlockTypes();
            
            foreach ($allBlockTypes as $systemName => &$type) {
                if ($systemName !== 'DefaultBlock') {
                    $type['is_active'] = $this->blockTypeManager->isBlockTypeActive($systemName);
                } else {
                    $type['is_active'] = true;
                }
                
                $type['is_visible_in_creation'] = isset($activeBlockTypes[$systemName]);
            }
            
            $this->render('admin/html_blocks/types_index', [
                'blockTypes' => $allBlockTypes,
                'pageTitle' => LANG_ACTION_HTMLBLOCKS_ADMINTYPEINDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPEINDEX_ERROR . $e->getMessage());
            $this->redirect(ADMIN_URL . '/html-blocks');
        }
    }

}