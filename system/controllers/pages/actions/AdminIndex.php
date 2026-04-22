<?php

namespace pages\actions;

/**
* Действие отображения списка всех страниц в административной панели
* @package pages\actions
*/
class AdminIndex extends PageAction {
    
    /**
    * Метод выполнения отображения списка страниц
    * @return void
    */
    public function execute() {
        
        $this->addBreadcrumb(LANG_ACTION_PAGES_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_PAGES_ADMININDEX_BREADCRUMB_PAGES);
        
        try {
            $pages = $this->loadPages();
            
            $this->renderPageList($pages);
            
        } catch (\Exception $e) {
            $this->handleLoadError($e);
        }
    }
    
    /**
    * Загружает список всех страниц из базы данных 
    * @return array Массив всех страниц
    */
    private function loadPages() {
        return $this->pageModel->getAll();
    }
    
    /**
    * Отображает страницу со списком страниц 
    * @param array $pages Массив страниц для отображения
    * @return void
    */
    private function renderPageList($pages) {
        $this->render('admin/pages/index', [
            'pages' => $pages,
            'pageTitle' => LANG_ACTION_PAGES_ADMININDEX_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ситуацию с отсутствием прав доступа
    * @return void
    */
    private function handleAccessDenied() {
        \Notification::error(LANG_ACTION_PAGES_ADMININDEX_ACCESS_DENIED);
        $this->redirect(ADMIN_URL . '/login');
    }
    
    /**
    * Обрабатывает ошибку при загрузке списка страниц
    * @param \Exception $e Исключение
    * @return void
    */
    private function handleLoadError($e) {
        \Notification::error(LANG_ACTION_PAGES_ADMININDEX_LOAD_ERROR);
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            \Notification::error(LANG_ACTION_PAGES_ADMININDEX_DETAILS . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL);
    }
}