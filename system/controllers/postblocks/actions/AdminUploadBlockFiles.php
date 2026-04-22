<?php

namespace postblocks\actions;

/**
* Действие загрузки файлов для постблока
* @package postblocks\actions
*/
class AdminUploadBlockFiles extends PostBlockAction {
    
    /**
    * Метод выполнения загрузки файлов для блока
    * @return void
    */
    public function execute() {
        
        try {
            $blockId = $_POST['block_id'] ?? '';
            $blockType = $_POST['block_type'] ?? '';
            
            if (empty($blockType)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINUPLOADBLOCKFILES_TYPE_NOT_SPECIFIED);
            }

            $contentJson = $_POST['content_json'] ?? '{}';
            $settingsJson = $_POST['settings_json'] ?? '{}';
            $content = json_decode($contentJson, true) ?: [];
            $settings = json_decode($settingsJson, true) ?: [];
            
            if (isset($_POST['preset_id']) && !isset($settings['preset_id'])) {
                $settings['preset_id'] = $_POST['preset_id'];
            }
            if (isset($_POST['preset_name']) && !isset($settings['preset_name'])) {
                $settings['preset_name'] = $_POST['preset_name'];
            }
            
            $postBlock = $this->postBlockManager->getPostBlock($blockType);
            
            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINUPLOADBLOCKFILES_BLOCK_NOT_FOUND . $blockType);
            }

            $blockInstance = $postBlock['class'];
            
            if (method_exists($blockInstance, 'prepareContent')) {
                $content = $blockInstance->prepareContent($content);
            }
            
            if (method_exists($blockInstance, 'prepareSettings')) {
                $settings = $blockInstance->prepareSettings($settings);
            }

            if (empty($content) && empty($_FILES)) {
                $this->processPostFields($content, $settings);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => LANG_ACTION_POSTBLOCKS_ADMINUPLOADBLOCKFILES_SUCCESS,
                'block_data' => [
                    'content' => $content,
                    'settings' => $settings
                ]
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'block_data' => [
                    'content' => [],
                    'settings' => []
                ]
            ]);
        }
    }

    /**
    * Обрабатывает поля формы из POST-запроса
    * Извлекает данные в формате content[ключ] и settings[ключ]
    * Поддерживает массивы (например, content[items][])
    * @param array &$content Ссылка на массив контента для заполнения
    * @param array &$settings Ссылка на массив настроек для заполнения
    * @return void
    */
    private function processPostFields(&$content, &$settings) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'content[') === 0) {
                $this->processContentField($key, $value, $content);
            } elseif (strpos($key, 'settings[') === 0) {
                $this->processSettingsField($key, $value, $settings);
            } elseif ($key === 'preset_id' || $key === 'preset_name') {
                $settings[$key] = $value;
            }
        }
    }

    /**
    * Объединяет данные пресета с настройками блока
    * @param array $settings Текущие настройки
    * @param array $content Текущий контент
    * @return array Обновленные настройки
    */
    private function mergePresetData($settings, $content) {
        if (!empty($settings['preset_id'])) {
            $presetModel = new \PostBlockModel($this->db);
            $preset = $presetModel->getPreset($settings['preset_id']);
            
            if ($preset && !empty($preset['preset_template'])) {
                $settings['applied_preset'] = [
                    'id' => $preset['id'],
                    'name' => $preset['preset_name'],
                    'template' => $preset['preset_template']
                ];
            }
        }
        
        return $settings;
    }

    /**
    * Обрабатывает одно поле контента
    * @param string $key Ключ поля (например, "content[text]" или "content[items][]")
    * @param mixed $value Значение поля
    * @param array &$content Ссылка на массив контента
    * @return void
    */
    private function processContentField($key, $value, &$content) {
        $contentKey = str_replace(['content[', ']'], '', $key);

        if (strpos($contentKey, '[]') !== false) {
            $arrayKey = str_replace('[]', '', $contentKey);
            if (!isset($content[$arrayKey])) {
                $content[$arrayKey] = [];
            }
            if (is_array($value)) {
                $content[$arrayKey] = array_merge($content[$arrayKey], $value);
            } else {
                $content[$arrayKey][] = $value;
            }
        } else {
            $content[$contentKey] = $value;
        }
    }

    /**
    * Обрабатывает одно поле настроек
    * @param string $key Ключ поля (например, "settings[align]")
    * @param mixed $value Значение поля
    * @param array &$settings Ссылка на массив настроек
    * @return void
    */
    private function processSettingsField($key, $value, &$settings) {
        $settingsKey = str_replace(['settings[', ']'], '', $key);
        $settings[$settingsKey] = $value;
    }

    /**
    * Отправляет JSON-ответ и завершает выполнение
    * @param array $data Данные для JSON-ответа
    * @return void
    */
    protected function jsonResponse($data) { 
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}