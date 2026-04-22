<?php

namespace postblocks\actions;

/**
* Действие отображения списка всех постблоков в административной панели
* @package postblocks\actions
*/
class AdminIndex extends PostBlockAction {
    
    /**
    * Метод выполнения отображения списка постблоков
    * @return void
    */
    public function execute() {
        
        $this->addBreadcrumb(LANG_ACTION_POSTBLOCKS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_POSTBLOCKS_ADMININDEX_BREADCRUMB_POSTBLOCKS);
        
        try {
            $allBlocks = $this->postBlockManager->getAllPostBlocksInfo();

            $dbSettings = $this->postBlockModel->getAllBlockSettings();
            
            $blocksWithSettings = $this->mergeBlocksWithSettings($allBlocks, $dbSettings);
            
            $postBlocksByCategory = $this->groupBlocksByCategory($blocksWithSettings);
            
            $this->render('admin/post_blocks/index', [
                'postBlocksByCategory' => $postBlocksByCategory,
                'pageTitle' => LANG_ACTION_POSTBLOCKS_ADMININDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_POSTBLOCKS_ADMININDEX_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
    * Объединяет информацию о блоках с настройками из базы данных
    * @param array $allBlocks Массив всех блоков из менеджера
    * @param array $dbSettings Массив настроек из БД
    * @return array Массив блоков с объединенными настройками
    */
    private function mergeBlocksWithSettings($allBlocks, $dbSettings) {
        $blocksWithSettings = [];
        
        foreach ($allBlocks as $block) {
            $systemName = $block['system_name'];
            $dbSetting = $dbSettings[$systemName] ?? null;

            $blocksWithSettings[] = [
                'system_name' => $block['system_name'],
                'name' => $block['name'],
                'description' => $block['description'],
                'icon' => $block['icon'],
                'category' => $block['category'],
                'version' => $block['version'],
                'author' => $block['author'],
                'can_use_in_posts' => $dbSetting ? (bool)$dbSetting['enable_in_posts'] : $block['can_use_in_posts'],
                'can_use_in_pages' => $dbSetting ? (bool)$dbSetting['enable_in_pages'] : $block['can_use_in_pages']
            ];
        }
        
        return $blocksWithSettings;
    }
    
    /**
    * Группирует блоки по категориям для удобного отображения
    * @param array $blocks Массив блоков с настройками
    * @return array Блоки, сгруппированные по категориям
    */
    private function groupBlocksByCategory($blocks) {
        $grouped = [];
        
        foreach ($blocks as $block) {
            $category = $block['category'] ?? 'general';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $block;
        }
        
        return $grouped;
    }
}