<?php

namespace users\actions;

/**
* Действие редактирования пользователя в административной панели
* @package users\actions
*/
class AdminEdit extends UserAction {
    
    private $fieldManager;
    
    /**
    * Конструктор
    * @param \Database $db
    * @param array $params
    */
    public function __construct($db, $params = []) {
        parent::__construct($db, $params);
        $this->fieldManager = new \FieldManager($db);
    }
    
    /**
    * Метод выполнения редактирования пользователя
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_USERS_ADMINEDIT_NO_ID);
            $this->redirect(ADMIN_URL . '/users');
            return;
        }
        
        try {
            $user = $this->loadUser($id);
            
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINEDIT_BREADCRUMB_USERS, ADMIN_URL . '/users');
            $this->addBreadcrumb(sprintf(LANG_ACTION_USERS_ADMINEDIT_BREADCRUMB_EDIT, ($user['display_name'] ?: $user['username'])));
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id, $user);
                return;
            }

            $this->renderEditForm($user);

        } catch (\Exception $e) {
            $this->handleError($e, $id);
        }
    }
    
    /**
    * Загружает пользователя по ID и проверяет существование
    * @param int $id ID пользователя
    * @return array Данные пользователя
    * @throws \Exception Если пользователь не найден
    */
    private function loadUser($id) {
        $user = $this->userModel->getById($id);
        if (!$user) {
            throw new \Exception(LANG_ACTION_USERS_ADMINEDIT_USER_NOT_FOUND);
        }
        return $user;
    }
    
    /**
    * Обрабатывает POST-запрос на обновление пользователя
    * @param int $id ID пользователя
    * @param array $user Текущие данные пользователя
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest($id, $user) {
        $this->validateRequiredFields();
        $this->checkUniqueness($id);
        $userData = $this->prepareUserData($user);
        $userData = $this->handlePasswordChange($userData);
        $userData = $this->handleAvatarUpdate($userData, $user);
        $this->saveCustomFields($id, $user);
        $this->updateUserGroups($id);
        $this->updateUserAchievements($id);
        $this->userModel->update($id, $userData);
        \Notification::success(LANG_ACTION_USERS_ADMINEDIT_SUCCESS);
        $this->redirect(ADMIN_URL . '/users');
    }
    
    /**
    * Валидирует обязательные поля формы
    * @throws \Exception При ошибках валидации
    */
    private function validateRequiredFields() {
        if (empty($_POST['username'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINEDIT_ERROR_USERNAME_REQUIRED);
        }

        if (empty($_POST['email'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINEDIT_ERROR_EMAIL_REQUIRED);
        }
    }
    
    /**
    * Проверяет уникальность имени пользователя и email
    * @param int $id ID текущего пользователя для исключения
    * @throws \Exception Если данные уже заняты
    */
    private function checkUniqueness($id) {
        $existingUser = $this->userModel->getByUsername($_POST['username']);
        if ($existingUser && $existingUser['id'] != $id) {
            throw new \Exception(LANG_ACTION_USERS_ADMINEDIT_ERROR_USERNAME_EXISTS);
        }

        $existingUser = $this->userModel->getByEmail($_POST['email']);
        if ($existingUser && $existingUser['id'] != $id) {
            throw new \Exception(LANG_ACTION_USERS_ADMINEDIT_ERROR_EMAIL_EXISTS);
        }
    }
    
    /**
    * Подготавливает основные данные пользователя
    * @param array $user Текущие данные пользователя
    * @return array Данные для обновления
    */
    private function prepareUserData($user) {
        return [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'display_name' => $_POST['display_name'] ?? null,
            'website' => $_POST['website'] ?? null,
            'bio' => $_POST['bio'] ?? null,
            'role' => $_POST['role'] ?? 'user',
            'status' => $_POST['status'] ?? 'active'
        ];
    }
    
    /**
    * Обрабатывает смену пароля
    * @param array $userData Данные пользователя
    * @return array Обновленные данные
    * @throws \Exception Если пароли не совпадают
    */
    private function handlePasswordChange($userData) {
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['password_confirm']) {
                throw new \Exception(LANG_ACTION_USERS_ADMINEDIT_ERROR_PASSWORD_MISMATCH);
            }
            $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        return $userData;
    }
    
    /**
    * Обрабатывает обновление аватара (загрузку или удаление)
    * @param array $userData Данные пользователя
    * @param array $user Текущие данные пользователя
    * @return array Обновленные данные
    */
    private function handleAvatarUpdate($userData, $user) {
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $uploadDir = UPLOADS_PATH . '/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                $this->deleteOldAvatar($user);
                $userData['avatar'] = $fileName;
            }
        }
        
        if (isset($_POST['remove_avatar']) && $_POST['remove_avatar']) {
            $this->deleteOldAvatar($user);
            $userData['avatar'] = 'default.jpg';
        }
        
        return $userData;
    }
    
    /**
    * Удаляет старый аватар пользователя
    * @param array $user Данные пользователя
    */
    private function deleteOldAvatar($user) {
        if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') {
            $avatarPath = UPLOADS_PATH . '/avatars/' . $user['avatar'];
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }
    }
    
    /**
    * Сохраняет пользовательские поля с поддержкой файлов
    * @param int $userId ID пользователя
    * @param array $user Данные пользователя
    */
    private function saveCustomFields($userId, $user) {
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        $currentValues = $this->getCurrentFieldValues($userId);
        
        foreach ($customFields as $field) {
            try {
                $value = $this->fieldManager->processFieldValue(
                    $field, 
                    $_POST, 
                    $_FILES,
                    $currentValues
                );

                if ($value !== null) {
                    $this->fieldModel->saveFieldValue(
                        'user',
                        $userId,
                        $field['system_name'],
                        $value
                    );
                }
                
            } catch (\Exception $e) {
                \Notification::error(sprintf(LANG_ACTION_USERS_ADMINEDIT_ERROR_FIELD_SAVE, $field['name'], $e->getMessage()));
            }
        }
    }
    
    /**
    * Получает текущие значения всех полей для пользователя
    * @param int $userId ID пользователя
    * @return array Массив значений, где ключ - system_name поля
    */
    private function getCurrentFieldValues($userId) {
        $values = [];
        
        $results = $this->db->fetchAll("
            SELECT f.system_name, fv.value 
            FROM field_values fv
            JOIN fields f ON fv.field_id = f.id
            WHERE fv.entity_type = 'user' AND fv.entity_id = ?
        ", [$userId]);
        
        foreach ($results as $row) {
            $values[$row['system_name']] = $row['value'];
        }
        
        return $values;
    }
    
    /**
    * Обновляет группы пользователя
    * @param int $userId ID пользователя
    */
    private function updateUserGroups($userId) {
        if (isset($_POST['groups'])) {
            $this->userModel->updateUserGroups($userId, $_POST['groups']);
        } else {
            $this->userModel->updateUserGroups($userId, []);
        }
    }
    
    /**
    * Обновляет ручные достижения пользователя
    * @param int $userId ID пользователя
    */
    private function updateUserAchievements($userId) {
        if (!isset($_POST['achievements'])) {
            return;
        }
        
        $currentAchievements = $this->userModel->getUserUnlockedAchievements($userId);
        $currentAchievementIds = array_column($currentAchievements, 'id');
        
        foreach ($_POST['achievements'] as $achievementId) {
            if (!in_array($achievementId, $currentAchievementIds)) {
                $achievement = $this->userModel->getAchievementById($achievementId);
                if ($achievement && $achievement['type'] == 'manual') {
                    $this->userModel->assignAchievementToUser($userId, $achievementId);
                }
            }
        }
        
        foreach ($currentAchievements as $achievement) {
            if ($achievement['type'] == 'manual' && !in_array($achievement['id'], $_POST['achievements'])) {
                $this->userModel->removeAchievementFromUser($userId, $achievement['id']);
            }
        }
    }
    
    /**
    * Отображает форму редактирования пользователя
    * @param array $user Данные пользователя
    */
    private function renderEditForm($user) {
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        $fieldValues = $this->getCurrentFieldValues($user['id']);
        
        $this->render('admin/users/edit', [
            'user' => $user,
            'customFields' => $customFields,
            'fieldValues' => $fieldValues,
            'pageTitle' => LANG_ACTION_USERS_ADMINEDIT_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ошибку при редактировании
    * @param \Exception $e Исключение
    * @param int $id ID пользователя
    */
    private function handleError($e, $id) {
        \Notification::error($e->getMessage());
        
        $user = $this->userModel->getById($id);
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        $this->render('admin/users/edit', [
            'user' => array_merge($user, $_POST),
            'customFields' => $customFields,
            'pageTitle' => LANG_ACTION_USERS_ADMINEDIT_PAGE_TITLE
        ]);
    }
}