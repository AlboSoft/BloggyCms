<?php

namespace posts\actions;

/**
* Действие редактирования поста в административной панели 
* @package posts\actions
*/
class Edit extends PostAction {
    
    /**
    * Метод выполнения редактирования поста
    * @return void
    * @throws \Exception Если ID не указан
    */
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            throw new \Exception(LANG_ACTION_POSTS_EDIT_NO_ID);
        }

        try {
            $post = $this->loadPost($id);
            
            $this->addBreadcrumb(LANG_ACTION_POSTS_EDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_POSTS_EDIT_BREADCRUMB_POSTS, ADMIN_URL . '/posts');
            $this->addBreadcrumb(sprintf(LANG_ACTION_POSTS_EDIT_BREADCRUMB_EDIT, $post['title']));
            
            $this->postBlockManager->loadAllPostBlockAssets();
            $categories = $this->categoryModel->getAll();
            $tags = $this->tagModel->getAll();
            $postTags = $this->tagModel->getForPost($id);
            $preparedBlocks = $this->loadPostBlocks($id);
            $hasCategories = !empty($categories);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id, $post, $categories, $tags, $postTags, $preparedBlocks, $hasCategories);
                return;
            }

            $this->renderEditForm($post, $categories, $tags, $postTags, $preparedBlocks, $hasCategories);

        } catch (\Exception $e) {
            $this->handleError($e, $post ?? null, $categories ?? [], $tags ?? [], $postTags ?? [], $preparedBlocks ?? [], $hasCategories ?? true);
        }
    }

    /**
    * Загружает пост по ID и проверяет его существование 
    * @param int $id ID поста
    * @return array Данные поста
    * @throws \Exception Если пост не найден
    */
    private function loadPost($id) {
        $post = $this->postModel->getById($id);
        if (!$post) {
            throw new \Exception(LANG_ACTION_POSTS_EDIT_POST_NOT_FOUND);
        }
        return $post;
    }

    /**
    * Загружает и подготавливает блоки поста для отображения в форме
    * @param int $postId ID поста
    * @return array Массив подготовленных блоков
    */
    private function loadPostBlocks($postId) {
        $blocks = $this->postBlockModel->getByPost($postId);
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
        $content = $block['content'];
        $settings = $block['settings'];
        
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            $content = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $content;
        }
        
        if (is_string($settings)) {
            $decodedSettings = json_decode($settings, true);
            $settings = (json_last_error() === JSON_ERROR_NONE) ? $decodedSettings : [];
        }
        
        return [
            'id' => 'block_' . $block['id'],
            'type' => $block['type'],
            'content' => $content,
            'settings' => $settings,
            'order' => (int)($block['order'] ?? 0)
        ];
    }

    /**
    * Обрабатывает POST-запрос на сохранение изменений поста
    * @param int $id ID поста
    * @param array $post Исходные данные поста
    * @param array $categories Список категорий
    * @param array $tags Список всех тегов
    * @param array $postTags Текущие теги поста
    * @param array $preparedBlocks Подготовленные блоки
    * @param bool $hasCategories Флаг наличия категорий
    * @return void
    */
    private function handlePostRequest($id, $post, $categories, $tags, $postTags, $preparedBlocks, $hasCategories) {
        $this->validateRequiredFields($hasCategories);
        $data = $this->preparePostData($id, $post, $hasCategories);
        $this->postModel->update($id, $data);
        $this->updatePostBlocks($id);
        $this->updatePostTags($id);
        $this->updateCustomFields($id);
        $this->handleUpdateSuccess();
    }

    /**
    * Валидирует обязательные поля формы 
    * @param bool $hasCategories Флаг наличия категорий
    * @throws \Exception При ошибках валидации
    * @return void
    */
    private function validateRequiredFields($hasCategories) {
        if (empty($_POST['title'])) {
            throw new \Exception(LANG_ACTION_POSTS_EDIT_ERROR_TITLE_REQUIRED);
        }
        
        if ($hasCategories && empty($_POST['category_id'])) {
            throw new \Exception(LANG_ACTION_POSTS_EDIT_ERROR_CATEGORY_REQUIRED);
        }

        if (!empty($_POST['tags_json'])) {
            $tagsData = json_decode($_POST['tags_json'], true);
            
            if (is_array($tagsData) && !empty($tagsData)) {
                $maxTags = \SettingsHelper::get('controller_tags', 'max_tags_per_post', 10);
                
                if (count($tagsData) > $maxTags) {
                    throw new \Exception(sprintf(LANG_ACTION_POSTS_EDIT_ERROR_MAX_TAGS, $maxTags, count($tagsData)));
                }
            }
        }

        if (isset($_POST['change_publish_date']) && $_POST['change_publish_date'] && !empty($_POST['publish_date'])) {
            $publishDate = $_POST['publish_date'];
            $selectedDate = new \DateTime($publishDate);
            $currentDate = new \DateTime();
            
            if ($selectedDate > $currentDate) {
                throw new \Exception(LANG_ACTION_POSTS_EDIT_ERROR_FUTURE_DATE);
            }
        }
    }

    /**
    * Подготавливает данные поста для обновления
    * @param int $id ID поста
    * @param array $post Текущие данные поста
    * @param bool $hasCategories Флаг наличия категорий
    * @return array Подготовленные данные
    */
    private function preparePostData($id, $post, $hasCategories) {
        $categoryId = null;
        if ($hasCategories && isset($_POST['category_id']) && $_POST['category_id'] !== '') {
            $categoryId = (int)$_POST['category_id'];
        }
        
        $data = [
            'title' => trim($_POST['title']),
            'short_description' => $_POST['short_description'] ?? null,
            'slug' => $this->postModel->createSlug($_POST['title'], $id),
            'category_id' => $categoryId,
            'status' => $_POST['status'] ?? 'draft',
            'meta_description' => $_POST['meta_description'] ?? null,
            'seo_title' => $_POST['seo_title'] ?? null,
            'password_protected' => isset($_POST['password_protected']) ? 1 : 0,
            'password' => $_POST['password'] ?? null,
            'show_to_groups' => !empty($_POST['show_to_groups']) ? json_encode($_POST['show_to_groups']) : null,
            'hide_from_groups' => !empty($_POST['hide_from_groups']) ? json_encode($_POST['hide_from_groups']) : null
        ];

        if (isset($_POST['change_publish_date']) && $_POST['change_publish_date'] && !empty($_POST['publish_date'])) {
            $data['created_at'] = $_POST['publish_date'];
        }

        $data['featured_image'] = $this->processFeaturedImage($post);

        return $data;
    }

    /**
    * Обрабатывает загрузку/обновление главного изображения 
    * @param array $post Текущие данные поста
    * @return string|null Путь к изображению или null
    */
    private function processFeaturedImage($post) {
        if (!empty($_POST['uploaded_image_path'])) {
            $this->deleteOldImage($post['featured_image']);
            return $_POST['uploaded_image_path'];
        }
        
        if (!empty($_FILES['featured_image']['tmp_name'])) {
            return $this->uploadNewImage($post['featured_image']);
        }
        
        if (isset($_POST['remove_featured_image']) && $_POST['remove_featured_image'] == '1') {
            $this->deleteOldImage($post['featured_image']);
            return null;
        }
        
        return $post['featured_image'] ?? null;
    }

    /**
    * Загружает новое изображение на сервер
    * @param string|null $oldImage Имя старого изображения для удаления
    * @return string|null Имя загруженного файла или null
    */
    private function uploadNewImage($oldImage) {
        $uploadDir = UPLOADS_PATH . '/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['featured_image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetPath)) {
            $this->deleteOldImage($oldImage);
            return $fileName;
        }
        
        return null;
    }

    /**
    * Удаляет старое изображение с сервера
    * @param string|null $imageName Имя файла для удаления
    * @return void
    */
    private function deleteOldImage($imageName) {
        if (!empty($imageName)) {
            $oldImagePath = UPLOADS_PATH . '/images/' . $imageName;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }

    /**
    * Обновляет блоки поста
    * @param int $postId ID поста
    * @return void
    */
    private function updatePostBlocks($postId) {
        if (!empty($_POST['post_blocks'])) {
            $blocksData = json_decode($_POST['post_blocks'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($blocksData)) {
                $this->processPostBlocks($postId, $blocksData);
            }
        }
    }

    /**
    * Обновляет теги поста 
    * @param int $postId ID поста
    * @return void
    */
    private function updatePostTags($postId) {
        $this->db->query("DELETE FROM post_tags WHERE post_id = ?", [$postId]);
        
        if (!empty($_POST['tags_json'])) {
            $tagsData = json_decode($_POST['tags_json'], true);
            
            if (is_array($tagsData) && !empty($tagsData)) {
                foreach ($tagsData as $tag) {
                    $tagId = is_array($tag) ? ($tag['id'] ?? $tag) : $tag;
                    
                    if (!empty($tagId)) {
                        $this->db->query(
                            "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)",
                            [$postId, (int)$tagId]
                        );
                    }
                }
            }
        }
    }

    /**
    * Обновляет пользовательские поля поста 
    * @param int $postId ID поста
    * @throws \Exception При ошибках валидации
    * @return void
    */
    private function updateCustomFields($postId) {
        $fieldModel = new \FieldModel($this->db);
        $fieldManager = new \FieldManager($this->db);
        $customFields = $fieldModel->getActiveByEntityType('post');
        $currentValues = $this->getCurrentFieldValues($customFields, $postId, $fieldModel);

        $validationErrors = $this->validateCustomFields($customFields, $currentValues, $fieldManager);
        if (!empty($validationErrors)) {
            throw new \Exception(implode('<br>', $validationErrors));
        }

        foreach ($customFields as $field) {
            $this->saveCustomField($field, $postId, $currentValues, $fieldModel, $fieldManager);
        }
    }

    /**
    * Получает текущие значения пользовательских полей
    * @param array $customFields Массив полей
    * @param int $postId ID поста
    * @param \FieldModel $fieldModel Модель полей
    * @return array Массив текущих значений
    */
    private function getCurrentFieldValues($customFields, $postId, $fieldModel) {
        $currentValues = [];
        foreach ($customFields as $field) {
            $currentValues[$field['system_name']] = $fieldModel->getFieldValue('post', $postId, $field['system_name']);
        }
        return $currentValues;
    }

    /**
    * Валидирует пользовательские поля
    * @param array $customFields Массив полей
    * @param array $currentValues Текущие значения
    * @param \FieldManager $fieldManager Менеджер полей
    * @return array Массив ошибок валидации
    */
    private function validateCustomFields($customFields, $currentValues, $fieldManager) {
        $errors = [];
        
        foreach ($customFields as $field) {
            $currentValue = $currentValues[$field['system_name']] ?? null;
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
    * @param array $currentValues Текущие значения
    * @param \FieldModel $fieldModel Модель полей
    * @param \FieldManager $fieldManager Менеджер полей
    * @return void
    */
    private function saveCustomField($field, $postId, $currentValues, $fieldModel, $fieldManager) {
        try {
            $value = $fieldManager->processFieldValue($field, $_POST, $_FILES, $currentValues);
            $config = is_array($field['config']) ? $field['config'] : json_decode($field['config'] ?? '{}', true);
            
            $fieldModel->saveFieldValue(
                'post',
                $postId,
                $field['system_name'],
                $value
            );
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_POSTS_EDIT_ERROR_FIELD_PROCESS, $field['name'], $e->getMessage()));
        }
    }

    /**
    * Обрабатывает успешное обновление поста
    * @return void
    */
    private function handleUpdateSuccess() {
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => LANG_ACTION_POSTS_EDIT_SUCCESS,
                'redirect' => ADMIN_URL . '/posts'
            ]);
            exit;
        }

        \Notification::success(LANG_ACTION_POSTS_EDIT_SUCCESS);
        $this->redirect(ADMIN_URL . '/posts');
    }

    /**
    * Отображает форму редактирования поста
    * @param array $post Данные поста
    * @param array $categories Список категорий
    * @param array $tags Список всех тегов
    * @param array $postTags Текущие теги поста
    * @param array $preparedBlocks Подготовленные блоки
    * @param bool $hasCategories Флаг наличия категорий
    * @return void
    */
    private function renderEditForm($post, $categories, $tags, $postTags, $preparedBlocks, $hasCategories) {
        $this->render('admin/posts/edit', [
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
            'postTags' => $postTags,
            'preparedBlocks' => $preparedBlocks,
            'postBlockManager' => $this->postBlockManager,
            'hasCategories' => $hasCategories,
            'pageTitle' => LANG_ACTION_POSTS_EDIT_PAGE_TITLE
        ]);
    }

    /**
    * Обрабатывает ошибки при редактировании поста
    * @param \Exception $e Исключение
    * @param array|null $post Данные поста
    * @param array $categories Список категорий
    * @param array $tags Список тегов
    * @param array $postTags Текущие теги поста
    * @param array $preparedBlocks Подготовленные блоки
    * @param bool $hasCategories Флаг наличия категорий
    * @return void
    */
    private function handleError($e, $post, $categories, $tags, $postTags, $preparedBlocks, $hasCategories) {
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
            exit;
        }

        \Notification::error($e->getMessage());
        
        $this->render('admin/posts/edit', [
            'post' => array_merge($post ?? [], $_POST),
            'categories' => $categories,
            'tags' => $tags,
            'postTags' => $postTags,
            'preparedBlocks' => $preparedBlocks,
            'postBlockManager' => $this->postBlockManager,
            'hasCategories' => $hasCategories
        ]);
    }
}