<?php

namespace fragments\actions;

/**
* Действие отображения списка фрагментов
*/
class AdminIndex extends FragmentAction {
    
    /**
    * Действие отображения списка фрагментов
    */
    public function execute() {
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMININDEX_BREADCRUMB_FRAGMENTS);
        $this->setPageTitle(LANG_ACTION_FRAGMENTS_ADMININDEX_PAGE_TITLE);
        
        $fragments = $this->fragmentModel->getAll();
        
        $hints = [
            LANG_ACTION_FRAGMENTS_ADMININDEX_HINT_1,
            LANG_ACTION_FRAGMENTS_ADMININDEX_HINT_2,
            LANG_ACTION_FRAGMENTS_ADMININDEX_HINT_3,
            LANG_ACTION_FRAGMENTS_ADMININDEX_HINT_4,
            LANG_ACTION_FRAGMENTS_ADMININDEX_HINT_5
        ];
        
        $randomHint = $hints[array_rand($hints)];
        
        $this->render('admin/fragments/index', [
            'fragments' => $fragments,
            'randomHint' => $randomHint,
            'totalFragments' => count($fragments)
        ]);
    }

}