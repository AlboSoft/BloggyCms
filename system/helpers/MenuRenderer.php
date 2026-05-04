<?php

/**
* Класс для рендеринга меню на сайте
* @package Core
*/
class MenuRenderer {
    
    /**
    * Рендерит меню по его ID
    * @param int $menuId ID меню
    * @param string|null $currentTheme Название темы (опционально)
    * @return string HTML-код меню
    */
    public static function renderById($menuId, $currentTheme = null) {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        
        $menu = $menuModel->getById($menuId);
        if (!$menu || $menu['status'] !== 'active') {
            return '<!-- ' . LANG_HELPER_MENURENDERER_NOT_FOUND . ' -->';
        }
        
        return self::renderMenu($menu, $currentTheme);
    }
    
    /**
    * Рендерит меню по его названию
    * @param string $menuName Название меню
    * @param string|null $currentTheme Название темы (опционально)
    * @return string HTML-код меню
    */
    public static function render($menuName, $currentTheme = null) {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        
        $menu = $menuModel->getByName($menuName);
        if (!$menu || $menu['status'] !== 'active') {
            return '<!-- ' . LANG_HELPER_MENURENDERER_NOT_FOUND . ' -->';
        }
        
        return self::renderMenu($menu, $currentTheme);
    }
    
    /**
    * Рендерит меню по шаблону (возвращает первое активное меню с этим шаблоном) 
    * @param string $template Название шаблона
    * @param string|null $currentTheme Название темы (опционально)
    * @return string HTML-код меню
    */
    public static function renderByTemplate($template, $currentTheme = null) {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        
        $menu = $menuModel->getByTemplate($template);
        if (!$menu) {
            return '<!-- ' . sprintf(LANG_HELPER_MENURENDERER_NO_MENU_FOR_TEMPLATE, $template) . ' -->';
        }
        
        return self::renderMenu($menu, $currentTheme);
    }

    /**
    * Рендерит все меню для указанного шаблона 
    * @param string $template Название шаблона
    * @param string|null $currentTheme Название темы (опционально)
    * @return string HTML-код всех меню
    */
    public static function renderAllByTemplate($template, $currentTheme = null) {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        
        $menus = $menuModel->getAllByTemplate($template);
        if (empty($menus)) {
            return '<!-- ' . sprintf(LANG_HELPER_MENURENDERER_NO_MENUS_FOR_TEMPLATE, $template) . ' -->';
        }
        
        $html = '';
        foreach ($menus as $menu) {
            $html .= self::renderMenu($menu, $currentTheme);
        }
        
        return $html;
    }

    /**
    * Основной метод рендеринга меню
    * @param array $menu Данные меню
    * @param string|null $currentTheme Название темы
    * @return string HTML-код меню
    */
    private static function renderMenu($menu, $currentTheme = null) {
        $structure = json_decode($menu['structure'], true);
        if (empty($structure)) {
            return '<!-- ' . sprintf(LANG_HELPER_MENURENDERER_EMPTY_MENU, $menu['name']) . ' -->';
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        
        $userGroups = $menuModel->getUserGroups($userId);
        $filteredStructure = $menuModel->filterMenuByUserGroups($structure, $userGroups);
        
        if (empty($filteredStructure)) {
            return '<!-- ' . LANG_HELPER_MENURENDERER_FILTERED_EMPTY . ' -->';
        }
        
        $useCustomTemplate = !empty($menu['use_custom_template']) && !empty($menu['custom_template']);
        
        if ($useCustomTemplate) {
            $currentUrl = $_SERVER['REQUEST_URI'];
            $customTemplate = $menu['custom_template'];
            
            if (!class_exists('CustomMenuParser')) {
                require_once SYSTEM_PATH . '/helpers/CustomMenuParser.php';
            }
            
            return CustomMenuParser::parse($customTemplate, $filteredStructure, $currentUrl);
        }
        
        if ($currentTheme === null) {
            $currentTheme = $menuModel->getCurrentTheme();
        }
        
        $templateFile = TEMPLATES_PATH . '/' . $currentTheme . '/front/assets/menu/' . $menu['template'] . '.php';
        
        if (!file_exists($templateFile)) {
            return self::renderDefault($filteredStructure, $menu);
        }
        
        ob_start();
        $menuItems = $filteredStructure;
        $menuData = $menu;
        include $templateFile;
        return ob_get_clean();
    }
    
    /**
    * Рендерит меню по умолчанию (если шаблон не найден)
    * @param array $structure Структура меню
    * @param array|null $menu Данные меню
    * @return string HTML-код меню
    */
    private static function renderDefault($structure, $menu = null) {
        $menuClass = $menu ? 'menu-' . $menu['id'] : 'menu-default';
        $html = '<ul class="menu ' . $menuClass . '">';
        
        foreach ($structure as $item) {
            $html .= self::renderMenuItem($item);
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    /**
    * Рендерит один пункт меню (рекурсивно)
    * @param array $item Данные пункта меню
    * @param int $level Уровень вложенности
    * @return string HTML-код пункта меню
    */
    private static function renderMenuItem($item, $level = 0) {
        $class = $item['class'] ?? '';
        $target = $item['target'] ?? '_self';
        $title = htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8');
        
        $url = self::processUrl($item['url'] ?? '');
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        
        $iconHtml = '';
        if (!empty($item['icon'])) {
            $iconSize = !empty($item['icon']['size']) ? $item['icon']['size'] : 16;
            $iconColor = !empty($item['icon']['color']) ? $item['icon']['color'] : 'currentColor';
            $iconSet = $item['icon']['set'] ?? 'bootstrap';
            $iconId = $item['icon']['id'] ?? '';
            
            if ($iconId) {
                $iconHtml = bloggy_icon($iconSet, $iconId, "$iconSize $iconSize", $iconColor) . ' ';
            }
        }
        
        $html = '<li class="menu-item' . ($class ? ' ' . $class : '') . '">';
        $html .= '<a href="' . $url . '" target="' . $target . '">';
        
        if (!empty($item['icon_only']) && $item['icon_only']) {
            $html .= $iconHtml;
            $html .= '<span class="visually-hidden">' . $title . '</span>';
        } else {
            $html .= $iconHtml . $title;
        }
        
        $html .= '</a>';
        
        if (!empty($item['children'])) {
            $html .= '<ul class="submenu level-' . ($level + 1) . '">';
            foreach ($item['children'] as $child) {
                $html .= self::renderMenuItem($child, $level + 1);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        return $html;
    }
    
    /**
    * Обрабатывает URL, заменяя шорткоды 
    * @param string $url Исходный URL с шорткодами
    * @return string URL с подставленными значениями
    */
    private static function processUrl($url) {
        if (empty($url)) {
            return $url;
        }
        
        $userData = self::getCurrentUserData();
        
        $url = self::replaceShortcodes($url, $userData);
        
        return $url;
    }
    
    /**
    * Заменяет шорткоды в URL
    * @param string $url URL с шорткодами
    * @param array $userData Данные пользователя
    * @return string Обработанный URL
    */
    private static function replaceShortcodes($url, $userData) {
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
        
        if (preg_match_all('/\{user_field:([^}]+)\}/', $url, $matches)) {
            foreach ($matches[0] as $index => $fullMatch) {
                $fieldName = $matches[1][$index];
                $fieldValue = $userData[$fieldName] ?? '';
                $url = str_replace($fullMatch, $fieldValue, $url);
            }
        }
        
        foreach ($shortcodes as $shortcode => $replacement) {
            $url = str_replace($shortcode, $replacement, $url);
        }
        
        return $url;
    }
    
    /**
    * Получает данные текущего пользователя 
    * @return array Массив с данными пользователя
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
    * Получает настройку сайта 
    * @param string $key Ключ настройки
    * @return string Значение настройки
    */
    private static function getSiteSetting($key) {
        try {
            if (class_exists('SettingsHelper')) {
                return SettingsHelper::get('site', $key) ?? '';
            }
            
            $db = Database::getInstance();
            $setting = $db->fetch(
                "SELECT value FROM settings WHERE section = 'site' AND name = ?",
                [$key]
            );
            
            return $setting ? $setting['value'] : '';
            
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
    * Проверяет активен ли URL (для выделения текущей страницы) 
    * @param string $url URL для проверки
    * @param string|null $currentUrl Текущий URL (опционально)
    * @return bool true если URL активен
    */
    public static function isActiveUrl($url, $currentUrl = null) {
        if ($currentUrl === null) {
            $currentUrl = $_SERVER['REQUEST_URI'];
        }
        
        $processedUrl = self::processUrl($url);
        
        if ($processedUrl === $currentUrl) {
            return true;
        }
        
        if ($processedUrl === '/' && $currentUrl === '/') {
            return true;
        }
        
        if ($processedUrl !== '/' && strpos($currentUrl, $processedUrl) === 0) {
            return true;
        }
        
        return false;
    }

    /**
    * Получает информацию о меню по ID 
    * @param int $menuId ID меню
    * @return array|null Данные меню
    */
    public static function getMenuInfo($menuId) {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        
        return $menuModel->getById($menuId);
    }

    /**
    * Получает список всех активных меню 
    * @return array Массив активных меню
    */
    public static function getAllActiveMenus() {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        
        return $menuModel->getAllActive();
    }

    /**
    * Получает список всех меню для выбора в админке
    * Формат: [id => 'Название (ID: X, Шаблон: Y)'] 
    * @return array Массив меню для select
    */
    public static function getAllMenusForSelect() {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        
        $menus = $menuModel->getAll();
        $result = [];
        
        foreach ($menus as $menu) {
            $result[$menu['id']] = $menu['name'] . ' (ID: ' . $menu['id'] . ', ' . sprintf(LANG_HELPER_MENURENDERER_TEMPLATE_LABEL, $menu['template']) . ')';
        }
        
        return $result;
    }

    /**
    * Получает меню по имени 
    * @param string $name Название меню
    * @return array|null Данные меню
    */
    public static function getByName($name) {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        return $menuModel->getByName($name);
    }

    /**
    * Получает все меню по шаблону
    * @param string $template Название шаблона
    * @return array Массив меню
    */
    public static function getAllByTemplate($template) {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        return $menuModel->getAllByTemplate($template);
    }

    /**
    * Получает все активные меню
    * @return array Массив активных меню
    */
    public static function getAllActive() {
        $db = Database::getInstance();
        $menuModel = new MenuModel($db);
        return $menuModel->getAllActive();
    }
    
    /**
    * Список доступных шорткодов для справки в админке 
    * @return array Массив с категориями шорткодов
    */
    public static function getAvailableShortcodes() {
        return [
            LANG_HELPER_MENURENDERER_SHORTCODE_BASIC => [
                '{user_id}' => LANG_HELPER_MENURENDERER_SHORTCODE_USER_ID,
                '{username}' => LANG_HELPER_MENURENDERER_SHORTCODE_USERNAME,
                '{email}' => LANG_HELPER_MENURENDERER_SHORTCODE_EMAIL,
                '{first_name}' => LANG_HELPER_MENURENDERER_SHORTCODE_FIRST_NAME,
                '{last_name}' => LANG_HELPER_MENURENDERER_SHORTCODE_LAST_NAME,
                '{display_name}' => LANG_HELPER_MENURENDERER_SHORTCODE_DISPLAY_NAME,
                '{slug}' => LANG_HELPER_MENURENDERER_SHORTCODE_SLUG,
            ],
            LANG_HELPER_MENURENDERER_SHORTCODE_SYSTEM => [
                '{base_url}' => LANG_HELPER_MENURENDERER_SHORTCODE_BASE_URL,
                '{admin_url}' => LANG_HELPER_MENURENDERER_SHORTCODE_ADMIN_URL,
                '{site_name}' => LANG_HELPER_MENURENDERER_SHORTCODE_SITE_NAME,
            ],
            LANG_HELPER_MENURENDERER_SHORTCODE_DATE => [
                '{year}' => LANG_HELPER_MENURENDERER_SHORTCODE_YEAR,
                '{month}' => LANG_HELPER_MENURENDERER_SHORTCODE_MONTH,
                '{day}' => LANG_HELPER_MENURENDERER_SHORTCODE_DAY,
            ],
            LANG_HELPER_MENURENDERER_SHORTCODE_CUSTOM => [
                '{user_field:field_name}' => LANG_HELPER_MENURENDERER_SHORTCODE_USER_FIELD,
            ]
        ];
    }
}