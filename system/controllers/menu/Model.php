<?php

/**
* Модель меню
* @package models
*/
class MenuModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getByTemplate', 
        'getAllByTemplate',
        'getAllActive',
        'getActiveById',
        'getActiveByName',
        'getAvailableTemplates',
        'getAllUserGroups',
        'filterMenuByUserGroups',
        'shouldShowMenuItem'
    ];
    
    private $db;
    
    /**
    * Конструктор модели меню
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
    * Создание нового меню
    * @param array $data Массив данных меню
    * @return int ID созданного меню
    */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['use_custom_template'] = isset($data['use_custom_template']) ? (int)$data['use_custom_template'] : 0;
        $data['custom_template'] = $data['custom_template'] ?? null;
        
        $this->db->insert('menus', $data);
        return $this->db->lastInsertId();
    }
    
    /**
    * Обновление существующего меню
    * @param int $id ID обновляемого меню
    * @param array $data Массив данных для обновления
    * @return bool Результат выполнения операции
    */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if (isset($data['use_custom_template'])) {
            $data['use_custom_template'] = (int)$data['use_custom_template'];
        }
        
        $updateData = [];
        $allowedFields = ['name', 'template', 'status', 'structure', 
                          'use_custom_template', 'custom_template'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        
        return $this->db->update('menus', $updateData, ['id' => $id]) > 0;
    }

    /**
    * Получить меню для рендеринга
    */
    public function getForRendering($id) {
        $menu = $this->getById($id);
        if (!$menu) return null;
        
        $menu['structure'] = json_decode($menu['structure'], true) ?: [];
        
        return $menu;
    }
    
    /**
    * Удаление меню
    * @param int $id ID удаляемого меню
    * @return bool Результат выполнения операции
    */
    public function delete($id) {
        return $this->db->delete('menus', ['id' => $id]) > 0;
    }
    
    /**
    * Получение меню по ID
    * @param int $id ID меню
    * @return array|null Данные меню или null если не найдено
    */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE id = ?", 
            [(int)$id]
        );
    }
    
    /**
    * Получение всех меню
    * @return array Массив всех меню
    */
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT * FROM menus ORDER BY created_at DESC"
        );
    }
    
    /**
    * Получение меню по названию
    * @param string $name Название меню
    * @return array|null Данные меню или null если не найдено
    */
    public function getByName($name) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE name = ?", 
            [$name]
        );
    }
    
    /**
    * Получение активного меню для указанного шаблона
    * @param string $template Название шаблона
    * @return array|null Данные меню или null если не найдено
    */
    public function getByTemplate($template) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE template = ? AND status = 'active'", 
            [$template]
        );
    }
    
    /**
    * Получение всех доступных шаблонов меню из папки текущей темы
    * @return array Ассоциативный массив доступных шаблонов меню
    */
    public function getAvailableTemplates() {
        $templates = [];
        $currentTheme = $this->getCurrentTheme();
        
        $menuTemplatesPath = TEMPLATES_PATH . '/' . $currentTheme . '/front/assets/menu';
        
        if (!is_dir($menuTemplatesPath)) {
            
            if (!mkdir($menuTemplatesPath, 0755, true)) {
            }
            return $templates;
        }
        
        $files = scandir($menuTemplatesPath);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $templateName = pathinfo($file, PATHINFO_FILENAME);
                $templates[$templateName] = $templateName;
            }
        }
        
        return $templates;
    }
    
    /**
    * Валидация структуры меню
    * @param array $structure Структура меню для проверки
    * @return bool Результат валидации
    */
    public function validateMenuStructure($structure) {
        if (!is_array($structure)) {
            return false;
        }
        
        foreach ($structure as $item) {
            if (!isset($item['title']) || empty(trim($item['title']))) {
                return false;
            }
            
            if (!isset($item['url']) || empty(trim($item['url']))) {
                return false;
            }
            
            if (isset($item['children']) && is_array($item['children'])) {
                if (!$this->validateMenuStructure($item['children'])) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
    * Получение текущего активного шаблона из настроек
    * @return string Название текущей темы
    */
    public function getCurrentTheme() {
        try {
            $theme = SettingsHelper::get('site', 'site_template');
            
            if (empty($theme)) {
                $theme = SettingsHelper::get('site', 'theme');
            }
            
            if (empty($theme)) {
                $theme = SettingsHelper::getCurrentTemplate();
            }
            
            if (empty($theme)) {
                $theme = 'default';
            }
            
            return $theme;
        } catch (Exception $e) {
            return 'default';
        }
    }

    /**
    * Получение всех активных меню для указанного шаблона
    * @param string $template Название шаблона
    * @return array Массив активных меню для шаблона
    */
    public function getAllByTemplate($template) {
        return $this->db->fetchAll(
            "SELECT * FROM menus WHERE template = ? AND status = 'active' ORDER BY name ASC", 
            [$template]
        );
    }

    /**
    * Получение всех активных меню
    * @return array Массив активных меню
    */
    public function getAllActive() {
        return $this->db->fetchAll(
            "SELECT * FROM menus WHERE status = 'active' ORDER BY name ASC"
        );
    }

    /**
    * Получение меню по ID с проверкой активности
    * @param int $id ID меню
    * @return array|null Данные активного меню или null если не найдено
    */
    public function getActiveById($id) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE id = ? AND status = 'active'", 
            [(int)$id]
        );
    }

    /**
    * Получение меню по названию с проверкой активности
    * @param string $name Название меню
    * @return array|null Данные активного меню или null если не найдено
    */
    public function getActiveByName($name) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE name = ? AND status = 'active'", 
            [$name]
        );
    }

    /**
    * Получение всех групп пользователей для выбора
    * @return array Массив групп пользователей
    */
    public function getAllUserGroups() {
        $groups = $this->db->fetchAll("SELECT * FROM user_groups ORDER BY name");
        
        $groups[] = [
            'id' => 'guest',
            'name' => 'Гость',
            'description' => 'Неавторизованные пользователи'
        ];
        
        return $groups;
    }

    /**
    * Получение групп пользователя
    * @param int|null $userId ID пользователя (null для неавторизованных)
    * @return array Массив ID групп пользователя
    */
    public function getUserGroups($userId) {
        if (!$userId) {
            return ['guest'];
        }
        
        $groups = $this->db->fetchAll("
            SELECT ug.id 
            FROM user_groups ug
            JOIN users_groups uug ON ug.id = uug.group_id
            WHERE uug.user_id = ?
        ", [$userId]);
        
        $groupIds = array_column($groups, 'id');
        return $groupIds;
    }

    /**
    * Проверка видимости пункта меню для пользователя
    * @param array $item Данные пункта меню
    * @param array $userGroups Группы пользователя
    * @return bool true если пункт меню должен быть видим
    */
    public function shouldShowMenuItem($item, $userGroups) {

        if (!isset($item['visibility']) || empty($item['visibility'])) {
            return true;
        }
        
        $visibility = $item['visibility'];
        
        if (!empty($visibility['show_to_groups'])) {
            $hasMatchingGroup = false;
            foreach ($visibility['show_to_groups'] as $groupId) {
                if (in_array($groupId, $userGroups)) {
                    $hasMatchingGroup = true;
                    break;
                }
            }
            if (!$hasMatchingGroup) {
                return false;
            }
        }
        
        if (!empty($visibility['hide_from_groups'])) {
            foreach ($visibility['hide_from_groups'] as $groupId) {
                if (in_array($groupId, $userGroups)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
    * Фильтрация структуры меню по группам пользователя
    * @param array $structure Исходная структура меню
    * @param array $userGroups Группы пользователя
    * @return array Отфильтрованная структура меню
    */
    public function filterMenuByUserGroups($structure, $userGroups) {
        $filteredStructure = [];
        
        foreach ($structure as $item) {
            if ($this->shouldShowMenuItem($item, $userGroups)) {
                $filteredItem = $item;
                
                if (!empty($item['children'])) {
                    $filteredItem['children'] = $this->filterMenuByUserGroups($item['children'], $userGroups);
                }
                
                $filteredStructure[] = $filteredItem;
            }
        }
        
        return $filteredStructure;
    }

}