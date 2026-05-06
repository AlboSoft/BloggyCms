<?php

namespace fragments\actions;

/**
* Действие создания записи фрагмента
*/
class AdminEntryCreate extends FragmentAction {
    
    public function execute() {
        $fragmentId = $this->params['fragment_id'] ?? null;
        
        if (!$fragmentId) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $fragment = $this->fragmentModel->getById($fragmentId);
        
        if (!$fragment) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_FRAGMENT_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_BREADCRUMB_FRAGMENTS, ADMIN_URL . '/fragments');
        $this->addBreadcrumb($fragment['name'], ADMIN_URL . '/fragments/edit/' . $fragmentId);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_BREADCRUMB_ENTRIES, ADMIN_URL . '/fragments/entries/' . $fragmentId);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_BREADCRUMB_CREATE);
        $this->setPageTitle(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_PAGE_TITLE . $fragment['name']);
        
        $fields = $this->fragmentModel->getFields($fragmentId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->processEntryData($fields, $_POST, $_FILES);
                
                $this->entryModel->create($fragmentId, $data);
                
                \Notification::success(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_SUCCESS);
                $this->redirect(ADMIN_URL . '/fragments/entries/' . $fragmentId);
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
            }
        }
        
        $this->render('admin/fragments/entry_form', [
            'fragment' => $fragment,
            'fields' => $fields,
            'entry' => null,
            'pageTitle' => LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_PAGE_TITLE . $fragment['name'],
            'isEdit' => false
        ]);
    }
    
    /**
    * Обработка данных записи 
    * @param array $fields
    * @param array $postData
    * @param array $filesData
    * @return array
    */
    private function processEntryData($fields, $postData, $filesData) {
        $data = [];
        $errors = [];
        
        foreach ($fields as $field) {
            $systemName = $field['system_name'];
            $fieldType = $field['type'];
            $config = $field['config'];
            
            $fieldData = [
                'type' => $fieldType,
                'system_name' => $systemName,
                'config' => $config,
                'is_required' => $field['is_required']
            ];
            
            $value = $this->fieldManager->processFieldValue($fieldData, $postData, $filesData);

            if ($field['is_required'] && (empty($value) && $value !== '0')) {
                $errors[] = sprintf(LANG_ACTION_FRAGMENTS_ADMINENTRYCREATE_FIELD_REQUIRED, $field['name']);
                continue;
            }
            
            $validationResult = $this->fieldManager->validateFieldValue($fieldData, $value, $postData, $filesData);
            if (!$validationResult['is_valid']) {
                $errors[] = $validationResult['message'];
                continue;
            }
            
            if ($value !== null) {
                $data[$systemName] = $value;
            }
        }
        
        if (!empty($errors)) {
            throw new \Exception(implode('<br>', $errors));
        }
        
        return $data;
    }
}