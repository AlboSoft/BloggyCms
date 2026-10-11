<?php

namespace addons\actions;

/**
* Действие загрузки и установки пакета (AJAX)
* @package addons\actions
*/
class AdminUpload extends AddonAction {
    
    const TEMP_DIR = UPLOADS_PATH . '/temp_addon/';
    
    /**
    * Метод выполнения
    */
    public function execute() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_INVALID_METHOD);
            }
            
            if (!isset($_FILES['addon_file']) || $_FILES['addon_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_FILE_NOT_UPLOADED);
            }
            
            $file = $_FILES['addon_file'];
            
            $fileInfo = pathinfo($file['name']);
            if (strtolower($fileInfo['extension'] ?? '') !== 'zip') {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_INVALID_FORMAT);
            }
            
            $maxSize = 50 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_FILE_TOO_LARGE);
            }
            
            $this->createTempDir();
            
            $zipPath = self::TEMP_DIR . 'package.zip';
            if (!move_uploaded_file($file['tmp_name'], $zipPath)) {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_SAVE_ERROR);
            }
            
            $extractPath = self::TEMP_DIR . 'extracted/';
            $this->extractZip($zipPath, $extractPath);
            
            $this->validatePackageStructure($extractPath);
            
            $packageInfo = $this->parsePackageIni($extractPath . 'package.ini');
            
            $this->validatePackageType($packageInfo, $extractPath);
            
            if ($packageInfo['type'] === 'update') {
                $this->validateVersionForUpdate($packageInfo);
            } else {
                $this->validateVersionForInstall($packageInfo);
            }
            
            $backupInfo = $this->createBackup($extractPath . 'files/');
            
            $this->copyFiles($extractPath . 'files/');
            
            $this->executeInstallScript($extractPath, $packageInfo);
            
            $this->registerPackage($packageInfo);
            
            $this->cleanupTempDir();
            
            echo json_encode([
                'success' => true,
                'message' => LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_SUCCESS,
                'package' => $packageInfo
            ]);
            
        } catch (\Exception $e) {
            $this->cleanupTempDir();
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
    * Создание временной директории
    */
    private function createTempDir() {
        if (is_dir(self::TEMP_DIR)) {
            $this->deleteDirectory(self::TEMP_DIR);
        }
        mkdir(self::TEMP_DIR, 0755, true);
        mkdir(self::TEMP_DIR . 'extracted/', 0755, true);
        mkdir(self::TEMP_DIR . 'backup/', 0755, true);
    }
    
    /**
    * Удаление директории рекурсивно
    * @param string $dir
    */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(@scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    /**
    * Очистка временной директории
    */
    private function cleanupTempDir() {
        if (is_dir(self::TEMP_DIR)) {
            $this->deleteDirectory(self::TEMP_DIR);
        }
    }
    
    /**
    * Распаковка ZIP-архива
    * @param string $zipPath
    * @param string $extractPath
    * @throws \Exception
    */
    private function extractZip($zipPath, $extractPath) {
        if (!class_exists('ZipArchive')) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_NO_ZIPARCHIVE);
        }
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_CANT_OPEN_ZIP);
        }
        
        $zip->extractTo($extractPath);
        $zip->close();
    }
    
    /**
    * Валидация структуры пакета 
    * @param string $path
    * @throws \Exception
    */
    private function validatePackageStructure($path) {
        if (!is_dir($path . 'files/')) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_MISSING_FILES_DIR);
        }
        
        if (!file_exists($path . 'package.ini')) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_MISSING_PACKAGE_INI);
        }
    }
    
    /**
    * Парсинг файла package.ini 
    * @param string $iniPath
    * @return array
    * @throws \Exception
    */
    private function parsePackageIni($iniPath) {
        $content = file_get_contents($iniPath);
        if (!$content) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_CANT_READ_INI);
        }
        
        $data = parse_ini_string($content, true, INI_SCANNER_RAW);
        if ($data === false) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_INVALID_INI_FORMAT);
        }
        
        if (!isset($data['info']) || empty($data['info']['title'])) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_MISSING_TITLE);
        }
        
        $title = trim($data['info']['title']);
        if (strlen($title) > 64) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_TITLE_TOO_LONG);
        }
        
        $systemName = $this->generateSystemName($title);
        
        if (!isset($data['version']) || 
            !isset($data['version']['major']) || 
            !isset($data['version']['minor']) || 
            !isset($data['version']['build'])) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_MISSING_VERSION);
        }
        
        $versionMajor = (int)$data['version']['major'];
        $versionMinor = (int)$data['version']['minor'];
        $versionBuild = (int)$data['version']['build'];
        $versionString = "{$versionMajor}.{$versionMinor}.{$versionBuild}";
        
        $result = [
            'system_name' => $systemName,
            'title' => $title,
            'version_major' => $versionMajor,
            'version_minor' => $versionMinor,
            'version_build' => $versionBuild,
            'version_string' => $versionString,
            'version_date' => $data['version']['date'] ?? date('Y-m-d'),
            'type' => null,
            'author_name' => $data['author']['name'] ?? null,
            'author_url' => $data['author']['url'] ?? null,
            'author_email' => $data['author']['email'] ?? null,
            'description' => null
        ];
        
        if (isset($data['description']['text'])) {
            if (is_array($data['description']['text'])) {
                $result['description'] = implode("\n", $data['description']['text']);
            } else {
                $result['description'] = $data['description']['text'];
            }
        }
        
        if (isset($data['install'])) {
            $result['type'] = 'install';
            $result['install_info'] = $data['install'];
        } elseif (isset($data['update'])) {
            $result['type'] = 'update';
            $result['update_info'] = $data['update'];
        } else {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_MISSING_INSTALL_OR_UPDATE);
        }
        
        if ($result['type'] === 'install') {
            if (empty($data['install']['type']) || $data['install']['type'] !== 'install') {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_INVALID_INSTALL_TYPE);
            }
        } else {
            if (empty($data['update']['type']) || $data['update']['type'] !== 'update') {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_INVALID_UPDATE_TYPE);
            }
            if (empty($data['update']['version'])) {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_MISSING_UPDATE_VERSION);
            }
        }
        
        return $result;
    }
    
    /**
    * Генерация системного имени из названия
    * @param string $title
    * @return string
    */
    private function generateSystemName($title) {
        $name = mb_strtolower($title, 'UTF-8');
        
        $cyr = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            ' ', '_', '.', ',', '(', ')', '[', ']', '{', '}', '!', '@', '#', '$', '%', '^', '&', '*', '+', '='
        ];
        $lat = [
            'a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
            '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'
        ];
        
        $name = str_replace($cyr, $lat, $name);
        $name = preg_replace('/[^a-z0-9_-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');
        
        return $name;
    }
    
    /**
    * Проверка типа пакета и наличие install.php 
    * @param array $packageInfo
    * @param string $path
    * @throws \Exception
    */
    private function validatePackageType($packageInfo, $path) {
        $hasInstallPhp = file_exists($path . 'install.php');
        
        if ($packageInfo['type'] === 'install' && !$hasInstallPhp) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_INSTALL_REQUIRES_INSTALL_PHP);
        }
        
        if ($packageInfo['type'] === 'update' && !$hasInstallPhp) {
            // Для обновления install.php не обязателен
            // Можно просто обновить файлы и обновить информацию в БД
        }
    }
    
    /**
    * Проверка версии для установки 
    * @param array $packageInfo
    * @throws \Exception
    */
    private function validateVersionForInstall($packageInfo) {
        if ($this->addonModel->exists($packageInfo['system_name'])) {
            throw new \Exception(sprintf(
                LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_ALREADY_INSTALLED,
                $packageInfo['title']
            ));
        }
    }
    
    /**
    * Проверка версии для обновления
    * @param array $packageInfo
    * @throws \Exception
    */
    private function validateVersionForUpdate($packageInfo) {
        $installedVersion = $this->addonModel->getInstalledVersion($packageInfo['system_name']);
        
        if (!$installedVersion) {
            throw new \Exception(sprintf(
                LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_NOT_INSTALLED,
                $packageInfo['title']
            ));
        }
        
        $versionFrom = $packageInfo['update_info']['version'] ?? null;
        if ($versionFrom && $versionFrom !== $installedVersion) {
            throw new \Exception(sprintf(
                LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_VERSION_MISMATCH,
                $installedVersion,
                $versionFrom
            ));
        }
    }
    
    /**
    * Создание резервной копии файлов
    * @param string $filesPath
    * @return array
    */
    private function createBackup($filesPath) {
        $backupDir = self::TEMP_DIR . 'backup/';
        $backupInfo = [
            'files' => [],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->copyFilesRecursive($filesPath, BASE_PATH, $backupDir, true);
        
        return $backupInfo;
    }
    
    /**
    * Копирование файлов из пакета в систему 
    * @param string $source
    */
    private function copyFiles($source) {
        $this->copyFilesRecursive($source, BASE_PATH);
    }
    
    /**
    * Рекурсивное копирование файлов 
    * @param string $source
    * @param string $destination
    * @param string|null $backupDir
    * @param bool $createBackup
    */
    private function copyFilesRecursive($source, $destination, $backupDir = null, $createBackup = false) {
        if (!is_dir($source)) {
            return;
        }
        
        $items = @scandir($source) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $sourcePath = $source . '/' . $item;
            $destPath = $destination . '/' . $item;
            
            if (is_dir($sourcePath)) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                $this->copyFilesRecursive($sourcePath, $destPath, $backupDir, $createBackup);
            } else {
                if ($createBackup && $backupDir && file_exists($destPath)) {
                    $backupFile = $backupDir . $item;
                    $dir = dirname($backupFile);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    copy($destPath, $backupFile);
                }
                
                copy($sourcePath, $destPath);
            }
        }
    }
    
    /**
    * Выполнение install.php скрипта
    * @param string $path
    * @param array $packageInfo
    * @throws \Exception
    */
    private function executeInstallScript($path, $packageInfo) {
        $installFile = $path . 'install.php';
        
        if (!file_exists($installFile)) {
            return;
        }
        
        $db = $this->db;
        $package = $packageInfo;
        
        ob_start();
        
        try {

            $installResult = null;
            
            $getPrefix = function() use ($db) {
                return $db->getPrefix();
            };
            
            $installResult = (function($db, $package, $getPrefix) use ($installFile) {
                extract(['db' => $db, 'package' => $package, 'getPrefix' => $getPrefix]);
                return require $installFile;
            })($db, $package, $getPrefix);
            
            $output = ob_get_clean();
            
            if ($output) {
                \Logger::info('Install script output: ' . $output);
            }
            
            if ($installResult === false) {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_INSTALL_SCRIPT_FAILED);
            }
            
        } catch (\Exception $e) {
            ob_end_clean();
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_UPLOAD_INSTALL_SCRIPT_ERROR . $e->getMessage());
        }
    }
    
    /**
    * Регистрация пакета в базе данных
    * @param array $packageInfo
    */
    private function registerPackage($packageInfo) {
        if ($packageInfo['type'] === 'update') {
            $this->addonModel->update($packageInfo['system_name'], $packageInfo);
        } else {
            $this->addonModel->register($packageInfo);
        }
    }
}