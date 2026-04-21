<?php
namespace categories\actions;

/**
* Действие отображения всех категорий на фронте
* @package categories\actions
*/
class Index extends CategoryAction {
    /**
    * Метод выполнения отображения всех категорий
    * @return void
    */
    public function execute() {
        try {
            $this->addBreadcrumb(LANG_ACTION_CATEGORIES_INDEX_BREADCRUMB_HOME, BASE_URL);
            $this->addBreadcrumb(LANG_ACTION_CATEGORIES_INDEX_BREADCRUMB_CATEGORIES);
            $this->setPageTitle(LANG_ACTION_CATEGORIES_INDEX_PAGE_TITLE);
            
            $layout = \SettingsHelper::get('controller_categories', 'category_layout', 'grid');
            $perPage = \SettingsHelper::get('controller_categories', 'categories_per_page', 12);
            $showImages = \SettingsHelper::get('controller_categories', 'show_category_images', true);
            $showDescriptions = \SettingsHelper::get('controller_categories', 'show_category_descriptions', true);
            $showPostCounts = \SettingsHelper::get('controller_categories', 'show_post_counts', true);
            $orderBy = \SettingsHelper::get('controller_categories', 'categories_order', 'name');
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            $categories = $this->categoryModel->getAll();
            $categories = $this->sortCategories($categories, $orderBy);
            $totalCategories = count($categories);
            $totalPages = ceil($totalCategories / $perPage);
            $offset = ($page - 1) * $perPage;
            $categories = array_slice($categories, $offset, $perPage);
            
            $this->render('front/category/categories', [
                'categories' => $categories,
                'total_categories' => $totalCategories,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'layout' => $layout,
                'show_images' => $showImages,
                'show_descriptions' => $showDescriptions,
                'show_post_counts' => $showPostCounts,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'has_more' => $page < $totalPages,
                    'next_url' => $this->getNextPageUrl($page, $totalPages)
                ]
            ]);
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_CATEGORIES_INDEX_ERROR);
            $this->redirect(BASE_URL);
        }
    }
    
    /**
    * Сортирует категории по указанному полю
    * @param array $categories Массив категорий
    * @param string $orderBy Поле для сортировки
    * @return array Отсортированный массив
    */
    private function sortCategories($categories, $orderBy) {
        switch ($orderBy) {
            case 'posts_count':
                usort($categories, function($a, $b) {
                    return ($b['posts_count'] ?? 0) - ($a['posts_count'] ?? 0);
                });
                break;
            case 'created_at':
                usort($categories, function($a, $b) {
                    return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
                });
                break;
            case 'sort_order':
                usort($categories, function($a, $b) {
                    return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
                });
                break;
            case 'name':
            default:
                usort($categories, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                break;
        }
        return $categories;
    }
    
    /**
    * Получить URL следующей страницы для пагинации
    * @param int $currentPage Текущая страница
    * @param int $totalPages Всего страниц
    * @return string|null URL следующей страницы или null
    */
    private function getNextPageUrl($currentPage, $totalPages) {
        if ($currentPage < $totalPages) {
            return BASE_URL . '/categories?page=' . ($currentPage + 1);
        }
        return null;
    }
}