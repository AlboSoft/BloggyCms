<?php
/**
 * Template Name: Страница не найдена
 */
?>

<div class="tg-error-404">
    <div class="tg-container">
        <div class="tg-error-content">
            <div class="tg-error-code">404</div>
            <h1 class="tg-error-title"><?php echo LANG_TEMPLATE_ERROR_404_TITLE; ?></h1>
            <p class="tg-error-description">
                <?php echo LANG_TEMPLATE_ERROR_404_DESCRIPTION; ?>
            </p>
            <div class="tg-error-actions">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                    <?php echo bloggy_icon('bs', 'house', '16', 'currentColor', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_ERROR_404_HOME_BTN; ?>
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <?php echo bloggy_icon('bs', 'arrow-left', '16', 'currentColor', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_ERROR_404_BACK_BTN; ?>
                </a>
            </div>
        </div>
    </div>
</div>