<?php
namespace seo\actions;

class AdminSettings extends SeoAction {

    public function execute() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!\CsrfToken::verify($_POST['csrf_token'] ?? '', 'seo_settings')) {
                    throw new \Exception(LANG_ACTION_SEO_ADMINSETTINGS_INVALID_CSRF);
                }

                $robotsSettings = [
                    'enabled' => isset($_POST['robots_enabled']) ? 1 : 0,
                    'disallow_paths' => array_filter(array_map('trim', explode("\n", $_POST['robots_disallow'] ?? ''))),
                    'allow_paths' => array_filter(array_map('trim', explode("\n", $_POST['robots_allow'] ?? ''))),
                    'crawl_delay' => (int)($_POST['robots_crawl_delay'] ?? 0),
                    'sitemap_url' => trim($_POST['robots_sitemap_url'] ?? '')
                ];

                $sitemapSettings = [
                    'enabled' => isset($_POST['sitemap_enabled']) ? 1 : 0,
                    'include_posts' => isset($_POST['sitemap_include_posts']) ? 1 : 0,
                    'include_pages' => isset($_POST['sitemap_include_pages']) ? 1 : 0,
                    'include_categories' => isset($_POST['sitemap_include_categories']) ? 1 : 0,
                    'include_tags' => isset($_POST['sitemap_include_tags']) ? 1 : 0,
                    'max_posts' => (int)($_POST['sitemap_max_posts'] ?? 1000),
                    'cache_enabled' => isset($_POST['sitemap_cache_enabled']) ? 1 : 0,
                    'cache_lifetime' => (int)($_POST['sitemap_cache_lifetime'] ?? 3600)
                ];

                $rssSettings = [
                    'enabled' => isset($_POST['rss_enabled']) ? 1 : 0,
                    'posts_limit' => (int)($_POST['rss_limit'] ?? 20),
                    'include_full_content' => isset($_POST['rss_full_content']) ? 1 : 0,
                    'copyright' => trim($_POST['rss_copyright'] ?? ''),
                    'language' => trim($_POST['rss_language'] ?? 'ru-ru')
                ];

                $indexnowSettings = [
                    'enabled' => isset($_POST['indexnow_enabled']) ? 1 : 0,
                    'ya_key' => trim($_POST['indexnow_ya_key'] ?? ''),
                    'bing_key' => trim($_POST['indexnow_bing_key'] ?? ''),
                    'auto_submit' => isset($_POST['indexnow_auto_submit']) ? 1 : 0,
                    'submit_delay' => (int)($_POST['indexnow_submit_delay'] ?? 0),
                    'notify_error' => isset($_POST['indexnow_notify_error']) ? 1 : 0
                ];
                
                if ($indexnowSettings['enabled']) {
                    if (empty($indexnowSettings['ya_key'])) {
                        $indexnowSettings['ya_key'] = $this->seoModel->generateRandomKey(32);
                    }
                    if (empty($indexnowSettings['bing_key'])) {
                        $indexnowSettings['bing_key'] = $this->seoModel->generateRandomKey(32);
                    }
                }

                $schemaSettings = [
                    'org_name' => trim($_POST['schema_org_name'] ?? ''),
                    'org_logo' => trim($_POST['schema_org_logo'] ?? ''),
                    'org_type' => $_POST['schema_org_type'] ?? 'Organization',
                    'org_url' => trim($_POST['schema_org_url'] ?? BASE_URL),
                    'social_facebook' => trim($_POST['schema_social_facebook'] ?? ''),
                    'social_twitter' => trim($_POST['schema_social_twitter'] ?? ''),
                    'social_instagram' => trim($_POST['schema_social_instagram'] ?? ''),
                    'social_telegram' => trim($_POST['schema_social_telegram'] ?? ''),
                    'social_vk' => trim($_POST['schema_social_vk'] ?? ''),
                    'social_youtube' => trim($_POST['schema_social_youtube'] ?? ''),
                    'contact_email' => trim($_POST['schema_contact_email'] ?? ''),
                    'contact_phone' => trim($_POST['schema_contact_phone'] ?? '')
                ];
                
                if (!empty($schemaSettings['contact_email']) && !filter_var($schemaSettings['contact_email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception(LANG_ACTION_SEO_ADMINSETTINGS_INVALID_EMAIL);
                }

                $this->seoModel->saveSettings('seo_robots', $robotsSettings);
                $this->seoModel->saveSettings('seo_sitemap', $sitemapSettings);
                $this->seoModel->saveSettings('seo_rss', $rssSettings);
                $this->seoModel->saveIndexNowSettings($indexnowSettings);
                $this->seoModel->saveSchemaSettings($schemaSettings);
                
                $this->forceGenerateFiles($indexnowSettings);
                
                $this->seoModel->clearCache();
                
                if (class_exists('\SettingsHelper')) {
                    \SettingsHelper::clearCache('seo_robots');
                    \SettingsHelper::clearCache('seo_sitemap');
                    \SettingsHelper::clearCache('seo_rss');
                    \SettingsHelper::clearCache('seo_indexnow');
                    \SettingsHelper::clearCache('seo_schema');
                }
                
                \Notification::success(LANG_ACTION_SEO_ADMINSETTINGS_SUCCESS);
                
            } catch (\Exception $e) {
                \Notification::error(sprintf(LANG_ACTION_SEO_ADMINSETTINGS_ERROR, $e->getMessage()));
            }
        }

        $tab = $_POST['active_tab'] ?? 'indexnow';
        $this->redirect(ADMIN_URL . '/seo?tab=' . $tab);
    }
    
    private function forceGenerateFiles($indexnowSettings = []) {
        $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(dirname(dirname(__DIR__))));
        
        $robots = $this->seoModel->generateRobots();
        if (!empty($robots)) {
            $robotsPath = $rootPath . '/robots.txt';
            file_put_contents($robotsPath, $robots);
        }
        
        $sitemap = $this->seoModel->generateSitemap();
        if (!empty($sitemap)) {
            $sitemapPath = $rootPath . '/sitemap.xml';
            file_put_contents($sitemapPath, $sitemap);
        }
        
        $rss = $this->seoModel->generateRss();
        if (!empty($rss)) {
            $rssPath = $rootPath . '/rss.xml';
            file_put_contents($rssPath, $rss);
        }
        
        $this->generateIndexNowKeyFiles($rootPath, $indexnowSettings);
    }
    
    private function generateIndexNowKeyFiles($rootPath, $settings) {
        $oldSettings = $this->seoModel->getIndexNowSettings();
        
        if (!empty($oldSettings['ya_key']) && $oldSettings['ya_key'] !== ($settings['ya_key'] ?? '')) {
            $oldYaPath = $rootPath . '/' . $oldSettings['ya_key'] . '.txt';
            if (file_exists($oldYaPath)) {
                @unlink($oldYaPath);
            }
        }
        
        if (!empty($oldSettings['bing_key']) && $oldSettings['bing_key'] !== ($settings['bing_key'] ?? '')) {
            $oldBingPath = $rootPath . '/' . $oldSettings['bing_key'] . '.txt';
            if (file_exists($oldBingPath)) {
                @unlink($oldBingPath);
            }
        }
        
        if (!empty($settings['enabled'])) {
            if (!empty($settings['ya_key'])) {
                $yaPath = $rootPath . '/' . $settings['ya_key'] . '.txt';
                file_put_contents($yaPath, $settings['ya_key']);
            }
            
            if (!empty($settings['bing_key'])) {
                $bingPath = $rootPath . '/' . $settings['bing_key'] . '.txt';
                file_put_contents($bingPath, $settings['bing_key']);
            }
        }
    }
}