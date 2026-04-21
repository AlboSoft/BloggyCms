<?php

namespace comments\actions;

/**
* Действие редактирования комментария пользователем
* @package comments\actions
* @extends CommentAction
*/
class Edit extends CommentAction {
    
    /**
    * Метод выполнения редактирования комментария
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_COMMENTS_EDIT_ID_NOT_SPECIFIED);
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
            return;
        }
        
        $id = (int)$id;
        
        try {
            $currentUserId = $this->getCurrentUserId();
            
            if (!$currentUserId) {
                \Notification::error(LANG_ACTION_COMMENTS_EDIT_NOT_AUTH);
                $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
                $this->redirect(BASE_URL . '/auth/login');
                return;
            }
            
            $comment = $this->commentModel->getCommentById($id);
            
            if (!$comment) {
                \Notification::error(LANG_ACTION_COMMENTS_EDIT_NOT_FOUND);
                $this->redirect(BASE_URL);
                return;
            }
            
            $post = $this->postModel->getById($comment['post_id']);
            
            if (!$post) {
                \Notification::error(LANG_ACTION_COMMENTS_EDIT_POST_NOT_FOUND);
                $this->redirect(BASE_URL);
                return;
            }
            
            $userId = $comment['user_id'] ?? null;
            
            if (!\AuthHelper::canEditComment($userId)) {
                \Notification::error(LANG_ACTION_COMMENTS_EDIT_NO_PERMISSION);
                $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
                return;
            }
            
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_EDIT_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_EDIT_BREADCRUMB_POSTS, BASE_URL . '/posts');
            
            if (!empty($post['category_id'])) {
                $category = $this->categoryModel->getById($post['category_id']);
                if ($category) {
                    $this->addBreadcrumb(
                        $category['name'],
                        BASE_URL . '/category/' . $category['slug']
                    );
                }
            }
            
            $this->addBreadcrumb($post['title'], BASE_URL . '/post/' . $post['slug']);
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_EDIT_BREADCRUMB_COMMENT . $id, BASE_URL . '/post/' . $post['slug'] . '#tg-comment-' . $id);
            $this->addBreadcrumb(LANG_ACTION_COMMENTS_EDIT_BREADCRUMB_EDIT);
            
            $isAdmin = $this->isAdmin();
            $pageTitle = $isAdmin ? LANG_ACTION_COMMENTS_EDIT_PAGE_TITLE_ADMIN : LANG_ACTION_COMMENTS_EDIT_PAGE_TITLE;
            $this->setPageTitle($pageTitle);
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $content = trim($_POST['content'] ?? '');
                
                if (empty($content)) {
                    \Notification::error(LANG_ACTION_COMMENTS_EDIT_EMPTY_CONTENT);
                    
                    $this->renderForm($comment, $post, $isAdmin);
                    return;
                }
                
                $status = $comment['status'];
                
                if (\AuthHelper::can('comment_edit_no_moderations')) {
                    $status = $_POST['status'] ?? $status;
                } else {
                    $status = 'pending';
                }
                
                $data = [
                    'content' => $content,
                    'status' => $status
                ];
                
                if ($isAdmin && isset($_POST['author_name'])) {
                    $data['author_name'] = trim($_POST['author_name']);
                }
                
                if ($isAdmin && isset($_POST['author_email'])) {
                    $data['author_email'] = trim($_POST['author_email']);
                }
                
                $this->commentModel->updateComment($id, $data);
                
                if ($status === 'approved') {
                    \Notification::success(LANG_ACTION_COMMENTS_EDIT_SUCCESS_APPROVED);
                } elseif ($status === 'pending') {
                    \Notification::success(LANG_ACTION_COMMENTS_EDIT_SUCCESS_PENDING);
                } else {
                    \Notification::success(LANG_ACTION_COMMENTS_EDIT_SUCCESS);
                }
                
                $this->redirect(BASE_URL . '/post/' . $post['slug'] . '#comment-' . $id);
                return;
            }
            
            $this->renderForm($comment, $post, $isAdmin);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_COMMENTS_EDIT_ERROR . $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
        }
    }
    
    /**
    * Рендеринг формы редактирования комментария
    * @param array $comment Данные редактируемого комментария
    * @param array $post Данные поста, к которому относится комментарий
    * @param bool $isAdmin Флаг административных прав
    * @return void
    */
    private function renderForm($comment, $post, $isAdmin = false) {
        $this->render('front/comments/edit', [
            'comment' => $comment,
            'post' => $post,
            'postModel' => $this->postModel,
            'isAdmin' => $isAdmin,
            'pageTitle' => $isAdmin ? LANG_ACTION_COMMENTS_EDIT_PAGE_TITLE_ADMIN : LANG_ACTION_COMMENTS_EDIT_PAGE_TITLE
        ]);
    }
}