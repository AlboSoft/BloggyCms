<?php

namespace docs\actions;

abstract class DocsAction {
    
    protected $db;
    protected $params;
    protected $controller;
    protected $breadcrumbs;
    protected $pageTitle;
    
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    abstract public function execute();
    
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    protected function render($template, $data = []) {
        if (!$this->controller) {
            throw new \Exception('Controller not set for Action');
        }
        
        if (!isset($data['breadcrumbs'])) {
            $data['breadcrumbs'] = $this->breadcrumbs;
        }
        
        if (!isset($data['pageTitle']) && $this->pageTitle) {
            $data['pageTitle'] = $this->pageTitle;
        }
        
        $this->controller->render($template, $data);
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
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}