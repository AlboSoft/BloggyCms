<?php

namespace fragments\actions;

/**
* Действие редактирования записи фрагмента
*/
class AdminEntryEdit extends FragmentAction {
    
    public function execute() {
        $entryId = $this->params['id'] ?? null;
        
        if (!$entryId) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $entry = $this->entryModel->getById($entryId);
        
        if (!$entry) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $fragment = $this->fragmentModel->getById($entry['fragment_id']);
        
        if (!$fragment) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_FRAGMENT_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_BREADCRUMB_FRAGMENTS, ADMIN_URL . '/fragments');
        $this->addBreadcrumb($fragment['name'], ADMIN_URL . '/fragments/edit/' . $fragment['id']);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_BREADCRUMB_ENTRIES, ADMIN_URL . '/fragments/entries/' . $fragment['id']);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_BREADCRUMB_EDIT . $entryId);
        $this->setPageTitle(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_PAGE_TITLE . $fragment['name']);
        
        $fields = $this->fragmentModel->getFields($fragment['id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->processEntryData($fields, $_POST, $_FILES, $entry['data']);
                
                $this->entryModel->update($entryId, $data);
                
                \Notification::success(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_SUCCESS);
                $this->redirect(ADMIN_URL . '/fragments/entries/' . $fragment['id']);
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
            }
        }
        
        $this->render('admin/fragments/entry_form', [
            'fragment' => $fragment,
            'fields' => $fields,
            'entry' => $entry,
            'isEdit' => true
        ]);
    }
    
    /**
    * Обработка данных записи 
    * @param array $fields
    * @param array $postData
    * @param array $filesData
    * @param array $currentData
    * @return array
    */
    private function processEntryData($fields, $postData, $filesData, $currentData = []) {
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
            
            $value = $this->fieldManager->processFieldValue($fieldData, $postData, $filesData, $currentData);
            
            if ($field['is_required'] && (empty($value) && $value !== '0')) {
                $errors[] = sprintf(LANG_ACTION_FRAGMENTS_ADMINENTRYEDIT_FIELD_REQUIRED, $field['name']);
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