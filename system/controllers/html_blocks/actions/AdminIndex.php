<?php

namespace html_blocks\actions;

/**
* Действие отображения списка HTML-блоков в админ-панели 
* @package html_blocks\actions
*/
class AdminIndex extends HtmlBlockAction {
    
    /**
    * Метод выполнения отображения списка HTML-блоков 
    * @return void
    */
    public function execute() {
        
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMININDEX_BREADCRUMB_BLOCKS);
        
        try {
            $blocks = $this->htmlBlockModel->getAll();
            
            $allBlockTypes = $this->blockTypeManager->getAllBlockTypes();
            
            foreach ($blocks as &$block) {
                $blockTypeName = $block['block_type'] ?? 'DefaultBlock';
                $block['type_is_active'] = $this->blockTypeManager->isBlockTypeActive($blockTypeName);
            }
            
            $this->render('admin/html_blocks/index', [
                'blocks' => $blocks,
                'blockTypes' => $this->blockTypeManager->getBlockTypes(),
                'pageTitle' => LANG_ACTION_HTMLBLOCKS_ADMININDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMININDEX_ERROR . $e->getMessage());
            $this->redirect(ADMIN_URL);
        }
    }

}