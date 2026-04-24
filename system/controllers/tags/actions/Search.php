<?php

namespace tags\actions;

/**
* Действие поиска тегов по названию (AJAX) 
* @package tags\actions
* @extends TagAction
*/
class Search extends TagAction {
    
    /**
    * Метод выполнения поиска тегов 
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json; charset=utf-8');
        
        try {

            if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
                echo json_encode([]);
                exit;
            }
            
            $query = trim($_GET['q']);
            
            $tags = $this->tagModel->searchByName($query, 10);
            
            echo json_encode($tags, JSON_UNESCAPED_UNICODE);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => LANG_ACTION_TAGS_SEARCH_ERROR]);
            exit;
        }
    }
}