<?php

namespace fields\actions;

/**
* Действие создания нового дополнительного поля в админ-панели
* @package fields\actions
*/
class AdminCreate extends FieldAction {
    
    /**
    * Метод выполнения создания поля
    * @return void
    */
    public function execute() {

        $entityType = $this->params['entityType'] ?? null;
        
        if (!$entityType) {
            \Notification::error(LANG_ACTION_FIELDS_ADMINCREATE_ENTITY_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fields');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FIELDS_ADMINCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FIELDS_ADMINCREATE_BREADCRUMB_FIELDS, ADMIN_URL . '/fields');
        $this->addBreadcrumb($this->getEntityName($entityType, true), ADMIN_URL . '/fields/entity/' . $entityType);
        $this->addBreadcrumb(LANG_ACTION_FIELDS_ADMINCREATE_BREADCRUMB_CREATE);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['name']) || empty($_POST['system_name']) || empty($_POST['type'])) {
                    throw new \Exception(LANG_ACTION_FIELDS_ADMINCREATE_REQUIRED_FIELDS);
                }
                
                $config = $_POST['config'] ?? [];
                $config = $this->fieldModel->processFieldConfig($_POST['type'], $config);
                $data = [
                    'name' => $_POST['name'],
                    'system_name' => $_POST['system_name'],
                    'type' => $_POST['type'],
                    'entity_type' => $entityType,
                    'description' => $_POST['description'] ?? '',
                    'is_required' => isset($_POST['is_required']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 1,
                    'sort_order' => $_POST['sort_order'] ?? 0,
                    'show_in_post' => isset($_POST['show_in_post']) ? 1 : 0,
                    'show_in_list' => isset($_POST['show_in_list']) ? 1 : 0,
                    'config' => json_encode($config)
                ];
                
                $this->fieldModel->create($data);
            
                \Notification::success(LANG_ACTION_FIELDS_ADMINCREATE_SUCCESS);
                
                $this->redirect(ADMIN_URL . "/fields/entity/{$entityType}");
                
            } catch (\Exception $e) {

                \Notification::error(LANG_ACTION_FIELDS_ADMINCREATE_ERROR . $e->getMessage());
                
                $fieldTypes = $this->fieldModel->getFieldTypes();
                
                $this->render('admin/fields/form', [
                    'fieldTypes' => $fieldTypes,
                    'entityType' => $entityType,
                    'entityName' => $this->getEntityName($entityType),
                    'data' => $_POST,
                    'pageTitle' => LANG_ACTION_FIELDS_ADMINCREATE_PAGE_TITLE
                ]);
            }
        } 
        else {
            
            $fieldTypes = $this->fieldModel->getFieldTypes();
            
            $this->render('admin/fields/form', [
                'fieldTypes' => $fieldTypes,
                'entityType' => $entityType,
                'entityName' => $this->getEntityName($entityType),
                'pageTitle' => LANG_ACTION_FIELDS_ADMINCREATE_PAGE_TITLE
            ]);
        }
    }
}