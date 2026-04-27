<?php

/**
* Поле для загрузки изображений в блоках
* @package Fields
*/
class FieldBlockImage extends Field {
    
    /**
    * Рендерит HTML-код поля для загрузки изображения 
    * @param mixed $currentValue Текущее значение поля (путь к файлу)
    * @return string HTML-код поля
    */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : ($this->options['default'] ?? '');
        $uploadPath = $this->options['upload_path'] ?? 'uploads/';
        
        $previewUrl = '';
        if (!empty($value)) {
            $cleanValue = str_replace(BASE_URL . '/', '', $value);
            $cleanValue = ltrim($cleanValue, '/');
            
            if (strpos($cleanValue, 'uploads/') === 0 || strpos($cleanValue, '/') === 0) {
                $previewUrl = BASE_URL . '/' . ltrim($cleanValue, '/');
            } else {
                $previewUrl = BASE_URL . '/' . ltrim($uploadPath, '/') . ltrim($value, '/');
            }
        }
        
        $fileFieldName = $this->name . '_file';
        $hiddenFieldName = "settings[{$this->name}]";
        $removeFieldName = "remove_{$this->name}";
        
        $previewSize = $this->options['preview_size'] ?? '64px';
        $previewClass = $this->options['preview_class'] ?? 'img-fluid rounded';
        
        $uploadLabel = $this->options['upload_label'] ?? LANG_HELPER_FIELDS_BLOCKIMAGE_UPLOAD_LABEL;
        $replaceLabel = $this->options['replace_label'] ?? LANG_HELPER_FIELDS_BLOCKIMAGE_REPLACE_LABEL;
        $currentLabel = $this->options['current_label'] ?? LANG_HELPER_FIELDS_BLOCKIMAGE_CURRENT_LABEL;
        $deleteLabel = $this->options['delete_label'] ?? LANG_HELPER_FIELDS_BLOCKIMAGE_DELETE_LABEL;
        
        ob_start();
        ?>
        <div class="image-field">
            <?php if ($previewUrl) { ?>
                <div class="mb-3">
                    <label class="form-label"><?php echo htmlspecialchars($currentLabel) ?></label>
                    <div class="border rounded p-3 text-center">
                        <img src="<?php echo htmlspecialchars($previewUrl) ?>" alt="Preview" class="<?php echo htmlspecialchars($previewClass) ?>" style="max-width: <?php echo htmlspecialchars($previewSize) ?>; max-height: <?php echo htmlspecialchars($previewSize) ?>;">
                        <div class="mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="<?php echo htmlspecialchars($removeFieldName) ?>" name="<?php echo htmlspecialchars($removeFieldName) ?>" value="1">
                                <label class="form-check-label text-danger" for="<?php echo htmlspecialchars($removeFieldName) ?>">
                                    <?php echo htmlspecialchars($deleteLabel) ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="mb-3">
                <label class="form-label">
                    <?php echo htmlspecialchars($previewUrl ? $replaceLabel : $uploadLabel) ?>
                </label>
                <input type="file" class="form-control" name="<?php echo htmlspecialchars($fileFieldName) ?>" accept="image/*" <?= $this->options['multiple'] ?? false ? 'multiple' : '' ?>>
                <input type="hidden" name="<?php echo htmlspecialchars($hiddenFieldName) ?>" value="<?php echo htmlspecialchars($value) ?>">
                <div class="form-text text-muted">
                    <?php echo htmlspecialchars($this->options['hint'] ?? '') ?>
                </div>
            </div>
        </div>
        <?php
        return $this->renderFieldGroup(ob_get_clean());
    }

    /**
    * Обрабатывает загрузку изображений для repeater поля
    * @param string $repeaterName Имя repeater поля
    * @param string $blockSystemName Системное имя блока
    * @param array $currentValues Текущие значения repeater
    * @return array Массив обновлений для каждого индекса
    */
    public static function handleRepeaterUploads($repeaterName, $blockSystemName, $currentValues = []) {
        $updates = [];

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
        
        foreach ($_FILES as $field => $fileData) {
            if (strpos($field, $repeaterName . '[') === 0 && strpos($field, '_file]') !== false) {
                preg_match('/' . preg_quote($repeaterName, '/') . '\[(\d+)\]\[(.+?)_file\]/', $field, $matches);
                
                if (count($matches) === 3 && $fileData['error'] === UPLOAD_ERR_OK) {
                    $index = $matches[1];
                    $fieldName = $matches[2];
                    
                    $uploadDir = 'uploads/images/html_blocks/' . $blockSystemName . '/repeater/' . $repeaterName . '/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    if (isset($currentValues[$index][$fieldName]) && !empty($currentValues[$index][$fieldName])) {
                        $oldPath = $currentValues[$index][$fieldName];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    
                    $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $fileName = $repeaterName . '_' . $index . '_' . $fieldName . '_' . uniqid() . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($fileData['tmp_name'], $filePath)) {
                        if (!isset($updates[$index])) {
                            $updates[$index] = [];
                        }
                        $updates[$index][$fieldName] = $filePath;
                    }
                }
            }
        }
        
        return $updates;
    }
    
    /**
    * Применяет обновления к данным repeater
    * @param array $repeaterData Текущие данные repeater
    * @param array $updates Обновления для применения
    * @return array Обновленные данные repeater
    */
    public static function applyRepeaterUpdates($repeaterData, $updates) {
        foreach ($updates as $index => $fieldUpdates) {
            if (isset($repeaterData[$index])) {
                foreach ($fieldUpdates as $fieldName => $value) {
                    $repeaterData[$index][$fieldName] = $value;
                }
            }
        }
        
        return $repeaterData;
    }
    
}