<?php

namespace categories\actions;

/**
* Действие удаления категории
* @package categories\actions
*/
class Delete extends CategoryAction {
    
    /**
    * Метод выполнения удаления категории
    * @return void
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_CATEGORIES_DELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/categories');
            return;
        }

        try {
            $category = $this->categoryModel->getById($id);
            
            $this->addBreadcrumb(LANG_ACTION_CATEGORIES_DELETE_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_CATEGORIES_DELETE_BREADCRUMB_CATEGORIES, ADMIN_URL . '/categories');
            $this->addBreadcrumb(LANG_ACTION_CATEGORIES_DELETE_BREADCRUMB_DELETE . ($category ? $category['name'] : LANG_ACTION_CATEGORIES_DELETE_CATEGORY_PREFIX . $id));
            
            $postsCount = $this->categoryModel->getPostsCount($id);
            
            if ($postsCount > 0) {
                if (isset($_POST['delete_action'])) {
                    $deleteAction = $_POST['delete_action'];
                    
                    if ($deleteAction === 'move_posts' && !empty($_POST['target_category_id'])) {
                        $targetCategoryId = (int)$_POST['target_category_id'];
                        
                        $this->categoryModel->movePostsToCategory($id, $targetCategoryId);
                        
                        if ($category && !empty($category['image'])) {
                            $imagePath = UPLOADS_PATH . '/images/' . $category['image'];
                            \FileUpload::delete($imagePath);
                        }
                        
                        $this->categoryModel->delete($id);
                        
                        $postsWord = get_numeric_ending($postsCount, explode('|', LANG_ACTION_CATEGORIES_DELETE_POSTS_ENDING));
                        \Notification::success(sprintf(LANG_ACTION_CATEGORIES_DELETE_MOVE_SUCCESS, $postsCount, $postsWord));
                        
                    } 
                    elseif ($deleteAction === 'delete_all') {
                        $this->categoryModel->deleteWithPosts($id);
                        $postsWord = get_numeric_ending($postsCount, explode('|', LANG_ACTION_CATEGORIES_DELETE_POSTS_ENDING));
                        \Notification::success(sprintf(LANG_ACTION_CATEGORIES_DELETE_DELETE_ALL_SUCCESS, $postsCount, $postsWord));
                        
                    } 
                    else {
                        \Notification::error(LANG_ACTION_CATEGORIES_DELETE_NO_ACTION);
                        $this->redirect(ADMIN_URL . '/categories');
                        return;
                    }
                } 
                else {
                    $this->showDeleteOptions($id, $category, $postsCount);
                    return;
                }
                
            } 
            else {
                if ($category && !empty($category['image'])) {
                    $imagePath = UPLOADS_PATH . '/images/' . $category['image'];
                    \FileUpload::delete($imagePath);
                }
                
                $this->categoryModel->delete($id);
                \Notification::success(LANG_ACTION_CATEGORIES_DELETE_SUCCESS);
            }
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_CATEGORIES_DELETE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/categories');
    }
    
    /**
    * Отображение формы выбора способа удаления категории с постами
    * @param int $categoryId ID удаляемой категории
    * @param array|null $category Данные удаляемой категории
    * @param int $postsCount Количество постов в категории
    * @return void
    */
    private function showDeleteOptions($categoryId, $category, $postsCount) {
        $categories = $this->categoryModel->getAll();
        $otherCategories = array_filter($categories, function($cat) use ($categoryId) {
            return $cat['id'] != $categoryId;
        });
        
        $this->render('admin/categories/delete_options', [
            'category' => $category,
            'postsCount' => $postsCount,
            'otherCategories' => $otherCategories,
            'pageTitle' => LANG_ACTION_CATEGORIES_DELETE_PAGE_TITLE
        ]);
    }
}