<?php

namespace forms\actions;

/**
* Действие удаления формы
*/
class AdminDelete extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error(LANG_ACTION_FORMS_ADMINDELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        try {
            $form = $this->formModel->getById($id);
            if (!$form) {
                throw new \Exception(LANG_ACTION_FORMS_ADMINDELETE_NOT_FOUND);
            }

            $this->deleteFormWithRelations($id);
            
            \Notification::success(LANG_ACTION_FORMS_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_FORMS_ADMINDELETE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/forms');
    }
    
    /**
    * Удаляет форму со всеми связанными данными
    */
    private function deleteFormWithRelations($formId) {
        $db = $this->db;
        $db->beginTransaction();
        
        try {
            $submissions = $db->fetchAll(
                "SELECT id FROM form_submissions WHERE form_id = ?",
                [$formId]
            );
            
            foreach ($submissions as $submission) {
                $files = $db->fetchAll(
                    "SELECT * FROM form_files WHERE submission_id = ?",
                    [$submission['id']]
                );
                
                foreach ($files as $file) {
                    if (file_exists(ROOT_PATH . '/' . $file['file_path'])) {
                        unlink(ROOT_PATH . '/' . $file['file_path']);
                    }
                }
                
                $db->delete('form_files', ['submission_id' => $submission['id']]);
                $db->delete('form_submissions', ['id' => $submission['id']]);
            }
            
            $result = $db->delete('forms', ['id' => $formId]);
            
            if ($result === false) {
                throw new \Exception(LANG_ACTION_FORMS_ADMINDELETE_DB_ERROR);
            }
            
            $db->commit();
            
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}