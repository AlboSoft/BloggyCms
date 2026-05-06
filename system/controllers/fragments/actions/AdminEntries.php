<?php

namespace fragments\actions;

/**
* Действие управления записями фрагмента
*/
class AdminEntries extends FragmentAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRIES_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $fragment = $this->fragmentModel->getById($id);
        
        if (!$fragment) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRIES_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRIES_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRIES_BREADCRUMB_FRAGMENTS, ADMIN_URL . '/fragments');
        $this->addBreadcrumb($fragment['name'], ADMIN_URL . '/fragments/edit/' . $id);
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ADMINENTRIES_BREADCRUMB_ENTRIES);
        $this->setPageTitle(LANG_ACTION_FRAGMENTS_ADMINENTRIES_PAGE_TITLE . $fragment['name']);
        
        $entries = $this->entryModel->getByFragment($id);
        $fields = $this->fragmentModel->getFields($id);
        $stats = $this->fragmentModel->getStats($id);
        
        $this->render('admin/fragments/entries', [
            'fragment' => $fragment,
            'entries' => $entries,
            'fields' => $fields,
            'pageTitle' => LANG_ACTION_FRAGMENTS_ADMINENTRIES_PAGE_TITLE . $fragment['name'],
            'stats' => $stats
        ]);
    }
}