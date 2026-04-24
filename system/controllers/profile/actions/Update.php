<?php

namespace profile\actions;

/**
* Действие обновления данных профиля пользователя 
* @package profile\actions
*/
class Update extends ProfileAction {
    
    /**
    * Метод выполнения обновления профиля
    * @return void
    */
    public function execute() {

        $this->checkAuthentication();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_INVALID_METHOD, '/profile/edit');
            return;
        }
        
        if (!$this->validateCsrfToken()) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_INVALID_CSRF, '/profile/edit');
            return;
        }
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        
        if (!$user) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_USER_NOT_FOUND, '/profile/edit');
            return;
        }

        $updateData = $this->prepareUpdateData();
        
        if ($this->processUpdate($user, $updateData)) {
            $this->redirectWithSuccess('/profile/' . $user['username']);
        }
    }
    
    /**
    * Подготавливает данные из POST-запроса для обновления
    * @return array Массив данных для обновления (пустые поля отфильтрованы)
    */
    private function prepareUpdateData() {
        $data = [
            'display_name' => trim($_POST['display_name'] ?? ''),
            'email' => $this->validateEmail($_POST['email'] ?? ''),
            'website' => $this->validateWebsite($_POST['website'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($_FILES['avatar']['tmp_name'])) {
            $avatarResult = $this->handleAvatarUpload();
            if ($avatarResult) {
                $data['avatar'] = $avatarResult;
            }
        }

        return array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
    }
    
    /**
    * Выполняет обновление данных пользователя
    * @param array $user Текущие данные пользователя
    * @param array $updateData Данные для обновления
    * @return bool true при успешном обновлении
    */
    private function processUpdate($user, $updateData) {
        if (!empty($_POST['new_password'])) {
            if (!$this->userModel->updatePassword(
                $user['id'],
                $_POST['current_password'] ?? '',
                $_POST['new_password']
            )) {
                $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_INVALID_PASSWORD, '/profile/edit');
                return false;
            }
        }

        if (!empty($updateData) && !$this->userModel->update($user['id'], $updateData)) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_SAVE_ERROR, '/profile/edit');
            return false;
        }

        if (!$this->saveCustomFields($user['id'])) {
            return false;
        }

        $this->updateSession($updateData);
        
        return true;
    }
    

    /**
    * Сохраняет значения пользовательских полей
    * @param int $userId ID пользователя
    * @return bool Результат сохранения
    */
    private function saveCustomFields($userId) {
        try {
            $customFields = $this->fieldModel->getActiveByEntityType('user');
            $currentValues = $this->fieldModel->getFieldValues($userId, 'user');
            $fieldManager = new \FieldManager($this->db);
            
            foreach ($customFields as $field) {

                $isRequired = (bool)$field['is_required'];
                
                if ($isRequired) {
                    $postKey = 'field_' . $field['system_name'];
                    $value = $_POST[$postKey] ?? null;
                    
                    if (empty($value) && $value !== '0') {
                        $this->redirectWithError(sprintf(LANG_ACTION_PROFILE_UPDATE_FIELD_REQUIRED, $field['name']), '/profile/edit');
                        return false;
                    }
                }
            }
            
            foreach ($customFields as $field) {
                $processedValue = $fieldManager->processFieldValue(
                    $field,
                    $_POST,
                    $_FILES,
                    $currentValues
                );
                
                if ($processedValue !== null) {
                    $result = $this->fieldModel->saveFieldValue(
                        'user',
                        $userId,
                        $field['system_name'],
                        $processedValue
                    );
                    
                    if (!$result) {
                        throw new Exception(sprintf(LANG_ACTION_PROFILE_UPDATE_FIELD_SAVE_ERROR, $field['name']));
                    }
                } elseif ($field['type'] === 'flag') {
                    $this->fieldModel->saveFieldValue(
                        'user',
                        $userId,
                        $field['system_name'],
                        '0'
                    );
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->redirectWithError(sprintf(LANG_ACTION_PROFILE_UPDATE_FIELDS_ERROR, $e->getMessage()), '/profile/edit');
            return false;
        }
    }
    
    /**
    * Обрабатывает загрузку нового аватара пользователя
    * @return string|null Имя загруженного файла или null при ошибке
    */
    private function handleAvatarUpload() {
        $file = $_FILES['avatar'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => LANG_ACTION_PROFILE_UPDATE_ERROR_INI_SIZE,
                UPLOAD_ERR_FORM_SIZE => LANG_ACTION_PROFILE_UPDATE_ERROR_FORM_SIZE,
                UPLOAD_ERR_PARTIAL => LANG_ACTION_PROFILE_UPDATE_ERROR_PARTIAL,
                UPLOAD_ERR_NO_FILE => LANG_ACTION_PROFILE_UPDATE_ERROR_NO_FILE,
                UPLOAD_ERR_NO_TMP_DIR => LANG_ACTION_PROFILE_UPDATE_ERROR_NO_TMP_DIR,
                UPLOAD_ERR_CANT_WRITE => LANG_ACTION_PROFILE_UPDATE_ERROR_CANT_WRITE,
                UPLOAD_ERR_EXTENSION => LANG_ACTION_PROFILE_UPDATE_ERROR_EXTENSION
            ];
            $errorMsg = $errorMessages[$file['error']] ?? LANG_ACTION_PROFILE_UPDATE_ERROR_UNKNOWN;
            $this->redirectWithError(sprintf(LANG_ACTION_PROFILE_UPDATE_AVATAR_ERROR, $errorMsg), '/profile/edit');
            return null;
        }

        $uploadDir = UPLOADS_PATH . '/avatars/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_AVATAR_TYPE_ERROR, '/profile/edit');
            return null;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_AVATAR_SIZE_ERROR, '/profile/edit');
            return null;
        }

        $currentUser = $this->userModel->getById($_SESSION['user_id']);
        if (!empty($currentUser['avatar']) && $currentUser['avatar'] !== 'default.jpg') {
            $oldAvatarPath = $uploadDir . $currentUser['avatar'];
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_AVATAR_UPLOAD_ERROR, '/profile/edit');
            return null;
        }

        return $filename;
    }
    
    /**
    * Обновляет данные пользователя в сессии 
    * @param array $data Обновленные данные
    * @return void
    */
    private function updateSession($data) {
        if (isset($data['display_name'])) {
            $_SESSION['display_name'] = $data['display_name'];
        }
        if (isset($data['avatar'])) {
            $_SESSION['avatar'] = $data['avatar'];
        }
    }
    
    /**
    * Валидирует email-адрес
    * @param string $email Email для проверки
    * @return string|null Валидный email или null
    */
    private function validateEmail($email) {
        $email = trim($email);
        
        if (empty($email)) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_EMAIL_EMPTY, '/profile/edit');
            return null;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_EMAIL_INVALID, '/profile/edit');
            return null;
        }
        
        $existingUser = $this->userModel->getByEmail($email);
        if ($existingUser && $existingUser['id'] != $_SESSION['user_id']) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_EMAIL_EXISTS, '/profile/edit');
            return null;
        }
        
        return $email;
    }
    
    /**
    * Валидирует URL веб-сайта
    * @param string $website URL для проверки
    * @return string|null Валидный URL или null
    */
    private function validateWebsite($website) {
        $website = trim($website);
        
        if (empty($website)) {
            return null;
        }
        
        if (!preg_match('/^https?:\/\//', $website)) {
            $website = 'http://' . $website;
        }
        
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            $this->redirectWithError(LANG_ACTION_PROFILE_UPDATE_WEBSITE_INVALID, '/profile/edit');
            return null;
        }
        
        return $website;
    }
}