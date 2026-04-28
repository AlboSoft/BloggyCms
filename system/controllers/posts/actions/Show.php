<?php

namespace posts\actions;

/**
* Действие отображения отдельного поста (публичная часть)
* @package posts\actions
*/
class Show extends PostAction {
    
    /**
    * Метод выполнения отображения поста
    * @return void
    * @throws \Exception Если slug не указан
    */
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        if (!$slug) {
            throw new \Exception(LANG_ACTION_POSTS_SHOW_NO_SLUG);
        }

        try {
            $post = $this->postModel->getBySlug($slug);
            
            if (!$post) {
                \Notification::error(LANG_ACTION_POSTS_SHOW_POST_NOT_FOUND);
                $this->redirect(BASE_URL);
                return;
            }
            
            $hasAccess = $this->postModel->checkAdultAccess($post['id']);
            if (!$hasAccess) {
                $settingsModel = new \SettingsModel($this->db);
                $settings = $settingsModel->get('controller_posts');
                $action = $settings['adult_content_action'] ?? 'none';
                
                if ($action === 'redirect_login') {
                    $_SESSION['redirect_after_login'] = BASE_URL . '/post/' . $post['slug'];
                    $this->redirect(BASE_URL . '/login');
                    return;
                } elseif ($action === 'age_check') {
                    $_SESSION['adult_redirect_after'] = BASE_URL . '/post/' . $post['slug'];
                    $this->redirect(BASE_URL . '/post/check-age/' . $post['id']);
                    return;
                }
            }
            
            $userGroups = $this->getUserGroups();
            $isVisible = $this->postModel->checkPostVisibility($post['id'], $userGroups);
            
            if (!$isVisible) {
                http_response_code(404);
                $this->render('front/404');
                return;
            }
            
            $isPasswordProtected = $post['password_protected'] == 1;
            
            if ($isPasswordProtected) {
                $hasAccess = $this->checkPostAccess($post['id']);
                
                if (!$hasAccess) {
                    $this->renderPasswordForm($post);
                    return;
                }
            }
            
            $this->showPost($post);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_POSTS_SHOW_ERROR);
            $this->redirect(BASE_URL);
        }
    }
    
    /**
    * Проверяет, есть ли у пользователя доступ к защищенному посту
    * @param int $postId ID поста
    * @return bool true если доступ есть
    */
    private function checkPostAccess($postId) {
        return isset($_SESSION['post_access'][$postId]) && $_SESSION['post_access'][$postId] === true;
    }
    
    /**
    * Отображает форму ввода пароля для защищенного поста
    * @param array $post Данные поста
    * @return void
    */
    private function renderPasswordForm($post) {
        $this->addBreadcrumb(LANG_ACTION_POSTS_SHOW_BREADCRUMB_HOME, BASE_URL);
        $this->addBreadcrumb(LANG_ACTION_POSTS_SHOW_BREADCRUMB_ALL_POSTS, BASE_URL . '/posts');
        
        if (!empty($post['category_id'])) {
            $category = $this->categoryModel->getById($post['category_id']);
            if ($category) {
                $this->addBreadcrumb(
                    $category['name'],
                    BASE_URL . '/category/' . $category['slug']
                );
            }
        }
        
        $this->addBreadcrumb(sprintf(LANG_ACTION_POSTS_SHOW_BREADCRUMB_PROTECTED, $post['title']));
        $this->setPageTitle(sprintf(LANG_ACTION_POSTS_SHOW_PAGE_TITLE_PROTECTED, $post['title']));
        
        $categories = $this->categoryModel->getAll();
        $category = $this->categoryModel->getById($post['category_id']);
        $createdDate = new \DateTime($post['created_at']);
        $formattedDate = $createdDate->format('d M Y');
        
        $this->render('front/posts/password', [
            'post' => $post,
            'categories' => $categories,
            'category' => $category,
            'formattedDate' => $formattedDate,
            'error' => isset($_GET['error']) && $_GET['error'] == 'password'
        ]);
    }
    
    /**
    * Отображает полную страницу поста со всем контентом
    * @param array $post Данные поста
    * @return void
    */
    private function showPost($post) {
        $this->addBreadcrumb(LANG_ACTION_POSTS_SHOW_BREADCRUMB_HOME, BASE_URL);
        $this->addBreadcrumb(LANG_ACTION_POSTS_SHOW_BREADCRUMB_ALL_POSTS, BASE_URL . '/posts');
        
        if (!empty($post['category_id'])) {
            $category = $this->categoryModel->getById($post['category_id']);
            if ($category) {
                $this->addBreadcrumb(
                    $category['name'],
                    BASE_URL . '/category/' . $category['slug']
                );
            }
        }
        
        $this->addBreadcrumb($post['title']);
        $this->setPageTitle($post['title']);
        $this->postModel->incrementUniqueViews($post['id']);
        
        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
        $userLiked = false;
        $userBookmarked = false;
        
        if (isset($_SESSION['user_id'])) {
            $userLiked = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
            $userBookmarked = $this->postModel->hasBookmark($post['id'], $_SESSION['user_id']);
        }
        
        $commentsData = $this->getComments($post['id'], $isAdmin);
        $comments = $commentsData['tree'];
        $totalComments = $commentsData['total'];
        $tags = $this->tagModel->getForPost($post['id']);
        $post['tags'] = $tags;
        $category = $this->categoryModel->getById($post['category_id']);
        $categories = $this->categoryModel->getAll();
        $processedBlocks = $this->getProcessedBlocks($post['id']);
        $fieldValues = $this->getCustomFields($post['id']);
        $scrollToComment = $_GET['scroll_to_comment'] ?? null;
        $pendingComment = $_GET['pending_comment'] ?? null;
        $post['comments_count'] = $totalComments;

        $seoModel = new \SeoModel($this->db);
        $schemaData = $seoModel->generateBlogPostingSchema($post);

        $this->render('front/posts/show', [
            'post' => $post,
            'tags' => $tags,
            'categories' => $categories,
            'category' => $category,
            'comments' => $comments,
            'totalComments' => $totalComments,
            'commentController' => new \CommentController($this->db),
            'userLiked' => $userLiked,
            'userBookmarked' => $userBookmarked,
            'fieldValues' => $fieldValues,
            'blocks' => $processedBlocks,
            'scrollToComment' => $scrollToComment,
            'pendingComment' => $pendingComment,
            'isAdmin' => $isAdmin,
            'likes_count' => $post['likes_count'] ?? 0,
            'schemaData' => $schemaData
        ]);
    }

    /**
    * Получает и обрабатывает комментарии к посту
    * @param int $postId ID поста
    * @param bool $isAdmin Флаг администратора
    * @return array Массив с деревом комментариев и общим количеством
    */
    private function getComments($postId, $isAdmin) {
        $commentController = new \CommentController($this->db);
        return $commentController->getCommentsByPostWithUserData($postId, $isAdmin);
    }

    /**
    * Получает и обрабатывает блоки поста
    * @param int $postId ID поста
    * @return array Массив обработанных блоков
    */
    private function getProcessedBlocks($postId) {
        $blocks = $this->postBlockModel->getByPost($postId);
        $processedBlocks = [];
        
        $blocksData = [];
        foreach ($blocks as $block) {
            $blocksData[] = [
                'type' => $block['type']
            ];
        }
        
        if (!empty($blocksData)) {
            $this->postBlockManager->loadFrontendAssetsForBlocks($blocksData);
        }
        
        foreach ($blocks as $block) {
            $processedBlocks[] = $this->processSingleBlock($block);
        }
        
        return $processedBlocks;
    }

    /**
    * Обрабатывает один блок поста
    * @param array $block Данные блока
    * @return array Обработанный блок
    */
    private function processSingleBlock($block) {
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
        
        $dbSettings = $this->postBlockModel->getBlockSettings($block['type']);
        $mergedSettings = array_merge($dbSettings, $settings);
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
    * Получает пользовательские поля для поста
    * @param int $postId ID поста
    * @return array Массив значений полей
    */
    private function getCustomFields($postId) {
        $fieldModel = new \FieldModel($this->db);
        $customFields = $fieldModel->getActiveByEntityType('post');
        $fieldValues = [];
        
        foreach ($customFields as $field) {
            $fieldValues[$field['system_name']] = [
                'value' => $fieldModel->getFieldValue('post', $postId, $field['system_name']),
                'field' => $field
            ];
        }
        
        return $fieldValues;
    }
    
    /**
    * Получает группы текущего пользователя для проверки видимости
    * @return array Массив групп пользователя
    */
    private function getUserGroups() {
        $userGroups = [];
        $userGroups[] = 'guest';
        
        if (isset($_SESSION['user_id'])) {
            try {
                $userModel = new \UserModel($this->db);
                $userGroupIds = $userModel->getUserGroupIds($_SESSION['user_id']);
                
                if (!empty($userGroupIds)) {
                    $userGroupIds = array_map('strval', $userGroupIds);
                    $userGroups = array_merge($userGroups, $userGroupIds);
                }
                
            } catch (\Exception $e) {
                DebugLogger::warning(LANG_ACTION_POSTS_SHOW_DEBUG_WARNING, [
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $userGroups = array_unique($userGroups);
        
        return $userGroups;
    }
}