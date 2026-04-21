<?php

namespace forms\actions;

/**
* Действие удаления отправки
*/
class AdminDeleteSubmission extends FormAction {
    
    public function execute() {
        $submissionId = $this->params['id'] ?? null;
        if (!$submissionId) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => LANG_ACTION_FORMS_ADMINDELETESUBMISSION_ID_NOT_SPECIFIED
            ]);
            exit;
        }
        
        try {
            $success = $this->formModel->deleteSubmission($submissionId);
            
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => LANG_ACTION_FORMS_ADMINDELETESUBMISSION_SUCCESS
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => LANG_ACTION_FORMS_ADMINDELETESUBMISSION_FAILED
                ]);
            }
            exit;
            
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}