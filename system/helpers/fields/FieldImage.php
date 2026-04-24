<?php

/**
* Поле типа "изображение" для системы полей
* @package Fields
* @extends Field
*/
class FieldImage extends Field {
    
    /**
    * Рендерит HTML-код поля для загрузки изображения 
    * @param mixed $currentValue Текущее значение поля (имя файла)
    * @return string HTML-код поля
    */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        $uploadPath = $this->options['upload_path'] ?? 'uploads/';
        
        $previewUrl = $value ? BASE_URL . '/' . $uploadPath . $value : '';
        
        $isAdminMode = $this->options['admin_mode'] ?? false;
        
        $fileFieldName = $this->name . '_file';
        $hiddenFieldName = $isAdminMode ? $this->name : "settings[{$this->name}]";
        $removeFieldName = $isAdminMode ? "remove_{$this->name}" : "remove_{$this->name}";
        
        ob_start();
        ?>
        <div class="image-field">
            <?php if ($previewUrl): ?>
            <div class="mb-3">
                <label class="form-label"><?php echo LANG_HELPER_FIELDS_IMAGE_CURRENT_LABEL; ?></label>
                <div class="border rounded p-3 text-center">
                    <img src="<?= $previewUrl ?>" 
                        alt="Preview" 
                        class="img-fluid rounded" 
                        style="width: 64px;">
                    <div class="mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                id="<?= $removeFieldName ?>" 
                                name="<?= $removeFieldName ?>" value="1">
                            <label class="form-check-label text-danger" for="<?= $removeFieldName ?>">
                                <?php echo LANG_HELPER_FIELDS_IMAGE_DELETE_LABEL; ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">
                    <?= $previewUrl ? LANG_HELPER_FIELDS_IMAGE_REPLACE_LABEL : LANG_HELPER_FIELDS_IMAGE_UPLOAD_LABEL ?>
                </label>
                <input type="file" 
                    class="form-control" 
                    name="<?= $fileFieldName ?>" 
                    accept="image/*">
                <input type="hidden" 
                    name="<?= $hiddenFieldName ?>" 
                    value="<?= htmlspecialchars($value) ?>">
                <div class="form-text text-muted">
                    <?= $this->options['hint'] ?? LANG_HELPER_FIELDS_IMAGE_HINT ?>
                </div>
            </div>
        </div>
        <?php
        return $this->renderFieldGroup(ob_get_clean());
    }
}