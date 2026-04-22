<?php

namespace postblocks\actions;

/**
* Действие получения HTML-формы настроек для постблока
* @package postblocks\actions
* @extends PostBlockAction
*/
class AdminGetSettingsForm extends PostBlockAction {
    
    /**
    * Метод выполнения получения формы настроек
    * @return void
    */
    public function execute() {
        
        $systemName = $_GET['system_name'] ?? '';
        
        $currentSettings = isset($_GET['current_settings']) ? 
            json_decode(urldecode($_GET['current_settings']), true) : [];
        $currentContent = isset($_GET['current_content']) ? 
            json_decode(urldecode($_GET['current_content']), true) : [];
        
        if (empty($systemName)) {
            echo '';
            return;
        }
        
        $postBlock = $this->postBlockManager->getPostBlock($systemName);
        
        if ($postBlock && $postBlock['class']) {
            $blockInstance = $postBlock['class'];
            
            $html = '
            <form id="post-block-form">
                <ul class="nav nav-tabs mb-4" id="blockTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-tab-pane" type="button" role="tab">
                            <i class="bi bi-text-left me-1"></i>' . LANG_ACTION_POSTBLOCKS_ADMINGETSETTINGSFORM_TAB_CONTENT . '
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-tab-pane" type="button" role="tab">
                            <i class="bi bi-gear me-1"></i>' . LANG_ACTION_POSTBLOCKS_ADMINGETSETTINGSFORM_TAB_SETTINGS . '
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="blockTabsContent">
                    <div class="tab-pane fade show active" id="content-tab-pane" role="tabpanel">
                        ' . $blockInstance->getContentForm($currentContent) . '
                    </div>
                    <div class="tab-pane fade" id="settings-tab-pane" role="tabpanel">
                        ' . $blockInstance->getSettingsForm($currentSettings) . '
                    </div>
                </div>
            </form>';
            
            echo $html;
        } else {
            echo '<div class="alert alert-warning">' . LANG_ACTION_POSTBLOCKS_ADMINGETSETTINGSFORM_BLOCK_NOT_FOUND . '</div>';
        }
    }
}