<?php

/**
* Парсер кастомных шаблонов меню
*/
class CustomMenuParser {
    
    /**
    * Главный метод парсинга
    */
    public static function parse($template, $menuItems, $currentUrl) {
        if (empty($template)) {
            return '';
        }
        
        $result = preg_replace_callback(
            '/\{li=sub\}(.*?)\{\/li=sub\}/s',
            function($matches) use ($menuItems, $currentUrl) {
                $itemTemplate = $matches[1];
                $output = '';
                
                foreach ($menuItems as $item) {
                    if (!empty($item['children'])) {
                        $output .= self::renderParentItem($item, $itemTemplate, $currentUrl);
                    }
                }
                
                return $output;
            },
            $template
        );
        
        $result = preg_replace_callback(
            '/\{li\}(.*?)\{\/li\}/s',
            function($matches) use ($menuItems, $currentUrl) {
                $itemTemplate = $matches[1];
                $output = '';
                
                foreach ($menuItems as $item) {
                    if (empty($item['children']) && empty($item['is_extra'])) {
                        $output .= self::renderRegularItem($item, $itemTemplate, $currentUrl);
                    }
                }
                
                return $output;
            },
            $result
        );
        
        $result = preg_replace_callback(
            '/\{li-extra\}(.*?)\{\/li-extra\}/s',
            function($matches) use ($menuItems, $currentUrl) {
                $itemTemplate = $matches[1];
                $output = '';
                
                foreach ($menuItems as $item) {
                    if (!empty($item['is_extra'])) {
                        $output .= self::renderRegularItem($item, $itemTemplate, $currentUrl);
                    }
                }
                
                return $output;
            },
            $result
        );
        
        return $result;
    }
    
    /**
    * Рендерит родительский пункт с детьми
    */
    private static function renderParentItem($item, $template, $currentUrl) {
        if (preg_match('/\{li=children\}(.*?)\{\/li=children\}/s', $template, $childrenMatch)) {
            $fullChildrenBlock  = $childrenMatch[0];
            $childrenContainer  = $childrenMatch[1];

            if (preg_match('/\{li=children-item\}(.*?)\{\/li=children-item\}/s', $childrenContainer, $itemMatch)) {
                $itemBlock    = $itemMatch[0];
                $itemTemplate = $itemMatch[1];

                $renderedChildren = '';
                foreach ($item['children'] as $child) {
                    $renderedChildren .= self::renderChildItem($child, $itemTemplate, $currentUrl);
                }

                $finalChildrenHtml = str_replace($itemBlock, $renderedChildren, $childrenContainer);
                $placeholder = '___CHILDREN_BLOCK_' . md5(uniqid('', true)) . '___';
                $result = str_replace($fullChildrenBlock, $placeholder, $template);
                $result = self::replaceShortcodes($result, $item, $currentUrl);
                $result = str_replace($placeholder, $finalChildrenHtml, $result);

                return $result;
            }
        }

        return self::replaceShortcodes($template, $item, $currentUrl);
    }
    
    /**
    * Рендерит дочерний пункт
    */
    private static function renderChildItem($child, $template, $currentUrl) {
        $url = self::processUrl($child['url'] ?? '#');
        $title = html($child['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $desc = isset($child['description']) ? html($child['description'], ENT_QUOTES, 'UTF-8') : '';
        $target = $child['target'] ?? '_self';
        $class = html($child['class'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $iconHtml = '';
        if (!empty($child['icon']) && is_array($child['icon']) && !empty($child['icon']['id'])) {
            $iconSet = $child['icon']['set'] ?? 'bs';
            $iconId = $child['icon']['id'];
            $iconSize = $child['icon']['size'] ?? 20;
            $iconColor = $child['icon']['color'] ?? 'currentColor';
            $iconHtml = bloggy_icon($iconSet, $iconId, "{$iconSize} {$iconSize}", $iconColor, 'menu-icon');
        }
        
        $result = str_replace('{url}', $url, $template);
        $result = str_replace('{title}', $title, $result);
        $result = str_replace('{desc}', $desc, $result);
        $result = str_replace('{target}', $target, $result);
        $result = str_replace('{class}', $class, $result);
        $result = str_replace('{icon}', $iconHtml, $result);
        
        return $result;
    }
    
    /**
    * Рендерит обычный пункт меню (без детей)
    */
    private static function renderRegularItem($item, $template, $currentUrl) {
        return self::replaceShortcodes($template, $item, $currentUrl);
    }
    
    /**
    * Заменяет все шорткоды в шаблоне
    */
    private static function replaceShortcodes($template, $item, $currentUrl) {
        $url = self::processUrl($item['url'] ?? '#');
        $title = html($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = isset($item['description']) ? html($item['description'], ENT_QUOTES, 'UTF-8') : '';
        $target = $item['target'] ?? '_self';
        $class = html($item['class'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $isActive = self::isActiveUrl($url, $currentUrl);
        $activeClass = $isActive ? 'active' : '';
        
        $iconHtml = '';
        if (!empty($item['icon']) && is_array($item['icon']) && !empty($item['icon']['id'])) {
            $iconSet = $item['icon']['set'] ?? 'bs';
            $iconId = $item['icon']['id'];
            $iconSize = $item['icon']['size'] ?? 20;
            $iconColor = $item['icon']['color'] ?? 'currentColor';
            $iconHtml = bloggy_icon($iconSet, $iconId, "{$iconSize} {$iconSize}", $iconColor, 'menu-icon');
        }
        
        $result = str_replace('{url}', $url, $template);
        $result = str_replace('{title}', $title, $result);
        $result = str_replace('{desc}', $description, $result);
        $result = str_replace('{target}', $target, $result);
        $result = str_replace('{class}', $class, $result);
        $result = str_replace('{icon}', $iconHtml, $result);
        $result = str_replace('{active_class}', $activeClass, $result);
        $result = str_replace('{has_children}', !empty($item['children']) ? 'true' : 'false', $result);
        
        return $result;
    }
    
    /**
    * Обрабатывает URL с шорткодами
    */
    private static function processUrl($url) {
        if (empty($url) || $url === '#') {
            return $url;
        }
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        $userData = self::getCurrentUserData();
        
        $shortcodes = [
            '{user_id}' => $userData['id'] ?? '',
            '{username}' => $userData['username'] ?? '',
            '{email}' => $userData['email'] ?? '',
            '{first_name}' => $userData['first_name'] ?? '',
            '{last_name}' => $userData['last_name'] ?? '',
            '{display_name}' => $userData['display_name'] ?? '',
            '{slug}' => $userData['slug'] ?? '',
            '{base_url}' => BASE_URL,
            '{admin_url}' => ADMIN_URL,
            '{year}' => date('Y'),
            '{month}' => date('m'),
            '{day}' => date('d'),
        ];
        
        $url = preg_replace_callback('/\{user_field:([^}]+)\}/', function($matches) use ($userData) {
            return $userData[$matches[1]] ?? '';
        }, $url);
        
        foreach ($shortcodes as $shortcode => $replacement) {
            $url = str_replace($shortcode, $replacement, $url);
        }
        
        return $url;
    }
    
    /**
    * Проверяет активный URL
    */
    private static function isActiveUrl($url, $currentUrl) {
        if (empty($url) || $url === '#') {
            return false;
        }
        
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);
        $urlPath = parse_url($url, PHP_URL_PATH);
        
        return $urlPath === $currentPath;
    }
    
    /**
    * Получает данные текущего пользователя
    */
    private static function getCurrentUserData() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        try {
            $db = Database::getInstance();
            $userModel = new UserModel($db);
            $user = $userModel->getById($_SESSION['user_id']);
            return $user ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}