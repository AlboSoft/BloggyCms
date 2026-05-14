<div class="login-wrapper">
    <div class="login-box">
        <div class="login-header">
            <div class="login-logo">
                <img src="/templates/default/admin/assets/img/logo-outline.png" alt="">
            </div>
            <h1 class="login-title"><?php echo LANG_LOGIN_TITLE; ?></h1>
            <p class="login-subtitle"><?php echo LANG_LOGIN_SUBTITLE; ?></p>
        </div>

        <?php if (isset($error) && !empty($error)) { ?>
            <div class="login-alert login-alert-error" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <span><?php echo html($error); ?></span>
            </div>
        <?php } ?>

        <?php if (isset($currentAttempts) && $currentAttempts > 0) { ?>
            <div class="login-alert login-alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <span><?php echo LANG_LOGIN_ATTEMPT_TEXT; ?> <?php echo $currentAttempts; ?> <?php echo LANG_LOGIN_OF_TEXT; ?> <?php echo $maxAttempts; ?></span>
            </div>
        <?php } ?>

        <form method="post" class="login-form" novalidate>
            <?php echo \CsrfToken::field('admin_login'); ?>
            <input type="hidden" name="username" value="<?php echo html($username ?? ''); ?>">
            <input type="hidden" name="password" value="<?php echo html($password ?? ''); ?>">
            <input type="hidden" name="expected_answer" value="<?php echo html($expectedAnswer ?? ''); ?>">

            <div class="login-field">
                <label for="login-username" class="login-label"><?php echo LANG_LOGIN_USERNAME_LABEL; ?></label>
                <div class="login-input-wrapper">
                    <input type="text"
                           id="login-username"
                           name="username"
                           class="login-input"
                           placeholder="<?php echo LANG_LOGIN_USERNAME_PLACEHOLDER; ?>"
                           required
                           autocomplete="username"
                           value="<?php echo html($username ?? ''); ?>"
                           <?php echo (isset($showQuestion) && $showQuestion && !empty($username)) ? 'readonly' : 'autofocus'; ?>>
                    <span class="login-input-icon"><i class="bi bi-person"></i></span>
                </div>
            </div>

            <div class="login-field">
                <label for="login-password" class="login-label"><?php echo LANG_LOGIN_PASSWORD_LABEL; ?></label>
                <div class="login-input-wrapper">
                    <input type="password"
                           id="login-password"
                           name="password"
                           class="login-input"
                           placeholder="<?php echo LANG_LOGIN_PASSWORD_PLACEHOLDER; ?>"
                           required
                           autocomplete="current-password"
                           <?php echo (isset($showQuestion) && $showQuestion && !empty($password)) ? 'readonly' : ''; ?>>
                    <span class="login-input-icon"><i class="bi bi-lock"></i></span>
                </div>
            </div>

            <?php if (isset($showQuestion) && $showQuestion && !empty($question) && $question !== LANG_LOGIN_ERROR_NO_QUESTIONS) { ?>
                <div class="login-question">
                    <span class="login-question-label"><?php echo LANG_LOGIN_SECURITY_QUESTION_LABEL; ?></span>
                    <?php echo html($question); ?>
                </div>

                <div class="login-field">
                    <label for="login-answer" class="login-label"><?php echo LANG_LOGIN_SECURITY_ANSWER_LABEL; ?></label>
                    <div class="login-input-wrapper">
                        <input type="text"
                               id="login-answer"
                               name="qa_answer"
                               class="login-input"
                               placeholder="<?php echo LANG_LOGIN_SECURITY_ANSWER_PLACEHOLDER; ?>"
                               required
                               autocomplete="off"
                               autofocus>
                        <span class="login-input-icon"><i class="bi bi-chat-dots"></i></span>
                    </div>
                </div>
            <?php } ?>

            <button type="submit" class="login-submit">
                <i class="bi bi-box-arrow-in-right"></i>
                <?php echo (isset($showQuestion) && $showQuestion) ? LANG_LOGIN_CONTINUE_BUTTON : LANG_LOGIN_SUBMIT_BUTTON; ?>
            </button>
        </form>
    </div>
</div>