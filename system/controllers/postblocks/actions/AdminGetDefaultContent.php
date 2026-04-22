<?php

namespace postblocks\actions;

/**
* Действие получения контента по умолчанию для постблока 
* @package postblocks\actions
*/
class AdminGetDefaultContent extends PostBlockAction {
    
    /**
    * Метод выполнения получения контента по умолчанию
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {
            $systemName = $_GET['system_name'] ?? '';
            
            if (empty($systemName)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINGETDEFAULTCONTENT_SYSTEM_NAME_NOT_SPECIFIED);
            }

            $postBlock = $this->postBlockManager->getPostBlock($systemName);
            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINGETDEFAULTCONTENT_BLOCK_NOT_FOUND);
            }

            $defaultContent = $postBlock['class']->getDefaultContent();

            echo json_encode([
                'success' => true,
                'content' => $defaultContent
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'content' => []
            ]);
        }
    }
}