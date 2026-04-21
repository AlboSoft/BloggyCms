<?php

namespace fields\actions;

/**
* Действие отображения общего списка полей в админ-панели
* @package fields\actions
*/
class AdminIndex extends FieldAction {
    
    /**
    * Метод выполнения отображения списка полей
    * @return void
    */
    public function execute() {
        $this->addBreadcrumb(LANG_ACTION_FIELDS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FIELDS_ADMININDEX_BREADCRUMB_FIELDS);
        
        $fields = $this->fieldModel->getAll();
        
        $this->render('admin/fields/index', [
            'fields' => $fields,
            'fieldModel' => $this->fieldModel,
            'pageTitle' => LANG_ACTION_FIELDS_ADMININDEX_PAGE_TITLE
        ]);
    }

}