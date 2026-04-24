<?php

namespace users\actions\groups;

/**
* Действие отображения списка всех групп пользователей в административной панели
* @package users\actions\groups
*/
class AdminGroupIndex extends AdminGroupAction {
    
    /**
    * Метод выполнения отображения списка групп
    * @return void
    */
    public function execute() {
        try {

            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINGROUPINDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINGROUPINDEX_BREADCRUMB_GROUPS, ADMIN_URL . '/user-groups');

            $groups = $this->userModel->getAllGroups();
            
            $this->render('admin/user-groups/index', [
                'groups' => $groups,
                'pageTitle' => LANG_ACTION_USERS_ADMINGROUPINDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_USERS_ADMINGROUPINDEX_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }

}