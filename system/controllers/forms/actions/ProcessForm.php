<?php

namespace forms\actions;

/**
* Действие обработки формы
*/
class ProcessForm extends FormAction {
    
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        if (!$slug) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => LANG_ACTION_FORMS_PROCESSFORM_FORM_NOT_SPECIFIED
                ]);
            } else {
                \Notification::error(LANG_ACTION_FORMS_PROCESSFORM_FORM_NOT_SPECIFIED);
                $this->redirect(BASE_URL);
            }
            return;
        }
        
        $form = $this->formModel->getBySlug($slug);
        if (!$form || $form['status'] !== 'active') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => LANG_ACTION_FORMS_PROCESSFORM_FORM_NOT_FOUND
                ]);
            } else {
                \Notification::error(LANG_ACTION_FORMS_PROCESSFORM_FORM_NOT_FOUND);
                $this->redirect(BASE_URL);
            }
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => LANG_ACTION_FORMS_PROCESSFORM_INVALID_METHOD
                ]);
            } else {
                \Notification::error(LANG_ACTION_FORMS_PROCESSFORM_INVALID_METHOD);
                $this->redirect(BASE_URL . '/form/' . $slug);
            }
            return;
        }
        
        try {
            $postData = $_POST;
            $filesData = $_FILES;
            
            unset($postData['csrf_token']);
            
            $settings = $form['settings'] ?? [];
            $csrfEnabled = $settings['csrf_protection'] ?? true;
            
            if ($csrfEnabled) {
                $token = $_POST['csrf_token'] ?? '';
                if (!$this->verifyCsrfToken($token, $slug)) {
                    throw new \Exception(LANG_ACTION_FORMS_PROCESSFORM_CSRF_ERROR);
                }
            }
            
            $captchaEnabled = $settings['captcha_enabled'] ?? false;
            if ($captchaEnabled) {
                if (!$this->verifyCaptcha($settings)) {
                    throw new \Exception(LANG_ACTION_FORMS_PROCESSFORM_CAPTCHA_FAILED);
                }
            }

            if (!empty($settings['spam_protection'])) {
                if ($this->checkSpamKeywords($postData, $settings)) {
                    $submissionId = $this->formModel->saveSubmission($form['id'], $postData, $filesData);
                    
                    $this->formModel->updateSubmissionStatus($submissionId, 'spam');
                    
                    $this->jsonResponse([
                        'success' => true,
                        'message' => $form['success_message'] ?? LANG_ACTION_FORMS_PROCESSFORM_DEFAULT_SUCCESS,
                        'submission_id' => $submissionId
                    ]);
                    return;
                }
            }
            
            if (!empty($settings['limit_submissions'])) {
                if (!$this->checkSubmissionLimits($form['id'], $settings)) {
                    throw new \Exception(LANG_ACTION_FORMS_PROCESSFORM_LIMIT_EXCEEDED);
                }
            }
            
            $errors = $this->validateSubmission($form, $postData, $filesData);
            
            if (!empty($errors)) {
                $errorMessage = is_array($errors) ? implode("\n", array_values($errors)) : $errors;
                throw new \Exception($errorMessage);
            }
            
            $submissionId = null;
            
            $storeSubmissions = $settings['store_submissions'] ?? true;
            if ($storeSubmissions) {
                $submissionId = $this->formModel->saveSubmission($form['id'], $postData, $filesData);
            }
            
            if (!empty($form['notifications'])) {
                $this->sendNotifications($form, $postData, $submissionId);
            }
            
            if (!empty($form['actions'])) {
                $this->executeActions($form, $postData, $submissionId);
            }
            
            $successMessage = $form['success_message'] ?? LANG_ACTION_FORMS_PROCESSFORM_DEFAULT_SUCCESS;
            
            $redirectUrl = null;
            foreach ($form['actions'] ?? [] as $action) {
                if ($action['enabled'] && $action['type'] === 'redirect') {
                    $redirectUrl = $action['url'] ?? null;
                    break;
                }
            }
            
            $isAjaxRequest = $this->isAjaxRequest();
            $ajaxEnabled = $form['settings']['ajax_enabled'] ?? true;
            
            if ($isAjaxRequest || $ajaxEnabled) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $successMessage,
                    'submission_id' => $submissionId,
                    'redirect' => $redirectUrl
                ]);
                return;
            }
            
            \Notification::success($successMessage);
            
            if ($redirectUrl) {
                $this->redirect($redirectUrl);
            } else {
                $_SESSION['form_success_' . $form['id']] = $successMessage;
                $this->redirect(BASE_URL . '/form/' . $slug);
            }
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            $isAjaxRequest = $this->isAjaxRequest();
            $ajaxEnabled = $form['settings']['ajax_enabled'] ?? true;
            
            if ($isAjaxRequest || $ajaxEnabled) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            } else {
                \Notification::error($errorMessage);
                $this->redirect(BASE_URL . '/form/' . $slug);
            }
        }
    }
    
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
    * Проверка на спам-слова
    * @param array $data Данные формы
    * @param array $settings Настройки формы
    * @return bool true если найдены спам-слова
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
    
    private function validateSubmission($form, $data, $files) {
        $errors = [];
        $structure = $form['structure'] ?? [];
        
        foreach ($structure as $field) {
            $fieldName = $field['name'] ?? '';
            $fieldType = $field['type'] ?? '';
            $fieldLabel = $field['label'] ?? $fieldName;
            $required = !empty($field['required']);
            
            if ($fieldType === 'submit' || $fieldType === 'hidden') {
                continue;
            }
            
            $value = $data[$fieldName] ?? '';
            $file = $files[$fieldName] ?? null;
            
            if ($required) {
                if ($fieldType === 'file') {
                    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
                        $errors[$fieldName] = sprintf(LANG_ACTION_FORMS_PROCESSFORM_FIELD_REQUIRED, $fieldLabel);
                        continue;
                    }
                } elseif (empty($value) && $value !== '0') {
                    $errors[$fieldName] = sprintf(LANG_ACTION_FORMS_PROCESSFORM_FIELD_REQUIRED, $fieldLabel);
                    continue;
                }
            }
            
            if (!$required && empty($value) && $value !== '0' && (!$file || $file['error'] === UPLOAD_ERR_NO_FILE)) {
                continue;
            }
            
            if (!empty($value)) {
                switch ($fieldType) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$fieldName] = sprintf(LANG_ACTION_FORMS_PROCESSFORM_FIELD_EMAIL, $fieldLabel);
                        }
                        break;
                    case 'number':
                        if (!is_numeric($value)) {
                            $errors[$fieldName] = sprintf(LANG_ACTION_FORMS_PROCESSFORM_FIELD_NUMBER, $fieldLabel);
                        }
                        break;
                    case 'tel':
                        if (!preg_match('/^[\d\s\-\+\(\)]+$/', $value)) {
                            $errors[$fieldName] = sprintf(LANG_ACTION_FORMS_PROCESSFORM_FIELD_TEL, $fieldLabel);
                        }
                        break;
                }
            }
            
            if ($fieldType === 'file' && $file && $file['error'] === UPLOAD_ERR_OK) {
                $maxSize = 5 * 1024 * 1024;
                if ($file['size'] > $maxSize) {
                    $errors[$fieldName] = LANG_ACTION_FORMS_PROCESSFORM_FILE_TOO_LARGE;
                }
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                if (!in_array($file['type'], $allowedTypes)) {
                    $errors[$fieldName] = LANG_ACTION_FORMS_PROCESSFORM_FILE_TYPE_ERROR;
                }
            }
        }
        
        return $errors;
    }
    
    private function sendNotifications($form, $data, $submissionId) {
        $notifications = $form['notifications'] ?? [];
        
        foreach ($notifications as $notification) {
            if (empty($notification['enabled'])) {
                continue;
            }
            
            $to = $this->parseTemplate($notification['to'] ?? '', $data);
            $subject = $this->parseTemplate($notification['subject'] ?? LANG_ACTION_FORMS_PROCESSFORM_NOTIFICATION_SUBJECT, $data);
            $message = $this->parseTemplate($notification['message'] ?? '', $data);
            $from = $notification['from'] ?? 'noreply@' . $_SERVER['HTTP_HOST'];
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $from . "\r\n";
            $headers .= "Reply-To: " . $from . "\r\n";
            
            if (function_exists('send_email')) {
                send_email($to, $subject, $message, $headers);
            } else {
                mail($to, $subject, $message, $headers);
            }
        }
    }
    
    private function parseTemplate($template, $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $template = str_replace('{' . $key . '}', html($value), $template);
        }
        
        $template = str_replace('{date}', date('d.m.Y H:i'), $template);
        $template = str_replace('{time}', date('H:i'), $template);
        $template = str_replace('{ip}', $_SERVER['REMOTE_ADDR'] ?? '', $template);
        $template = str_replace('{base_url}', BASE_URL, $template);
        
        return $template;
    }
    
    private function executeActions($form, $data, $submissionId) {
        $actions = $form['actions'] ?? [];
        
        foreach ($actions as $action) {
            if (empty($action['enabled'])) {
                continue;
            }
            
            switch ($action['type']) {
                case 'redirect':
                    break;
                    
                case 'webhook':
                    $this->executeWebhook($action, $form, $data, $submissionId);
                    break;
                    
                case 'save_to_db':
                    break;
            }
        }
    }
    
    private function executeWebhook($action, $form, $data, $submissionId) {
        $webhookData = [
            'form_id' => $form['id'],
            'form_name' => $form['name'],
            'submission_id' => $submissionId,
            'data' => $data,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        $ch = curl_init($action['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: BloggyCMS/1.0'
        ]);
        
        if (!empty($action['headers'])) {
            $headers = [];
            foreach ($action['headers'] as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
                ['Content-Type: application/json'],
                $headers
            ));
        }
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
    }
}