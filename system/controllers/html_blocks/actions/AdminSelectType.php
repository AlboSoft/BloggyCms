<?php

namespace html_blocks\actions;

/**
* Действие выбора типа HTML-блока при создании
* @package html_blocks\actions
*/
class AdminSelectType extends HtmlBlockAction {
    
    /**
    * Метод выполнения выбора типа блока
    * @return void
    */
    public function execute() {
        
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINSELECTTYPE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINSELECTTYPE_BREADCRUMB_BLOCKS, ADMIN_URL . '/html-blocks');
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINSELECTTYPE_BREADCRUMB_SELECT);
        
        $blockTypes = $this->blockTypeManager->getBlockTypes();
        
        $currentTemplate = get_current_template();
        
        $defaultBlock = [
            'DefaultBlock' => [
                'name' => LANG_ACTION_HTMLBLOCKS_ADMINSELECTTYPE_DEFAULT_BLOCK_NAME,
                'system_name' => 'DefaultBlock',
                'description' => LANG_ACTION_HTMLBLOCKS_ADMINSELECTTYPE_DEFAULT_BLOCK_DESC,
                'icon' => 'bi bi-code-slash',
                'author' => 'BloggyCMS',
                'version' => '1.0.0',
                'author_website' => '',
                'short_description' => LANG_ACTION_HTMLBLOCKS_ADMINSELECTTYPE_DEFAULT_BLOCK_SHORT_DESC,
                'template' => 'all'
            ]
        ];
        
        $allBlocks = $defaultBlock + $blockTypes;
        
        $availableTemplates = $this->getAvailableTemplates($blockTypes);
        
        $this->render('admin/html_blocks/select_type', [
            'blockTypes' => $allBlocks,
            'availableTemplates' => $availableTemplates,
            'currentTemplate' => $currentTemplate,
            'pageTitle' => LANG_ACTION_HTMLBLOCKS_ADMINSELECTTYPE_PAGE_TITLE
        ]);
    }

}