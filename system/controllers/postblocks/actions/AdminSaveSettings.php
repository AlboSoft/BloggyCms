<?php

namespace postblocks\actions;

/**
* Быстрое сохранение настроек постблока из списка (переключатели «в постах» / «на страницах») без перехода на страницу редактирования
* @package postblocks\actions
* @extends PostBlockAction
*/
class AdminSaveSettings extends PostBlockAction {
    
    /**
    * Метод выполнения сохранения настроек
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!is_array($input)) {
                $input = $_POST;
            }

            $systemName = $input['system_name'] ?? '';

            if (empty($systemName)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVESETTINGS_SYSTEM_NAME_NOT_SPECIFIED);
            }

            $postBlock = $this->postBlockManager->getPostBlock($systemName);
            if (!$postBlock) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVESETTINGS_BLOCK_NOT_FOUND);
            }

            $current = $this->postBlockModel->getBlockSettings($systemName);

            $settings = [
                'enable_in_posts' => array_key_exists('enable_in_posts', $input)
                    ? (bool)$input['enable_in_posts']
                    : (bool)($current['enable_in_posts'] ?? true),
                'enable_in_pages' => array_key_exists('enable_in_pages', $input)
                    ? (bool)$input['enable_in_pages']
                    : (bool)($current['enable_in_pages'] ?? true),
                'template' => $current['template'] ?? ''
            ];

            $saved = $this->postBlockModel->updateBlockSettings($systemName, $settings);

            if (!$saved) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVESETTINGS_SAVE_ERROR);
            }

            echo json_encode([
                'success' => true,
                'message' => LANG_ACTION_POSTBLOCKS_ADMINSAVESETTINGS_SUCCESS,
                'settings' => $settings
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }
}
