<?php

/**
* Абстрактный базовый класс для всех постблоков контента в постах/страницах
*/
abstract class BasePostBlock {

    /**
    * Возвращает название постблока для админки
    * @return string Название постблока
    */
    abstract public function getName(): string;

    /**
    * Возвращает системное имя постблока (должно совпадать с именем класса)
    * @return string Системное имя постблока
    */
    abstract public function getSystemName(): string;

    /**
    * Возвращает описание постблока
    * @return string Описание постблока
    */
    abstract public function getDescription(): string;

    /**
    * Возвращает иконку постблока (класс Bootstrap Icons)
    * @return string CSS класс иконки
    */
    public function getIcon(): string {
        return 'bi bi-square';
    }

    /**
    * Возвращает имя автора постблока
    * @return string Имя автора
    */
    public function getAuthor(): string {
        return 'BloggyCMS';
    }

    /**
    * Возвращает версию постблока
    * @return string Версия постблока
    */
    public function getVersion(): string {
        return '1.0.0';
    }

    /**
    * Возвращает HTML-шаблон постблока с шорткодами для редактора
    * @return string HTML шаблон
    */
    public function getTemplateWithShortcodes(): string {
        return $this->getEditorHtml([], []);
    }

    /**
    * Возвращает форму настроек постблока (HTML)
    * @param array $currentSettings Текущие настройки
    * @return string HTML код формы настроек
    */
    abstract public function getSettingsForm($currentSettings = []): string;

    /**
    * Возвращает форму контента постблока (HTML)
    * @param array $currentContent Текущее содержимое
    * @return string HTML код формы контента
    */
    abstract public function getContentForm($currentContent = []): string;

    /**
    * Возвращает HTML-код постблока для редактора (с шорткодами)
    * @param array $settings Настройки постблока
    * @param array $content Содержимое постблока
    * @return string HTML код для редактора
    */
    abstract public function getEditorHtml($settings = [], $content = []): string;

    /**
    * Обрабатывает постблок на фронтенде с поддержкой пресетов
    * @param array $content Данные постблока
    * @param array $settings Настройки постблока
    * @return string Обработанный HTML постблок
    */
    public function processFrontend($content, $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        $blockSettings = $this->getBlockSettings();
        $template = $blockSettings['template'] ?? $this->getTemplateWithShortcodes();
        return $this->renderWithTemplate($content, $settings, $template);
    }

    protected function getBlockSettings(): array {

        static $cache = [];
        
        $systemName = $this->getSystemName();
        
        if (!isset($cache[$systemName])) {
            try {
                $db = Database::getInstance();
                $model = new PostBlockModel($db);
                $cache[$systemName] = $model->getBlockSettings($systemName);
            } catch (Exception $e) {
                $cache[$systemName] = [
                    'enable_in_posts' => true,
                    'enable_in_pages' => true,
                    'template' => $this->getTemplateWithShortcodes()
                ];
            }
        }
        
        return $cache[$systemName];
    }

    /**
    * Получает шаблон для рендеринга с учетом пресетов
    * @param array $settings Настройки постблока
    * @return string HTML шаблон
    */
    protected function getTemplateForRendering($settings): string {
        $presetId = $settings['preset_id'] ?? null;
        
        if ($presetId) {
            $preset = $this->getPreset($presetId);
            if ($preset && !empty($preset['preset_template'])) {
                return $preset['preset_template'];
            }
        }
        
        return $settings['template'] ?? $this->getTemplateWithShortcodes();
    }

    /**
    * Получить доступные пресеты для этого постблока
    * @return array Массив пресетов
    */
    public function getAvailablePresets() {
        try {
            $db = Database::getInstance();
            $postBlockModel = new PostBlockModel($db);
            return $postBlockModel->getBlockPresets($this->getSystemName());
        } catch (Exception $e) {
            return [];
        }
    }

    /**
    * Получить пресет по ID
    * @param int $presetId ID пресета
    * @return array|null Данные пресета
    */
    public function getPreset($presetId) {
        try {
            $db = Database::getInstance();
            $postBlockModel = new PostBlockModel($db);
            return $postBlockModel->getPreset($presetId);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
    * Получить HTML для выбора пресета в форме контента
    * @param array $currentSettings Текущие настройки
    * @return string HTML код селектора пресетов
    */
    public function getPresetSelector($currentSettings = []): string {
        $presets = $this->getAvailablePresets();
        $currentPresetId = $currentSettings['preset_id'] ?? '';
        
        if (empty($presets)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label"><?php echo LANG_CORE_BASEPOSTBLOCK_PRESET_SELECTOR_LABEL; ?></label>
            <select name="settings[preset_id]" class="form-select preset-selector" 
                    data-block-system-name="<?= html($this->getSystemName()) ?>">
                <option value=""><?php echo LANG_CORE_BASEPOSTBLOCK_PRESET_SELECTOR_NO_PRESET; ?></option>
                <?php foreach ($presets as $preset) { ?>
                    <option value="<?= $preset['id'] ?>" 
                            <?= $currentPresetId == $preset['id'] ? 'selected' : '' ?>
                            data-template="<?= html($preset['preset_template']) ?>">
                        <?= html($preset['preset_name']) ?>
                    </option>
                <?php } ?>
            </select>
            <div class="form-text">
                <?php echo LANG_CORE_BASEPOSTBLOCK_PRESET_SELECTOR_HINT; ?>
            </div>
            
            <div class="preset-preview mt-3 p-3 border rounded bg-light" style="display: none;">
                <h6 class="mb-2"><?php echo LANG_CORE_BASEPOSTBLOCK_PRESET_PREVIEW_TITLE; ?></h6>
                <div class="preset-preview-content small text-muted">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
    * Возвращает форму настроек постблока с учетом пресетов
    * @param array $currentSettings Текущие настройки
    * @return string HTML код формы настроек
    */
    public function getSettingsFormWithPresets($currentSettings = []): string {
        $presetSelector = $this->getPresetSelector($currentSettings);
        $customSettingsForm = $this->getSettingsForm($currentSettings);
        
        return $presetSelector . $customSettingsForm;
    }

    /**
    * Возвращает массив доступных шорткодов для этого постблока
    * @return array Массив шорткодов
    */
    public function getShortcodes(): array {
        return [
            '{block_id}' => LANG_CORE_BASEPOSTBLOCK_SHORTCODE_BLOCK_ID,
            '{block_type}' => LANG_CORE_BASEPOSTBLOCK_SHORTCODE_BLOCK_TYPE,
            '{block_name}' => LANG_CORE_BASEPOSTBLOCK_SHORTCODE_BLOCK_NAME,
            '{custom_class}' => LANG_CORE_BASEPOSTBLOCK_SHORTCODE_CUSTOM_CLASS,
            '{preset_id}' => LANG_CORE_BASEPOSTBLOCK_SHORTCODE_PRESET_ID,
            '{preset_name}' => LANG_CORE_BASEPOSTBLOCK_SHORTCODE_PRESET_NAME
        ];
    }

    /**
    * Возвращает CSS файлы для админки
    * @return array Массив CSS файлов
    */
    public function getAdminCss(): array {
        return [];
    }

    /**
    * Возвращает JS файлы для админки
    * @return array Массив JS файлов
    */
    public function getAdminJs(): array {
        return [];
    }

    /**
    * CSS файлы для фронтенда
    * @return array Массив CSS файлов
    */
    public function getFrontendCss(): array {
        return [];
    }
    
    /**
    * JS файлы для фронтенда
    * @return array Массив JS файлов
    */
    public function getFrontendJs(): array {
        return [];
    }
    
    /**
    * Inline CSS для админки
    * @return string Inline CSS
    */
    public function getAdminInlineCss(): string {
        return '';
    }
    
    /**
    * Inline JS для админки
    * @return string Inline JS
    */
    public function getAdminInlineJs(): string {
        return '';
    }
    
    /**
    * Inline CSS для фронтенда
    * @return string Inline CSS
    */
    public function getFrontendInlineCss(): string {
        return '';
    }
    
    /**
    * Inline JS для фронтенда
    * @return string Inline JS
    */
    public function getFrontendInlineJs(): string {
        return '';
    }
    
    /**
    * Загружает все ресурсы постблока
    * @param bool $isAdmin Для админки или фронтенда
    */
    public function loadAssets(bool $isAdmin = false): void {
        if ($isAdmin) {
            foreach ($this->getAdminCss() as $cssFile) {
                admin_css($cssFile);
            }
            foreach ($this->getAdminJs() as $jsFile) {
                admin_js($jsFile);
            }
            
            $inlineCss = $this->getAdminInlineCss();
            $inlineJs = $this->getAdminInlineJs();
        } else {
            foreach ($this->getFrontendCss() as $cssFile) {
                front_css($cssFile);
            }
            foreach ($this->getFrontendJs() as $jsFile) {
                front_js($jsFile);
            }
            
            $inlineCss = $this->getFrontendInlineCss();
            $inlineJs = $this->getFrontendInlineJs();
        }
        
        if (!empty($inlineCss)) {
            if ($isAdmin) {
                admin_inline_css($inlineCss);
            } else {
                front_inline_css($inlineCss);
            }
        }
        
        if (!empty($inlineJs)) {
            if ($isAdmin) {
                admin_inline_js($inlineJs);
            } else {
                front_inline_js($inlineJs);
            }
        }
    }
    
    /**
    * Автоматически загружает ассеты на фронтенде
    */
    protected function autoLoadFrontendAssets(): void {
        static $loadedBlocks = [];
        
        $blockName = $this->getSystemName();
        
        if (!isset($loadedBlocks[$blockName])) {
            $this->loadAssets(false);
            $loadedBlocks[$blockName] = true;
        }
    }

    /**
    * Валидирует настройки постблока
    * @param array $settings Настройки
    * @return array Массив [результат, сообщения об ошибках]
    */
    public function validateSettings($settings): array {
        return [true, []];
    }

    /**
    * Подготавливает настройки перед сохранением
    * @param array $settings Настройки
    * @return array Подготовленные настройки
    */
    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            foreach ($_POST['settings'] as $key => $value) {
                $settings[$key] = is_string($value) ? trim($value) : $value;
            }
        }
        
        return $settings;
    }

    /**
    * Подготавливает контент перед сохранением
    * @param array $content Контент
    * @return array Подготовленный контент
    */
    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            foreach ($_POST['content'] as $key => $value) {
                $content[$key] = is_string($value) ? trim($value) : $value;
            }
        }
        
        return $content;
    }

    /**
    * Можно ли использовать постблок в постах
    * @return bool Результат проверки
    */
    public function canUseInPosts(): bool {
        return true;
    }

    /**
    * Можно ли использовать постблок в страницах
    * @return bool Результат проверки
    */
    public function canUseInPages(): bool {
        return true;
    }

    /**
    * Возвращает категорию постблока для организации в админке
    * @return string Категория постблока
    */
    public function getCategory(): string {
        return 'basic';
    }

    /**
    * Возвращает содержимое по умолчанию для постблока
    * @return array Контент по умолчанию
    */
    public function getDefaultContent(): array {
        return [];
    }

    /**
    * Возвращает настройки по умолчанию для постблока
    * @return array Настройки по умолчанию
    */
    public function getDefaultSettings(): array {
        return [];
    }

    /**
    * Извлекает данные контента из HTML строки
    * @param string $html HTML строка для парсинга
    * @return array|null Массив с извлеченными данными
    */
    public function extractFromHtml(string $html): ?array {
        $plainText = trim(strip_tags($html));
        if (!empty($plainText)) {
            return ['text' => $plainText];
        }
        return null;
    }
    
    /**
    * Может ли постблок извлекать данные из HTML
    * @return bool Результат проверки
    */
    public function canExtractFromHtml(): bool {
        return true;
    }

    /**
    * Валидирует и нормализует данные контента
    * @param mixed $content Контент
    * @return array Нормализованный контент
    */
    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['text' => $content];
        }
        
        if (!is_array($content)) {
            return ['text' => (string)$content];
        }
        
        return $content;
    }

    /**
    * Валидирует и нормализует настройки
    * @param mixed $settings Настройки
    * @return array Нормализованные настройки
    */
    public function validateAndNormalizeSettings($settings): array {
        if (is_string($settings)) {
            $decoded = json_decode($settings, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        if (!is_array($settings)) {
            return [];
        }
        
        return $settings;
    }

    /**
    * Безопасный рендеринг контента - защита от двойного экранирования
    * @param array $content Контент
    * @param array $settings Настройки
    * @return string Безопасный HTML
    */
    protected function safeRender($content, $settings = []): string {
        return $this->processFrontend($content, $settings);
    }

    /**
    * Рендер с шаблоном с защитой от двойной обработки
    * @param array $content Контент
    * @param array $settings Настройки
    * @param string $template Шаблон
    * @return string Отрендеренный HTML
    */
    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $result = $template;
        
        foreach ($content as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (strpos($result, $placeholder) !== false) {
                if (strip_tags($value) !== $value) {
                    $result = str_replace($placeholder, $value, $result);
                } else {
                    $result = str_replace($placeholder, html($value), $result);
                }
            }
        }
        
        foreach ($settings as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (strpos($result, $placeholder) !== false) {
                $result = str_replace($placeholder, html($value), $result);
            }
        }
        
        return $result;
    }

    /**
    * Проверяет, содержит ли строка HTML теги
    * @param string $string Строка для проверки
    * @return bool Результат проверки
    */
    protected function containsHtml($string): bool {
        return $string !== strip_tags($string);
    }

    /**
    * Возвращает HTML для превью в админке
    * @param array $content Данные контента
    * @param array $settings Настройки постблока
    * @return string HTML код превью
    */
    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $hasContent = $this->hasPreviewContent($content);
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-<?= $this->getSystemName() ?>">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="<?= $this->getIcon() ?>"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong><?= $this->getName() ?></strong>
                                <?php if ($this->hasSettings($settings)) { ?>
                                    <span class="badge bg-info badge-sm"><?php echo LANG_CORE_BASEPOSTBLOCK_PREVIEW_CONFIGURED; ?></span>
                                <?php } ?>
                            </div>
                            <div class="preview-stats">
                                <?= $this->getPreviewStats($content, $settings) ?>
                            </div>
                        </div>
                    </div>
                    <div class="preview-actions">
                        <button type="button" class="btn btn-xs btn-outline-secondary preview-edit-btn" 
                                onclick="postBlocksManager.editBlock('{block_id}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
                
                <div class="preview-body">
                    <?php if ($hasContent) { ?>
                        <?= $this->renderPreviewContent($content, $settings) ?>
                    <?php } else { ?>
                        <div class="preview-empty-state">
                            <i class="<?= $this->getEmptyIcon() ?>"></i>
                            <div class="empty-text"><?= $this->getEmptyText() ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> <?php echo LANG_CORE_BASEPOSTBLOCK_PREVIEW_ADD_CONTENT; ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
                
                <?php if ($hasContent) { ?>
                    <div class="preview-footer">
                        <div class="preview-footer-content">
                            <?php if ($this->hasSettings($settings)) { ?>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-gear"></i> <?php echo LANG_CORE_BASEPOSTBLOCK_PREVIEW_CONFIGURED_LABEL; ?>
                                </span>
                            <?php } ?>
                            <?php if (!empty($settings['preset_name'])) { ?>
                                <span class="badge bg-info">
                                    <i class="bi bi-stars"></i> <?= html($settings['preset_name']) ?>
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
    * Проверяет, есть ли контент для превью
    * @param array $content Контент
    * @return bool Результат проверки
    */
    protected function hasPreviewContent($content): bool {
        if (empty($content)) {
            return false;
        }
        
        foreach ($content as $value) {
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
            if (is_array($value) && !empty($value)) {
                return true;
            }
            if (!empty($value)) {
                return true;
            }
        }
        
        return false;
    }

    /**
    * Проверяет, есть ли настройки
    * @param array $settings Настройки
    * @return bool Результат проверки
    */
    protected function hasSettings($settings): bool {
        if (empty($settings)) {
            return false;
        }
        
        $systemFields = ['preset_id', 'preset_name', 'custom_class'];
        foreach ($settings as $key => $value) {
            if (!in_array($key, $systemFields) && !empty($value)) {
                return true;
            }
        }
        
        return false;
    }

    /**
    * Возвращает статистику для превью
    * @param array $content Контент
    * @param array $settings Настройки
    * @return string Строка статистики
    */
    protected function getPreviewStats($content, $settings): string {
        $stats = [];
        
        if (isset($content['text']) && is_string($content['text'])) {
            $stats[] = sprintf(LANG_CORE_BASEPOSTBLOCK_STATS_CHARS, strlen($content['text']));
        }
        
        if (isset($content['images']) && is_array($content['images'])) {
            $stats[] = sprintf(LANG_CORE_BASEPOSTBLOCK_STATS_IMAGES, count($content['images']));
        }
        
        if (isset($content['items']) && is_array($content['items'])) {
            $stats[] = sprintf(LANG_CORE_BASEPOSTBLOCK_STATS_ITEMS, count($content['items']));
        }
        
        return implode(' · ', $stats);
    }

    /**
    * Рендерит контент для превью
    * @param array $content Контент
    * @param array $settings Настройки
    * @return string HTML код превью
    */
    protected function renderPreviewContent($content, $settings): string {
        if (isset($content['text'])) {
            $text = $content['text'];
            $preview = strip_tags($text);
            $preview = mb_substr($preview, 0, 150);
            
            return '<div class="text-preview">' . nl2br(html($preview)) . 
                   (strlen(strip_tags($text)) > 150 ? '...' : '') . '</div>';
        }
        
        return '<pre class="small text-muted">' . 
               html(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . 
               '</pre>';
    }

    /**
    * Иконка для пустого состояния
    * @return string CSS класс иконки
    */
    protected function getEmptyIcon(): string {
        return 'bi ' . $this->getIcon();
    }

    /**
    * Текст для пустого состояния
    * @return string Текст
    */
    protected function getEmptyText(): string {
        return LANG_CORE_BASEPOSTBLOCK_EMPTY_TEXT;
    }

    /**
    * Нормализует данные контента для совместимости
    * @param mixed $content Контент
    * @return array Нормализованный контент
    */
    protected function normalizeContentData($content): array {
        if (empty($content)) {
            return [];
        }
        
        if (is_string($content)) {
            return ['text' => $content];
        }
        
        if (is_array($content)) {
            $normalized = [];
            
            if (isset($content['content']) && !isset($content['text'])) {
                $normalized['text'] = $content['content'];
            }
            
            if (isset($content['image_url']) && !isset($content['url'])) {
                $normalized['url'] = $content['image_url'];
            }
            
            if (isset($content['alt_text']) && !isset($content['alt'])) {
                $normalized['alt'] = $content['alt_text'];
            }
            
            return array_merge($content, $normalized);
        }
        
        return [];
    }

    /**
    * Подготавливает контент для отображения в превью
    * @param mixed $content Контент
    * @param int $maxLength Максимальная длина
    * @return string Подготовленный текст
    */
    protected function preparePreviewContent($content, $maxLength = 150): string {
        if (is_string($content)) {
            $text = strip_tags($content);
            if (mb_strlen($text) > $maxLength) {
                return mb_substr($text, 0, $maxLength) . '...';
            }
            return $text;
        }
        
        if (is_array($content)) {
            foreach ($content as $value) {
                if (is_string($value) && trim($value) !== '') {
                    return $this->preparePreviewContent($value, $maxLength);
                }
            }
        }
        
        return '';
    }
    
    /**
    * Возвращает упрощенную версию HTML для быстрого превью
    * @param array $content Данные контента
    * @param array $settings Настройки постблока
    * @return string Упрощенный HTML превью
    */
    public function getSimplePreview($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        ob_start();
        ?>
        <div class="post-block-preview-default">
            <div class="preview-header">
                <i class="<?= $this->getIcon() ?>"></i>
                <strong><?= $this->getName() ?></strong>
            </div>
            <?php if (!empty($content)): ?>
                <div class="preview-content">
                    <?php foreach ($content as $key => $value) { ?>
                        <?php if (is_string($value) && !empty(trim($value))) { ?>
                            <div class="preview-item">
                                <small class="text-muted"><?= html($key) ?>:</small>
                                <div class="text-truncate"><?= html(substr($value, 0, 100)) ?></div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php else: ?>
                <div class="text-muted small"><?php echo LANG_CORE_BASEPOSTBLOCK_SIMPLE_PREVIEW_NO_DATA; ?></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Можно ли показывать превью для этого постблока
    * @return bool Результат проверки
    */
    public function canShowPreview(): bool {
        return true;
    }
    
    /**
    * Возвращает CSS для превью в админке
    * @return array Массив CSS файлов
    */
    public function getPreviewCss(): array {
        return [];
    }
    
    /**
    * Возвращает JS для превью в админке
    * @return array Массив JS файлов
    */
    public function getPreviewJs(): array {
        return [];
    }
    
    /**
    * Загружает ассеты для превью
    */
    public function loadPreviewAssets(): void {
        static $loaded = false;
        
        if (!$loaded) {
            foreach ($this->getPreviewCss() as $cssFile) {
                admin_css($cssFile);
            }
            foreach ($this->getPreviewJs() as $jsFile) {
                admin_js($jsFile);
            }
            $loaded = true;
        }
    }
}