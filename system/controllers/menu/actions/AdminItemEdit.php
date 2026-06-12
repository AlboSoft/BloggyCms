<?php

namespace menu\actions;

/**
* Действие редактирования пункта меню
* @package menu\actions
*/
class AdminItemEdit extends MenuAction {
    
    /**
    * Метод выполнения редактирования пункта
    * @return void
    */
    public function execute() {
        $itemId = $this->params['id'] ?? null;
        
        if (!$itemId) {
            \Notification::error(LANG_ACTION_MENU_ADMINITEMEDIT_ITEM_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $menu = $this->findMenuByItemId($itemId);
        
        if (!$menu) {
            \Notification::error(LANG_ACTION_MENU_ADMINITEMEDIT_ITEM_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $itemData = $this->findItemData($menu['id'], $itemId);
        
        if (!$itemData) {
            \Notification::error(LANG_ACTION_MENU_ADMINITEMEDIT_ITEM_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/menu/items/' . $menu['id']);
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMEDIT_BREADCRUMB_MENU, ADMIN_URL . '/menu');
        $this->addBreadcrumb(html($menu['name']), ADMIN_URL . '/menu/edit/' . $menu['id']);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMEDIT_BREADCRUMB_ITEMS, ADMIN_URL . '/menu/items/' . $menu['id']);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMEDIT_BREADCRUMB_EDIT . html($itemData['title']));
        $this->setPageTitle(LANG_ACTION_MENU_ADMINITEMEDIT_PAGE_TITLE);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest($menu['id'], $itemId);
            return;
        }
        
        $this->renderForm($menu, $itemId, $itemData);
    }
    
    /**
    * Поиск меню по ID пункта
    * @param string $itemId ID пункта
    * @return array|null Данные меню или null
    */
    private function findMenuByItemId($itemId) {
        $menus = $this->menuModel->getAll();
        
        foreach ($menus as $menu) {
            $flatItems = $this->menuModel->getFlatStructure($menu['id']);
            foreach ($flatItems as $item) {
                if ($item['id'] === $itemId) {
                    return $menu;
                }
            }
        }
        
        return null;
    }
    
    /**
    * Поиск данных пункта по ID
    * @param int $menuId ID меню
    * @param string $itemId ID пункта
    * @return array|null Данные пункта или null
    */
    private function findItemData($menuId, $itemId) {
        $flatItems = $this->menuModel->getFlatStructure($menuId);
        
        foreach ($flatItems as $item) {
            if ($item['id'] === $itemId) {
                return $item;
            }
        }
        
        return null;
    }
    
    /**
    * Обработка POST-запроса на обновление пункта
    * @param int $menuId ID меню
    * @param string $itemId ID пункта
    * @return void
    */
    private function handlePostRequest($menuId, $itemId) {
        try {
            
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!\CsrfToken::verify($csrfToken, 'menu_item')) {
                throw new \Exception(LANG_ACTION_MENU_ADMINITEMEDIT_INVALID_CSRF);
            }
            
            $this->validateItemData($_POST);
            
            $itemData = $this->prepareItemData($_POST);
            
            $result = $this->menuModel->updateMenuItem($menuId, $itemId, $itemData);
            
            if ($result) {
                \Notification::success(LANG_ACTION_MENU_ADMINITEMEDIT_SUCCESS);
                $this->redirect(ADMIN_URL . '/menu/items/' . $menuId);
            } else {
                throw new \Exception(LANG_ACTION_MENU_ADMINITEMEDIT_SAVE_ERROR);
            }
            
        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->renderForm($this->menuModel->getById($menuId), $itemId, null, $_POST);
        }
    }
    
    /**
    * Валидация данных пункта меню
    * @param array $data Данные из POST
    * @throws \Exception При ошибках валидации
    */
    private function validateItemData($data) {
        if (empty(trim($data['title']))) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMEDIT_TITLE_REQUIRED);
        }
        
        if (empty(trim($data['url']))) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMEDIT_URL_REQUIRED);
        }
        
        if (mb_strlen($data['title']) > 100) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMEDIT_TITLE_TOO_LONG);
        }
        
        if (mb_strlen($data['url']) > 255) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMEDIT_URL_TOO_LONG);
        }
        
        if (!empty($data['class']) && mb_strlen($data['class']) > 50) {
            throw new \Exception(LANG_ACTION_MENU_ADMINITEMEDIT_CLASS_TOO_LONG);
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
    * Отображение формы редактирования пункта
    * @param array $menu Данные меню
    * @param string $itemId ID пункта
    * @param array|null $itemData Данные пункта
    * @param array $formData Данные для заполнения формы (при ошибке)
    * @return void
    */
    private function renderForm($menu, $itemId, $itemData = null, $formData = []) {
        $groups = $this->getUserGroups();
        
        $currentData = $formData ?: [
            'title' => $itemData['title'] ?? '',
            'url' => $itemData['url'] ?? '',
            'description' => $itemData['description'] ?? '',
            'class' => $itemData['class'] ?? '',
            'target' => $itemData['target'] ?? '_self',
            'parent_id' => $itemData['parent_id'] ?? '',
            'icon' => $itemData['icon'] ?? null,
            'icon_only' => $itemData['icon_only'] ?? false,
            'is_extra' => $itemData['is_extra'] ?? false,
            'show_to_groups' => isset($itemData['visibility']) && is_array($itemData['visibility']) && isset($itemData['visibility']['show_to_groups']) 
                ? $itemData['visibility']['show_to_groups'] 
                : [],
            'hide_from_groups' => isset($itemData['visibility']) && is_array($itemData['visibility']) && isset($itemData['visibility']['hide_from_groups']) 
                ? $itemData['visibility']['hide_from_groups'] 
                : []
        ];
        
        $this->render('admin/menu/item_form', [
            'menu' => $menu,
            'itemId' => $itemId,
            'groups' => $groups,
            'formData' => $currentData,
            'isEdit' => true,
            'pageTitle' => LANG_ACTION_MENU_ADMINITEMEDIT_PAGE_TITLE,
            'csrf_token' => \CsrfToken::generate('menu_item')
        ]);
    }
}