<?php

/**
* Абстрактный базовый класс для всех типов HTML-блоков 
* @package core
* @author BloggyCMS Team
* @version 1.0.0
*/
abstract class BaseHtmlBlock {

    /** @var string|null Каталог темы, поставляющей этот класс */
    protected $sourceTemplate = null;

    /**
    * Устанавливает каталог темы-источника для fallback-шаблонов.
    */
    public function setSourceTemplate(?string $template): void {
        $this->sourceTemplate = $template;
    }

    /**
    * Возвращает название блока для отображения в админ-панели
    * @return string Название блока
    */
    abstract public function getName(): string;

    /**
    * Возвращает системное имя блока 
    * @return string Системное имя блока
    */
    abstract public function getSystemName(): string;

    /**
    * Возвращает подробное описание блока 
    * @return string Описание блока
    */
    abstract public function getDescription(): string;

    /**
    * Возвращает иконку блока 
    * Может быть классом Bootstrap Icons (bi bi-box) или путем к изображению.
    * @return string Класс иконки или путь к изображению
    */
    public function getIcon(): string {
        return 'bi bi-box';
    }

    /**
    * Возвращает имя автора блока
    * @return string Имя автора
    */
    public function getAuthor(): string {
        return 'BloggyCMS Team';
    }

    /**
    * Возвращает версию блока 
    * @return string Версия в формате x.y.z
    */
    public function getVersion(): string {
        return '1.0.0';
    }

    /**
    * Возвращает сайт автора блока
    * @return string URL сайта автора (может быть пустым)
    */
    public function getAuthorWebsite(): string {
        return '';
    }

    /**
    * Возвращает краткое описание блока для списка 
    * @return string Краткое описание
    */
    public function getShortDescription(): string {
        return $this->getDescription();
    }

    /**
    * Возвращает HTML-форму настроек блока
    * Используется в админ-панели для конфигурации блока.
    * Должна быть реализована в каждом конкретном блоке. 
    * @param array $currentSettings Текущие настройки блока (если есть)
    * @return string HTML-код формы настроек
    */
    abstract public function getSettingsForm($currentSettings = []): string;

    /**
    * Рендерит блок на фронтенде 
    * На основе настроек и выбранного шаблона генерирует HTML-код блока.
    * @param array $settings Настройки блока
    * @param string|null $templateName Имя шаблона (если null, берется из настроек или 'default')
    * @return string HTML-код блока
    */
    public function processFrontend($settings = [], $templateName = null): string {
        $template = $templateName ?? ($settings['template'] ?? 'default');
        return $this->renderFromTemplate($settings, $template);
    }

    /**
    * Рендерит блок из файла шаблона
    * Ищет подходящий файл шаблона, подключает его и возвращает результат.
    * Если указанный шаблон не найден, пробует использовать 'default'. 
    * @param array $settings Настройки блока
    * @param string $templateName Имя шаблона
    * @return string HTML-код блока
    */
    protected function renderFromTemplate($settings = [], $templateName = 'default'): string {
        $templatePath = $this->findTemplatePath($templateName);
        
        if ($templatePath && file_exists($templatePath)) {
            extract([
                'settings' => $settings, 
                'block' => $this,
                'templateName' => $templateName
            ], EXTR_SKIP);
            
            ob_start();
            include $templatePath;
            return ob_get_clean();
        }
        
        if ($templateName !== 'default') {
            return $this->renderFromTemplate($settings, 'default');
        }
        
        return $this->getFallbackContent($settings);
    }
    
    /**
    * Ищет путь к файлу шаблона
    * @param string $templateName Имя шаблона (файла)
    * @return string|null Путь к файлу шаблона или null если не найден
    */
    protected function findTemplatePath($templateName = 'default'): ?string {
        $systemName = $this->getSystemName();
        $preferredTemplate = $this->getTemplate();
        $templateCandidates = [];

        if ($preferredTemplate && strcasecmp($preferredTemplate, 'all') !== 0) {
            $templateCandidates[] = $preferredTemplate;
        }
        $templateCandidates[] = get_current_template();
        if (!empty($this->sourceTemplate)) {
            $templateCandidates[] = $this->sourceTemplate;
        }
        $templateCandidates[] = 'default';
        $templateCandidates = array_values(array_unique($templateCandidates));

        foreach ($templateCandidates as $templateDirectory) {
            $path = BASE_PATH . "/templates/{$templateDirectory}/front/assets/html_blocks/{$systemName}/{$templateName}.php";
            if (is_file($path)) {
                return $path;
            }
        }

        foreach ($templateCandidates as $templateDirectory) {
            $legacyPath = BASE_PATH . "/templates/{$templateDirectory}/front/html_blocks/{$systemName}.php";
            if (is_file($legacyPath)) {
                return $legacyPath;
            }
        }

        return null;
    }
    
    /**
    * Возвращает заглушку, когда шаблон не найден
    * @param array $settings Настройки блока
    * @return string HTML-код сообщения об ошибке
    */
    protected function getFallbackContent($settings): string {
        return sprintf(LANG_CORE_BASEHTMLBLOCK_TEMPLATE_NOT_FOUND, $this->getName());
    }

    /**
    * Возвращает массив CSS файлов для админ-панели
    * @return array Массив путей к CSS файлам
    */
    public function getAdminCss(): array {
        return [];
    }

    /**
    * Возвращает массив JavaScript файлов для админ-панели
    * @return array Массив путей к JS файлам
    */
    public function getAdminJs(): array {
        return [];
    }

    /**
    * Возвращает массив CSS файлов для фронтенда
    * @return array Массив путей к CSS файлам
    */
    public function getFrontendCss(): array {
        return [];
    }

    /**
    * Возвращает массив JavaScript файлов для фронтенда
    * @return array Массив путей к JS файлам
    */
    public function getFrontendJs(): array {
        return [];
    }

    /**
    * Возвращает инлайн CSS код для фронтенда
    * @return string CSS код
    */
    public function getFrontendInlineCss(): string {
        return '';
    }

    /**
    * Возвращает инлайн JavaScript код для фронтенда
    * @return string JavaScript код
    */
    public function getFrontendInlineJs(): string {
        return '';
    }

    /**
    * Валидирует настройки блока перед сохранением
    * @param array $settings Настройки для валидации
    * @return array Массив [bool $isValid, array $errors]
    */
    public function validateSettings($settings): array {
        return [true, []];
    }

    /**
    * Подготавливает настройки перед сохранением в базу данных 
    * @param array $settings Исходные настройки из формы
    * @return array Подготовленные настройки
    */
    public function prepareSettings($settings): array {
        return $settings;
    }

    /**
    * Возвращает область доступности блока: ID темы или 'all'.
    * Пустая строка также означает доступность для всех тем.
    * Для тематического типа менеджер проверяет, что область совпадает с темой-владельцем.
    * Используется также как первый кандидат при поиске шаблона рендеринга.
    * @return string Название шаблона темы или 'all'
    */
    public function getTemplate(): string {
        return 'all';
    }

    /**
    * Возвращает системные CSS файлы
    * Эти файлы подключаются автоматически и не могут быть удалены
    * через интерфейс управления ресурсами.
    * @return array Массив путей к CSS файлам
    */
    public function getSystemCss(): array {
        return [];
    }
    
    /**
    * Возвращает системные JavaScript файлы
    * Эти файлы подключаются автоматически и не могут быть удалены
    * через интерфейс управления ресурсами. 
    * @return array Массив путей к JS файлам
    */
    public function getSystemJs(): array {
        return [];
    }
    
    /**
    * Возвращает системный инлайн CSS код 
    * Этот код подключается автоматически и не может быть удален
    * через интерфейс управления ресурсами. 
    * @return string CSS код
    */
    public function getSystemInlineCss(): string {
        return '';
    }
    
    /**
    * Возвращает системный инлайн JavaScript код 
    * Этот код подключается автоматически и не может быть удален
    * через интерфейс управления ресурсами.
    * @return string JavaScript код
    */
    public function getSystemInlineJs(): string {
        return '';
    }

    /**
    * Возвращает варианты отображения, доступные в активной теме и системной теме fallback.
    * Вариант отображения не является областью доступности типа.
    * @return array Ассоциативный массив доступных вариантов
    */
    public function getAvailableTemplates(): array {
        $templates = [];
        $systemName = $this->getSystemName();
        $templatesDir = BASE_PATH . '/templates';
        $templateDirs = array_values(array_unique(array_filter([
            get_current_template(),
            $this->sourceTemplate,
            'default'
        ])));

        foreach ($templateDirs as $templateDir) {
            $blockDir = $templatesDir . '/' . $templateDir . '/front/assets/html_blocks/' . $systemName;
            if (is_dir($blockDir)) {
                foreach (glob($blockDir . '/*.php') ?: [] as $file) {
                    $templateName = pathinfo($file, PATHINFO_FILENAME);
                    $templates[$templateName] = $this->getTemplateDescription($templateName, $file);
                }
            }

            $legacyFile = $templatesDir . '/' . $templateDir . '/front/html_blocks/' . $systemName . '.php';
            if (is_file($legacyFile)) {
                $description = $this->getTemplateDescription('default', $legacyFile);
                $templates['default'] = $description . ' [' . $templateDir . '] (legacy)';
            }
        }

        if (empty($templates)) {
            $templates['default'] = LANG_CORE_BASEHTMLBLOCK_DEFAULT_TEMPLATE;
        }

        return $templates;
    }

    /**
    * Извлекает описание шаблона из PHPDoc комментария файла
    * @param string $templateName Имя шаблона
    * @param string $filePath Путь к файлу шаблона
    * @return string Описание шаблона или имя файла, если описание не найдено
    */
    protected function getTemplateDescription($templateName, $filePath): string {
        if (!file_exists($filePath)) {
            return $templateName;
        }
        
        $content = file_get_contents($filePath);
        if (preg_match('/\/\*\*\s*(.*?)\s*\*\//s', $content, $matches)) {
            $lines = explode("\n", $matches[1]);
            foreach ($lines as $line) {
                $line = trim($line, " *\t\r\n\0\x0B");
                if (strpos($line, '@') === false && !empty($line)) {
                    return $line;
                }
            }
        }
        
        return $templateName;
    }

    /**
    * Возвращает путь к директории с шаблонами блока 
    * @deprecated 1.0.0 Используйте findTemplatePath() вместо этого метода
    * @return string Путь к директории (всегда пустая строка)
    */
    protected function getTemplatesDirectory(): string {
        return '';
    }

    /**
    * Возвращает путь к файлу шаблона 
    * @deprecated 1.0.0 Используйте findTemplatePath() вместо этого метода
    * @param string $templateName Имя шаблона
    * @return string Путь к файлу или пустая строка
    */
    protected function getTemplatePath($templateName = 'default'): string {
        $path = $this->findTemplatePath($templateName);
        return $path ?: '';
    }
}