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
        
        if (empty($updateData)) {
            return true;
        }
        
        $existing = $this->db->fetch("SELECT id FROM menus WHERE id = ?", [$id]);
        if (!$existing) {
            return false;
        }
        
        $this->db->update('menus', $updateData, ['id' => $id]);
        return true;
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

    /**
    * Получение плоской структуры меню
    * @param int $menuId ID меню
    * @return array Массив пунктов с информацией об уровне вложенности
    */
    public function getFlatStructure($menuId) {
        $menu = $this->getById($menuId);
        if (!$menu) {
            return [];
        }
        
        $structure = json_decode($menu['structure'], true) ?: [];
        $flat = [];
        $this->flattenStructure($structure, $flat);
        
        return $flat;
    }
    
    /**
    * Рекурсивное преобразование вложенной структуры в плоскую
    * @param array $items Массив пунктов
    * @param array &$flat Ссылка на плоский массив
    * @param int $level Текущий уровень вложенности
    * @param int|null $parentId ID родительского пункта
    */
    private function flattenStructure($items, &$flat, $level = 0, $parentId = null) {
        foreach ($items as $index => $item) {
            $itemId = $this->generateItemId($item, $index);
            
            $flat[] = [
                'id' => $itemId,
                'title' => $item['title'],
                'url' => $item['url'],
                'description' => $item['description'] ?? '',
                'class' => $item['class'] ?? '',
                'target' => $item['target'] ?? '_self',
                'icon' => $item['icon'] ?? null,
                'icon_only' => $item['icon_only'] ?? false,
                'visibility' => $item['visibility'] ?? null,
                'is_extra' => $item['is_extra'] ?? false,
                'level' => $level,
                'parent_id' => $parentId,
                'has_children' => !empty($item['children']),
                'order' => $index
            ];
            
            if (!empty($item['children'])) {
                $this->flattenStructure($item['children'], $flat, $level + 1, $itemId);
            }
        }
    }
    
    /**
    * Генерация уникального ID для пункта меню
    * @param array $item Данные пункта
    * @param int $index Индекс в массиве
    * @return string Уникальный ID
    */
    private function generateItemId($item, $index) {
        if (!empty($item['item_id'])) {
            return $item['item_id'];
        }
        
        $string = ($item['title'] ?? '') . ($item['url'] ?? '') . $index;
        return md5($string);
    }
    
    /**
    * Добавление нового пункта в структуру меню
    * @param int $menuId ID меню
    * @param array $itemData Данные нового пункта
    * @param string|null $parentId ID родительского пункта
    * @return bool Результат операции
    */
    public function addMenuItem($menuId, $itemData, $parentId = null) {
        $menu = $this->getById($menuId);
        if (!$menu) {
            return false;
        }
        
        $structure = json_decode($menu['structure'], true) ?: [];
        
        if ($parentId) {
            $result = $this->addItemToParent($structure, $parentId, $itemData);
            if (!$result) {
                return false;
            }
        } else {
            $structure[] = $this->prepareItemData($itemData);
        }
        
        return $this->update($menuId, ['structure' => json_encode($structure, JSON_UNESCAPED_UNICODE)]);
    }
    
    /**
    * Рекурсивное добавление пункта к родителю
    * @param array &$items Ссылка на массив пунктов
    * @param string $parentId ID родительского пункта
    * @param array $itemData Данные нового пункта
    * @return bool Результат операции
    */
    private function addItemToParent(&$items, $parentId, $itemData) {
        if (!is_array($items)) {
            return false;
        }
        
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }
            
            $currentId = $this->generateItemId($item, 0);
            if ($currentId === $parentId) {
                if (!isset($item['children'])) {
                    $item['children'] = [];
                }
                $item['children'][] = $this->prepareItemData($itemData);
                return true;
            }
            
            if (!empty($item['children']) && is_array($item['children'])) {
                if ($this->addItemToParent($item['children'], $parentId, $itemData)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
    * Обновление существующего пункта меню
    * @param int $menuId ID меню
    * @param string $itemId ID пункта для обновления
    * @param array $itemData Новые данные пункта
    * @return bool Результат операции
    */
    public function updateMenuItem($menuId, $itemId, $itemData) {
        $menu = $this->getById($menuId);
        if (!$menu) {
            return false;
        }
        $structure = json_decode($menu['structure'], true) ?: [];
        $updated = $this->updateItemInStructure($structure, $itemId, $itemData);
        if (!$updated) {
            return false;
        }
        $newStructure = json_encode($structure, JSON_UNESCAPED_UNICODE);

        if ($newStructure === $menu['structure']) {
            return true;
        }

        return $this->update($menuId, ['structure' => $newStructure]);
    }
    
    /**
    * Рекурсивное обновление пункта в структуре
    * @param array &$items Ссылка на массив пунктов
    * @param string $itemId ID искомого пункта
    * @param array $itemData Новые данные
    * @return bool Результат операции
    */
    private function updateItemInStructure(&$items, $itemId, $itemData) {
        foreach ($items as $key => &$item) {
            $currentId = $this->generateItemId($item, $key);
            if ($currentId === $itemId) {
                $children = isset($item['children']) ? $item['children'] : [];
                
                $newData = $this->prepareItemData($itemData);
                
                foreach ($newData as $field => $value) {
                    $item[$field] = $value;
                }
                
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                
                return true;
            }
            
            if (!empty($item['children'])) {
                if ($this->updateItemInStructure($item['children'], $itemId, $itemData)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
    * Удаление пункта из структуры меню
    * @param int $menuId ID меню
    * @param string $itemId ID удаляемого пункта
    * @return bool Результат операции
    */
    public function deleteMenuItem($menuId, $itemId) {
        $menu = $this->getById($menuId);
        if (!$menu) {
            return false;
        }
        
        $structure = json_decode($menu['structure'], true) ?: [];
        
        if ($this->deleteItemFromStructure($structure, $itemId)) {
            return $this->update($menuId, ['structure' => json_encode($structure, JSON_UNESCAPED_UNICODE)]);
        }
        
        return false;
    }
    
    /**
    * Рекурсивное удаление пункта из структуры
    * @param array &$items Ссылка на массив пунктов
    * @param string $itemId ID удаляемого пункта
    * @return bool Результат операции
    */
    private function deleteItemFromStructure(&$items, $itemId) {
        foreach ($items as $key => &$item) {
            $currentId = $this->generateItemId($item, $key);
            if ($currentId === $itemId) {
                unset($items[$key]);
                $items = array_values($items);
                return true;
            }
            
            if (!empty($item['children'])) {
                if ($this->deleteItemFromStructure($item['children'], $itemId)) {
                    if (empty($item['children'])) {
                        unset($item['children']);
                    }
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
    * Обновление порядка сортировки пунктов меню
    * @param int $menuId ID меню
    * @param array $orderedItems Массив с порядком ID
    * @return bool Результат операции
    */
    public function reorderItems($menuId, $orderedItems) {
        $menu = $this->getById($menuId);
        if (!$menu) {
            return false;
        }
        
        $currentStructure = json_decode($menu['structure'], true) ?: [];
        $newStructure = $this->buildStructureFromOrder($currentStructure, $orderedItems);
        
        return $this->update($menuId, ['structure' => json_encode($newStructure, JSON_UNESCAPED_UNICODE)]);
    }
    
    /**
    * Построение структуры из плоского порядка
    * @param array $currentStructure Текущая структура
    * @param array $orderedItems Плоский массив с порядком
    * @return array Новая структура
    */
    private function buildStructureFromOrder($currentStructure, $orderedItems) {
        $itemsMap = [];
        $this->buildItemsMap($currentStructure, $itemsMap);
        
        $orderMap = [];
        foreach ($orderedItems as $index => $item) {
            $orderMap[$item['id']] = [
                'parent_id' => $item['parent_id'],
                'order' => $index
            ];
        }
        
        usort($orderedItems, function($a, $b) use ($orderMap) {
            $aParent = $orderMap[$a['id']]['parent_id'];
            $bParent = $orderMap[$b['id']]['parent_id'];
            
            if ($aParent === null && $bParent !== null) return -1;
            if ($aParent !== null && $bParent === null) return 1;
            
            return $orderMap[$a['id']]['order'] - $orderMap[$b['id']]['order'];
        });
        
        $newStructure = [];
        $structureMap = [];
        
        foreach ($orderedItems as $item) {
            $itemId = $item['id'];
            $parentId = $item['parent_id'];
            
            if (!isset($itemsMap[$itemId])) {
                continue;
            }
            
            $menuItem = $itemsMap[$itemId];
            
            unset($menuItem['children']);
            
            if ($parentId === null) {
                $newStructure[] = $menuItem;
                $structureMap[$itemId] = &$newStructure[count($newStructure) - 1];
            } else {
                if (isset($structureMap[$parentId])) {
                    if (!isset($structureMap[$parentId]['children'])) {
                        $structureMap[$parentId]['children'] = [];
                    }
                    $structureMap[$parentId]['children'][] = $menuItem;
                    $structureMap[$itemId] = &$structureMap[$parentId]['children'][count($structureMap[$parentId]['children']) - 1];
                } else {
                    $newStructure[] = $menuItem;
                    $structureMap[$itemId] = &$newStructure[count($newStructure) - 1];
                }
            }
        }
        
        return $newStructure;
    }
    
    /**
    * Построение карты всех элементов структуры
    * @param array $items Массив пунктов
    * @param array &$map Ссылка на карту
    */
    private function buildItemsMap($items, &$map, $index = 0) {
        foreach ($items as $itemIndex => $item) {
            $itemId = $this->generateItemId($item, $itemIndex);
            $map[$itemId] = $item;
            
            if (!empty($item['children'])) {
                $this->buildItemsMap($item['children'], $map, 0);
            }
        }
    }
    
    /**
    * Подготовка данных пункта для сохранения
    * @param array $itemData Исходные данные
    * @return array Подготовленные данные
    */
        /**
     * Подготовка данных пункта для сохранения
     * @param array $itemData Исходные данные
     * @return array Подготовленные данные
     */
    private function prepareItemData($itemData) {
        $item = [];

        if (!isset($itemData['item_id'])) {
            $item['item_id'] = uniqid() . '_' . time() . '_' . mt_rand(1000, 9999);
        } else {
            $item['item_id'] = $itemData['item_id'];
        }

        if (isset($itemData['title'])) {
            $item['title'] = trim($itemData['title']);
        }
        if (isset($itemData['url'])) {
            $item['url'] = trim($itemData['url']);
        }
        if (isset($itemData['target'])) {
            $item['target'] = $itemData['target'];
        }
        if (!empty($itemData['description'])) {
            $item['description'] = trim($itemData['description']);
        }
        if (!empty($itemData['class'])) {
            $item['class'] = trim($itemData['class']);
        }

        if (!empty($itemData['icon']) && is_array($itemData['icon']) && !empty($itemData['icon']['id'])) {
            $item['icon'] = [
                'id' => $itemData['icon']['id'],
                'set' => $itemData['icon']['set'] ?? 'bs',
                'size' => (int)($itemData['icon']['size'] ?? 20),
                'color' => $itemData['icon']['color'] ?? '#000000'
            ];
        }
        
        if (isset($itemData['icon_only'])) {
            $item['icon_only'] = ($itemData['icon_only'] == 1 || $itemData['icon_only'] === true);
        } else {
            $item['icon_only'] = false;
        }

        if (isset($itemData['is_extra'])) {
            $item['is_extra'] = ($itemData['is_extra'] == 1 || $itemData['is_extra'] === true);
        } else {
            $item['is_extra'] = false;
        }

        $hasVisibility = false;
        $visibilityData = [];

        $showToGroups = $itemData['visibility']['show_to_groups'] 
            ?? $itemData['show_to_groups'] 
            ?? null;
            
        $hideFromGroups = $itemData['visibility']['hide_from_groups'] 
            ?? $itemData['hide_from_groups'] 
            ?? null;

        if (isset($showToGroups) && is_array($showToGroups)) {
            $filtered = array_values(array_filter($showToGroups, function($val) {
                return $val !== null && $val !== '' && $val !== 'null';
            }));
            if (!empty($filtered)) {
                $visibilityData['show_to_groups'] = $filtered;
                $hasVisibility = true;
            }
        }

        if (isset($hideFromGroups) && is_array($hideFromGroups)) {
            $filtered = array_values(array_filter($hideFromGroups, function($val) {
                return $val !== null && $val !== '' && $val !== 'null';
            }));
            if (!empty($filtered)) {
                $visibilityData['hide_from_groups'] = $filtered;
                $hasVisibility = true;
            }
        }

        if ($hasVisibility) {
            $item['visibility'] = $visibilityData;
        }

        return $item;
    }

}