<?php

namespace users\actions\groups;

/**
* Действие управления членством пользователя в группах в административной панели
* @package users\actions\groups
*/
class AdminManageUserGroups extends AdminGroupAction {
    
    /**
    * Метод выполнения управления группами пользователя
    * @return void
    */
    public function execute() {
        try {

            $userId = $this->params['id'] ?? null;
            if (!$userId) {
                throw new \Exception(LANG_ACTION_USERS_ADMINMANAGEUSERGROUPS_NO_USER_ID);
            }

            $user = $this->userModel->getById($userId);
            if (!$user) {
                throw new \Exception(LANG_ACTION_USERS_ADMINMANAGEUSERGROUPS_USER_NOT_FOUND);
            }

            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINMANAGEUSERGROUPS_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINMANAGEUSERGROUPS_BREADCRUMB_USERS, ADMIN_URL . '/users');
            $this->addBreadcrumb(sprintf(LANG_ACTION_USERS_ADMINMANAGEUSERGROUPS_BREADCRUMB_EDIT, ($user['display_name'] ?? $user['username'])), ADMIN_URL . '/users/edit/' . $userId);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINMANAGEUSERGROUPS_BREADCRUMB_GROUPS);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($userId);
                return;
            }

            $this->renderGroupsForm($user);

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/users');
        }
    }
    
    /**
    * Обрабатывает POST-запрос на обновление групп пользователя 
    * @param int $userId ID пользователя
    * @return void
    */
    private function handlePostRequest($userId) {

        $groupIds = $_POST['groups'] ?? [];
        
        $this->userModel->updateUserGroups($userId, $groupIds);
        
        \Notification::success(LANG_ACTION_USERS_ADMINMANAGEUSERGROUPS_SUCCESS);
        $this->redirect(ADMIN_URL . '/users');
    }
    
    /**
    * Отображает форму управления группами пользователя
    * @param array $user Данные пользователя
    * @return void
    */
    private function renderGroupsForm($user) {
        $allGroups = $this->userModel->getAllGroups();
        $userGroups = $this->userModel->getUserGroups($user['id']);
        
        $this->render('admin/users/manage-groups', [
            'user' => $user,
            'allGroups' => $allGroups,
            'userGroups' => $userGroups,
            'pageTitle' => LANG_ACTION_USERS_ADMINMANAGEUSERGROUPS_PAGE_TITLE
        ]);
    }

}