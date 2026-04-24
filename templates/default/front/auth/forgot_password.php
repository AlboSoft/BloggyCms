<?php
/**
 * Template Name: Восстановление пароля
 */

$success = $success ?? false;
$error = $error ?? '';
$email = $email ?? '';
?>

<div class="tg-forgot-page">
    <div class="tg-container tg-container-sm" style = "max-width: 760px;">
        
        <div class="tg-forgot-header">
            <h1 class="tg-forgot-title"><?php echo LANG_TEMPLATE_AUTH_FORGOT_TITLE; ?></h1>
            <p class="tg-forgot-subtitle"><?php echo LANG_TEMPLATE_AUTH_FORGOT_SUBTITLE; ?></p>
        </div>
        
        <div class="tg-card">
            <div class="tg-card-body">
                
                <?php if ($success) { ?>
                <div class="tg-alert tg-alert-success tg-mb-4">
                    <div class="tg-alert-icon">
                        <?php echo bloggy_icon('bs', 'check-circle', '18', 'currentColor'); ?>
                    </div>
                    <div class="tg-alert-content">
                        <strong><?php echo LANG_TEMPLATE_AUTH_FORGOT_SUCCESS_TITLE; ?></strong>
                        <p><?php echo LANG_TEMPLATE_AUTH_FORGOT_SUCCESS_TEXT; ?></p>
                        <div class="tg-alert-small"><?php echo LANG_TEMPLATE_AUTH_FORGOT_SUCCESS_HINT; ?></div>
                    </div>
                </div>
                <?php } elseif ($error) { ?>
                <div class="tg-alert tg-alert-error tg-mb-4">
                    <div class="tg-alert-icon">
                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '18', 'currentColor'); ?>
                    </div>
                    <div class="tg-alert-content">
                        <strong><?php echo LANG_TEMPLATE_AUTH_ERROR; ?></strong> <?php echo html($error); ?>
                    </div>
                </div>
                <?php } ?>
                
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="tg-field">
                        <label for="email" class="tg-label">
                            <?php echo LANG_TEMPLATE_AUTH_EMAIL_LABEL; ?> <span class="tg-text-muted">*</span>
                        </label>
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
                                   value="<?php echo html($email); ?>" 
                                   autofocus>
                        </div>
                        <div class="tg-field-hint">
                            <?php echo bloggy_icon('bs', 'info-circle', '12', 'var(--tg-text-muted)', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_AUTH_FORGOT_EMAIL_HINT; ?>
                        </div>
                    </div>
                    
                    <div class="tg-info-box tg-mb-4">
                        <div class="tg-info-box-icon">
                            <?php echo bloggy_icon('bs', 'shield-exclamation', '16', 'var(--tg-primary)'); ?>
                        </div>
                        <div class="tg-info-box-content">
                            <strong><?php echo LANG_TEMPLATE_AUTH_FORGOT_SECURITY_TITLE; ?></strong>
                            <span><?php echo LANG_TEMPLATE_AUTH_FORGOT_SECURITY_TEXT; ?></span>
                            <small class="tg-text-muted"><?php echo LANG_TEMPLATE_AUTH_FORGOT_SECURITY_HINT; ?></small>
                        </div>
                    </div>
                    
                    <button type="submit" class="tg-btn tg-btn-primary tg-btn-block">
                        <?php echo bloggy_icon('bs', 'send', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_AUTH_FORGOT_SUBMIT_BTN; ?>
                    </button>
                </form>
                
                <div class="tg-forgot-footer tg-mt-4">
                    <div class="tg-login-links">
                        <a href="<?php echo BASE_URL; ?>/login" class="tg-link">
                            <?php echo bloggy_icon('bs', 'box-arrow-in-right', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_AUTH_LOGIN_LINK; ?>
                        </a>
                        
                        <span class="tg-link-sep">•</span>
                        
                        <a href="<?php echo BASE_URL; ?>/register" class="tg-link">
                            <?php echo bloggy_icon('bs', 'person-plus', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_AUTH_REGISTER_LINK; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>