<?php

namespace pages\actions;

/**
* Действие удаления страницы в административной панели 
* @package pages\actions
*/
class AdminDelete extends PageAction {
    
    protected $id;
    
    /**
    * Устанавливает ID страницы для удаления 
    * @param int|null $id ID страницы
    * @return void
    */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
    * Метод выполнения удаления страницы
    * @return void
    */
    public function execute() {
        
        if (!$this->validatePageId()) {
            return;
        }
        
        $this->deletePage();
        
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
    * Проверяет наличие ID страницы для удаления
    * @return bool true если ID указан, false в противном случае
    */
    private function validatePageId() {
        if (!$this->id) {
            \Notification::error(LANG_ACTION_PAGES_ADMINDELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/pages');
            return false;
        }
        return true;
    }
    
    /**
    * Выполняет удаление страницы из базы данных
    * @return void
    */
    private function deletePage() {
        try {
            $this->pageModel->delete($this->id);
            
            \Notification::success(LANG_ACTION_PAGES_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_PAGES_ADMINDELETE_ERROR);
            
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                \Notification::error(LANG_ACTION_PAGES_ADMINDELETE_DETAILS . $e->getMessage());
            }
        }
    }
    
    /**
    * Обрабатывает ситуацию с отсутствием прав доступа 
    * @return void
    */
    private function handleAccessDenied() {
        \Notification::error(LANG_ACTION_PAGES_ADMINDELETE_ACCESS_DENIED);
        $this->redirect(ADMIN_URL . '/login');
    }
}