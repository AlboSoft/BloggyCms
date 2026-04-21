<?php

namespace categories\actions;

/**
* Действие для отображения списка категорий в админ-панели
* @package categories\actions
* @extends CategoryAction
*/
class AdminIndex extends CategoryAction {
    
    /**
    * Основной метод выполнения действия
    * @return void
    * @throws \Exception
    */
    public function execute() {
        try {

            $this->addBreadcrumb(LANG_ACTION_CATEGORIES_ADMININDEX_BREADCRUMB);
        
            $categories = $this->categoryModel->getAllOrdered();
            
            $hints = [
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_1,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_2,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_3,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_4,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_5,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_6,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_7,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_8,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_9,
                LANG_ACTION_CATEGORIES_ADMININDEX_HINT_10,
            ];
            
            $randomHint = $hints[array_rand($hints)];
            
            /**
            * Рендеринг шаблона админ-панели с передачей данных
            */
            $this->render('admin/categories/index', [
                'categories' => $categories,
                'randomHint' => $randomHint,
                'pageTitle' => LANG_ACTION_CATEGORIES_ADMININDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_CATEGORIES_ADMININDEX_ERROR . $e->getMessage());
            $this->redirect(ADMIN_URL);
        }
    }
}