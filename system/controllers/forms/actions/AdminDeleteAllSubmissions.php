<?php

namespace forms\actions;

/**
* Действие удаления всех отправок формы
*/
class AdminDeleteAllSubmissions extends FormAction {
    
    public function execute() {
        $formId = $this->params['id'] ?? null;
        if (!$formId) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => LANG_ACTION_FORMS_ADMINDELETEALLSUBMISSIONS_ID_NOT_SPECIFIED
            ]);
            exit;
        }
        
        try {
            $submissions = $this->db->fetchAll(
                "SELECT id FROM form_submissions WHERE form_id = ?",
                [$formId]
            );
            
            $deletedCount = 0;
            
            foreach ($submissions as $submission) {
                if ($this->formModel->deleteSubmission($submission['id'])) {
                    $deletedCount++;
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => LANG_ACTION_FORMS_ADMINDELETEALLSUBMISSIONS_SUCCESS,
                'count' => $deletedCount
            ]);
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