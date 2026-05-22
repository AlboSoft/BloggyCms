<?php

namespace pages\actions;

/**
* Действие редактирования страницы в административной панели
* @package pages\actions
*/
class AdminEdit extends PageAction {
    
    protected $id;
    
    /**
    * Устанавливает ID страницы для редактирования
    * @param int|null $id ID страницы
    * @return void
    */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
    * Метод выполнения редактирования страницы
    * @return void
    */
    public function execute() {
        
        if (!$this->validatePageId()) {
            return;
        }
        
        try {
            $page = $this->loadPage();
            
            $this->addBreadcrumb(LANG_ACTION_PAGES_ADMINEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_PAGES_ADMINEDIT_BREADCRUMB_PAGES, ADMIN_URL . '/pages');
            $this->addBreadcrumb(LANG_ACTION_PAGES_ADMINEDIT_BREADCRUMB_EDIT . html($page['title']));
            
            $this->postBlockManager->loadAllPostBlockAssets();
            
            $preparedBlocks = $this->loadAndPrepareBlocks();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($page);
                return;
            }
            
            $this->renderEditForm($page, $preparedBlocks);
            
        } catch (\Exception $e) {
            $this->handleLoadError($e);
        }
    }
    
    /**
    * Проверяет наличие ID страницы для редактирования
    * @return bool true если ID указан, false в противном случае
    */
    private function validatePageId() {
        if (!$this->id) {
            \Notification::error(LANG_ACTION_PAGES_ADMINEDIT_ID_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/pages');
            return false;
        }
        return true;
    }
    
    /**
    * Загружает данные страницы из базы данных
    * @return array Данные страницы
    * @throws \Exception Если страница не найдена
    */
    private function loadPage() {
        $page = $this->pageModel->getById($this->id);
        
        if (!$page) {
            throw new \Exception(LANG_ACTION_PAGES_ADMINEDIT_PAGE_NOT_FOUND);
        }
        
        return $page;
    }
    
    /**
    * Загружает и подготавливает блоки страницы для отображения в форме
    * @return array Массив подготовленных блоков
    */
    private function loadAndPrepareBlocks() {
        $blocks = $this->postBlockModel->getByPage($this->id);
        $preparedBlocks = [];
        
        foreach ($blocks as $block) {
            $preparedBlocks[] = $this->prepareBlockData($block);
        }
        
        return $preparedBlocks;
    }
    
    /**
    * Подготавливает данные одного блока для отображения в форме
    * @param array $block Данные блока из БД
    * @return array Подготовленные данные блока
    */
    private function prepareBlockData($block) {
        $content = $this->normalizeBlockContent($block['content'], $block['type']);
        
        $settings = $this->normalizeBlockSettings($block['settings']);
        
        return [
            'id' => 'block_' . $block['id'],
            'type' => $block['type'],
            'content' => $content,
            'settings' => $settings,
            'order' => (int)($block['order'] ?? 0)
        ];
    }
    
    /**
    * Нормализует контент блока для использования в форме 
     * @param mixed $content Контент блока
     * @param string $blockType Тип блока
     * @return array Нормализованный контент
     */
    private function normalizeBlockContent($content, $blockType) {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $content = $decoded;
            } else {
                $content = ['text' => $content];
            }
        }
        
        if (!is_array($content)) {
            $content = ['text' => (string)$content];
        }
        
        if ($blockType === 'ListBlock' && isset($content['items']) && !is_array($content['items'])) {
            $content['items'] = [['text' => (string)$content['items']]];
        }
        
        return $content;
    }
    
    /**
    * Нормализует настройки блока для использования в форме
    * @param mixed $settings Настройки блока
    * @return array Нормализованные настройки
    */
    private function normalizeBlockSettings($settings) {
        if (is_string($settings)) {
            $decoded = json_decode($settings, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }
        
        return is_array($settings) ? $settings : [];
    }
    
    /**
    * Обрабатывает POST-запрос на сохранение изменений страницы 
    * @param array $page Данные страницы
    * @return void
    */
    private function handlePostRequest($page) {
        try {
            $this->validateRequiredFields();
            $this->updatePageData();
            $this->updatePageBlocks();
            $this->updateCustomFields();
            $this->handleUpdateSuccess();
        } catch (\Exception $e) {
            $this->handleUpdateError($e);
        }
    }
    
    /**
    * Проверяет обязательные поля формы
    * @throws \Exception Если обязательные поля не заполнены
    */
    private function validateRequiredFields() {
        if (empty($_POST['title'])) {
            throw new \Exception(LANG_ACTION_PAGES_ADMINEDIT_TITLE_REQUIRED);
        }
    }
    
    /**
    * Обновляет основные данные страницы
    */
    private function updatePageData() {
        $data = [
            'title' => $_POST['title'],
            'status' => $_POST['status'] ?? 'draft',
            'parent_id' => isset($_POST['parent_id']) && $_POST['parent_id'] !== '' 
                ? (int)$_POST['parent_id'] 
                : null
        ];

        if (!empty($_POST['slug'])) {
            $data['slug'] = $this->sanitizeSlug($_POST['slug']);
        }
        
        $this->pageModel->update($this->id, $data);
    }

    /**
    * Санитизирует slug
    */
    private function sanitizeSlug($slug) {
        $slug = mb_strtolower(trim($slug), 'UTF-8');
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
    
    /**
    * Обновляет блоки страницы
    * @throws \Exception При неверном формате данных блоков
    */
    private function updatePageBlocks() {
        if (!empty($_POST['post_blocks'])) {
            $blocksData = json_decode($_POST['post_blocks'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($blocksData)) {
                throw new \Exception(LANG_ACTION_PAGES_ADMINEDIT_INVALID_BLOCKS_DATA);
            }
            
            $this->processPageBlocks($this->id, $blocksData);
        } else {
            $this->postBlockModel->deleteByPage($this->id);
        }
    }
    
    /**
    * Обновляет пользовательские поля страницы
    */
    private function updateCustomFields() {
        $fieldModel = new \FieldModel($this->db);
        $fieldManager = new \FieldManager($this->db);
        $customFields = $fieldModel->getActiveByEntityType('page');
        $currentValues = $this->getCurrentFieldValues($fieldModel, $customFields);
        
        foreach ($customFields as $field) {
            $this->processCustomField($field, $fieldModel, $fieldManager, $currentValues);
        }
    }
    
    /**
    * Получает текущие значения полей страницы 
    * @param \FieldModel $fieldModel Модель полей
    * @param array $customFields Массив полей
    * @return array Массив текущих значений
    */
    private function getCurrentFieldValues($fieldModel, $customFields) {
        $currentValues = [];
        foreach ($customFields as $field) {
            $currentValues[$field['system_name']] = $fieldModel->getFieldValue('page', $this->id, $field['system_name']);
        }
        return $currentValues;
    }
    
    /**
    * Обрабатывает одно пользовательское поле
    * @param array $field Данные поля
    * @param \FieldModel $fieldModel Модель полей
    * @param \FieldManager $fieldManager Менеджер полей
    * @param array $currentValues Текущие значения
    */
    private function processCustomField($field, $fieldModel, $fieldManager, $currentValues) {
        try {
            $value = $fieldManager->processFieldValue($field, $_POST, $_FILES, $currentValues);
            
            $config = is_array($field['config']) 
                ? $field['config'] 
                : json_decode($field['config'] ?? '{}', true);
            
            $fieldModel->saveFieldValue(
                'page',
                $this->id,
                $field['system_name'],
                $value
            );
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_PAGES_ADMINEDIT_FIELD_ERROR, $field['name'], $e->getMessage()));
        }
    }
    
    /**
    * Обрабатывает успешное обновление страницы
    */
    private function handleUpdateSuccess() {
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(true, LANG_ACTION_PAGES_ADMINEDIT_SUCCESS_JSON, [
                'redirect' => ADMIN_URL . '/pages'
            ]);
            exit;
        }
        
        \Notification::success(LANG_ACTION_PAGES_ADMINEDIT_SUCCESS);
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
    * Обрабатывает ошибку при обновлении страницы
    * @param \Exception $e Исключение
    */
    private function handleUpdateError($e) {
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(false, $e->getMessage());
            exit;
        }
        
        \Notification::error(LANG_ACTION_PAGES_ADMINEDIT_UPDATE_ERROR . $e->getMessage());
    }
    
    /**
    * Отправляет JSON-ответ для AJAX-запросов
    * @param bool $success Флаг успеха
    * @param string $message Сообщение
    * @param array $extra Дополнительные данные
    */
    private function sendJsonResponse($success, $message, $extra = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(
            ['success' => $success, 'message' => $message],
            $extra
        ));
    }
    
    /**
    * Отображает форму редактирования страницы
    * @param array $page Данные страницы
    * @param array $preparedBlocks Подготовленные блоки
    */
    private function renderEditForm($page, $preparedBlocks) {

        $availableParents = $this->pageModel->getAvailableParents($page['id']);

        $this->render('admin/pages/edit', [
            'page' => $page,
            'preparedBlocks' => $preparedBlocks,
            'postBlockManager' => $this->postBlockManager,
            'pageTitle' => LANG_ACTION_PAGES_ADMINEDIT_PAGE_TITLE,
            'availableParents' => $availableParents
        ]);
    }
    
    /**
    * Обрабатывает ошибку при загрузке страницы
    * @param \Exception $e Исключение
    */
    private function handleLoadError($e) {
        \Notification::error(LANG_ACTION_PAGES_ADMINEDIT_LOAD_ERROR . $e->getMessage());
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
    * Обрабатывает ситуацию с отсутствием прав доступа
    */
    private function handleAccessDenied() {
        \Notification::error(LANG_ACTION_PAGES_ADMINEDIT_ACCESS_DENIED);
        $this->redirect(ADMIN_URL . '/login');
    }
}