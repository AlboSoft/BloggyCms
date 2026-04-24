<?php

namespace settings\actions;

/**
* Абстрактный базовый класс для всех действий модуля настроек
* @package settings\actions
*/
abstract class SettingsAction {
    
    protected $db;
    protected $params;
    protected $controller;
    protected $settingsModel;
    protected $breadcrumbs;
    protected $pageTitle;
    
    /**
    * Конструктор класса действия
    * @param object $db Подключение к базе данных
    * @param array $params Параметры запроса (по умолчанию [])
    */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->settingsModel = new \SettingsModel($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    /**
    * Устанавливает контроллер, вызывающий действие
    * @param object $controller Контроллер
    * @return void
    */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
    * Абстрактный метод выполнения действия
    * @return void
    */
    abstract public function execute();
    
    /**
    * Добавляет элемент в хлебные крошки
    * @param string $title Название элемента
    * @param string|null $url URL элемента
    * @return self
    */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
    * Устанавливает заголовок страницы
    * @param string $title Заголовок
    * @return self
    */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    /**
    * Рендерит шаблон с переданными данными 
    * @param string $template Путь к шаблону относительно папки views
    * @param array $data Данные для передачи в шаблон
    * @throws \Exception Если контроллер не установлен
    * @return void
    */
    protected function render($template, $data = []) {
        if ($this->controller) {
            if (!isset($data['breadcrumbs'])) {
                $data['breadcrumbs'] = $this->breadcrumbs;
            }
            if (!isset($data['title']) && $this->pageTitle) {
                $data['title'] = $this->pageTitle;
            }
            $this->controller->render($template, $data);
        } else {
            throw new \Exception(LANG_ACTION_SETTINGS_BASE_CONTROLLER_NOT_SET);
        }
    }
    
    /**
    * Выполняет перенаправление на указанный URL
    * @param string $url URL для перенаправления
    * @return void
    */
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    /**
    * Возвращает менеджер хлебных крошек 
    * @return \BreadcrumbsManager
    */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }
    
    /**
    * Возвращает настройки по умолчанию для указанной группы
    * @param string $group Ключ группы настроек
    * @return array Массив настроек по умолчанию для указанной группы
    */
    protected function getDefaultSettings($group) {
        $defaults = [
            'general' => [
                'site_name' => LANG_ACTION_SETTINGS_BASE_DEFAULT_SITE_NAME,
                'site_tagline' => '',
                'site_description' => '',
                'favicon' => '',
                'admin_email' => 'admin@example.com',
                'contact_email' => '',
                'site_language' => 'ru',
                'timezone' => 'Europe/Moscow',
                'date_format' => 'd.m.Y',
                'time_format' => 'H:i',
                'meta_keywords' => '',
                'site_author' => '',
                'enable_sitemap' => true,
                'enable_robots_txt' => true,
                'allow_registration' => true,
                'user_email_verification' => false,
                'show_admin_bar' => true,
                'privacy_page' => '',
                'debug_mode' => false,
                'maintenance_mode' => false,
                'maintenance_message' => LANG_ACTION_SETTINGS_BASE_DEFAULT_MAINTENANCE_MESSAGE,
                'error_logging' => 'errors_only',
                'log_level' => 'error'
            ],
            'category' => [
                'category_layout' => 'grid',
                'category_columns' => '3',
                'show_category_images' => true,
                'show_category_descriptions' => true,
                'show_post_counts' => true,
                'category_depth' => '2',
                'category_order' => 'name',
                'show_empty_categories' => false,
                'category_breadcrumbs' => true,
                'show_subcategories' => true,
                'category_title_template' => '{category} - {site_name}',
                'category_description_template' => '',
                'auto_generate_meta' => true,
                'category_canonical_urls' => true,
                'category_base' => 'category',
                'category_url_parents' => false,
                'category_url_case' => 'lowercase',
                'category_posts_per_page' => 12,
                'max_subcategories' => 10,
                'show_featured_posts' => true,
                'category_rss_feeds' => true,
                'show_category_widget' => true,
                'widget_show_counts' => true,
                'widget_hierarchical' => true,
                'widget_categories_count' => 15
            ],
            'site' => [
                'site_template' => 'default',
                'base_url' => BASE_URL
            ],
            'blog' => [
                'posts_per_page' => 10,
                'archive_posts_per_page' => 15,
                'reading_speed' => 200,
                'excerpt_length' => 150,
                'show_reading_time' => true,
                'allow_disable_reading_time' => false,
                'enable_rating' => true,
                'allow_disable_rating' => false,
                'guest_rating' => false,
                'enable_tags' => true,
                'max_tags_per_post' => 10,
                'popular_tags_count' => 20,
                'enable_comments' => true,
                'guest_comments' => false,
                'comment_approval' => true,
                'comment_depth' => 3,
                'comments_per_page' => 20,
                'show_author' => true,
                'show_publish_date' => true,
                'show_views' => true,
                'related_posts' => true,
                'social_sharing' => true,
                'lazy_loading' => true,
                'date_format' => 'd.m.Y',
                'posts_order' => 'newest',
                'enabled_blocks' => []
            ],
            'search' => [
                'search_h1' => LANG_ACTION_SETTINGS_BASE_DEFAULT_SEARCH_H1,
                'search_title' => LANG_ACTION_SETTINGS_BASE_DEFAULT_SEARCH_TITLE,
                'search_per_page' => 10,
                'save_search_result' => true,
                'default_sort' => 'date'
            ],
            'tags' => [
                'tags_display_type' => 'cloud',
                'cloud_font_size' => 'medium',
                'show_tag_counts' => true,
                'tag_colors' => true,
                'tag_animations' => true,
                'tag_min_count' => 1,
                'tag_cloud_limit' => 50,
                'tag_min_font' => 12,
                'tag_max_font' => 24,
                'tag_color_scheme' => 'gradient',
                'tag_sort_order' => 'name',
                'tag_grouping' => 'none',
                'tag_exclude_empty' => true,
                'tag_auto_cleanup' => false,
                'tag_title_template' => 'Тег: {tag} - {site_name}',
                'tag_description_template' => '',
                'tag_noindex_pagination' => true,
                'tag_canonical_urls' => true,
                'tag_base' => 'tag',
                'tag_url_case' => 'lowercase',
                'tag_cyrillic_slugs' => true,
                'tag_slug_separator' => 'hyphen',
                'tag_posts_per_page' => 12,
                'max_tags_per_post' => 10,
                'tag_related_posts' => true,
                'tag_rss_feeds' => true,
                'tag_min_length' => 2,
                'tag_max_length' => 30,
                'show_tag_widget' => true,
                'widget_tag_show_counts' => true,
                'widget_tag_cloud' => true,
                'widget_tags_count' => 20,
                'tag_auto_generation' => false,
                'tag_auto_approval' => false,
                'auto_tags_limit' => 5,
                'tag_min_frequency' => 2
            ],
            'dashboard' => [
                'stat' => true,
                'posts' => true,
                'posts_draft' => true,
                'fast_actions' => true,
                'last_search' => true,
                'top_search' => true,
                'sidebar_bg' => 'bg1.jpg',
                'notification_position' => 'top-right'
            ]
        ];
        
        return $defaults[$group] ?? [];
    }

    /**
    * Обновляет шаблон сайта в конфигурационном файле
    * @param string $template Название нового шаблона
    * @throws \Exception Если файл конфигурации недоступен для записи
    * @return void
    */
    protected function updateConfigTemplate($template) {
        $configFile = BASE_PATH . '/system/config/config.php';
        
        if (!file_exists($configFile) || !is_writable($configFile)) {
            throw new \Exception(LANG_ACTION_SETTINGS_BASE_CONFIG_NOT_WRITABLE);
        }
        
        $content = file_get_contents($configFile);
        $newContent = preg_replace(
            "/define\('DEFAULT_TEMPLATE',\s*'([^']*)'\);/",
            "define('DEFAULT_TEMPLATE', '$template');",
            $content
        );
        
        if ($content !== $newContent) {
            file_put_contents($configFile, $newContent);
        }
    }

    /**
    * Обновляет базовый URL сайта в конфигурационном файле
    * @param string $newUrl Новый базовый URL
    * @throws \Exception Если файл конфигурации недоступен для записи
    * @return void
    */
    protected function updateConfigBaseUrl($newUrl) {
        $configFile = BASE_PATH . '/system/config/config.php';
        
        if (!file_exists($configFile) || !is_writable($configFile)) {
            throw new \Exception(LANG_ACTION_SETTINGS_BASE_CONFIG_NOT_WRITABLE);
        }
        
        $content = file_get_contents($configFile);
        $newUrl = rtrim($newUrl, '/');
        
        $escapedCurrent = preg_quote(BASE_URL, '/');
        
        $newContent = preg_replace(
            "/define\('BASE_URL',\s*'{$escapedCurrent}'\);/",
            "define('BASE_URL', '{$newUrl}');",
            $content
        );
        
        if ($newContent !== $content) {
            file_put_contents($configFile, $newContent);
        }
    }

    /**
    * Обрабатывает настройки резервного копирования шаблонов
    * @param array $postSettings Массив настроек из POST-запроса
    * @return array Обработанный массив настроек
    */
    protected function handleBackupSettings($postSettings) {
        $currentSettings = $this->settingsModel->get('site');
        
        if (!isset($postSettings['template_backups_enabled'])) {
            $postSettings['template_backups_enabled'] = '0';
            
            if (isset($currentSettings['template_backups_count'])) {
                $postSettings['template_backups_count'] = $currentSettings['template_backups_count'];
            }
            if (isset($currentSettings['template_backups_cleanup'])) {
                $postSettings['template_backups_cleanup'] = $currentSettings['template_backups_cleanup'];
            }
        } else {
            $postSettings['template_backups_enabled'] = '1';
            
            if (!isset($postSettings['template_backups_count'])) {
                $postSettings['template_backups_count'] = $currentSettings['template_backups_count'] ?? '5';
            }
            if (!isset($postSettings['template_backups_cleanup'])) {
                $postSettings['template_backups_cleanup'] = $currentSettings['template_backups_cleanup'] ?? 'auto';
            }
        }
        
        return $postSettings;
    }
}