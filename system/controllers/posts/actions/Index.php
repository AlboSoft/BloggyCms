<?php

namespace posts\actions;

/**
* Действие отображения главной страницы с постами (публичная часть)
* @package posts\actions
*/
class Index extends PostAction {
    
    /**
    * Метод выполнения отображения главной страницы
    * @return void
    */
    public function execute() {
        try {

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);

            $userGroups = $this->getUserGroups();
            
            $result = $this->postModel->getAllPaginated($page, null, $userGroups);
            
            $postIds = array_column($result['posts'], 'id');
            
            $commentsCount = [];
            if (!empty($postIds)) {
                $commentsCount = $this->postModel->getCommentsCountForPosts($postIds);
            }
            
            $categories = $this->categoryModel->getAll();
            
            foreach ($result['posts'] as &$post) {
                $postId = $post['id'];
                $count = $commentsCount[$postId] ?? 0;
                
                $post['comments_count'] = $count;
                
                if (isset($_SESSION['user_id'])) {
                    $post['userLiked'] = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
                } else {
                    $post['userLiked'] = false;
                }
                
                $post['password_protected'] = $post['password_protected'] == 1;
            }
            
            $pagination = [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'has_more' => $page < $result['pages'],
                'next_url' => $this->getNextPageUrl($page)
            ];
        
            $this->render('front/index', [
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'pagination' => $pagination,
                'title' => LANG_ACTION_POSTS_INDEX_PAGE_TITLE,
                'categories' => $categories
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_POSTS_INDEX_ERROR, $e->getMessage()));
            $this->redirect(BASE_URL);
        }
    }
    
    /**
    * Получить URL следующей страницы для пагинации
    * @param int $currentPage Текущая страница
    * @return string URL следующей страницы
    */
    private function getNextPageUrl($currentPage) {
        $nextPage = $currentPage + 1;
        return BASE_URL . '/posts?page=' . $nextPage;
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
                
            } catch (\Exception $e) {
                DebugLogger::warning(LANG_ACTION_POSTS_INDEX_DEBUG_WARNING, [
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $userGroups = array_unique($userGroups);
        
        return $userGroups;
    }
}