<?php

/**
* Класс для рендеринга предпросмотра пост-блоков
* @package Core
*/
class PostBlockPreviewRenderer {
    
    /** @var PostBlockManager Менеджер пост-блоков */
    private $postBlockManager;
    
    /**
    * Конструктор класса 
    * @param PostBlockManager $postBlockManager Менеджер пост-блоков
    */
    public function __construct(PostBlockManager $postBlockManager) {
        $this->postBlockManager = $postBlockManager;
    }
    
    /**
    * Рендерит превью блока
    * @param string $blockType Тип блока
    * @param array $content Контент блока
    * @param array $settings Настройки блока
    * @return string HTML-код превью
    */
    public function renderPreview($blockType, $content = [], $settings = []): string {
        $blockInstance = $this->postBlockManager->getBlockInstance($blockType);
        
        if (!$blockInstance) {
            return $this->renderDefaultPreview($blockType, $content);
        }
        
        $blockInstance->loadPreviewAssets();
        
        if (!$blockInstance->canShowPreview()) {
            return $blockInstance->getSimplePreview($content, $settings);
        }
        
        try {
            return $blockInstance->getPreviewHtml($content, $settings);
        } catch (Exception $e) {
            return $this->renderErrorPreview($blockType, $e->getMessage());
        }
    }
    
    /**
    * Получает превью через AJAX в формате JSON
    */
    public function getPreviewViaAjax($blockType, $content = [], $settings = []): array {
        try {
            $html = $this->renderPreview($blockType, $content, $settings);
            
            return [
                'success' => true,
                'html' => $html,
                'block_type' => $blockType
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'html' => $this->renderErrorPreview($blockType, $e->getMessage())
            ];
        }
    }
    
    /**
    * Рендерит превью по умолчанию (если блок не поддерживает кастомное превью)
    * @param string $blockType Тип блока
    * @param array $content Контент блока
    * @return string HTML-код превью
    */
    private function renderDefaultPreview($blockType, $content): string {
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-default">
            <div class="preview-header bg-light p-2 border-bottom">
                <div class="d-flex align-items-center">
                    <i class="bi bi-box me-2"></i>
                    <strong><?= htmlspecialchars($blockType) ?></strong>
                </div>
            </div>
            <div class="preview-body p-3">
                <?php if (!empty($content)) { ?>
                    <div class="small text-muted">
                        <pre class="mb-0" style="font-size: 11px;"><?= htmlspecialchars(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    </div>
                <?php } else { ?>
                    <div class="text-center text-muted py-2">
                        <i class="bi bi-inbox"></i>
                        <div class="small"><?php echo LANG_HELPER_POSTBLOCKPREVIEW_NO_DATA; ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Рендерит превью с ошибкой
    * @param string $blockType Тип блока
    * @param string $error Текст ошибки
    * @return string HTML-код превью с ошибкой
    */
    private function renderErrorPreview($blockType, $error): string {
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-error border border-danger">
            <div class="preview-header bg-danger text-white p-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong><?php echo sprintf(LANG_HELPER_POSTBLOCKPREVIEW_ERROR_HEADER, htmlspecialchars($blockType)); ?></strong>
                </div>
            </div>
            <div class="preview-body p-3">
                <div class="alert alert-danger small mb-0">
                    <?= htmlspecialchars($error) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}