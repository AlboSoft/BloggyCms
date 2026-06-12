<?php

namespace menu\actions;

/**
* Действие создания нового меню (только основные настройки)
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
                
                $useCustomTemplate = isset($_POST['use_custom_template']) ? 1 : 0;
                $customTemplate = null;
                
                if ($useCustomTemplate) {
                    $customTemplate = $_POST['custom_template'] ?? '';
                    if (empty(trim($customTemplate))) {
                        throw new \Exception(LANG_ACTION_MENU_ADMINCREATE_CUSTOM_TEMPLATE_REQUIRED);
                    }
                } else {
                    if (empty($_POST['template'])) {
                        throw new \Exception(LANG_ACTION_MENU_ADMINCREATE_TEMPLATE_REQUIRED);
                    }
                    if (!isset($availableTemplates[$_POST['template']])) {
                        throw new \Exception(LANG_ACTION_MENU_ADMINCREATE_TEMPLATE_NOT_EXISTS);
                    }
                }
                
                $menuData = [
                    'name' => trim($_POST['name']),
                    'structure' => json_encode([], JSON_UNESCAPED_UNICODE),
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
                
                if (!$menuId) {
                    throw new \Exception(LANG_ACTION_MENU_ADMINCREATE_DB_ERROR);
                }
                
                \Notification::success(LANG_ACTION_MENU_ADMINCREATE_SUCCESS);
                
                $this->redirect(ADMIN_URL . '/menu/items/' . $menuId);
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
                
                $this->render('admin/menu/form', [
                    'menu' => $_POST,
                    'availableTemplates' => $availableTemplates,
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
            'currentTheme' => $currentTheme,
            'useCustomTemplate' => false,
            'customTemplate' => '',
            'pageTitle' => LANG_ACTION_MENU_ADMINCREATE_PAGE_TITLE
        ]);
    }
}