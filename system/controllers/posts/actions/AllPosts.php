<?php

namespace posts\actions;

/**
* Действие отображения всех постов с пагинацией (публичная часть) 
* @package posts\actions
*/
class AllPosts extends PostAction {
    
    /**
    * Метод выполнения отображения всех постов
    * @return void
    */
    public function execute() {
        try {

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
        
            $userGroups = $this->getUserGroups();
            
            $result = $this->postModel->getAllPaginated($page, null, $userGroups);
            
            $categories = $this->categoryModel->getAll();
            
            foreach ($result['posts'] as &$post) {
                if (isset($_SESSION['user_id'])) {
                    $post['userLiked'] = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
                } else {
                    $post['userLiked'] = false;
                }
            }
        
            $this->render('front/posts/posts', [
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'title' => LANG_ACTION_POSTS_ALLPOSTS_TITLE,
                'categories' => $categories
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_POSTS_ALLPOSTS_ERROR, $e->getMessage()));
            $this->redirect(BASE_URL);
        }
    }
    
    /**
    * Получает группы текущего пользователя для фильтрации видимости постов
    * @return array Массив групп пользователя
    */
    private function getUserGroups() {
        $userGroups = [];
        
        $userGroups[] = 'guest';
        
        if (isset($_SESSION['user_id'])) {
            try {
                $userModel = new \UserModel($this->db);
                $userGroupIds = $userModel->getUserGroupIds($_SESSION['user_id']);
                
                if (!empty($userGroupIds)) {
                    $userGroupIds = array_map('strval', $userGroupIds);
                    $userGroups = array_merge($userGroups, $userGroupIds);
                }
                
            } catch (\Exception $e) {}
        }
        
        $userGroups = array_unique($userGroups);
        
        return $userGroups;
    }

}