<?php

namespace postblocks\actions;

/**
* Действие создания нового пресета для постблока
* @package postblocks\actions
*/
class AdminPresetCreate extends PostBlockAction {
    
    /**
    * Метод выполнения создания пресета
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {

            $systemName = $_POST['system_name'] ?? '';
            $presetName = $_POST['preset_name'] ?? '';
            $presetTemplate = $_POST['preset_template'] ?? '';

            if (empty($systemName) || empty($presetName)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINPRESETCREATE_SYSTEM_OR_NAME_NOT_SPECIFIED);
            }

            $postBlock = $this->postBlockManager->getPostBlock($systemName);
            if (!$postBlock) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINPRESETCREATE_BLOCK_NOT_FOUND);
            }

            $existingPreset = $this->postBlockModel->getPresetByName($systemName, $presetName);
            if ($existingPreset) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINPRESETCREATE_PRESET_EXISTS);
            }

            $result = $this->postBlockModel->createPreset($systemName, $presetName, $presetTemplate);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => LANG_ACTION_POSTBLOCKS_ADMINPRESETCREATE_SUCCESS,
                    'preset_id' => $this->db->lastInsertId()
                ]);
            } else {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINPRESETCREATE_CREATE_ERROR);
            }

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}