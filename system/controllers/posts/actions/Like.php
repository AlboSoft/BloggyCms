<?php

namespace posts\actions;

/**
* Действие лайка/дизлайка поста
* @package posts\actions
*/
class Like extends PostAction {
    
    /**
    * Метод выполнения переключения лайка 
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new \Exception(LANG_ACTION_POSTS_LIKE_AUTH_REQUIRED);
            }
            
            $postId = $this->params['id'] ?? null;
            if (!$postId) {
                throw new \Exception(LANG_ACTION_POSTS_LIKE_NO_POST_ID);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception(LANG_ACTION_POSTS_LIKE_INVALID_METHOD);
            }
            
            $userId = $_SESSION['user_id'];
            
            $post = $this->postModel->getById($postId);
            if (!$post) {
                throw new \Exception(LANG_ACTION_POSTS_LIKE_POST_NOT_FOUND);
            }
            
            $result = $this->postModel->toggleLike($postId, $userId);
            
            if ($result['liked']) {
                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onPostLiked($userId, $postId);
                } catch (\Exception $e) {}
            }
            
            echo json_encode([
                'success' => true,
                'liked' => $result['liked'],
                'likes_count' => $result['likes_count'],
                'message' => $result['liked'] ? LANG_ACTION_POSTS_LIKE_ADDED : LANG_ACTION_POSTS_LIKE_REMOVED
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