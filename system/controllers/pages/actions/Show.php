<?php

namespace pages\actions;

/**
* Действие отображения страницы на фронтенде
* @package pages\actions
*/
class Show extends PageAction {
    
    /**
    * Метод выполнения отображения страницы
    * @return void
    */
    public function execute() {
        $slug = $this->params['slug'] ?? null;

        if (!$slug) {
            \Notification::error(LANG_ACTION_PAGES_SHOW_SLUG_NOT_SPECIFIED);
            $this->redirect(BASE_URL);
            return;
        }
        
        try {
            $page = $this->pageModel->getBySlug($slug);
            
            if (!$page) {
                \Notification::error(LANG_ACTION_PAGES_SHOW_PAGE_NOT_FOUND);
                $this->redirect(BASE_URL);
                return;
            }
            
            $this->addBreadcrumb(LANG_ACTION_PAGES_SHOW_BREADCRUMB_HOME, BASE_URL);
            
            if (!empty($page['parent_id'])) {
                $this->addParentPageBreadcrumbs($page['parent_id']);
            }
            
            $this->addBreadcrumb($page['title']);
            $this->setPageTitle($page['title']);
            
            $processedBlocks = $this->preparePageBlocks($page['id']);
            
            $customFields = $this->prepareCustomFields('page', $page['id']);
            
            $this->render('front/pages/page', [
                'page' => $page,
                'fieldValues' => $customFields,
                'blocks' => $processedBlocks
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_PAGES_SHOW_LOAD_ERROR);
            $this->redirect(BASE_URL);
        }
    }
    
    /**
    * Подготавливает блоки страницы для отображения 
    * @param int $pageId ID страницы
    * @return array Массив обработанных блоков
    */
    private function preparePageBlocks($pageId) {
        $blocks = $this->postBlockModel->getByPage($pageId);
        $this->loadBlockAssets($blocks);
        
        $processedBlocks = [];
        foreach ($blocks as $block) {
            $processedBlocks[] = $this->processSingleBlock($block);
        }
        
        return $processedBlocks;
    }
    
    /**
    * Загружает фронтенд-ассеты (CSS, JS) для всех блоков на странице
    * @param array $blocks Массив блоков страницы
    * @return void
    */
    private function loadBlockAssets($blocks) {
        $blocksData = [];
        foreach ($blocks as $block) {
            $blocksData[] = [
                'type' => $block['type']
            ];
        }
        
        $this->postBlockManager->loadFrontendAssetsForBlocks($blocksData);
    }
    
    /**
    * Обрабатывает отдельный блок страницы
    * @param array $block Данные блока из базы данных
    * @return array Обработанный блок готовый к отображению
    */
    private function processSingleBlock($block) {
        
        $content = $this->decodeJsonIfNeeded($block['content']);
        $settings = $this->decodeJsonIfNeeded($block['settings'], true);
        $dbSettings = $this->postBlockModel->getBlockSettings($block['type']);
        $blockInstance = $this->postBlockManager->getBlockInstance($block['type']);
        $defaultSettings = $blockInstance ? $blockInstance->getDefaultSettings() : [];
        $mergedSettings = array_merge($defaultSettings, $dbSettings, $settings);
        
        $processedContent = $this->postBlockManager->processPostBlockContent(
            $content, 
            $block['type'], 
            $mergedSettings
        );
        
        return [
            'type' => $block['type'],
            'content' => $processedContent,
            'settings' => $mergedSettings
        ];
    }
    
    /**
    * Декодирует строку из JSON, если это необходимо
    * @param mixed $data Данные для декодирования
    * @param bool $defaultArray Возвращать массив по умолчанию
    * @return mixed Декодированные данные
    */
    private function decodeJsonIfNeeded($data, $defaultArray = false) {

        if (is_string($data)) {
            
            $cleaned = stripslashes($data);
            $decoded = json_decode($cleaned, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            } else {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }
        
        return $defaultArray ? [] : $data;
    }
    
    /**
    * Подготавливает пользовательские поля для сущности 
    * @param string $entityType Тип сущности (например, 'page')
    * @param int $entityId ID сущности
    * @return array Массив значений полей с метаданными
    */
    private function prepareCustomFields($entityType, $entityId) {
        $fieldModel = new \FieldModel($this->db);
        $customFields = $fieldModel->getActiveByEntityType($entityType);
        
        $fieldValues = [];
        foreach ($customFields as $field) {
            $fieldValues[$field['system_name']] = [
                'value' => $fieldModel->getFieldValue($entityType, $entityId, $field['system_name']),
                'field' => $field
            ];
        }
        
        return $fieldValues;
    }
    
    /**
    * Рекурсивно добавляет хлебные крошки для родительских страниц
    * @param int $parentId ID родительской страницы
    * @return void
    */
    private function addParentPageBreadcrumbs($parentId) {
        $parentPage = $this->pageModel->getById($parentId);
        
        if ($parentPage) {
            if (!empty($parentPage['parent_id'])) {
                $this->addParentPageBreadcrumbs($parentPage['parent_id']);
            }
            
            $this->addBreadcrumb(
                $parentPage['title'],
                BASE_URL . '/page/' . $parentPage['slug']
            );
        }
    }
}