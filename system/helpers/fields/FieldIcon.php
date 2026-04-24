<?php

/**
* Поле типа "иконка" для системы полей
* @package Fields
*/
class FieldIcon extends Field {
    
    /**
    * Рендерит HTML-код поля для выбора иконки
    * @param mixed $currentValue Текущее значение поля (формат: "набор:имя_иконки")
    * @return string HTML-код поля
    */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        $iconParts = $this->parseIconValue($value);
        $iconSet = $iconParts['set'] ?? '';
        $iconName = $iconParts['name'] ?? '';
        
        $previewHtml = $this->getIconPreview($iconSet, $iconName);
        
        $iconsPageUrl = $this->options['icons_page_url'] ?? BASE_URL . '/admin/icons';
        
        $modalId = 'iconPickerModal_' . $this->name . '_' . uniqid();
        
        ob_start();
        ?>
        <div class="icon-field-wrapper" 
             data-field-name="<?= $this->name ?>"
             data-icons-page-url="<?= htmlspecialchars($iconsPageUrl) ?>"
             data-modal-id="<?= $modalId ?>">
            
            <input type="hidden" 
                   name="settings[<?= $this->name ?>]" 
                   value="<?= htmlspecialchars($value) ?>" 
                   class="icon-hidden-input">
            
            <div class="icon-preview-container mb-2">
                <?php if (!empty($previewHtml)): ?>
                <div class="current-icon-preview">
                    <div class="icon-preview-large" style="font-size: 2rem;">
                        <?= $previewHtml ?>
                    </div>
                    <div class="mt-1">
                        <small class="text-muted icon-code">
                            <?= htmlspecialchars($iconSet . ':' . $iconName) ?>
                        </small>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-icon-placeholder text-muted text-center py-3">
                    <i class="bi bi-question-circle fs-3"></i>
                    <div class="mt-1">
                        <small><?php echo LANG_HELPER_FIELDS_ICON_NOT_SELECTED; ?></small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" 
                        class="btn btn-outline-primary btn-sm icon-select-btn"
                        data-bs-toggle="modal" 
                        data-bs-target="#<?= $modalId ?>">
                    <i class="bi bi-images me-1"></i>
                    <?php echo LANG_HELPER_FIELDS_ICON_SELECT_BTN; ?>
                </button>
                
                <?php if (!empty($value)): ?>
                <button type="button" 
                        class="btn btn-outline-danger btn-sm icon-clear-btn">
                    <i class="bi bi-x-circle me-1"></i>
                    <?php echo LANG_HELPER_FIELDS_ICON_CLEAR_BTN; ?>
                </button>
                <?php endif; ?>
            </div>
            
            <div class="modal fade icon-picker-modal" 
                 id="<?= $modalId ?>" 
                 tabindex="-1" 
                 aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-emoji-smile me-2"></i>
                                <?php echo LANG_HELPER_FIELDS_ICON_MODAL_TITLE; ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="p-3 border-bottom">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control icon-search-input" 
                                           placeholder="<?php echo LANG_HELPER_FIELDS_ICON_SEARCH_PLACEHOLDER; ?>"
                                           data-modal-id="<?= $modalId ?>">
                                </div>
                            </div>
                            
                            <div class="icon-modal-content" 
                                 data-field-name="<?= $this->name ?>"
                                 data-modal-id="<?= $modalId ?>">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden"><?php echo LANG_HELPER_FIELDS_ICON_LOADING; ?></span>
                                    </div>
                                    <p class="mt-2"><?php echo LANG_HELPER_FIELDS_ICON_LOADING_TEXT; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo LANG_HELPER_FIELDS_ICON_CANCEL_BTN; ?></button>
                            <button type="button" class="btn btn-primary icon-select-confirm-btn" disabled>
                                <?php echo LANG_HELPER_FIELDS_ICON_SELECT_CONFIRM_BTN; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        
        $fieldHtml = ob_get_clean();
        return $this->renderFieldGroup($fieldHtml);
    }
    
    /**
    * Разбирает значение иконки на набор и имя
    * @param string $value Значение поля
    * @return array Ассоциативный массив с ключами 'set' и 'name'
    */
    private function parseIconValue($value) {
        if (empty($value) || !is_string($value)) {
            return ['set' => '', 'name' => ''];
        }
        
        $parts = explode(':', $value, 2);
        if (count($parts) === 2) {
            return ['set' => $parts[0], 'name' => $parts[1]];
        }
        
        return ['set' => '', 'name' => $value];
    }
    
    /**
    * Генерирует HTML для превью иконки
    * @param string $set Набор иконок
    * @param string $name Имя иконки
    * @return string HTML-код для отображения иконки
    */
    private function getIconPreview($set, $name) {
        if (empty($set) || empty($name)) {
            return '';
        }
        
        if (function_exists('bloggy_icon')) {
            return bloggy_icon($set, $name, '48 48', 'currentColor', 'icon-preview');
        }
        
        return '<div class="alert alert-info p-2 m-0">' . 
               htmlspecialchars($set . ':' . $name) . 
               '</div>';
    }
    
    /**
    * Переопределяет метод getAttributes для правильного рендеринга 
    * @return string Пустая строка
    */
    protected function getAttributes() {
        return '';
    }
}