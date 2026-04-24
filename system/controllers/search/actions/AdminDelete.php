<?php

namespace search\actions;

/**
* Действие удаления конкретного поискового запроса из истории
* @package search\actions
*/
class AdminDelete extends SearchAction {
    
    /**
    * Метод выполнения удаления поискового запроса
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_SEARCH_ADMINDELETE_NO_ID);
            $this->redirect(ADMIN_URL . '/search-history');
            return;
        }
        
        try {
            $this->searchModel->deleteSearchQuery($id);
            
            \Notification::success(LANG_ACTION_SEARCH_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_SEARCH_ADMINDELETE_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/search-history');
    }
}