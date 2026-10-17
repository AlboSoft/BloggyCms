<?php

namespace fragments\actions;

/**
* Абстрактный базовый класс для всех действий модуля фрагментов
* @package fragments\actions
*/
abstract class FragmentAction {
    
    protected $db;
    protected $params;
    protected $controller;
    protected $fragmentModel;
    protected $entryModel;
    protected $fieldModel;
    protected $fieldManager;
    protected $breadcrumbs;
    protected $pageTitle;
    
    /**
    * Конструктор
    * @param \Database $db
    * @param array $params
    */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->fragmentModel = new \FragmentModel($db);
        $this->entryModel = new \FragmentEntryModel($db);
        $this->fieldModel = new \FieldModel($db);
        $this->fieldManager = new \FieldManager($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    /**
    * Установка контроллера
    * @param object $controller
    */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
    * Абстрактный метод выполнения
    */
    abstract public function execute();
    
    /**
    * Добавляет элемент в хлебные крошки
    * @param string $title
    * @param string|null $url
    * @return self
    */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
    * Устанавливает заголовок страницы
    * @param string $title
    * @return self
    */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    /**
    * Рендеринг шаблона
    * @param string $template
    * @param array $data
    */
    protected function render($template, $data = []) {
        if (!$this->controller) {
            throw new \Exception('Controller not set for Action');
        }
        
        if (!isset($data['breadcrumbs'])) {
            $data['breadcrumbs'] = $this->breadcrumbs;
        }
        
        if (!isset($data['title']) && $this->pageTitle) {
            $data['title'] = $this->pageTitle;
        }
        
        $this->controller->render($template, $data);
    }
    
    /**
    * Перенаправление
    * @param string $url
    */
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    /**
    * Проверка AJAX-запроса
    * @return bool
    */
    protected function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Получение ID текущего пользователя
    * @return int|null
    */
    protected function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
    * Возвращает менеджер хлебных крошек
    * @return \BreadcrumbsManager
    */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }
    
    /**
    * Генерирует уникальное системное имя
    * @param string $name
    * @param int|null $excludeId
    * @return string
    */
    protected function generateUniqueSystemName($name, $excludeId = null) {
        $baseName = $this->sanitizeSystemName($name);
        $systemName = $baseName;
        $counter = 1;
        
        while ($this->fragmentModel->isSystemNameExists($systemName, $excludeId)) {
            $systemName = $baseName . '_' . $counter;
            $counter++;
        }
        
        return $systemName;
    }
    
    /**
    * Очищает системное имя
    * @param string $name
    * @return string
    */
    protected function sanitizeSystemName($name) {
        $name = mb_strtolower($name, 'UTF-8');
        $name = preg_replace('/[^a-z0-9_]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');
        return $name;
    }
    
    /**
    * Обрабатывает загрузку файлов для фрагмента
    * @param array $postSettings
    * @return array
    */
    protected function handleFragmentAssets($postSettings) {
        $cssFiles = [];
        if (!empty($_POST['css_files'])) {
            foreach ($_POST['css_files'] as $cssFile) {
                $cssFile = trim($cssFile);
                if (!empty($cssFile)) {
                    $cssFiles[] = $cssFile;
                }
            }
        }
        $postSettings['css_files'] = json_encode($cssFiles);
        
        $jsFiles = [];
        if (!empty($_POST['js_files'])) {
            foreach ($_POST['js_files'] as $jsFile) {
                $jsFile = trim($jsFile);
                if (!empty($jsFile)) {
                    $jsFiles[] = $jsFile;
                }
            }
        }
        $postSettings['js_files'] = json_encode($jsFiles);
        
        $postSettings['inline_css'] = trim($_POST['inline_css'] ?? '');
        $postSettings['inline_js'] = trim($_POST['inline_js'] ?? '');
        
        $postSettings['entry_template_enabled'] = !empty($_POST['entry_template_enabled']) ? 1 : 0;
        $postSettings['entry_template'] = trim($_POST['entry_template'] ?? '');
        
        return $postSettings;
    }
    
    /**
    * Подключает CSS/JS файлы и инлайн-код фрагмента к странице
    * @param array $fragment
    * @return void
    */
    protected function loadFragmentAssets($fragment) {
        if (!empty($fragment['css_files'])) {
            foreach ($fragment['css_files'] as $cssFile) {
                if (!empty($cssFile)) {
                    front_css($cssFile);
                }
            }
        }
        
        if (!empty($fragment['js_files'])) {
            foreach ($fragment['js_files'] as $jsFile) {
                if (!empty($jsFile)) {
                    front_js($jsFile);
                }
            }
        }
        
        if (!empty($fragment['inline_css'])) {
            front_inline_css($fragment['inline_css']);
        }
        
        if (!empty($fragment['inline_js'])) {
            front_inline_js($fragment['inline_js']);
        }
    }
    
    /**
    * Отображает стандартную страницу 404
    * @return void
    */
    protected function notFound() {
        http_response_code(404);
        
        $currentTemplate = function_exists('get_current_template') ? get_current_template() : 'default';
        $template404 = TEMPLATES_PATH . '/' . $currentTemplate . '/404.php';
        
        if (!file_exists($template404)) {
            $template404 = TEMPLATES_PATH . '/404.php';
        }
        
        if (file_exists($template404)) {
            include $template404;
        } else {
            echo '404 - Page not found';
        }
        
        exit;
    }
}