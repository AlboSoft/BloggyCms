<?php

namespace menu\actions;

/**
* Действие редактирования основных настроек меню
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
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty(trim($_POST['name']))) {
                    throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_NAME_REQUIRED);
                }
                
                $useCustomTemplate = isset($_POST['use_custom_template']) ? 1 : 0;
                $customTemplate = null;
                
                if ($useCustomTemplate) {
                    $customTemplate = $_POST['custom_template'] ?? '';
                    if (empty(trim($customTemplate))) {
                        throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_CUSTOM_TEMPLATE_REQUIRED);
                    }
                } else {
                    if (empty($_POST['template'])) {
                        throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_TEMPLATE_REQUIRED);
                    }
                    if (!isset($availableTemplates[$_POST['template']])) {
                        throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_TEMPLATE_NOT_EXISTS);
                    }
                }
                
                $menuData = [
                    'name' => trim($_POST['name']),
                    'status' => $_POST['status'] ?? 'active',
                    'use_custom_template' => $useCustomTemplate,
                    'custom_template' => $customTemplate
                ];
                
                if (!$useCustomTemplate) {
                    $menuData['template'] = $_POST['template'];
                } else {
                    $menuData['template'] = $menu['template'] ?? 'custom';
                }
                
                $success = $this->menuModel->update($id, $menuData);
                
                if ($success) {
                    \Notification::success(LANG_ACTION_MENU_ADMINEDIT_SUCCESS);
                    $this->redirect(ADMIN_URL . '/menu');
                } else {
                    throw new \Exception(LANG_ACTION_MENU_ADMINEDIT_UPDATE_FAILED);
                }
                
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
            }
        }
        
        $this->render('admin/menu/form', [
            'menu' => $menu,
            'availableTemplates' => $availableTemplates,
            'currentTheme' => $currentTheme,
            'useCustomTemplate' => (bool)($menu['use_custom_template'] ?? false),
            'customTemplate' => $menu['custom_template'] ?? '',
            'pageTitle' => LANG_ACTION_MENU_ADMINEDIT_PAGE_TITLE . html($menu['name'])
        ]);
    }
}