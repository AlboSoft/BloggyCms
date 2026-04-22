<?php

namespace html_blocks\actions;

/**
* Действие получения доступных шаблонов для типа блока через AJAX
* @package html_blocks\actions
*/
class AdminGetBlockTemplates extends HtmlBlockAction {
    
    /**
    * Метод выполнения получения шаблонов блока
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {
            $blockTypeName = $_GET['block_type'] ?? '';
            
            if (empty($blockTypeName) || $blockTypeName === 'DefaultBlock') {
                echo json_encode([
                    'success' => true,
                    'templates' => ['default' => LANG_ACTION_HTMLBLOCKS_ADMINGETBLOCKTEMPLATES_DEFAULT_TEMPLATE]
                ]);
                return;
            }
            
            $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
            
            if ($blockType && $blockType['class']) {
                $templates = $blockType['class']->getAvailableTemplates();
                
                echo json_encode([
                    'success' => true,
                    'templates' => $templates
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => LANG_ACTION_HTMLBLOCKS_ADMINGETBLOCKTEMPLATES_NOT_FOUND
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => LANG_ACTION_HTMLBLOCKS_ADMINGETBLOCKTEMPLATES_ERROR . $e->getMessage()
            ]);
        }
    }
}