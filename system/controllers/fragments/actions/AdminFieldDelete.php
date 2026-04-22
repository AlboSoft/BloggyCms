<?php

namespace fragments\actions;

/**
* Действие удаления поля фрагмента
*/
class AdminFieldDelete extends FragmentAction {
    
    public function execute() {
        $fieldId = $this->params['id'] ?? null;
        
        if (!$fieldId) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINFIELDDELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $field = $this->fragmentModel->getFieldById($fieldId);
        
        if (!$field) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINFIELDDELETE_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        try {
            $this->fragmentModel->deleteField($fieldId);
            \Notification::success(LANG_ACTION_FRAGMENTS_ADMINFIELDDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINFIELDDELETE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/fragments/fields/' . $field['fragment_id']);
    }
}