<?php

namespace categories\actions;

/**
* Действие проверки пароля для защищенных категорий
* @package categories\actions
*/
class CheckPassword extends CategoryAction {
    
    /**
    * Метод выполнения проверки пароля
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => LANG_ACTION_CATEGORIES_CHECKPASSWORD_ID_NOT_SPECIFIED
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => LANG_ACTION_CATEGORIES_CHECKPASSWORD_METHOD_NOT_SUPPORTED
            ]);
            return;
        }
        
        try {
            $password = $_POST['password'] ?? '';
            
            $category = $this->categoryModel->getById($id);
            
            if (!$category) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => LANG_ACTION_CATEGORIES_CHECKPASSWORD_CATEGORY_NOT_FOUND
                ]);
                return;
            }
            
            if (!$category['password_protected'] || $category['password'] === $password) {
                if (!isset($_SESSION['category_access'])) {
                    $_SESSION['category_access'] = [];
                }
                
                $_SESSION['category_access'][$category['id']] = true;
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => LANG_ACTION_CATEGORIES_CHECKPASSWORD_INVALID_PASSWORD
                ]);
            }
            
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => LANG_ACTION_CATEGORIES_CHECKPASSWORD_ERROR
            ]);
        }
    }
}