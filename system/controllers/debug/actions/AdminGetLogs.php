<?php

namespace debug\actions;

/**
* Действие получения списка логов (AJAX)
* @package debug\actions
*/
class AdminGetLogs extends DebugAction {
    
    public function execute() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => LANG_ACTION_DEBUG_ADMINGETLOGS_AJAX_ONLY]);
            return;
        }
        
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);
        $type = $_GET['type'] ?? null;
        $onlyUnfixed = isset($_GET['only_unfixed']) && $_GET['only_unfixed'] == '1';
        
        $result = $this->debugModel->getAll($page, $perPage, $type, $onlyUnfixed);
        
        $this->jsonResponse([
            'success' => true,
            'logs' => $result['logs'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'current_page' => $result['current_page']
        ]);
    }
}