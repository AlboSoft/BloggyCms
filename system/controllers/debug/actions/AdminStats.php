<?php

namespace debug\actions;

/**
* Действие получения статистики (AJAX)
* @package debug\actions
*/
class AdminStats extends DebugAction {
    
    public function execute() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINSTATS_AJAX_ONLY]);
            return;
        }
        
        $stats = $this->debugModel->getStats();
        
        $this->jsonResponse([
            'success' => true,
            'stats' => $stats
        ]);
    }
}