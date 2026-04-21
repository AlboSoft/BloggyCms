<?php

namespace fields\actions;

/**
* Действие удаления дополнительного поля в админ-панели
* @package fields\actions
*/
class AdminDelete extends FieldAction {
    
    /**
    * Метод выполнения удаления поля
    * @return void
    */
    public function execute() {
        
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_FIELDS_ADMINDELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fields');
            return;
        }
        
        try {

            $field = $this->fieldModel->getById($id);
        
            $this->fieldModel->delete($id);
            
            \Notification::success(LANG_ACTION_FIELDS_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_FIELDS_ADMINDELETE_ERROR);
        }
        
        if (isset($field['entity_type'])) {
            $this->redirect(ADMIN_URL . "/fields/entity/{$field['entity_type']}");
        } else {
            $this->redirect(ADMIN_URL . '/fields');
        }
    }
}