<?php

/**
* Определяет текущий раздел админки на основе URI запроса
* Для главной страницы админки возвращает '/' 
* @return string Название текущего раздела (например, 'users', 'settings')
*/
function get_current_admin_section() {
    $currentUri = $_SERVER['REQUEST_URI'];
    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
    $relativeUri = str_replace($basePath, '', $currentUri);
    $pathParts = explode('/', trim($relativeUri, '/'));
    $currentSection = '';
    if (isset($pathParts[0]) && $pathParts[0] === 'admin') {
        $currentSection = $pathParts[1] ?? '';
    }
    
    if ($currentUri === ADMIN_URL . '/' || $currentUri === ADMIN_URL || empty($currentSection)) {
        $currentSection = '/';
    }
    
    return $currentSection;
}

/**
* Проверяет, является ли указанный раздел текущим активным
* @param string $section Название раздела для проверки
* @return bool true если раздел активен
*/
function is_admin_section_active($section) {
    $currentSection = get_current_admin_section();
    return $currentSection === $section;
}

/**
* Возвращает CSS класс для активного пункта меню
* @param string $section Название раздела
* @param string $additionalClass Дополнительные CSS классы
* @return string Строка с классами
*/
function get_admin_menu_class($section, $additionalClass = '') {
    $classes = [];
    
    if ($additionalClass) {
        $classes[] = $additionalClass;
    }
    
    if (is_admin_section_active($section)) {
        $classes[] = 'active';
    }
    
    return implode(' ', $classes);
}

/**
* Создает пункт меню админки с автоматическим определением активности
* @param string $section Название раздела (для определения активности)
* @param string $url URL ссылки
* @param string $icon Название иконки Bootstrap Icons
* @param string $title Текст пункта меню
* @param int|null $counter Количество записей для отображения (null - не показывать)
* @return string HTML-код пункта меню
*/
function admin_menu_item($section, $url, $icon, $title, $counter = null) {
    $class = get_admin_menu_class($section, 'nav-link d-flex align-items-center');
    $iconHtml = bloggy_icon('bs', $icon, '20 20', '#fff; margin-right:10px');
    
    $counterHtml = '';
    if ($counter !== null && SettingsHelper::get('controller_admin', 'show_menu_counters', false)) {
        $counterHtml = '<span class="menu-counter">' . html($counter) . '</span>';
    }
    
    return <<<HTML
    <a class="$class" href="$url">
        $iconHtml $title $counterHtml
    </a>
HTML;
}