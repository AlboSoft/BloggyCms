<?php

namespace html_blocks\actions;

/**
* Действие удаления HTML-блока в админ-панели
* @package html_blocks\actions
*/
class AdminDelete extends HtmlBlockAction {
    
    /**
    * Метод выполнения удаления HTML-блока
    * @return void
    */
    public function execute() {
        
        try {
            $this->htmlBlockModel->delete($this->id);

            \Event::trigger('html_block.deleted', $this->id);
            
            \Notification::success(LANG_ACTION_HTMLBLOCKS_ADMINDELETE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINDELETE_ERROR);
        }
        
        $this->redirect(ADMIN_URL . '/html-blocks');
    }
}