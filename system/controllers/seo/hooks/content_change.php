<?php

/**
* Хук для обработки изменений контента
* Вызывается при создании, обновлении, удалении постов, страниц, категорий, тегов
* @param string $event Название события
* @param array $data Данные события
*/
class onSeoContentChange {
    
    public function run($event, $data) {
        $url = null;
        $contentType = null;
        $contentId = null;
        
        switch ($event) {
            case 'post.created':
            case 'post.updated':
            case 'post.deleted':
            case 'post.status_changed':
                if (isset($data[0]) && is_array($data[0])) {
                    $post = $data[0];
                    $contentId = $post['id'] ?? null;
                    $contentType = 'post';
                    $url = href_to_abs('post', ($post['slug'] ?? '') . '.html');
                } elseif (isset($data['id'])) {
                    $contentId = $data['id'];
                    $contentType = 'post';
                    $url = href_to_abs('post', ($data['slug'] ?? '') . '.html');
                }
                break;
                
            case 'page.created':
            case 'page.updated':
            case 'page.deleted':
                if (isset($data[0]) && is_array($data[0])) {
                    $page = $data[0];
                    $contentId = $page['id'] ?? null;
                    $contentType = 'page';
                    $url = href_to_abs('page', ($page['slug'] ?? '') . '.html');
                } elseif (isset($data['id'])) {
                    $contentId = $data['id'];
                    $contentType = 'page';
                    $url = href_to_abs('page', ($data['slug'] ?? '') . '.html');
                }
                break;
                
            case 'category.created':
            case 'category.updated':
            case 'category.deleted':
                if (isset($data[0]) && is_array($data[0])) {
                    $category = $data[0];
                    $contentId = $category['id'] ?? null;
                    $contentType = 'category';
                    $url = href_to_abs('category', $category['slug'] ?? '');
                } elseif (isset($data['id'])) {
                    $contentId = $data['id'];
                    $contentType = 'category';
                    $url = href_to_abs('category', $data['slug'] ?? '');
                }
                break;
                
            case 'tag.created':
            case 'tag.updated':
            case 'tag.deleted':
                if (isset($data[0]) && is_array($data[0])) {
                    $tag = $data[0];
                    $contentId = $tag['id'] ?? null;
                    $contentType = 'tag';
                    $url = href_to_abs('tag', $tag['slug'] ?? '');
                } elseif (isset($data['id'])) {
                    $contentId = $data['id'];
                    $contentType = 'tag';
                    $url = href_to_abs('tag', $data['slug'] ?? '');
                }
                break;
        }
        
        if ($url && class_exists('SeoModel')) {
            $db = Database::getInstance();
            $seoModel = new SeoModel($db);
            $seoModel->onContentChange($url, $contentType, $contentId);
        }
    }
}