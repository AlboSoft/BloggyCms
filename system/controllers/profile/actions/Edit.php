<?php

namespace profile\actions;

class Edit extends ProfileAction {
    
    public function execute() {
        $this->checkAuthentication();
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        
        if (!$user) {
            $this->redirectWithError(LANG_ACTION_PROFILE_EDIT_USER_NOT_FOUND, '/');
            return;
        }

        $customFieldValues = $this->fieldModel->getFieldValues($user['id'], 'user');
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        $fieldsWithValues = [];
        foreach ($customFields as $field) {
            $field['value'] = $customFieldValues[$field['system_name']] ?? null;
            $fieldsWithValues[] = $field;
        }
        
        $this->addBreadcrumb(LANG_ACTION_PROFILE_EDIT_BREADCRUMB_HOME, BASE_URL);
        $this->addBreadcrumb(LANG_ACTION_PROFILE_EDIT_BREADCRUMB_PROFILE, BASE_URL . '/profile/' . $user['username']);
        $this->addBreadcrumb(LANG_ACTION_PROFILE_EDIT_BREADCRUMB_EDIT);
        $this->setPageTitle(LANG_ACTION_PROFILE_EDIT_PAGE_TITLE);
        
        $this->render('front/profile/edit', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken(),
            'customFields' => $fieldsWithValues,
            'fieldManager' => new \FieldManager($this->db)
        ]);
    }
}