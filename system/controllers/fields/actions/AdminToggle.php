<?php

namespace fields\actions;

/**
* Действие переключения активности поля в админ-панели
* @package fields\actions
*/
class AdminToggle extends FieldAction {
    
    /**
    * Метод выполнения переключения активности поля
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_FIELDS_ADMINTOGGLE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fields');
            return;
        }
        
        try {
            $field = $this->fieldModel->getById($id);
            if (!$field) {
                throw new \Exception(LANG_ACTION_FIELDS_ADMINTOGGLE_NOT_FOUND);
            }
            
            $newStatus = $field['is_active'] ? 0 : 1;
            
            $data = [
                'system_name' => $field['system_name'],
                'name' => $field['name'],
                'type' => $field['type'],
                'description' => $field['description'],
                'is_required' => $field['is_required'],
                'is_active' => $newStatus,
                'sort_order' => $field['sort_order'],
                'config' => $field['config']
            ];
            
            $result = $this->fieldModel->update($id, $data);
            
            if ($result) {
                $statusText = $newStatus ? LANG_ACTION_FIELDS_ADMINTOGGLE_ENABLED : LANG_ACTION_FIELDS_ADMINTOGGLE_DISABLED;
                \Notification::success(LANG_ACTION_FIELDS_ADMINTOGGLE_SUCCESS . $statusText);
            } else {
                throw new \Exception(LANG_ACTION_FIELDS_ADMINTOGGLE_UPDATE_FAILED);
            }
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_FIELDS_ADMINTOGGLE_ERROR . $e->getMessage());
        }
        
        if (isset($field['entity_type'])) {
            $this->redirect(ADMIN_URL . "/fields/entity/{$field['entity_type']}");
        } else {
            $this->redirect(ADMIN_URL . '/fields');
        }
    }
}