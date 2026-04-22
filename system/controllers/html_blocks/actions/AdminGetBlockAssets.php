<?php
namespace html_blocks\actions;

/**
* Действие получения списка доступных файлов ассетов для блока
* @package html_blocks\actions
*/
class AdminGetBlockAssets extends HtmlBlockAction {
    
    /**
    * Метод выполнения получения списка файлов
    * @return void
    */
    public function execute() {
        header('Content-Type: application/json');
        
        try {
            
            $blockType = $_GET['block_type'] ?? '';
            $assetType = $_GET['asset_type'] ?? 'css';
            
            if (empty($blockType)) {
                throw new \Exception(LANG_ACTION_HTMLBLOCKS_ADMINGETBLOCKASSETS_BLOCK_TYPE_REQUIRED);
            }
            
            if (!in_array($assetType, ['css', 'js'])) {
                throw new \Exception(LANG_ACTION_HTMLBLOCKS_ADMINGETBLOCKASSETS_INVALID_ASSET_TYPE);
            }
            
            $files = $this->scanBlockAssets($blockType, $assetType);
            
            echo json_encode([
                'success' => true,
                'files' => $files,
                'asset_type' => $assetType
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'files' => []
            ]);
        }
        
        exit;
    }
    
    /**
    * Сканирует папку с ассетами блока и возвращает список файлов
    * @param string $blockType Тип блока (system_name)
    * @param string $assetType Тип ассета (css/js)
    * @return array Массив файлов с путями
    */
    private function scanBlockAssets($blockType, $assetType) {
        $files = [];
        $currentTemplate = get_current_template();
        
        $paths = [
            ROOT_PATH . "/templates/{$currentTemplate}/front/assets/html_blocks/{$blockType}/{$assetType}/",
            ROOT_PATH . "/templates/{$currentTemplate}/front/assets/html_blocks/{$assetType}/",
            ROOT_PATH . "/system/html_blocks/{$blockType}/assets/{$assetType}/",
        ];
        
        foreach ($paths as $basePath) {
            if (!is_dir($basePath)) {
                continue;
            }
            
            $scannedFiles = $this->scanDirectory($basePath, $basePath, $assetType);
            $files = array_merge($files, $scannedFiles);
        }
        
        $files = array_unique($files, SORT_REGULAR);
        
        return $files;
    }
    
    /**
    * Рекурсивно сканирует директорию на наличие файлов
    * @param string $dir Путь к директории
    * @param string $baseDir Базовая директория для относительных путей
    * @param string $assetType Тип ассета (css/js)
    * @return array Массив найденных файлов
    */
    private function scanDirectory($dir, $baseDir, $assetType) {
        $files = [];
        $extension = $assetType === 'css' ? 'css' : 'js';
        
        if (!is_dir($dir)) {
            return $files;
        }
        
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . $item;
            
            if (is_dir($path)) {
                $subFiles = $this->scanDirectory($path . '/', $baseDir, $assetType);
                $files = array_merge($files, $subFiles);
            } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === $extension) {
                $relativePath = str_replace(ROOT_PATH . '/', '', $path);
                
                $files[] = [
                    'path' => $relativePath,
                    'name' => basename($path),
                    'size' => filesize($path),
                    'modified' => date('d.m.Y H:i', filemtime($path))
                ];
            }
        }
        
        return $files;
    }
}