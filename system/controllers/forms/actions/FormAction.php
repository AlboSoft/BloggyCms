<?php

namespace forms\actions;

abstract class FormAction {
    protected $db;
    protected $params;
    protected $controller;
    protected $formModel;
    protected $breadcrumbs;
    protected $pageTitle;
    
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->formModel = new \FormModel($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    abstract public function execute();
    
    /**
    * Добавляет элемент в хлебные крошки
    * @param string $title Название элемента
    * @param string|null $url URL элемента
    * @return self
    */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
    * Устанавливает заголовок страницы 
    * @param string $title Заголовок
    * @return self
    */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    protected function render($template, $data = []) {
        if ($this->controller) {
            if (!isset($data['breadcrumbs'])) {
                $data['breadcrumbs'] = $this->breadcrumbs;
            }
            if (!isset($data['title']) && $this->pageTitle) {
                $data['title'] = $this->pageTitle;
            }
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    protected function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function getFormSettings() {
        return [
            'ajax_enabled' => true,
            'show_labels' => true,
            'show_descriptions' => true,
            'store_submissions' => true,
            'redirect_after_submit' => false,
            'redirect_url' => '',
            'captcha_enabled' => false,
            'captcha_type' => 'math',
            'captcha_question' => LANG_ACTION_FORMS_FORMACTION_CAPTCHA_DEFAULT_QUESTION,
            'captcha_secret' => 'bloggy_cms_captcha',
            'csrf_protection' => true,
            'spam_protection' => false,
            'spam_keywords' => '',
            'success_message' => LANG_ACTION_FORMS_FORMACTION_DEFAULT_SUCCESS_MESSAGE,
            'error_message' => LANG_ACTION_FORMS_FORMACTION_DEFAULT_ERROR_MESSAGE
        ];
    }
    
    protected function getDefaultNotifications() {
        return [
            [
                'enabled' => true,
                'type' => 'admin',
                'to' => 'admin@example.com',
                'subject' => LANG_ACTION_FORMS_FORMACTION_DEFAULT_ADMIN_SUBJECT,
                'message' => LANG_ACTION_FORMS_FORMACTION_DEFAULT_ADMIN_MESSAGE
            ],
            [
                'enabled' => false,
                'type' => 'user',
                'to' => '{email}',
                'subject' => LANG_ACTION_FORMS_FORMACTION_DEFAULT_USER_SUBJECT,
                'message' => LANG_ACTION_FORMS_FORMACTION_DEFAULT_USER_MESSAGE
            ]
        ];
    }
    
    protected function getDefaultActions() {
        return [
            [
                'enabled' => true,
                'type' => 'save_to_db',
                'name' => LANG_ACTION_FORMS_FORMACTION_ACTION_SAVE_DB
            ],
            [
                'enabled' => false,
                'type' => 'redirect',
                'name' => LANG_ACTION_FORMS_FORMACTION_ACTION_REDIRECT,
                'url' => ''
            ],
            [
                'enabled' => false,
                'type' => 'webhook',
                'name' => LANG_ACTION_FORMS_FORMACTION_ACTION_WEBHOOK,
                'url' => '',
                'method' => 'POST',
                'headers' => []
            ]
        ];
    }
    
    /**
    * Возвращает менеджер хлебных крошек
    * @return \BreadcrumbsManager
    */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }
}