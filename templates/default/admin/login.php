<div class="login-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card login-card">
                    <div class="card-body p-4">
                        <div class="login-header">
                            <div class="login-icon">
                                <img src="/templates/default/admin/assets/img/logo-outline.png" style="width: 64px;">
                            </div>
                            <h4><?php echo LANG_LOGIN_TITLE; ?></h4>
                            <p class="text-muted"><?php echo LANG_LOGIN_SUBTITLE; ?></p>
                        </div>
                        
                        <?php if(isset($error) && !empty($error)) { ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i><?php echo html($error); ?>
                            </div>
                        <?php } ?>

                        <?php if (isset($currentAttempts) && $currentAttempts > 0) { ?>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php echo LANG_LOGIN_ATTEMPT_TEXT; ?> <?php echo $currentAttempts; ?> <?php echo LANG_LOGIN_OF_TEXT; ?> <?php echo $maxAttempts; ?>
                        </div>
                        <?php } ?>

                        <form method="post">
                            <?php echo \CsrfToken::field('admin_login'); ?>
                            <input type="hidden" name="username" value="<?php echo html($username ?? ''); ?>">
                            <input type="hidden" name="password" value="<?php echo html($password ?? ''); ?>">
                            <input type="hidden" name="expected_answer" value="<?php echo html($expectedAnswer ?? ''); ?>">
                            
                            <div class="mb-4">
                                <label class="form-label text-muted"><?php echo LANG_LOGIN_USERNAME_LABEL; ?></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" name="username" class="form-control" placeholder="<?php echo LANG_LOGIN_USERNAME_PLACEHOLDER; ?>" required 
                                        value="<?php echo html($username ?? ''); ?>" 
                                        <?php echo (isset($showQuestion) && $showQuestion && !empty($username)) ? 'readonly' : 'autofocus'; ?>>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted"><?php echo LANG_LOGIN_PASSWORD_LABEL; ?></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control" placeholder="<?php echo LANG_LOGIN_PASSWORD_PLACEHOLDER; ?>" required
                                        <?php echo (isset($showQuestion) && $showQuestion && !empty($password)) ? 'readonly' : ''; ?>>
                                </div>
                            </div>

                            <?php if (isset($showQuestion) && $showQuestion && !empty($question) && $question !== LANG_LOGIN_ERROR_NO_QUESTIONS) { ?>
                                <div class="mb-4">
                                    <label class="form-label text-muted"><?php echo LANG_LOGIN_SECURITY_QUESTION_LABEL; ?></label>
                                    <div class="alert alert-info">
                                        <strong><?php echo html($question); ?></strong>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label text-muted"><?php echo LANG_LOGIN_SECURITY_ANSWER_LABEL; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-chat-dots"></i>
                                        </span>
                                        <input type="text" name="qa_answer" class="form-control" placeholder="<?php echo LANG_LOGIN_SECURITY_ANSWER_PLACEHOLDER; ?>" required autofocus>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    <?php echo (isset($showQuestion) && $showQuestion) ? LANG_LOGIN_CONTINUE_BUTTON : LANG_LOGIN_SUBMIT_BUTTON; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>