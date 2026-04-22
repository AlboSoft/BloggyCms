<?php

namespace pages\actions;

/**
* Действие создания новой страницы в административной панели
* @package pages\actions
*/
class AdminCreate extends PageAction {
    
    /**
    * Метод выполнения создания страницы
    * @return void
    */
    public function execute() {
        
        $this->addBreadcrumb(LANG_ACTION_PAGES_ADMINCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_PAGES_ADMINCREATE_BREADCRUMB_PAGES, ADMIN_URL . '/pages');
        $this->addBreadcrumb(LANG_ACTION_PAGES_ADMINCREATE_BREADCRUMB_CREATE);
        
        $this->postBlockManager->loadAllPostBlockAssets();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        } else {
            $this->renderCreateForm();
        }
    }
    
    /**
    * Обрабатывает POST-запрос на создание страницы
    * @return void
    */
    private function handlePostRequest() {
        try {

            $this->validateRequiredFields();
            
            $data = $this->preparePageData();
            
            $pageId = $this->pageModel->create($data);
            
            $this->processPageBlocksFromPost($pageId);
            
            $this->processCustomFields($pageId);
            
            $this->handleSuccess();
            
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
    * Проверяет обязательные поля формы
    * @throws \Exception Если обязательные поля не заполнены
    * @return void
    */
    private function validateRequiredFields() {
        if (empty($_POST['title'])) {
            throw new \Exception(LANG_ACTION_PAGES_ADMINCREATE_TITLE_REQUIRED);
        }
    }
    
    /**
    * Подготавливает данные страницы из POST-запроса
    * @return array Массив с данными страницы
    */
    private function preparePageData() {
        $data = [
            'title' => $_POST['title'],
            'status' => $_POST['status'] ?? 'draft'
        ];

        if (!empty($_POST['slug'])) {
            $data['slug'] = $this->sanitizeSlug($_POST['slug']);
        }
        
        return $data;
    }

    /**
    * Санитизирует slug: удаляет спецсимволы, заменяет пробелы на дефисы
    * @param string $slug Исходный slug
    * @return string Очищенный slug
    */
    private function sanitizeSlug($slug) {
        $slug = mb_strtolower(trim($slug), 'UTF-8');
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
    
    /**
    * Обрабатывает и сохраняет блоки страницы из POST-запроса
    * @param int $pageId ID созданной страницы
    * @throws \Exception При неверном формате данных блоков
    * @return void
    */
    private function processPageBlocksFromPost($pageId) {
        if (empty($_POST['post_blocks'])) {
            return;
        }
        
        $blocksData = json_decode($_POST['post_blocks'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($blocksData)) {
            throw new \Exception(LANG_ACTION_PAGES_ADMINCREATE_INVALID_BLOCKS_DATA);
        }
        
        $this->processPageBlocks($pageId, $blocksData);
    }
    
    /**
    * Обрабатывает и сохраняет пользовательские поля для страницы
    * @param int $pageId ID созданной страницы
    * @return void
    */
    private function processCustomFields($pageId) {
        $fieldModel = new \FieldModel($this->db);
        $fieldManager = new \FieldManager($this->db);
        
        $customFields = $fieldModel->getActiveByEntityType('page');
        
        foreach ($customFields as $field) {
            $this->processSingleCustomField($field, $pageId, $fieldModel, $fieldManager);
        }
    }
    
    /**
    * Обрабатывает одно пользовательское поле
    * @param array $field Данные поля
    * @param int $pageId ID страницы
    * @param \FieldModel $fieldModel Модель полей
    * @param \FieldManager $fieldManager Менеджер полей
    * @return void
    */
    private function processSingleCustomField($field, $pageId, $fieldModel, $fieldManager) {
        try {
            $value = $fieldManager->processFieldValue($field, $_POST, $_FILES);
            
            if ($value !== null) {
                $config = is_array($field['config']) 
                    ? $field['config'] 
                    : json_decode($field['config'] ?? '{}', true);
                
                $fieldModel->saveFieldValue(
                    $field['id'], 
                    'page', 
                    $pageId, 
                    $value,
                    $field['type'],
                    $config
                );
            }
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_PAGES_ADMINCREATE_FIELD_ERROR, $field['name'], $e->getMessage()));
        }
    }
    
    /**
    * Обрабатывает успешное создание страницы
    * @return void
    */
    private function handleSuccess() {
        \Notification::success(LANG_ACTION_PAGES_ADMINCREATE_SUCCESS);
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
    * Обрабатывает ошибку при создании страницы
    * @param \Exception $e Исключение с сообщением об ошибке
    * @return void
    */
    private function handleError($e) {
        \Notification::error(LANG_ACTION_PAGES_ADMINCREATE_ERROR . $e->getMessage());
        
        $preparedBlocks = $this->prepareBlocksFromPost();
        
        $this->render('admin/pages/create', [
            'data' => $_POST,
            'preparedBlocks' => $preparedBlocks,
            'postBlockManager' => $this->postBlockManager,
            'pageTitle' => LANG_ACTION_PAGES_ADMINCREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Подготавливает данные блоков из POST-запроса для повторного отображения
    * @return array Массив подготовленных блоков
    */
    private function prepareBlocksFromPost() {
        $preparedBlocks = [];
        
        if (!empty($_POST['post_blocks'])) {
            $blocksData = json_decode($_POST['post_blocks'], true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($blocksData)) {
                foreach ($blocksData as $index => $block) {
                    $preparedBlocks[] = [
                        'id' => $block['id'] ?? 'block_' . $index,
                        'type' => $block['type'] ?? '',
                        'content' => $block['content'] ?? [],
                        'settings' => $block['settings'] ?? [],
                        'order' => (int)($block['order'] ?? $index)
                    ];
                }
            }
        }
        
        return $preparedBlocks;
    }
    
    /**
    * Отображает пустую форму создания страницы 
    * @return void
    */
    private function renderCreateForm() {
        $this->render('admin/pages/create', [
            'postBlockManager' => $this->postBlockManager,
            'pageTitle' => LANG_ACTION_PAGES_ADMINCREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ситуацию с отсутствием прав доступа
    * @return void
    */
    private function handleAccessDenied() {
        \Notification::error(LANG_ACTION_PAGES_ADMINCREATE_ACCESS_DENIED);
        $this->redirect(ADMIN_URL . '/login');
    }
}