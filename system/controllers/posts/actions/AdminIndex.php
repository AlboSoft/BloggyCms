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
            $createMode = $_GET['create_mode'] ?? null;
            $createDate = $_GET['create_date'] ?? null;
            $createDateFrom = $_GET['create_date_from'] ?? null;
            $createDateTo = $_GET['create_date_to'] ?? null;
            
            $posts = $this->postModel->getAllWithFilters($categoryId, $status, $createMode, $createDate, $createDateFrom, $createDateTo);
            
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