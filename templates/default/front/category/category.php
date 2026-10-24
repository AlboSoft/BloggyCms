<?php
/**
* Template Name: Страница категории
*/

$templatePath = TEMPLATES_PATH . '/' . (function_exists('get_current_template') ? get_current_template() : DEFAULT_TEMPLATE);

$hasAccess = $hasAccess ?? true;
$totalPages = (int)($total_pages ?? 0);
$pagination = [
    'current_page' => (int)($current_page ?? 1),
    'total_pages' => $totalPages,
];
?>
<div class="tg-category-page">
    <div class="tg-container">

        <div class="tg-category-header tg-mb-4">
            <div class="tg-category-header-left">
                <div class="tg-category-icon-large">
                    <?php if (!empty($category['image'])) { ?>
                        <img src="<?php echo BASE_URL . '/uploads/images/' . html($category['image']); ?>"
                             alt="<?php echo html($category['name']); ?>">
                    <?php } else { ?>
                        <?php echo bloggy_icon('bs', 'folder-fill', '28', 'var(--tg-primary)'); ?>
                    <?php } ?>
                </div>
                <div class="tg-category-info">
                    <h1 class="tg-category-title">
                        <?php echo html($category['name']); ?>
                        <?php if (!empty($category['password_protected'])) { ?>
                            <span class="tg-category-protected" title="<?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_PROTECTED_TITLE; ?>">
                                <?php echo bloggy_icon('bs', 'lock-fill', '16', 'currentColor'); ?>
                            </span>
                        <?php } ?>
                    </h1>

                    <?php if (!empty($category['description'])) { ?>
                        <p class="tg-category-description tg-text-muted">
                            <?php echo html($category['description']); ?>
                        </p>
                    <?php } ?>

                    <div class="tg-category-meta">
                        <span class="tg-meta-item">
                            <?php echo bloggy_icon('bs', 'file-text', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo ($total = (int)($total_posts ?? count($posts ?? []))) . ' ' . plural($total, [LANG_TEMPLATE_CATEGORY_PUBLICATION_1, LANG_TEMPLATE_CATEGORY_PUBLICATION_2, LANG_TEMPLATE_CATEGORY_PUBLICATION_3]); ?>
                        </span>

                        <?php if (!empty($category['created_at'])) { ?>
                            <span class="tg-meta-item">
                                <?php echo bloggy_icon('bs', 'calendar', '14', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo sprintf(LANG_TEMPLATE_CATEGORY_CREATED_AT, date('d.m.Y', strtotime($category['created_at']))); ?>
                            </span>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="tg-category-actions">
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">
                    <?php echo bloggy_icon('bs', 'grid-3x3-gap', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_CATEGORY_ALL_POSTS_BTN; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline tg-btn-sm">
                    <?php echo bloggy_icon('bs', 'folder', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_CATEGORY_ALL_CATEGORIES_BTN; ?>
                </a>
            </div>
        </div>

        <?php if (!empty($category['password_protected']) && !$hasAccess) { ?>

            <div class="tg-password-card tg-mb-4">
                <div class="tg-password-icon">
                    <?php echo bloggy_icon('bs', 'shield-lock', '48', 'var(--tg-primary)'); ?>
                </div>
                <h2 class="tg-password-title"><?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_TITLE; ?></h2>
                <p class="tg-password-text tg-text-muted">
                    <?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_TEXT; ?>
                </p>

                <form id="tg-category-password-form" class="tg-password-form">
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">

                    <div class="tg-field tg-mb-3">
                        <div class="tg-input-wrapper">
                            <span class="tg-input-icon">
                                <?php echo bloggy_icon('bs', 'key', '16', 'currentColor'); ?>
                            </span>
                            <input type="password"
                                name="password"
                                class="tg-input"
                                placeholder="<?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_PLACEHOLDER; ?>"
                                required>
                        </div>
                    </div>

                    <div class="tg-password-actions">
                        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block" id="tg-submit-password">
                            <?php echo bloggy_icon('bs', 'unlock', '16', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_UNLOCK_BTN; ?>
                        </button>
                    </div>

                    <div id="tg-password-error" class="tg-alert tg-alert-error tg-mt-3" style="display: none;"></div>
                </form>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('tg-category-password-form');
                const errorDiv = document.getElementById('tg-password-error');
                const submitBtn = document.getElementById('tg-submit-password');

                if (!form) {
                    return;
                }

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const originalText = submitBtn.innerHTML;

                    submitBtn.innerHTML = '<?php echo bloggy_icon('bs', 'hourglass-split', '14', 'currentColor', 'tg-mr-1'); ?> <?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_CHECKING; ?>';
                    submitBtn.disabled = true;
                    errorDiv.style.display = 'none';

                    fetch('<?php echo BASE_URL; ?>/category/check-password/<?php echo $category['id']; ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (data.success) {
                            submitBtn.innerHTML = '<?php echo bloggy_icon('bs', 'check', '14', 'currentColor', 'tg-mr-1'); ?> <?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_SUCCESS; ?>';
                            setTimeout(function () { location.reload(); }, 1000);
                        } else {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;

                            errorDiv.textContent = data.message || '<?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_ERROR; ?>';
                            errorDiv.style.display = 'flex';

                            const input = form.querySelector('input[name="password"]');
                            input.classList.add('tg-error');
                            input.value = '';

                            setTimeout(function () {
                                input.classList.remove('tg-error');
                                errorDiv.style.display = 'none';
                            }, 3000);
                        }
                    })
                    .catch(function () {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;

                        errorDiv.textContent = '<?php echo LANG_TEMPLATE_CATEGORY_PASSWORD_CHECK_ERROR; ?>';
                        errorDiv.style.display = 'flex';
                    });
                });
            });
            </script>

        <?php } else { ?>

            <?php if (!empty($posts)) { ?>
                <div class="tg-posts-card">
                    <div class="tg-posts-list"
                         data-base-url="<?php echo BASE_URL; ?>"
                         data-logged-in="<?php echo isset($_SESSION['user_id']) ? '1' : '0'; ?>">
                        <?php foreach ($posts as $post) {
                            include $templatePath . '/front/posts/_card.php';
                        } ?>
                    </div>

                    <?php
                    $paginationBase = BASE_URL . '/category/' . html($category['slug']);
                    include $templatePath . '/front/posts/_pagination.php';
                    ?>
                </div>
            <?php } else { ?>
                <div class="tg-empty-state">
                    <div class="tg-empty-state-icon">
                        <?php echo bloggy_icon('bs', 'folder2-open', '48', 'var(--tg-text-secondary)'); ?>
                    </div>
                    <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_CATEGORY_EMPTY_TITLE; ?></h3>
                    <p class="tg-empty-state-text tg-text-muted">
                        <?php echo LANG_TEMPLATE_CATEGORY_EMPTY_TEXT; ?>
                    </p>
                    <div class="tg-empty-actions">
                        <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                            <?php echo bloggy_icon('bs', 'grid-3x3-gap', '16', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_CATEGORY_EMPTY_ALL_POSTS_BTN; ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline tg-ml-2">
                            <?php echo bloggy_icon('bs', 'folder', '16', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_CATEGORY_EMPTY_ALL_CATEGORIES_BTN; ?>
                        </a>
                    </div>
                </div>
            <?php } ?>

        <?php } ?>

    </div>
</div>

<?php
ob_start();
?>
<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<?php
front_bottom_js(ob_get_clean());
echo add_frontend_js('/templates/default/front/assets/js/post-list.js');
?>
