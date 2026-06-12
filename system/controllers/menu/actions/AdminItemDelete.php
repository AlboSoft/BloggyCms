<?php

namespace menu\actions;

/**
* Действие удаления пункта меню
* @package menu\actions
*/
class AdminItemDelete extends MenuAction {
    
    /**
    * Метод выполнения удаления пункта
    * @return void
    */
    public function execute() {
        $itemId = $this->params['id'] ?? null;
        
        if (!$itemId) {
            $this->sendJsonResponse(false, LANG_ACTION_MENU_ADMINITEMDELETE_ID_NOT_SPECIFIED);
            return;
        }
        
        $menu = $this->findMenuByItemId($itemId);
        
        if (!$menu) {
            $this->sendJsonResponse(false, LANG_ACTION_MENU_ADMINITEMDELETE_ITEM_NOT_FOUND);
            return;
        }
        
        try {
            $result = $this->menuModel->deleteMenuItem($menu['id'], $itemId);
            
            if ($result) {
                $this->sendJsonResponse(true, LANG_ACTION_MENU_ADMINITEMDELETE_SUCCESS);
            } else {
                $this->sendJsonResponse(false, LANG_ACTION_MENU_ADMINITEMDELETE_ERROR);
            }
            
        } catch (\Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
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
    * Отправка JSON-ответа для AJAX-запросов
    * @param bool $success Флаг успеха
    * @param string $message Сообщение
    * @return void
    */
    private function sendJsonResponse($success, $message) {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
        exit;
    }
}