<?php

namespace menu\actions;

/**
* Действие редактирования меню в админ-панели
* @package menu\actions
*/
class AdminEdit extends MenuAction {
    
    /**
    * Метод выполнения редактирования меню
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_MENU_ADMINEDIT_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $menu = $this->menuModel->getById($id);
        
        if (!$menu) {
            \Notification::error(LANG_ACTION_MENU_ADMINEDIT_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINEDIT_BREADCRUMB_MENU, ADMIN_URL . '/menu');
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINEDIT_BREADCRUMB_EDIT . html($menu['name']));
        
        $availableTemplates = $this->menuModel->getAvailableTemplates();
        $currentTheme = $this->menuModel->getCurrentTheme();
        $menuStructure = json_decode($menu['structure'], true) ?: [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->handlePostRequest($id, $menu, $menuStructure, $availableTemplates);
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
                
                $menu = array_merge($menu, $_POST);
                $menuStructure = $menuStructure ?? json_decode($menu['structure'], true) ?: [];
            }
        }
        
        $this->render('admin/menu/form', [
            'menu' => $menu,
            'availableTemplates' => $availableTemplates,
            'menuStructure' => $menuStructure,
            'currentTheme' => $currentTheme,
            'pageTitle' => LANG_ACTION_MENU_ADMINEDIT_PAGE_TITLE . html($menu['name'])
        ]);
    }
    
    /**
    * Обрабатывает POST-запрос на обновление меню
    * @param int|string $id ID редактируемого меню
    * @param array $menu Текущие данные меню
    * @param array $menuStructure Текущая структура меню
    * @param array $availableTemplates Доступные шаблоны меню
    * @throws \Exception При ошибках валидации или сохранения
    * @return void
    */
    private function handlePostRequest($id, &$menu, &$menuStructure, $availableTemplates) {
        
        if (empty($_POST['name'])) {
            throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_NAME_REQUIRED);
        }
        
        if (empty($_POST['template'])) {
            throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_TEMPLATE_REQUIRED);
        }
        
        if (!isset($availableTemplates[$_POST['template']])) {
            throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_TEMPLATE_NOT_EXISTS);
        }
        
        $menuStructure = json_decode($_POST['menu_structure'] ?? '[]', true);
        if (!$this->menuModel->validateMenuStructure($menuStructure)) {
            throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_INVALID_STRUCTURE);
        }
        
        $this->validateAndProcessVisibilitySettings($menuStructure);
        
        $menuData = [
            'name' => trim($_POST['name']),
            'template' => $_POST['template'],
            'structure' => json_encode($menuStructure, JSON_UNESCAPED_UNICODE),
            'status' => $_POST['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->menuModel->update($id, $menuData);
        
        if ($success) {
            \Notification::success(LANG_ACTION_MENU_ADMINEDIT_SUCCESS);
            $this->redirect(ADMIN_URL . '/menu');
        } else {
            throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_UPDATE_FAILED);
        }
    }
    
    /**
    * Валидирует и обрабатывает настройки видимости для структуры меню
    * @param array &$menuStructure Структура меню для обработки
    * @return void
    */
    private function validateAndProcessVisibilitySettings(&$menuStructure) {
        if (!is_array($menuStructure)) {
            return;
        }
        
        foreach ($menuStructure as &$item) {
            $this->processMenuItemVisibility($item);
            
            if (!empty($item['children']) && is_array($item['children'])) {
                $this->validateAndProcessVisibilitySettings($item['children']);
            }
        }
    }
    
    /**
    * Обрабатывает настройки видимости для одного пункта меню
    * @param array &$item Пункт меню для обработки
    * @return void
    */
    private function processMenuItemVisibility(&$item) {
        if (!isset($item['visibility'])) {
            return;
        }
        
        $visibility = $item['visibility'];
        $validGroups = $this->getValidUserGroups();
        
        $item['visibility']['show_to_groups'] = $this->filterValidGroups(
            $visibility['show_to_groups'] ?? [], 
            $validGroups
        );
        
        $item['visibility']['hide_from_groups'] = $this->filterValidGroups(
            $visibility['hide_from_groups'] ?? [], 
            $validGroups
        );
        
        if (empty($item['visibility']['show_to_groups']) && empty($item['visibility']['hide_from_groups'])) {
            unset($item['visibility']);
            return;
        }
        
        $this->checkVisibilityConflicts($item['visibility']);
    }
    
    /**
    * Фильтрует массив ID групп, оставляя только валидные 
    * @param array $groupIds Массив ID групп для фильтрации
    * @param array $validGroups Массив валидных ID групп
    * @return array Отфильтрованный массив валидных ID групп
    */
    private function filterValidGroups($groupIds, $validGroups) {
        if (!is_array($groupIds)) {
            return [];
        }
        
        $filteredGroups = [];
        foreach ($groupIds as $groupId) {
            if ($this->isValidGroupId($groupId, $validGroups)) {
                $filteredGroups[] = $groupId;
            }
        }
        
        return $filteredGroups;
    }
    
    /**
    * Проверяет конфликтующие настройки видимости
    * @param array &$visibility Настройки видимости для проверки 
    * @return void
    */
    private function checkVisibilityConflicts(&$visibility) {
        $showGroups = $visibility['show_to_groups'] ?? [];
        $hideGroups = $visibility['hide_from_groups'] ?? [];
        
        $conflictingGroups = array_intersect($showGroups, $hideGroups);
        
        if (!empty($conflictingGroups)) {
            $visibility['hide_from_groups'] = array_diff($hideGroups, $conflictingGroups);
        }
    }
    
    /**
    * Получает список валидных групп пользователей 
    * @return array Массив валидных ID групп
    */
    private function getValidUserGroups() {
        $userModel = new \UserModel($this->db);
        $groups = $userModel->getAllGroups();
        
        $validGroups = ['guest'];
        foreach ($groups as $group) {
            $validGroups[] = $group['id'];
        }
        
        return $validGroups;
    }
    
    /**
    * Проверяет валидность ID группы
    * @param mixed $groupId ID группы для проверки
    * @param array $validGroups Массив валидных ID групп
    * @return bool true если группа валидна, false в противном случае
    */
    private function isValidGroupId($groupId, $validGroups) {
        return in_array($groupId, $validGroups);
    }
}