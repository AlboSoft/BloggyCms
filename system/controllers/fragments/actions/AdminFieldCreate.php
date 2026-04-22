<?php

namespace fragments\actions;

/**
* Действие создания поля фрагмента
*/
class AdminFieldCreate extends FragmentAction {
    
    public function execute() {
        $fragmentId = $this->params['fragment_id'] ?? null;
        
        if (!$fragmentId) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $fragment = $this->fragmentModel->getById($fragmentId);
        
        if (!$fragment) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_FRAGMENT_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_BREADCRUMB_FRAGMENTS, ADMIN_URL . '/fragments');
        $this->addBreadcrumb($fragment['name'], ADMIN_URL . '/fragments/edit/' . $fragmentId);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_BREADCRUMB_FIELDS, ADMIN_URL . '/fragments/fields/' . $fragmentId);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_BREADCRUMB_CREATE);
        $this->setPageTitle(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_PAGE_TITLE . $fragment['name']);
        
        $fieldTypes = $this->fieldManager->getAvailableFieldTypes();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['name'])) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_NAME_REQUIRED);
                }
                
                if (empty($_POST['system_name'])) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_SYSTEM_NAME_REQUIRED);
                }
                
                if ($this->fragmentModel->isFieldSystemNameExists($fragmentId, $_POST['system_name'])) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_SYSTEM_NAME_EXISTS);
                }
                
                $config = $_POST['config'] ?? [];
                $fieldType = $_POST['type'] ?? 'string';
                
                $config = $this->fieldManager->processFieldConfig($fieldType, $config);
                
                $data = [
                    'system_name' => $this->sanitizeSystemName($_POST['system_name']),
                    'name' => trim($_POST['name']),
                    'type' => $fieldType,
                    'description' => trim($_POST['description'] ?? ''),
                    'is_required' => isset($_POST['is_required']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 1,
                    'show_in_list' => isset($_POST['show_in_list']) ? 1 : 0,
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
                    'config' => $config
                ];
                
                $this->fragmentModel->createField($fragmentId, $data);
                
                \Notification::success(LANG_ACTION_FRAGMENTS_ADMINFIELDCREATE_SUCCESS);
                $this->redirect(ADMIN_URL . '/fragments/fields/' . $fragmentId);
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
            }
        }
        
        $this->render('admin/fragments/field_form', [
            'fragment' => $fragment,
            'fieldTypes' => $fieldTypes,
            'field' => null,
            'isEdit' => false
        ]);
    }
}