<?php

namespace debug\actions;

/**
 * Действие переключения режима отладки (AJAX)
 * @package debug\actions
 */
class AdminToggle extends DebugAction {
    
    public function execute() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINTOGGLE_AJAX_ONLY]);
            return;
        }
        
        $currentState = \SettingsHelper::get('general', 'debug_mode', false);
        $newState = !$currentState;
        
        $settingsModel = new \SettingsModel($this->db);
        $generalSettings = $settingsModel->get('general');
        $generalSettings['debug_mode'] = $newState;
        $result = $settingsModel->save('general', $generalSettings);
        
        if ($result) {

            \DebugHandler::init($newState, $this->db);
            \SettingsHelper::clearCache('general');
            
            $message = $newState ? LANG_ACTION_DEBUG_ADMINTOGGLE_ENABLED : LANG_ACTION_DEBUG_ADMINTOGGLE_DISABLED;
            $_SESSION['toast'] = [
                'type' => $newState ? 'success' : 'warning',
                'message' => $message
            ];
        }
        
        $this->jsonResponse([
            'success' => $result,
            'debug_enabled' => $newState,
            'message' => $result 
                ? ($newState ? LANG_ACTION_DEBUG_ADMINTOGGLE_ON : LANG_ACTION_DEBUG_ADMINTOGGLE_OFF)
                : LANG_ACTION_DEBUG_ADMINTOGGLE_ERROR
        ]);
    }
}