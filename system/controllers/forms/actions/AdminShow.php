<?php

namespace forms\actions;

/**
* Действие просмотра отправок формы
*/
class AdminShow extends FormAction {
    
    /**
    * Действие просмотра отправок формы
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error(LANG_ACTION_FORMS_ADMINSHOW_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $form = $this->formModel->getById($id);
        if (!$form) {
            \Notification::error(LANG_ACTION_FORMS_ADMINSHOW_FORM_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINSHOW_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINSHOW_BREADCRUMB_FORMS, ADMIN_URL . '/forms');
        $this->addBreadcrumb(html($form['name']), ADMIN_URL . '/forms/edit/' . $id);
        $this->addBreadcrumb(LANG_ACTION_FORMS_ADMINSHOW_BREADCRUMB_SUBMISSIONS);
        
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $submissions = $this->formModel->getSubmissions($id, $perPage, $offset);
        $totalSubmissions = $this->formModel->getSubmissionsCount($id);
        $totalPages = ceil($totalSubmissions / $perPage);
        
        $statusStats = [
            'new' => 0,
            'read' => 0,
            'processed' => 0,
            'spam' => 0
        ];
        
        foreach ($submissions as $submission) {
            if (isset($statusStats[$submission['status']])) {
                $statusStats[$submission['status']]++;
            }
        }
        
        $this->render('admin/forms/show', [
            'form' => $form,
            'submissions' => $submissions,
            'submissionsCount' => $totalSubmissions,
            'newCount' => $statusStats['new'],
            'processedCount' => $statusStats['processed'],
            'spamCount' => $statusStats['spam'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => LANG_ACTION_FORMS_ADMINSHOW_PAGE_TITLE . html($form['name']),
            'formModel' => $this->formModel
        ]);
    }

}