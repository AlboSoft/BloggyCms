<?php

namespace addons\actions;

/**
* Действие анализа пакета без установки
* @package addons\actions
*/
class AdminAnalyze extends AddonAction {

    /**
    * Временная директория для анализа
    */
    const TEMP_DIR = UPLOADS_PATH . '/temp_analyze/';
    
    /**
    * Метод выполнения
    */
    public function execute() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_METHOD);
            }
            
            if (!isset($_FILES['addon_file']) || $_FILES['addon_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_FILE_NOT_UPLOADED);
            }
            
            $file = $_FILES['addon_file'];
            
            $fileInfo = pathinfo($file['name']);
            if (strtolower($fileInfo['extension'] ?? '') !== 'zip') {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_FORMAT);
            }
            
            if (is_dir(self::TEMP_DIR)) {
                $this->deleteDirectory(self::TEMP_DIR);
            }
            mkdir(self::TEMP_DIR, 0755, true);
            
            $zipPath = self::TEMP_DIR . 'package.zip';
            if (!move_uploaded_file($file['tmp_name'], $zipPath)) {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_SAVE_ERROR);
            }
            
            $extractPath = self::TEMP_DIR . 'extracted/';
            $this->extractZip($zipPath, $extractPath);
            
            if (!file_exists($extractPath . 'package.ini')) {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_NO_PACKAGE_INI);
            }
            
            $packageInfo = $this->parsePackageIni($extractPath . 'package.ini');

            $isInstalled = $this->addonModel->exists($packageInfo['system_name']);
            if ($isInstalled) {
                $installedVersion = $this->addonModel->getInstalledVersion($packageInfo['system_name']);
                $packageInfo['is_installed'] = true;
                $packageInfo['installed_version'] = $installedVersion;
                
                if ($packageInfo['type'] === 'update') {
                    $versionFrom = $packageInfo['update_info']['version'] ?? null;
                    if ($versionFrom && $versionFrom !== $installedVersion) {
                        $packageInfo['update_available'] = false;
                        $packageInfo['update_error'] = sprintf(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_VERSION_REQUIRED, $versionFrom, $installedVersion);
                    } else {
                        $packageInfo['update_available'] = true;
                    }
                } else {
                    $packageInfo['update_available'] = false;
                    $packageInfo['update_error'] = LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_ALREADY_INSTALLED;
                }
            } else {
                $packageInfo['is_installed'] = false;
            }
            
            $this->deleteDirectory(self::TEMP_DIR);
            
            echo json_encode([
                'success' => true,
                'package' => $packageInfo
            ]);
            
        } catch (\Exception $e) {
            $this->deleteDirectory(self::TEMP_DIR);
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
    * Распаковка ZIP-архива
    */
    private function extractZip($zipPath, $extractPath) {
        if (!class_exists('ZipArchive')) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_NO_ZIPARCHIVE);
        }
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_CANT_OPEN_ZIP);
        }
        
        $zip->extractTo($extractPath);
        $zip->close();
    }
    
    /**
    * Удаление директории рекурсивно
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
    * Парсинг файла package.ini
    */
    private function parsePackageIni($iniPath) {
        $content = file_get_contents($iniPath);
        if (!$content) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_CANT_READ_INI);
        }
        
        $data = parse_ini_string($content, true, INI_SCANNER_RAW);
        if ($data === false) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_INI_FORMAT);
        }
        
        if (!isset($data['info']) || empty($data['info']['title'])) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_TITLE);
        }
        
        $title = trim($data['info']['title']);
        if (strlen($title) > 64) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_TITLE_TOO_LONG);
        }
        
        $systemName = $this->generateSystemName($title);
        
        if (!isset($data['version']) || 
            !isset($data['version']['major']) || 
            !isset($data['version']['minor']) || 
            !isset($data['version']['build'])) {
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_VERSION);
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
            'version_date' => $data['version']['date'] ?? null,
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
            throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_INSTALL_OR_UPDATE);
        }
        
        if ($result['type'] === 'install') {
            if (empty($data['install']['type']) || $data['install']['type'] !== 'install') {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_INSTALL_TYPE);
            }
        } else {
            if (empty($data['update']['type']) || $data['update']['type'] !== 'update') {
                throw new \Exception(LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_UPDATE_TYPE);
            }
        }
        
        return $result;
    }
    
    /**
    * Генерация системного имени из названия
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
}