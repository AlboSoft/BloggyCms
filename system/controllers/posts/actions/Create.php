<?php

namespace posts\actions;

/**
* Действие создания нового поста в административной панели
* @package posts\actions
*/
class Create extends PostAction {
    
    /**
    * Метод выполнения создания поста
    * @return void
    */
    public function execute() {

        $this->addBreadcrumb(LANG_ACTION_POSTS_CREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_POSTS_CREATE_BREADCRUMB_POSTS, ADMIN_URL . '/posts');
        $this->addBreadcrumb(LANG_ACTION_POSTS_CREATE_BREADCRUMB_CREATE);
        
        try {
            $categories = $this->categoryModel->getAll();
            $tags = $this->tagModel->getAll();
            $this->postBlockManager->loadAllPostBlockAssets();
            $maxTags = \SettingsHelper::get('controller_tags', 'max_tags_per_post', 10);
            $hasCategories = !empty($categories);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($categories, $tags, $maxTags, $hasCategories);
                return;
            }

            $this->renderCreateForm($categories, $tags, $maxTags, $hasCategories);

        } catch (\Exception $e) {
            $this->handleError($e, $categories, $tags, $maxTags, $hasCategories ?? true);
        }
    }

    /**
    * Обрабатывает POST-запрос на создание поста
    * @param array $categories Список категорий
    * @param array $tags Список тегов
    * @param int $maxTags Максимальное количество тегов
    * @param bool $hasCategories Флаг наличия категорий
    * @throws \Exception При ошибках валидации
    * @return void
    */
    private function handlePostRequest($categories, $tags, $maxTags, $hasCategories) {
        $this->validateRequiredFields($maxTags, $hasCategories);
        $postData = $this->preparePostData();
        $postId = $this->postModel->create($postData);
        $this->processPostBlocks($postId);
        $this->processPostTags($postId);
        $this->processCustomFields($postId);
        
        \Notification::success(LANG_ACTION_POSTS_CREATE_SUCCESS);
        $this->redirect(ADMIN_URL . '/posts');
    }

    /**
    * Валидирует обязательные поля формы
    * @param int $maxTags Максимальное количество тегов
    * @param bool $hasCategories Флаг наличия категорий
    * @throws \Exception При ошибках валидации
    * @return void
    */
    private function validateRequiredFields($maxTags, $hasCategories) {
        if (empty($_POST['title'])) {
            throw new \Exception(LANG_ACTION_POSTS_CREATE_ERROR_TITLE_REQUIRED);
        }
        
        if ($hasCategories && empty($_POST['category_id'])) {
            throw new \Exception(LANG_ACTION_POSTS_CREATE_ERROR_CATEGORY_REQUIRED);
        }

        if (!empty($_POST['tags_json'])) {
            $tags = json_decode($_POST['tags_json'], true);
            if (is_array($tags)) {
                if (count($tags) > $maxTags) {
                    throw new \Exception(sprintf(LANG_ACTION_POSTS_CREATE_ERROR_MAX_TAGS, $maxTags, count($tags)));
                }
            }
        }

        if (isset($_POST['change_publish_date']) && $_POST['change_publish_date'] && !empty($_POST['publish_date'])) {
            $publishDate = $_POST['publish_date'];
            $selectedDate = new \DateTime($publishDate);
            $currentDate = new \DateTime();
            
            if ($selectedDate > $currentDate) {
                throw new \Exception(LANG_ACTION_POSTS_CREATE_ERROR_FUTURE_DATE);
            }
        }
    }

    /**
    * Подготавливает данные поста из POST-запроса 
    * @return array Массив с данными поста
    * @throws \Exception При ошибке загрузки изображения
    */
    private function preparePostData() {
        $categoryId = null;
        if (isset($_POST['category_id']) && $_POST['category_id'] !== '' && $_POST['category_id'] !== 'NULL') {
            $categoryId = $_POST['category_id'];
        }
        
        $postData = [
            'title' => $_POST['title'],
            'short_description' => $_POST['short_description'] ?? null,
            'slug' => $this->postModel->createSlug($_POST['title']),
            'category_id' => $categoryId,
            'user_id' => $_SESSION['user_id'],
            'status' => $_POST['status'] ?? 'draft',
            'meta_description' => $_POST['meta_description'] ?? null,
            'seo_title' => $_POST['seo_title'] ?? null,
            'password_protected' => isset($_POST['password_protected']) ? 1 : 0,
            'password' => $_POST['password'] ?? null,
            'show_to_groups' => !empty($_POST['show_to_groups']) ? json_encode($_POST['show_to_groups']) : null,
            'hide_from_groups' => !empty($_POST['hide_from_groups']) ? json_encode($_POST['hide_from_groups']) : null
        ];

        if (isset($_POST['change_publish_date']) && $_POST['change_publish_date'] && !empty($_POST['publish_date'])) {
            $postData['created_at'] = $_POST['publish_date'];
        }

        $postData['featured_image'] = $this->processFeaturedImage();

        return $postData;
    }

    /**
    * Обрабатывает загрузку главного изображения
    * @return string|null Путь к изображению или null
    * @throws \Exception При ошибке загрузки
    */
    private function processFeaturedImage() {
        if (!empty($_POST['uploaded_image_path'])) {
            return $_POST['uploaded_image_path'];
        }
        
        if (!empty($_FILES['featured_image']['tmp_name'])) {
            $uploadDir = UPLOADS_PATH . '/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['featured_image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetPath)) {
                throw new \Exception(LANG_ACTION_POSTS_CREATE_ERROR_IMAGE_UPLOAD);
            }
            return $fileName;
        }
        
        return null;
    }

    /**
    * Обрабатывает и сохраняет блоки поста
    * @param int $postId ID созданного поста
    * @param array $blocksData Данные блоков (передаются из родительского вызова)
    * @return void
    */
    protected function processPostBlocks($postId, $blocksData = []) {
        if (empty($blocksData) && !empty($_POST['post_blocks'])) {
            $decodedData = json_decode($_POST['post_blocks'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedData)) {
                $blocksData = $decodedData;
            }
        }
        
        if (!empty($blocksData)) {
            parent::processPostBlocks($postId, $blocksData);
        }
    }

    /**
    * Обрабатывает и сохраняет теги поста 
    * @param int $postId ID созданного поста
    * @return void
    */
    private function processPostTags($postId) {
        if (!empty($_POST['tags_json'])) {
            $tags = json_decode($_POST['tags_json'], true);
            if (is_array($tags)) {
                foreach ($tags as $tagId) {
                    $this->db->query(
                        "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)",
                        [$postId, $tagId]
                    );
                }
            }
        }
    }

    /**
    * Обрабатывает и сохраняет пользовательские поля 
    * @param int $postId ID созданного поста
    * @throws \Exception При ошибках валидации
    * @return void
    */
    private function processCustomFields($postId) {
        $fieldModel = new \FieldModel($this->db);
        $fieldManager = new \FieldManager($this->db);
        $customFields = $fieldModel->getActiveByEntityType('post');

        $validationErrors = $this->validateCustomFields($customFields, $fieldModel, $fieldManager);
        if (!empty($validationErrors)) {
            throw new \Exception(implode('<br>', $validationErrors));
        }

        foreach ($customFields as $field) {
            $this->saveCustomField($field, $postId, $fieldModel, $fieldManager);
        }
    }

    /**
    * Валидирует пользовательские поля
    * @param array $customFields Массив полей
    * @param \FieldModel $fieldModel Модель полей
    * @param \FieldManager $fieldManager Менеджер полей
    * @return array Массив ошибок валидации
    */
    private function validateCustomFields($customFields, $fieldModel, $fieldManager) {
        $errors = [];
        
        foreach ($customFields as $field) {
            $currentValue = $fieldModel->getFieldValue('post', 0, $field['system_name']);
            $validationResult = $fieldManager->validateFieldValue($field, $currentValue, $_POST, $_FILES);
            
            if (!$validationResult['is_valid']) {
                $errors[] = $validationResult['message'];
            }
        }
        
        return $errors;
    }

    /**
    * Сохраняет одно пользовательское поле
    * @param array $field Данные поля
    * @param int $postId ID поста
    * @param \FieldModel $fieldModel Модель полей
    * @param \FieldManager $fieldManager Менеджер полей
    * @return void
    */
    private function saveCustomField($field, $postId, $fieldModel, $fieldManager) {
        try {
            $value = $fieldManager->processFieldValue($field, $_POST, $_FILES);
            
            if ($value !== null) {
                $config = is_array($field['config']) ? $field['config'] : json_decode($field['config'] ?? '{}', true);
                $fieldModel->saveFieldValue(
                    $field['id'], 
                    'post', 
                    $postId, 
                    $value,
                    $field['type'],
                    $config
                );
            }
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_POSTS_CREATE_ERROR_FIELD_PROCESS, $field['name'], $e->getMessage()));
        }
    }

    /**
    * Отображает форму создания поста
    * @param array $categories Список категорий
    * @param array $tags Список тегов
    * @param int $maxTags Максимальное количество тегов
    * @param bool $hasCategories Флаг наличия категорий
    * @return void
    */
    private function renderCreateForm($categories, $tags, $maxTags, $hasCategories) {
        $this->render('admin/posts/create', [
            'categories' => $categories,
            'tags' => $tags,
            'postBlockManager' => $this->postBlockManager,
            'maxTags' => $maxTags,
            'hasCategories' => $hasCategories,
            'pageTitle' => LANG_ACTION_POSTS_CREATE_PAGE_TITLE
        ]);
    }

    /**
    * Обрабатывает ошибку при создании поста
    * @param \Exception $e Исключение
    * @param array $categories Список категорий
    * @param array $tags Список тегов
    * @param int $maxTags Максимальное количество тегов
    * @param bool $hasCategories Флаг наличия категорий
    * @return void
    */
    private function handleError($e, $categories, $tags, $maxTags, $hasCategories) {
        \Notification::error($e->getMessage());
        
        $this->render('admin/posts/create', [
            'categories' => $categories,
            'tags' => $tags,
            'post' => $_POST,
            'postBlockManager' => $this->postBlockManager,
            'maxTags' => $maxTags,
            'hasCategories' => $hasCategories
        ]);
    }
}