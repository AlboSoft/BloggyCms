<?php

namespace search\actions;

/**
* Действие для отображения результатов поиска на фронтенде
* @package search\actions
*/
class Index extends SearchAction {
    
    /**
    * Выполняет действие поиска
    * @return void
    */
    public function execute() {
        try {
            $query = trim($_GET['q'] ?? '');
            $type = $_GET['type'] ?? 'all';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            
            if ($page < 1) $page = 1;
            
            if (empty($query)) {

                $this->addBreadcrumb(LANG_ACTION_SEARCH_INDEX_BREADCRUMB_HOME, BASE_URL);
                $this->addBreadcrumb(LANG_ACTION_SEARCH_INDEX_BREADCRUMB_SEARCH);
                $this->setPageTitle(LANG_ACTION_SEARCH_INDEX_PAGE_TITLE_EMPTY);
                
                $popularQueries = $this->searchModel->getPopularSearchQueries(10);
                $suggestedSearches = $this->searchModel->getSuggestedSearches(6);
                
                $this->render('front/search/index', [
                    'query' => '',
                    'results' => [],
                    'total' => 0,
                    'pages' => 0,
                    'current_page' => 1,
                    'type' => 'all',
                    'popularQueries' => $popularQueries,
                    'suggestedSearches' => $suggestedSearches
                ]);
                return;
            }
            
            $this->addBreadcrumb(LANG_ACTION_SEARCH_INDEX_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_SEARCH_INDEX_BREADCRUMB_SEARCH, BASE_URL . '/search');
            $this->addBreadcrumb(sprintf(LANG_ACTION_SEARCH_INDEX_BREADCRUMB_RESULTS, html($query)));
            $this->setPageTitle(sprintf(LANG_ACTION_SEARCH_INDEX_PAGE_TITLE_RESULTS, html($query)));
            
            $this->searchModel->saveSearchQuery($query);
            $results = $this->searchModel->searchAll($query, $type, $page);
            $popularQueries = $this->searchModel->getPopularSearchQueries(10);
            $suggestedSearches = $this->searchModel->getSuggestedSearches(6);
            
            $this->render('front/search/index', [
                'query' => $query,
                'results' => $results['items'],
                'total' => $results['total'],
                'pages' => $results['pages'],
                'current_page' => $results['current_page'],
                'type' => $type,
                'popularQueries' => $popularQueries,
                'suggestedSearches' => $suggestedSearches
            ]);
            
        } catch (\Exception $e) {
            
            $this->addBreadcrumb(LANG_ACTION_SEARCH_INDEX_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_SEARCH_INDEX_BREADCRUMB_SEARCH, BASE_URL . '/search');
            $this->addBreadcrumb(LANG_ACTION_SEARCH_INDEX_BREADCRUMB_ERROR);
            
            $this->setPageTitle(LANG_ACTION_SEARCH_INDEX_PAGE_TITLE_ERROR);
            
            $this->render('front/search/index', [
                'error' => LANG_ACTION_SEARCH_INDEX_ERROR_MESSAGE,
                'query' => $_GET['q'] ?? '',
                'popularQueries' => $this->searchModel->getPopularSearchQueries(10),
                'suggestedSearches' => $this->searchModel->getSuggestedSearches(6)
            ]);
        }
    }
}