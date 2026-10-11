<?php

/**
* Поле типа "иконка со стилями" для системы пользовательских полей
* Объединяет выбор иконки, её цвет и размер в одном компактном поле
* @package Fields
*/
class IconWithStyleField extends BaseField {
    
    private static $assetsLoaded = false;
    
    /**
    * Возвращает тип поля
    * @return string 'icon_with_style'
    */
    public function getType(): string {
        return 'icon_with_style';
    }
    
    /**
    * Возвращает отображаемое название типа поля
    * @return string 'Иконка со стилями'
    */
    public function getName(): string {
        return LANG_FIELD_ICONSTYLE_TITLE;
    }
    
    /**
    * Получает список всех доступных иконок через файловую систему
    * @return array
    */
    private function getAllIconsFromFilesystem() {
        $icons = [];
        $iconsDir = TEMPLATES_PATH . '/default/admin/icons/';
        
        if (!is_dir($iconsDir)) {
            return $icons;
        }
        
        $files = glob($iconsDir . '*.svg') ?: [];
        
        foreach ($files as $file) {
            $set = basename($file, '.svg');
            $content = file_get_contents($file);
            
            preg_match_all('/<symbol\s+id="([^"]+)"/', $content, $matches);
            
            if (!empty($matches[1])) {
                $icons[$set] = [
                    'name' => $set,
                    'icons' => array_map(function($id) use ($set) {
                        return [
                            'id' => $id,
                            'preview' => '<svg width="24" height="24" style="fill: currentColor;"><use href="' . BASE_URL . '/templates/default/admin/icons/' . $set . '.svg#' . $id . '"/></svg>'
                        ];
                    }, $matches[1])
                ];
            }
        }
        
        return $icons;
    }
    
    /**
    * Загружает ресурсы для выбора иконок
    */
    private function loadIconAssets(): void {
        if (self::$assetsLoaded) {
            return;
        }
        
        admin_js('templates/default/admin/assets/js/controllers/icon-with-style-field.js');
        front_js('templates/default/admin/assets/js/controllers/icon-with-style-field.js');
        
        self::$assetsLoaded = true;
    }
    
    /**
    * Генерирует HTML для редактирования поля в форме
    * @param mixed $value Текущее значение поля (JSON строка)
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML-код для редактирования
    */
    public function renderInput($value, $entityType, $entityId): string {
        $this->loadIconAssets();
        
        $decodedValue = $this->decodeValue($value);

        error_log("IconWithStyleField renderInput - raw value: " . print_r($value, true));
        error_log("IconWithStyleField renderInput - decoded: " . print_r($decodedValue, true));

        $iconSet = $decodedValue['set'] ?? '';
        $iconName = $decodedValue['name'] ?? '';
        $iconColor = $decodedValue['color'] ?? $this->config['default_color'] ?? '#000000';
        $iconSize = $decodedValue['size'] ?? $this->config['default_size'] ?? 24;
        
        $fieldName = 'field_' . ($this->systemName ?? '');
        $pickerId = 'icon-picker-' . $this->systemName . '-' . uniqid();
        $previewId = 'icon-preview-' . $this->systemName . '-' . uniqid();
        
        $previewHtml = $this->getIconPreviewHtml($iconSet, $iconName, $iconColor, $iconSize);
        
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        
        $sizeMin = $this->config['size_min'] ?? 8;
        $sizeMax = $this->config['size_max'] ?? 128;
        $sizeStep = $this->config['size_step'] ?? 2;
        
        $allowColor = isset($this->config['allow_color']) ? (bool)$this->config['allow_color'] : true;
        $colorPickerClass = $allowColor ? '' : 'd-none';
        $allowSize = isset($this->config['allow_size']) ? (bool)$this->config['allow_size'] : true;
        $sizeSliderClass = $allowSize ? '' : 'd-none';
        
        $iconColWidth = 12;
        if ($allowColor && $allowSize) {
            $iconColWidth = 4;
        } elseif ($allowColor || $allowSize) {
            $iconColWidth = 6;
        }
        
        $iconsData = $this->getAllIconsFromFilesystem();
        $iconsDataJson = json_encode($iconsData);
        $hasIcons = !empty($iconsData);
        
        ob_start();
        ?>
        <div class="icon-with-style-field mb-3" 
             data-field-name="<?= $fieldName ?>"
             data-picker-id="<?= $pickerId ?>"
             data-preview-id="<?= $previewId ?>"
             data-current-set="<?= htmlspecialchars($iconSet) ?>"
             data-current-name="<?= htmlspecialchars($iconName) ?>"
             data-current-color="<?= htmlspecialchars($iconColor) ?>"
             data-current-size="<?= (int)$iconSize ?>"
             data-size-min="<?= (int)$sizeMin ?>"
             data-size-max="<?= (int)$sizeMax ?>"
             data-size-step="<?= (int)$sizeStep ?>"
             data-allow-color="<?= $allowColor ? '1' : '0' ?>"
             data-allow-size="<?= $allowSize ? '1' : '0' ?>"
             data-has-icons="<?= $hasIcons ? '1' : '0' ?>"
             data-icons-data='<?= $iconsDataJson ?>'>
            
            <input type="hidden" 
                   name="<?= $fieldName ?>" 
                   id="<?= $pickerId ?>-input"
                   value='<?= htmlspecialchars(json_encode($decodedValue, JSON_UNESCAPED_UNICODE)) ?>'
                   <?= $required ?>>
            
            <div class="row g-3 align-items-end">
                <div class="col-md-<?= $iconColWidth ?>">
                    <label class="form-label small fw-semibold"><?= LANG_FIELD_ICONSTYLE_ICON_LABEL ?></label>
                    <div class="d-flex gap-2">
                        <div class="icon-preview-wrapper flex-shrink-0">
                            <div id="<?= $previewId ?>" class="icon-preview-box border rounded p-2 text-center bg-light" style="width: 60px; height: 60px;">
                                <?= $previewHtml ?>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 select-icon-btn">
                                <i class="bi bi-images me-1"></i>
                                <?= LANG_FIELD_ICONSTYLE_SELECT_BTN ?>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-1 clear-icon-btn"
                                    style="display: <?= !empty($iconName) ? 'block' : 'none' ?>;">
                                <i class="bi bi-trash me-1"></i>
                                <?= LANG_FIELD_ICONSTYLE_CLEAR_BTN ?>
                            </button>
                        </div>
                    </div>
                    <div class="form-text small"><?= LANG_FIELD_ICONSTYLE_ICON_HINT ?></div>
                </div>
                
                <div class="col-md-<?= $iconColWidth ?> <?= $colorPickerClass ?>">
                    <label class="form-label small fw-semibold"><?= LANG_FIELD_ICONSTYLE_COLOR_LABEL ?></label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" class="form-control form-control-color icon-color-input"
                               style="width: 50px; height: 38px;" value="<?= htmlspecialchars($iconColor) ?>">
                        <input type="text" class="form-control form-control-sm icon-color-text"
                               value="<?= htmlspecialchars($iconColor) ?>" placeholder="#000000" style="font-family: monospace;">
                    </div>
                </div>
                
                <div class="col-md-<?= $iconColWidth ?> <?= $sizeSliderClass ?>">
                    <label class="form-label small fw-semibold"><?= LANG_FIELD_ICONSTYLE_SIZE_LABEL ?> <span class="icon-size-value"><?= $iconSize ?></span>px</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted"><?= $sizeMin ?></span>
                        <input type="range" class="form-range icon-size-slider flex-grow-1"
                               min="<?= $sizeMin ?>" max="<?= $sizeMax ?>" step="<?= $sizeStep ?>" value="<?= $iconSize ?>">
                        <span class="small text-muted"><?= $sizeMax ?></span>
                        <input type="number" class="form-control form-control-sm icon-size-number"
                               style="width: 70px;" min="<?= $sizeMin ?>" max="<?= $sizeMax ?>" step="<?= $sizeStep ?>" value="<?= $iconSize ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .icon-preview-box { background: #f8f9fa; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; }
        .icon-preview-box svg { max-width: 100%; max-height: 100%; }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
    * Генерирует HTML для отображения значения поля в детальном просмотре
    * @param mixed $value Значение поля (JSON строка)
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML-код для отображения
    */
    public function renderDisplay($value, $entityType, $entityId): string {
        $decodedValue = $this->decodeValue($value);
        
        $iconSet = $decodedValue['set'] ?? '';
        $iconName = $decodedValue['name'] ?? '';
        $iconColor = $decodedValue['color'] ?? $this->config['default_color'] ?? '#000000';
        $iconSize = $decodedValue['size'] ?? $this->config['default_size'] ?? 24;
        
        if (empty($iconName) || empty($iconSet)) {
            return '';
        }
        
        return $this->getIconPreviewHtml($iconSet, $iconName, $iconColor, $iconSize);
    }
    
    /**
    * Генерирует HTML для отображения значения поля в списке
    * @param mixed $value Значение поля (JSON строка)
    * @param string $entityType Тип сущности
    * @param int $entityId ID сущности
    * @return string HTML-код для отображения в списке
    */
    public function renderList($value, $entityType, $entityId): string {
        $decodedValue = $this->decodeValue($value);
        
        $iconSet = $decodedValue['set'] ?? '';
        $iconName = $decodedValue['name'] ?? '';
        $iconColor = $decodedValue['color'] ?? $this->config['default_color'] ?? '#000000';
        $iconSize = $decodedValue['size'] ?? 20;
        
        if (empty($iconName) || empty($iconSet)) {
            return '<span class="text-muted">' . LANG_FIELD_ICONSTYLE_EMPTY . '</span>';
        }
        
        return $this->getIconPreviewHtml($iconSet, $iconName, $iconColor, $iconSize);
    }
    
    /**
    * Декодирует значение поля из JSON
    * @param mixed $value Значение поля
    * @return array Декодированный массив
    */
    private function decodeValue($value): array {
        if (empty($value)) {
            return [];
        }
        
        if (is_array($value)) {
            if (isset($value['set']) || isset($value['name'])) {
                return [
                    'set' => $value['set'] ?? '',
                    'name' => $value['name'] ?? '',
                    'color' => $value['color'] ?? $this->config['default_color'] ?? '#000000',
                    'size' => $value['size'] ?? $this->config['default_size'] ?? 24
                ];
            }
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return [
                    'set' => $decoded['set'] ?? '',
                    'name' => $decoded['name'] ?? '',
                    'color' => $decoded['color'] ?? $this->config['default_color'] ?? '#000000',
                    'size' => $decoded['size'] ?? $this->config['default_size'] ?? 24
                ];
            }
            
            if (!empty($value) && strpos($value, '{') !== 0) {
                return [
                    'set' => 'bs',
                    'name' => $value,
                    'color' => $this->config['default_color'] ?? '#000000',
                    'size' => $this->config['default_size'] ?? 24
                ];
            }
        }
        
        return [];
    }
    
    /**
    * Генерирует HTML для превью иконки
    * @param string $set Набор иконок
    * @param string $name Имя иконки
    * @param string $color Цвет иконки
    * @param int $size Размер иконки в пикселях
    * @return string HTML-код иконки
    */
    private function getIconPreviewHtml($set, $name, $color, $size): string {
        if (empty($set) || empty($name)) {
            return '<div class="text-muted small text-center">' . LANG_FIELD_ICONSTYLE_NO_ICON . '</div>';
        }
        
        if (function_exists('bloggy_icon')) {
            $svg = bloggy_icon($set, $name, $size . ' ' . $size, $color, 'icon-display');
            return $svg;
        }
        
        return '<div class="text-center text-muted small">' . htmlspecialchars($name) . '</div>';
    }
    
    /**
    * Валидирует значение поля
    * @param mixed $value Значение для проверки
    * @return bool true если значение корректно
    */
    public function validate($value): bool {
        if (isset($this->config['required']) && $this->config['required']) {
            if (empty($value)) {
                return false;
            }
            
            $decoded = $this->decodeValue($value);
            if (empty($decoded['name']) || empty($decoded['set'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
    * Обрабатывает значение перед сохранением
    * @param mixed $value Исходное значение
    * @return string JSON строка
    */
    public function processValue($value) {
        if ($value === null || $value === '') {
            return json_encode([]);
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $value;
            }
            
            if (!empty($value) && strpos($value, '{') !== 0) {
                return json_encode([
                    'set' => 'bs',
                    'name' => $value,
                    'color' => $this->config['default_color'] ?? '#000000',
                    'size' => $this->config['default_size'] ?? 24
                ], JSON_UNESCAPED_UNICODE);
            }
        }
        
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        
        return json_encode([]);
    }
    
    /**
    * Возвращает HTML-форму для настройки поля в административной панели
    * @return string HTML-код формы настроек
    */
    public function getSettingsForm(): string {
        $defaultColor = html($this->config['default_color'] ?? '#000000', ENT_QUOTES, 'UTF-8');
        $defaultSize = html($this->config['default_size'] ?? '24', ENT_QUOTES, 'UTF-8');
        $sizeMin = html($this->config['size_min'] ?? '8', ENT_QUOTES, 'UTF-8');
        $sizeMax = html($this->config['size_max'] ?? '128', ENT_QUOTES, 'UTF-8');
        $sizeStep = html($this->config['size_step'] ?? '2', ENT_QUOTES, 'UTF-8');
        
        $allowColor = isset($this->config['allow_color']) ? (bool)$this->config['allow_color'] : true;
        $allowSize = isset($this->config['allow_size']) ? (bool)$this->config['allow_size'] : true;
        
        $allowColorChecked = $allowColor ? 'checked' : '';
        $allowSizeChecked = $allowSize ? 'checked' : '';
        
        return "
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>" . LANG_FIELD_ICONSTYLE_DEFAULT_COLOR_LABEL . "</label>
                        <div class='d-flex align-items-center gap-2'>
                            <input type='color' class='form-control form-control-color' name='config[default_color]' value='{$defaultColor}' style='width: 50px;'>
                            <input type='text' class='form-control' name='config[default_color]' value='{$defaultColor}' placeholder='#000000'>
                        </div>
                        <div class='form-text'>" . LANG_FIELD_ICONSTYLE_DEFAULT_COLOR_HINT . "</div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>" . LANG_FIELD_ICONSTYLE_DEFAULT_SIZE_LABEL . "</label>
                        <input type='number' class='form-control' name='config[default_size]' value='{$defaultSize}' min='8' max='128'>
                        <div class='form-text'>" . LANG_FIELD_ICONSTYLE_DEFAULT_SIZE_HINT . "</div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-4'>
                    <div class='mb-3'>
                        <label class='form-label'>" . LANG_FIELD_ICONSTYLE_SIZE_MIN_LABEL . "</label>
                        <input type='number' class='form-control' name='config[size_min]' value='{$sizeMin}' min='4' max='64'>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='mb-3'>
                        <label class='form-label'>" . LANG_FIELD_ICONSTYLE_SIZE_MAX_LABEL . "</label>
                        <input type='number' class='form-control' name='config[size_max]' value='{$sizeMax}' min='16' max='256'>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='mb-3'>
                        <label class='form-label'>" . LANG_FIELD_ICONSTYLE_SIZE_STEP_LABEL . "</label>
                        <input type='number' class='form-control' name='config[size_step]' value='{$sizeStep}' min='1' max='10'>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' name='config[allow_color]' id='allow_color' value='1' {$allowColorChecked}>
                            <label class='form-check-label' for='allow_color'>
                                " . LANG_FIELD_ICONSTYLE_ALLOW_COLOR_LABEL . "
                            </label>
                        </div>
                        <div class='form-text'>" . LANG_FIELD_ICONSTYLE_ALLOW_COLOR_HINT . "</div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' name='config[allow_size]' id='allow_size' value='1' {$allowSizeChecked}>
                            <label class='form-check-label' for='allow_size'>
                                " . LANG_FIELD_ICONSTYLE_ALLOW_SIZE_LABEL . "
                            </label>
                        </div>
                        <div class='form-text'>" . LANG_FIELD_ICONSTYLE_ALLOW_SIZE_HINT . "</div>
                    </div>
                </div>
            </div>
        ";
    }
    
    /**
    * Возвращает шорткод для поля
    * @return string Имя шорткода
    */
    public function getShortcode(): string {
        $systemName = $this->getSystemName();
        $entityType = $this->getEntityType();
        
        $shortcodeName = $entityType . '_' . $systemName;
        
        if (class_exists('Shortcodes')) {
            Shortcodes::add($shortcodeName, function($attrs) use ($systemName, $entityType) {
                return $this->renderShortcode($attrs);
            });
        }
        
        return $shortcodeName;
    }
    
    /**
    * Форматирует значение для шорткода
    * @param mixed $value Значение поля
    * @param array $attrs Атрибуты шорткода
    * @return string Отформатированное значение
    */
    protected function formatShortcodeValue($value, $attrs): string {
        $decoded = $this->decodeValue($value);
        
        $iconSet = $decoded['set'] ?? '';
        $iconName = $decoded['name'] ?? '';
        $iconColor = $decoded['color'] ?? $this->config['default_color'] ?? '#000000';
        $iconSize = $decoded['size'] ?? $this->config['default_size'] ?? 24;
        
        if (isset($attrs['color'])) $iconColor = $attrs['color'];
        if (isset($attrs['size'])) $iconSize = (int)$attrs['size'];
        if (isset($attrs['set'])) $iconSet = $attrs['set'];
        
        if (empty($iconName) || empty($iconSet)) return '';
        
        if (isset($attrs['return']) && $attrs['return'] === 'array') {
            return json_encode(['set' => $iconSet, 'name' => $iconName, 'color' => $iconColor, 'size' => $iconSize]);
        }
        
        if (isset($attrs['return']) && $attrs['return'] === 'css') {
            return "background-image: url('" . BASE_URL . "/templates/default/admin/icons/{$iconSet}.svg#{$iconName}');";
        }
        
        return $this->getIconPreviewHtml($iconSet, $iconName, $iconColor, $iconSize);
    }
}