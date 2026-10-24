<?php
/**
 * Template Name: Список постов
 */

$templatePath = TEMPLATES_PATH . '/' . (function_exists('get_current_template') ? get_current_template() : DEFAULT_TEMPLATE);
?>
<div class="tg-posts-page">
    <div class="tg-container">
        <div class="tg-two-columns">
            <div class="tg-posts-main">
                <div class="tg-posts-card">
                    <div class="tg-page-header">
                        <h1 class="tg-page-title"><?php echo LANG_TEMPLATE_POSTS_LIST_TITLE; ?></h1>
                        <p class="tg-page-subtitle"><?php echo LANG_TEMPLATE_POSTS_LIST_SUBTITLE; ?></p>
                    </div>

                    <?php if (!empty($posts)) { ?>
                        <div class="tg-posts-list"
                             data-base-url="<?php echo BASE_URL; ?>"
                             data-logged-in="<?php echo isset($_SESSION['user_id']) ? '1' : '0'; ?>">
                            <?php foreach ($posts as $post) {
                                include $templatePath . '/front/posts/_card.php';
                            } ?>
                        </div>

                        <?php
                        $paginationBase = BASE_URL . '/posts';
                        include $templatePath . '/front/posts/_pagination.php';
                        ?>
                    <?php } else { ?>
                        <div class="tg-empty-state">
                            <?php echo bloggy_icon('bs', 'file-text', '48', '#cbd5e0'); ?>
                            <p><?php echo LANG_TEMPLATE_POSTS_LIST_EMPTY_TEXT; ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="tg-posts-sidebar">
                <div class="tg-sidebar-card">
                    <div class="tg-sidebar-placeholder">
                        расположите тут контент-блоки...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo add_frontend_js('/templates/default/front/assets/js/post-list.js'); ?>
