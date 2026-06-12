<?php

namespace menu\actions;

/**
* Действие отображения списка пунктов меню для управления
* @package menu\actions
*/
class AdminItems extends MenuAction {
    
    /**
    * Метод выполнения отображения пунктов меню
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_MENU_ADMINITEMS_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $menu = $this->menuModel->getById($id);
        
        if (!$menu) {
            \Notification::error(LANG_ACTION_MENU_ADMINITEMS_MENU_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMS_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMS_BREADCRUMB_MENU, ADMIN_URL . '/menu');
        $this->addBreadcrumb(html($menu['name']), ADMIN_URL . '/menu/edit/' . $id);
        $this->addBreadcrumb(LANG_ACTION_MENU_ADMINITEMS_BREADCRUMB_ITEMS);
        $this->setPageTitle(sprintf(LANG_ACTION_MENU_ADMINITEMS_PAGE_TITLE, html($menu['name'])));
        
        $flatItems = $this->menuModel->getFlatStructure($id);
        $stats = $this->getItemsStats($flatItems);
        
        $this->render('admin/menu/items', [
            'menu' => $menu,
            'items' => $flatItems,
            'stats' => $stats,
            'pageTitle' => sprintf(LANG_ACTION_MENU_ADMINITEMS_PAGE_TITLE, html($menu['name']))
        ]);
    }
    
    /**
    * Получение статистики по пунктам меню
    * @param array $items Плоский массив пунктов
    * @return array Статистика
    */
    private function getItemsStats($items) {
        $total = count($items);
        $maxLevel = 0;
        $withIcon = 0;
        $withDesc = 0;
        $withVisibility = 0;
        
        foreach ($items as $item) {
            $maxLevel = max($maxLevel, $item['level']);
            if (!empty($item['icon'])) $withIcon++;
            if (!empty($item['description'])) $withDesc++;
            if (!empty($item['visibility'])) $withVisibility++;
        }
        
        return [
            'total' => $total,
            'max_level' => $maxLevel,
            'with_icon' => $withIcon,
            'with_desc' => $withDesc,
            'with_visibility' => $withVisibility
        ];
    }
}