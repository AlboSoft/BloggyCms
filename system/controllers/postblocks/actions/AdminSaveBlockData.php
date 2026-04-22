<?php

namespace postblocks\actions;

/**
* Действие сохранения данных постблока из формы
* @package postblocks\actions
*/
class AdminSaveBlockData extends PostBlockAction {
    
    /**
    * Метод выполнения сохранения данных блока
    * @return void
    */
    public function execute() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if (!isset($_SESSION['is_admin'])) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVEBLOCKDATA_ACCESS_DENIED);
            }

            $blockId = $_POST['block_id'] ?? '';
            $blockType = $_POST['block_type'] ?? '';
            
            if (empty($blockId) || empty($blockType)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVEBLOCKDATA_DATA_NOT_SPECIFIED);
            }

            $postBlock = $this->postBlockManager->getPostBlock($blockType);
            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINSAVEBLOCKDATA_BLOCK_NOT_FOUND . $blockType);
            }

            $blockInstance = $postBlock['class'];
            
            $settings = $this->extractSettingsFromPost();
            $content = $this->extractContentFromPost();
            
            if (isset($_POST['preset_id'])) {
                $settings['preset_id'] = $_POST['preset_id'];
            }
            if (isset($_POST['preset_name'])) {
                $settings['preset_name'] = $_POST['preset_name'];
            }

            if (method_exists($blockInstance, 'prepareSettings')) {
                $settings = $blockInstance->prepareSettings($settings);
            }
            
            if (method_exists($blockInstance, 'prepareContent')) {
                $content = $blockInstance->prepareContent($content);
            }

            echo json_encode([
                'success' => true,
                'content' => $content,
                'settings' => $settings,
                'message' => LANG_ACTION_POSTBLOCKS_ADMINSAVEBLOCKDATA_SUCCESS
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'content' => [],
                'settings' => []
            ], JSON_UNESCAPED_UNICODE);
        }
        
        exit;
    }

    /**
    * Извлекает настройки из POST-запроса
    * Поддерживает два формата:
    * - Массив $_POST['settings']
    * - Отдельные поля вида settings[ключ]=значение
    * @return array Массив настроек
    */
    private function extractSettingsFromPost() {
        $settings = [];
        
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            $settings = $_POST['settings'];
        } else {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'settings[') === 0) {
                    $settingKey = str_replace(['settings[', ']'], '', $key);
                    $settings[$settingKey] = $value;
                }
            }
        }
        
        return $settings;
    }

    /**
    * Извлекает контент из POST-запроса
    * Поддерживает два формата:
    * - Массив $_POST['content']
    * - Отдельные поля вида content[ключ]=значение
    * @return array Массив контента
    */
    private function extractContentFromPost() {
        $content = [];
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            $content = $_POST['content'];
        } else {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'content[') === 0) {
                    $contentKey = str_replace(['content[', ']'], '', $key);
                    $content[$contentKey] = $value;
                }
            }
        }
        
        return $content;
    }
}