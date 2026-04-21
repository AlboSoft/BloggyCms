<?php

namespace forms\actions;

/**
* Действие настроек формы
*/
class AdminSettings extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error(LANG_ACTION_FORMS_ADMINSETTINGS_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $form = $this->formModel->getById($id);
        if (!$form) {
            \Notification::error(LANG_ACTION_FORMS_ADMINSETTINGS_FORM_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }

        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINSETTINGS_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINSETTINGS_BREADCRUMB_FORMS, ADMIN_URL . '/forms');
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINSETTINGS_BREADCRUMB_EDIT . html($form['name']), ADMIN_URL . '/forms/edit/' . $id);
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINSETTINGS_BREADCRUMB_SETTINGS);
        
        $settings = $form['settings'] ?? $this->getFormSettings();
        $notifications = $form['notifications'] ?? $this->getDefaultNotifications();
        $actions = $form['actions'] ?? $this->getDefaultActions();
        $captchaTypes = $this->getCaptchaTypes();
        $captchaExample = $this->generateCaptchaExample($settings['captcha_type'] ?? 'math');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {

                $settings = $this->prepareSettings($_POST, $settings);
                $notifications = $this->prepareNotifications($_POST, $notifications);
                $actions = $this->prepareActions($_POST, $actions);

                $formData = [
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                    'notifications' => json_encode($notifications, JSON_UNESCAPED_UNICODE),
                    'actions' => json_encode($actions, JSON_UNESCAPED_UNICODE),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $success = $this->formModel->update($id, $formData);
                
                if ($success) {
                    \Notification::success(LANG_ACTION_FORMS_ADMINSETTINGS_SUCCESS);
                    $this->redirect(ADMIN_URL . '/forms/settings/' . $id);
                } else {
                    throw new \Exception(LANG_ACTION_FORMS_ADMINSETTINGS_UPDATE_FAILED);
                }
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
            }
        }
        
        $this->render('admin/forms/settings', [
            'form' => $form,
            'settings' => $settings,
            'notifications' => $notifications,
            'actions' => $actions,
            'captchaTypes' => $captchaTypes,
            'captchaExample' => $captchaExample,
            'pageTitle' => LANG_ACTION_FORMS_ADMINSETTINGS_PAGE_TITLE . html($form['name'])
        ]);
    }
    
    /**
    * Подготовка настроек
    */
    private function prepareSettings($postData, $currentSettings) {
        $settings = $currentSettings;
        
        $checkboxSettings = [
            'ajax_enabled' => 'ajax_enabled',
            'show_labels' => 'show_labels',
            'show_descriptions' => 'show_descriptions',
            'captcha_enabled' => 'captcha_enabled',
            'store_submissions' => 'store_submissions',
            'redirect_after_submit' => 'redirect_after_submit',
            'csrf_protection' => 'csrf_protection',
            'limit_submissions' => 'limit_submissions',
            'spam_protection' => 'spam_protection',
            'email_validation' => 'email_validation'
        ];
        
        foreach ($checkboxSettings as $key => $postKey) {
            $settings[$key] = isset($postData[$postKey]) && !empty($postData[$postKey]);
        }
        
        $textSettings = [
            'redirect_url' => 'redirect_url',
            'captcha_type' => 'captcha_type',
            'captcha_question' => 'captcha_question',
            'captcha_secret' => 'captcha_secret',
            'spam_keywords' => 'spam_keywords',
            'success_message' => 'success_message',
            'error_message' => 'error_message'
        ];
        
        foreach ($textSettings as $key => $postKey) {
            if (isset($postData[$postKey])) {
                $settings[$key] = trim($postData[$postKey]);
            }
        }
        
        $numericSettings = [
            'max_submissions_per_day' => 'max_submissions_per_day',
            'max_submissions_per_ip' => 'max_submissions_per_ip'
        ];
        
        foreach ($numericSettings as $key => $postKey) {
            if (isset($postData[$postKey])) {
                $settings[$key] = intval($postData[$postKey]);
            }
        }
        
        return $settings;
    }
    
    /**
    * Подготовка уведомлений
    */
    private function prepareNotifications($postData, $currentNotifications) {
        $notifications = [];
        
        $adminNotification = [
            'enabled' => !empty($postData['notify_admin_enabled']),
            'type' => 'admin',
            'to' => $postData['admin_email'] ?? '',
            'from' => $postData['admin_from'] ?? '',
            'subject' => $postData['admin_subject'] ?? LANG_ACTION_FORMS_ADMINSETTINGS_DEFAULT_ADMIN_SUBJECT,
            'message' => $postData['admin_message'] ?? LANG_ACTION_FORMS_ADMINSETTINGS_DEFAULT_ADMIN_MESSAGE
        ];
        
        $userNotification = [
            'enabled' => !empty($postData['notify_user_enabled']),
            'type' => 'user',
            'to_field' => $postData['user_email_field'] ?? '{email}',
            'from' => $postData['user_from'] ?? '',
            'subject' => $postData['user_subject'] ?? LANG_ACTION_FORMS_ADMINSETTINGS_DEFAULT_USER_SUBJECT,
            'message' => $postData['user_message'] ?? LANG_ACTION_FORMS_ADMINSETTINGS_DEFAULT_USER_MESSAGE
        ];
        
        $notifications = [$adminNotification, $userNotification];
        
        return $notifications;
    }
    
    /**
    * Подготовка действий
    */
    private function prepareActions($postData, $currentActions) {
        $actions = [];
        
        $actions[] = [
            'enabled' => true,
            'type' => 'save_to_db',
            'name' => LANG_ACTION_FORMS_ADMINSETTINGS_ACTION_SAVE_DB
        ];
        
        if (!empty($postData['redirect_enabled'])) {
            $actions[] = [
                'enabled' => true,
                'type' => 'redirect',
                'name' => LANG_ACTION_FORMS_ADMINSETTINGS_ACTION_REDIRECT,
                'url' => $postData['redirect_url'] ?? ''
            ];
        }
        
        if (!empty($postData['webhook_enabled'])) {
            $headers = [];
            $headersText = $postData['webhook_headers'] ?? '';
            if (!empty($headersText)) {
                $lines = explode("\n", $headersText);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strpos($line, ':') !== false) {
                        list($key, $value) = explode(':', $line, 2);
                        $headers[trim($key)] = trim($value);
                    }
                }
            }
            
            $actions[] = [
                'enabled' => true,
                'type' => 'webhook',
                'name' => LANG_ACTION_FORMS_ADMINSETTINGS_ACTION_WEBHOOK,
                'url' => $postData['webhook_url'] ?? '',
                'method' => $postData['webhook_method'] ?? 'POST',
                'headers' => $headers
            ];
        }
        
        if (!empty($postData['email_action_enabled'])) {
            $actions[] = [
                'enabled' => true,
                'type' => 'send_email',
                'name' => LANG_ACTION_FORMS_ADMINSETTINGS_ACTION_SEND_EMAIL,
                'to' => $postData['email_action_to'] ?? '',
                'subject' => $postData['email_action_subject'] ?? '',
                'template' => $postData['email_action_template'] ?? ''
            ];
        }
        
        return $actions;
    }
    
    /**
    * Получение типов капчи
    */
    private function getCaptchaTypes() {
        return [
            'math' => [
                'name' => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_MATH_NAME,
                'description' => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_MATH_DESC
            ],
            'text' => [
                'name' => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_TEXT_NAME,
                'description' => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_TEXT_DESC
            ],
            'logic' => [
                'name' => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_LOGIC_NAME,
                'description' => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_LOGIC_DESC
            ],
            'image' => [ 
                'name' => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_IMAGE_NAME,
                'description' => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_IMAGE_DESC
            ]
        ];
    }
    
    /**
    * Генерация примера для капчи
    */
    private function generateCaptchaExample($type = 'math') {
        switch ($type) {
            case 'math':
                $operations = ['+', '-', '*'];
                $op = $operations[array_rand($operations)];
                $a = rand(1, 10);
                $b = rand(1, 10);
                
                if ($op === '-') {
                    $a = max($a, $b) + rand(0, 5);
                }
                
                $question = LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_MATH_QUESTION . "$a $op $b?";
                $answer = eval("return $a $op $b;");
                break;
                
            case 'text':
                $questions = [
                    LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_TEXT_Q1 => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_TEXT_A1,
                    LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_TEXT_Q2 => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_TEXT_A2,
                    LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_TEXT_Q3 => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_TEXT_A3
                ];
                $question = array_rand($questions);
                $answer = $questions[$question];
                break;
                
            case 'logic':
                $questions = [
                    LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_LOGIC_Q1 => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_LOGIC_A1,
                    LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_LOGIC_Q2 => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_LOGIC_A2,
                    LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_LOGIC_Q3 => LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_LOGIC_A3
                ];
                $question = array_rand($questions);
                $answer = $questions[$question];
                break;
                
            default:
                $question = LANG_ACTION_FORMS_ADMINSETTINGS_CAPTCHA_DEFAULT_QUESTION;
                $answer = '4';
        }
        
        return [
            'question' => $question,
            'answer' => $answer
        ];
    }
}