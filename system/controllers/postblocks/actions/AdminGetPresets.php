<?php

namespace postblocks\actions;

/**
* Действие получения списка пресетов для постблока
* @package postblocks\actions
*/
class AdminGetPresets extends PostBlockAction {
    
    /**
    * Метод выполнения получения списка пресетов
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {

            $systemName = $_GET['system_name'] ?? '';

            if (empty($systemName)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINGETPRESETS_SYSTEM_NAME_NOT_SPECIFIED);
            }

            $presets = $this->postBlockModel->getBlockPresets($systemName);

            echo json_encode([
                'success' => true,
                'presets' => $presets
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}