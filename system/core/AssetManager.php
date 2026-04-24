<?php

/**
* Менеджер для управления CSS и JS ресурсами приложения
*/
class AssetManager {

    /**
    * @var self Единственный экземпляр класса (паттерн Singleton)
    */
    private static $instance;
    
    /**
    * @var array Группы ресурсов по контекстам (frontend/admin)
    */
    private $resources = [
        'frontend' => [
            'css' => [],
            'js' => [],
            'inline_css' => [],
            'inline_js' => [],
            'bottom_js' => []
        ],
        'admin' => [
            'css' => [],
            'js' => [],
            'inline_css' => [],
            'inline_js' => [],
            'bottom_js' => []
        ]
    ];
    
    /**
    * @var array Базовые ресурсы (подключаются первыми)
    */
    private $baseResources = [
        'frontend' => [
            'css' => [],
            'js' => []
        ],
        'admin' => [
            'css' => [],
            'js' => []
        ]
    ];
    
    /**
    * @var array Регистр для отслеживания дубликатов
    */
    private $registry = [
        'frontend_css' => [],
        'frontend_js' => [],
        'admin_css' => [],
        'admin_js' => []
    ];

    /**
    * Получение экземпляра AssetManager (Singleton)
    * @return self Экземпляр класса
    */
    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    /**
    * Нормализует путь для сравнения
    * @param string $path Путь к файлу
    * @return string Нормализованный путь
    */
    private function normalizePath(string $path): string {
        if (defined('BASE_URL') && strpos($path, BASE_URL) === 0) {
            $path = substr($path, strlen(BASE_URL));
        }
        
        $path = trim($path, '/');
        $path = preg_replace('#/+#', '/', $path);
        
        return $path;
    }
    
    /**
    * Преобразует путь в абсолютный URL
    * @param string $path Путь к файлу
    * @return string Абсолютный URL
    */
    private function makeAbsoluteUrl(string $path): string {
        if (preg_match('#^(https?:|//)#', $path)) {
            return $path;
        }
        
        if (strpos($path, '/') === 0) {
            return $path;
        }
        
        return BASE_URL . '/' . ltrim($path, '/');
    }
    
    /**
    * Определяет контекст (админка или фронт)
    * @return string Контекст: 'frontend' или 'admin'
    */
    private function getContext(): string {
        if (defined('ADMIN_URL') && strpos($_SERVER['REQUEST_URI'], ADMIN_URL) === 0) {
            return 'admin';
        }
        return 'frontend';
    }
    
    /**
    * Добавляет CSS файл
    * @param string $path Путь к CSS файлу
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    */
    public function addCss(string $path, ?string $context = null): void {
        $context = $context ?: $this->getContext();
        $normalized = $this->normalizePath($path);
        
        if (!in_array($normalized, $this->registry["{$context}_css"])) {
            $this->resources[$context]['css'][] = $path;
            $this->registry["{$context}_css"][] = $normalized;
        }
    }
    
    /**
    * Добавляет JS файл
    * @param string $path Путь к JS файлу
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    */
    public function addJs(string $path, ?string $context = null): void {
        $context = $context ?: $this->getContext();
        $normalized = $this->normalizePath($path);
        
        if (!in_array($normalized, $this->registry["{$context}_js"])) {
            $this->resources[$context]['js'][] = $path;
            $this->registry["{$context}_js"][] = $normalized;
        }
    }
    
    /**
    * Добавляет инлайн CSS
    * @param string $css CSS код
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    */
    public function addInlineCss(string $css, ?string $context = null): void {
        $context = $context ?: $this->getContext();
        $this->resources[$context]['inline_css'][] = $css;
    }
    
    /**
    * Добавляет инлайн JS
    * @param string $js JavaScript код
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    */
    public function addInlineJs(string $js, ?string $context = null): void {
        $context = $context ?: $this->getContext();
        $this->resources[$context]['inline_js'][] = $js;
    }
    
    /**
    * Добавляет базовый CSS (высокий приоритет)
    * @param string $path Путь к CSS файлу
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    */
    public function addBaseCss(string $path, ?string $context = null): void {
        $context = $context ?: $this->getContext();
        $normalized = $this->normalizePath($path);
        
        if (!in_array($normalized, $this->registry["{$context}_css"])) {
            $this->baseResources[$context]['css'][] = $path;
            $this->registry["{$context}_css"][] = $normalized;
        }
    }
    
    /**
    * Добавляет базовый JS (высокий приоритет)
    * @param string $path Путь к JS файлу
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    */
    public function addBaseJs(string $path, ?string $context = null): void {
        $context = $context ?: $this->getContext();
        $normalized = $this->normalizePath($path);
        
        if (!in_array($normalized, $this->registry["{$context}_js"])) {
            $this->baseResources[$context]['js'][] = $path;
            $this->registry["{$context}_js"][] = $normalized;
        }
    }

    /**
    * Добавляет инлайн JS в конец страницы (перед </body>)
    * @param string $js JavaScript код
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    */
    public function addBottomJs(string $js, ?string $context = null): void {
        $context = $context ?: $this->getContext();
        $this->resources[$context]['bottom_js'][] = $js;
    }
    
    /**
    * Рендерит все CSS для текущего контекста
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    * @return string HTML код для подключения CSS
    */
    public function renderCss(?string $context = null): string {
        $context = $context ?: $this->getContext();
        $html = '';
        
        foreach ($this->baseResources[$context]['css'] as $cssFile) {
            $html .= '<link rel="stylesheet" href="' . 
                    html($this->makeAbsoluteUrl($cssFile)) . '">' . "\n";
        }
        
        foreach ($this->resources[$context]['css'] as $cssFile) {
            $html .= '<link rel="stylesheet" href="' . 
                    html($this->makeAbsoluteUrl($cssFile)) . '">' . "\n";
        }
        
        if (!empty($this->resources[$context]['inline_css'])) {
            $html .= '<style>' . "\n";
            foreach ($this->resources[$context]['inline_css'] as $css) {
                $html .= $css . "\n";
            }
            $html .= '</style>' . "\n";
        }
        
        $this->baseResources[$context]['css'] = [];
        $this->resources[$context]['css'] = [];
        $this->resources[$context]['inline_css'] = [];
        $this->registry["{$context}_css"] = [];
        
        return $html;
    }
    
    /**
    * Рендерит все JS для текущего контекста
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    * @return string HTML код для подключения JS
    */
    public function renderJs(?string $context = null): string {
        $context = $context ?: $this->getContext();
        $html = '';
        
        foreach ($this->baseResources[$context]['js'] as $jsFile) {
            $html .= '<script src="' . 
                    html($this->makeAbsoluteUrl($jsFile)) . '"></script>' . "\n";
        }
        
        foreach ($this->resources[$context]['js'] as $jsFile) {
            $html .= '<script src="' . 
                    html($this->makeAbsoluteUrl($jsFile)) . '"></script>' . "\n";
        }
        
        if (!empty($this->resources[$context]['inline_js'])) {
            $html .= '<script>' . "\n";
            foreach ($this->resources[$context]['inline_js'] as $js) {
                $html .= $js . "\n";
            }
            $html .= '</script>' . "\n";
        }
        
        return $html;
    }

    /**
    * Рендерит JS для конца страницы (перед </body>)
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    * @return string HTML код для JS в конце страницы
    */
    public function renderBottomJs(?string $context = null): string {
        $context = $context ?: $this->getContext();
        
        if (empty($this->resources[$context]['bottom_js'])) {
            return '';
        }
        
        $html = '';
        foreach ($this->resources[$context]['bottom_js'] as $js) {
            $js = trim($js);
            
            if (preg_match('/^\s*<script[^>]*>/i', $js)) {
                $html .= $js . "\n";
            } 
            elseif (preg_match('/<\/script>\s*$/i', $js)) {
                $html .= $js . "\n";
            }
            else {
                $html .= '<script>' . "\n" . $js . "\n" . '</script>' . "\n";
            }
        }
        
        return $html;
    }
    
    /**
    * Рендерит все ресурсы для контекста
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    * @return string HTML код для всех ресурсов
    */
    public function renderAll(?string $context = null): string {
        $context = $context ?: $this->getContext();
        return $this->renderCss($context) . $this->renderJs($context);
    }

    /**
    * Рендерит всё для конца страницы (CSS, JS, Bottom JS)
    * @param string|null $context Контекст ('frontend'/'admin'), null для автоматического определения
    * @return string HTML код для всех ресурсов включая bottom JS
    */
    public function renderAllWithBottom(?string $context = null): string {
        $context = $context ?: $this->getContext();
        return $this->renderCss($context) . $this->renderJs($context) . $this->renderBottomJs($context);
    }
    
    /**
    * Очищает ресурсы для контекста
    * @param string|null $context Контекст ('frontend'/'admin'), null для очистки всех
    */
    public function clear(?string $context = null): void {
        if ($context) {
            $this->resources[$context] = [
                'css' => [], 'js' => [], 
                'inline_css' => [], 'inline_js' => [], 'bottom_js' => []
            ];
            $this->baseResources[$context] = [
                'css' => [], 'js' => []
            ];
        } else {
            $this->resources = [
                'frontend' => ['css' => [], 'js' => [], 'inline_css' => [], 'inline_js' => [], 'bottom_js' => []],
                'admin' => ['css' => [], 'js' => [], 'inline_css' => [], 'inline_js' => [], 'bottom_js' => []]
            ];
            $this->baseResources = [
                'frontend' => ['css' => [], 'js' => []],
                'admin' => ['css' => [], 'js' => []]
            ];
            $this->registry = [
                'frontend_css' => [], 'frontend_js' => [],
                'admin_css' => [], 'admin_js' => []
            ];
        }
    }
    
    /**
    * Получает статистику по ресурсам
    * @return array Массив со статистикой по ресурсам
    */
    public function getStats(): array {
        return [
            'frontend' => [
                'css' => count($this->resources['frontend']['css']),
                'js' => count($this->resources['frontend']['js']),
                'base_css' => count($this->baseResources['frontend']['css']),
                'base_js' => count($this->baseResources['frontend']['js'])
            ],
            'admin' => [
                'css' => count($this->resources['admin']['css']),
                'js' => count($this->resources['admin']['js']),
                'base_css' => count($this->baseResources['admin']['css']),
                'base_js' => count($this->baseResources['admin']['js'])
            ]
        ];
    }
}