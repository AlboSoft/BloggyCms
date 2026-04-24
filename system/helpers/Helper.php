<?php

/**
* Функция для перенаправления на главную страницу фронта.
* @return void
*/
function go_home(): void {
    header('Location: /');
    exit;
}

/**
* Функция для получения URL главной страницы.
* @return string URL главной страницы.
*/
function get_home_url(): string {
    return BASE_URL;
}

/**
* Функция для генерации пути к изображению в шаблоне.
* @param string $file Имя файла изображения (например, "logo.png").
* @param string $subpath Подпапка внутри assets/img/ (например, "logo/").
* @return string Полный URL к изображению.
*/
function front_image(string $file, string $subpath = ''): string {
    $subpath = trim($subpath, '/');
    
    if (!empty($subpath)) {
        $subpath .= '/';
    }
    
    return BASE_URL . '/templates/' . DEFAULT_TEMPLATE . '/front/assets/img/' . $subpath . $file;
}

/**
* Глобальный массив для хранения зарегистрированных блоков
*/
$GLOBALS['_registered_blocks_slugs'] = [];

/**
* Выводит содержимое HTML-блока по его slug.
* @param string $slug Уникальный идентификатор блока.
* @return void
*/
function render_html_block(string $slug): void {
    if (!in_array($slug, $GLOBALS['_registered_blocks_slugs'])) {
        $GLOBALS['_registered_blocks_slugs'][] = $slug;
    }
    
    static $loaded_blocks = [];
    
    $db = Database::getInstance();
    
    $block = $db->fetch("
        SELECT 
            hb.*, 
            COALESCE(hbt.system_name, 'DefaultBlock') as block_type,
            hb.template as block_template
        FROM html_blocks hb 
        LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
        WHERE hb.slug = ?
    ", [$slug]);

    if ($block) {
        if (!isset($loaded_blocks[$slug])) {
            load_block_js_assets($block);
            $loaded_blocks[$slug] = true;
        }
        
        $content = '';
        
        $settings = [];
        if (!empty($block['settings'])) {
            $settings = json_decode($block['settings'], true);
        }
        
        $blockType = $block['block_type'] ?? 'DefaultBlock';
        
        if ($blockType === 'DefaultBlock') {
            $content = $settings['html'] ?? '';
            
            if (function_exists('process_shortcodes')) {
                $content = process_shortcodes($content);
            }
            
            if (empty(trim($content))) {
                $content = sprintf(LANG_HELPER_FUNCTIONS_BLOCK_EMPTY, htmlspecialchars($block['name'] ?? ''));
            }
        } elseif (!empty($blockType)) {
            $blockTypeManager = new HtmlBlockTypeManager($db);
            $templateToUse = $block['block_template'] ?? 'default';
            $content = $blockTypeManager->renderBlockFront($blockType, $settings, $templateToUse);
        } else {
            $content = sprintf(LANG_HELPER_FUNCTIONS_BLOCK_UNDEFINED, htmlspecialchars($block['name'] ?? ''));
        }
        
        echo $content;
    } else {
        echo '<!-- ' . sprintf(LANG_HELPER_FUNCTIONS_BLOCK_NOT_FOUND, htmlspecialchars($slug)) . ' -->';
    }
}

/**
* Получает ассеты всех HTML-блоков с кешированием
* @param bool $forceRefresh Принудительно обновить кеш
* @return array Массив с ассетами всех блоков
*/
function get_all_blocks_assets_cached($forceRefresh = false): array {
    $cacheFile = CACHE_DIR . '/blocks_assets.cache';
    $cacheTime = 3600;
    
    if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        $cached = unserialize(file_get_contents($cacheFile));
        if ($cached && is_array($cached)) {
            return $cached;
        }
    }
    
    $db = Database::getInstance();
    
    $blocks = $db->fetchAll("
        SELECT 
            hb.id, 
            hb.slug, 
            hb.css_files, 
            hb.js_files, 
            hb.inline_css, 
            hb.inline_js,
            COALESCE(hbt.system_name, 'DefaultBlock') as block_type
        FROM html_blocks hb 
        LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id
    ");
    
    $allAssets = [
        'css' => [],
        'js' => [],
        'inline_css' => [],
        'inline_js' => [],
        'blocks_map' => [],
        'last_update' => time()
    ];
    
    foreach ($blocks as $block) {
        $allAssets['blocks_map'][$block['slug']] = [
            'css' => [],
            'js' => [],
            'inline_css' => [],
            'inline_js' => []
        ];
        
        if (!empty($block['css_files'])) {
            $cssFiles = json_decode($block['css_files'], true);
            if (is_array($cssFiles)) {
                $allAssets['css'] = array_merge($allAssets['css'], $cssFiles);
                $allAssets['blocks_map'][$block['slug']]['css'] = $cssFiles;
            }
        }
        
        if (!empty($block['js_files'])) {
            $jsFiles = json_decode($block['js_files'], true);
            if (is_array($jsFiles)) {
                $allAssets['js'] = array_merge($allAssets['js'], $jsFiles);
                $allAssets['blocks_map'][$block['slug']]['js'] = $jsFiles;
            }
        }
        
        if (!empty($block['inline_css'])) {
            $allAssets['inline_css'][] = $block['inline_css'];
            $allAssets['blocks_map'][$block['slug']]['inline_css'][] = $block['inline_css'];
        }
        if (!empty($block['inline_js'])) {
            $allAssets['inline_js'][] = $block['inline_js'];
            $allAssets['blocks_map'][$block['slug']]['inline_js'][] = $block['inline_js'];
        }
        
        if (!empty($block['block_type']) && $block['block_type'] !== 'DefaultBlock') {
            $blockTypeManager = new HtmlBlockTypeManager($db);
            $blockTypeData = $blockTypeManager->getBlockType($block['block_type']);
            if ($blockTypeData && $blockTypeData['class']) {
                $blockInstance = $blockTypeData['class'];
                
                $systemCss = $blockInstance->getSystemCss();
                $frontendCss = $blockInstance->getFrontendCss();
                $allAssets['css'] = array_merge($allAssets['css'], $systemCss, $frontendCss);
                $allAssets['blocks_map'][$block['slug']]['css'] = array_merge(
                    $allAssets['blocks_map'][$block['slug']]['css'],
                    $systemCss,
                    $frontendCss
                );
                
                $systemJs = $blockInstance->getSystemJs();
                $frontendJs = $blockInstance->getFrontendJs();
                $allAssets['js'] = array_merge($allAssets['js'], $systemJs, $frontendJs);
                $allAssets['blocks_map'][$block['slug']]['js'] = array_merge(
                    $allAssets['blocks_map'][$block['slug']]['js'],
                    $systemJs,
                    $frontendJs
                );
                
                if ($blockInstance->getFrontendInlineCss()) {
                    $allAssets['inline_css'][] = $blockInstance->getFrontendInlineCss();
                    $allAssets['blocks_map'][$block['slug']]['inline_css'][] = $blockInstance->getFrontendInlineCss();
                }
                if ($blockInstance->getFrontendInlineJs()) {
                    $allAssets['inline_js'][] = $blockInstance->getFrontendInlineJs();
                    $allAssets['blocks_map'][$block['slug']]['inline_js'][] = $blockInstance->getFrontendInlineJs();
                }
            }
        }
    }
    
    $allAssets['css'] = array_values(array_unique($allAssets['css']));
    $allAssets['js'] = array_values(array_unique($allAssets['js']));
    $allAssets['inline_css'] = array_values(array_unique($allAssets['inline_css']));
    $allAssets['inline_js'] = array_values(array_unique($allAssets['inline_js']));
    
    file_put_contents($cacheFile, serialize($allAssets));
    
    return $allAssets;
}

/**
* Генерирует общий CSS файл для всех блоков
* @return string Путь к сгенерированному CSS файлу
*/
function regenerate_blocks_css(): string {
    $cacheFile = CACHE_DIR . '/blocks.css';
    $allAssets = get_all_blocks_assets_cached(true);
    
    $css = '';
    
    if (!empty($allAssets['css'])) {
        foreach ($allAssets['css'] as $cssFile) {
            $fullPath = BASE_PATH . '/' . $cssFile;
            if (file_exists($fullPath)) {
                $css .= "/* === " . $cssFile . " === */\n";
                $css .= file_get_contents($fullPath);
                $css .= "\n\n";
            } else {
                error_log("CSS file not found: " . $fullPath);
            }
        }
    }
    
    if (!empty($allAssets['inline_css'])) {
        $css .= "/* === " . LANG_HELPER_FUNCTIONS_INLINE_CSS_COMMENT . " === */\n";
        foreach ($allAssets['inline_css'] as $inlineCss) {
            $css .= $inlineCss;
            $css .= "\n";
        }
        $css .= "\n";
    }
    
    $css = minify_css($css);
    
    file_put_contents($cacheFile, $css);
    chmod($cacheFile, 0644);
    
    return $cacheFile;
}

/**
* Загружает JS ассеты блока
* @param array $block Данные блока
*/
function load_block_js_assets($block): void {
    if (!empty($block['js_files'])) {
        $jsFiles = json_decode($block['js_files'], true);
        if (is_array($jsFiles)) {
            foreach ($jsFiles as $jsFile) {
                if (!empty(trim($jsFile))) {
                    front_js($jsFile);
                }
            }
        }
    }
    
    if (!empty($block['inline_js'])) {
        front_inline_js($block['inline_js']);
    }
    
    if (!empty($block['block_type']) && $block['block_type'] !== 'DefaultBlock') {
        $db = Database::getInstance();
        $blockTypeManager = new HtmlBlockTypeManager($db);
        $blockTypeData = $blockTypeManager->getBlockType($block['block_type']);
        if ($blockTypeData && $blockTypeData['class']) {
            $blockInstance = $blockTypeData['class'];
            
            foreach ($blockInstance->getSystemJs() as $jsFile) {
                front_js($jsFile);
            }
            foreach ($blockInstance->getFrontendJs() as $jsFile) {
                front_js($jsFile);
            }
            
            if ($blockInstance->getFrontendInlineJs()) {
                front_inline_js($blockInstance->getFrontendInlineJs());
            }
        }
    }
}

/**
* Минификация CSS
* @param string $css Исходный CSS
* @return string Минифицированный CSS
*/
function minify_css(string $css): string {
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{}|:;,])\s*/', '$1', $css);
    $css = str_replace(';}', '}', $css);
    
    return trim($css);
}

/**
* Получает URL сгенерированного CSS файла блоков
* @return string URL для подключения
*/
function get_blocks_css_url(): string {
    $cacheFile = CACHE_DIR . '/blocks.css';
    
    if (!file_exists($cacheFile)) {
        regenerate_blocks_css();
    }
    
    $version = filemtime($cacheFile);
    return BASE_URL . '/cache/blocks.css?v=' . $version;
}

/**
* Инициализация системы кеширования блоков
*/
function init_blocks_cache(): void {
    $cacheFile = CACHE_DIR . '/blocks.css';
    
    if (!file_exists($cacheFile) || filesize($cacheFile) === 0) {
        regenerate_blocks_css();
    }
}

/**
* Очищает кеш ассетов блоков
*/
function clear_blocks_assets_cache(): void {
    $cacheFile = CACHE_DIR . '/blocks_assets.cache';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

/**
* Форматирует дату в формате "23 июня 2025".
* @param string $date Дата в формате, понятном для strtotime
* @return string Отформатированная дата
*/
function format_date($date) {
    if (!$date) return '';
    
    $months = [
        1 => LANG_HELPER_FUNCTIONS_MONTH_JANUARY,
        2 => LANG_HELPER_FUNCTIONS_MONTH_FEBRUARY,
        3 => LANG_HELPER_FUNCTIONS_MONTH_MARCH,
        4 => LANG_HELPER_FUNCTIONS_MONTH_APRIL,
        5 => LANG_HELPER_FUNCTIONS_MONTH_MAY,
        6 => LANG_HELPER_FUNCTIONS_MONTH_JUNE,
        7 => LANG_HELPER_FUNCTIONS_MONTH_JULY,
        8 => LANG_HELPER_FUNCTIONS_MONTH_AUGUST,
        9 => LANG_HELPER_FUNCTIONS_MONTH_SEPTEMBER,
        10 => LANG_HELPER_FUNCTIONS_MONTH_OCTOBER,
        11 => LANG_HELPER_FUNCTIONS_MONTH_NOVEMBER,
        12 => LANG_HELPER_FUNCTIONS_MONTH_DECEMBER
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

/**
* Возвращает время, прошедшее с указанной даты в человекочитаемом формате
* @param string $date Дата в формате, понятном для strtotime
* @return string Отформатированное время
*/
function time_ago($date) {
    if (!$date) return '';
    
    $timestamp = strtotime($date);
    $current = time();
    $diff = $current - $timestamp;
    
    $intervals = [
        'year'   => 31536000,
        'month'  => 2592000,
        'week'   => 604800,
        'day'    => 86400,
        'hour'   => 3600,
        'minute' => 60
    ];
    
    $forms = [
        'year'   => [LANG_HELPER_FUNCTIONS_YEAR_1, LANG_HELPER_FUNCTIONS_YEAR_2, LANG_HELPER_FUNCTIONS_YEAR_3],
        'month'  => [LANG_HELPER_FUNCTIONS_MONTH_1, LANG_HELPER_FUNCTIONS_MONTH_2, LANG_HELPER_FUNCTIONS_MONTH_3],
        'week'   => [LANG_HELPER_FUNCTIONS_WEEK_1, LANG_HELPER_FUNCTIONS_WEEK_2, LANG_HELPER_FUNCTIONS_WEEK_3],
        'day'    => [LANG_HELPER_FUNCTIONS_DAY_1, LANG_HELPER_FUNCTIONS_DAY_2, LANG_HELPER_FUNCTIONS_DAY_3],
        'hour'   => [LANG_HELPER_FUNCTIONS_HOUR_1, LANG_HELPER_FUNCTIONS_HOUR_2, LANG_HELPER_FUNCTIONS_HOUR_3],
        'minute' => [LANG_HELPER_FUNCTIONS_MINUTE_1, LANG_HELPER_FUNCTIONS_MINUTE_2, LANG_HELPER_FUNCTIONS_MINUTE_3]
    ];
    
    foreach ($intervals as $interval => $seconds) {
        $count = floor($diff / $seconds);
        if ($count > 0) {
            $form = function($n, $forms) {
                return $n%10==1 && $n%100!=11 ? $forms[0] : ($n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ? $forms[1] : $forms[2]);
            };
            
            return $count . ' ' . $form($count, $forms[$interval]) . ' ' . LANG_HELPER_FUNCTIONS_AGO;
        }
    }
    
    return LANG_HELPER_FUNCTIONS_JUST_NOW;
}

/**
* Форматирует число в сокращенном виде
* @param int $number Число для форматирования.
* @return string Отформатированное число.
*/
function format_number(int $number): string {
    if ($number < 1000) {
        return (string)$number;
    } elseif ($number < 1000000) {
        return number_format($number / 1000, 1) . 'K';
    } else {
        return number_format($number / 1000000, 1) . 'M';
    }
}

/**
* Функция для вывода selected в select-опциях
* @param mixed $value Значение опции
* @param mixed $current Текущее значение
* @param bool $echo Выводить или возвращать
* @return string HTML-атрибут selected
*/
function selected($value, $current, $echo = true) {
    $result = $value == $current ? 'selected="selected"' : '';
    if ($echo) {
        echo $result;
    }
    return $result;
}

/**
* Функция для вывода checked в checkbox-опциях
* @param mixed $value Значение опции
* @param mixed $current Текущее значение
* @param bool $echo Выводить или возвращать
* @return string HTML-атрибут checked
*/
function checked($value, $current, $echo = true) {
    $result = $value == $current ? 'checked="checked"' : '';
    if ($echo) {
        echo $result;
    }
    return $result;
}

/**
* Подключает фавиконку
* @param string|null $path Путь к фавиконке
* @return string HTML-тег link для фавиконки
*/
function favicon($path = null) {
    if ($path === null) {
        $path = BASE_URL . '/templates/default/admin/assets/img/favicon.png';
    }
    
    return '<link rel="icon" type="image/png" href="' . htmlspecialchars($path) . '">' . "\n";
}

/**
* Возвращает название текущего активного шаблона
* @return string Название шаблона
*/
function get_current_template(): string {
    return defined('CURRENT_TEMPLATE') ? CURRENT_TEMPLATE : 'default';
}

/**
* Проверяет, доступен ли блок для текущего шаблона
* @param mixed $blockTemplate Шаблон блока
* @return bool Всегда true (заглушка)
*/
function is_block_available_for_template($blockTemplate): bool {
    return true;
}

/**
* Склонение числительных в русском языке.
* @param int $number Число
* @param array $titles Массив форм слова
* @return string Правильная форма слова
*/
function plural($number, $titles) {
    $lastTwoDigits = abs($number) % 100;
    $lastDigit = $lastTwoDigits % 10;
    
    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
        return $titles[2];
    }
    
    if ($lastDigit == 1) {
        return $titles[0];
    } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
        return $titles[1];
    } else {
        return $titles[2];
    }
}