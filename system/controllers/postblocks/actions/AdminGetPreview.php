<?php

namespace postblocks\actions;

/**
* Действие получения HTML-предпросмотра постблока
* @package postblocks\actions
* @extends PostBlockAction
*/
class AdminGetPreview extends PostBlockAction {
    
    /**
    * Метод выполнения получения предпросмотра блока
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['block_type'])) {
                throw new Exception(LANG_ACTION_POSTBLOCKS_ADMINGETPREVIEW_BLOCK_TYPE_NOT_SPECIFIED);
            }
            
            $blockType = $input['block_type'];
            $content = $input['content'] ?? [];
            $settings = $input['settings'] ?? [];
            
            $blockInstance = $this->postBlockManager->getBlockInstance($blockType);
            
            if (!$blockInstance) {
                throw new Exception(sprintf(LANG_ACTION_POSTBLOCKS_ADMINGETPREVIEW_BLOCK_NOT_FOUND, $blockType));
            }
            
            $blockInstance->loadPreviewAssets();
            
            $html = $blockInstance->getPreviewHtml($content, $settings);
            
            if (isset($input['block_id'])) {
                $html = str_replace('{block_id}', $input['block_id'], $html);
            }
            
            echo json_encode([
                'success' => true,
                'html' => $html,
                'block_type' => $blockType
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'html' => $this->renderErrorHtml($e->getMessage())
            ]);
        }
        exit;
    }
    
    /**
    * Генерирует HTML для отображения ошибки в предпросмотре 
    * @param string $error Текст ошибки
    * @return string HTML-код с сообщением об ошибке
    */
    private function renderErrorHtml($error): string {
        return '<div class="alert alert-danger small">' . 
               html($error) . 
               '</div>';
    }
}