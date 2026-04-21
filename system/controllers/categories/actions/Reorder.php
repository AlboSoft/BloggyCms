<?php

namespace categories\actions;

/**
* Действие изменения порядка сортировки категорий
* @package categories\actions
*/
class Reorder extends CategoryAction {
    
    /**
    * Метод выполнения изменения порядка категорий
    * @return void
    */
    public function execute() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception(LANG_ACTION_CATEGORIES_REORDER_INVALID_METHOD);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['order']) || !is_array($input['order'])) {
                throw new \Exception(LANG_ACTION_CATEGORIES_REORDER_INVALID_DATA);
            }
            
            foreach ($input['order'] as $item) {
                if (!isset($item['id']) || !isset($item['order'])) {
                    continue;
                }
                
                $this->categoryModel->updateOrder($item['id'], $item['order']);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => LANG_ACTION_CATEGORIES_REORDER_SUCCESS
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
        
        exit;
    }
}