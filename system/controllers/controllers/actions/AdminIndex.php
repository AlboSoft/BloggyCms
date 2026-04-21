<?php

namespace controllers\actions;

/**
* Действие отображения списка контроллеров в админ-панели
* @package controllers\actions
*/
class AdminIndex extends ControllersAction {
    
    /**
    * Метод выполнения отображения списка контроллеров
    */
    public function execute() {
        try {
            $this->addBreadcrumb(LANG_ACTION_CONTROLLERS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_CONTROLLERS_ADMININDEX_BREADCRUMB_CONTROLLERS);

            $controllers = $this->getAllControllers();
            
            usort($controllers, function($a, $b) {
                if ($a['is_system'] === $b['is_system']) {
                    return strcmp($a['name'], $b['name']);
                }
                return $a['is_system'] ? -1 : 1;
            });
            
            $hints = [
                LANG_ACTION_CONTROLLERS_ADMININDEX_HINT_1,
                LANG_ACTION_CONTROLLERS_ADMININDEX_HINT_2,
                LANG_ACTION_CONTROLLERS_ADMININDEX_HINT_3,
                LANG_ACTION_CONTROLLERS_ADMININDEX_HINT_4,
                LANG_ACTION_CONTROLLERS_ADMININDEX_HINT_5,
                LANG_ACTION_CONTROLLERS_ADMININDEX_HINT_6,
                LANG_ACTION_CONTROLLERS_ADMININDEX_HINT_7,
            ];
            
            $randomHint = $hints[array_rand($hints)];
            
            return $this->render('admin/controllers/index', [
                'controllers' => $controllers,
                'randomHint' => $randomHint,
                'pageTitle' => LANG_ACTION_CONTROLLERS_ADMININDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_CONTROLLERS_ADMININDEX_ERROR . $e->getMessage());
            $this->redirect(\ADMIN_URL);
        }
    }
    
    /**
    * Получение всех контроллеров системы
    * @return array Массив информации о контроллерах
    */
    private function getAllControllers() {
        $controllers = [];
        
        $basePath = $this->getBasePath();
        $controllersDir = $basePath . '/system/controllers';
        
        if (!is_dir($controllersDir)) {
            return $controllers;
        }

        $items = scandir($controllersDir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === 'controllers') continue;
            
            $controllerPath = $controllersDir . '/' . $item;
            
            if (is_dir($controllerPath)) {
                $controllerInfo = $this->getControllerInfo($item, $controllerPath);
                if ($controllerInfo) {
                    $controllers[] = $controllerInfo;
                }
            }
        }
        
        return $controllers;
    }
    
    /**
    * Определение базового пути проекта
    * @return string Абсолютный путь к корневой директории проекта
    */
    private function getBasePath() {
        $currentFile = __FILE__;
        $basePath = dirname(dirname(dirname(dirname($currentFile))));
        
        if (is_dir($basePath . '/system/controllers')) {
            return $basePath;
        }
        
        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'];
            if (is_dir($docRoot . '/system/controllers')) {
                return $docRoot;
            }
        }
        
        return getcwd();
    }
    
    /**
    * Получение информации о конкретном контроллере
    * @param string $dirName Имя директории контроллера
    * @param string $controllerPath Абсолютный путь к директории контроллера
    * @return array Информация о контроллере
    */
    private function getControllerInfo($dirName, $controllerPath) {
        $info = [
            'name' => $this->formatControllerName($dirName),
            'key' => $dirName,
            'has_settings' => false,
            'has_routing' => false,
            'is_system' => false,
            'version' => '1.0.0',
            'description' => '',
            'author' => 'BloggyCMS',
            'path' => $dirName,
            'actions_count' => 0
        ];
        
        $systemControllers = [
            'admin', 'auth', 'settings', 'users', 'posts', 'categories', 
            'pages', 'menu', 'comments', 'profile', 'search', 
            'tags', 'fields', 'html_blocks', 'icons', 'postblocks', 'home', 'fragments',
            'docs', 'addons', 'archive', 'forms', 'login_attempt', 'notifications', 'seo', 'debug', 'language'
        ];
        $info['is_system'] = in_array(strtolower($dirName), $systemControllers);
        
        $manifestFile = $controllerPath . '/manifest.php';
        if (file_exists($manifestFile)) {
            $manifestData = $this->loadManifestFile($manifestFile);
            if ($manifestData) {
                $info = array_merge($info, $manifestData);
            }
        }
        
        $settingsFile = $controllerPath . '/Settings.php';
        if (file_exists($settingsFile)) {
            $info['has_settings'] = true;
        }
        
        $routesFile = $controllerPath . '/routes.php';
        if (file_exists($routesFile)) {
            $info['has_routing'] = true;
        }
        
        $actionsDir = $controllerPath . '/actions';
        if (is_dir($actionsDir)) {
            $phpFiles = glob($actionsDir . '/*.php');
            $info['actions_count'] = count($phpFiles);
        }
        
        return $info;
    }
    
    /**
    * Загрузка файла manifest.php контроллера
    * @param string $manifestFile Путь к файлу manifest.php
    * @return array|null Данные манифеста или null при ошибке
    */
    private function loadManifestFile($manifestFile) {
        try {
            $manifestData = include $manifestFile;
            if (is_array($manifestData)) {
                return $manifestData;
            }
        } catch (\Exception $e) {}
        
        return null;
    }
    
    /**
    * Форматирование имени контроллера для отображения
    * @param string $dirName Исходное имя директории контроллера
    * @return string Отформатированное имя для отображения
    */
    private function formatControllerName($dirName) {
        $name = str_replace(['_', '-'], ' ', $dirName);

        $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);
        
        return ucwords($name);
    }
}