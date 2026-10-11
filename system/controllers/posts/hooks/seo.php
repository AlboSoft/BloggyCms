<?php
/**
* SEO хуки для контроллера постов
* Автоматически подключается при загрузке контроллера
*/

\Event::listen('controller.render.before', function($eventData) {
    if (!isset($eventData['template']) || strpos($eventData['template'], 'posts/show') === false) {
        return;
    }
    
    $post = null;
    if (isset($eventData['post'])) {
        $post = $eventData['post'];
    } elseif (isset($eventData['data']['post'])) {
        $post = $eventData['data']['post'];
    }
    
    if (!$post) {
        return;
    }
    
    try {
        $db = $GLOBALS['db'] ?? null;
        if (!$db && class_exists('Database')) {
            $db = Database::getInstance();
        }
        if (!$db) {
            return;
        }
        $seoModel = new SeoModel($db);
        $metaSettings = $seoModel->getMetaSettings();
    } catch (Throwable $e) {
        return;
    }
    
    if ($metaSettings['canonical_enabled']) {
        $canonical = BASE_URL . '/post/' . $post['slug'];
        \Event::trigger('seo.meta.canonical', ['url' => &$canonical]);
        $data['canonical_url'] = $canonical;
    }
    
    if ($metaSettings['og_enabled']) {
        $data['og_data'] = [
            'title' => $post['title'],
            'description' => $post['short_description'] ?? '',
            'url' => BASE_URL . '/post/' . $post['slug'],
            'type' => 'article',
            'image' => !empty($post['featured_image']) 
                ? BASE_URL . '/uploads/images/' . $post['featured_image'] 
                : ''
        ];
    }
    
    if ($metaSettings['schema_enabled']) {
        $data['schema_data'] = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post['title'],
            'datePublished' => $post['created_at'],
            'dateModified' => $post['updated_at'] ?? $post['created_at'],
            'author' => [
                '@type' => 'Person',
                'name' => $post['author_name'] ?? ''
            ]
        ];
    }
}, 100);