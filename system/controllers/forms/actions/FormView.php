<?php

namespace forms\actions;

/**
* Действие просмотра формы
*/
class FormView extends FormAction {
    
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        if (!$slug) {
            \Notification::error(LANG_ACTION_FORMS_FORMVIEW_FORM_NOT_SPECIFIED);
            $this->redirect(BASE_URL);
            return;
        }
        
        $form = $this->formModel->getBySlug($slug);
        if (!$form || $form['status'] !== 'active') {
            \Notification::error(LANG_ACTION_FORMS_FORMVIEW_FORM_NOT_FOUND);
            $this->redirect(BASE_URL);
            return;
        }
        
        $csrfToken = \FormRenderer::generateToken();
        $settings = $form['settings'] ?? [];
        $ajaxEnabled = $settings['ajax_enabled'] ?? true;
        $showLabels = $settings['show_labels'] ?? true;
        $showDescriptions = $settings['show_descriptions'] ?? true;
        $recaptchaSiteKey = $settings['recaptcha_site_key'] ?? '';
        $recaptchaEnabled = $settings['recaptcha_enabled'] ?? false;
        
        $formHtml = \FormRenderer::render($slug, [
            'class' => 'form-view',
            'ajax' => $ajaxEnabled,
            'show_labels' => $showLabels,
            'show_descriptions' => $showDescriptions,
            'recaptcha' => $recaptchaEnabled,
            'recaptcha_site_key' => $recaptchaSiteKey
        ]);
        
        $additionalScripts = '';
        
        $this->render('forms/view', [
            'form' => $form,
            'formHtml' => $formHtml,
            'csrfToken' => $csrfToken,
            'ajaxEnabled' => $ajaxEnabled,
            'additionalScripts' => $additionalScripts,
            'pageTitle' => html($form['name'])
        ]);
    }
}