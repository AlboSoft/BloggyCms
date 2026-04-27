<?php

namespace tags\actions;

/**
* Действие создания нового тега в административной панели
* @package tags\actions
*/
class Create extends TagAction {
    
    /**
    * Метод выполнения создания тега
    * @return void
    */
    public function execute() {

        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_TAGS, ADMIN_URL . '/tags');
        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_CREATE);
        
        $form = new \TagForm($this->db);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($form);
                return;
            }
            
            $this->renderCreateForm($form);
            
        } catch (\Exception $e) {
            $this->handleError($e, $form);
        }
    }
    
    /**
    * Обрабатывает POST-запрос на создание тега
    * @param \TagForm $form Объект формы
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest($form) {

        $csrfToken = $_POST['simple_csrf'] ?? '';
        if ($csrfToken !== md5(session_id())) {
            throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_INVALID_CSRF);
        }
        
        $form->handleRequest($_POST, $_FILES);
        
        if (!$form->isValid()) {
            foreach ($form->getErrors() as $field => $errors) {
                foreach ($errors as $error) {
                    \Notification::error($error);
                }
            }
            $this->renderCreateForm($form);
            return;
        }
        
        $data = $form->getFieldsData();
        $name = trim($data['name'] ?? '');
        
        $existingTags = $this->tagModel->searchByName($name, 1);
        if (!empty($existingTags)) {
            throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_NAME_EXISTS);
        }
        
        $slug = $this->tagModel->createSlugFromName($name);
        
        $tagData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null
        ];

        $this->tagModel->create($tagData);
        
        \Notification::success(LANG_ACTION_TAGS_CREATE_SUCCESS);
        $this->redirect(ADMIN_URL . '/tags');
    }
    
    /**
    * Отображает форму создания тега
    * @param \TagForm $form Объект формы
    * @param array $currentData Данные для заполнения формы (при ошибке)
    * @return void
    */
    private function renderCreateForm($form, $currentData = []) {
        $this->render('admin/tags/form', [
            'form' => $form,
            'tag' => null,
            'currentData' => $currentData,
            'pageTitle' => LANG_ACTION_TAGS_CREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ошибку при создании тега
    * @param \Exception $e Исключение
    * @param \TagForm $form Объект формы
    * @return void
    */
    private function handleError($e, $form) {
        \Notification::error($e->getMessage());
        $this->renderCreateForm($form);
    }
}