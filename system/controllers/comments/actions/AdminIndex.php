<?php

namespace comments\actions;

/**
* Действие отображения списка комментариев в админ-панели
* @package comments\actions
*/
class AdminIndex extends CommentAction {
    
    /**
    * Метод выполнения отображения списка комментариев
    * @return void
    */
    public function execute() {
        try {
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_ADMININDEX_BREADCRUMB_COMMENTS);

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 20;
            
            $result = $this->commentModel->getAllComments($page, $perPage);
            
            $this->render('admin/comments/index', [
                'comments' => $result['comments'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'pageTitle' => LANG_ACTION_COMMENTS_ADMININDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_COMMENTS_ADMININDEX_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }
}