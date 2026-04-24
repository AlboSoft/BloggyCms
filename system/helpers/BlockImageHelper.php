<?php

/**
* Вспомогательный класс для работы с изображениями в HTML-блоках 
* @package Helpers
*/
class BlockImageHelper {
    
    /**
    * Обрабатывает загрузку изображения для контент-блока
    * Проверяет тип файла, размер, создает директорию и сохраняет файл
    */
    public static function handleUpload($fieldName, $blockSystemName, $currentValue = '') {
        $result = [
            'success' => false,
            'value' => $currentValue,
            'error' => '',
            'file_path' => ''
        ];
        
        $fileField = $fieldName . '_file';
        
        if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = LANG_HELPER_BLOCKIMAGE_UPLOAD_NO_FILE;
            return $result;
        }
        
        $file = $_FILES[$fileField];
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $result['error'] = LANG_HELPER_BLOCKIMAGE_INVALID_TYPE;
            return $result;
        }
        
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $result['error'] = LANG_HELPER_BLOCKIMAGE_FILE_TOO_LARGE;
            return $result;
        }
        
        $uploadDir = 'uploads/images/html_blocks/' . $blockSystemName . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (!empty($currentValue) && file_exists($currentValue)) {
            unlink($currentValue);
        }
        
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $fieldName . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $result['success'] = true;
            $result['value'] = $filePath;
            $result['file_path'] = $filePath;
        } else {
            $result['error'] = LANG_HELPER_BLOCKIMAGE_SAVE_ERROR;
        }
        
        return $result;
    }
    
    /**
    * Обрабатывает удаление изображения по чекбоксу
    */
    public static function handleDelete($fieldName, $currentValue) {
        $removeField = 'remove_' . $fieldName;
        
        if (isset($_POST[$removeField]) && $_POST[$removeField] == '1' && !empty($currentValue)) {
            if (file_exists($currentValue)) {
                unlink($currentValue);
            }
            return '';
        }
        
        return $currentValue;
    }
    
    /**
    * Обрабатывает загрузку изображений для repeater поля
    */
    public static function handleRepeaterUploads($repeaterName, $blockSystemName, $currentValues = []) {
        $updates = [];
        
        foreach ($_FILES as $field => $fileData) {
            if (is_array($fileData['name'])) {
                if (isset($fileData['name'][0]) && is_array($fileData['name'][0])) {
                    foreach ($fileData['name'] as $index => $innerArray) {
                        if (is_array($innerArray)) {
                            foreach ($innerArray as $fieldKey => $fileName) {
                                if (!empty($fileName) && $fileData['error'][$index][$fieldKey] === UPLOAD_ERR_OK) {
                                    $fieldName = str_replace('_file', '', $fieldKey);
                                    
                                    $singleFileData = [
                                        'name' => $fileName,
                                        'type' => $fileData['type'][$index][$fieldKey],
                                        'tmp_name' => $fileData['tmp_name'][$index][$fieldKey],
                                        'error' => $fileData['error'][$index][$fieldKey],
                                        'size' => $fileData['size'][$index][$fieldKey]
                                    ];
                                    
                                    $uploadResult = self::uploadRepeaterFile($singleFileData, $blockSystemName, $repeaterName, $index, $fieldName);
                                    
                                    if ($uploadResult['success']) {
                                        if (!isset($updates[$index])) {
                                            $updates[$index] = [];
                                        }
                                        $updates[$index][$fieldName] = $uploadResult['file_path'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        foreach ($_POST as $field => $value) {
            if (strpos($field, $repeaterName . '[') === 0 && strpos($field, 'remove_') !== false) {
                preg_match('/' . preg_quote($repeaterName, '/') . '\[(\d+)\]\[remove_(.+?)\]/', $field, $matches);
                
                if (count($matches) === 3 && $value == '1') {
                    $index = $matches[1];
                    $fieldName = $matches[2];
                    
                    if (isset($currentValues[$index][$fieldName]) && !empty($currentValues[$index][$fieldName])) {
                        $filePath = $currentValues[$index][$fieldName];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        
                        if (!isset($updates[$index])) {
                            $updates[$index] = [];
                        }
                        $updates[$index][$fieldName] = '';
                    }
                }
            }
        }
        
        return $updates;
    }
    
    /**
    * Загружает один файл для repeater
    * Внутренний вспомогательный метод
    */
    private static function uploadRepeaterFile($fileData, $blockSystemName, $repeaterName, $index, $fieldName) {
        $result = ['success' => false, 'file_path' => '', 'error' => ''];
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $fileType = mime_content_type($fileData['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $result['error'] = LANG_HELPER_BLOCKIMAGE_INVALID_TYPE;
            return $result;
        }
        
        $maxSize = 5 * 1024 * 1024;
        if ($fileData['size'] > $maxSize) {
            $result['error'] = LANG_HELPER_BLOCKIMAGE_FILE_TOO_LARGE;
            return $result;
        }
        
        $uploadDir = 'uploads/images/html_blocks/' . $blockSystemName . '/repeater/' . $repeaterName . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $fileName = $repeaterName . '_' . $index . '_' . $fieldName . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($fileData['tmp_name'], $filePath)) {
            $result['success'] = true;
            $result['file_path'] = $filePath;
        } else {
            $result['error'] = LANG_HELPER_BLOCKIMAGE_SAVE_ERROR;
        }
        
        return $result;
    }
    
    /**
    * Применяет обновления к данным repeater
    */
    public static function applyRepeaterUpdates($repeaterData, $updates) {
        foreach ($updates as $index => $fieldUpdates) {
            if (isset($repeaterData[$index])) {
                foreach ($fieldUpdates as $fieldName => $value) {
                    $repeaterData[$index][$fieldName] = $value;
                }
            } else {
                $repeaterData[$index] = $fieldUpdates;
            }
        }
        
        return $repeaterData;
    }
    
    /**
    * Получает URL для отображения изображения
    */
    public static function getImageUrl($imagePath) {
        if (empty($imagePath)) {
            return '';
        }
        
        $cleanPath = str_replace(BASE_URL . '/', '', $imagePath);
        $cleanPath = ltrim($cleanPath, '/');
        
        return BASE_URL . '/' . $cleanPath;
    }
}