<?php

/**
* Поле типа "повторитель" (repeater) для системы полей 
* @package Fields
* @extends Field
*/
class FieldRepeater extends Field {
    
    /**
    * Рендерит HTML-код поля-повторителя
    * @param mixed $currentValue Текущее значение поля (массив элементов)
    * @return string HTML-код поля
    */
    public function render($currentValue = null) {
        $values = $currentValue !== null ? $currentValue : [];
        $subFieldsConfig = $this->options['fields'] ?? [];
        $columns = $this->options['repeater_columns'] ?? 1;
        $cardColumnClass = $this->getCardColumnClass($columns);
        
        ob_start();
        ?>
        <div class="repeater-field" data-field-name="<?= $this->name ?>">
            <div class="repeater-items row g-3">
                <?php foreach ($values as $index => $item) { ?>
                    <div class="<?= $cardColumnClass ?>">
                        <div class="repeater-item card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted small"><?php echo sprintf(LANG_HELPER_FIELDS_REPEATER_ELEMENT_NUMBER, $index + 1); ?></span>
                                <button type="button" class="btn btn-sm btn-outline-danger repeater-remove-btn">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="repeater-fields-container">
                                    <?php foreach ($subFieldsConfig as $fieldConfig) { ?>
                                        <?php
                                        $fieldName = $fieldConfig['name'] ?? '';
                                        $fieldTitle = $fieldConfig['title'] ?? '';
                                        $fieldType = $fieldConfig['type'] ?? 'string';
                                        $fieldValue = $item[$fieldName] ?? '';
                                        ?>
                                        <div class="repeater-field-item mb-3">
                                            <label class="form-label small fw-bold mb-1"><?= $fieldTitle ?></label>
                                            <?php
                                            $hiddenFieldName = "settings[{$this->name}][{$index}][{$fieldName}]";
                                            $fileFieldName = "{$this->name}[{$index}][{$fieldName}_file]";
                                            $removeFieldName = "{$this->name}[{$index}][remove_{$fieldName}]";
                                            
                                            $this->renderFieldByType($fieldConfig, $fieldValue, $hiddenFieldName, $fileFieldName, $removeFieldName, $index, $fieldType);
                                            
                                            if (!empty($fieldConfig['hint'])) {
                                                echo '<div class="form-text small text-muted mt-1">' . $fieldConfig['hint'] . '</div>';
                                            }
                                            ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <button type="button" class="btn btn-outline-primary btn-sm repeater-add-btn mt-3">
                <i class="bi bi-plus"></i> <?php echo LANG_HELPER_FIELDS_REPEATER_ADD_BTN; ?>
            </button>
            
            <template class="repeater-template">
                <div class="<?= $cardColumnClass ?>">
                    <div class="repeater-item card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted small"><?php echo LANG_HELPER_FIELDS_REPEATER_NEW_ELEMENT; ?></span>
                            <button type="button" class="btn btn-sm btn-outline-danger repeater-remove-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="repeater-fields-container">
                                <?php foreach ($subFieldsConfig as $fieldConfig) { ?>
                                    <?php
                                    $fieldName = $fieldConfig['name'] ?? '';
                                    $fieldTitle = $fieldConfig['title'] ?? '';
                                    $fieldType = $fieldConfig['type'] ?? 'string';
                                    ?>
                                    <div class="repeater-field-item mb-3">
                                        <label class="form-label small fw-bold mb-1"><?= $fieldTitle ?></label>
                                        <?php
                                        $hiddenFieldName = "settings[{$this->name}][__INDEX__][{$fieldName}]";
                                        $fileFieldName = "{$this->name}[__INDEX__][{$fieldName}_file]";
                                        $removeFieldName = "{$this->name}[__INDEX__][remove_{$fieldName}]";
                                        
                                        $this->renderFieldByType($fieldConfig, '', $hiddenFieldName, $fileFieldName, $removeFieldName, '__INDEX__', $fieldType, true);
                                        
                                        if (!empty($fieldConfig['hint'])) {
                                            echo '<div class="form-text small text-muted mt-1">' . $fieldConfig['hint'] . '</div>';
                                        }
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        
        <script>
            (function() {
                function initRepeater(repeater) {
                    if (!repeater) return;
                    
                    const addBtn = repeater.querySelector('.repeater-add-btn');
                    const template = repeater.querySelector('.repeater-template');
                    const itemsContainer = repeater.querySelector('.repeater-items');
                    
                    let itemIndex = itemsContainer.children.length;
                    
                    addBtn.addEventListener('click', function() {
                        const newItem = template.content.cloneNode(true);
                        const tempDiv = document.createElement('div');
                        tempDiv.appendChild(newItem);
                        
                        let htmlContent = tempDiv.innerHTML;
                        htmlContent = htmlContent.replace(/__INDEX__/g, itemIndex);
                        
                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = htmlContent;
                        const newItemElement = wrapper.firstElementChild;
                        
                        itemsContainer.appendChild(newItemElement);
                        itemIndex++;
                    });
                    
                    itemsContainer.addEventListener('click', function(e) {
                        if (e.target.closest('.repeater-remove-btn')) {
                            const item = e.target.closest('[class*="col-"]');
                            if (item && itemsContainer.children.length > 1) {
                                item.remove();
                            }
                        }
                    });
                }
                
                const repeater = document.querySelector('.repeater-field[data-field-name="<?= $this->name ?>"]');
                initRepeater(repeater);
            })();
        </script>
        <?php
        
        $fieldHtml = ob_get_clean();
        return $this->renderFieldGroup($fieldHtml);
    }

    /**
    * Определяет класс колонки для карточки репитера
    */
    private function getCardColumnClass($columns) {
        switch ($columns) {
            case 2:
                return 'col-md-6';
            case 3:
                return 'col-md-4';
            case 4:
                return 'col-md-3';
            case 6:
                return 'col-md-2';
            default:
                return 'col-12';
        }
    }

    /**
    * Определяет класс колонки для поля внутри карточки
    */
    private function getFieldColumnClass($fieldConfig) {
        if (isset($fieldConfig['field_column'])) {
            return "col-{$fieldConfig['field_column']}";
        }
        return 'col-12';
    }
    
    /**
    * Определяет класс колонки для поля (сетка Bootstrap)
    * Учитывает параметр column из конфигурации поля 
    * @param array $fieldConfig Конфигурация поля
    * @return string CSS класс для колонки
    */
    private function getColumnClass($fieldConfig) {
        if (isset($fieldConfig['column'])) {
            return "col-md-{$fieldConfig['column']}";
        }
        
        $fieldType = $fieldConfig['type'] ?? 'string';
        if ($fieldType === 'image' || $fieldType === 'blockimage' || $fieldType === 'textarea') {
            return 'col-12';
        }
        return 'col-md-6';
    }
    
    /**
    * Рендерит поле в зависимости от его типа 
    * @param array $config Конфигурация поля
    * @param mixed $value Текущее значение
    * @param string $hiddenFieldName Имя скрытого поля
    * @param string $fileFieldName Имя поля для файла
    * @param string $removeFieldName Имя поля для удаления
    * @param string|int $index Индекс элемента
    * @param string $fieldType Тип поля
    * @param bool $isTemplate Флаг шаблона
    */
    private function renderFieldByType($config, $value, $hiddenFieldName, $fileFieldName, $removeFieldName, $index, $fieldType, $isTemplate = false) {
        switch ($fieldType) {
            case 'string':
                echo '<input type="text" name="' . $hiddenFieldName . '" class="form-control form-control-sm w-100" value="' . htmlspecialchars($value) . '" placeholder="' . ($config['placeholder'] ?? '') . '">';
                break;
                
            case 'select':
                echo '<select name="' . $hiddenFieldName . '" class="form-select form-select-sm w-100">';
                foreach ($config['options'] as $optValue => $optLabel) {
                    $selected = $value == $optValue ? ' selected' : '';
                    echo '<option value="' . $optValue . '"' . $selected . '>' . $optLabel . '</option>';
                }
                echo '</select>';
                break;
                
            case 'textarea':
                echo '<textarea name="' . $hiddenFieldName . '" class="form-control form-control-sm w-100" rows="2">' . htmlspecialchars($value) . '</textarea>';
                break;
                
            case 'number':
                echo '<input type="number" name="' . $hiddenFieldName . '" class="form-control form-control-sm w-100" value="' . htmlspecialchars($value) . '" min="' . ($config['min'] ?? '') . '" max="' . ($config['max'] ?? '') . '" step="' . ($config['step'] ?? '1') . '">';
                break;
                
            case 'image':
            case 'blockimage':
                $this->renderBlockImageField($config, $value, $hiddenFieldName, $fileFieldName, $removeFieldName, $index, $isTemplate);
                break;
        }
    }
    
    /**
    * Рендерит поле blockimage для repeater 
    * @param array $config Конфигурация поля
    * @param string $value Текущее значение (путь к файлу)
    * @param string $hiddenFieldName Имя скрытого поля
    * @param string $fileFieldName Имя поля для файла
    * @param string $removeFieldName Имя поля для удаления
    * @param string|int $index Индекс элемента
    * @param bool $isTemplate Флаг шаблона
    */
    private function renderBlockImageField($config, $value, $hiddenFieldName, $fileFieldName, $removeFieldName, $index, $isTemplate = false) {
        $uploadPath = $config['upload_path'] ?? 'uploads/';
        $previewUrl = '';
        
        if (!empty($value) && !$isTemplate) {
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $previewUrl = $value;
            } else {
                $cleanValue = ltrim($value, '/');
                if (defined('BASE_URL')) {
                    $previewUrl = BASE_URL . '/' . $cleanValue;
                } else {
                    $previewUrl = '/' . $cleanValue;
                }
            }
        }
        
        $previewSize = $config['preview_size'] ?? '48px';
        $previewClass = $config['preview_class'] ?? 'img-fluid rounded';
        
        if ($previewUrl && !$isTemplate) {
            ?>
            <div class="image-preview mb-2">
                <div class="border rounded p-2 text-center">
                    <img src="<?= htmlspecialchars($previewUrl) ?>" 
                         alt="Preview" 
                         class="<?= htmlspecialchars($previewClass) ?>" 
                         style="max-width: <?= htmlspecialchars($previewSize) ?>; max-height: <?= htmlspecialchars($previewSize) ?>;">
                    <div class="mt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" 
                                   name="<?= $removeFieldName ?>" 
                                   value="1" 
                                   id="remove_<?= $index ?>_<?= $config['name'] ?>">
                            <label class="form-check-label text-danger small" 
                                   for="remove_<?= $index ?>_<?= $config['name'] ?>">
                                <?php echo LANG_HELPER_FIELDS_REPEATER_DELETE_LABEL; ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <div>
            <input type="hidden" 
                   name="<?= $hiddenFieldName ?>" 
                   value="<?= $isTemplate ? '' : htmlspecialchars($value) ?>">
            <input type="file" 
                   class="form-control form-control-sm" 
                   name="<?= $fileFieldName ?>" 
                   accept="image/*">
        </div>
        <?php
    }
}