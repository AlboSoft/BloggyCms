<?php

namespace users\actions\groups;

/**
* Действие редактирования группы пользователей в административной панели
* @package users\actions\groups
*/
class AdminGroupEdit extends AdminGroupAction {
    
    /**
    * Метод выполнения редактирования группы
    * @return void
    */
    public function execute() {
        try {

            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception(LANG_ACTION_USERS_ADMINGROUPEDIT_NO_ID);
            }

            $group = $this->userModel->getGroupById($id);
            if (!$group) {
                throw new \Exception(LANG_ACTION_USERS_ADMINGROUPEDIT_NOT_FOUND);
            }

            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINGROUPEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINGROUPEDIT_BREADCRUMB_GROUPS, ADMIN_URL . '/user-groups');
            $this->addBreadcrumb(sprintf(LANG_ACTION_USERS_ADMINGROUPEDIT_BREADCRUMB_EDIT, $group['name']));

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id);
                return;
            }

            $this->renderEditForm($group);

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/user-groups');
        }
    }
    
    /**
    * Обрабатывает POST-запрос на обновление группы
    * @param int $id ID группы
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest($id) {

        if (empty($_POST['name'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINGROUPEDIT_ERROR_NAME_REQUIRED);
        }

        $groupData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        $this->userModel->updateGroup($id, $groupData);

        \Notification::success(LANG_ACTION_USERS_ADMINGROUPEDIT_SUCCESS);
        $this->redirect(ADMIN_URL . '/user-groups');
    }
    
    /**
    * Отображает форму редактирования группы
    * @param array $group Данные группы
    * @return void
    */
    private function renderEditForm($group) {
        $this->render('admin/user-groups/edit', [
            'group' => $group,
            'pageTitle' => LANG_ACTION_USERS_ADMINGROUPEDIT_PAGE_TITLE
        ]);
    }
}