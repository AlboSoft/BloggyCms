<?php

namespace tags\actions;

/**
* Действие редактирования тега в административной панели
* @package tags\actions
*/
class Edit extends TagAction {
    
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
        
        try {
            $tag = $this->tagModel->getById($id);
            if (!$tag) {
                \Notification::error(LANG_ACTION_TAGS_EDIT_TAG_NOT_FOUND);
                $this->redirect(ADMIN_URL . '/tags');
                return;
            }
            
            $this->addBreadcrumb(LANG_ACTION_TAGS_EDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_TAGS_EDIT_BREADCRUMB_TAGS, ADMIN_URL . '/tags');
            $this->addBreadcrumb(sprintf(LANG_ACTION_TAGS_EDIT_BREADCRUMB_EDIT, $tag['name']));
            
            $form = new \TagForm($this->db);
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id, $tag, $form);
                return;
            }
            
            $this->renderEditForm($tag, $form);
            
        } catch (\Exception $e) {
            $this->handleError($e, $id);
        }
    }
    
    /**
    * Обрабатывает POST-запрос на обновление тега
    * @param int $id ID тега
    * @param array $tag Текущие данные тега
    * @param \TagForm $form Объект формы
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest($id, $tag, $form) {

        $csrfToken = $_POST['simple_csrf'] ?? '';
        if ($csrfToken !== md5(session_id())) {
            throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_INVALID_CSRF);
        }
        
        $form->handleRequest($_POST, $_FILES);
        
        if (!$form->isValid()) {
            foreach ($form->getErrors() as $field => $errors) {
                foreach ($errors as $error) {
                    \Notification::error($error);
                }
            }
            $this->renderEditForm($tag, $form);
            return;
        }
        
        $data = $form->getFieldsData();
        $name = trim($data['name'] ?? '');
        
        $existingTags = $this->tagModel->searchByName($name, 1);
        if (!empty($existingTags) && $existingTags[0]['id'] != $id) {
            throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_NAME_EXISTS);
        }
        
        $slug = $this->tagModel->createSlugFromName($name);
        
        $tagData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? null
        ];
        
        if (isset($data['image'])) {
            $tagData['image'] = $data['image'];
        } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
            $tagData['image'] = null;
        } else {
            $tagData['image'] = $tag['image'] ?? null;
        }
        
        $this->tagModel->update($id, $tagData);
        
        \Notification::success(LANG_ACTION_TAGS_EDIT_SUCCESS);
        $this->redirect(ADMIN_URL . '/tags');
    }
    
    /**
    * Отображает форму редактирования тега
    * @param array $tag Данные тега
    * @param \TagForm $form Объект формы
    * @return void
    */
    private function renderEditForm($tag, $form) {
        
        $currentData = $form->populateFromDb($tag);
        
        $this->render('admin/tags/form', [
            'form' => $form,
            'tag' => $tag,
            'currentData' => $currentData,
            'pageTitle' => LANG_ACTION_TAGS_EDIT_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ошибку при редактировании тега
    * @param \Exception $e Исключение
    * @param int $id ID тега
    * @return void
    */
    private function handleError($e, $id) {
        \Notification::error($e->getMessage());
        
        $tag = $this->tagModel->getById($id);
        if ($tag) {
            $form = new \TagForm($this->db);
            $currentData = $form->populateFromDb($tag);
            $this->render('admin/tags/form', [
                'form' => $form,
                'tag' => $tag,
                'currentData' => $currentData,
                'pageTitle' => LANG_ACTION_TAGS_EDIT_PAGE_TITLE
            ]);
        } else {
            $this->redirect(ADMIN_URL . '/tags');
        }
    }
}