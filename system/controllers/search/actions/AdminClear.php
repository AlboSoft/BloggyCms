<?php

namespace search\actions;

/**
* Действие очистки всей истории поисковых запросов в административной панели
* @package search\actions
*/
class AdminClear extends SearchAction {
    
    /**
    * Метод выполнения очистки истории поиска
    * @return void
    */
    public function execute() {
        try {

            $this->searchModel->clearSearchHistory();
            
            \Notification::success(LANG_ACTION_SEARCH_ADMINCLEAR_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_SEARCH_ADMINCLEAR_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/search-history');
    }
}