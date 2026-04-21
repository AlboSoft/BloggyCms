<?php

namespace debug\actions;

/**
* Действие удаления всех логов (AJAX)
* @package debug\actions
*/
class AdminDeleteAll extends DebugAction {
    
    public function execute() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINDELETEALL_AJAX_ONLY]);
            return;
        }
        
        $result = $this->debugModel->deleteAll();
        
        if ($result) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => LANG_ACTION_DEBUG_ADMINDELETEALL_TOAST
            ];
        }
        
        $this->jsonResponse([
            'success' => $result,
            'message' => $result ? LANG_ACTION_DEBUG_ADMINDELETEALL_SUCCESS : LANG_ACTION_DEBUG_ADMINDELETEALL_ERROR
        ]);
    }
}