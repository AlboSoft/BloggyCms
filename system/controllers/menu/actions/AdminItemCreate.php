<?php

namespace menu\actions;

/**
* Действие создания нового пункта меню
* @package menu\actions
*/
class AdminItemCreate extends MenuAction {
    
    /**
    * Метод выполнения создания пункта меню
    * @return void
    */
    public function execute() {
        $menuId = $this->params['menuId'] ?? null;
        
        if (!$menuId) {
            \Notification::error(LANG_ACTION_MENU_ADMINITEMCREATE_MENU_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $menu = $this->menuModel->getById($menuId);
        
        if (!$menu) {
            \Notification::error(LANG_ACTION_MENU_ADMINITEMCREATE_MENU_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $parentId = $this->params['parent_id'] ?? null;
        
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMCREATE_BREADCRUMB_MENU, ADMIN_URL . '/menu');
        $this->addBreadcrumb(html($menu['name']), ADMIN_URL . '/menu/edit/' . $menuId);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMCREATE_BREADCRUMB_ITEMS, ADMIN_URL . '/menu/items/' . $menuId);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMCREATE_BREADCRUMB_CREATE);
        $this->setPageTitle(LANG_ACTION_MENU_ADMINITEMCREATE_PAGE_TITLE);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest($menuId, $parentId);
            return;
        }
        
        $this->renderForm($menu, $parentId);
    }
    
    /**
    * Обработка POST-запроса на создание пункта
    * @param int $menuId ID меню
    * @param string|null $parentId ID родительского пункта
    * @return void
    */
    private function handlePostRequest($menuId, $parentId = null) {
        try {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!\CsrfToken::verify($csrfToken, 'menu_item')) {
                throw new \Exception(LANG_ACTION_MENU_ADMINITEMCREATE_INVALID_CSRF);
            }
            
            $this->validateItemData($_POST);
            
            $itemData = $this->prepareItemData($_POST);
            
            $result = $this->menuModel->addMenuItem($menuId, $itemData, $parentId);
            
            if ($result) {
                \Notification::success(LANG_ACTION_MENU_ADMINITEMCREATE_SUCCESS);
                $this->redirect(ADMIN_URL . '/menu/items/' . $menuId);
            } else {
                throw new \Exception(LANG_ACTION_MENU_ADMINITEMCREATE_SAVE_ERROR);
            }
            
        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->renderForm($this->menuModel->getById($menuId), $parentId, $_POST);
        }
    }
    
    /**
    * Валидация данных пункта меню
    * @param array $data Данные из POST
    * @throws \Exception При ошибках валидации
    */
    private function validateItemData($data) {
        if (empty(trim($data['title']))) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMCREATE_TITLE_REQUIRED);
        }
        
        if (empty(trim($data['url']))) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMCREATE_URL_REQUIRED);
        }
        
        if (mb_strlen($data['title']) > 100) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMCREATE_TITLE_TOO_LONG);
        }
        
        if (mb_strlen($data['url']) > 255) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMCREATE_URL_TOO_LONG);
        }
        
        if (!empty($data['class']) && mb_strlen($data['class']) > 50) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMCREATE_CLASS_TOO_LONG);
        }
    }
    
    /**
    * Подготовка данных пункта для сохранения
    * @param array $data Данные из POST
    * @return array Подготовленные данные
    */
    private function prepareItemData($data) {
        $itemData = [
            'title' => trim($data['title']),
            'url' => trim($data['url']),
            'target' => $data['target'] ?? '_self'
        ];
        
        if (!empty($data['description'])) {
            $itemData['description'] = trim($data['description']);
        }
        
        if (!empty($data['class'])) {
            $itemData['class'] = trim($data['class']);
        }
        
        if (!empty($data['icon_id']) && !empty($data['icon_set'])) {
            $itemData['icon'] = [
                'id' => $data['icon_id'],
                'set' => $data['icon_set'],
                'size' => (int)($data['icon_size'] ?? 20),
                'color' => $data['icon_color'] ?? '#000000'
            ];
        }
        
        if (!empty($data['icon_only'])) {
            $itemData['icon_only'] = true;
        }
        
        if (!empty($data['is_extra'])) {
            $itemData['is_extra'] = true;
        }
        
        $visibility = [];
        if (!empty($data['show_to_groups']) && is_array($data['show_to_groups'])) {
            $visibility['show_to_groups'] = array_filter($data['show_to_groups']);
        }
        if (!empty($data['hide_from_groups']) && is_array($data['hide_from_groups'])) {
            $visibility['hide_from_groups'] = array_filter($data['hide_from_groups']);
        }
        if (!empty($visibility)) {
            $itemData['visibility'] = $visibility;
        }
        
        return $itemData;
    }
    
    /**
    * Отображение формы создания пункта
    * @param array $menu Данные меню
    * @param string|null $parentId ID родительского пункта
    * @param array $formData Данные для заполнения формы (при ошибке)
    * @return void
    */
    private function renderForm($menu, $parentId = null, $formData = []) {
        $groups = $this->getUserGroups();
        $parentItem = null;
        
        if ($parentId) {
            $flatItems = $this->menuModel->getFlatStructure($menu['id']);
            foreach ($flatItems as $item) {
                if ($item['id'] === $parentId) {
                    $parentItem = $item;
                    break;
                }
            }
        }
        
        $this->render('admin/menu/item_form', [
            'menu' => $menu,
            'parentId' => $parentId,
            'parentItem' => $parentItem,
            'groups' => $groups,
            'formData' => $formData,
            'isEdit' => false,
            'pageTitle' => LANG_ACTION_MENU_ADMINITEMCREATE_PAGE_TITLE,
            'csrf_token' => \CsrfToken::generate('menu_item')
        ]);
    }
}