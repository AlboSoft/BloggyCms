<?php

namespace html_blocks\actions;

/**
* Действие выбора типа HTML-блока при создании.
*/
class AdminSelectType extends HtmlBlockAction {

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
                'template' => 'all',
                'is_active' => true,
                'source' => 'system'
            ]
        ];

        $allTypes = $defaultBlock + $blockTypes;
        foreach ($allTypes as $systemName => &$type) {
            $type['blocks_count'] = $this->blockTypeManager->getBlocksCountByType($systemName);
        }
        unset($type);

        $this->render('admin/html_blocks/select_type', [
            'blockTypes' => $allTypes,
            'currentTemplate' => $currentTemplate,
            'pageTitle' => LANG_ACTION_HTMLBLOCKS_ADMINSELECTTYPE_PAGE_TITLE
        ]);
    }
}
