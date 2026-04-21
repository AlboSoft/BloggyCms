<?php

namespace forms\actions;

/**
* Действие переключения статуса формы
*/
class AdminToggleStatus extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error(LANG_ACTION_FORMS_ADMINTOGGLESTATUS_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        try {
            $form = $this->formModel->getById($id);
            if (!$form) {
                throw new \Exception(LANG_ACTION_FORMS_ADMINTOGGLESTATUS_FORM_NOT_FOUND);
            }
            
            $newStatus = ($form['status'] === 'active') ? 'inactive' : 'active';
            
            $success = $this->formModel->update($id, [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                $statusText = $newStatus === 'active' ? LANG_ACTION_FORMS_ADMINTOGGLESTATUS_ACTIVE : LANG_ACTION_FORMS_ADMINTOGGLESTATUS_INACTIVE;
                \Notification::success(sprintf(LANG_ACTION_FORMS_ADMINTOGGLESTATUS_SUCCESS, $statusText));
            } else {
                throw new \Exception(LANG_ACTION_FORMS_ADMINTOGGLESTATUS_UPDATE_FAILED);
            }
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_FORMS_ADMINTOGGLESTATUS_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/forms');
    }
}