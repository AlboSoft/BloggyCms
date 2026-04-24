<?php

namespace posts\actions;

/**
* Действие голосования за пост (устаревшее, использует систему лайков)
* @package posts\actions
*/
class Vote extends PostAction {
    
    /**
    * Метод выполнения голосования за пост 
    * @return void
    */
    public function execute() {

        $postId = $this->params['id'] ?? null;
        if (!$postId) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_POSTS_VOTE_NO_POST_ID]);
            return;
        }

        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_POSTS_VOTE_INVALID_METHOD]);
            return;
        }
    
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => LANG_ACTION_POSTS_VOTE_AUTH_REQUIRED,
                    'redirect' => BASE_URL . '/login'
                ]);
                return;
            }
            
            $userId = $_SESSION['user_id'];
    
            $post = $this->postModel->getById($postId);
            if (!$post) {
                echo json_encode(['success' => false, 'message' => LANG_ACTION_POSTS_VOTE_POST_NOT_FOUND]);
                return;
            }
    
            $result = $this->postModel->toggleLike($postId, $userId);
            
            echo json_encode([
                'success' => true,
                'liked' => $result['liked'],
                'likes_count' => $result['likes_count'],
                'message' => $result['liked'] ? LANG_ACTION_POSTS_VOTE_LIKED : LANG_ACTION_POSTS_VOTE_UNLIKED
            ]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => sprintf(LANG_ACTION_POSTS_VOTE_SERVER_ERROR, $e->getMessage())]);
        }
        exit;
    }
}