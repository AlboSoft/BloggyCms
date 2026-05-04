<?php

/**
* Парсер кастомных шаблонов меню с поддержкой шорткодов
*/
class CustomMenuParser {
    
    /**
    * Парсит кастомный шаблон меню
    * @param string $template Шаблон с шорткодами
    * @param array $menuItems Массив пунктов меню
    * @param string $currentUrl Текущий URL для определения активности
    * @return string Отрендеренный HTML
    */
    public static function parse($template, $menuItems, $currentUrl) {
        if (empty($template)) {
            return '<!-- Empty custom template -->';
        }
        
        // шорткод {li=sub}...{/li} (пункты с детьми)
        $template = preg_replace_callback(
            '/\{li=sub\}(.*?)\{\/li=sub\}/s',
            function($matches) use ($menuItems, $currentUrl) {
                $innerTemplate = $matches[1];
                return self::renderMenuItems($menuItems, $innerTemplate, $currentUrl, true);
            },
            $template
        );
        
        // шорткод {li-extra}...{/li} (экстра-пункты)
        $template = preg_replace_callback(
            '/\{li-extra\}(.*?)\{\/li-extra\}/s',
            function($matches) use ($menuItems, $currentUrl) {
                $innerTemplate = $matches[1];
                $extraItems = self::filterExtraItems($menuItems);
                return self::renderMenuItems($extraItems, $innerTemplate, $currentUrl, false);
            },
            $template
        );
        
        // шорткод {li}...{/li} (обычные пункты)
        $template = preg_replace_callback(
            '/\{li\}(.*?)\{\/li\}/s',
            function($matches) use ($menuItems, $currentUrl) {
                $innerTemplate = $matches[1];
                $normalItems = self::filterNormalItems($menuItems);
                return self::renderMenuItems($normalItems, $innerTemplate, $currentUrl, false);
            },
            $template
        );
        
        return $template;
    }
    
    /**
    * Рендерит пункты меню с помощью шаблона
    */
    private static function renderMenuItems($items, $template, $currentUrl, $isSubmenu = false) {
        if (empty($items)) {
            return '';
        }
        
        $output = '';
        foreach ($items as $item) {
            if ($isSubmenu && empty($item['children'])) {
                continue;
            }
            
            if ($isSubmenu && !empty($item['children'])) {
                $output .= self::renderMenuItemWithChildren($item, $template, $currentUrl);
            }
            else if (!$isSubmenu) {
                if (!empty($item['children'])) {
                    $output .= self::renderMenuItemWithChildren($item, $template, $currentUrl);
                } else {
                    $output .= self::renderMenuItem($item, $template, $currentUrl);
                }
            }
        }
        
        return $output;
    }
    
    /**
    * Рендерит один пункт меню без детей
    */
    private static function renderMenuItem($item, $template, $currentUrl) {
        $url = self::processUrl($item['url'] ?? '#');
        $title = html($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $target = $item['target'] ?? '_self';
        $class = html($item['class'] ?? '', ENT_QUOTES, 'UTF-8');
        $isActive = self::isActiveUrl($url, $currentUrl);
        $level = $item['level'] ?? 0;
        $hasChildren = !empty($item['children']);
        
        $iconHtml = '';
        if (!empty($item['icon']) && is_array($item['icon']) && !empty($item['icon']['id'])) {
            $iconSet = $item['icon']['set'] ?? 'bs';
            $iconId = $item['icon']['id'];
            $iconSize = $item['icon']['size'] ?? 18;
            $iconColor = $item['icon']['color'] ?? 'currentColor';
            $iconHtml = bloggy_icon($iconSet, $iconId, "{$iconSize} {$iconSize}", $iconColor, 'menu-icon');
        }
        
        $result = str_replace('{url}', $url, $template);
        $result = str_replace('{title}', $title, $result);
        $result = str_replace('{target}', $target, $result);
        $result = str_replace('{class}', $class, $result);
        $result = str_replace('{icon}', $iconHtml, $result);
        $result = str_replace('{active_class}', $isActive ? 'active' : '', $result);
        $result = str_replace('{has_children}', $hasChildren ? 'true' : 'false', $result);
        $result = str_replace('{level}', $level, $result);
        
        return $result;
    }
    
    /**
    * Рендерит пункт меню с детьми
    */
    private static function renderMenuItemWithChildren($item, $template, $currentUrl) {
        $output = self::renderMenuItem($item, $template, $currentUrl);
        
        if (!empty($item['children'])) {
            if (strpos($output, '{children}') !== false) {
                $childrenHtml = '';
                foreach ($item['children'] as $child) {
                    $child['level'] = ($item['level'] ?? 0) + 1;
                    $childrenHtml .= self::renderMenuItemWithChildren($child, $template, $currentUrl);
                }
                $output = str_replace('{children}', $childrenHtml, $output);
            }
        }
        
        return $output;
    }
    
    /**
    * Фильтрует обычные пункты меню (не экстра и без детей)
    */
    private static function filterNormalItems($items) {
        $result = [];
        foreach ($items as $item) {
            if (!self::isExtraItem($item) && empty($item['children'])) {
                $result[] = $item;
            }
        }
        return $result;
    }
    
    /**
    * Фильтрует экстра-пункты меню
    */
    private static function filterExtraItems($items) {
        $result = [];
        foreach ($items as $item) {
            if (self::isExtraItem($item)) {
                $result[] = $item;
                if (!empty($item['children'])) {
                    $result = array_merge($result, self::filterExtraItems($item['children']));
                }
            } elseif (!empty($item['children'])) {
                $childExtras = self::filterExtraItems($item['children']);
                $result = array_merge($result, $childExtras);
            }
        }
        return $result;
    }
    
    /**
    * Проверяет, является ли пункт экстра-пунктом
    */
    private static function isExtraItem($item) {
        return isset($item['is_extra']) && $item['is_extra'] === true;
    }
    
    /**
    * Проверяет, есть ли у пункта видимые дети (не экстра)
    */
    private static function hasVisibleChildren($item) {
        if (empty($item['children'])) return false;
        
        foreach ($item['children'] as $child) {
            if (!self::isExtraItem($child)) {
                return true;
            }
        }
        return false;
    }
    
    /**
    * Обрабатывает URL с шорткодами
    */
    private static function processUrl($url) {
        if (empty($url)) {
            return $url;
        }
        
        $userId = $_SESSION['user_id'] ?? null;
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
            '{site_name}' => self::getSiteSetting('site_name'),
            '{year}' => date('Y'),
            '{month}' => date('m'),
            '{day}' => date('d'),
        ];
        
        $url = preg_replace_callback('/\{user_field:([^}]+)\}/', function($matches) use ($userData) {
            $fieldName = $matches[1];
            return $userData[$fieldName] ?? '';
        }, $url);
        
        foreach ($shortcodes as $shortcode => $replacement) {
            $url = str_replace($shortcode, $replacement, $url);
        }
        
        return $url;
    }
    
    /**
    * Проверяет, активен ли URL
    */
    private static function isActiveUrl($url, $currentUrl) {
        if ($url === $currentUrl) {
            return true;
        }
        
        if ($url !== '/' && strpos($currentUrl, $url) === 0) {
            return true;
        }
        
        return false;
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
            
            if (!$user) {
                return [];
            }
            
            return [
                'id' => $user['id'] ?? '',
                'username' => $user['username'] ?? '',
                'email' => $user['email'] ?? '',
                'first_name' => $user['first_name'] ?? '',
                'last_name' => $user['last_name'] ?? '',
                'display_name' => $user['display_name'] ?? '',
                'slug' => $user['slug'] ?? '',
                'avatar' => $user['avatar'] ?? '',
                'role' => $user['role'] ?? '',
                'status' => $user['status'] ?? ''
            ];
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
    * Получает настройку блога
    */
    private static function getSiteSetting($key) {
        try {
            return SettingsHelper::get('site', $key) ?? '';
        } catch (Exception $e) {
            return '';
        }
    }
}