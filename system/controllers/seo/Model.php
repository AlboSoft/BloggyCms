<?php
/**
* Модель для работы с SEO-настройками
* Управляет robots.txt, sitemap.xml, RSS-лентами и мета-тегами
*
* @package Models
*/
class SeoModel implements ModelAPI {
    use APIAware;
    
    protected $allowedAPIMethods = [
        'getRobotsSettings',
        'getSitemapSettings',
        'getRssSettings',
        'getMetaSettings',
        'saveSettings',
        'generateSitemap',
        'generateRobots',
        'generateRss'
    ];

    /** @var \Database Подключение к базе данных */
    private $db;

    /** @var \SettingsModel Модель настроек */
    private $settingsModel;

    /**
    * Конструктор модели
    */
    public function __construct($db) {
        $this->db = $db;
        $this->settingsModel = new \SettingsModel($db);
    }

    /**
    * Получение настроек robots.txt
    */
    public function getRobotsSettings() {
        $default = [
            'enabled' => true,
            'disallow_paths' => [
                '/admin/',
                '/system/',
                '/uploads/temp/',
                '/cache/'
            ],
            'allow_paths' => [],
            'sitemap_url' => '',
            'crawl_delay' => '',
            'user_agents' => []
        ];
        
        $settings = $this->settingsModel->get('seo_robots', []);
        return array_merge($default, $settings);
    }

    /**
    * Получение настроек sitemap
    */
    public function getSitemapSettings() {
        $default = [
            'enabled' => true,
            'include_posts' => true,
            'include_pages' => true,
            'include_categories' => true,
            'include_tags' => true,
            'include_users' => false,
            'posts_priority' => 0.8,
            'pages_priority' => 0.9,
            'categories_priority' => 0.7,
            'tags_priority' => 0.6,
            'users_priority' => 0.5,
            'change_frequency_posts' => 'weekly',
            'change_frequency_pages' => 'monthly',
            'change_frequency_categories' => 'weekly',
            'change_frequency_tags' => 'weekly',
            'max_posts' => 1000,
            'cache_enabled' => true,
            'cache_lifetime' => 3600
        ];
        
        $settings = $this->settingsModel->get('seo_sitemap', []);
        return array_merge($default, $settings);
    }

    /**
    * Получение настроек RSS
    */
    public function getRssSettings() {
        $default = [
            'enabled' => true,
            'posts_enabled' => true,
            'posts_limit' => 20,
            'categories_enabled' => true,
            'tags_enabled' => true,
            'include_full_content' => false,
            'include_images' => true,
            'copyright' => '',
            'language' => 'ru-ru',
            'ttl' => 60
        ];
        
        $settings = $this->settingsModel->get('seo_rss', []);
        return array_merge($default, $settings);
    }

    /**
    * Получение общих SEO настроек
    */
    public function getMetaSettings() {
        $default = [
            'default_title' => '',
            'default_description' => '',
            'default_keywords' => '',
            'title_separator' => '-',
            'title_format' => '{title} {separator} {site_name}',
            'canonical_enabled' => true,
            'og_enabled' => true,
            'twitter_enabled' => true,
            'schema_enabled' => true,
            'robots_meta_default' => 'index, follow'
        ];
        
        $settings = $this->settingsModel->get('seo_meta', []);
        return array_merge($default, $settings);
    }

    /**
    * Сохранение настроек
    */
    public function saveSettings($group, $settings) {
        return $this->settingsModel->save($group, $settings);
    }

    /**
    * Генерация sitemap.xml
    */
    public function generateSitemap() {
        $settings = $this->getSitemapSettings();
        
        if (!$settings['enabled']) {
            return '';
        }
        
        $baseUrl = $this->getBaseUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Главная страница
        $xml .= $this->createUrlEntry($baseUrl, date('Y-m-d'), 'daily', 1.0);
        
        // Посты
        if ($settings['include_posts']) {
            $xml .= $this->generatePostsSitemap($settings);
        }
        
        // Страницы
        if ($settings['include_pages']) {
            $xml .= $this->generatePagesSitemap($settings);
        }
        
        // Категории
        if ($settings['include_categories']) {
            $xml .= $this->generateCategoriesSitemap($settings);
        }
        
        // Теги
        if ($settings['include_tags']) {
            $xml .= $this->generateTagsSitemap($settings);
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
    * Генерация записей для постов
    */
    private function generatePostsSitemap($settings) {
        $baseUrl = $this->getBaseUrl();
        $limit = (int)$settings['max_posts'];
        $priority = (float)$settings['posts_priority'];
        $changefreq = $settings['change_frequency_posts'];

        $posts = $this->db->fetchAll("
            SELECT id, slug, created_at, updated_at, featured_image
            FROM posts
            WHERE status = 'published'
            ORDER BY created_at DESC
            LIMIT ?
        ", [$limit]);

        $xml = '';
        foreach ($posts as $post) {
            $loc = $baseUrl . '/post/' . $post['slug'];
            $lastmod = !empty($post['updated_at']) ? $post['updated_at'] : $post['created_at'];
            $skip = false;
            \Event::trigger('seo.sitemap.post_url', [
                'post' => $post,
                'url' => &$loc,
                'skip' => &$skip
            ]);
            
            if ($skip) continue;

            $xml .= $this->createUrlEntry($loc, $lastmod, $changefreq, $priority);
        }

        return $xml;
    }

    /**
    * Генерация записей для страниц
    */
    private function generatePagesSitemap($settings) {
        $baseUrl = $this->getBaseUrl();
        $priority = (float)$settings['pages_priority'];
        $changefreq = $settings['change_frequency_pages'];

        $pages = $this->db->fetchAll("
            SELECT id, slug, created_at, updated_at
            FROM pages
            WHERE status = 'published'
            ORDER BY created_at DESC
        ");

        $xml = '';
        foreach ($pages as $page) {
            $loc = $baseUrl . '/page/' . $page['slug'];
            $lastmod = !empty($page['updated_at']) ? $page['updated_at'] : $page['created_at'];
            
            $skip = false;
            \Event::trigger('seo.sitemap.page_url', [
                'page' => $page,
                'url' => &$loc,
                'skip' => &$skip
            ]);
            
            if ($skip) continue;

            $xml .= $this->createUrlEntry($loc, $lastmod, $changefreq, $priority);
        }

        return $xml;
    }

    /**
    * Генерация записей для категорий
    */
    private function generateCategoriesSitemap($settings) {
        $baseUrl = $this->getBaseUrl();
        $priority = (float)$settings['categories_priority'];
        $changefreq = $settings['change_frequency_categories'];

        $categories = $this->db->fetchAll("
            SELECT id, slug, created_at
            FROM categories
            ORDER BY created_at DESC
        ");

        $xml = '';
        foreach ($categories as $category) {
            $loc = $baseUrl . '/category/' . $category['slug'];
            
            $skip = false;
            \Event::trigger('seo.sitemap.category_url', [
                'category' => $category,
                'url' => &$loc,
                'skip' => &$skip
            ]);
            
            if ($skip) continue;

            $xml .= $this->createUrlEntry($loc, $category['created_at'], $changefreq, $priority);
        }

        return $xml;
    }

    /**
    * Генерация записей для тегов
    */
    private function generateTagsSitemap($settings) {
        $baseUrl = $this->getBaseUrl();
        $priority = (float)$settings['tags_priority'];
        $changefreq = $settings['change_frequency_tags'];

        $tags = $this->db->fetchAll("
            SELECT id, slug, created_at
            FROM tags
            ORDER BY created_at DESC
        ");

        $xml = '';
        foreach ($tags as $tag) {
            $loc = $baseUrl . '/tag/' . $tag['slug'];
            
            $skip = false;
            \Event::trigger('seo.sitemap.tag_url', [
                'tag' => $tag,
                'url' => &$loc,
                'skip' => &$skip
            ]);
            
            if ($skip) continue;

            $xml .= $this->createUrlEntry($loc, $tag['created_at'], $changefreq, $priority);
        }

        return $xml;
    }

    /**
    * Создание XML записи URL
    */
    private function createUrlEntry($loc, $lastmod, $changefreq, $priority) {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . $this->escapeXml($loc) . "</loc>\n";
        $xml .= "    <lastmod>" . $this->formatXmlDate($lastmod) . "</lastmod>\n";
        $xml .= "    <changefreq>" . $changefreq . "</changefreq>\n";
        $xml .= "    <priority>" . number_format($priority, 1, '.', '') . "</priority>\n";
        $xml .= "  </url>\n";
        return $xml;
    }

    /**
    * Генерация robots.txt
    */
    public function generateRobots() {
        $settings = $this->getRobotsSettings();
        
        if (!$settings['enabled']) {
            return '';
        }
        
        $baseUrl = $this->getBaseUrl();
        $robots = "# Robots.txt для " . parse_url($baseUrl, PHP_URL_HOST) . "\n";
        $robots .= "# Сгенерировано: " . date('Y-m-d H:i:s') . "\n\n";
        
        $robots .= "User-agent: *\n";
        
        // Disallow
        if (!empty($settings['disallow_paths']) && is_array($settings['disallow_paths'])) {
            foreach ($settings['disallow_paths'] as $path) {
                $path = trim($path);
                if (!empty($path)) {
                    $robots .= "Disallow: " . $path . "\n";
                }
            }
        } else {
            $robots .= "Disallow: /admin/\n";
            $robots .= "Disallow: /system/\n";
        }
        
        // Allow (если есть)
        if (!empty($settings['allow_paths']) && is_array($settings['allow_paths'])) {
            foreach ($settings['allow_paths'] as $path) {
                $path = trim($path);
                if (!empty($path)) {
                    $robots .= "Allow: " . $path . "\n";
                }
            }
        }
        
        // Crawl-delay
        if (!empty($settings['crawl_delay']) && $settings['crawl_delay'] > 0) {
            $robots .= "Crawl-delay: " . (int)$settings['crawl_delay'] . "\n";
        }
        
        // Sitemap
        $sitemapUrl = !empty($settings['sitemap_url']) 
            ? $settings['sitemap_url'] 
            : $baseUrl . '/sitemap.xml';
        $robots .= "\nSitemap: " . $sitemapUrl . "\n";
        
        return $robots;
    }

    /**
    * Генерация RSS ленты
    */
    public function generateRss($type = 'posts', $filter = null) {
        $settings = $this->getRssSettings();
        
        if (!$settings['enabled']) {
            return '';
        }
        
        $baseUrl = $this->getBaseUrl();
        $siteName = 'BloggyCMS';
        
        try {
            $settingsModel = new SettingsModel($this->db);
            $generalSettings = $settingsModel->get('general');
            $siteName = $generalSettings['site_name'] ?? 'BloggyCMS';
        } catch (Exception $e) {}
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        $xml .= "<channel>\n";
        $xml .= "  <title>" . $this->escapeXml($siteName) . "</title>\n";
        $xml .= "  <link>" . $this->escapeXml($baseUrl) . "</link>\n";
        $xml .= "  <description>RSS лента блога</description>\n";
        $xml .= "  <language>" . $settings['language'] . "</language>\n";
        $xml .= "  <ttl>" . (int)$settings['posts_limit'] . "</ttl>\n";
        $xml .= "  <atom:link href=\"" . $this->escapeXml($baseUrl) . "/rss.xml\" rel=\"self\" type=\"application/rss+xml\" />\n";
        
        if (!empty($settings['copyright'])) {
            $xml .= "  <copyright>" . $this->escapeXml($settings['copyright']) . "</copyright>\n";
        }
        
        $posts = $this->getRssItems($type, $filter, $settings);
        
        foreach ($posts as $post) {
            $xml .= $this->createRssItem($post, $settings, $baseUrl);
        }
        
        $xml .= "</channel>\n</rss>";
        
        return $xml;
    }

    /**
    * Получение элементов для RSS
    */
    private function getRssItems($type, $filter, $settings) {
        $limit = (int)$settings['posts_limit'];

        switch ($type) {
            case 'category':
                $categoryId = (int)$filter;
                return $this->db->fetchAll("
                    SELECT p.*, c.name as category_name, c.slug as category_slug,
                           u.username as author_name
                    FROM posts p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.status = 'published' AND p.category_id = ?
                    ORDER BY p.created_at DESC
                    LIMIT ?
                ", [$categoryId, $limit]);

            case 'tag':
                $tagId = (int)$filter;
                return $this->db->fetchAll("
                    SELECT DISTINCT p.*, u.username as author_name
                    FROM posts p
                    LEFT JOIN post_tags pt ON p.id = pt.post_id
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.status = 'published' AND pt.tag_id = ?
                    ORDER BY p.created_at DESC
                    LIMIT ?
                ", [$tagId, $limit]);

            case 'posts':
            default:
                return $this->db->fetchAll("
                    SELECT p.*, c.name as category_name, c.slug as category_slug,
                           u.username as author_name
                    FROM posts p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.status = 'published'
                    ORDER BY p.created_at DESC
                    LIMIT ?
                ", [$limit]);
        }
    }

    /**
    * Создание RSS элемента
    */
    private function createRssItem($post, $settings, $baseUrl) {
        $xml = "  <item>\n";
        $xml .= "    <title>" . $this->escapeXml($post['title']) . "</title>\n";
        $xml .= "    <link>" . $this->escapeXml($baseUrl . '/post/' . $post['slug']) . "</link>\n";
        $xml .= "    <guid isPermaLink=\"true\">" . $this->escapeXml($baseUrl . '/post/' . $post['slug']) . "</guid>\n";
        $xml .= "    <pubDate>" . date('D, d M Y H:i:s O', strtotime($post['created_at'])) . "</pubDate>\n";
        
        $description = $post['short_description'] ?? '';
        if (empty($description) && !empty($post['content'])) {
            $description = strip_tags($post['content']);
            $description = mb_substr($description, 0, 500);
        }
        $xml .= "    <description>" . $this->escapeXml($description) . "</description>\n";
        if ($settings['include_full_content'] && !empty($post['content'])) {
            $content = $post['content'];
            $xml .= "    <content:encoded><![CDATA[" . $content . "]]></content:encoded>\n";
        }

        if (!empty($post['author_name'])) {
            $xml .= "    <author>" . $this->escapeXml($post['author_name']) . "</author>\n";
        }

        if (!empty($post['category_name'])) {
            $xml .= "    <category>" . $this->escapeXml($post['category_name']) . "</category>\n";
        }

        $xml .= "  </item>\n";
        
        return $xml;
    }

    /**
    * Очистка кэша SEO
    */
    public function clearCache() {
        \Event::trigger('seo.cache.clear');
        return true;
    }

    /**
    * Получение базового URL сайта
    * @return string
    */
    private function getBaseUrl() {
        return defined('BASE_URL') ? BASE_URL : 'http://localhost';
    }

    /**
    * Экранирование для XML
    * @param string $string
    * @return string
    */
    protected function escapeXml($string) {
        return html($string, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
    * Форматирование даты для XML
    * @param string|int $timestamp
    * @return string
    */
    private function formatXmlDate($timestamp) {
        if (empty($timestamp)) {
            return date('Y-m-d\TH:i:s+00:00');
        }
        return date('Y-m-d\TH:i:s+00:00', strtotime($timestamp));
    }

    /**
    * Получить настройки IndexNow
    * @return array
    */
    public function getIndexNowSettings() {
        $default = [
            'enabled' => false,
            'ya_key' => $this->generateRandomKey(),
            'bing_key' => $this->generateRandomKey(),
            'notify_error' => true,
            'auto_submit' => true,
            'submit_delay' => 0
        ];
        
        $settings = $this->settingsModel->get('seo_indexnow', []);
        return array_merge($default, $settings);
    }

    /**
    * Сохранить настройки IndexNow
    * @param array $settings
    * @return bool
    */
    public function saveIndexNowSettings($settings) {
        return $this->settingsModel->save('seo_indexnow', $settings);
    }

    /**
    * Генерация случайного ключа для IndexNow
    * 
    * @param int $length
    * @return string
    */
    public function generateRandomKey($length = 32) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-';
        return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
    }

    /**
    * Получить список поисковых систем, поддерживающих IndexNow
    * @return array
    */
    public function getIndexNowEngines() {
        return [
            'ya_key' => [
                'name' => 'Yandex',
                'url' => 'https://yandex.com/indexnow',
                'supports_bulk' => true
            ],
            'bing_key' => [
                'name' => 'Bing',
                'url' => 'https://www.bing.com/indexnow',
                'supports_bulk' => true
            ]
        ];
    }

    /**
    * Отправить URL в IndexNow (синхронно)
    * @param string $key_name Название ключа (ya_key, bing_key)
    * @param string|array $urls URL или массив URL для отправки
    * @return array ['code' => http_code, 'success' => bool, 'error' => string]
    */
    public function sendIndexNowPing($key_name, $urls) {
        $settings = $this->getIndexNowSettings();
        $engines = $this->getIndexNowEngines();
        
        if (!isset($engines[$key_name])) {
            return ['code' => 0, 'success' => false, 'error' => 'Неверный ключ поисковой системы'];
        }
        
        $key = $settings[$key_name] ?? '';
        if (empty($key)) {
            return ['code' => 0, 'success' => false, 'error' => 'Ключ не задан в настройках'];
        }
        
        if (!is_array($urls)) {
            $urls = [$urls];
        }

        $urls = array_map([$this, 'normalizeUrl'], $urls);
        $urls = array_filter($urls);
        
        if (empty($urls)) {
            return ['code' => 0, 'success' => false, 'error' => 'Нет URL для отправки'];
        }
        
        $host = $this->getHost();
        if ($this->isLocalhost($host)) {
            return [
                'code' => 0, 
                'success' => false, 
                'error' => 'IndexNow не работает с локальными доменами. Требуется публичный доступ к сайту.'
            ];
        }
        
        $keyUrl = $this->getKeyLocation($key);
        if (!$this->checkKeyFileAccessibility($keyUrl)) {
            return [
                'code' => 0, 
                'success' => false, 
                'error' => 'Файл с ключом недоступен: ' . $keyUrl
            ];
        }
        
        $engine = $engines[$key_name];
        
        $data = [
            'host' => $host,
            'key' => $key,
            'urlList' => $urls
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $engine['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $success = $httpCode >= 200 && $httpCode < 300;
        
        if (!$success && $settings['notify_error']) {
            $this->logIndexNowError($key_name, $urls, $httpCode, $error, $response);
        }
        
        return [
            'code' => $httpCode,
            'success' => $success,
            'error' => $error ?: ($response ?: 'Неизвестная ошибка')
        ];
    }

    /**
    * Проверить, является ли хост локальным
    * @param string $host
    * @return bool
    */
    public function isLocalhost($host) {
        $localHosts = ['localhost', '127.0.0.1', '::1'];
        $localDomains = ['.local', '.localhost', '.test', '.dev'];
        
        if (in_array($host, $localHosts)) {
            return true;
        }
        
        foreach ($localDomains as $domain) {
            if (strpos($host, $domain) !== false) {
                return true;
            }
        }
        
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (filter_var($host, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return true;
            }
        }
        
        return false;
    }

    /**
    * Проверить доступность файла с ключом
    * @param string $keyUrl
    * @return bool
    */
    private function checkKeyFileAccessibility($keyUrl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $keyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }

    /**
    * Отправить URL в IndexNow через очередь
    * @param string|array $urls URL или массив URL
    * @param int $delay Задержка в секундах
    * @return bool
    */
    public function queueIndexNowPing($urls, $delay = 0) {
        if (!$this->isIndexNowEnabled()) {
            return false;
        }
        
        $settings = $this->getIndexNowSettings();
        
        if (!is_array($urls)) {
            $urls = [$urls];
        }
        
        $urls = array_map([$this, 'normalizeUrl'], $urls);
        $urls = array_filter($urls);
        
        if (empty($urls)) {
            return false;
        }
        
        $taskData = [
            'urls' => $urls,
            'created_at' => time(),
            'scheduled_at' => time() + $delay
        ];
        
        return $this->addToQueue('indexnow_ping', $taskData);
    }

    /**
    * Обработка очереди IndexNow
    * 
    * @param array $task Данные задачи
    * @return bool
    */
    public function processIndexNowQueue($task) {
        $settings = $this->getIndexNowSettings();
        $engines = $this->getIndexNowEngines();
        $results = [];
        
        foreach (array_keys($engines) as $key_name) {
            if (!empty($settings[$key_name])) {
                $results[$key_name] = $this->sendIndexNowPing($key_name, $task['urls']);
            }
        }
        
        return $results;
    }

    /**
    * Добавить задачу в очередь
    * 
    * @param string $task Тип задачи
    * @param array $data Данные задачи
    * @return bool
    */
    private function addToQueue($task, $data) {
        try {
            $sql = "INSERT INTO queue_tasks (task, data, status, created_at) VALUES (?, ?, 'pending', NOW())";
            $this->db->query($sql, [$task, json_encode($data)]);
            return true;
        } catch (Exception $e) {
            Logger::error('IndexNow queue error: ' . $e->getMessage());
            return false;
        }
    }

    /**
    * Обработать все ожидающие задачи очереди
    * @param int $limit Максимальное количество задач
    * @return int Количество обработанных задач
    */
    public function processQueue($limit = 10) {
        $tasks = $this->db->fetchAll(
            "SELECT * FROM queue_tasks WHERE status = 'pending' ORDER BY created_at ASC LIMIT ?",
            [$limit]
        );
        
        $processed = 0;
        
        foreach ($tasks as $task) {
            $data = json_decode($task['data'], true);
            
            $this->db->query(
                "UPDATE queue_tasks SET status = 'processing', processed_at = NOW() WHERE id = ?",
                [$task['id']]
            );
            
            try {
                switch ($task['task']) {
                    case 'indexnow_ping':
                        $this->processIndexNowQueue($data);
                        break;
                    default:
                        throw new Exception("Unknown task type: {$task['task']}");
                }
                
                $this->db->query(
                    "UPDATE queue_tasks SET status = 'completed' WHERE id = ?",
                    [$task['id']]
                );
                $processed++;
                
            } catch (Exception $e) {
                $attempts = $task['attempts'] + 1;
                $status = $attempts >= 3 ? 'failed' : 'pending';
                
                $this->db->query(
                    "UPDATE queue_tasks SET status = ?, attempts = ? WHERE id = ?",
                    [$status, $attempts, $task['id']]
                );
                
                Logger::error("IndexNow queue task #{$task['id']} failed: " . $e->getMessage());
            }
        }
        
        return $processed;
    }

    /**
    * Проверить, включен ли IndexNow
    * @return bool
    */
    public function isIndexNowEnabled() {
        $settings = $this->getIndexNowSettings();
        return !empty($settings['enabled']);
    }

    /**
    * Получить хост сайта
    * @return string
    */
    public function getHost() {
        $baseUrl = $this->getBaseUrl();
        $parsed = parse_url($baseUrl);
        return $parsed['host'] ?? $_SERVER['HTTP_HOST'] ?? '';
    }

    /**
    * Получить URL для TXT файла ключа
    * @param string $key
    * @return string
    */
    public function getKeyLocation($key) {
        return rtrim($this->getBaseUrl(), '/') . '/' . $key . '.txt';
    }

    /**
    * Нормализовать URL
    * @param string $url
    * @return string
    */
    public function normalizeUrl($url) {
        if (empty($url)) {
            return '';
        }
        
        if (($pos = strpos($url, '#')) !== false) {
            $url = substr($url, 0, $pos);
        }
        
        $url = preg_replace('#(?<=:)/+#', '/', $url);
        $url = preg_replace('#(https?):/([^/])#', '$1://$2', $url);
        $url = rtrim($url, '/');
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Logger::error('[IndexNow] Invalid URL after normalization: ' . $url);
            return '';
        }
        
        return $url;
    }

    /**
    * Логирование ошибок IndexNow
    * @param string $key_name
    * @param array $urls
    * @param int $httpCode
    * @param string $error
    */
    private function logIndexNowError($key_name, $urls, $httpCode, $error) {
        $engines = $this->getIndexNowEngines();
        $engineName = $engines[$key_name]['name'] ?? $key_name;
        
        $message = sprintf(
            '[IndexNow] %s returned code %d for URL(s): %s',
            $engineName,
            $httpCode,
            implode(', ', $urls)
        );
        
        if ($error) {
            $message .= ' | CURL error: ' . $error;
        }
        
        Logger::error($message);
        $this->sendIndexNowNotification($engineName, $httpCode, $urls);
    }

    /**
    * Отправить уведомление администраторам об ошибке IndexNow
    * @param string $engineName
    * @param int $httpCode
    * @param array $urls
    */
    private function sendIndexNowNotification($engineName, $httpCode, $urls) {
        if (class_exists('NotificationModel')) {
            $notificationModel = new NotificationModel($this->db);
            $admin_ids = $this->db->fetchAll(
                "SELECT id FROM users WHERE is_admin = 1"
            );
            
            foreach ($admin_ids as $admin) {
                $notificationModel->add([
                    'type' => 'indexnow_error',
                    'title' => 'Ошибка IndexNow',
                    'message' => sprintf(
                        'При отправке в %s получен код ответа %d. URL: %s',
                        $engineName,
                        $httpCode,
                        implode(', ', $urls)
                    ),
                    'user_id' => $admin['id']
                ]);
            }
        }
    }

    /**
    * Обработка события изменения контента
    * @param string $url URL измененной страницы
    * @param string $contentType Тип контента (post, page, category, tag)
    * @param int $contentId ID контента
    * @return bool
    */
    public function onContentChange($url, $contentType, $contentId) {
        if (!$this->isIndexNowEnabled()) {
            return false;
        }
        
        $settings = $this->getIndexNowSettings();
        $delay = (int)($settings['submit_delay'] ?? 0);
        
        return $this->queueIndexNowPing($url, $delay);
    }

    /**
    * Получить старые ключи IndexNow (для очистки)
    * @return array
    */
    public function getOldIndexNowKeys() {
        $settings = $this->settingsModel->get('seo_indexnow', []);
        return [
            'ya_key' => $settings['ya_key'] ?? '',
            'bing_key' => $settings['bing_key'] ?? ''
        ];
    }

    /**
    * Очистить файлы ключей IndexNow
    * @param array $keys Ключи для удаления
    * @return int Количество удаленных файлов
    */
    public function cleanupIndexNowKeyFiles($rootPath, $keys) {
        $deleted = 0;
        
        foreach ($keys as $key_name => $key) {
            if (!empty($key)) {
                $filePath = $rootPath . '/' . $key . '.txt';
                if (file_exists($filePath)) {
                    if (@unlink($filePath)) {
                        $deleted++;
                    }
                }
            }
        }
        
        return $deleted;
    }

    /**
    * Получить настройки Schema.org
    * @return array
    */
    public function getSchemaSettings() {
        $default = [
            'org_name' => \SettingsHelper::get('site', 'site_name', 'BloggyCMS'),
            'org_logo' => '',
            'org_type' => 'Organization',
            'org_url' => defined('BASE_URL') ? BASE_URL : '',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_instagram' => '',
            'social_telegram' => '',
            'social_vk' => '',
            'social_youtube' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'same_as' => []
        ];
        
        $settings = $this->settingsModel->get('seo_schema', []);
        return array_merge($default, $settings);
    }

    /**
    * Сохранить настройки Schema.org
    * @param array $settings
    * @return bool
    */
    public function saveSchemaSettings($settings) {
        return $this->settingsModel->save('seo_schema', $settings);
    }

    /**
    * Сгенерировать Organization Schema для сайта
    * @param array $settings Настройки Schema
    * @return array Schema.org данные
    */
    public function generateOrganizationSchema($settings = []) {
        if (empty($settings)) {
            $settings = $this->getSchemaSettings();
        }
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $settings['org_type'] ?? 'Organization',
            'name' => $settings['org_name'] ?? '',
            'url' => $settings['org_url'] ?? ''
        ];
        
        // Логотип
        if (!empty($settings['org_logo'])) {
            $schema['logo'] = [
                '@type' => 'ImageObject',
                'url' => $settings['org_logo']
            ];
        }
        
        // Социальные профили
        $sameAs = [];
        if (!empty($settings['social_facebook'])) $sameAs[] = $settings['social_facebook'];
        if (!empty($settings['social_twitter'])) $sameAs[] = $settings['social_twitter'];
        if (!empty($settings['social_instagram'])) $sameAs[] = $settings['social_instagram'];
        if (!empty($settings['social_telegram'])) $sameAs[] = $settings['social_telegram'];
        if (!empty($settings['social_vk'])) $sameAs[] = $settings['social_vk'];
        if (!empty($settings['social_youtube'])) $sameAs[] = $settings['social_youtube'];
        
        if (!empty($sameAs)) {
            $schema['sameAs'] = $sameAs;
        }
        
        // Контакты
        if (!empty($settings['contact_email']) || !empty($settings['contact_phone'])) {
            $schema['contactPoint'] = [];
            if (!empty($settings['contact_email'])) {
                $schema['contactPoint']['email'] = $settings['contact_email'];
            }
            if (!empty($settings['contact_phone'])) {
                $schema['contactPoint']['telephone'] = $settings['contact_phone'];
            }
            $schema['contactPoint']['contactType'] = 'customer service';
        }
        
        return $schema;
    }

    /**
    * Сгенерировать Schema.org BlogPosting для поста
    * @param array $post Данные поста
    * @return array Schema.org данные
    */
    public function generateBlogPostingSchema($post) {
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost';
        
        $schemaSettings = $this->getSchemaSettings();
        
        $imageData = null;
        if (!empty($post['featured_image'])) {
            $imageData = [
                '@type' => 'ImageObject',
                'url' => $baseUrl . '/uploads/images/' . $post['featured_image'],
                'width' => 1200,
                'height' => 630
            ];
        }
        
        $authorData = [
            '@type' => 'Person',
            'name' => $post['author_name'] ?? $post['author_display_name'] ?? 'Admin'
        ];
        
        $publisherData = [
            '@type' => $schemaSettings['org_type'] ?? 'Organization',
            'name' => $schemaSettings['org_name'] ?? \SettingsHelper::get('site', 'site_name', 'BloggyCMS')
        ];
        
        if (!empty($schemaSettings['org_logo'])) {
            $publisherData['logo'] = [
                '@type' => 'ImageObject',
                'url' => $schemaSettings['org_logo']
            ];
        } else {
            $publisherData['logo'] = [
                '@type' => 'ImageObject',
                'url' => $baseUrl . '/uploads/images/logo.png'
            ];
        }
        
        $keywords = '';
        if (!empty($post['tags']) && is_array($post['tags'])) {
            $keywords = implode(', ', array_column($post['tags'], 'name'));
        }
        
        $wordCount = 0;
        if (!empty($post['content'])) {
            $wordCount = str_word_count(strip_tags($post['content']));
        }
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $baseUrl . '/post/' . ($post['slug'] ?? '')
            ],
            'headline' => $post['title'] ?? '',
            'description' => $post['short_description'] ?? mb_substr(strip_tags($post['content'] ?? ''), 0, 160),
            'image' => $imageData,
            'author' => $authorData,
            'publisher' => $publisherData,
            'datePublished' => date('c', strtotime($post['created_at'] ?? 'now')),
            'dateModified' => date('c', strtotime($post['updated_at'] ?? $post['created_at'] ?? 'now')),
            'articleSection' => $post['category_name'] ?? '',
            'keywords' => $keywords,
            'wordCount' => $wordCount,
            'inLanguage' => \SettingsHelper::get('site', 'site_language', 'ru-RU')
        ];
        
        if (!empty($post['comments_count']) && $post['comments_count'] > 0) {
            $schema['commentCount'] = (int)$post['comments_count'];
        }
        
        return $schema;
    }

}