<?php

namespace tags\actions;

/**
* Действие создания нового тега в административной панели
* @package tags\actions
*/
class Create extends TagAction {
    
    protected $pageTitle;
    
    /**
    * Метод выполнения создания тега
    * @return void
    */
    public function execute() {

        $this->pageTitle = LANG_ACTION_TAGS_CREATE_PAGE_TITLE;
        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_TAGS, ADMIN_URL . '/tags');
        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_CREATE);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                ];
                
                if (empty($data['name'])) {
                    throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_EMPTY_NAME);
                }
                
                if ($this->tagModel->isNameExists($data['name'])) {
                    throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_NAME_EXISTS);
                }
                
                if (!empty($_FILES['image']['name'])) {
                    $uploadDir = UPLOADS_PATH . '/tags';
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $maxSize = 2048;
                    $fileName = \FileUpload::upload($_FILES['image'], $uploadDir, $allowedTypes, $maxSize);
                    $data['image'] = $fileName;
                } else {
                    $data['image'] = null;
                }
                
                $this->tagModel->create($data);
                
                \Notification::success(LANG_ACTION_TAGS_CREATE_SUCCESS);
                $this->redirect(ADMIN_URL . '/tags');
                return;
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());

                $this->render('admin/tags/form', [
                    'data' => $_POST,
                    'pageTitle' => $this->pageTitle
                ]);
                return;
            }
        }
        
        $this->render('admin/tags/form', [
            'pageTitle' => $this->pageTitle
        ]);
    }
}