<?php

namespace categories\actions;

/**
* Действие создания новой категории
* @package categories\actions
*/
class Create extends CategoryAction {
    
    protected $pageTitle;
    
    /**
    * Метод выполнения создания категории
    * @return void
    */
    public function execute() {

        $this->pageTitle = LANG_ACTION_CATEGORIES_CREATE_PAGE_TITLE;
        $this->addBreadcrumb(LANG_ACTION_CATEGORIES_CREATE_BREADCRUMB_CATEGORIES, ADMIN_URL . '/categories');
        $this->addBreadcrumb(LANG_ACTION_CATEGORIES_CREATE_BREADCRUMB_CREATE);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['name']),
                    'description' => trim($_POST['description'] ?? ''),
                    'meta_title' => trim($_POST['meta_title'] ?? ''),
                    'meta_description' => trim($_POST['meta_description'] ?? ''),
                    'canonical_url' => trim($_POST['canonical_url'] ?? ''),
                    'noindex' => isset($_POST['noindex']) ? 1 : 0,
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
                    'password_protected' => isset($_POST['password_protected']) ? 1 : 0,
                    'password' => isset($_POST['password_protected']) && !empty($_POST['password']) 
                        ? trim($_POST['password']) 
                        : null
                ];
                
                if (!empty($_FILES['image']['name'])) {
                    $uploadDir = UPLOADS_PATH . '/images/categories';
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $maxSize = 5120;
                    $fileName = \FileUpload::upload($_FILES['image'], $uploadDir, $allowedTypes, $maxSize);
                    $data['image'] = 'categories/' . $fileName;
                } else {
                    $data['image'] = '';
                }
                
                $categoryId = $this->categoryModel->create($data);
                
                $fieldModel = new \FieldModel($this->db);
                $fieldManager = new \FieldManager($this->db);
                
                $customFields = $fieldModel->getActiveByEntityType('category');
                
                foreach ($customFields as $field) {
                    try {
                        $value = $fieldManager->processFieldValue($field, $_POST, $_FILES);
                        
                        if ($value !== null) {
                            $config = is_array($field['config']) 
                                ? $field['config'] 
                                : json_decode($field['config'] ?? '{}', true);
                            $fieldModel->saveFieldValue(
                                'category',
                                $categoryId,
                                $field['system_name'],
                                $value
                            );
                        }
                    } catch (\Exception $e) {
                        \Notification::error(sprintf(LANG_ACTION_CATEGORIES_CREATE_FIELD_ERROR, $field['name'], $e->getMessage()));
                    }
                }
                
                \Notification::success(LANG_ACTION_CATEGORIES_CREATE_SUCCESS);
                $this->redirect(ADMIN_URL . '/categories');
                return;
                
            } catch (\Exception $e) {
                \Notification::error(LANG_ACTION_CATEGORIES_CREATE_ERROR . $e->getMessage());

                $this->render('admin/categories/form', [
                    'data' => $_POST,
                    'pageTitle' => $this->pageTitle
                ]);
                return;
            }
        }
        
        $this->render('admin/categories/form', [
            'pageTitle' => $this->pageTitle
        ]);
    }
}