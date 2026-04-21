<?php

namespace forms\actions;

/**
* Действие предварительного просмотра формы
*/
class AdminPreview extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error(LANG_ACTION_FORMS_ADMINPREVIEW_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $form = $this->formModel->getById($id);
        if (!$form) {
            \Notification::error(LANG_ACTION_FORMS_ADMINPREVIEW_FORM_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINPREVIEW_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINPREVIEW_BREADCRUMB_FORMS, ADMIN_URL . '/forms');
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINPREVIEW_BREADCRUMB_PREVIEW . html($form['name']));
        
        $settings = $form['settings'] ?? [];
        
        $formHtml = \FormRenderer::render($form['slug'], [
            'class' => 'form-preview',
            'ajax' => $settings['ajax_enabled'] ?? false,
            'show_labels' => $settings['show_labels'] ?? true,
            'show_descriptions' => $settings['show_descriptions'] ?? true,
            'captcha' => $settings['captcha_enabled'] ?? false,
            'captcha_type' => $settings['captcha_type'] ?? 'math',
            'captcha_question' => $settings['captcha_question'] ?? '',
            'captcha_secret' => $settings['captcha_secret'] ?? 'bloggy_cms_captcha',
            'csrf_protection' => $settings['csrf_protection'] ?? true
        ]);
        
        $this->render('admin/forms/preview', [
            'form' => $form,
            'formHtml' => $formHtml,
            'pageTitle' => LANG_ACTION_FORMS_ADMINPREVIEW_PAGE_TITLE . html($form['name'])
        ]);
    }

}