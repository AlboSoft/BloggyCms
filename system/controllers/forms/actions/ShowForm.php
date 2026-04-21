<?php

namespace forms\actions;

/**
* Действие показа формы
*/
class ShowForm extends FormAction {
    
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        if (!$slug) {
            \Notification::error(LANG_ACTION_FORMS_SHOWFORM_FORM_NOT_SPECIFIED);
            $this->redirect(BASE_URL);
            return;
        }
        
        $form = $this->formModel->getBySlug($slug);
        if (!$form || $form['status'] !== 'active') {
            \Notification::error(LANG_ACTION_FORMS_SHOWFORM_FORM_NOT_FOUND);
            $this->redirect(BASE_URL);
            return;
        }

        $this->addBreadcrumb(LANG_ACTION_FORMS_SHOWFORM_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FORMS_SHOWFORM_BREADCRUMB_FORMS, ADMIN_URL . '/forms');
        $this->addBreadcrumb(html($form['name']));
        
        $this->render('forms/view', [
            'form' => $form,
            'pageTitle' => html($form['name'])
        ]);
    }
}