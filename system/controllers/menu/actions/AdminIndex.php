<?php

namespace menu\actions;

/**
* Действие отображения списка всех меню в админ-панели
* @package menu\actions
* @extends MenuAction
*/
class AdminIndex extends MenuAction {
    
    /**
    * Метод выполнения отображения списка меню 
    * @return void
    */
    public function execute() {

        $this->addBreadcrumb(LANG_ACTION_MENU_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMININDEX_BREADCRUMB_MENU);
        
        $menus = $this->menuModel->getAll();
        
        $this->render('admin/menu/index', [
            'menus' => $menus,
            'pageTitle' => LANG_ACTION_MENU_ADMININDEX_PAGE_TITLE
        ]);
    }
    
}