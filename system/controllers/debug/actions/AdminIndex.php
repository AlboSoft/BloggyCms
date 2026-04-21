<?php

namespace debug\actions;

/**
* Действие отображения главной страницы отладки
* @package debug\actions
*/
class AdminIndex extends DebugAction {
    
    public function execute() {
        $this->addBreadcrumb(LANG_ACTION_DEBUG_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_DEBUG_ADMININDEX_BREADCRUMB_DEBUG);
        $this->setPageTitle(LANG_ACTION_DEBUG_ADMININDEX_PAGE_TITLE);
        
        $stats = $this->debugModel->getStats();
        $debugEnabled = \SettingsHelper::get('general', 'debug_mode', false);
        
        $errorLogs = $this->getErrorLogEntries(50);
        
        $this->render('admin/debug/index', [
            'stats' => $stats,
            'debug_enabled' => $debugEnabled,
            'error_logs' => $errorLogs,
            'pageTitle' => LANG_ACTION_DEBUG_ADMININDEX_PAGE_TITLE
        ]);
    }
    
    /**
    * Получает последние записи из error_log
    * @param int $limit
    * @return array
    */
    private function getErrorLogEntries($limit = 50) {
        $logFile = ini_get('error_log');
        if (!$logFile || !file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile);
        $lines = array_reverse($lines);
        $entries = [];
        
        foreach ($lines as $line) {
            if (count($entries) >= $limit) break;
            
            if (preg_match('/\[(.*?)\]\s+(\w+):\s+(.*)/', $line, $matches)) {
                $entries[] = [
                    'date' => $matches[1],
                    'type' => strtolower($matches[2]),
                    'message' => trim($matches[3]),
                    'full_line' => trim($line)
                ];
            } else {
                $entries[] = [
                    'date' => date('Y-m-d H:i:s'),
                    'type' => 'info',
                    'message' => trim($line),
                    'full_line' => trim($line)
                ];
            }
        }
        
        return $entries;
    }
}