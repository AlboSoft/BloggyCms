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
            $currentTemplate = get_current_template();
            
            foreach ($allBlockTypes as $systemName => &$type) {
                $type['is_active'] = $this->blockTypeManager->isBlockTypeActive($systemName);
                $type['is_template_compatible'] = $this->blockTypeManager->isBlockTypeCompatibleWithCurrentTemplate($systemName);
                $type['is_visible_in_creation'] = isset($activeBlockTypes[$systemName]);
            }
            
            $this->render('admin/html_blocks/types_index', [
                'blockTypes' => $allBlockTypes,
                'currentTemplate' => $currentTemplate,
                'pageTitle' => LANG_ACTION_HTMLBLOCKS_ADMINTYPEINDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINTYPEINDEX_ERROR . $e->getMessage());
            $this->redirect(ADMIN_URL . '/html-blocks');
        }
    }

}