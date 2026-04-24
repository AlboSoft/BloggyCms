<?php
/**
 * Template Name: Страница авторизации
 */
?>

<div class="tg-login-page">
    <div class="tg-container tg-container-sm">
        
        <div class="tg-login-header">
            <h1 class="tg-login-title"><?php echo LANG_TEMPLATE_AUTH_LOGIN_TITLE; ?></h1>
            <p class="tg-login-subtitle"><?php echo LANG_TEMPLATE_AUTH_LOGIN_SUBTITLE; ?></p>
        </div>
        
        <div class="tg-login-form">
            
            <?php if (isset($currentAttempts) && $currentAttempts > 0) { ?>
            <div class="tg-alert tg-alert-warning">
                <div class="tg-alert-icon">
                    <?php echo bloggy_icon('bs', 'exclamation-triangle', '18', 'currentColor'); ?>
                </div>
                <div class="tg-alert-content">
                    <strong><?php echo LANG_TEMPLATE_AUTH_LOGIN_ATTEMPT; ?></strong> <?php echo sprintf(LANG_TEMPLATE_AUTH_LOGIN_ATTEMPT_COUNT, $currentAttempts, $maxAttempts); ?>
                    <?php if ($currentAttempts >= $maxAttempts - 1) { ?>
                    <div class="tg-alert-small"><?php echo LANG_TEMPLATE_AUTH_LOGIN_ATTEMPT_WARNING; ?></div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="tg-field">
                    <label for="email" class="tg-label"><?php echo LANG_TEMPLATE_AUTH_EMAIL_LABEL; ?></label>
                    <div class="tg-input-wrapper">
                        <span class="tg-input-icon">
                            <?php echo bloggy_icon('bs', 'envelope', '16', 'currentColor'); ?>
                        </span>
                        <input type="email" 
                               id="email"
                               name="email" 
                               class="tg-input" 
                               placeholder="<?php echo LANG_TEMPLATE_AUTH_EMAIL_PLACEHOLDER; ?>" 
                               required 
                               value="<?php echo html($email ?? ''); ?>" 
                               autofocus>
                    </div>
                </div>
                
                <div class="tg-field">
                    <label for="password" class="tg-label"><?php echo LANG_TEMPLATE_AUTH_PASSWORD_LABEL; ?></label>
                    <div class="tg-input-wrapper">
                        <span class="tg-input-icon">
                            <?php echo bloggy_icon('bs', 'lock', '16', 'currentColor'); ?>
                        </span>
                        <input type="password" 
                               id="password"
                               name="password" 
                               class="tg-input" 
                               placeholder="<?php echo LANG_TEMPLATE_AUTH_PASSWORD_PLACEHOLDER; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="tg-field tg-field-row">
                    <label class="tg-checkbox">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span class="tg-checkbox-mark"></span>
                        <span class="tg-checkbox-label"><?php echo LANG_TEMPLATE_AUTH_REMEMBER_ME_LABEL; ?></span>
                    </label>
                </div>
                
                <button type="submit" class="tg-btn tg-btn-primary tg-btn-block">
                    <?php echo bloggy_icon('bs', 'box-arrow-in-right', '16', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_AUTH_LOGIN_BTN; ?>
                </button>
            </form>
            
            <div class="tg-login-footer">
                <div class="tg-login-links">
                    <a href="<?php echo BASE_URL; ?>/register" class="tg-link">
                        <?php echo bloggy_icon('bs', 'person-plus', '14', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_AUTH_CREATE_ACCOUNT_LINK; ?>
                    </a>
                    
                    <?php if (!$disable_restore) { ?>
                    <span class="tg-link-sep">•</span>
                    <a href="<?php echo BASE_URL; ?>/forgot-password" class="tg-link">
                        <?php echo bloggy_icon('bs', 'key', '14', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_AUTH_FORGOT_PASSWORD_LINK; ?>
                    </a>
                    <?php } ?>
                </div>
                
                <div class="tg-login-security">
                    <small class="tg-text-muted">
                        <?php echo bloggy_icon('bs', 'shield-check', '12', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_AUTH_SECURE_LOGIN; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>