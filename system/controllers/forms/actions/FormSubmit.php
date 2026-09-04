<?php

namespace forms\actions;

/**
* Действие отправки формы
*/
class FormSubmit extends FormAction {
    
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        
        if (!$slug) {
            $this->jsonResponse([
                'success' => false,
                'message' => LANG_ACTION_FORMS_FORMSUBMIT_FORM_NOT_SPECIFIED
            ]);
            return;
        }
        
        $form = $this->formModel->getBySlug($slug);
        if (!$form || $form['status'] !== 'active') {
            $this->jsonResponse([
                'success' => false,
                'message' => LANG_ACTION_FORMS_FORMSUBMIT_FORM_NOT_FOUND
            ]);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse([
                'success' => false,
                'message' => LANG_ACTION_FORMS_FORMSUBMIT_INVALID_METHOD
            ]);
            return;
        }
        
        try {
            $settings = $form['settings'] ?? [];
            $postData = $_POST;
            $filesData = $_FILES;
            
            unset($postData['form_id'], $postData['form_slug'], $postData['csrf_token']);
            
            $csrfEnabled = $settings['csrf_protection'] ?? true;
            if ($csrfEnabled) {
                $token = $_POST['csrf_token'] ?? '';
                if (!$this->verifyCsrfToken($token, $slug)) {
                    throw new \Exception(LANG_ACTION_FORMS_FORMSUBMIT_CSRF_ERROR);
                }
            }
            
            $captchaEnabled = $settings['captcha_enabled'] ?? false;
            if ($captchaEnabled) {
                if (!$this->verifyCaptcha($settings)) {
                    throw new \Exception(LANG_ACTION_FORMS_FORMSUBMIT_CAPTCHA_FAILED);
                }
            }
            
            if (!empty($settings['limit_submissions'])) {
                if (!$this->checkSubmissionLimits($form['id'], $settings)) {
                    throw new \Exception(LANG_ACTION_FORMS_FORMSUBMIT_LIMIT_EXCEEDED);
                }
            }
            
            if (!empty($settings['spam_protection'])) {
                if ($this->checkSpamKeywords($postData, $settings)) {
                    $submissionId = $this->formModel->saveSubmission($form['id'], $postData, $filesData);
                    $this->formModel->updateSubmissionStatus($submissionId, 'spam');
                    
                    $this->jsonResponse([
                        'success' => true,
                        'message' => $form['success_message'] ?? LANG_ACTION_FORMS_FORMSUBMIT_DEFAULT_SUCCESS,
                        'submission_id' => $submissionId
                    ]);
                    return;
                }
            }
            
            $errors = $this->validateSubmissionSecure($form, $postData, $filesData);
            
            if (!empty($errors)) {
                $errorMessage = is_array($errors) ? implode("\n", $errors) : $errors;
                throw new \Exception($errorMessage);
            }
            
            $submissionId = null;
            if (!empty($settings['store_submissions'])) {
                $submissionId = $this->formModel->saveSubmission($form['id'], $postData, $filesData);
            }
            
            if (!empty($form['notifications'])) {
                \FormRenderer::sendNotifications($form, $postData, $submissionId);
            }
            
            if (!empty($form['actions'])) {
                \FormRenderer::executeActions($form, $postData, $submissionId);
            }
            
            $successMessage = $form['success_message'] ?? LANG_ACTION_FORMS_FORMSUBMIT_DEFAULT_SUCCESS;
            
            $redirectUrl = null;
            foreach ($form['actions'] ?? [] as $action) {
                if ($action['enabled'] && $action['type'] === 'redirect') {
                    $redirectUrl = $action['url'] ?? null;
                    break;
                }
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => $successMessage,
                'submission_id' => $submissionId,
                'redirect' => $redirectUrl
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function validateSubmissionSecure($form, $data, $files) {
        $errors = [];
        $structure = $form['structure'] ?? [];
        $fieldLabels = [];
        
        foreach ($structure as $field) {
            if (!empty($field['name'])) {
                $fieldLabels[$field['name']] = $field['label'] ?? $field['name'];
            }
        }
        
        foreach ($structure as $field) {
            $fieldName = $field['name'] ?? '';
            $fieldType = $field['type'] ?? '';
            $fieldLabel = $field['label'] ?? $fieldName;
            $required = !empty($field['required']);
            $validation = $field['validation'] ?? [];
            
            if ($fieldType === 'submit' || $fieldType === 'hidden') {
                continue;
            }
            
            $value = $data[$fieldName] ?? '';
            $file = $files[$fieldName] ?? null;
            
            if ($required) {
                if ($fieldType === 'file') {
                    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
                        $errors[] = "Поле '{$fieldLabel}' обязательно для заполнения";
                        continue;
                    }
                } elseif (empty($value) && $value !== '0') {
                    $errors[] = "Поле '{$fieldLabel}' обязательно для заполнения";
                    continue;
                }
            }
            
            if (!$required && empty($value) && $value !== '0' && (!$file || $file['error'] === UPLOAD_ERR_NO_FILE)) {
                continue;
            }
            
            foreach ($validation as $rule => $params) {
                $error = $this->validateRule($rule, $params, $value, $fieldLabel, $fieldType);
                if ($error) {
                    $errors[] = $error;
                    break;
                }
            }
            
            $typeError = $this->validateFieldType($fieldType, $value, $fieldLabel);
            if ($typeError) {
                $errors[] = $typeError;
            }
            
            if ($fieldType === 'file' && $file && $file['error'] === UPLOAD_ERR_OK) {
                $fileError = $this->validateFileSecure($file, $field);
                if ($fileError) {
                    $errors[] = $fileError;
                }
            }
        }
        
        return $errors;
    }

    private function validateFileSecure($file, $field) {
        $maxSize = $field['max_size'] ?? 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 1);
            return "Размер файла не должен превышать {$maxSizeMB}MB";
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            $realMimeType = mime_content_type($file['tmp_name']);
        }

        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'application/zip', 'application/x-rar-compressed'
        ];

        if (!empty($field['allowed_types'])) {
            $allowedTypes = array_map('strtolower', $field['allowed_types']);
            $allowedMimeTypes = array_intersect($allowedMimeTypes, $allowedTypes);
        }

        if (!in_array($realMimeType, $allowedMimeTypes)) {
            return "Недопустимый тип файла. Разрешены: " . implode(', ', $allowedMimeTypes);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
        
        if (empty($extension) || !in_array($extension, $allowedExtensions)) {
            return "Недопустимое расширение файла. Разрешены: " . implode(', ', $allowedExtensions);
        }

        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'cgi', 'pl', 'sh'];
        if (in_array($extension, $dangerousExtensions)) {
            return "Загрузка исполняемых файлов запрещена";
        }

        if (preg_match('/\.[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/', $file['name'])) {
            return "Файл имеет подозрительное двойное расширение";
        }

        if (strpos($realMimeType, 'image/') === 0) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return "Файл не является корректным изображением";
            }
            
            $content = file_get_contents($file['tmp_name']);
            if (preg_match('/<\?php|<\?=/i', $content)) {
                return "Обнаружен подозрительный код в файле";
            }
        }

        $content = file_get_contents($file['tmp_name']);
        $dangerousPatterns = [
            '/<\?php/i', '/<\?=/i', '/<\?xml/i',
            '/eval\s*\(/i', '/base64_decode\s*\(/i',
            '/system\s*\(/i', '/exec\s*\(/i',
            '/passthru\s*\(/i', '/shell_exec\s*\(/i'
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return "Файл содержит потенциально опасный код";
            }
        }

        return null;
    }
    
    /**
    * Проверка CSRF-токена
    */
    private function verifyCsrfToken($token, $formSlug) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $formName = 'form_' . $formSlug;
        
        if (!isset($_SESSION['csrf_tokens'][$formName])) {
            return false;
        }
        
        $storedToken = $_SESSION['csrf_tokens'][$formName];
        
        if (time() - $storedToken['created_at'] > 3600) {
            unset($_SESSION['csrf_tokens'][$formName]);
            return false;
        }
        
        if (!hash_equals($storedToken['token'], $token)) {
            return false;
        }
        
        unset($_SESSION['csrf_tokens'][$formName]);
        
        return true;
    }
    
    /**
    * Проверка капчи
    */
    private function verifyCaptcha($settings) {
        $captchaAnswer = trim($_POST['captcha_answer'] ?? '');
        $captchaHash = $_POST['captcha_hash'] ?? '';
        
        if (empty($captchaAnswer) || empty($captchaHash)) {
            return false;
        }
        
        $secretKey = $settings['captcha_secret'] ?? 'bloggy_cms_captcha';
        
        $decrypted = openssl_decrypt(
            $captchaHash,
            'AES-128-ECB',
            $secretKey,
            0
        );
        
        if ($decrypted === false || $decrypted === '') {
            return false;
        }
        
        $decrypted = trim($decrypted);
        $captchaType = $settings['captcha_type'] ?? 'math';
        
        if ($captchaType === 'math') {
            return intval($captchaAnswer) === intval($decrypted);
        }
        
        if ($captchaType === 'image') {
            return strtolower($captchaAnswer) === strtolower($decrypted);
        }
        
        return strtolower($captchaAnswer) === strtolower($decrypted);
    }
    
    /**
    * Проверка лимитов отправок
    */
    private function checkSubmissionLimits($formId, $settings) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $maxPerDay = intval($settings['max_submissions_per_day'] ?? 0);
        if ($maxPerDay > 0) {
            $countToday = $this->formModel->getSubmissionsCountToday($formId, $ip);
            if ($countToday >= $maxPerDay) {
                return false;
            }
        }
        
        $maxPerIp = intval($settings['max_submissions_per_ip'] ?? 0);
        if ($maxPerIp > 0) {
            $countByIp = $this->formModel->getSubmissionsCountByIp($formId, $ip);
            if ($countByIp >= $maxPerIp) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
    * Проверка на спам-слова
    */
    private function checkSpamKeywords($data, $settings) {
        if (empty($settings['spam_protection']) || empty($settings['spam_keywords'])) {
            return false;
        }
        
        $spamWords = array_filter(array_map('trim', explode("\n", $settings['spam_keywords'])));
        
        if (empty($spamWords)) {
            return false;
        }
        
        foreach ($data as $fieldName => $value) {
            if (in_array($fieldName, ['form_id', 'form_slug', 'csrf_token', 'captcha_answer', 'captcha_hash'])) {
                continue;
            }
            
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            
            $value = mb_strtolower(trim($value));
            
            foreach ($spamWords as $spamWord) {
                $spamWord = mb_strtolower(trim($spamWord));
                
                if (empty($spamWord)) {
                    continue;
                }
                
                if (mb_strpos($value, $spamWord) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
}