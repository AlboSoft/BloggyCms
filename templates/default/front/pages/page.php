<?php
/**
 * Template Name: Шаблон статичной страницы
 */
?>

<div class="tg-page">
    <div class="tg-container">
        
        <div class="tg-two-columns">
            <div class="tg-page-main">
                <div class="tg-page-card">
                    <div class="tg-page-header tg-mb-4">
                        <h1 class="tg-page-title"><?php echo html($page['title']); ?></h1>
                        <?php if (!empty($page['updated_at'])) { ?>
                            <div class="tg-page-meta">
                                <span class="tg-meta-item">
                                    <?php echo bloggy_icon('bs', 'calendar', '14', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo sprintf(LANG_TEMPLATE_PAGE_UPDATED_AT, date('d.m.Y', strtotime($page['updated_at']))); ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="tg-page-content">
                        <?php if (!empty($blocks)) { ?>
                            <?php foreach ($blocks as $block) { ?>
                                <div class="tg-page-block tg-page-block-<?php echo $block['type']; ?> tg-mb-4">
                                    <?php 
                                    if (is_array($block['content'])) {
                                        echo BlockRenderer::render($block);
                                    } else {
                                        echo $block['content'];
                                    }
                                    ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        
                        <?php
                            $fieldModel = new FieldModel($this->db);
                            $customFields = $fieldModel->getActiveByEntityType('page');
                        ?>
                        
                        <?php if (!empty($customFields)) { 
                            $hasValues = false;
                            foreach ($customFields as $field) { 
                                $value = $fieldModel->getFieldValue('page', $page['id'], $field['system_name']);
                                if (!empty($value)) {
                                    $hasValues = true;
                                    break;
                                }
                            }
                        ?>
                        
                        <?php if ($hasValues) { ?>
                            <div class="tg-custom-fields">
                                <?php foreach ($customFields as $field) { 
                                    $value = $fieldModel->getFieldValue('page', $page['id'], $field['system_name']);
                                    if (!empty($value)) { 
                                ?>
                                <div class="tg-custom-field-block">
                                    <span class="tg-custom-field-label"><?php echo html($field['name']); ?>:</span>
                                    <span class="tg-custom-field-value">
                                        <?php echo $fieldModel->renderFieldDisplay($field, $value, 'page', $page['id']); ?>
                                    </span>
                                </div>
                                <?php } } ?>
                            </div>
                        <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
            <div class="tg-page-sidebar">
                <div class="tg-sidebar-card">
                    <div class="tg-sidebar-widget">
                        <div class="tg-sidebar-placeholder">
                            <?php echo render_html_block('tags-sidebar'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>