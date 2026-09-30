<?php

/**
* Менеджер регистрации, доступности и рендеринга типов HTML-блоков.
* Системные типы находятся в system/html_blocks. Темы могут явно регистрировать
* собственные типы через templates/{theme}/html_blocks/manifest.php.
*/
class HtmlBlockTypeManager {

    /** @var array Зарегистрированные типы блоков, индексированные по system_name */
    private $blockTypes = [];

    /** @var mixed Подключение к базе данных */
    private $db;

    /** @var bool Защита от повторной загрузки метаданных из БД */
    private $metadataLoaded = false;

    public function __construct($db) {
        $this->db = $db;
        $this->loadBlockTypes();
    }

    /**
    * Загружает системные типы и явно зарегистрированные типы установленных тем.
    */
    private function loadBlockTypes(): void {
        $baseBlockFile = __DIR__ . '/../html_blocks/BaseHtmlBlock.php';
        if (is_file($baseBlockFile)) {
            require_once $baseBlockFile;
        }

        $systemBlocksDir = __DIR__ . '/../html_blocks';
        if (is_dir($systemBlocksDir)) {
            $files = scandir($systemBlocksDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === 'BaseHtmlBlock.php') {
                    continue;
                }
                if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') {
                    continue;
                }

                $filePath = $systemBlocksDir . '/' . $file;
                $className = pathinfo($file, PATHINFO_FILENAME);
                $this->registerBlockClass($className, $filePath, 'system', null);
            }
        }

        $this->loadThemeBlockTypes();
    }

    /**
    * Находит манифесты установленных тем и загружает только перечисленные в них классы.
    * Произвольные PHP-файлы из директории темы не сканируются.
    */
    private function loadThemeBlockTypes(): void {
        $templatesDir = defined('TEMPLATES_PATH') ? TEMPLATES_PATH : dirname(__DIR__, 2) . '/templates';
        if (!is_dir($templatesDir)) {
            return;
        }

        foreach (scandir($templatesDir) as $themeName) {
            if ($themeName === '.' || $themeName === '..' || !preg_match('/^[A-Za-z0-9_-]+$/', $themeName)) {
                continue;
            }

            $themePath = realpath($templatesDir . '/' . $themeName);
            if ($themePath === false || !is_dir($themePath)) {
                continue;
            }

            $blocksDir = realpath($themePath . '/html_blocks');
            if ($blocksDir === false || !is_dir($blocksDir)) {
                continue;
            }

            $manifestPath = realpath($blocksDir . '/manifest.php');
            if ($manifestPath === false || !is_file($manifestPath)) {
                continue;
            }

            try {
                $manifest = require $manifestPath;
            } catch (\Throwable $e) {
                error_log('[HTML BLOCKS] Failed to load theme manifest ' . $manifestPath . ': ' . $e->getMessage());
                continue;
            }

            if (!is_array($manifest)) {
                error_log('[HTML BLOCKS] Invalid theme HTML-block manifest: ' . $manifestPath);
                continue;
            }

            $definitions = $manifest['blocks'] ?? [];
            if (!is_array($definitions)) {
                error_log('[HTML BLOCKS] The "blocks" entry must be an array in ' . $manifestPath);
                continue;
            }

            foreach ($definitions as $definition) {
                if (!is_array($definition)) {
                    error_log('[HTML BLOCKS] Invalid block definition in ' . $manifestPath);
                    continue;
                }

                $relativeFile = $definition['file'] ?? '';
                $className = $definition['class'] ?? '';
                if (!is_string($relativeFile) || $relativeFile === '' || !is_string($className) || $className === '') {
                    error_log('[HTML BLOCKS] Each block definition must contain a class and file in ' . $manifestPath);
                    continue;
                }

                $filePath = realpath($blocksDir . '/' . $relativeFile);
                $blocksDirPrefix = rtrim($blocksDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                if ($filePath === false || !is_file($filePath) || strpos($filePath, $blocksDirPrefix) !== 0 ||
                    strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'php') {
                    error_log('[HTML BLOCKS] Invalid theme block file path in ' . $manifestPath . ': ' . $relativeFile);
                    continue;
                }

                $this->registerBlockClass($className, $filePath, 'theme', $themeName);
            }
        }
    }

    /**
    * Загружает и регистрирует один PHP-класс HTML-блока.
    */
    private function registerBlockClass(string $className, string $filePath, string $source, ?string $sourceTemplate): void {
        try {
            require_once $filePath;

            if (!class_exists($className)) {
                error_log('[HTML BLOCKS] Block class not found: ' . $className . ' (' . $filePath . ')');
                return;
            }

            $reflection = new \ReflectionClass($className);
            if ($reflection->isAbstract() || !$reflection->isSubclassOf('BaseHtmlBlock')) {
                return;
            }

            $blockInstance = $reflection->newInstance();
            if (method_exists($blockInstance, 'setSourceTemplate')) {
                $blockInstance->setSourceTemplate($sourceTemplate);
            }
            $systemName = trim((string)$blockInstance->getSystemName());
            $templateScope = trim((string)$blockInstance->getTemplate());

            if ($systemName === '' || !preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $systemName)) {
                error_log('[HTML BLOCKS] Invalid system name returned by ' . $className . ': ' . $systemName);
                return;
            }

            if ($templateScope === '' || strcasecmp($templateScope, 'all') === 0) {
                $templateScope = 'all';
            } elseif (!preg_match('/^[A-Za-z0-9_-]{1,50}$/', $templateScope)) {
                error_log('[HTML BLOCKS] Invalid template scope returned by ' . $className . ': ' . $templateScope);
                return;
            }

            if ($source === 'theme' && $templateScope !== 'all' && strcasecmp($templateScope, (string)$sourceTemplate) !== 0) {
                error_log('[HTML BLOCKS] Theme block ' . $systemName . ' must target "all" or its owning theme "' . $sourceTemplate . '".');
                return;
            }

            if (isset($this->blockTypes[$systemName])) {
                error_log('[HTML BLOCKS] Duplicate system name "' . $systemName . '"; keeping the first registered class.');
                return;
            }

            $dbBlock = $this->db->fetch(
                "SELECT id FROM html_block_types WHERE system_name = ?",
                [$systemName]
            );

            if (!$dbBlock) {
                $this->db->query(
                    "INSERT INTO html_block_types (name, system_name, description, template, is_active) VALUES (?, ?, ?, ?, 1)",
                    [
                        $blockInstance->getName(),
                        $systemName,
                        $blockInstance->getDescription(),
                        $templateScope
                    ]
                );
                $blockId = $this->db->lastInsertId();
            } else {
                $blockId = $dbBlock['id'];
                $this->db->query(
                    "UPDATE html_block_types SET name = ?, description = ?, template = ? WHERE id = ?",
                    [
                        $blockInstance->getName(),
                        $blockInstance->getDescription(),
                        $templateScope,
                        $blockId
                    ]
                );
            }

            $this->blockTypes[$systemName] = [
                'id' => $blockId,
                'name' => $blockInstance->getName(),
                'system_name' => $systemName,
                'description' => $blockInstance->getDescription(),
                'class' => $blockInstance,
                'template' => $templateScope,
                'icon' => $blockInstance->getIcon(),
                'author' => $blockInstance->getAuthor(),
                'version' => $blockInstance->getVersion(),
                'author_website' => $blockInstance->getAuthorWebsite(),
                'short_description' => $blockInstance->getShortDescription(),
                'source' => $source,
                'source_template' => $sourceTemplate,
                'source_path' => $filePath,
                'is_template_compatible' => $this->isTemplateScopeCompatible($templateScope)
            ];
        } catch (\Throwable $e) {
            error_log('[HTML BLOCKS] Failed to register block class ' . $className . ' (' . $filePath . '): ' . $e->getMessage());
        }
    }

    /**
    * Возвращает типы, включенные администратором и совместимые с выбранной темой.
    */
    public function getBlockTypes(): array {
        return array_filter($this->getAllBlockTypes(), function($blockType) {
            return !empty($blockType['is_active']) && $this->isTemplateScopeCompatible($blockType['template'] ?? 'all');
        });
    }

    /**
    * Проверяет глобальный переключатель типа. Совместимость с темой проверяется отдельно.
    */
    public function isBlockTypeActive(string $systemName): bool {
        if ($systemName === 'DefaultBlock') {
            return true;
        }

        $allBlockTypes = $this->getAllBlockTypes();
        return isset($allBlockTypes[$systemName]) && !empty($allBlockTypes[$systemName]['is_active']);
    }

    /**
    * Проверяет, может ли тип использоваться в текущей теме с учетом глобального статуса.
    */
    public function isBlockTypeAvailable(string $systemName): bool {
        return $this->isBlockTypeActive($systemName) && $this->isBlockTypeCompatibleWithCurrentTemplate($systemName);
    }

    /**
    * Проверяет совместимость типа с активной темой без учета глобального переключателя.
    */
    public function isBlockTypeCompatibleWithCurrentTemplate(string $systemName): bool {
        if ($systemName === 'DefaultBlock') {
            return true;
        }

        $allBlockTypes = $this->getAllBlockTypes();
        if (!isset($allBlockTypes[$systemName])) {
            return false;
        }

        return $this->isTemplateScopeCompatible($allBlockTypes[$systemName]['template'] ?? 'all');
    }

    /**
    * Проверяет совместимость области типа с активной темой.
    */
    private function isTemplateScopeCompatible($templateScope): bool {
        $templateScope = trim((string)$templateScope);
        if ($templateScope === '' || strcasecmp($templateScope, 'all') === 0) {
            return true;
        }

        return strcasecmp($templateScope, get_current_template()) === 0;
    }

    /**
    * Возвращает все загруженные типы, в том числе выключенные и предназначенные для других тем.
    */
    public function getAllBlockTypes(): array {
        if ($this->metadataLoaded) {
            return $this->blockTypes;
        }

        foreach ($this->blockTypes as $systemName => &$type) {
            $dbBlock = $this->db->fetch(
                "SELECT is_active FROM html_block_types WHERE system_name = ?",
                [$systemName]
            );

            if ($dbBlock) {
                $type['is_active'] = (bool)$dbBlock['is_active'];
            } else {
                $type['is_active'] = true;
                $this->db->query(
                    "INSERT INTO html_block_types (name, system_name, description, template, is_active) VALUES (?, ?, ?, ?, 1)",
                    [
                        $type['name'] ?? $systemName,
                        $systemName,
                        $type['description'] ?? '',
                        $type['template'] ?? 'all'
                    ]
                );
            }

            $type['is_template_compatible'] = $this->isTemplateScopeCompatible($type['template'] ?? 'all');
        }
        unset($type);

        $this->metadataLoaded = true;
        return $this->blockTypes;
    }

    /**
    * Возвращает конкретный загруженный тип блока.
    */
    public function getBlockType(string $systemName): ?array {
        $this->getAllBlockTypes();
        return $this->blockTypes[$systemName] ?? null;
    }

    /**
    * Загружает CSS и JS файлы типа для формы в админ-панели.
    */
    public function loadBlockAssets(string $systemName): void {
        if (!$this->isBlockTypeAvailable($systemName)) {
            return;
        }

        $blockType = $this->getBlockType($systemName);
        if (!$blockType || empty($blockType['class'])) {
            return;
        }

        foreach ($blockType['class']->getAdminCss() as $cssFile) {
            add_admin_css($cssFile);
        }
        foreach ($blockType['class']->getAdminJs() as $jsFile) {
            add_admin_js($jsFile);
        }
    }

    /**
    * Обрабатывает содержимое блока на фронтенде.
    */
    public function processBlockContent($systemName, $settings = [], $template = null): string {
        if ($systemName === 'DefaultBlock') {
            $html = $settings['html'] ?? '';
            if (function_exists('process_shortcodes')) {
                $html = process_shortcodes($html);
            }
            return (string)$html;
        }

        if (!$this->isBlockTypeAvailable((string)$systemName)) {
            return '';
        }

        $blockType = $this->getBlockType((string)$systemName);
        if (!$blockType || empty($blockType['class'])) {
            return '';
        }

        $templateName = $template ?? ($settings['template'] ?? null);
        $result = $blockType['class']->processFrontend($settings, $templateName);
        return is_array($result) ? implode('', $result) : (string)$result;
    }

    /**
    * Загружает JavaScript-файлы типа для фронтенда. CSS подключается общим кеш-файлом.
    */
    public function loadBlockFrontendAssets(string $systemName): void {
        if (!$this->isBlockTypeAvailable($systemName) || $systemName === 'DefaultBlock') {
            return;
        }

        $blockType = $this->getBlockType($systemName);
        if (!$blockType || empty($blockType['class'])) {
            return;
        }

        $blockInstance = $blockType['class'];
        foreach (array_merge($blockInstance->getSystemJs(), $blockInstance->getFrontendJs()) as $jsFile) {
            if (!empty(trim((string)$jsFile))) {
                add_frontend_js($jsFile);
            }
        }

        if ($blockInstance->getFrontendInlineJs()) {
            add_inline_js($blockInstance->getFrontendInlineJs());
        }
    }

    /**
    * Рендерит блок на фронтенде, предварительно проверив его доступность.
    */
    public function renderBlockFront($systemName, $settings = [], $template = null): string {
        if (!$this->isBlockTypeAvailable((string)$systemName)) {
            return '';
        }

        $this->loadBlockFrontendAssets((string)$systemName);
        return $this->processBlockContent($systemName, $settings, $template);
    }

    /**
    * Загружает активы блоков, используемых сущностью.
    */
    public function loadPageBlocksAssets($entityType, $entityId): void {
        $contentBlockModel = new ContentBlock($this->db);
        $blocks = $contentBlockModel->getForEntity($entityType, $entityId);

        foreach ($blocks as $block) {
            $this->loadBlockFrontendAssets($block['block_type']);
        }
    }

    public function getBlocksCountByType(string $systemName): int {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM html_blocks hb JOIN html_block_types hbt ON hb.type_id = hbt.id WHERE hbt.system_name = ?",
            [$systemName]
        );
        return (int)($result['count'] ?? 0);
    }

    public function deleteBlockType(string $systemName) {
        return $this->db->query("DELETE FROM html_block_types WHERE system_name = ?", [$systemName]);
    }

    public function hasBlocks(string $systemName): bool {
        return $this->getBlocksCountByType($systemName) > 0;
    }

    public function toggleBlockTypeStatus(string $systemName, $status) {
        return $this->db->query(
            "UPDATE html_block_types SET is_active = ? WHERE system_name = ?",
            [(int)$status, $systemName]
        );
    }
}
