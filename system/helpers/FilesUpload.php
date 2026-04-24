<?php

/**
* Класс для множественной загрузки и управления файлами 
* @package Core
*/
class FilesUpload {
    
    /**
    * Загружает несколько файлов в указанную директорию 
    * @param array $files Массив файлов из $_FILES
    * @param string $uploadDir Директория для загрузки
    * @param array $allowedTypes Разрешенные расширения (например ['jpg', 'png'])
    * @param int $maxSize Максимальный размер в КБ (по умолчанию 5120 = 5MB)
    * @return array Массив результатов для каждого файла
    */
    public static function uploadMultiple($files, $uploadDir, $allowedTypes = [], $maxSize = 5120) {
        $results = [];
        
        if (!isset($files['name']) || !is_array($files['name'])) {
            $files = self::normalizeFilesArray($files);
        }
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $results[] = [
                    'success' => false,
                    'error' => sprintf(LANG_HELPER_FILESUPLOAD_UPLOAD_ERROR, self::getUploadError($files['error'][$i])),
                    'file_name' => $files['name'][$i]
                ];
                continue;
            }
            
            if ($files['size'][$i] > $maxSize * 1024) {
                $results[] = [
                    'success' => false,
                    'error' => sprintf(LANG_HELPER_FILESUPLOAD_FILE_TOO_LARGE, $maxSize),
                    'file_name' => $files['name'][$i]
                ];
                continue;
            }
            
            $fileExtension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!empty($allowedTypes) && !in_array($fileExtension, $allowedTypes)) {
                $results[] = [
                    'success' => false,
                    'error' => sprintf(LANG_HELPER_FILESUPLOAD_INVALID_TYPE, implode(', ', $allowedTypes)),
                    'file_name' => $files['name'][$i]
                ];
                continue;
            }
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . self::sanitizeFileName($files['name'][$i]);
            $targetPath = $uploadDir . '/' . $fileName;
            
            if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                $results[] = [
                    'success' => false,
                    'error' => LANG_HELPER_FILESUPLOAD_SAVE_ERROR,
                    'file_name' => $files['name'][$i]
                ];
                continue;
            }
            
            $results[] = [
                'success' => true,
                'file_name' => $fileName,
                'original_name' => $files['name'][$i],
                'file_path' => $targetPath,
                'file_size' => $files['size'][$i],
                'file_type' => $files['type'][$i]
            ];
        }
        
        return $results;
    }
    
    /**
    * Загружает несколько изображений для блока галереи
    * @param array $files Массив файлов из $_FILES
    * @param string $subfolder Подпапка внутри images/
    * @return array Массив результатов с добавленными URL
    */
    public static function uploadGalleryImages($files, $subfolder = 'gallery') {
        $uploadDir = UPLOADS_PATH . '/images/' . $subfolder;
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5120;
        
        $results = self::uploadMultiple($files, $uploadDir, $allowedTypes, $maxSize);
        
        foreach ($results as &$result) {
            if ($result['success']) {
                $result['url'] = $subfolder . '/' . $result['file_name'];
            }
        }
        
        return $results;
    }
    
    /**
    * Нормализует массив файлов для единообразной обработки 
    * @param array $files Исходный массив файлов
    * @return array Нормализованный массив
    */
    private static function normalizeFilesArray($files) {
        $normalized = [];
        
        if (is_array($files['name'])) {
            foreach ($files as $key => $values) {
                foreach ($values as $index => $value) {
                    $normalized[$index][$key] = $value;
                }
            }
        } else {
            $normalized[] = $files;
        }
        
        $result = ['name' => [], 'type' => [], 'tmp_name' => [], 'error' => [], 'size' => []];
        
        foreach ($normalized as $file) {
            $result['name'][] = $file['name'];
            $result['type'][] = $file['type'];
            $result['tmp_name'][] = $file['tmp_name'];
            $result['error'][] = $file['error'];
            $result['size'][] = $file['size'];
        }
        
        return $result;
    }
    
    /**
    * Удаляет несколько файлов по списку путей
    * @param array $filePaths Массив путей к файлам
    * @return array Массив результатов удаления
    */
    public static function deleteMultiple($filePaths) {
        $results = [];
        
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath) && is_file($filePath)) {
                $results[] = [
                    'success' => unlink($filePath),
                    'file_path' => $filePath
                ];
            } else {
                $results[] = [
                    'success' => false,
                    'error' => LANG_HELPER_FILESUPLOAD_FILE_NOT_EXISTS,
                    'file_path' => $filePath
                ];
            }
        }
        
        return $results;
    }
    
    /**
    * Удаляет изображения галереи по именам файлов 
    * @param array $fileNames Массив имен файлов в папке gallery
    * @return array Массив результатов удаления
    */
    public static function deleteGalleryImages($fileNames) {
        $results = [];
        
        foreach ($fileNames as $fileName) {
            $filePath = UPLOADS_PATH . '/images/gallery/' . $fileName;
            $results[] = self::delete($filePath);
        }
        
        return $results;
    }
    
    /**
    * Удаляет один файл (совместимость с FileUpload) 
    * @param string $filePath Путь к файлу
    * @return bool true при успешном удалении
    */
    public static function delete($filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
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
            UPLOAD_ERR_INI_SIZE => LANG_HELPER_FILESUPLOAD_ERROR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => LANG_HELPER_FILESUPLOAD_ERROR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL => LANG_HELPER_FILESUPLOAD_ERROR_PARTIAL,
            UPLOAD_ERR_NO_FILE => LANG_HELPER_FILESUPLOAD_ERROR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR => LANG_HELPER_FILESUPLOAD_ERROR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE => LANG_HELPER_FILESUPLOAD_ERROR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION => LANG_HELPER_FILESUPLOAD_ERROR_EXTENSION
        ];
        
        return $errors[$errorCode] ?? LANG_HELPER_FILESUPLOAD_ERROR_UNKNOWN;
    }
    
    /**
    * Проверяет, является ли файл изображением по MIME-типу
    * @param string $filePath Путь к файлу
    * @return bool true если файл является изображением
    */
    public static function isImage($filePath) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedTypes);
    }
    
    /**
    * Получает размеры изображения 
    * @param string $filePath Путь к файлу
    * @return array|null Массив с шириной, высотой и MIME-типом или null
    */
    public static function getImageDimensions($filePath) {
        if (!self::isImage($filePath)) {
            return null;
        }
        
        $dimensions = getimagesize($filePath);
        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'mime' => $dimensions['mime']
        ];
    }
}