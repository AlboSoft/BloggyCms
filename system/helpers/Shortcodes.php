<?php

if (class_exists('ShortcodeRegistry')) {
    return;
}

/**
* Класс для регистрации и обработки шорткодов
*/
class ShortcodeRegistry {

    private static $shortcodes = [];
    
    /**
    * Регистрирует новый шорткод
    * @param string $name Имя шорткода
    * @param callable $callback Функция обработки
    */
    public static function add($name, $callback) {
        $name = strtolower($name);
        self::$shortcodes[$name] = $callback;
    }
    
    /**
    * Удаляет шорткод 
    * @param string $name Имя шорткода
    */
    public static function remove($name) {
        $name = strtolower($name);
        unset(self::$shortcodes[$name]);
    }
    
    /**
    * Получает все зарегистрированные шорткоды
    * @return array Массив шорткодов
    */
    public static function getAll() {
        return self::$shortcodes;
    }
    
    /**
    * Проверяет, зарегистрирован ли шорткод
    * @param string $name Имя шорткода
    * @return bool
    */
    public static function has($name) {
        $name = strtolower($name);
        return isset(self::$shortcodes[$name]);
    }
    
    /**
    * Обрабатывает шорткоды в контенте
    * @param string $content Контент для обработки
    * @return string Обработанный контент
    */
    public static function process(string $content): string {
        
        if (empty(self::$shortcodes)) {
            return $content;
        }
        
        $pairedPattern = '/\{([a-z0-9_-]+(?::[a-z0-9_-]+)?)\}(.*?)\{\/\1\}/is';
        
        $content = preg_replace_callback(
            $pairedPattern,
            function($matches) {
                $fullMatch = $matches[0];
                $name = strtolower($matches[1]);
                $innerContent = $matches[2];
                
                if (self::has($name)) {
                    try {
                        $result = call_user_func(self::$shortcodes[$name], [], $innerContent);
                        return $result !== null ? $result : $fullMatch;
                    } catch (Exception $e) {
                        return $fullMatch;
                    }
                } else {
                    return $fullMatch;
                }
            },
            $content
        );
        
        $simplePattern = '/\{([a-z0-9_-]+(?::[a-z0-9_-]+)?)(?:\s+([^}]+))?\}/i';
        
        $content = preg_replace_callback(
            $simplePattern,
            function($matches) {
                $fullMatch = $matches[0];
                $name = strtolower($matches[1]);
                $attrs = [];
                
                if (isset($matches[2])) {
                    $attrString = $matches[2];
                    
                    preg_match_all('/(\w+)\s*=\s*["\']([^"\']+)["\']/', $attrString, $attrMatches, PREG_SET_ORDER);
                    foreach ($attrMatches as $attr) {
                        $attrs[$attr[1]] = $attr[2];
                    }
                }
                
                if (self::has($name)) {
                    try {
                        $result = call_user_func(self::$shortcodes[$name], $attrs);
                        return $result !== null ? $result : $fullMatch;
                    } catch (Exception $e) {
                        return $fullMatch;
                    }
                } else {
                    return $fullMatch;
                }
            },
            $content
        );
        
        return $content;
    }
}

$GLOBALS['_menu_cache'] = [];

/**
* Получение кешированного меню
* @param string $key Ключ кеша
* @param callable $callback Функция для получения меню
* @return string HTML меню
*/
function get_cached_menu($key, $callback) {
    if (!isset($GLOBALS['_menu_cache'][$key])) {
        $GLOBALS['_menu_cache'][$key] = $callback();
    }
    return $GLOBALS['_menu_cache'][$key];
}

/**
* Рендеринг меню по ID
* @param int $menuId ID меню
* @return string HTML меню
*/
function render_menu_by_id($menuId) {
    return get_cached_menu('menu_id_' . $menuId, function() use ($menuId) {
        if (!class_exists('MenuRenderer')) {
            if (file_exists(__DIR__ . '/MenuRenderer.php')) {
                require_once __DIR__ . '/MenuRenderer.php';
            } else {
                return '<!-- MenuRenderer.php не найден -->';
            }
        }
        
        if (method_exists('MenuRenderer', 'renderById')) {
            return MenuRenderer::renderById($menuId);
        }
        
        return '<!-- Метод renderById не найден в MenuRenderer -->';
    });
}

/**
* Рендеринг меню по имени (slug)
* @param string $menuName Название меню
* @return string HTML меню
*/
function render_menu_by_name($menuName) {
    return get_cached_menu('menu_name_' . $menuName, function() use ($menuName) {
        if (!class_exists('MenuRenderer')) {
            if (file_exists(__DIR__ . '/MenuRenderer.php')) {
                require_once __DIR__ . '/MenuRenderer.php';
            } else {
                return '<!-- MenuRenderer.php не найден -->';
            }
        }
        
        if (method_exists('MenuRenderer', 'render')) {
            return MenuRenderer::render($menuName);
        }
        
        return '<!-- Метод render не найден в MenuRenderer -->';
    });
}

ShortcodeRegistry::add('menurender', function($attrs, $content = null) {
    if (isset($attrs['id'])) {
        return render_menu_by_id((int)$attrs['id']);
    }
    
    if (isset($attrs['slug'])) {
        return render_menu_by_name($attrs['slug']);
    }
    
    if (isset($attrs['name'])) {
        return render_menu_by_name($attrs['name']);
    }
    
    if (!empty($attrs[0])) {
        $param = $attrs[0];
        if (is_numeric($param)) {
            return render_menu_by_id((int)$param);
        } else {
            return render_menu_by_name($param);
        }
    }
    
    return '<!-- Ошибка: не указан идентификатор меню. Используйте: {menurender id="1"} или {menurender slug="main-menu"} -->';
});

ShortcodeRegistry::add('menu', function($attrs, $content = null) {
    if (isset($attrs['id'])) {
        return render_menu_by_id((int)$attrs['id']);
    }
    
    if (isset($attrs['slug'])) {
        return render_menu_by_name($attrs['slug']);
    }
    
    if (isset($attrs['name'])) {
        return render_menu_by_name($attrs['name']);
    }
    
    if (!empty($attrs[0])) {
        $param = $attrs[0];
        if (is_numeric($param)) {
            return render_menu_by_id((int)$param);
        } else {
            return render_menu_by_name($param);
        }
    }
    
    return '<!-- Ошибка: не указан идентификатор меню. Используйте: {menu id="1"} или {menu slug="main-menu"} -->';
});

/**
* Основная функция обработки всех шорткодов в контенте
* @param string $content Содержимое для обработки
* @param array $posts Массив постов для шорткода {posts}
* @param array $blocks Массив HTML-блоков для шорткода {add-block-*}
* @return string Обработанное содержимое
*/
function process_shortcodes(string $content, array $posts = [], array $blocks = []): string {
    
    if (empty($content)) {
        return $content;
    }
    
    if (preg_match_all('/\{css\s+["\']([^"\']+)["\']\}/', $content, $matches)) {
        foreach ($matches[1] as $cssFile) {
            if (function_exists('add_html_block_css')) {
                add_html_block_css($cssFile);
            }
        }
        $content = preg_replace('/\{css\s+["\']([^"\']+)["\']\}/', '', $content);
    }
    
    if (preg_match_all('/\{js\s+["\']([^"\']+)["\']\}/', $content, $matches)) {
        foreach ($matches[1] as $jsFile) {
            if (function_exists('add_html_block_js')) {
                add_html_block_js($jsFile);
            }
        }
        $content = preg_replace('/\{js\s+["\']([^"\']+)["\']\}/', '', $content);
    }
    
    if (preg_match_all('/\{css-inline\}(.*?)\{\/css-inline\}/s', $content, $matches)) {
        foreach ($matches[1] as $inlineCss) {
            if (function_exists('add_html_inline_css')) {
                add_html_inline_css($inlineCss);
            }
        }
        $content = preg_replace('/\{css-inline\}(.*?)\{\/css-inline\}/s', '', $content);
    }
    
    if (preg_match_all('/\{js-inline\}(.*?)\{\/js-inline\}/s', $content, $matches)) {
        foreach ($matches[1] as $inlineJs) {
            if (function_exists('add_html_inline_js')) {
                add_html_inline_js($inlineJs);
            }
        }
        $content = preg_replace('/\{js-inline\}(.*?)\{\/js-inline\}/s', '', $content);
    }
    
    if (preg_match_all('/\{add-block-([^\}]+)\}/', $content, $matches)) {
        foreach ($matches[0] as $key => $fullMatch) {
            $blockSlug = $matches[1][$key];
            $blockContent = '';
            
            foreach ($blocks as $block) {
                if (isset($block['slug']) && $block['slug'] === $blockSlug) {
                    $blockContent = $block['content'];
                    break;
                }
            }
            
            $blockContent = process_shortcodes($blockContent, $posts, $blocks);
            $content = str_replace($fullMatch, $blockContent, $content);
        }
    }
    
    if (preg_match('/\{posts(?:\s+limit=(\d+))?\}(.*?)\{\/posts\}/s', $content, $matches)) {
        $limit = isset($matches[1]) ? (int)$matches[1] : null;
        $postTemplate = $matches[2];
        $postsContent = '';
        
        $postsToProcess = $limit ? array_slice($posts, 0, $limit) : $posts;
        
        foreach ($postsToProcess as $post) {
            $postContent = $postTemplate;
            
            $postUrl = BASE_URL . '/post/' . ($post['slug'] ?? '');
            $categoryUrl = BASE_URL . '/category/' . ($post['category_slug'] ?? '');
            $imageUrl = isset($post['featured_image']) 
                ? BASE_URL . '/uploads/images/' . $post['featured_image']
                : '';
            
            $replacements = [
                '{post-title}' => htmlspecialchars($post['title'] ?? ''),
                '{post-description}' => htmlspecialchars($post['excerpt'] ?? ''),
                '{post-id}' => $post['id'] ?? '',
                '{post-slug}' => $postUrl,
                '{post-category}' => htmlspecialchars($post['category_name'] ?? ''),
                '{category-slug}' => $categoryUrl,
                '{post-image}' => $imageUrl,
                '{post-excerpt}' => nl2br(htmlspecialchars($post['excerpt'] ?? '')),
                '{post-meta}' => htmlspecialchars($post['meta_description'] ?? ''),
                '{post-tags}' => htmlspecialchars($post['tags'] ?? ''),
                '{post-created:date}' => format_date($post['created_at'] ?? ''),
                '{post-updated:date}' => format_date($post['updated_at'] ?? ''),
                '{post-created:ago}' => time_ago($post['created_at'] ?? ''),
                '{post-updated:ago}' => time_ago($post['updated_at'] ?? ''),
                '{post-created}' => htmlspecialchars($post['created_at'] ?? ''),
                '{post-updated}' => htmlspecialchars($post['updated_at'] ?? '')
            ];
            
            foreach ($replacements as $search => $replace) {
                $postContent = str_replace($search, $replace, $postContent);
            }
            
            $postsContent .= $postContent;
        }
        
        $content = str_replace($matches[0], $postsContent, $content);
    }
    
    if (preg_match_all('/\{if:([^\}]+)\}(.*?)\{\/if:\1\}/s', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $settingName = $match[1];
            $conditionalContent = $match[2];
            
            $shouldShow = false;
            
            if (isset($settings[$settingName]) && $settings[$settingName]) {
                $shouldShow = true;
            } elseif (isset($GLOBALS['block_settings'][$settingName]) && $GLOBALS['block_settings'][$settingName]) {
                $shouldShow = true;
            }
            
            $content = str_replace($fullMatch, $shouldShow ? $conditionalContent : '', $content);
        }
    }
    
    if (preg_match_all('/\{icon[\s]+[\'"]([^:]+):([^\'""]+)[\'"]((?:\s+(?:size=[\'"][\d\s]+[\'"]|color=[\'"][^\'""]+[\'"]))*)\}/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $set = $match[1];
            $icon = $match[2];
            $attrs = $match[3];
            $size = preg_match('/size=[\'"]([\d\s]+)[\'"]/', $attrs, $sizeMatch) ? $sizeMatch[1] : null;
            $color = preg_match('/color=[\'"]([^\'""]+)[\'"]/', $attrs, $colorMatch) ? $colorMatch[1] : null;
            
            if (function_exists('bloggy_icon')) {
                $content = str_replace($fullMatch, bloggy_icon($set, $icon, $size, $color), $content);
            }
        }
    }
    
    if (preg_match_all('/\{menurender:([^}\s]+)\}/', $content, $matches)) {
        foreach ($matches[0] as $index => $fullMatch) {
            $param = $matches[1][$index];
            $menuHtml = '';
            
            try {
                if (is_numeric($param)) {
                    $menuHtml = render_menu_by_id((int)$param);
                } else {
                    $menuHtml = render_menu_by_name($param);
                }
                
                if (empty($menuHtml)) {
                    $menuHtml = '<!-- Меню "' . htmlspecialchars($param) . '" не найдено -->';
                }
                
                $content = str_replace($fullMatch, $menuHtml, $content);
            } catch (Exception $e) {
                $content = str_replace($fullMatch, '<!-- Ошибка загрузки меню: ' . $e->getMessage() . ' -->', $content);
            }
        }
    }
    
    if (preg_match_all('/\{menurender(?:\s+id=["\'](\d+)["\']|\s+slug=["\']([^"\']+)["\']|\s+name=["\']([^"\']+)["\'])/i', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $menuHtml = '';
            
            try {
                if (!empty($match[1])) {
                    $menuId = (int)$match[1];
                    $menuHtml = render_menu_by_id($menuId);
                } elseif (!empty($match[2]) || !empty($match[3])) {
                    $slug = !empty($match[2]) ? $match[2] : $match[3];
                    $menuHtml = render_menu_by_name($slug);
                }
                
                if (empty($menuHtml)) {
                    $menuHtml = '<!-- Меню не найдено -->';
                }
                
                $content = str_replace($fullMatch, $menuHtml, $content);
            } catch (Exception $e) {
                $content = str_replace($fullMatch, '<!-- Ошибка загрузки меню: ' . $e->getMessage() . ' -->', $content);
            }
        }
    }
    
    $content = preg_replace_callback(
        '/\{front-image:([^:}]+)(?::([^}]+))?\}/i',
        function($matches) {
            if (function_exists('front_image')) {
                return front_image($matches[1], $matches[2] ?? '');
            }
            return $matches[0];
        },
        $content
    );

    if (class_exists('ShortcodeRegistry')) {
        $content = ShortcodeRegistry::process($content);
    }
    
    return $content;
}