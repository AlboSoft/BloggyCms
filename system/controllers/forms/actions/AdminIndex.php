<?php

namespace forms\actions;

/**
* Действие главной страницы управления формами
*/
class AdminIndex extends FormAction {
    
    public function execute() {
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMININDEX_BREADCRUMB_FORMS);
        
        $forms = $this->formModel->getAll();
        $statistics = $this->formModel->getStatistics();
        
        $this->render('admin/forms/index', [
            'forms' => $forms,
            'statistics' => $statistics,
            'pageTitle' => LANG_ACTION_FORMS_ADMININDEX_PAGE_TITLE,
            'formModel' => $this->formModel
        ]);
    }

}