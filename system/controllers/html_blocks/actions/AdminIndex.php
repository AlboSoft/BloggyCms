<?php

namespace html_blocks\actions;

/**
* Действие отображения списка HTML-блоков в админ-панели.
*/
class AdminIndex extends HtmlBlockAction {

    public function execute() {
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMININDEX_BREADCRUMB_BLOCKS);

        try {
            $blocks = $this->htmlBlockModel->getAll();
            $this->blockTypeManager->getAllBlockTypes();

            foreach ($blocks as &$block) {
                $blockTypeName = $block['block_type'] ?? 'DefaultBlock';
                $block['type_is_active'] = $this->blockTypeManager->isBlockTypeActive($blockTypeName);
                $block['type_is_compatible'] = $this->blockTypeManager->isBlockTypeCompatibleWithCurrentTemplate($blockTypeName);
                $block['type_is_available'] = $block['type_is_active'] && $block['type_is_compatible'];
            }
            unset($block);

            $this->render('admin/html_blocks/index', [
                'blocks' => $blocks,
                'currentTemplate' => get_current_template(),
                'pageTitle' => LANG_ACTION_HTMLBLOCKS_ADMININDEX_PAGE_TITLE
            ]);
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMININDEX_ERROR . $e->getMessage());
            $this->redirect(ADMIN_URL);
        }
    }
}
