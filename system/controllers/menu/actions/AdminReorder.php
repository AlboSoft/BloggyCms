<?php

namespace menu\actions;

/**
* Действие сортировки пунктов меню (AJAX)
* @package menu\actions
*/
class AdminReorder extends MenuAction {
    
    /**
    * Метод выполнения сортировки пунктов
    * @return void
    */
    public function execute() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => LANG_ACTION_MENU_ADMINREORDER_INVALID_METHOD
            ]);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['menu_id']) || !isset($input['order']) || !is_array($input['order'])) {
            echo json_encode([
                'success' => false,
                'message' => LANG_ACTION_MENU_ADMINREORDER_INVALID_DATA
            ]);
            return;
        }
        
        $menuId = (int)$input['menu_id'];
        $order = $input['order'];
        
        try {
            $result = $this->menuModel->reorderItems($menuId, $order);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => LANG_ACTION_MENU_ADMINREORDER_SUCCESS
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => LANG_ACTION_MENU_ADMINREORDER_ERROR
                ]);
            }
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}