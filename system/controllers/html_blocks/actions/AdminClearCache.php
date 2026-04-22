<?php

namespace html_blocks\actions;

class AdminClearCache extends HtmlBlockAction {
    
    public function execute() {
        
        try {
            clear_blocks_assets_cache();
            
            regenerate_blocks_css();
            
            \Notification::success(LANG_ACTION_HTMLBLOCKS_ADMINCLEARCACHE_SUCCESS);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINCLEARCACHE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/html-blocks');
    }
}