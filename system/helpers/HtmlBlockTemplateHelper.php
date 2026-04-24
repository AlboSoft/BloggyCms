<?php

/**
* Вспомогательный класс для работы с шаблонами HTML-блоков 
* @package Helpers
*/
class HtmlBlockTemplateHelper {
    
    /**
    * Получает список доступных шаблонов для блока
    * @param string $blockSystemName Системное имя блока
    * @return array Массив шаблонов в формате [имя_файла => описание]
    */
    public static function getAvailableTemplates($blockSystemName): array {
        $templates = [];
        $templatesDir = BASE_PATH . '/templates';
        
        if (is_dir($templatesDir)) {
            $templateDirs = scandir($templatesDir);
            
            foreach ($templateDirs as $templateDir) {
                if ($templateDir === '.' || $templateDir === '..') continue;
                
                $blockDir = $templatesDir . '/' . $templateDir . '/front/assets/html_blocks/' . $blockSystemName;
                
                if (is_dir($blockDir)) {
                    $templateFiles = glob($blockDir . '/*.php');
                    foreach ($templateFiles as $file) {
                        $templateName = pathinfo($file, PATHINFO_FILENAME);
                        $templates[$templateName] = $templateDir . ' - ' . $templateName;
                    }
                }
            }
        }
        
        return $templates;
    }
    
    /**
    * Ищет шаблон блока в доступных темах
    * @param string $blockSystemName Системное имя блока
    * @param string $templateName Имя файла шаблона (без расширения)
    * @param string|null $preferredTemplate Предпочтительная тема
    * @return string|null Полный путь к файлу шаблона или null
    */
    public static function findBlockTemplate($blockSystemName, $templateName = 'default', $preferredTemplate = null): ?string {
        
        if ($preferredTemplate) {
            $path = BASE_PATH . "/templates/{$preferredTemplate}/front/assets/html_blocks/{$blockSystemName}/{$templateName}.php";
            if (file_exists($path)) {
                return $path;
            }
        }
        
        $currentTemplate = get_current_template();
        $path = BASE_PATH . "/templates/{$currentTemplate}/front/assets/html_blocks/{$blockSystemName}/{$templateName}.php";
        if (file_exists($path)) {
            return $path;
        }
        
        $defaultPath = BASE_PATH . "/templates/default/front/assets/html_blocks/{$blockSystemName}/{$templateName}.php";
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }
        
        return null;
    }
    
    /**
    * Рендерит блок из шаблона 
    * @param string $blockSystemName Системное имя блока
    * @param array $settings Настройки блока
    * @param string $templateName Имя файла шаблона (по умолчанию 'default')
    * @return string HTML-код, сгенерированный шаблоном
    */
    public static function renderFromTemplate($blockSystemName, $settings = [], $templateName = 'default'): string {
        $templatePath = self::findBlockTemplate(
            $blockSystemName, 
            $templateName,
            $settings['preferred_template'] ?? null
        );
        
        if ($templatePath && file_exists($templatePath)) {
            extract(['settings' => $settings], EXTR_SKIP);
            
            ob_start();
            include $templatePath;
            return ob_get_clean();
        }
        
        return sprintf(LANG_HELPER_HTMLBLOCKTEMPLATE_TEMPLATE_NOT_FOUND, htmlspecialchars($templateName), htmlspecialchars($blockSystemName));
    }
}