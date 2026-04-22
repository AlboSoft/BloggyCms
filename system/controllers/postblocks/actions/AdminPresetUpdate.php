<?php

namespace postblocks\actions;

/**
* Действие обновления существующего пресета постблока
* @package postblocks\actions
*/
class AdminPresetUpdate extends PostBlockAction {
    
    /**
    * Метод выполнения обновления пресета
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {

            $presetId = $_POST['preset_id'] ?? 0;
            $presetName = $_POST['preset_name'] ?? '';
            $presetTemplate = $_POST['preset_template'] ?? '';

            if (empty($presetId) || empty($presetName)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINPRESETUPDATE_PARAMS_REQUIRED);
            }

            $preset = $this->postBlockModel->getPreset($presetId);
            if (!$preset) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINPRESETUPDATE_PRESET_NOT_FOUND);
            }

            $existingPreset = $this->postBlockModel->getPresetByName($preset['block_system_name'], $presetName);
            if ($existingPreset && $existingPreset['id'] != $presetId) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINPRESETUPDATE_PRESET_EXISTS);
            }

            $result = $this->postBlockModel->updatePreset($presetId, $presetName, $presetTemplate);

            echo json_encode([
                'success' => $result !== false,
                'message' => $result ? LANG_ACTION_POSTBLOCKS_ADMINPRESETUPDATE_SUCCESS : LANG_ACTION_POSTBLOCKS_ADMINPRESETUPDATE_ERROR
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}