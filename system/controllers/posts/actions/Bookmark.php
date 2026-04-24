<?php

namespace posts\actions;

/**
* Действие добавления/удаления поста в закладки пользователя 
* @package posts\actions
*/
class Bookmark extends PostAction {
    
    /**
    * Метод выполнения переключения закладки
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new \Exception(LANG_ACTION_POSTS_BOOKMARK_AUTH_REQUIRED);
            }
            
            $postId = $this->params['id'] ?? null;
            if (!$postId) {
                throw new \Exception(LANG_ACTION_POSTS_BOOKMARK_NO_POST_ID);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception(LANG_ACTION_POSTS_BOOKMARK_INVALID_METHOD);
            }
            
            $userId = $_SESSION['user_id'];
            
            $post = $this->postModel->getById($postId);
            if (!$post) {
                throw new \Exception(LANG_ACTION_POSTS_BOOKMARK_POST_NOT_FOUND);
            }
            
            $result = $this->postModel->toggleBookmark($postId, $userId);
            
            if ($result['bookmarked']) {
                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onPostBookmarked($userId, $postId);
                } catch (\Exception $e) {}
            }
            
            echo json_encode([
                'success' => true,
                'bookmarked' => $result['bookmarked'],
                'message' => $result['message']
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}