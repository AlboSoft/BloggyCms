<?php

/**
* Поле типа "изображение" для системы пользовательских полей
* @package Fields
*/
class ImageField extends BaseField {
    
    /**
    * Возвращает тип поля 
    * @return string 'image'
    */
    public function getType(): string {
        return 'image';
    }
    
    /**
    * Возвращает отображаемое название типа поля
    * @return string 'Изображение'
    */
    public function getName(): string {
        return LANG_FIELD_IMAGE_TITLE;
    }
    
    /**
    * Генерирует HTML для редактирования поля в форме
    * @param mixed $value Текущее значение поля (имя файла)
    * @param string $entityType Тип сущности (post, user, category и т.д.)
    * @param int $entityId ID сущности
    * @return string HTML-код для редактирования
    */
    public function renderInput($value, $entityType, $entityId): string {
        $safeValue = ($value === null) ? '' : $value;
        
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        $maxSize = $this->config['max_size'] ?? 2048;
        $allowedTypes = html($this->config['allowed_types'] ?? 'jpg,jpeg,png,gif,webp', ENT_QUOTES, 'UTF-8');
        
        $fieldName = 'field_' . ($this->systemName ?? '');
        
        $html = "<div class='image-field' data-image-upload-group data-max-size='{$maxSize}' data-allowed-types='{$allowedTypes}'>";
        
        if (!empty($safeValue)) {
            $safeValueEscaped = html($safeValue, ENT_QUOTES, 'UTF-8');
            $html .= "
                <div class='mb-2' data-image-upload-legacy>
                    <img src='" . BASE_URL . "/uploads/images/{$safeValueEscaped}' 
                         class='img-thumbnail' 
                         style='max-height: 100px;'
                         alt='" . LANG_FIELD_IMAGE_PREVIEW_ALT . "'>
                    <div class='mt-1'>
                        <label class='form-check-label small'>
                            <input type='checkbox' name='{$fieldName}_delete' value='1' class='form-check-input'>
                            " . LANG_FIELD_IMAGE_DELETE_CHECKBOX . "
                        </label>
                    </div>
                </div>
            ";
        }
        
        $maxSizeMB = $maxSize / 1024;
        $html .= "
            <input type='file' 
                   name='{$fieldName}' 
                   class='form-control form-control-sm image-upload-input'
                   accept='image/*'
                   {$required}>
            <div class='form-text'>
                " . sprintf(LANG_FIELD_IMAGE_HINT, $maxSizeMB, $allowedTypes) . "
            </div>
        ";
        
        $html .= "<input type='hidden' name='{$fieldName}_current' value='" . html($safeValue, ENT_QUOTES, 'UTF-8') . "'>";
        
        $html .= "</div>";
        
        return $html;
    }
    
    /**
    * Генерирует HTML для отображения значения поля в детальном просмотре 
    * @param mixed $value Значение поля (имя файла)
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML-код для отображения
    */
    public function renderDisplay($value, $entityType, $entityId): string {
        if (empty($value) && $value !== '0') {
            return '<span class="text-muted">' . LANG_FIELD_IMAGE_NOT_UPLOADED . '</span>';
        }
        
        $safeValue = html($value, ENT_QUOTES, 'UTF-8');
        
        return "
            <div class='text-center'>
                <img src='" . BASE_URL . "/uploads/images/{$safeValue}' 
                     class='img-fluid rounded'
                     style='max-height: 200px;'
                     alt='" . LANG_FIELD_IMAGE_ALT . "'>
            </div>
        ";
    }
    
    /**
    * Генерирует HTML для отображения значения поля в списке 
    * @param mixed $value Значение поля (имя файла)
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML-код для отображения в списке
    */
    public function renderList($value, $entityType, $entityId): string {
        if (empty($value) && $value !== '0') {
            return '<span class="text-muted">' . LANG_FIELD_IMAGE_NO . '</span>';
        }
        
        $safeValue = html($value, ENT_QUOTES, 'UTF-8');
        
        return "
            <img src='" . BASE_URL . "/uploads/images/{$safeValue}' 
                 class='rounded'
                 style='width: 30px; height: 30px; object-fit: cover;'
                 alt='" . LANG_FIELD_IMAGE_CHECK_ALT . "'>
        ";
    }
    
    /**
    * Валидирует значение поля
    * @param mixed $value Значение для проверки
    * @return bool true если значение корректно
    */
    public function validate($value): bool {
        if (isset($this->config['required']) && $this->config['required'] && empty($value) && $value !== '0') {
            return false;
        }
        return true;
    }
    
    /**
    * Обрабатывает значение перед сохранением
    * @param mixed $value Исходное значение
    * @return string Имя файла
    */
    public function processValue($value) {
        return ($value === null) ? '' : $value;
    }
    
    /**
    * Указывает, что поле требует обработки файлов
    * @return bool true
    */
    public function requiresFileUpload(): bool {
        return true;
    }
    
    /**
    * Обрабатывает загрузку файла
    * @param array $fileData Данные из $_FILES
    * @param string|null $currentValue Текущее значение поля
    * @return string|null Имя загруженного файла
    * @throws Exception При ошибке загрузки
    */
    public function processFileUpload($fileData, $currentValue = null) {
        if (empty($fileData['tmp_name'])) {
            return $currentValue;
        }
        
        $uploadDir = UPLOADS_PATH . '/images';
        $allowedTypes = explode(',', $this->config['allowed_types'] ?? 'jpg,jpeg,png,gif,webp');
        $maxSize = $this->config['max_size'] ?? 2048;
        
        try {
            $fileName = FileUpload::upload($fileData, $uploadDir, $allowedTypes, $maxSize);
            if (!empty($currentValue)) {
                $oldFilePath = $uploadDir . '/' . $currentValue;
                FileUpload::delete($oldFilePath);
            }
            
            return $fileName;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
    * Обрабатывает удаление файла
    * @param string|null $currentValue Текущее значение поля
    * @return null Всегда возвращает null
    */
    public function handleDelete($currentValue) {
        if (!empty($currentValue)) {
            $filePath = UPLOADS_PATH . '/images/' . $currentValue;
            FileUpload::delete($filePath);
        }
        return null;
    }
    
    /**
    * Возвращает HTML-форму для настройки поля в административной панели
    * @return string HTML-код формы настроек
    */
    public function getSettingsForm(): string {
        $maxSize = html($this->config['max_size'] ?? '2048', ENT_QUOTES, 'UTF-8');
        $allowedTypes = html($this->config['allowed_types'] ?? 'jpg,jpeg,png,gif,webp', ENT_QUOTES, 'UTF-8');
        
        return "
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>" . LANG_FIELD_IMAGE_MAX_SIZE_LABEL . "</label>
                        <input type='number' class='form-control' name='config[max_size]' value='{$maxSize}' min='100' max='10240'>
                        <div class='form-text'>" . LANG_FIELD_IMAGE_MAX_SIZE_HINT . "</div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>" . LANG_FIELD_IMAGE_ALLOWED_TYPES_LABEL . "</label>
                        <input type='text' class='form-control' name='config[allowed_types]' value='{$allowedTypes}' placeholder='jpg,jpeg,png,gif,webp'>
                        <div class='form-text'>" . LANG_FIELD_IMAGE_ALLOWED_TYPES_HINT . "</div>
                    </div>
                </div>
            </div>
        ";
    }
}