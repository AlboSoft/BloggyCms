<?php

namespace fragments\actions;

/**
* Действие удаления фрагмента
*/
class AdminDelete extends FragmentAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINDELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        try {
            $fragment = $this->fragmentModel->getById($id);
            
            if (!$fragment) {
                \Notification::error(LANG_ACTION_FRAGMENTS_ADMINDELETE_NOT_FOUND);
                $this->redirect(ADMIN_URL . '/fragments');
                return;
            }
            
            $this->entryModel->deleteByFragment($id);
            $this->fragmentModel->delete($id);
            
            \Notification::success(LANG_ACTION_FRAGMENTS_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINDELETE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/fragments');
    }
}