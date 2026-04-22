<?php

namespace postblocks\actions;

/**
* Действие сохранения постблока
* @package postblocks\actions
* @extends PostBlockAction
*/
class AdminSaveBlock extends PostBlockAction {
    
    /**
    * Метод выполнения сохранения блока
    * @return void
    */
    public function execute() {
        
        try {

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVEBLOCK_INVALID_METHOD);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            $blockType = $input['type'] ?? '';
            $content = $input['content'] ?? [];
            $settings = $input['settings'] ?? [];

            if (empty($blockType)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVEBLOCK_TYPE_REQUIRED);
            }

            $postBlock = $this->postBlockManager->getPostBlock($blockType);
            if ($postBlock && $postBlock['class']) {
                list($isValid, $errors) = $postBlock['class']->validateSettings($settings);
                if (!$isValid) {
                    throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVEBLOCK_VALIDATION_ERROR . implode(', ', $errors));
                }
                
                $settings = $postBlock['class']->prepareSettings($settings);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => LANG_ACTION_POSTBLOCKS_ADMINSAVEBLOCK_SUCCESS,
                'content' => $content,
                'settings' => $settings
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
    * Отправляет JSON-ответ и завершает выполнение
    * @param array $data Данные для JSON-ответа
    * @return void
    */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}