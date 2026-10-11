<?php

class AdminController extends Controller {

    private $userModel;

    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_ADMIN_MANIFEST_NAME,
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => LANG_CONTROLLER_ADMIN_MANIFEST_DESCRIPTION
    ];
    
    /**
    * Конструктор контроллера администратора
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new UserModel($db);

        $this->initAdminBreadcrumbs();
        
        $currentAction = $this->getCurrentAction();
        
        if ($currentAction !== 'login') {
            if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
                Notification::error(LANG_CONTROLLER_ADMIN_ACCESS_DENIED);
                $this->redirect(BASE_URL);
                exit;
            }
        }
    }

    /**
     * Проверка AJAX запроса
     * @return bool
     */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
    * Определяет текущее действие из URI
    * @return string Название текущего действия или пустая строка
    */
    private function getCurrentAction() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($uri, '/'));
        
        if (count($pathParts) >= 2 && $pathParts[0] === 'admin') {
            return $pathParts[1];
        }
        
        return '';
    }
    
    /**
    * Главная страница панели управления
    * @throws Exception При ошибках
    */
    public function indexAction() {
        $this->pageTitle = 'Bloggy';
        
        try {
            $stats = [
                'posts' => $this->db->fetch("SELECT COUNT(*) as count FROM posts")['count'],
                'categories' => $this->db->fetch("SELECT COUNT(*) as count FROM categories")['count'],
                'tags' => $this->db->fetch("SELECT COUNT(*) as count FROM tags")['count'],
                'pages' => $this->db->fetch("SELECT COUNT(*) as count FROM pages")['count'],
                'comments' => $this->db->fetch("SELECT COUNT(*) as count FROM comments")['count'],
                'users' => $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'],
                'content_blocks' => $this->db->fetch("SELECT COUNT(*) as count FROM html_block_types")['count']
            ];
            
            $stats = Event::filter('admin.dashboard.stats', $stats, [
                'db' => $this->db,
                'controller' => $this
            ]);
            
            $count_posts = SettingsHelper::get('controller_admin', 'count_posts', 4);
            
            $recentPosts = $this->db->fetchAll("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT $count_posts");
            $popularPosts = $this->db->fetchAll("SELECT * FROM posts WHERE status = 'published' AND views > 0 ORDER BY views DESC LIMIT $count_posts");
            $commentedPosts = $this->db->fetchAll("
                SELECT 
                    p.*,
                    COUNT(c.id) as comments_count
                FROM posts p
                LEFT JOIN comments c ON p.id = c.post_id AND c.status = 'approved'
                WHERE p.status = 'published'
                GROUP BY p.id
                HAVING comments_count > 0
                ORDER BY comments_count DESC
                LIMIT $count_posts
            ");
            $draftPosts = $this->db->fetchAll("SELECT * FROM posts WHERE status = 'draft' ORDER BY created_at DESC LIMIT $count_posts");
            $recentSearches = [];
            $popularSearches = [];
            
            try {
                $searchModel = new SearchModel($this->db);
                $recentSearches = $searchModel->getRecentSearchQueries(5);
                $popularSearches = $searchModel->getPopularSearchQueries(5);
            } catch (Exception $e) {
                Notification::warning(LANG_CONTROLLER_ADMIN_SEARCH_LOAD_ERROR);
            }
            
            $this->render('admin/dashboard', [
                'stats' => $stats,
                'recentPosts' => $recentPosts,
                'popularPosts' => $popularPosts,
                'commentedPosts' => $commentedPosts,
                'draftPosts' => $draftPosts,
                'recentSearches' => $recentSearches,
                'popularSearches' => $popularSearches,
                'hasQuickActions' => $this->hasQuickActions(),
                'menuCounters' => $this->getMenuCounters()
            ]);
            
        } catch (Exception $e) {
            Notification::error(LANG_CONTROLLER_ADMIN_DASHBOARD_LOAD_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
    * Страница аутентификации администратора
    */
    public function loginAction() {
        if (isset($_SESSION['user_id'])) {
            Notification::info(LANG_CONTROLLER_ADMIN_ALREADY_AUTH);
            $this->redirect(ADMIN_URL);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $token = $_POST['csrf_token'] ?? '';
                if (!\CsrfToken::verify($token, 'admin_login')) {
                    throw new \Exception('Invalid CSRF token');
                }

                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';

                $user = $this->userModel->authenticate($username, $password);

                if ($user) {
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    Notification::success(sprintf(LANG_CONTROLLER_ADMIN_WELCOME, $user['username']));
                    $this->redirect(ADMIN_URL);
                    return;
                } else {
                    Notification::error(LANG_CONTROLLER_ADMIN_INVALID_CREDENTIALS);
                    $this->render('admin/login', ['csrf_token' => \CsrfToken::generate('admin_login')]);
                }
            } catch (Exception $e) {
                Notification::error(LANG_CONTROLLER_ADMIN_AUTH_ERROR);
                $this->render('admin/login', ['csrf_token' => \CsrfToken::generate('admin_login')]);
            }
        } else {
            $this->render('admin/login', ['csrf_token' => \CsrfToken::generate('admin_login')]);
        }
    }
    
    /**
    * Завершение сессии администратора
    */
    public function logoutAction() {
        try {
            $username = $_SESSION['username'] ?? LANG_CONTROLLER_ADMIN_USER;
            session_destroy();
            Notification::success(sprintf(LANG_CONTROLLER_ADMIN_LOGOUT_SUCCESS, $username));
        } catch (Exception $e) {
            Notification::error(LANG_CONTROLLER_ADMIN_LOGOUT_ERROR);
        }
        $this->redirect(ADMIN_URL . '/login');
    }

    /**
    * Проверка активных быстрых действий
    */
    public function hasQuickActions() {
        $actions = [
            'add_post', 'add_page', 'add_category', 'add_tag',
            'add_user', 'add_content_block', 'add_field', 'add_form'
        ];
        
        foreach ($actions as $action) {
            if (SettingsHelper::get('controller_admin', $action, false)) {
                return true;
            }
        }
        return false;
    }

    /**
    * Удаление папки install (AJAX)
    */
    public function deleteInstallFolderAction() {
        header('Content-Type: application/json');
        
        if (!$this->isAjaxRequest()) {
            echo json_encode(['success' => false, 'message' => LANG_CONTROLLER_ADMIN_AJAX_ONLY]);
            exit;
        }
        
        $installPath = dirname(dirname(__DIR__)) . '/install';
        
        if (!is_dir($installPath)) {
            $installPath = ROOT_PATH . '/install';
        }
        if (!is_dir($installPath)) {
            $installPath = __DIR__ . '/../../install';
        }
        if (!is_dir($installPath)) {
            $installPath = BASE_PATH . '/install';
        }
        
        error_log("[DEBUG] Trying to delete install folder at: " . $installPath);
        
        if (!is_dir($installPath)) {
            echo json_encode(['success' => false, 'message' => sprintf(LANG_CONTROLLER_ADMIN_INSTALL_NOT_FOUND, $installPath)]);
            exit;
        }
        
        if (!is_writable(dirname($installPath))) {
            echo json_encode(['success' => false, 'message' => LANG_CONTROLLER_ADMIN_INSTALL_NOT_WRITABLE]);
            exit;
        }
        
        $result = $this->deleteDirectory($installPath);
        
        if ($result) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => LANG_CONTROLLER_ADMIN_INSTALL_DELETED_SUCCESS
            ];
            echo json_encode(['success' => true, 'message' => LANG_CONTROLLER_ADMIN_INSTALL_DELETED]);
        } else {
            echo json_encode(['success' => false, 'message' => LANG_CONTROLLER_ADMIN_INSTALL_DELETE_FAILED]);
        }
        exit;
    }

    /**
    * Рекурсивное удаление директории с расширенной обработкой ошибок
    * @param string $dir
    * @return bool
    */
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        $files = array_diff(@scandir($dir) ?: [], ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (in_array($file, ['.htaccess', '.gitignore', 'index.html'])) {
                @chmod($path, 0777);
            }
            
            if (is_dir($path)) {
                if (!$this->deleteDirectory($path)) {
                    error_log("[DEBUG] Failed to delete directory: " . $path);
                    return false;
                }
            } else {
                @chmod($path, 0777);
                if (!@unlink($path)) {
                    error_log("[DEBUG] Failed to delete file: " . $path);
                    return false;
                }
            }
        }
        
        @chmod($dir, 0777);
        
        return @rmdir($dir);
    }

    /**
    * Действие проверки обновлений
    */
    public function checkUpdatesAction() {
        
        $this->addBreadcrumb(LANG_CONTROLLER_ADMIN_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_CONTROLLER_ADMIN_UPDATES_BREADCRUMB);
        
        $this->pageTitle = LANG_CONTROLLER_ADMIN_UPDATES_TITLE;
        
        $updateResult = VersionHelper::checkUpdates();
        
        $this->render('admin/updates/index', [
            'update_result' => $updateResult,
            'current_version' => VersionHelper::getVersion(),
            'current_version_name' => VersionHelper::getVersionName(),
            'current_version_date' => VersionHelper::getVersionDate(),
            'current_build' => VersionHelper::getBuild(),
            'pageTitle' => LANG_CONTROLLER_ADMIN_UPDATES_TITLE
        ]);
    }

    /**
    * Получает счетчики для отображения в главном меню
    * @return array
    */
    private function getMenuCounters() {
        $counters = [];
        
        if (SettingsHelper::get('controller_admin', 'show_menu_counters', false)) {
            try {
                $counters = [
                    'posts' => $this->db->fetchValue("SELECT COUNT(*) FROM posts") ?? 0,
                    'categories' => $this->db->fetchValue("SELECT COUNT(*) FROM categories") ?? 0,
                    'tags' => $this->db->fetchValue("SELECT COUNT(*) FROM tags") ?? 0,
                    'comments' => $this->db->fetchValue("SELECT COUNT(*) FROM comments") ?? 0,
                    'users' => $this->db->fetchValue("SELECT COUNT(*) FROM users") ?? 0,
                    'pages' => $this->db->fetchValue("SELECT COUNT(*) FROM pages") ?? 0,
                    'html-blocks' => $this->db->fetchValue("SELECT COUNT(*) FROM html_blocks") ?? 0,
                ];
            } catch (Exception $e) {
                return [];
            }
        }
        
        return $counters;
    }

    /**
    * Статический метод для получения счетчиков меню (для использования в layout)
    * @param Database $db
    * @return array
    */
    public static function getMenuCountersStatic($db) {
        $counters = [];
        
        if (SettingsHelper::get('controller_admin', 'show_menu_counters', false)) {
            try {
                $counters = [
                    'posts' => $db->fetchValue("SELECT COUNT(*) FROM posts") ?? 0,
                    'categories' => $db->fetchValue("SELECT COUNT(*) FROM categories") ?? 0,
                    'tags' => $db->fetchValue("SELECT COUNT(*) FROM tags") ?? 0,
                    'comments' => $db->fetchValue("SELECT COUNT(*) FROM comments") ?? 0,
                    'users' => $db->fetchValue("SELECT COUNT(*) FROM users") ?? 0,
                    'pages' => $db->fetchValue("SELECT COUNT(*) FROM pages") ?? 0,
                    'html-blocks' => $db->fetchValue("SELECT COUNT(*) FROM html_blocks") ?? 0,
                ];
            } catch (Exception $e) {
                return [];
            }
        }
        
        return $counters;
    }

}