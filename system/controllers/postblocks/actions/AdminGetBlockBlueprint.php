<?php

namespace postblocks\actions;

/**
* Получает «чертёж» постблока: контент по умолчанию, настройки по умолчанию
* и список пресетов оформления — одним ответом.
*
* Конструктор контента использует этот метод, чтобы добавление блока
* требовало одного запроса вместо трёх.
*
* @package postblocks\actions
* @extends PostBlockAction
*/
class AdminGetBlockBlueprint extends PostBlockAction {

    /**
    * Метод выполнения получения чертежа блока
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (is_array($input) && !empty($input['system_name'])) {
                $systemName = $input['system_name'];
            } else {
                $systemName = $_GET['system_name'] ?? '';
            }

            if (empty($systemName)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINGETBLOCKBLUEPRINT_SYSTEM_NAME_NOT_SPECIFIED);
            }

            $postBlock = $this->postBlockManager->getPostBlock($systemName);

            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception(sprintf(LANG_ACTION_POSTBLOCKS_ADMINGETBLOCKBLUEPRINT_BLOCK_NOT_FOUND, $systemName));
            }

            $blockInstance = $postBlock['class'];

            $content = $blockInstance->getDefaultContent();
            $settings = $blockInstance->getDefaultSettings();
            $presets = $this->postBlockModel->getBlockPresets($systemName);

            echo json_encode([
                'success' => true,
                'content' => is_array($content) ? (object)$content : (object)[],
                'settings' => is_array($settings) ? (object)$settings : (object)[],
                'presets' => is_array($presets) ? $presets : []
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'content' => (object)[],
                'settings' => (object)[],
                'presets' => []
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }
}
