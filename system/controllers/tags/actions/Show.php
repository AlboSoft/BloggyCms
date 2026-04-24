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
            
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            foreach ($result['posts'] as &$post) {
                $post['userVote'] = $this->postModel->getUserVote($post['id'], $ipAddress);
            }

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