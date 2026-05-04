<?php

namespace menu\actions;

/**
* Действие создания нового меню в админ-панели
* @package menu\actions
*/
class AdminCreate extends MenuAction {
    
    /**
    * Метод выполнения создания меню
    * @return void
    */
    public function execute() {
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINCREATE_BREADCRUMB_MENU, ADMIN_URL . '/menu');
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINCREATE_BREADCRUMB_CREATE);
        
        $availableTemplates = $this->menuModel->getAvailableTemplates();
        $currentTheme = $this->menuModel->getCurrentTheme();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty(trim($_POST['name']))) {
                    throw new \Exception(LANG_ACTION_MENU_ADMINCREATE_NAME_REQUIRED);
                }
                
                if (empty($_POST['template']) && empty($_POST['use_custom_template'])) {
                    throw new \Exception(LANG_ACTION_MENU_ADMINCREATE_TEMPLATE_REQUIRED);
                }
                
                $useCustomTemplate = isset($_POST['use_custom_template']) ? 1 : 0;
                $customTemplate = null;
                
                if ($useCustomTemplate) {
                    $customTemplate = $_POST['custom_template'] ?? '';
                    if (empty(trim($customTemplate))) {
                        throw new \Exception('Пожалуйста, заполните кастомный шаблон меню');
                    }
                } else {
                    if (!isset($availableTemplates[$_POST['template']])) {
                        throw new \Exception(LANG_ACTION_MENU_ADMINCREATE_TEMPLATE_NOT_EXISTS);
                    }
                }
                
                $menuStructure = json_decode($_POST['menu_structure'] ?? '[]', true);
                if (!$this->menuModel->validateMenuStructure($menuStructure)) {
                    throw new \Exception(LANG_ACTION_MENU_ADMINCREATE_INVALID_STRUCTURE);
                }
                
                $menuData = [
                    'name' => trim($_POST['name']),
                    'structure' => json_encode($menuStructure, JSON_UNESCAPED_UNICODE),
                    'status' => $_POST['status'] ?? 'active',
                    'use_custom_template' => $useCustomTemplate,
                    'custom_template' => $customTemplate
                ];
                
                if ($useCustomTemplate) {
                    $menuData['template'] = 'custom';
                } else {
                    $menuData['template'] = $_POST['template'];
                }
                
                $menuId = $this->menuModel->create($menuData);
                
                \Notification::success(LANG_ACTION_MENU_ADMINCREATE_SUCCESS);
                $this->redirect(ADMIN_URL . '/menu');
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
                
                $this->render('admin/menu/form', [
                    'menu' => $_POST,
                    'availableTemplates' => $availableTemplates,
                    'menuStructure' => $menuStructure ?? [],
                    'currentTheme' => $currentTheme,
                    'useCustomTemplate' => $useCustomTemplate ?? false,
                    'customTemplate' => $customTemplate ?? '',
                    'pageTitle' => LANG_ACTION_MENU_ADMINCREATE_PAGE_TITLE
                ]);
                return;
            }
        }
        
        $this->render('admin/menu/form', [
            'menu' => [],
            'availableTemplates' => $availableTemplates,
            'menuStructure' => [],
            'currentTheme' => $currentTheme,
            'useCustomTemplate' => false,
            'customTemplate' => '',
            'pageTitle' => LANG_ACTION_MENU_ADMINCREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Валидация и обработка настроек видимости для структуры меню
    * @param array &$menuStructure Ссылка на структуру меню для обработки
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
    * Обработка настроек видимости для одного пункта меню
    * @param array &$item Ссылка на пункт меню для обработки
    * @return void
    */
    private function processMenuItemVisibility(&$item) {
        if (!isset($item['visibility'])) {
            return;
        }
        
        $visibility = $item['visibility'];
        
        if (isset($visibility['show_to_groups']) && is_array($visibility['show_to_groups'])) {
            $validGroups = $this->getValidUserGroups();
            $filteredShowGroups = [];
            
            foreach ($visibility['show_to_groups'] as $groupId) {
                if ($this->isValidGroupId($groupId, $validGroups)) {
                    $filteredShowGroups[] = $groupId;
                }
            }
            
            $item['visibility']['show_to_groups'] = $filteredShowGroups;
        } else {
            $item['visibility']['show_to_groups'] = [];
        }
        
        if (isset($visibility['hide_from_groups']) && is_array($visibility['hide_from_groups'])) {
            $validGroups = $this->getValidUserGroups();
            $filteredHideGroups = [];
            
            foreach ($visibility['hide_from_groups'] as $groupId) {
                if ($this->isValidGroupId($groupId, $validGroups)) {
                    $filteredHideGroups[] = $groupId;
                }
            }
            
            $item['visibility']['hide_from_groups'] = $filteredHideGroups;
        } else {
            $item['visibility']['hide_from_groups'] = [];
        }
        
        if (empty($item['visibility']['show_to_groups']) && empty($item['visibility']['hide_from_groups'])) {
            unset($item['visibility']);
        }
    }
    
    /**
    * Получение списка валидных групп пользователей
    * @return array Массив валидных ID групп пользователей
    */
    private function getValidUserGroups() {
        $userModel = new \UserModel($this->db);
        $groups = $userModel->getAllGroups();
        
        $groups[] = ['id' => 'guest', 'name' => LANG_ACTION_MENU_ADMINCREATE_GROUP_GUEST];
        
        $validGroups = ['guest'];
        foreach ($groups as $group) {
            $validGroups[] = $group['id'];
        }
        
        return $validGroups;
    }
    
    /**
    * Проверка валидности ID группы
    * @param string|int $groupId Проверяемый ID группы
    * @param array $validGroups Массив валидных ID групп
    * @return bool true если группа существует
    */
    private function isValidGroupId($groupId, $validGroups) {
        return in_array($groupId, $validGroups);
    }
}