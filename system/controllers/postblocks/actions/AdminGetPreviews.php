<?php

namespace postblocks\actions;

/**
* Пакетное получение HTML-предпросмотра нескольких постблоков за один запрос.
*
* Используется конструктором контента: вместо N запросов к adminGetPreview
* редактор отправляет один запрос со списком блоков и получает карту
* «идентификатор блока => HTML».
*
* @package postblocks\actions
* @extends PostBlockAction
*/
class AdminGetPreviews extends PostBlockAction {

    /**
    * Максимальное количество блоков, обрабатываемых за один запрос
    */
    const MAX_BLOCKS_PER_REQUEST = 40;

    /**
    * Метод выполнения пакетного получения предпросмотров
    * @return void
    */
    public function execute() {

        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $items = (is_array($input) && isset($input['blocks']) && is_array($input['blocks']))
                ? $input['blocks']
                : [];

            if (empty($items)) {
                throw new \Exception(LANG_ACTION_POSTBLOCKS_ADMINGETPREVIEWS_BLOCKS_NOT_SPECIFIED);
            }

            $previews = [];
            $loadedAssets = [];

            foreach (array_slice($items, 0, self::MAX_BLOCKS_PER_REQUEST) as $item) {
                if (!is_array($item) || empty($item['block_type']) || !isset($item['block_id'])) {
                    continue;
                }

                $blockType = $item['block_type'];
                $blockInstance = $this->postBlockManager->getBlockInstance($blockType);

                if (!$blockInstance) {
                    continue;
                }

                if (!isset($loadedAssets[$blockType])) {
                    $blockInstance->loadPreviewAssets();
                    $loadedAssets[$blockType] = true;
                }

                $html = $blockInstance->getPreviewHtml(
                    isset($item['content']) ? $item['content'] : [],
                    isset($item['settings']) ? $item['settings'] : []
                );

                if (!empty($item['block_id'])) {
                    $html = str_replace('{block_id}', (string)$item['block_id'], $html);
                }

                $previews[(string)$item['block_id']] = $html;
            }

            echo json_encode([
                'success' => true,
                'previews' => (object)$previews
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'previews' => (object)[]
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }
}
