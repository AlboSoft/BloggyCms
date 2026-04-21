<?php

namespace debug\actions;

/**
* Действие отметки лога как исправленного (AJAX)
* @package debug\actions
*/
class AdminMarkFixed extends DebugAction {
    
    public function execute() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINMARKFIXED_AJAX_ONLY]);
            return;
        }
        
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINMARKFIXED_ID_NOT_SPECIFIED]);
            return;
        }
        
        $result = $this->debugModel->markAsFixed($id);
        
        $this->jsonResponse([
            'success' => $result,
            'message' => $result ? LANG_ACTION_DEBUG_ADMINMARKFIXED_SUCCESS : LANG_ACTION_DEBUG_ADMINMARKFIXED_ERROR
        ]);
    }
}