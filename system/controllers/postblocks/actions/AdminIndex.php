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
            $presetCounts = $this->postBlockModel->getPresetCounts();
            $usageCounts = $this->postBlockModel->getUsageCounts();
            
            $blocks = $this->mergeBlocksWithSettings($allBlocks, $dbSettings, $presetCounts, $usageCounts);
            
            $postBlocksByCategory = $this->groupBlocksByCategory($blocks);
            
            $this->render('admin/post_blocks/index', [
                'postBlocksByCategory' => $postBlocksByCategory,
                'postBlocks' => $blocks,
                'blocksStats' => $this->buildStatsSummary($blocks),
                'pageTitle' => LANG_ACTION_POSTBLOCKS_ADMININDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_POSTBLOCKS_ADMININDEX_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
    * Объединяет информацию о блоках с настройками из базы данных,
    * количеством пресетов и статистикой использования
    * @param array $allBlocks Массив всех блоков из менеджера
    * @param array $dbSettings Массив настроек из БД
    * @param array $presetCounts Количество пресетов по системным именам
    * @param array $usageCounts Статистика использования по системным именам
    * @return array Массив блоков с объединенными настройками
    */
    private function mergeBlocksWithSettings($allBlocks, $dbSettings, $presetCounts, $usageCounts) {
        $blocksWithSettings = [];
        
        foreach ($allBlocks as $block) {
            $systemName = $block['system_name'];
            $dbSetting = $dbSettings[$systemName] ?? null;
            $usage = $usageCounts[$systemName] ?? [];

            $customTemplate = $dbSetting['template'] ?? '';

            $blocksWithSettings[] = [
                'system_name' => $systemName,
                'name' => $block['name'],
                'description' => $block['description'],
                'icon' => $block['icon'],
                'category' => $block['category'],
                'version' => $block['version'],
                'author' => $block['author'],
                'can_use_in_posts' => $dbSetting ? (bool)$dbSetting['enable_in_posts'] : $block['can_use_in_posts'],
                'can_use_in_pages' => $dbSetting ? (bool)$dbSetting['enable_in_pages'] : $block['can_use_in_pages'],
                'has_custom_template' => trim((string)$customTemplate) !== '',
                'presets_count' => $presetCounts[$systemName] ?? 0,
                'used_in_posts' => $usage['posts'] ?? 0,
                'used_in_pages' => $usage['pages'] ?? 0
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

    /**
    * Строит сводную статистику по всем блокам для шапки списка
    * @param array $blocks Массив блоков с настройками
    * @return array
    */
    private function buildStatsSummary($blocks) {
        $enabledPosts = 0;
        $enabledPages = 0;
        $presets = 0;
        $customTemplates = 0;

        foreach ($blocks as $block) {
            $enabledPosts += $block['can_use_in_posts'] ? 1 : 0;
            $enabledPages += $block['can_use_in_pages'] ? 1 : 0;
            $presets += (int)$block['presets_count'];
            $customTemplates += $block['has_custom_template'] ? 1 : 0;
        }

        return [
            'total' => count($blocks),
            'enabled_posts' => $enabledPosts,
            'enabled_pages' => $enabledPages,
            'presets' => $presets,
            'custom_templates' => $customTemplates
        ];
    }
}
