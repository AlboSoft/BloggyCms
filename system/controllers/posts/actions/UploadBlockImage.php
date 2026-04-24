<?php

namespace posts\actions;

/**
* Действие загрузки изображений для блоков контента 
* @package posts\actions
*/
class UploadBlockImage extends PostAction {
    
    /**
    * Метод выполнения загрузки изображения для блока
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {

            if (!isset($_FILES['block_image']) || $_FILES['block_image']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception(LANG_ACTION_POSTS_UPLOADBLOCKIMAGE_UPLOAD_ERROR);
            }

            $file = $_FILES['block_image'];
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                throw new \Exception(LANG_ACTION_POSTS_UPLOADBLOCKIMAGE_INVALID_TYPE);
            }
            
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new \Exception(LANG_ACTION_POSTS_UPLOADBLOCKIMAGE_FILE_TOO_LARGE);
            }

            $uploadDir = UPLOADS_PATH . '/images/blocks/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid() . '_' . basename($file['name']);
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new \Exception(LANG_ACTION_POSTS_UPLOADBLOCKIMAGE_SAVE_ERROR);
            }

            echo json_encode([
                'success' => true,
                'url' => BASE_URL . '/uploads/images/blocks/' . $fileName,
                'path' => $fileName,
                'message' => LANG_ACTION_POSTS_UPLOADBLOCKIMAGE_SUCCESS
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