<?php

namespace tags\actions;

/**
* Действие отображения постов по тегу в публичной части
* @package tags\actions
*/
class Show extends TagAction {
    
    /**
    * Метод выполнения отображения постов по тегу
    * @return void
    */
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        
        if (!$slug) {
            \Notification::error(LANG_ACTION_TAGS_SHOW_TAG_NOT_FOUND);
            $this->redirect(BASE_URL);
            return;
        }
        
        try {

            $tag = $this->tagModel->getBySlug($slug);
            
            if (!$tag) {
                \Notification::error(LANG_ACTION_TAGS_SHOW_TAG_NOT_FOUND);
                $this->redirect(BASE_URL);
                return;
            }
            
            $this->addBreadcrumb(LANG_ACTION_TAGS_SHOW_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_TAGS_SHOW_BREADCRUMB_TAGS, BASE_URL . '/tags');
            $this->addBreadcrumb(sprintf(LANG_ACTION_TAGS_SHOW_BREADCRUMB_TAG, $tag['name']));
            $this->setPageTitle(sprintf(LANG_ACTION_TAGS_SHOW_PAGE_TITLE, $tag['name']));
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            
            $result = $this->tagModel->getPostsByTag($tag['id'], $page);
            
            $categories = $this->categoryModel->getAll();
            
            $postIds = array_column($result['posts'], 'id');
            $commentsCount = !empty($postIds) ? $this->postModel->getCommentsCountForPosts($postIds) : [];
            $postTags = !empty($postIds) ? $this->tagModel->getForPosts($postIds) : [];
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

            foreach ($result['posts'] as &$post) {
                $post['userVote'] = $this->postModel->getUserVote($post['id'], $ipAddress);
                $post['comments_count'] = $commentsCount[$post['id']] ?? 0;
                $post['tags'] = $postTags[$post['id']] ?? [];
                $post['password_protected'] = !empty($post['password_protected']);

                if (isset($_SESSION['user_id'])) {
                    $post['userLiked'] = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
                    $post['userBookmarked'] = $this->postModel->hasBookmark($post['id'], $_SESSION['user_id']);
                } else {
                    $post['userLiked'] = false;
                    $post['userBookmarked'] = false;
                }
            }
            unset($post);

            $this->render('front/tags/tag', [
                'tag' => $tag,
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'categories' => $categories
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_TAGS_SHOW_ERROR);
            $this->redirect(BASE_URL);
        }
    }
}