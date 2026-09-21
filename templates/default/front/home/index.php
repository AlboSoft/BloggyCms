<?php
/**
 * Template Name: Главная - Habr Pro Style
 * Чистая лента как на хабре, без лишнего
 */
?>
<div class="tg-home-page">
    <?php echo render_html_block('hero'); ?>
    <?php echo render_html_block('cats'); ?>
    <?php echo render_html_block('services'); ?>
    <?php echo render_html_block('posts'); ?>
    <?php echo render_html_block('tags'); ?>
    <?php echo render_html_block('feedback'); ?>
</div>
