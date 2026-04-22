<?php

namespace menu\actions;

/**
* Действие удаления меню в админ-панели
* @package menu\actions
*/
class AdminDelete extends MenuAction {
    
    /**
    * Метод выполнения удаления меню
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_MENU_ADMINDELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        try {

            $menu = $this->menuModel->getById($id);
            
            if (!$menu) {
                throw new \Exception(LANG_ACTION_MENU_ADMINDELETE_NOT_FOUND);
            }

            $this->menuModel->delete($id);
            
            \Notification::success(LANG_ACTION_MENU_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_MENU_ADMINDELETE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/menu');
    }
}