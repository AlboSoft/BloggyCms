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
        $itemId = rawurldecode((string)($this->params['id'] ?? ''));
        
        if ($itemId === '') {
            $this->sendJsonResponse(false, LANG_ACTION_MENU_ADMINITEMDELETE_ID_NOT_SPECIFIED);
            return;
        }
        
        $menu = $this->menuModel->findMenuByItemId($itemId);
        
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