<?php

namespace tags\actions;

/**
* Действие редактирования тега в административной панели
* @package tags\actions
*/
class Edit extends TagAction {
    
    protected $pageTitle;
    
    /**
    * Метод выполнения редактирования тега
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_TAGS_EDIT_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/tags');
            return;
        }

        $this->pageTitle = LANG_ACTION_TAGS_EDIT_PAGE_TITLE;
        $this->addBreadcrumb(LANG_ACTION_TAGS_EDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_TAGS_EDIT_BREADCRUMB_TAGS, ADMIN_URL . '/tags');
        
        try {
            $tag = $this->tagModel->getById($id);
            
            if (!$tag) {
                \Notification::error(LANG_ACTION_TAGS_EDIT_TAG_NOT_FOUND);
                $this->redirect(ADMIN_URL . '/tags');
                return;
            }
            
            $this->addBreadcrumb(sprintf(LANG_ACTION_TAGS_EDIT_BREADCRUMB_EDIT, $tag['name']));
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $data = [
                        'name' => trim($_POST['name'] ?? ''),
                        'description' => trim($_POST['description'] ?? ''),
                    ];
                    
                    if (empty($data['name'])) {
                        throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_EMPTY_NAME);
                    }
                    
                    if ($this->tagModel->isNameExists($data['name'], $id)) {
                        throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_NAME_EXISTS);
                    }
                    
                    if (!empty($_FILES['image']['name'])) {
                        $uploadDir = UPLOADS_PATH . '/tags';
                        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $maxSize = 2048;
                        $fileName = \FileUpload::upload($_FILES['image'], $uploadDir, $allowedTypes, $maxSize);
                        $data['image'] = $fileName;
                        
                        if (!empty($tag['image'])) {
                            \FileUpload::delete(UPLOADS_PATH . '/tags/' . $tag['image']);
                        }
                    } 
                    elseif (isset($_POST['delete_image']) && $_POST['delete_image']) {
                        if (!empty($tag['image'])) {
                            \FileUpload::delete(UPLOADS_PATH . '/tags/' . $tag['image']);
                        }
                        $data['image'] = null;
                    } 
                    else {
                        $data['image'] = $tag['image'] ?? null;
                    }
                    
                    $this->tagModel->update($id, $data);
                    
                    \Notification::success(LANG_ACTION_TAGS_EDIT_SUCCESS);
                    $this->redirect(ADMIN_URL . '/tags');
                    return;
                    
                } catch (\Exception $e) {
                    \Notification::error($e->getMessage());
                    $tag = $this->tagModel->getById($id);
                    $merged = array_merge($tag ?: ['id' => $id], $_POST);
                    $this->render('admin/tags/form', [
                        'tag' => $merged,
                        'data' => $merged,
                        'pageTitle' => $this->pageTitle
                    ]);
                    return;
                }
            }
            
            $this->render('admin/tags/form', [
                'tag' => $tag,
                'pageTitle' => $this->pageTitle
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_TAGS_EDIT_ERROR . $e->getMessage());
            $this->redirect(ADMIN_URL . '/tags');
        }
    }
}
