<?php

namespace posts\actions;

/**
* Действие отображения списка всех постов в административной панели
* @package posts\actions
*/
class AdminIndex extends PostAction {
    
    /**
    * Метод выполнения отображения списка постов в админ-панели
    * @return void
    */
    public function execute() {
        $this->pageTitle = LANG_ACTION_POSTS_ADMININDEX_PAGE_TITLE;
        
        $this->addBreadcrumb(LANG_ACTION_POSTS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_POSTS_ADMININDEX_BREADCRUMB_POSTS);
        
        try {
            $categoryId = $_GET['category'] ?? null;
            $status = $_GET['status'] ?? null;
            
            $posts = $this->postModel->getAllWithFilters($categoryId, $status);
            
            $categories = $this->categoryModel->getAll();
            
            $this->render('admin/posts/index', [
                'posts' => $posts,
                'categories' => $categories,
                'pageTitle' => LANG_ACTION_POSTS_ADMININDEX_RENDER_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_POSTS_ADMININDEX_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }

}