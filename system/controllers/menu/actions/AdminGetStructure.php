<?php

namespace menu\actions;

/**
* Действие получения структуры меню в формате JSON
* @package menu\actions
*/
class AdminGetStructure extends MenuAction {
    
    /**
    * Метод выполнения получения структуры меню
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            $this->sendJsonResponse(false, LANG_ACTION_MENU_ADMINGETSTRUCTURE_ID_NOT_SPECIFIED);
            return;
        }
        
        try {
            $menu = $this->menuModel->getById($id);
            
            if (!$menu) {
                throw new \Exception(LANG_ACTION_MENU_ADMINGETSTRUCTURE_NOT_FOUND);
            }
            
            $structure = json_decode($menu['structure'], true) ?: [];
            
            $this->sendJsonResponse(true, null, $structure);
            
        } catch (\Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
    * Отправляет JSON-ответ и завершает выполнение скрипта
    * @param bool $success Флаг успешности операции
    * @param string|null $message Сообщение об ошибке (для неуспешных ответов)
    * @param array|null $structure Структура меню (для успешных ответов)
    * @return void
    */
    private function sendJsonResponse($success, $message = null, $structure = null) {

        header('Content-Type: application/json');
        
        $response = ['success' => $success];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if ($structure !== null) {
            $response['structure'] = $structure;
        }
        
        echo json_encode($response);
        exit;
    }
}