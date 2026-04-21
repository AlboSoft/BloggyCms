<?php

namespace debug\actions;

/**
* Действие получения деталей лога (AJAX)
* @package debug\actions
*/
class AdminGetLog extends DebugAction {
    
    public function execute() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINGETLOG_AJAX_ONLY]);
            return;
        }
        
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINGETLOG_ID_NOT_SPECIFIED]);
            return;
        }
        
        $log = $this->debugModel->getById($id);
        
        if (!$log) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINGETLOG_NOT_FOUND]);
            return;
        }
        
        $this->jsonResponse([
            'success' => true,
            'log' => $log
        ]);
    }
}