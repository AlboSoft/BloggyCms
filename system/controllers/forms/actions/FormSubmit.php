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
            
            $errors = \FormRenderer::validateSubmission($form, $postData, $filesData);
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