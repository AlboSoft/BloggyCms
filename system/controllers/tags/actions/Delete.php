<?php

namespace tags\actions;

/**
* Действие удаления тега в административной панели
* @package tags\actions
* @extends TagAction
*/
class Delete extends TagAction {
    
    /**
    * Метод выполнения удаления тега
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_TAGS_DELETE_NO_ID);
            $this->redirect(ADMIN_URL . '/tags');
            return;
        }
        
        try {
            $this->tagModel->delete($id);

            \Notification::success(LANG_ACTION_TAGS_DELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_TAGS_DELETE_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/tags');
    }
}