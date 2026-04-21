<?php

namespace debug\actions;

/**
* Действие удаления лога (AJAX)
* @package debug\actions
*/
class AdminDelete extends DebugAction {
    
    public function execute() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINDELETE_AJAX_ONLY]);
            return;
        }
        
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINDELETE_ID_NOT_SPECIFIED]);
            return;
        }
        
        $result = $this->debugModel->delete($id);
        
        $this->jsonResponse([
            'success' => $result,
            'message' => $result ? LANG_ACTION_DEBUG_ADMINDELETE_SUCCESS : LANG_ACTION_DEBUG_ADMINDELETE_ERROR
        ]);
    }
}