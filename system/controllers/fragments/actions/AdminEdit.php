<?php

namespace fragments\actions;

/**
* Действие редактирования фрагмента
*/
class AdminEdit extends FragmentAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINEDIT_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $fragment = $this->fragmentModel->getById($id);
        
        if (!$fragment) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINEDIT_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINEDIT_BREADCRUMB_FRAGMENTS, ADMIN_URL . '/fragments');
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINEDIT_BREADCRUMB_EDIT . $fragment['name']);
        $this->setPageTitle(LANG_ACTION_FRAGMENTS_ADMINEDIT_PAGE_TITLE . $fragment['name']);
        
        $stats = $this->fragmentModel->getStats($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['name'])) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINEDIT_NAME_REQUIRED);
                }
                
                if (empty($_POST['system_name'])) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINEDIT_SYSTEM_NAME_REQUIRED);
                }
                
                if ($this->fragmentModel->isSystemNameExists($_POST['system_name'], $id)) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINEDIT_SYSTEM_NAME_EXISTS);
                }
                
                $data = [
                    'system_name' => $this->sanitizeSystemName($_POST['system_name']),
                    'name' => trim($_POST['name']),
                    'description' => trim($_POST['description'] ?? ''),
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                $data = $this->handleFragmentAssets($data);
                $this->fragmentModel->update($id, $data);
                
                \Notification::success(LANG_ACTION_FRAGMENTS_ADMINEDIT_SUCCESS);
                $this->redirect(ADMIN_URL . '/fragments/edit/' . $id);
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
            }
        }
        
        $this->render('admin/fragments/form', [
            'fragment' => $fragment,
            'stats' => $stats,
            'isEdit' => true
        ]);
    }
}