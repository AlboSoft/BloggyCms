<?php

namespace fragments\actions;

/**
* Действие удаления записи фрагмента
*/
class AdminEntryDelete extends FragmentAction {
    
    public function execute() {
        $entryId = $this->params['id'] ?? null;
        
        if (!$entryId) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRYDELETE_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        $entry = $this->entryModel->getById($entryId);
        
        if (!$entry) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRYDELETE_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/fragments');
            return;
        }
        
        try {
            $this->entryModel->delete($entryId);
            \Notification::success(LANG_ACTION_FRAGMENTS_ADMINENTRYDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_FRAGMENTS_ADMINENTRYDELETE_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/fragments/entries/' . $entry['fragment_id']);
    }
}