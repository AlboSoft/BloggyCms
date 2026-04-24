<?php

namespace users\actions\groups;

/**
* Действие создания новой группы пользователей в административной панели
* @package users\actions\groups
*/
class AdminGroupCreate extends AdminGroupAction {
    
    /**
    * Метод выполнения создания группы
    * @return void
    */
    public function execute() {
        try {

            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINGROUPCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINGROUPCREATE_BREADCRUMB_GROUPS, ADMIN_URL . '/user-groups');
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINGROUPCREATE_BREADCRUMB_CREATE);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest();
                return;
            }

            $this->renderCreateForm();

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
    * Обрабатывает POST-запрос на создание группы
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest() {
        if (empty($_POST['name'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINGROUPCREATE_ERROR_NAME_REQUIRED);
        }

        $groupData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        $groupId = $this->userModel->createGroup($groupData);

        \Notification::success(LANG_ACTION_USERS_ADMINGROUPCREATE_SUCCESS);
        $this->redirect(ADMIN_URL . '/user-groups');
    }
    
    /**
    * Отображает форму создания группы
    * @return void
    */
    private function renderCreateForm() {
        $this->render('admin/user-groups/create', [
            'pageTitle' => LANG_ACTION_USERS_ADMINGROUPCREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ошибку при создании группы 
    * @param \Exception $e Исключение
    * @return void
    */
    private function handleError($e) {
        \Notification::error($e->getMessage());
        
        $this->render('admin/user-groups/create', [
            'group' => $_POST,
            'pageTitle' => LANG_ACTION_USERS_ADMINGROUPCREATE_PAGE_TITLE
        ]);
    }
}