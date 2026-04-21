<?php

namespace fields\actions;

/**
* Действие отображения полей для конкретной сущности в админ-панели
* @package fields\actions
*/
class AdminEntity extends FieldAction {
    
    /**
    * Метод выполнения отображения полей сущности
    * @return void
    */
    public function execute() {
        $entityType = $this->params['entityType'] ?? null;
        
        if (!$entityType) {
            \Notification::error(LANG_ACTION_FIELDS_ADMINENTITY_ENTITY_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fields');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FIELDS_ADMINENTITY_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FIELDS_ADMINENTITY_BREADCRUMB_FIELDS, ADMIN_URL . '/fields');
        $this->addBreadcrumb($this->getEntityName($entityType, true));
        
        $fields = $this->fieldModel->getByEntityType($entityType);
        
        $this->render('admin/fields/entity', [
            'fields' => $fields,
            'entityType' => $entityType,
            'entityName' => $this->getEntityName($entityType),
            'fieldModel' => $this->fieldModel,
            'pageTitle' => LANG_ACTION_FIELDS_ADMINENTITY_PAGE_TITLE . $this->getEntityName($entityType)
        ]);
    }

}