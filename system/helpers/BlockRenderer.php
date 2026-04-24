<?php

/**
* Класс для рендеринга различных типов блоков контента 
* @package Core
*/
class BlockRenderer {
    
    /** @var PostBlockManager|null Менеджер пост-блоков */
    private static $postBlockManager;
    
    /** @var PostBlock|null Модель пост-блоков */
    private static $postBlockModel;
    
    /** @var bool Флаг инициализации */
    private static $initialized = false;
    
    /**
    * Инициализирует менеджеры пост-блоков
    * Получает подключение к БД и создает необходимые объекты
    * @return void
    */
    private static function init() {
        if (!self::$initialized) {
            $db = Database::getInstance();
            self::$postBlockManager = new PostBlockManager($db);
            self::$postBlockModel = new PostBlock($db);
            self::$initialized = true;
        }
    }
    
    /**
    * Рендерит блок с учетом кастомных шаблонов из настроек
    * @param array $blockData Данные блока
    * @return string HTML-код блока или сообщение об ошибке
    */
    public static function render($blockData) {
        if (!is_array($blockData)) {
            return LANG_HELPER_BLOCKRENDERER_INVALID_DATA;
        }
        
        self::init();
        
        if (isset($blockData['type'])) {
            return self::renderPostBlock($blockData);
        } elseif (isset($blockData['block_type'])) {
            return self::renderHtmlBlock($blockData);
        }
        
        return $blockData['content'] ?? '';
    }
    
    /**
    * Рендерит пост-блок через PostBlockManager
    * @param array $blockData Данные пост-блока
    * @return string HTML-код блока
    */
    private static function renderPostBlock($blockData) {
        try {
            $type = $blockData['type'];
            $content = $blockData['content'] ?? [];
            $settings = $blockData['settings'] ?? [];

            if (is_string($content) && (strpos($content, '<') === 0)) {
                return $content;
            }
            
            if (is_string($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $content = $decoded;
                }
            }
            
            if (!is_array($content)) {
                $content = ['text' => (string)$content];
            }

            $dbSettings = self::$postBlockModel->getBlockSettings($type);
            
            $mergedSettings = array_merge($settings, $dbSettings);
            
            if (!empty($dbSettings['template'])) {
                $mergedSettings['template'] = $dbSettings['template'];
            }

            $result = self::$postBlockManager->processPostBlockContent($content, $type, $mergedSettings);
            
            return $result;
            
        } catch (Exception $e) {
            return sprintf(LANG_HELPER_BLOCKRENDERER_POST_BLOCK_ERROR, $e->getMessage());
        }
    }

    /**
    * Извлекает данные контента из HTML строки используя метод блока 
    * @param string $blockType Тип блока
    * @param string $html HTML-строка для парсинга
    * @return array|null Извлеченные данные или null
    */
    private static function extractContentFromHtml($blockType, $html) {
        try {
            $blockInfo = self::$postBlockManager->getPostBlock($blockType);
            
            if ($blockInfo && $blockInfo['class']) {
                $blockInstance = $blockInfo['class'];
                
                if (method_exists($blockInstance, 'extractFromHtml')) {
                    $result = $blockInstance->extractFromHtml($html);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
            
            if ($blockType === 'ListBlock') {
                return self::extractListFromHtml($html);
            }
            
            $plainText = trim(strip_tags($html));
            if (!empty($plainText)) {
                return ['text' => $plainText];
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
    * Извлекает данные списка из HTML
    * @param string $html HTML-код списка
    * @return array|null Данные списка с элементами
    */
    private static function extractListFromHtml($html) {
        if (preg_match('/<(ul|ol)[^>]*>(.*?)<\/\1>/s', $html, $listMatches)) {
            $listType = $listMatches[1];
            $listContent = $listMatches[2];
            preg_match_all('/<li[^>]*>(.*?)<\/li>/s', $listContent, $itemMatches);
            
            $items = [];
            foreach ($itemMatches[1] as $itemContent) {
                $text = trim(strip_tags($itemContent));
                if (!empty($text)) {
                    $items[] = ['text' => $text];
                }
            }
            
            if (!empty($items)) {
                return [
                    'list_type' => $listType,
                    'items' => $items
                ];
            }
        }
        return null;
    }
    
    /**
    * Рендерит HTML блок через HtmlBlockTypeManager (старая система) 
    * @param array $blockData Данные HTML-блока
    * @return string HTML-код блока
    */
    private static function renderHtmlBlock($blockData) {
        try {
            $db = Database::getInstance();
            $blockTypeManager = new HtmlBlockTypeManager($db);
            
            $content = $blockData['content'] ?? '';
            $blockType = $blockData['block_type'] ?? 'DefaultBlock';
            $settings = [];
            
            if (!empty($blockData['settings'])) {
                $settings = is_string($blockData['settings']) 
                    ? json_decode($blockData['settings'], true) 
                    : $blockData['settings'];
            }
            
            if ($blockType !== 'DefaultBlock') {
                $processedContent = $blockTypeManager->processBlockContent($content, $blockType, $settings);
                if (!empty($processedContent)) {
                    $content = $processedContent;
                }
            }
            
            if (class_exists('Shortcodes')) {
                $posts = $db->fetchAll("
                    SELECT p.*, c.name as category_name, GROUP_CONCAT(t.name SEPARATOR ', ') as tags
                    FROM posts p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN post_tags pt ON p.id = pt.post_id
                    LEFT JOIN tags t ON pt.tag_id = t.id
                    WHERE p.status = 'published'
                    GROUP BY p.id
                    ORDER BY p.created_at DESC
                ");
                
                $content = process_shortcodes($content, $posts);
            }
            
            return $content;
            
        } catch (Exception $e) {
            return LANG_HELPER_BLOCKRENDERER_HTML_BLOCK_ERROR;
        }
    }
    
    /**
    * Альтернативный метод для рендера блока
    * @param array $blockData Данные блока
    * @return string HTML-код блока
    */
    public static function renderBlock($blockData) {
        return self::render($blockData);
    }
    
    /**
    * Рендерит массив пост-блоков
    * @param array $blocks Массив блоков
    * @return string HTML-код всех блоков
    */
    public static function renderPostBlocks($blocks) {
        self::init();
        
        $output = '';
        foreach ($blocks as $block) {
            $output .= self::renderPostBlock($block);
        }
        
        return $output;
    }
    
    /**
    * Очищает данные контента от лишнего экранирования 
    * @param mixed $content Данные контента
    * @return mixed Очищенные данные
    */
    private static function cleanContentData($content) {
        if (!is_array($content)) {
            return $content;
        }
        
        foreach ($content as $key => $value) {
            if (is_string($value)) {
                $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                $value = preg_replace('/^"(.*)"$/', '$1', $value);
                $content[$key] = trim($value);
            }
        }
        
        return $content;
    }
}