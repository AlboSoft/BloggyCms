<?php

namespace fragments\actions;

/**
* Действие сортировки полей (AJAX)
*/
class AdminFieldReorder extends FragmentAction {
    
    public function execute() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_FRAGMENTS_ADMINFIELDREORDER_INVALID_METHOD]);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['order']) || !is_array($input['order'])) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_FRAGMENTS_ADMINFIELDREORDER_INVALID_DATA]);
            return;
        }
        
        try {
            $success = $this->fragmentModel->reorderFields($input['order']);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => LANG_ACTION_FRAGMENTS_ADMINFIELDREORDER_SUCCESS]);
            } else {
                echo json_encode(['success' => false, 'message' => LANG_ACTION_FRAGMENTS_ADMINFIELDREORDER_UPDATE_ERROR]);
            }
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}