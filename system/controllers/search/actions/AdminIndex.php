<?php

namespace search\actions;

/**
* Действие отображения списка всех поисковых запросов в административной панели
* @package search\actions
*/
class AdminIndex extends SearchAction {
    
    /**
    * Метод выполнения отображения списка поисковых запросов
    * @return void
    */
    public function execute() {
        
        if (!$this->checkAuth()) {
            \Notification::error(LANG_ACTION_SEARCH_ADMININDEX_AUTH_REQUIRED);
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_SEARCH_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_SEARCH_ADMININDEX_BREADCRUMB_SEARCH);
        
        try {
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            
            $result = $this->searchModel->getAllSearchQueries($page);
            
            $this->render('admin/search/index', [
                'queries' => $result['queries'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'pageTitle' => LANG_ACTION_SEARCH_ADMININDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_SEARCH_ADMININDEX_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }

}