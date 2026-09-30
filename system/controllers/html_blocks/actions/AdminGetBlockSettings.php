<?php

namespace html_blocks\actions;

/**
* Действие получения настроек типа блока через AJAX
* @package html_blocks\actions
* @extends HtmlBlockAction
*/
class AdminGetBlockSettings extends HtmlBlockAction {
    
    /**
    * Метод выполнения получения настроек типа блока 
    * @return void
    */
    public function execute() {
        $systemName = $_GET['system_name'] ?? '';
        
        $currentSettings = isset($_GET['current_settings']) ? 
            json_decode($_GET['current_settings'], true) : [];
        
        if (empty($systemName)) {
            echo '';
            return;
        }
        
        if ($systemName === 'DefaultBlock') {
            echo $this->getDefaultBlockSettingsForm($currentSettings);
            return;
        }

        if (!$this->blockTypeManager->isBlockTypeAvailable($systemName)) {
            echo '<div class="alert alert-warning">' . html(LANG_ACTION_HTMLBLOCKS_ADMINGETBLOCKSETTINGS_UNAVAILABLE) . '</div>';
            return;
        }
        
        $blockType = $this->blockTypeManager->getBlockType($systemName);
        
        if ($blockType && $blockType['class']) {
            echo $blockType['class']->getSettingsForm($currentSettings);
        } else {
            echo '';
        }
    }
}