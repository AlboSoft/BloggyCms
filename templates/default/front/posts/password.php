<?php
/**
 * Template Name: Страница поста, защищенного паролем
 */
?>

<div class="tg-password-page">
    <div class="tg-container tg-container-sm">
        
        <div class="tg-card">
            <div class="tg-card-body">
                
                <div class="tg-password-icon tg-text-center tg-mb-3">
                    <?php echo bloggy_icon('bs', 'lock-fill', '48', 'var(--tg-primary)'); ?>
                </div>
                
                <h1 class="tg-password-title tg-text-center tg-mb-2">
                    <?php echo LANG_TEMPLATE_POST_PASSWORD_TITLE; ?>
                </h1>
                
                <div class="tg-password-post-title tg-text-center tg-mb-4">
                    <span class="tg-text-muted"><?php echo LANG_TEMPLATE_POST_PASSWORD_RESTRICTED_LABEL; ?></span>
                    <strong class="tg-post-title-block"><?php echo html($post['title']); ?></strong>
                </div>
                
                <?php if (!empty($post['short_description'])) { ?>
                    <p class="tg-password-description tg-text-center tg-text-muted tg-mb-4">
                        <?php echo html($post['short_description']); ?>
                    </p>
                <?php } ?>
                
                <?php if ($error) { ?>
                    <div class="tg-alert tg-alert-error tg-mb-4">
                        <div class="tg-alert-icon">
                            <?php echo bloggy_icon('bs', 'exclamation-triangle', '18', 'currentColor'); ?>
                        </div>
                        <div class="tg-alert-content">
                            <strong><?php echo LANG_TEMPLATE_POST_PASSWORD_ERROR_TITLE; ?></strong>
                            <p class="tg-mb-0"><?php echo LANG_TEMPLATE_POST_PASSWORD_ERROR_TEXT; ?></p>
                        </div>
                    </div>
                <?php } ?>
                
                <form method="post" action="<?php echo BASE_URL; ?>/post/check-password/<?php echo $post['id']; ?>" class="tg-password-form">
                    <input type="hidden" name="redirect" value="<?php echo BASE_URL; ?>/post/<?php echo html($post['slug']); ?>">
                    
                    <div class="tg-field tg-mb-4">
                        <label for="password" class="tg-label"><?php echo LANG_TEMPLATE_POST_PASSWORD_LABEL; ?></label>
                        <div class="tg-input-wrapper">
                            <span class="tg-input-icon">
                                <?php echo bloggy_icon('bs', 'key', '16', 'currentColor'); ?>
                            </span>
                            <input type="password" 
                                   id="password"
                                   name="password" 
                                   class="tg-input" 
                                   placeholder="<?php echo LANG_TEMPLATE_POST_PASSWORD_PLACEHOLDER; ?>" 
                                   required
                                   autofocus>
                        </div>
                        <div class="tg-field-hint">
                            <?php echo bloggy_icon('bs', 'info-circle', '12', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_POST_PASSWORD_HINT; ?>
                        </div>
                    </div>
                    
                    <div class="tg-password-actions">
                        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block">
                            <?php echo bloggy_icon('bs', 'unlock', '16', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_POST_PASSWORD_UNLOCK_BTN; ?>
                        </button>
                        
                        <a href="<?php echo BASE_URL; ?>/posts" class="tg-password-back-link tg-text-center tg-mt-3">
                            <?php echo bloggy_icon('bs', 'arrow-left', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_POST_PASSWORD_BACK_BTN; ?>
                        </a>
                    </div>
                </form>
                
            </div>
        </div>
        
        <div class="tg-password-help tg-text-center tg-mt-4">
            <small class="tg-text-muted">
                <?php echo bloggy_icon('bs', 'shield-check', '12', 'currentColor', 'tg-mr-1'); ?>
                <?php echo LANG_TEMPLATE_POST_PASSWORD_HELP_TEXT; ?>
            </small>
        </div>
        
    </div>
</div>