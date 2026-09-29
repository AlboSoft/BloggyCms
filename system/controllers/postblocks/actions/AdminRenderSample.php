<?php

namespace postblocks\actions;

/**
* Рендерит демо-образец постблока (с применением шаблона, пресета или глобального шаблона типа) для предпросмотра в административной панели.
* @package postblocks\actions
* @extends PostBlockAction
*/
class AdminRenderSample extends PostBlockAction {
    
    /**
    * Метод выполнения рендера демо-образца
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
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINRENDERSAMPLE_SYSTEM_NAME_NOT_SPECIFIED);
            }

            $postBlock = $this->postBlockManager->getPostBlock($systemName);

            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception(sprintf(LANG_ACTION_POSTBLOCKS_ADMINRENDERSAMPLE_BLOCK_NOT_FOUND, $systemName));
            }

            $blockInstance = $postBlock['class'];

            $content = $blockInstance->getDefaultContent();
            if (isset($input['content']) && is_array($input['content'])) {
                $content = array_merge($content, $input['content']);
            }

            $settings = $blockInstance->getDefaultSettings();
            if (isset($input['settings']) && is_array($input['settings'])) {
                $settings = array_merge($settings, $input['settings']);
            }

            $presetName = '';

            if (!empty($input['preset_id'])) {
                $presetId = (int)$input['preset_id'];
                $preset = $this->postBlockModel->getPreset($presetId);
                if (!$preset || $preset['block_system_name'] !== $systemName) {
                    throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINRENDERSAMPLE_PRESET_NOT_FOUND);
                }
                $settings['preset_id'] = $presetId;
                $settings['preset_name'] = $preset['preset_name'] ?? '';
                $presetName = $preset['preset_name'] ?? '';
            } else {
                unset($settings['preset_id']);
            }

            $html = $blockInstance->processFrontend($content, $settings);

            echo json_encode([
                'success' => true,
                'html' => $html,
                'block' => $blockInstance->getName(),
                'preset_name' => $presetName
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }
}
