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
                'hasQuickActions' => $this->hasQuickActions()
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
    * Управление шаблонами сайта
    */
    public function templatesAction() {
        $this->pageTitle = LANG_CONTROLLER_ADMIN_TEMPLATES_TITLE;
        
        $this->addBreadcrumb(LANG_CONTROLLER_ADMIN_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_CONTROLLER_ADMIN_TEMPLATES_BREADCRUMB);
        
        $templates = $this->getAvailableTemplates();
        $currentTemplate = SettingsHelper::get('site', 'site_template', 'default');
        
        $this->render('admin/templates/index', [
            'templates' => $templates,
            'currentTemplate' => $currentTemplate
        ]);
    }

    /**
    * API: Получение списка файлов шаблона
    */
    public function getTemplateFilesAction() {
        header('Content-Type: application/json');
        
        $template = $_GET['template'] ?? 'default';
        $files = $this->getTemplateFiles($template);
        
        echo json_encode($files);
        exit;
    }

    /**
    * API: Получение содержимого файла шаблона
    */
    public function getTemplateFileAction() {
        header('Content-Type: application/json');
        
        $template = $_GET['template'] ?? 'default';
        $filePath = $_GET['file'] ?? '';
        
        if (empty($filePath) || strpos($filePath, '..') !== false) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_INVALID_FILE_PATH]);
            exit;
        }
        
        $fullPath = TEMPLATES_PATH . '/' . $template . '/front/' . $filePath;
        
        $normalizedPath = $this->normalizePath($fullPath);
        $templateBasePath = $this->normalizePath(TEMPLATES_PATH . '/' . $template);
        
        if (strpos($normalizedPath, $templateBasePath) !== 0) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_ACCESS_DENIED]);
            exit;
        }
        
        if (!file_exists($fullPath)) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_FILE_NOT_FOUND . $fullPath]);
            exit;
        }
        
        if (!is_file($fullPath)) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_IS_DIRECTORY]);
            exit;
        }
        
        $content = file_get_contents($fullPath);
        $fileInfo = $this->getFileInfo($fullPath, $filePath);
        
        echo json_encode([
            'success' => true,
            'content' => $content,
            'info' => $fileInfo
        ]);
        exit;
    }

    /**
    * API: Сохранение изменений в файле шаблона
    */
    public function saveTemplateFileAction() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $template = $input['template'] ?? 'default';
        $filePath = $input['file'] ?? '';
        $content = $input['content'] ?? '';
        
        if (empty($filePath) || strpos($filePath, '..') !== false) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_INVALID_FILE_PATH]);
            exit;
        }
        
        $fullPath = TEMPLATES_PATH . '/' . $template . '/front/' . $filePath;
        
        $normalizedPath = $this->normalizePath($fullPath);
        $templateBasePath = $this->normalizePath(TEMPLATES_PATH . '/' . $template);
        
        if (strpos($normalizedPath, $templateBasePath) !== 0) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_ACCESS_DENIED]);
            exit;
        }
        
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $backupCreated = false;
        if (file_exists($fullPath)) {
            $backupCreated = BackupHelper::createBackup($fullPath);
        }
        
        if (file_put_contents($fullPath, $content) !== false) {
            $response = ['success' => true];
            if ($backupCreated) {
                $response['backup_created'] = true;
                $response['message'] = LANG_CONTROLLER_ADMIN_SAVE_SUCCESS_WITH_BACKUP;
            } else {
                $response['message'] = LANG_CONTROLLER_ADMIN_SAVE_SUCCESS;
            }
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_SAVE_ERROR]);
        }
        exit;
    }

    /**
    * Нормализация пути к файлу
    */
    private function normalizePath($path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = [];
        
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        
        return implode('/', $absolutes);
    }

    /**
    * Получение списка доступных шаблонов
    */
    private function getAvailableTemplates() {
        $templates = [];
        $templatesPath = TEMPLATES_PATH;
        
        if (is_dir($templatesPath)) {
            $items = scandir($templatesPath);
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($templatesPath . '/' . $item)) {
                    $templates[] = [
                        'name' => $item,
                        'path' => $templatesPath . '/' . $item
                    ];
                }
            }
        }
        
        return $templates;
    }

    /**
    * Рекурсивное получение файлов шаблона с кэшированием
    */
    private function getTemplateFiles($template) {
        $templatePath = TEMPLATES_PATH . '/' . $template;
        $frontPath = $templatePath . '/front';
        
        if (!is_dir($frontPath)) {
            return [];
        }

        $cacheKey = 'tmpl_files_all_' . md5($template);
        $cacheFile = CACHE_DIR . '/' . $cacheKey . '.json';
        
        if (file_exists($cacheFile) && is_writable(CACHE_DIR)) {
            $cacheData = @json_decode(file_get_contents($cacheFile), true);
            if ($cacheData && isset($cacheData['cached_at'], $cacheData['template_mtime'], $cacheData['files'])) {
                $templateMtime = filemtime($templatePath);
                if ((time() - $cacheData['cached_at']) < 600 && $cacheData['template_mtime'] === $templateMtime) {
                    return $cacheData['files'];
                }
            }
        }

        $files = [];
        $excludeDirs = ['.git', 'node_modules', 'vendor', '.cache', 'tmp', '.idea', '.vscode', 'admin'];
        $allowedExtensions = ['php', 'html', 'css', 'js', 'json', 'xml', 'txt', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
        
        $scanDir = function($dir, $basePath) use (&$scanDir, &$files, $excludeDirs, $allowedExtensions, $frontPath) {
            $items = @scandir($dir);
            if ($items === false) return;
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
                $relativePath = str_replace($frontPath . DIRECTORY_SEPARATOR, '', $fullPath);
                $relativePath = str_replace('\\', '/', $relativePath);
                
                if (is_dir($fullPath)) {
                    if (!in_array($item, $excludeDirs)) {
                        $scanDir($fullPath, $basePath);
                    }
                    continue;
                }
                
                if (is_file($fullPath)) {
                    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                    if (in_array($extension, $allowedExtensions)) {
                        $files[] = [
                            'name' => basename($relativePath),
                            'path' => $relativePath,
                            'size' => $this->formatFileSize(filesize($fullPath)),
                            'description' => $this->getFileDescription($fullPath),
                            'extension' => $extension,
                            'full_path' => $fullPath
                        ];
                    }
                }
            }
        };
        
        $scanDir($frontPath, $frontPath);
        usort($files, fn($a, $b) => strcmp($a['path'], $b['path']));

        if (is_writable(CACHE_DIR)) {
            $cacheData = [
                'cached_at' => time(),
                'template_mtime' => filemtime($templatePath),
                'files' => $files
            ];
            @file_put_contents($cacheFile, json_encode($cacheData), LOCK_EX);
        }

        return $files;
    }

    /**
    * Получает описание файла (для PHP-шаблонов)
    */
    private function getFileDescription($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if ($extension === 'php') {
            $content = @file_get_contents($filePath, false, null, 0, 4096);
            if ($content !== false && preg_match('/\/\*\*\s*\*\s*Template Name:\s*(.+?)\s*\*\//s', $content, $matches)) {
                return trim($matches[1]);
            }
        }
        
        return '';
    }

    /**
    * Парсер заголовка шаблона
    * @param string $filePath Полный путь к файлу
    * @return array ['is_template' => bool, 'description' => string]
    */
    private function parseTemplateHeader($filePath) {

        $content = @file_get_contents($filePath, false, null, 0, 4096);
        if ($content === false || $content === '') {
            return ['is_template' => false, 'description' => ''];
        }
        
        if (preg_match('/\/\*\*\s*\*\s*Template Name:\s*(.+?)\s*\*\//s', $content, $matches)) {
            return ['is_template' => true, 'description' => trim($matches[1])];
        }
        
        if (preg_match('/\/\/\s*Template Name:\s*(.+?)$/m', $content, $matches)) {
            return ['is_template' => true, 'description' => trim($matches[1])];
        }
        
        return ['is_template' => false, 'description' => ''];
    }

    /**
    * Получение метаинформации о файле
    */
    private function getFileInfo($fullPath, $relativePath) {
        $size = @filesize($fullPath);
        if ($size === false) $size = 0;
        
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $description = $this->getFileDescription($fullPath);
        
        return [
            'name' => basename($relativePath),
            'path' => $relativePath,
            'size' => $this->formatFileSize($size),
            'description' => $description,
            'extension' => $extension,
            'full_path' => $fullPath,
            'updated_at' => filemtime($fullPath),
            'is_editable' => $this->isEditableFile($fullPath)
        ];
    }

    private function isEditableFile($fullPath) {
        $editableExtensions = ['php', 'html', 'css', 'js', 'json', 'xml', 'txt', 'svg'];
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        return in_array($extension, $editableExtensions);
    }

    /**
    * Форматирование размера файла
    */
    private function formatFileSize($size) {
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return round($size / 1048576, 2) . ' MB';
        }
    }

    /**
    * API: Скачивание файла
    */
    public function downloadFileAction() {
        $template = $_GET['template'] ?? 'default';
        $filePath = $_GET['file'] ?? '';
        
        if (empty($filePath) || strpos($filePath, '..') !== false) {
            die(LANG_CONTROLLER_ADMIN_INVALID_FILE_PATH);
        }
        
        $fullPath = TEMPLATES_PATH . '/' . $template . '/front/' . $filePath;
        
        $normalizedPath = $this->normalizePath($fullPath);
        $templateBasePath = $this->normalizePath(TEMPLATES_PATH . '/' . $template);
        
        if (strpos($normalizedPath, $templateBasePath) !== 0) {
            die(LANG_CONTROLLER_ADMIN_ACCESS_DENIED);
        }
        
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            die(LANG_CONTROLLER_ADMIN_FILE_NOT_FOUND_SIMPLE);
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
    * API: Загрузка файла на сервер
    */
    public function uploadFileAction() {
        header('Content-Type: application/json');
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_UPLOAD_ERROR]);
            exit;
        }
        
        $template = $_POST['template'] ?? 'default';
        $uploadPath = $_POST['path'] ?? '';
        
        if (strpos($uploadPath, '..') !== false || strpos($uploadPath, './') !== false) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_INVALID_PATH]);
            exit;
        }
        
        $file = $_FILES['file'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['php', 'html', 'css', 'js', 'json', 'xml', 'txt', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_INVALID_FILE_TYPE]);
            exit;
        }
        
        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_FILE_TOO_LARGE]);
            exit;
        }
        
        $targetDir = TEMPLATES_PATH . '/' . $template . '/front';
        if (!empty($uploadPath)) {
            $targetDir .= '/' . trim($uploadPath, '/');
        }
        
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_CANT_CREATE_DIR]);
                exit;
            }
        }
        
        $targetFile = $targetDir . '/' . basename($file['name']);
        
        $normalizedTarget = $this->normalizePath($targetFile);
        $templateBasePath = $this->normalizePath(TEMPLATES_PATH . '/' . $template);
        
        if (strpos($normalizedTarget, $templateBasePath) !== 0) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_ACCESS_DENIED]);
            exit;
        }
        
        if (file_exists($targetFile)) {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_FILE_EXISTS]);
            exit;
        }
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            BackupHelper::createBackup($targetFile);
            
            $relativePath = str_replace(TEMPLATES_PATH . '/' . $template . '/front/', '', $targetFile);
            
            echo json_encode(['success' => true, 'message' => LANG_CONTROLLER_ADMIN_UPLOAD_SUCCESS, 'path' => $relativePath]);
        } else {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_SAVE_ERROR]);
        }
        exit;
    }

    /**
    * API: Создание папки front для шаблона
    */
    public function createFrontFolderAction() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $template = $input['template'] ?? 'default';
        
        $frontPath = TEMPLATES_PATH . '/' . $template . '/front';
        
        if (is_dir($frontPath)) {
            echo json_encode(['success' => true, 'message' => LANG_CONTROLLER_ADMIN_FOLDER_EXISTS]);
            exit;
        }
        
        if (mkdir($frontPath, 0755, true)) {
            echo json_encode(['success' => true, 'message' => LANG_CONTROLLER_ADMIN_FOLDER_CREATED]);
        } else {
            echo json_encode(['success' => false, 'error' => LANG_CONTROLLER_ADMIN_CANT_CREATE_FOLDER]);
        }
        exit;
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
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
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

}