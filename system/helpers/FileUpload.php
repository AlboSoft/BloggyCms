<?php

/**
* Класс для загрузки и управления отдельными файлами
* @package Core
*/
class FileUpload {
    
    /**
    * Загружает файл в указанную директорию и возвращает имя файла
    * @param array $file Данные файла из $_FILES
    * @param string $uploadDir Директория для загрузки
    * @param array $allowedTypes Разрешенные расширения (например ['jpg', 'png'])
    * @param int $maxSize Максимальный размер в КБ (по умолчанию 2048 = 2MB)
    * @return string Имя загруженного файла
    * @throws Exception При ошибках загрузки или валидации
    */
    public static function upload($file, $uploadDir, $allowedTypes = [], $maxSize = 2048) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception(sprintf(LANG_HELPER_FILEUPLOAD_UPLOAD_ERROR, self::getUploadError($file['error'])));
        }
        
        if ($file['size'] > $maxSize * 1024) {
            throw new Exception(sprintf(LANG_HELPER_FILEUPLOAD_FILE_TOO_LARGE, $maxSize));
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowedTypes) && !in_array($fileExtension, $allowedTypes)) {
            throw new Exception(sprintf(LANG_HELPER_FILEUPLOAD_INVALID_TYPE, implode(', ', $allowedTypes)));
        }
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . self::sanitizeFileName($file['name']);
        $targetPath = $uploadDir . '/' . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception(LANG_HELPER_FILEUPLOAD_SAVE_ERROR);
        }
        
        return $fileName;
    }
    
    /**
    * Загружает изображение для блока поста 
    * @param array $file Данные файла из $_FILES
    * @param string $subfolder Подпапка внутри images/
    * @return string Путь к файлу относительно корня (например 'blocks/имя.jpg')
    * @throws Exception При ошибках загрузки
    */
    public static function uploadBlockImage($file, $subfolder = 'blocks') {
        $uploadDir = UPLOADS_PATH . '/images/' . $subfolder;
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5120;
        
        $fileName = self::upload($file, $uploadDir, $allowedTypes, $maxSize);
        
        return $subfolder . '/' . $fileName;
    }
    
    /**
    * Удаляет файл 
    * @param string $filePath Полный путь к файлу
    * @return bool true при успешном удалении, false если файл не существует
    */
    public static function delete($filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
    * Удаляет изображение блока 
    * @param string $fileName Имя файла (может включать подпапку)
    * @return bool Результат удаления
    */
    public static function deleteBlockImage($fileName) {
        $filePath = UPLOADS_PATH . '/images/' . $fileName;
        return self::delete($filePath);
    }
    
    /**
    * Очищает имя файла от небезопасных символов 
    * @param string $fileName Исходное имя файла
    * @return string Очищенное имя файла
    */
    private static function sanitizeFileName($fileName) {
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        return preg_replace('/_{2,}/', '_', $fileName);
    }
    
    /**
    * Возвращает текстовое описание ошибки загрузки по коду 
    * @param int $errorCode Код ошибки из $_FILES
    * @return string Описание ошибки
    */
    private static function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => LANG_HELPER_FILEUPLOAD_ERROR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => LANG_HELPER_FILEUPLOAD_ERROR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL => LANG_HELPER_FILEUPLOAD_ERROR_PARTIAL,
            UPLOAD_ERR_NO_FILE => LANG_HELPER_FILEUPLOAD_ERROR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR => LANG_HELPER_FILEUPLOAD_ERROR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE => LANG_HELPER_FILEUPLOAD_ERROR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION => LANG_HELPER_FILEUPLOAD_ERROR_EXTENSION
        ];
        
        return $errors[$errorCode] ?? LANG_HELPER_FILEUPLOAD_ERROR_UNKNOWN;
    }

    /**
    * Проверяет, является ли файл ZIP-архивом 
    * @param string $filePath Путь к файлу
    * @return bool
    */
    public static function isZip($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return $mimeType === 'application/zip' || 
               $mimeType === 'application/x-zip-compressed' ||
               pathinfo($filePath, PATHINFO_EXTENSION) === 'zip';
    }

}