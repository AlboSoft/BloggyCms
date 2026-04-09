<?php
/**
 * Хуки для автоматического обновления SEO-файлов при изменениях контента
 */

/**
 * Функция для обновления sitemap.xml и rss.xml
 * 
 * @param array $data Данные о событии (пост, категория, тег, страница)
 * @return void
 */
function regenerate_seo_files($data = []) {
    try {
        $db = Database::getInstance();
        $seoModel = new SeoModel($db);
        
        $sitemapSettings = $seoModel->getSitemapSettings();
        $rssSettings = $seoModel->getRssSettings();
        
        $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(dirname(dirname(__DIR__))));
        
        if ($sitemapSettings['enabled']) {
            $sitemap = $seoModel->generateSitemap();
            if (!empty($sitemap)) {
                $sitemapPath = $rootPath . '/sitemap.xml';
                file_put_contents($sitemapPath, $sitemap);
            }
        }

        if ($rssSettings['enabled']) {
            $rss = $seoModel->generateRss();
            if (!empty($rss)) {
                $rssPath = $rootPath . '/rss.xml';
                file_put_contents($rssPath, $rss);
            }
        }
        
        $seoModel->clearCache();
        
    } catch (Exception $e) {
        error_log("Ошибка обновления SEO файлов: " . $e->getMessage());
    }
}

// При создании поста
Event::listen('post.created', function($data) {
    regenerate_seo_files($data);
    
    if (isset($data['url'])) {
        $db = Database::getInstance();
        $seoModel = new SeoModel($db);
        $seoModel->onContentChange($data['url'], 'post', $data['id'] ?? 0);
    }
}, 10, 1);

// При обновлении поста
Event::listen('post.updated', function($data) {
    regenerate_seo_files($data);
    
    if (isset($data['url'])) {
        $db = Database::getInstance();
        $seoModel = new SeoModel($db);
        $seoModel->onContentChange($data['url'], 'post', $data['id'] ?? 0);
    }
}, 10, 1);

// При удалении поста
Event::listen('post.deleted', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При изменении статуса поста (опубликован/черновик)
Event::listen('post.status_changed', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При создании страницы
Event::listen('page.created', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При обновлении страницы
Event::listen('page.updated', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При удалении страницы
Event::listen('page.deleted', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При создании категории
Event::listen('category.created', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При обновлении категории
Event::listen('category.updated', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При удалении категории
Event::listen('category.deleted', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При создании тега
Event::listen('tag.created', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При обновлении тега
Event::listen('tag.updated', function($data) {
    regenerate_seo_files($data);
}, 10, 1);

// При удалении тега
Event::listen('tag.deleted', function($data) {
    regenerate_seo_files($data);
}, 10, 1);