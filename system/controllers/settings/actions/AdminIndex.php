<?php

namespace settings\actions;

/**
* Действие отображения и обработки главной страницы настроек
* @package settings\actions
*/
class AdminIndex extends SettingsAction {
    
    /**
    * Метод выполнения отображения и обработки настроек
    * @return void
    */
    public function execute() {

        $this->addBreadcrumb(LANG_ACTION_SETTINGS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_SETTINGS_ADMININDEX_BREADCRUMB_SETTINGS);
        
        try {
            $controllerManager = new \ControllerManager($this->db);

            $activeTab = $_GET['tab'] ?? 'general';
            $selectedController = $_GET['controller'] ?? '';
            
            $settings = [];
            
            if (in_array($activeTab, ['general', 'site'])) {
                $settings = $this->settingsModel->get($activeTab);

                foreach ($settings as $key => $value) {
                    if (is_string($value) && strpos($value, ',') !== false) {
                        if (in_array($key, ['notify_on_error_types', 'show_to_groups', 'hide_from_groups'])) {
                            $settings[$key] = explode(',', $value);
                        }
                    }
                }
                
                $defaultSettings = $this->getDefaultSettings($activeTab);
                $settings = array_merge($defaultSettings, $settings);
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    try {
                        if (class_exists('SettingsHelper')) {
                            \SettingsHelper::clearCache($activeTab);
                        }
                        
                        $postSettings = $_POST['settings'] ?? [];
                        
                        if ($activeTab === 'site' || $activeTab === 'general') {
                            $postSettings = $this->handleBackupSettings($postSettings);
                            $postSettings = $this->handleFaviconUpload($postSettings);
                        }
                        
                        $this->settingsModel->save($activeTab, $postSettings);
                        
                        if (class_exists('SettingsHelper')) {
                            \SettingsHelper::clearCache($activeTab);
                        }
                        
                        if ($activeTab === 'site') {
                            if (isset($postSettings['site_template'])) {
                                $this->updateConfigTemplate($postSettings['site_template']);
                            }
                            if (isset($postSettings['base_url'])) {
                                $this->updateConfigBaseUrl($postSettings['base_url']);
                            }
                        }
                        
                        \Notification::success(LANG_ACTION_SETTINGS_ADMININDEX_SAVE_SUCCESS);
                        $this->redirect(ADMIN_URL . '/settings?tab=' . $activeTab);
                        return;
                    } catch (\Exception $e) {
                        \Notification::error(sprintf(LANG_ACTION_SETTINGS_ADMININDEX_SAVE_ERROR, $e->getMessage()));
                    }
                }
            }
            
            if ($activeTab === 'components') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedController) {
                    try {
                        $postSettings = $_POST['settings'] ?? [];
                        
                        $postSettings = $this->handleComponentImageUploads($postSettings, $selectedController);
                        
                        $postSettings = $this->handleComponentImageDeletes($postSettings);
                        
                        $controllerManager->saveControllerSettings($selectedController, $postSettings);
                        
                        \Notification::success(LANG_ACTION_SETTINGS_ADMININDEX_CONTROLLER_SAVE_SUCCESS);
                        $this->redirect(ADMIN_URL . '/settings?tab=components&controller=' . $selectedController);
                        return;
                    } catch (\Exception $e) {
                        \Notification::error(sprintf(LANG_ACTION_SETTINGS_ADMININDEX_CONTROLLER_SAVE_ERROR, $e->getMessage()));
                    }
                }
                
                if ($selectedController) {
                    $controllerSettings = $controllerManager->getControllerSettings($selectedController);
                    $controller = $controllerManager->getController($selectedController);
                    
                    if ($controller) {
                        $controllerInstance = new $controller['class']($this->db);
                        $defaultControllerSettings = method_exists($controllerInstance, 'getDefaultSettings') 
                            ? $controllerInstance->getDefaultSettings() 
                            : [];
                        $settings = array_merge($defaultControllerSettings, $controllerSettings);
                    }
                }
            }
            
            $this->render('admin/settings/index', [
                'settings' => $settings,
                'activeTab' => $activeTab,
                'selectedController' => $selectedController,
                'controllerManager' => $controllerManager,
                'pageTitle' => LANG_ACTION_SETTINGS_ADMININDEX_PAGE_TITLE
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_SETTINGS_ADMININDEX_LOAD_ERROR, $e->getMessage()));
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
    * Обрабатывает загрузку изображений для компонентов
    */
    private function handleComponentImageUploads($postSettings, $controllerName) {
        
        foreach ($_FILES as $fieldName => $file) {
            
            if (strpos($fieldName, '_file') !== false && $file['error'] === UPLOAD_ERR_OK) {
                $baseFieldName = str_replace('_file', '', $fieldName);
                
                try {
                    $imageName = $this->handleComponentImageUpload($file, $controllerName);
                    
                    $postSettings[$baseFieldName] = $imageName;
                    
                } catch (\Exception $e) {
                    \Notification::error(sprintf(LANG_ACTION_SETTINGS_ADMININDEX_IMAGE_UPLOAD_ERROR, $baseFieldName, $e->getMessage()));
                }
            }
        }
        
        return $postSettings;
    }
    
    /**
    * Обрабатывает удаление изображений для компонентов
    */
    private function handleComponentImageDeletes($postSettings) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'remove_') === 0 && $value == '1') {
                $fieldName = str_replace('remove_', '', $key);
                
                try {
                    if (!empty($postSettings[$fieldName])) {
                        $this->handleComponentImageDelete($postSettings[$fieldName]);
                    }
                    
                    $postSettings[$fieldName] = '';
                    
                } catch (\Exception $e) {
                    \Notification::error(sprintf(LANG_ACTION_SETTINGS_ADMININDEX_IMAGE_DELETE_ERROR, $fieldName, $e->getMessage()));
                }
            }
        }
        
        return $postSettings;
    }
    
    /**
    * Загружает изображение для компонента
    */
    private function handleComponentImageUpload($file, $controllerName) {
        $uploadDir = UPLOADS_PATH . '/settings/' . $controllerName . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new \Exception(LANG_ACTION_SETTINGS_ADMININDEX_INVALID_IMAGE_TYPE);
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \Exception(LANG_ACTION_SETTINGS_ADMININDEX_IMAGE_TOO_LARGE);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . $this->generateSlug(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception(LANG_ACTION_SETTINGS_ADMININDEX_IMAGE_UPLOAD_FAILED);
        }
        
        return $fileName;
    }
    
    /**
    * Удаляет изображение компонента
    */
    private function handleComponentImageDelete($fileName) {
        if ($fileName) {
            $possiblePaths = [
                UPLOADS_PATH . '/settings/tags/' . $fileName,
                UPLOADS_PATH . '/settings/categories/' . $fileName,
                UPLOADS_PATH . '/settings/posts/' . $fileName,
            ];
            
            foreach ($possiblePaths as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                    break;
                }
            }
        }
    }
    
    /**
    * Генерирует slug для имени файла
    */
    private function generateSlug($string) {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        );
        
        $string = strtr(mb_strtolower($string), $converter);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }

    /**
    * Обрабатывает загрузку favicon
    */
    private function handleFaviconUpload($postSettings) {

        $currentSettings = $this->settingsModel->get('site');
        
        if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $file = $_FILES['favicon_file'];
                
                $allowedTypes = ['ico', 'png', 'svg', 'jpg', 'jpeg', 'gif', 'webp'];
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($extension, $allowedTypes)) {
                    throw new \Exception(LANG_ACTION_SETTINGS_ADMININDEX_FAVICON_INVALID_TYPE);
                }
                
                $uploadDir = UPLOADS_PATH . '/favicon/';
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        throw new \Exception(LANG_ACTION_SETTINGS_ADMININDEX_UPLOAD_DIR_ERROR);
                    }
                }
                
                $oldFavicon = $currentSettings['favicon'] ?? $postSettings['favicon'] ?? null;
                if (!empty($oldFavicon)) {
                    $oldFile = BASE_PATH . '/' . ltrim($oldFavicon, '/');
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                
                $fileName = 'favicon_' . time() . '_' . uniqid() . '.' . $extension;
                $targetPath = $uploadDir . $fileName;
                
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new \Exception(LANG_ACTION_SETTINGS_ADMININDEX_FAVICON_SAVE_ERROR);
                }
                
                $postSettings['favicon'] = 'uploads/favicon/' . $fileName;
                
                \Notification::success(LANG_ACTION_SETTINGS_ADMININDEX_FAVICON_SUCCESS);
                
            } catch (\Exception $e) {
                \Notification::error(sprintf(LANG_ACTION_SETTINGS_ADMININDEX_FAVICON_ERROR, $e->getMessage()));
            }
        }
        
        if (isset($_POST['remove_favicon']) && $_POST['remove_favicon'] == '1') {
            $oldFavicon = $currentSettings['favicon'] ?? $postSettings['favicon'] ?? null;
            if (!empty($oldFavicon)) {
                $oldFile = BASE_PATH . '/' . ltrim($oldFavicon, '/');
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $postSettings['favicon'] = '';
            \Notification::success(LANG_ACTION_SETTINGS_ADMININDEX_FAVICON_REMOVED);
        }
        
        return $postSettings;
    }

}