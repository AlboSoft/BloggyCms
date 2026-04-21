<?php

namespace forms\actions;

/**
* Действие получения структуры формы через AJAX
*/
class AdminGetStructure extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            $this->jsonResponse([
                'success' => false,
                'message' => LANG_ACTION_FORMS_ADMINGETSTRUCTURE_ID_NOT_SPECIFIED
            ]);
            return;
        }
        
        try {
            $form = $this->formModel->getById($id);
            if (!$form) {
                throw new \Exception(LANG_ACTION_FORMS_ADMINGETSTRUCTURE_FORM_NOT_FOUND);
            }
            
            $structure = $form['structure'] ?? [];
            
            $this->jsonResponse([
                'success' => true,
                'structure' => $structure
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}