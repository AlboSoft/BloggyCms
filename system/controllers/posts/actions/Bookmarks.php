<?php

namespace posts\actions;

/**
* Действие отображения для отображения закладок пользователя
* @package posts\actions
*/
class Bookmarks extends PostAction {
    
    /**
    * Метод выполнения отображения закладок пользователя
    * @return void
    */
    public function execute() {
        if (!isset($_SESSION['user_id'])) {
            \Notification::error(LANG_ACTION_POSTS_BOOKMARKS_AUTH_REQUIRED);
            $this->redirect(BASE_URL . '/login');
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $username = $_SESSION['username'] ?? '';
            
            if (empty($username)) {
                $user = $this->userModel->getById($userId);
                $username = $user['username'] ?? '';
            }
            
            $this->addBreadcrumb(LANG_ACTION_POSTS_BOOKMARKS_BREADCRUMB_HOME, BASE_URL);
            
            if (!empty($username)) {
                $this->addBreadcrumb(LANG_ACTION_POSTS_BOOKMARKS_BREADCRUMB_PROFILE, BASE_URL . '/profile/' . $username);
            }
            
            $this->addBreadcrumb(LANG_ACTION_POSTS_BOOKMARKS_BREADCRUMB_BOOKMARKS);
            $this->setPageTitle(LANG_ACTION_POSTS_BOOKMARKS_PAGE_TITLE);
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            $perPage = 10;
            
            $result = $this->postModel->getUserBookmarks($userId, $page, $perPage);
            
            $this->render('front/posts/bookmarks', [
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'bookmarks_count' => $result['total']
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_POSTS_BOOKMARKS_ERROR, $e->getMessage()));
            $this->redirect(BASE_URL);
        }
    }
}