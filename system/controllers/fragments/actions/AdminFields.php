<?php

namespace fragments\actions;

/**
* Действие отображения списка полей фрагмента
*/
class AdminFields extends FragmentAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINFIELDS_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $fragment = $this->fragmentModel->getById($id);
        
        if (!$fragment) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINFIELDS_FRAGMENT_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINFIELDS_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINFIELDS_BREADCRUMB_FRAGMENTS, ADMIN_URL . '/fragments');
        $this->addBreadcrumb($fragment['name'], ADMIN_URL . '/fragments/edit/' . $id);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINFIELDS_BREADCRUMB_FIELDS);
        $this->setPageTitle(LANG_ACTION_FRAGMENTS_ADMINFIELDS_PAGE_TITLE . $fragment['name']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        $perPage = 20;
        
        $result = $this->fragmentModel->getFieldsPaginated($id, $page, $perPage);
        $fields = $result['fields'] ?? [];
        
        $fieldsForDisplay = array_filter($fields, function($field) {
            return isset($field['show_in_list']) && $field['show_in_list'] == 1;
        });
        
        $this->render('admin/fragments/fields_list', [
            'fragment' => $fragment,
            'fields' => $fields,
            'fieldsForDisplay' => $fieldsForDisplay,
            'total' => $result['total'] ?? 0,
            'pages' => $result['pages'] ?? 1,
            'current_page' => $result['current_page'] ?? 1,
            'perPage' => $perPage
        ]);
    }
}