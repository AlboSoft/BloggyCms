<?php
/**
 * Template Name: Страница - Habr Pro
 */
?>
<div class="tg-page">
    <div class="tg-container">
        <div class="tg-two-columns">
            <div class="tg-page-main">
                <div class="tg-page-card">
                    <div class="tg-page-header">
                        <h1 class="tg-page-title"><?php echo html($page['title']); ?></h1>
                        <?php if (!empty($page['updated_at'])) { ?>
                            <div class="tg-page-meta"><span class="tg-meta-item">обновлено <?php echo date('d.m.Y', strtotime($page['updated_at'])); ?></span></div>
                        <?php } ?>
                    </div>
                    <div class="tg-page-content">
                        <?php if (!empty($blocks)) { ?>
                            <?php foreach ($blocks as $block) { ?>
                                <div class="tg-page-block tg-page-block-<?php echo $block['type']; ?>">
                                    <?php echo is_array($block['content']) ? BlockRenderer::render($block) : $block['content']; ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <?php
                            $fieldModel = new FieldModel($this->db);
                            $customFields = $fieldModel->getActiveByEntityType('page');
                            $hasValues = false;
                            foreach ($customFields as $field) {
                                $value = $fieldModel->getFieldValue('page', $page['id'], $field['system_name']);
                                if (!empty($value)) { $hasValues = true; break; }
                            }
                        ?>
                        <?php if (!empty($customFields) && $hasValues) { ?>
                            <div class="tg-custom-fields">
                                <?php foreach ($customFields as $field) { 
                                    $value = $fieldModel->getFieldValue('page', $page['id'], $field['system_name']);
                                    if (!empty($value)) { 
                                ?>
                                <div class="tg-custom-field-block">
                                    <span class="tg-custom-field-label"><?php echo html($field['name']); ?>:</span>
                                    <span class="tg-custom-field-value"><?php echo $fieldModel->renderFieldDisplay($field, $value, 'page', $page['id']); ?></span>
                                </div>
                                <?php } } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="tg-page-sidebar">
                <div class="tg-sidebar-card">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">разделы</div>
                    <div class="tg-sidebar-placeholder"><?php echo render_html_block('tags-sidebar'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
