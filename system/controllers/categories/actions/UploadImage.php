<?php

namespace categories\actions;

/**
* Действие загрузки изображения для категории
* @package categories\actions
*/
class UploadImage extends CategoryAction {
    
    /**
    * Метод выполнения загрузки изображения
    * @return void
    */
    public function execute() {

        if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'error' => [
                    'message' => LANG_ACTION_CATEGORIES_UPLOADIMAGE_UPLOAD_ERROR
                ]
            ]);
            return;
        }
    
        $file = $_FILES['upload'];
        
        $fileName = uniqid() . '_' . $file['name'];
        
        $uploadPath = 'uploads/images/categories/' . $fileName;
        
        $uploadDir = dirname($uploadPath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    
        $allowedTypes = [
            'image/jpeg', 
            'image/png', 
            'image/gif', 
            'image/webp'
        ];
        
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode([
                'error' => [
                    'message' => LANG_ACTION_CATEGORIES_UPLOADIMAGE_INVALID_TYPE
                ]
            ]);
            return;
        }
    
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo json_encode([
                'url' => BASE_URL . '/' . $uploadPath
            ]);
        } else {
            echo json_encode([
                'error' => [
                    'message' => LANG_ACTION_CATEGORIES_UPLOADIMAGE_SAVE_ERROR
                ]
            ]);
        }
    }
}