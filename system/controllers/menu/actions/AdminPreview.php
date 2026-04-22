<?php

namespace menu\actions;

/**
* Действие предпросмотра меню в админ-панели
* @package menu\actions
*/
class AdminPreview extends MenuAction {
    
    /**
    * Метод выполнения предпросмотра меню
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_MENU_ADMINPREVIEW_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $menu = $this->menuModel->getById($id);

        if (!$menu) {
            \Notification::error(LANG_ACTION_MENU_ADMINPREVIEW_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINPREVIEW_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINPREVIEW_BREADCRUMB_MENU, ADMIN_URL . '/menu');
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINPREVIEW_BREADCRUMB_EDIT . html($menu['name']), ADMIN_URL . '/menu/edit/' . $id);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINPREVIEW_BREADCRUMB_PREVIEW);
        
        $currentTheme = $this->menuModel->getCurrentTheme();
        
        $structure = json_decode($menu['structure'], true) ?: [];
        
        $templateFile = TEMPLATES_PATH . '/' . $currentTheme . '/front/assets/menu/' . $menu['template'] . '.php';
        
        $this->render('admin/menu/preview', [
            'menu' => $menu,
            'structure' => $structure,
            'templateFile' => $templateFile,
            'currentTheme' => $currentTheme,
            'pageTitle' => LANG_ACTION_MENU_ADMINPREVIEW_PAGE_TITLE . $menu['name']
        ]);
    }

}