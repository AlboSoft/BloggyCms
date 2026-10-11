<?php

/**
* Менеджер для управления постблоками в системе
*/
class PostBlockManager {

    /**
    * @var array Зарегистрированные постблоки
    */
    private $postBlocks = [];
    
    /**
    * @var mixed Подключение к базе данных
    */
    private $db;

    /**
    * Конструктор PostBlockManager
    * @param mixed $db Подключение к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
        $this->loadPostBlocks();
    }

    /**
    * Загружает все постблоки из папки
    */
    private function loadPostBlocks() {
        $baseBlockFile = __DIR__ . '/BasePostBlock.php';
        if (file_exists($baseBlockFile)) {
            require_once $baseBlockFile;
        }

        $blocksDir = __DIR__ . '/../post_blocks';
        if (is_dir($blocksDir)) {
            $files = @scandir($blocksDir) ?: [];
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== 'BasePostBlock.php') {
                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $filePath = $blocksDir . '/' . $file;
                    
                    require_once $filePath;
                    
                    if (class_exists($className)) {
                        $reflection = new ReflectionClass($className);
                        if (!$reflection->isAbstract() && $reflection->isSubclassOf('BasePostBlock')) {
                            $blockInstance = new $className();
                            
                            $this->postBlocks[$className] = [
                                'name' => $blockInstance->getName(),
                                'system_name' => $className,
                                'description' => $blockInstance->getDescription(),
                                'class' => $blockInstance,
                                'icon' => $blockInstance->getIcon(),
                                'author' => $blockInstance->getAuthor(),
                                'version' => $blockInstance->getVersion(),
                                'category' => $blockInstance->getCategory(),
                                'can_use_in_posts' => $blockInstance->canUseInPosts(),
                                'can_use_in_pages' => $blockInstance->canUseInPages()
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
    * Получает настройки блоков из базы данных
    * @return array Настройки блоков
    */
    private function getBlockSettingsFromDB() {
        try {
            $postBlockModel = new PostBlockModel($this->db);
            return $postBlockModel->getAllBlockSettings();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
    * Возвращает все доступные постблоки
    * @return array Постблоки
    */
    public function getPostBlocks(): array {
        return $this->postBlocks;
    }

    /**
    * Возвращает постблоки для использования в постах с учетом настроек из БД
    * @return array Постблоки для постов
    */
    public function getPostBlocksForPosts(): array {
        $dbSettings = $this->getBlockSettingsFromDB();
        
        return array_filter($this->postBlocks, function($block) use ($dbSettings) {
            $systemName = $block['system_name'];
            $dbSetting = $dbSettings[$systemName] ?? null;
            
            if ($dbSetting !== null) {
                return (bool)$dbSetting['enable_in_posts'];
            }
            
            return $block['can_use_in_posts'];
        });
    }

    /**
    * Возвращает постблоки для использования в страницах с учетом настроек из БД
    * @return array Постблоки для страниц
    */
    public function getPostBlocksForPages(): array {
        $dbSettings = $this->getBlockSettingsFromDB();
        
        return array_filter($this->postBlocks, function($block) use ($dbSettings) {
            $systemName = $block['system_name'];
            $dbSetting = $dbSettings[$systemName] ?? null;
            
            if ($dbSetting !== null) {
                return (bool)$dbSetting['enable_in_pages'];
            }
            
            return $block['can_use_in_pages'];
        });
    }

    /**
    * Возвращает конкретный постблок
    * @param string $systemName Системное имя блока
    * @return array|null Данные блока
    */
    public function getPostBlock($systemName) {
        return $this->postBlocks[$systemName] ?? null;
    }

    /**
    * Возвращает экземпляр класса блока
    * @param string $systemName Системное имя блока
    * @return BasePostBlock|null Экземпляр блока
    */
    public function getBlockInstance($systemName) {
        $postBlock = $this->getPostBlock($systemName);
        return $postBlock['class'] ?? null;
    }

    /**
    * Обрабатывает контент блока на фронтенде
    * @param array $content Данные контента
    * @param string $systemName Системное имя блока
    * @param array $settings Настройки блока
    * @return string Обработанный HTML
    */
    public function processPostBlockContent($content, $systemName, $settings = []) {

        $postBlock = $this->getPostBlock($systemName);
        
        if ($postBlock && $postBlock['class']) {
            $result = $postBlock['class']->processFrontend($content, $settings);
            return $result;
        }

        return $content;
    }

    /**
    * Возвращает блоки сгруппированные по категориям
    * @return array Блоки по категориям
    */
    public function getPostBlocksByCategory(): array {
        $categories = [];
        
        foreach ($this->postBlocks as $block) {
            $category = $block['category'] ?? 'other';
            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][] = $block;
        }
        
        return $categories;
    }

    /**
    * Возвращает настройки для JS с учетом фильтрации по типу контента
    * @param string $contentType Тип контента (post/page)
    * @return array Настройки блоков для JS
    */
    public function getPostBlocksForJS($contentType = 'post'): array {
        $blocks = [];
        $dbSettings = $this->getBlockSettingsFromDB();
        
        $filteredBlocks = ($contentType === 'page') 
            ? $this->getPostBlocksForPages()
            : $this->getPostBlocksForPosts();
        
        foreach ($filteredBlocks as $systemName => $block) {
            $dbSetting = $dbSettings[$systemName] ?? null;
            
            $blocks[$systemName] = [
                'name' => $block['name'],
                'system_name' => $systemName,
                'icon' => $block['icon'],
                'description' => $block['description'],
                'category' => $block['category'],
                'enabled_in_posts' => $dbSetting ? (bool)$dbSetting['enable_in_posts'] : $block['can_use_in_posts'],
                'enabled_in_pages' => $dbSetting ? (bool)$dbSetting['enable_in_pages'] : $block['can_use_in_pages']
            ];
        }
        
        return $blocks;
    }

    /**
    * Загружает активы для постблока в админке
    * @param string $systemName Системное имя блока
    */
    public function loadPostBlockAssets($systemName) {
        $postBlock = $this->getPostBlock($systemName);
        if ($postBlock && $postBlock['class']) {
            $blockInstance = $postBlock['class'];
            
            $cssFiles = method_exists($blockInstance, 'getAdminCss') ? $blockInstance->getAdminCss() : [];
            foreach ($cssFiles as $cssFile) {
                if (function_exists('admin_css')) {
                    admin_css($cssFile);
                }
            }
            
            $jsFiles = method_exists($blockInstance, 'getAdminJs') ? $blockInstance->getAdminJs() : [];
            foreach ($jsFiles as $jsFile) {
                if (function_exists('admin_js')) {
                    admin_js($jsFile);
                }
            }
            
            if (method_exists($blockInstance, 'getAdminInlineCss')) {
                $inlineCss = $blockInstance->getAdminInlineCss();
                if (!empty($inlineCss) && function_exists('admin_inline_css')) {
                    admin_inline_css($inlineCss);
                }
            }
            
            if (method_exists($blockInstance, 'getAdminInlineJs')) {
                $inlineJs = $blockInstance->getAdminInlineJs();
                if (!empty($inlineJs) && function_exists('admin_inline_js')) {
                    admin_inline_js($inlineJs);
                }
            }
        }
    }
    
    /**
    * Загружает активы для всех постблоков с обработкой ошибок
    */
    public function loadAllPostBlockAssets() {
        foreach ($this->postBlocks as $systemName => $block) {
            try {
                $this->loadPostBlockAssets($systemName);
            } catch (Exception $e) {

            }
        }
    }
    
    /**
    * Загружает фронтенд ассеты для постблока
    * @param string $systemName Системное имя блока
    */
    public function loadPostBlockFrontendAssets($systemName) {
        $postBlock = $this->getPostBlock($systemName);
        if ($postBlock && $postBlock['class']) {
            $blockInstance = $postBlock['class'];
            
            $cssFiles = method_exists($blockInstance, 'getFrontendCss') ? $blockInstance->getFrontendCss() : [];
            foreach ($cssFiles as $cssFile) {
                if (function_exists('front_css')) {
                    front_css($cssFile);
                }
            }
            
            $jsFiles = method_exists($blockInstance, 'getFrontendJs') ? $blockInstance->getFrontendJs() : [];
            foreach ($jsFiles as $jsFile) {
                if (function_exists('front_js')) {
                    front_js($jsFile);
                }
            }
            
            if (method_exists($blockInstance, 'getFrontendInlineCss')) {
                $inlineCss = $blockInstance->getFrontendInlineCss();
                if (!empty($inlineCss) && function_exists('front_inline_css')) {
                    front_inline_css($inlineCss);
                }
            }
            
            if (method_exists($blockInstance, 'getFrontendInlineJs')) {
                $inlineJs = $blockInstance->getFrontendInlineJs();
                if (!empty($inlineJs) && function_exists('front_inline_js')) {
                    front_inline_js($inlineJs);
                }
            }
        }
    }
    
    /**
    * Загружает фронтенд ассеты для массива блоков
    * @param array $blocksData Данные блоков
    */
    public function loadFrontendAssetsForBlocks(array $blocksData) {
        foreach ($blocksData as $blockData) {
            if (is_array($blockData) && isset($blockData['type'])) {
                $this->loadPostBlockFrontendAssets($blockData['type']);
            }
        }
    }

    /**
    * Получить информацию о всех постблоках для админки
    * @return array Информация о постблоках
    */
    public function getAllPostBlocksInfo() {
        $blocks = [];
        
        foreach ($this->postBlocks as $systemName => $block) {
            $blocks[] = [
                'system_name' => $systemName,
                'name' => $block['name'] ?? $systemName,
                'description' => $block['description'] ?? '',
                'icon' => $block['icon'] ?? 'bi bi-square',
                'category' => $block['category'] ?? 'general',
                'version' => $block['version'] ?? '1.0.0',
                'author' => $block['author'] ?? 'Unknown',
                'can_use_in_posts' => $block['can_use_in_posts'] ?? true,
                'can_use_in_pages' => $block['can_use_in_pages'] ?? true
            ];
        }
        
        return $blocks;
    }

    /**
    * Возвращает все доступные блоки как экземпляры классов
    * @return array Экземпляры блоков
    */
    public function getAllBlocks() {
        $blocks = [];
        foreach ($this->postBlocks as $systemName => $block) {
            if ($block['class']) {
                $blocks[] = $block['class'];
            }
        }
        return $blocks;
    }
}