<?php

namespace pages\actions;

/**
* Действие загрузки изображений для страниц
* @package pages\actions
*/
class AdminUploadImage extends PageAction {
    
    /**
    * Метод выполнения загрузки изображения
    * @return void
    */
    public function execute() {
        try {

            $file = $this->validateUploadedFile();
            $fileName = $this->generateFileName($file['name']);
            $uploadPath = $this->getUploadPath($fileName);
            
            $this->ensureUploadDirectoryExists(dirname($uploadPath));
            $this->validateFileType($file['type']);
            $this->saveUploadedFile($file['tmp_name'], $uploadPath);
            $this->returnSuccessResponse($uploadPath);
            
        } catch (\Exception $e) {
            $this->returnErrorResponse($e->getMessage());
        }
    }
    
    /**
    * Проверяет наличие и корректность загруженного файла
    * @return array Данные загруженного файла
    * @throws \Exception Если файл не загружен или произошла ошибка
    */
    private function validateUploadedFile() {
        if (!isset($_FILES['upload'])) {
            throw new \Exception(LANG_ACTION_PAGES_ADMINUPLOADIMAGE_NO_FILE);
        }
        
        if ($_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($_FILES['upload']['error']);
            throw new \Exception($errorMessage);
        }
        
        return $_FILES['upload'];
    }
    
    /**
    * Возвращает текстовое описание ошибки загрузки по коду
    * @param int $errorCode Код ошибки из $_FILES['error']
    * @return string Описание ошибки
    */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => LANG_ACTION_PAGES_ADMINUPLOADIMAGE_ERROR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => LANG_ACTION_PAGES_ADMINUPLOADIMAGE_ERROR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL => LANG_ACTION_PAGES_ADMINUPLOADIMAGE_ERROR_PARTIAL,
            UPLOAD_ERR_NO_FILE => LANG_ACTION_PAGES_ADMINUPLOADIMAGE_ERROR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR => LANG_ACTION_PAGES_ADMINUPLOADIMAGE_ERROR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE => LANG_ACTION_PAGES_ADMINUPLOADIMAGE_ERROR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION => LANG_ACTION_PAGES_ADMINUPLOADIMAGE_ERROR_EXTENSION
        ];
        
        return $errors[$errorCode] ?? LANG_ACTION_PAGES_ADMINUPLOADIMAGE_ERROR_UNKNOWN;
    }
    
    /**
    * Генерирует уникальное имя файла для сохранения
    * @param string $originalName Оригинальное имя файла
    * @return string Уникальное имя файла
    */
    private function generateFileName($originalName) {
        $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
        
        if (empty($cleanName)) {
            $cleanName = 'image.jpg';
        }
        
        return uniqid() . '_' . $cleanName;
    }
    
    /**
    * Формирует полный путь для сохранения файла
    * @param string $fileName Имя файла
    * @return string Путь для сохранения
    */
    private function getUploadPath($fileName) {
        $baseDir = 'uploads/images/';
        $dateDir = date('Y/m/d');
        $fullDir = $baseDir . $dateDir . '/';
        
        return $fullDir . $fileName;
    }
    
    /**
    * Проверяет существование директории и создает её при необходимости
    * @param string $dir Путь к директории
    * @throws \Exception Если не удалось создать директорию
    */
    private function ensureUploadDirectoryExists($dir) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new \Exception(LANG_ACTION_PAGES_ADMINUPLOADIMAGE_CANT_CREATE_DIR);
            }
        }
    }
    
    /**
    * Проверяет допустимость типа файла
    * @param string $fileType MIME-тип файла
    * @throws \Exception Если тип файла недопустим
    */
    private function validateFileType($fileType) {
        $allowedTypes = [
            'image/jpeg' => '.jpg,.jpeg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp'
        ];
        
        if (!isset($allowedTypes[$fileType])) {
            $allowedExtensions = implode(', ', array_values($allowedTypes));
            throw new \Exception(sprintf(LANG_ACTION_PAGES_ADMINUPLOADIMAGE_INVALID_TYPE, $allowedExtensions));
        }
    }
    
    /**
    * Сохраняет загруженный файл на сервер
    * @param string $tmpName Временный путь к файлу
    * @param string $destination Конечный путь сохранения
    * @throws \Exception Если не удалось сохранить файл
    */
    private function saveUploadedFile($tmpName, $destination) {
        if (!move_uploaded_file($tmpName, $destination)) {
            throw new \Exception(LANG_ACTION_PAGES_ADMINUPLOADIMAGE_SAVE_ERROR);
        }
    }
    
    /**
    * Возвращает успешный JSON-ответ с URL загруженного изображения 
    * @param string $uploadPath Путь к загруженному файлу
    * @return void
    */
    private function returnSuccessResponse($uploadPath) {
        $url = BASE_URL . '/' . $uploadPath;
        
        header('Content-Type: application/json');
        echo json_encode([
            'url' => $url
        ]);
    }
    
    /**
    * Возвращает JSON-ответ с сообщением об ошибке
    * @param string $message Сообщение об ошибке
    * @return void
    */
    private function returnErrorResponse($message) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => [
                'message' => $message
            ]
        ]);
    }
}