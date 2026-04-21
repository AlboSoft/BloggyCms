<?php

namespace forms\actions;

/**
* Действие экспорта отправок в CSV
*/
class AdminExport extends FormAction {
    
    public function execute() {
        $formId = $this->params['id'] ?? null;
        if (!$formId) {
            \Notification::error(LANG_ACTION_FORMS_ADMINEXPORT_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $form = $this->formModel->getById($formId);
        if (!$form) {
            \Notification::error(LANG_ACTION_FORMS_ADMINEXPORT_FORM_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $csvContent = $this->formModel->exportSubmissionsToCSV($formId);
        
        if (empty($csvContent)) {
            \Notification::warning(LANG_ACTION_FORMS_ADMINEXPORT_NO_DATA);
            $this->redirect(ADMIN_URL . '/forms/show/' . $formId);
            return;
        }
        
        $filename = 'form-submissions-' . $form['slug'] . '-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csvContent));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $csvContent;
        exit;
    }
}