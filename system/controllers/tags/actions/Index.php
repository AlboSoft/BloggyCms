<?php

namespace tags\actions;

/**
* Действие отображения списка всех тегов в публичной части
* @package tags\actions
*/
class Index extends TagAction {
    
    /**
    * Метод выполнения отображения списка тегов
    * @return void
    */
    public function execute() {
        try {
            $this->addBreadcrumb(LANG_ACTION_TAGS_INDEX_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_TAGS_INDEX_BREADCRUMB_TAGS);
            $this->setPageTitle(LANG_ACTION_TAGS_INDEX_PAGE_TITLE);
            
            $page = (int)($_GET['page'] ?? 1);
            
            $tagsOrder = \SettingsHelper::get('controller_tags', 'tags_order', 'name');
            $minPostsToShow = \SettingsHelper::get('controller_tags', 'min_posts_to_show', 1);
            $tagsPerPage = \SettingsHelper::get('controller_tags', 'cont_tags_in_front', 12);
            
            if ($tagsPerPage < 1) {
                $tagsPerPage = 12;
            }
            
            $result = $this->tagModel->getFilteredTags($minPostsToShow, $page, $tagsPerPage, $tagsOrder);
            
            $this->render('front/tags/tags', [
                'tags' => $result['tags'],
                'pagination' => $result['pagination'],
                'settings' => [
                    'tags_per_page' => $tagsPerPage,
                    'min_posts' => $minPostsToShow,
                    'order' => $tagsOrder
                ]
            ]);
            
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}