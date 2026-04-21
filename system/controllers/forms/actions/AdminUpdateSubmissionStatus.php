<?php

namespace forms\actions;

/**
* Действие обновления статуса отправки
*/
class AdminUpdateSubmissionStatus extends FormAction {
    
    public function execute() {
        $submissionId = $this->params['id'] ?? null;
        $status = $_GET['status'] ?? null;
        
        if (!$submissionId || !$status) {
            $this->jsonResponse([
                'success' => false,
                'message' => LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_MISSING_PARAMS
            ]);
            return;
        }
        
        $validStatuses = ['new', 'read', 'processed', 'spam'];
        if (!in_array($status, $validStatuses)) {
            $this->jsonResponse([
                'success' => false,
                'message' => LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_INVALID_STATUS
            ]);
            return;
        }
        
        try {
            $success = $this->formModel->updateSubmissionStatus($submissionId, $status);
            
            if ($success) {
                $statusTexts = [
                    'new' => LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_STATUS_NEW,
                    'read' => LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_STATUS_READ,
                    'processed' => LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_STATUS_PROCESSED,
                    'spam' => LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_STATUS_SPAM
                ];
                
                \Notification::success(LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_CHANGED . $statusTexts[$status]);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_UPDATED,
                    'status_text' => $statusTexts[$status]
                ]);
            } else {
                throw new \Exception(LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_UPDATE_FAILED);
            }
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_FORMS_ADMINUPDATESUBMISSIONSTATUS_ERROR . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}