<?php

namespace posts\actions;

/**
* Действие отображения всех постов с пагинацией (публичная часть)
* @package posts\actions
*/
class All extends PostAction {
    
    /**
    * Метод выполнения отображения всех постов 
    * @return void
    */
    public function execute() {
        try {

            $this->addBreadcrumb(LANG_ACTION_POSTS_ALL_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_POSTS_ALL_BREADCRUMB_ALL_POSTS);
            $this->setPageTitle(LANG_ACTION_POSTS_ALL_PAGE_TITLE);
            
            $settingsModel = new \SettingsModel($this->db);
            $postsSettings = $settingsModel->get('controller_posts');
            $postsPerPage = (int)($postsSettings['homepage_posts_per_page'] ?? 10);
            $postsPerPage = max(1, min(50, $postsPerPage));
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            $userGroups = $this->getUserGroups();
            $result = $this->postModel->getAllPaginated($page, $postsPerPage, $userGroups);
            
            $categories = $this->categoryModel->getAll();
            $postIds = array_column($result['posts'], 'id');
            $commentsCount = [];
            
            if (!empty($postIds)) {
                $commentsCount = $this->postModel->getCommentsCountForPosts($postIds);
            }
            
            foreach ($result['posts'] as &$post) {
                $postId = $post['id'];
                $post['comments_count'] = $commentsCount[$postId] ?? 0;
                
                if (isset($_SESSION['user_id'])) {
                    $post['userLiked'] = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
                    $post['userBookmarked'] = $this->postModel->hasBookmark($post['id'], $_SESSION['user_id']);
                } else {
                    $post['userLiked'] = false;
                    $post['userBookmarked'] = false;
                }
                
                $post['password_protected'] = $post['password_protected'] == 1;
                
                if (!isset($post['likes_count'])) {
                    $post['likes_count'] = $post['likes_count'] ?? 0;
                }
            }
            
            $this->render('front/posts/index', [
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'categories' => $categories,
                'pagination' => [
                    'current_page' => $result['current_page'],
                    'total_pages' => $result['pages'],
                    'has_more' => $page < $result['pages'],
                    'next_url' => $this->getNextPageUrl($page, $result['pages'])
                ]
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_POSTS_ALL_ERROR, $e->getMessage()));
            $this->redirect(BASE_URL);
        }
    }
    
    /**
    * Получить URL следующей страницы для пагинации
    * @param int $currentPage Текущая страница
    * @param int $totalPages Всего страниц
    * @return string|null URL следующей страницы или null
    */
    private function getNextPageUrl($currentPage, $totalPages) {
        if ($currentPage < $totalPages) {
            return BASE_URL . '/posts?page=' . ($currentPage + 1);
        }
        return null;
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