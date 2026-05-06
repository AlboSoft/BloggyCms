<?php

namespace fragments\actions;

/**
* Действие создания фрагмента
*/
class AdminCreate extends FragmentAction {
    
    public function execute() {
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINCREATE_BREADCRUMB_FRAGMENTS, ADMIN_URL . '/fragments');
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINCREATE_BREADCRUMB_CREATE);
        $this->setPageTitle(LANG_ACTION_FRAGMENTS_ADMINCREATE_PAGE_TITLE);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['name'])) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINCREATE_NAME_REQUIRED);
                }
                
                if (empty($_POST['system_name'])) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINCREATE_SYSTEM_NAME_REQUIRED);
                }
                
                if ($this->fragmentModel->isSystemNameExists($_POST['system_name'])) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINCREATE_SYSTEM_NAME_EXISTS);
                }
                
                $data = [
                    'system_name' => $this->sanitizeSystemName($_POST['system_name']),
                    'name' => trim($_POST['name']),
                    'description' => trim($_POST['description'] ?? ''),
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                $data = $this->handleFragmentAssets($data);
                $fragmentId = $this->fragmentModel->create($data);
                
                if (!$fragmentId || !is_numeric($fragmentId)) {
                    throw new \Exception(LANG_ACTION_FRAGMENTS_ADMINCREATE_CREATE_FAILED);
                }
                
                \Notification::success(LANG_ACTION_FRAGMENTS_ADMINCREATE_SUCCESS);
                $this->redirect(ADMIN_URL . '/fragments');
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
            }
        }
        
        $this->render('admin/fragments/form', [
            'fragment' => $_POST ?? null,
            'pageTitle' => LANG_ACTION_FRAGMENTS_ADMINCREATE_PAGE_TITLE,
            'isEdit' => false
        ]);
    }
}