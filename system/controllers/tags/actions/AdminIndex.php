<?php

namespace tags\actions;

/**
* Действие отображения списка всех тегов в административной панели
* @package tags\actions
*/
class AdminIndex extends TagAction {
    
    /**
    * Метод выполнения отображения списка тегов
    * @return void
    */
    public function execute() {

        $this->addBreadcrumb(LANG_ACTION_TAGS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_TAGS_ADMININDEX_BREADCRUMB_TAGS);
        
        try {
            $tags = $this->tagModel->getAll();
            $hints = [
                LANG_ACTION_TAGS_ADMININDEX_HINT_1,
                LANG_ACTION_TAGS_ADMININDEX_HINT_2,
                LANG_ACTION_TAGS_ADMININDEX_HINT_3,
                LANG_ACTION_TAGS_ADMININDEX_HINT_4,
                LANG_ACTION_TAGS_ADMININDEX_HINT_5
            ];
            
            $randomHint = $hints[array_rand($hints)];

            $settings = [
                'default_image' => \SettingsHelper::get('controller_tags', 'default_image'),
                'tag_prefix' => \SettingsHelper::get('controller_tags', 'tag_prefix', '#'),
                'show_info' => \SettingsHelper::get('controller_tags', 'show_info')
            ];

            $this->render('admin/tags/index', [
                'tags' => $tags,
                'randomHint' => $randomHint,
                'pageTitle' => LANG_ACTION_TAGS_ADMININDEX_PAGE_TITLE,
                'settings' => $settings
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_TAGS_ADMININDEX_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }

}