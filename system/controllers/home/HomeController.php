<?php

/**
* Контроллер главной страницы
* @package controllers
*/
class HomeController extends Controller {
    
    private $postModel;
    private $categoryModel;
    private $settingsModel;
    
    /**
    * Конструктор контроллера главной страницы
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        
        $this->postModel = new PostModel($db);
        $this->categoryModel = new CategoryModel($db);
        $this->settingsModel = new SettingsModel($db);
    }
    
    /**
    * Действие: Главная страница сайта
    * @return void
    */
    public function indexAction() {
        try {
            $postsSettings = $this->settingsModel->get('controller_posts');
            $homepageType = $postsSettings['homepage_type'] ?? 'default';
            
            if ($homepageType === 'posts_list') {
                $postsPerPage = (int)($postsSettings['homepage_posts_per_page'] ?? 10);
                $postsPerPage = max(1, min(50, $postsPerPage));
                
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $page = max(1, $page);
                
                $userGroups = $this->getUserGroups();
                $result = $this->postModel->getAllPaginated($page, $postsPerPage, $userGroups);
                
                $postIds = array_column($result['posts'], 'id');
                $commentsCount = [];
                if (!empty($postIds)) {
                    $commentsCount = $this->postModel->getCommentsCountForPosts($postIds);
                }
                
                foreach ($result['posts'] as &$post) {
                    $postId = $post['id'];
                    $post['comments_count'] = $commentsCount[$postId] ?? 0;
                    
                    if (isset($_SESSION['user_id'])) {
                        $post['userLiked'] = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
                        $post['userBookmarked'] = $this->postModel->hasBookmark($post['id'], $_SESSION['user_id']);
                    } else {
                        $post['userLiked'] = false;
                        $post['userBookmarked'] = false;
                    }
                    
                    $post['password_protected'] = $post['password_protected'] == 1;
                    
                    if (!isset($post['likes_count'])) {
                        $post['likes_count'] = $post['likes_count'] ?? 0;
                    }
                }
                
                $this->render('front/posts/index', [
                    'posts' => $result['posts'],
                    'total_posts' => $result['total'],
                    'total_pages' => $result['pages'],
                    'current_page' => $result['current_page'],
                    'categories' => $this->categoryModel->getAll(),
                    'pagination' => [
                        'current_page' => $result['current_page'],
                        'total_pages' => $result['pages'],
                        'has_more' => $page < $result['pages'],
                        'next_url' => $page < $result['pages'] ? BASE_URL . '/posts?page=' . ($page + 1) : null
                    ]
                ]);
                return;
            }
            
            $homeSettings = $this->settingsModel->get('home_page') ?? [];
            
            $showRecentPosts = $homeSettings['show_recent_posts'] ?? true;
            $postsPerPage = $homeSettings['posts_per_page'] ?? 5;
            
            $userGroups = $this->getUserGroups();
            
            if ($showRecentPosts) {
                $result = $this->postModel->getAllPaginated(1, $postsPerPage, $userGroups);
                $posts = $result['posts'];
                
                $postIds = array_column($posts, 'id');
                $commentsCount = [];
                if (!empty($postIds)) {
                    $commentsCount = $this->postModel->getCommentsCountForPosts($postIds);
                }
                
                foreach ($posts as &$post) {
                    $post['comments_count'] = $commentsCount[$post['id']] ?? 0;
                }
            } else {
                $posts = [];
            }
            
            $categories = $this->categoryModel->getAll();
            
            $popularPosts = $this->getPopularPosts(5, $userGroups);
            
            $htmlBlocks = $this->getHomeHtmlBlocks();
            
            $this->render('front/home/index', [
                'posts' => $posts,
                'popular_posts' => $popularPosts,
                'categories' => $categories,
                'html_blocks' => $htmlBlocks,
                'home_settings' => $homeSettings,
                'title' => $homeSettings['title'] ?? LANG_CONTROLLER_HOME_DEFAULT_TITLE,
                'meta_description' => $homeSettings['meta_description'] ?? '',
                'meta_keywords' => $homeSettings['meta_keywords'] ?? ''
            ]);
            
        } catch (\Exception $e) {
            $this->render('front/home/index', [
                'posts' => [],
                'popular_posts' => [],
                'categories' => [],
                'html_blocks' => [],
                'home_settings' => [],
                'title' => LANG_CONTROLLER_HOME_DEFAULT_TITLE,
                'meta_description' => '',
                'meta_keywords' => ''
            ]);
        }
    }
    
    /**
    * Получение групп текущего пользователя
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
                
            } catch (\Exception $e) {}
        }
        
        return array_unique($userGroups);
    }
    
    /**
    * Получение популярных постов
    * @param int $limit Количество возвращаемых постов
    * @param array $userGroups Группы пользователя для фильтрации доступа
    * @return array Массив популярных постов
    */
    private function getPopularPosts($limit = 5, $userGroups = []) {
        try {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                           u.username as author_name, u.display_name as author_display_name
                    FROM posts p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.status = 'published'
                    ORDER BY p.views DESC, p.created_at DESC
                    LIMIT " . (int)$limit;
            
            $posts = $this->db->fetchAll($sql);
            
            $visiblePosts = [];
            foreach ($posts as $post) {
                if ($this->postModel->checkPostVisibility($post['id'], $userGroups)) {
                    $visiblePosts[] = $post;
                }
            }
            
            return $visiblePosts;
            
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
    * Получение HTML-блоков для главной страницы
    * @return array Массив HTML-блоков с обработанным контентом
    */
    private function getHomeHtmlBlocks() {
        try {
            $htmlBlocks = $this->db->fetchAll("
                SELECT * FROM html_blocks 
                WHERE is_active = 1 
                AND position LIKE '%home%'
                ORDER BY sort_order ASC
            ");
            
            foreach ($htmlBlocks as &$block) {
                $block['processed_content'] = process_shortcodes($block['content']);
            }
            
            return $htmlBlocks;
            
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
    * Действие: Настройки главной страницы в админ-панели 
    * @return void
    */
    public function adminSettingsAction() {
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $settings = [
                    'title' => $_POST['title'] ?? LANG_CONTROLLER_HOME_DEFAULT_TITLE,
                    'meta_description' => $_POST['meta_description'] ?? '',
                    'meta_keywords' => $_POST['meta_keywords'] ?? '',
                    'show_recent_posts' => isset($_POST['show_recent_posts']) ? 1 : 0,
                    'posts_per_page' => (int)($_POST['posts_per_page'] ?? 5),
                    'show_popular_posts' => isset($_POST['show_popular_posts']) ? 1 : 0,
                    'popular_posts_count' => (int)($_POST['popular_posts_count'] ?? 5),
                    'custom_welcome_text' => $_POST['custom_welcome_text'] ?? ''
                ];
                
                $this->settingsModel->set('home_page', $settings);
                \Notification::success(LANG_CONTROLLER_HOME_SETTINGS_SAVED);
                
            } catch (\Exception $e) {
                \Notification::error(LANG_CONTROLLER_HOME_SETTINGS_ERROR . $e->getMessage());
            }
            
            $this->redirect(ADMIN_URL . '/home/settings');
        }
        
        $currentSettings = $this->settingsModel->get('home_page') ?? [];
        
        $this->render('admin/home/settings', [
            'settings' => $currentSettings,
            'pageTitle' => LANG_CONTROLLER_HOME_SETTINGS_TITLE
        ]);
    }
}