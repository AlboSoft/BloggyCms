<?php

namespace users\actions;

/**
* Действие создания нового пользователя в административной панели
* @package users\actions
*/
class AdminCreate extends UserAction {
    
    /**
    * Метод выполнения создания пользователя
    * @return void
    */
    public function execute() {

        $this->addBreadcrumb(LANG_ACTION_USERS_ADMINCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_USERS_ADMINCREATE_BREADCRUMB_USERS, ADMIN_URL . '/users');
        $this->addBreadcrumb(LANG_ACTION_USERS_ADMINCREATE_BREADCRUMB_CREATE);
        
        try {
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
    * Обрабатывает POST-запрос на создание пользователя
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest() {

        $this->validateRequiredFields();
        $this->checkUniqueness();
        
        $userData = $this->prepareUserData();
        $userData = $this->handleAvatarUpload($userData);
        
        $userId = $this->userModel->create($userData);
        
        $this->saveCustomFields($userId);
        
        $this->assignUserGroups($userId);
        
        $this->assignAchievements($userId);
        
        \Notification::success(LANG_ACTION_USERS_ADMINCREATE_SUCCESS);
        $this->redirect(ADMIN_URL . '/users');
    }
    
    /**
    * Валидирует обязательные поля формы
     * 
     * @throws \Exception При ошибках валидации
     * @return void
     */
    private function validateRequiredFields() {
        if (empty($_POST['username'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINCREATE_ERROR_USERNAME_REQUIRED);
        }

        if (empty($_POST['email'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINCREATE_ERROR_EMAIL_REQUIRED);
        }

        if (empty($_POST['password'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINCREATE_ERROR_PASSWORD_REQUIRED);
        }

        if ($_POST['password'] !== $_POST['password_confirm']) {
            throw new \Exception(LANG_ACTION_USERS_ADMINCREATE_ERROR_PASSWORD_MISMATCH);
        }
    }
    
    /**
    * Проверяет уникальность имени пользователя и email 
    * @throws \Exception Если пользователь уже существует
    * @return void
    */
    private function checkUniqueness() {
        if ($this->userModel->getByUsername($_POST['username'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINCREATE_ERROR_USERNAME_EXISTS);
        }

        if ($this->userModel->getByEmail($_POST['email'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINCREATE_ERROR_EMAIL_EXISTS);
        }
    }
    
    /**
    * Подготавливает основные данные пользователя из POST
    * @return array Массив с данными пользователя
    */
    private function prepareUserData() {
        return [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'display_name' => $_POST['display_name'] ?? null,
            'bio' => $_POST['bio'] ?? null,
            'website' => $_POST['website'] ?? null,
            'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'active'
        ];
    }
    
    /**
    * Обрабатывает загрузку аватара пользователя
    * @param array $userData Данные пользователя
    * @return array Обновленные данные пользователя
    */
    private function handleAvatarUpload($userData) {
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $uploadDir = UPLOADS_PATH . '/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                $userData['avatar'] = $fileName;
            }
        }
        
        return $userData;
    }
    
    /**
    * Сохраняет пользовательские поля для нового пользователя
    * @param int $userId ID созданного пользователя
    * @return void
    */
    private function saveCustomFields($userId) {
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        $currentValues = [];
        $fieldManager = new \FieldManager($this->db);
        
        foreach ($customFields as $field) {
            try {
                $value = $fieldManager->processFieldValue(
                    $field, 
                    $_POST, 
                    $_FILES,
                    $currentValues
                );
                
                if ($value !== null) {
                    $this->fieldModel->saveFieldValue(
                        $field['id'],
                        'user',
                        $userId,
                        $value,
                        $field['type'],
                        $field['config']
                    );
                }
            } catch (\Exception $e) {
                \Notification::error(sprintf(LANG_ACTION_USERS_ADMINCREATE_ERROR_FIELD_SAVE, $field['name'], $e->getMessage()));
            }
        }
    }
    
    /**
    * Назначает пользователю группы
    * @param int $userId ID пользователя
    * @return void
    */
    private function assignUserGroups($userId) {
        if (!empty($_POST['groups'])) {
            $this->userModel->updateUserGroups($userId, $_POST['groups']);
        } else {
            $defaultGroup = $this->userModel->getDefaultGroup();
            if ($defaultGroup) {
                $this->userModel->updateUserGroups($userId, [$defaultGroup['id']]);
            }
        }
    }
    
    /**
    * Назначает пользователю ручные достижения
    * @param int $userId ID пользователя
    * @return void
    */
    private function assignAchievements($userId) {
        if (!empty($_POST['achievements'])) {
            foreach ($_POST['achievements'] as $achievementId) {
                $achievement = $this->userModel->getAchievementById($achievementId);
                if ($achievement && $achievement['type'] == 'manual') {
                    $this->userModel->assignAchievementToUser($userId, $achievementId);
                }
            }
        }
    }
    
    /**
    * Отображает форму создания пользователя
    * @return void
    */
    private function renderCreateForm() {
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        $this->render('admin/users/create', [
            'customFields' => $customFields,
            'pageTitle' => LANG_ACTION_USERS_ADMINCREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ошибку при создании пользователя
    * @param \Exception $e Исключение
    * @return void
    */
    private function handleError($e) {
        \Notification::error($e->getMessage());
        
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        $this->render('admin/users/create', [
            'user' => $_POST,
            'customFields' => $customFields,
            'pageTitle' => LANG_ACTION_USERS_ADMINCREATE_PAGE_TITLE
        ]);
    }
}