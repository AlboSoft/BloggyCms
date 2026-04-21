<?php

namespace forms\actions;

/**
* Действие редактирования существующей формы
*/
class AdminEdit extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error(LANG_ACTION_FORMS_ADMINEDIT_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $form = $this->formModel->getById($id);
        if (!$form) {
            \Notification::error(LANG_ACTION_FORMS_ADMINEDIT_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }

        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINEDIT_BREADCRUMB_FORMS, ADMIN_URL . '/forms');
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINEDIT_BREADCRUMB_EDIT . html($form['name']));
        
        $templates = $this->controller->getAvailableTemplates();
        $currentTheme = $this->controller->getCurrentTheme();
        $formStructure = $form['structure'] ?? [];
        $settings = $form['settings'] ?? $this->getFormSettings();
        $notifications = $form['notifications'] ?? [];
        $actions = $form['actions'] ?? [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty(trim($_POST['name']))) {
                    throw new \Exception(LANG_ACTION_FORMS_ADMINEDIT_NAME_REQUIRED);
                }
                
                $formStructure = json_decode($_POST['form_structure'] ?? '[]', true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception(LANG_ACTION_FORMS_ADMINEDIT_STRUCTURE_PARSE_ERROR . json_last_error_msg());
                }
                
                list($isValid, $validationErrors) = $this->formModel->validateFormStructure($formStructure);
                
                if (!$isValid) {
                    throw new \Exception(LANG_ACTION_FORMS_ADMINEDIT_STRUCTURE_ERROR . implode(', ', $validationErrors));
                }
                
                $fieldNames = [];
                foreach ($formStructure as $field) {
                    if (!empty($field['name']) && $field['type'] !== 'submit') {
                        if (in_array($field['name'], $fieldNames)) {
                            throw new \Exception(sprintf(LANG_ACTION_FORMS_ADMINEDIT_DUPLICATE_FIELD, $field['name']));
                        }
                        $fieldNames[] = $field['name'];
                    }
                }
                
                $processedStructure = $this->processFormStructure($formStructure, $_POST);
                
                $settings = $this->prepareSettings($_POST, $settings);
                $notifications = $this->prepareNotifications($_POST, $notifications);
                $actions = $this->prepareActions($_POST, $actions);
                
                $formData = [
                    'name' => trim($_POST['name']),
                    'description' => trim($_POST['description'] ?? ''),
                    'template' => $_POST['template'] ?? 'default',
                    'structure' => $processedStructure,
                    'settings' => $settings,
                    'success_message' => trim($_POST['success_message'] ?? LANG_ACTION_FORMS_ADMINEDIT_DEFAULT_SUCCESS_MESSAGE),
                    'error_message' => trim($_POST['error_message'] ?? LANG_ACTION_FORMS_ADMINEDIT_DEFAULT_ERROR_MESSAGE),
                    'status' => $_POST['status'] ?? 'active',
                    'notifications' => $notifications,
                    'actions' => $actions,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $success = $this->formModel->update($id, $formData);
                
                if ($success) {
                    \Notification::success(LANG_ACTION_FORMS_ADMINEDIT_SUCCESS);
                    $this->redirect(ADMIN_URL . '/forms/edit/' . $id);
                } else {
                    throw new \Exception(LANG_ACTION_FORMS_ADMINEDIT_UPDATE_FAILED);
                }
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
                
                $form = array_merge($form, [
                    'name' => $_POST['name'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'template' => $_POST['template'] ?? 'default',
                    'status' => $_POST['status'] ?? 'active',
                    'success_message' => $_POST['success_message'] ?? '',
                    'error_message' => $_POST['error_message'] ?? ''
                ]);
                
                $settings = $this->prepareSettings($_POST, $settings);
                $notifications = $this->prepareNotifications($_POST, $notifications);
                $actions = $this->prepareActions($_POST, $actions);
            }
        }
        
        $this->render('admin/forms/form', [
            'form' => $form,
            'formStructure' => $formStructure,
            'fieldTypes' => $this->controller->getAvailableFieldTypes(),
            'validationTypes' => $this->controller->getValidationTypes(),
            'templates' => $templates,
            'currentTheme' => $currentTheme,
            'pageTitle' => LANG_ACTION_FORMS_ADMINEDIT_PAGE_TITLE . html($form['name']),
            'isEdit' => true,
            'settings' => $settings,
            'notifications' => $notifications,
            'actions' => $actions,
            'formModel' => $this->formModel
        ]);
    }
    
    /**
    * Обработка структуры формы - добавление дополнительных данных
    */
    private function processFormStructure($structure, $postData) {
        $processed = [];
        
        foreach ($structure as $field) {
            $processedField = $field;
            
            if (!empty($postData['field_css_' . ($field['name'] ?? '')])) {
                $processedField['class'] = trim($postData['field_css_' . $field['name']]);
            }
            
            if ($field['type'] === 'submit' && !empty($postData['submit_text'])) {
                $processedField['label'] = trim($postData['submit_text']);
            }
            
            $processed[] = $processedField;
        }
        
        return $processed;
    }
    
    /**
    * Подготовка настроек формы
    */
    private function prepareSettings($postData, $currentSettings = []) {
        $settings = !empty($currentSettings) ? $currentSettings : $this->getFormSettings();
        
        $checkboxSettings = [
            'ajax_enabled' => 'ajax_enabled',
            'show_labels' => 'show_labels',
            'show_descriptions' => 'show_descriptions',
            'store_submissions' => 'store_submissions',
            'redirect_after_submit' => 'redirect_after_submit',
            'captcha_enabled' => 'captcha_enabled',
            'csrf_protection' => 'csrf_protection',
            'limit_submissions' => 'limit_submissions',
            'spam_protection' => 'spam_protection',
            'email_validation' => 'email_validation'
        ];
        
        foreach ($checkboxSettings as $key => $postKey) {
            if (array_key_exists($postKey, $postData)) {
                $settings[$key] = !empty($postData[$postKey]);
            }
        }
        
        $textSettings = [
            'redirect_url' => 'redirect_url',
            'captcha_type' => 'captcha_type',
            'captcha_question' => 'captcha_question',
            'captcha_secret' => 'captcha_secret',
            'spam_keywords' => 'spam_keywords'
        ];
        
        foreach ($textSettings as $key => $postKey) {
            if (array_key_exists($postKey, $postData)) {
                $settings[$key] = trim($postData[$postKey]);
            }
        }
        
        $numericSettings = [
            'max_submissions_per_day' => 'max_submissions_per_day',
            'max_submissions_per_ip' => 'max_submissions_per_ip'
        ];
        
        foreach ($numericSettings as $key => $postKey) {
            if (array_key_exists($postKey, $postData)) {
                $settings[$key] = intval($postData[$postKey]);
            }
        }
        
        return $settings;
    }
    
    /**
    * Подготовка уведомлений
    */
    private function prepareNotifications($postData, $currentNotifications = []) {
        if (empty($postData['notify_admin_enabled']) && empty($postData['admin_email']) &&
            empty($postData['notify_user_enabled']) && empty($postData['user_email_field'])) {
            return !empty($currentNotifications) ? $currentNotifications : $this->getDefaultNotifications();
        }
        
        $notifications = [];
        
        if (array_key_exists('notify_admin_enabled', $postData) || array_key_exists('admin_email', $postData)) {
            $adminNotification = !empty($currentNotifications[0]) ? $currentNotifications[0] : [];
            $notifications[] = [
                'enabled' => !empty($postData['notify_admin_enabled']),
                'type' => 'admin',
                'to' => trim($postData['admin_email'] ?? ($adminNotification['to'] ?? '')),
                'from' => trim($postData['admin_from'] ?? ($adminNotification['from'] ?? '')),
                'subject' => trim($postData['admin_subject'] ?? ($adminNotification['subject'] ?? LANG_ACTION_FORMS_ADMINEDIT_DEFAULT_ADMIN_SUBJECT)),
                'message' => trim($postData['admin_message'] ?? ($adminNotification['message'] ?? LANG_ACTION_FORMS_ADMINEDIT_DEFAULT_ADMIN_MESSAGE))
            ];
        } else {
            $notifications[] = !empty($currentNotifications[0]) ? $currentNotifications[0] : [];
        }
        
        if (array_key_exists('notify_user_enabled', $postData) || array_key_exists('user_email_field', $postData)) {
            $userNotification = !empty($currentNotifications[1]) ? $currentNotifications[1] : [];
            $notifications[] = [
                'enabled' => !empty($postData['notify_user_enabled']),
                'type' => 'user',
                'to_field' => trim($postData['user_email_field'] ?? ($userNotification['to_field'] ?? '{email}')),
                'from' => trim($postData['user_from'] ?? ($userNotification['from'] ?? '')),
                'subject' => trim($postData['user_subject'] ?? ($userNotification['subject'] ?? LANG_ACTION_FORMS_ADMINEDIT_DEFAULT_USER_SUBJECT)),
                'message' => trim($postData['user_message'] ?? ($userNotification['message'] ?? LANG_ACTION_FORMS_ADMINEDIT_DEFAULT_USER_MESSAGE))
            ];
        } else {
            $notifications[] = !empty($currentNotifications[1]) ? $currentNotifications[1] : [];
        }
        
        return $notifications;
    }
    
    /**
    * Подготовка действий
    */
    private function prepareActions($postData, $currentActions = []) {
        $actions = [];
        
        $currentSaveAction = $this->findActionByType($currentActions, 'save_to_db');
        $actions[] = [
            'enabled' => array_key_exists('store_submissions', $postData) ? !empty($postData['store_submissions']) : ($currentSaveAction['enabled'] ?? true),
            'type' => 'save_to_db',
            'name' => LANG_ACTION_FORMS_ADMINEDIT_ACTION_SAVE_DB
        ];
        
        if (array_key_exists('redirect_enabled', $postData) || array_key_exists('redirect_url', $postData)) {
            $currentRedirectAction = $this->findActionByType($currentActions, 'redirect');
            $actions[] = [
                'enabled' => !empty($postData['redirect_enabled']),
                'type' => 'redirect',
                'name' => LANG_ACTION_FORMS_ADMINEDIT_ACTION_REDIRECT,
                'url' => trim($postData['redirect_url'] ?? ($currentRedirectAction['url'] ?? ''))
            ];
        } else {
            $currentRedirectAction = $this->findActionByType($currentActions, 'redirect');
            if (!empty($currentRedirectAction)) {
                $actions[] = $currentRedirectAction;
            }
        }
        
        if (array_key_exists('webhook_enabled', $postData) || array_key_exists('webhook_url', $postData)) {
            $currentWebhookAction = $this->findActionByType($currentActions, 'webhook');
            $headers = [];
            $headersText = $postData['webhook_headers'] ?? ($currentWebhookAction['headers_text'] ?? '');
            if (!empty($headersText)) {
                $lines = explode("\n", $headersText);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line) && strpos($line, ':') !== false) {
                        list($key, $value) = explode(':', $line, 2);
                        $headers[trim($key)] = trim($value);
                    }
                }
            }
            $actions[] = [
                'enabled' => !empty($postData['webhook_enabled']),
                'type' => 'webhook',
                'name' => LANG_ACTION_FORMS_ADMINEDIT_ACTION_WEBHOOK,
                'url' => trim($postData['webhook_url'] ?? ($currentWebhookAction['url'] ?? '')),
                'method' => $postData['webhook_method'] ?? ($currentWebhookAction['method'] ?? 'POST'),
                'headers' => !empty($headers) ? $headers : ($currentWebhookAction['headers'] ?? [])
            ];
        } else {
            $currentWebhookAction = $this->findActionByType($currentActions, 'webhook');
            if (!empty($currentWebhookAction)) {
                $actions[] = $currentWebhookAction;
            }
        }
        
        return $actions;
    }

    private function findActionByType($actions, $type) {
        if (empty($actions)) return [];
        foreach ($actions as $action) {
            if (($action['type'] ?? '') === $type) {
                return $action;
            }
        }
        return [];
    }
}