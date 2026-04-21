<?php
add_admin_js('templates/default/admin/assets/js/controllers/form-settings.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'gear', '24', '#000', 'me-2'); ?>
            <?php echo sprintf(LANG_TEMPLATE_FORMS_SETTINGS_TITLE, html($form['name'])); ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/forms" class="btn btn-outline-secondary me-2">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_FORMS_SETTINGS_BACK_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/forms/edit/<?php echo $form['id']; ?>" class="btn btn-outline-primary">
                <?php echo bloggy_icon('bs', 'pencil', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_FORMS_SETTINGS_EDIT_FORM_BTN; ?>
            </a>
        </div>
    </div>
    
    <form method="POST" id="form-settings-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'sliders', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_MAIN_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?php echo bloggy_icon('bs', 'check-circle', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SUCCESS_MESSAGE_LABEL; ?>
                                </label>
                                <textarea class="form-control" 
                                          name="success_message" 
                                          rows="2"><?php echo html($settings['success_message'] ?? LANG_TEMPLATE_FORMS_SETTINGS_DEFAULT_SUCCESS_MESSAGE); ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_FORMS_SETTINGS_ERROR_MESSAGE_LABEL; ?>
                                </label>
                                <textarea class="form-control" 
                                          name="error_message" 
                                          rows="2"><?php echo html($settings['error_message'] ?? LANG_TEMPLATE_FORMS_SETTINGS_DEFAULT_ERROR_MESSAGE); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="show_labels" 
                                           id="show_labels"
                                           <?php echo !empty($settings['show_labels']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="show_labels">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SHOW_LABELS; ?>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="show_descriptions" 
                                           id="show_descriptions"
                                           <?php echo !empty($settings['show_descriptions']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="show_descriptions">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SHOW_DESCRIPTIONS; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="store_submissions" 
                                           id="store_submissions"
                                           <?php echo !empty($settings['store_submissions']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="store_submissions">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_STORE_SUBMISSIONS; ?>
                                    </label>
                                    <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_STORE_SUBMISSIONS_HINT; ?></div>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="csrf_protection" 
                                           id="csrf_protection"
                                           <?php echo !empty($settings['csrf_protection'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="csrf_protection">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_CSRF_PROTECTION; ?>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="email_validation" 
                                           id="email_validation"
                                           <?php echo !empty($settings['email_validation']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_validation">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_EMAIL_VALIDATION; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'shield-check', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SPAM_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="captcha_enabled" 
                                           id="captcha_enabled"
                                           <?php echo !empty($settings['captcha_enabled']) ? 'checked' : ''; ?>
                                           onchange="toggleCaptchaSettings()">
                                    <label class="form-check-label" for="captcha_enabled">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_ENABLE; ?>
                                    </label>
                                    <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_HINT; ?></div>
                                </div>
                                
                                <div id="captcha_settings" style="<?php echo !empty($settings['captcha_enabled']) ? '' : 'display: none;'; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'question-circle', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_TYPE; ?>
                                        </label>
                                        <select class="form-select" name="captcha_type" id="captcha_type" onchange="updateCaptchaExample()">
                                            <?php foreach ($captchaTypes as $type => $info) { ?>
                                                <option value="<?php echo $type; ?>" <?php echo ($settings['captcha_type'] ?? 'math') === $type ? 'selected' : ''; ?>>
                                                    <?php echo html($info['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div class="form-text small" id="captcha_type_description">
                                            <?php echo $captchaTypes[$settings['captcha_type'] ?? 'math']['description']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'chat-left-text', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_QUESTION; ?>
                                        </label>
                                        <input type="text"
                                            class="form-control"
                                            name="captcha_question"
                                            id="captcha_question"
                                            value="<?php echo html($settings['captcha_question'] ?? LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_DEFAULT_QUESTION); ?>"
                                            placeholder="<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_QUESTION_PLACEHOLDER; ?>">
                                        <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_QUESTION_HINT; ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'key', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_SECRET; ?>
                                        </label>
                                        <div class="input-group">
                                            <input type="text"
                                                class="form-control"
                                                name="captcha_secret"
                                                id="captcha_secret"
                                                value="<?php echo html($settings['captcha_secret'] ?? 'bloggy_cms_captcha'); ?>">
                                            <button type="button" class="btn btn-outline-secondary" onclick="generateCaptchaSecret()">
                                                <?php echo bloggy_icon('bs', 'arrow-clockwise', '16', '#000'); ?>
                                            </button>
                                        </div>
                                        <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_SECRET_HINT; ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-2'); ?>
                                                    <?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_EXAMPLE_TITLE; ?>
                                                </h6>
                                                <div id="captcha_example">
                                                    <p><strong><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_EXAMPLE_QUESTION; ?></strong> <?php echo html($captchaExample['question']); ?></p>
                                                    <p><strong><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_EXAMPLE_ANSWER; ?></strong> <?php echo html($captchaExample['answer']); ?></p>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateCaptchaExample()">
                                                    <?php echo bloggy_icon('bs', 'arrow-clockwise', '16', '#000', 'me-1'); ?>
                                                    <?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_GENERATE_BTN; ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    document.getElementById('captcha_type').addEventListener('change', function() {
                                        const type = this.value;
                                        const externalSettings = document.getElementById('external_captcha_settings');
                                        if (['recaptcha', 'hcaptcha'].includes(type)) {
                                            externalSettings.style.display = 'block';
                                        } else {
                                            externalSettings.style.display = 'none';
                                        }
                                    });
                                </script>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="spam_protection" 
                                           id="spam_protection"
                                           <?php echo !empty($settings['spam_protection']) ? 'checked' : ''; ?>
                                           onchange="toggleSpamSettings()">
                                    <label class="form-check-label" for="spam_protection">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SPAM_FILTER; ?>
                                    </label>
                                </div>
                                
                                <div id="spam_settings" style="<?php echo !empty($settings['spam_protection']) ? '' : 'display: none;'; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'ban', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SPAM_WORDS; ?>
                                        </label>
                                        <textarea class="form-control" 
                                                  name="spam_keywords" 
                                                  rows="4"
                                                  placeholder="<?php echo LANG_TEMPLATE_FORMS_SETTINGS_SPAM_WORDS_PLACEHOLDER; ?>"><?php echo html($settings['spam_keywords'] ?? ''); ?></textarea>
                                        <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_SPAM_WORDS_HINT; ?></div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="limit_submissions" 
                                           id="limit_submissions"
                                           <?php echo !empty($settings['limit_submissions']) ? 'checked' : ''; ?>
                                           onchange="toggleLimitSettings()">
                                    <label class="form-check-label" for="limit_submissions">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_LIMIT_SUBMISSIONS; ?>
                                    </label>
                                </div>
                                
                                <div id="limit_settings" style="<?php echo !empty($settings['limit_submissions']) ? '' : 'display: none;'; ?>">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <?php echo bloggy_icon('bs', 'calendar-day', '16', '#000', 'me-1'); ?>
                                                <?php echo LANG_TEMPLATE_FORMS_SETTINGS_MAX_PER_DAY; ?>
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="max_submissions_per_day" 
                                                   min="0"
                                                   value="<?php echo html($settings['max_submissions_per_day'] ?? 0); ?>">
                                            <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_MAX_PER_DAY_HINT; ?></div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <?php echo bloggy_icon('bs', 'pc-display', '16', '#000', 'me-1'); ?>
                                                <?php echo LANG_TEMPLATE_FORMS_SETTINGS_MAX_PER_IP; ?>
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="max_submissions_per_ip" 
                                                   min="0"
                                                   value="<?php echo html($settings['max_submissions_per_ip'] ?? 0); ?>">
                                            <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_MAX_PER_IP_HINT; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'bell', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_NOTIFICATIONS_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="notify_admin_enabled" 
                                           id="notify_admin_enabled"
                                           <?php echo !empty($notifications[0]['enabled'] ?? false) ? 'checked' : ''; ?>
                                           onchange="toggleAdminNotification()">
                                    <label class="form-check-label" for="notify_admin_enabled">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_NOTIFY_ADMIN; ?>
                                    </label>
                                </div>
                                
                                <div id="admin_notification" style="<?php echo !empty($notifications[0]['enabled'] ?? false) ? '' : 'display: none;'; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'envelope', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_ADMIN_EMAIL; ?>
                                        </label>
                                        <input type="email" 
                                               class="form-control" 
                                               name="admin_email" 
                                               value="<?php echo html($notifications[0]['to'] ?? ''); ?>"
                                               placeholder="admin@example.com">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'person-circle', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SENDER; ?>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="admin_from" 
                                               value="<?php echo html($notifications[0]['from'] ?? ''); ?>"
                                               placeholder="<?php echo LANG_TEMPLATE_FORMS_SETTINGS_SENDER_PLACEHOLDER; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'card-heading', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SUBJECT; ?>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="admin_subject" 
                                               value="<?php echo html($notifications[0]['subject'] ?? LANG_TEMPLATE_FORMS_SETTINGS_DEFAULT_ADMIN_SUBJECT); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'card-text', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_MESSAGE; ?>
                                        </label>
                                        <textarea class="form-control" 
                                                  name="admin_message" 
                                                  rows="4"><?php echo html($notifications[0]['message'] ?? LANG_TEMPLATE_FORMS_SETTINGS_DEFAULT_ADMIN_MESSAGE); ?></textarea>
                                        <div class="form-text small">
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_MESSAGE_VARIABLES; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="notify_user_enabled" 
                                           id="notify_user_enabled"
                                           <?php echo !empty($notifications[1]['enabled'] ?? false) ? 'checked' : ''; ?>
                                           onchange="toggleUserNotification()">
                                    <label class="form-check-label" for="notify_user_enabled">
                                        <?php echo LANG_TEMPLATE_FORMS_SETTINGS_NOTIFY_USER; ?>
                                    </label>
                                </div>
                                
                                <div id="user_notification" style="<?php echo !empty($notifications[1]['enabled'] ?? false) ? '' : 'display: none;'; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'input-cursor', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_USER_EMAIL_FIELD; ?>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="user_email_field" 
                                               value="<?php echo html($notifications[1]['to_field'] ?? '{email}'); ?>"
                                               placeholder="{email}">
                                        <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_USER_EMAIL_FIELD_HINT; ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'person-circle', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SENDER; ?>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="user_from" 
                                               value="<?php echo html($notifications[1]['from'] ?? ''); ?>"
                                               placeholder="<?php echo LANG_TEMPLATE_FORMS_SETTINGS_SENDER_PLACEHOLDER; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'card-heading', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SUBJECT; ?>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="user_subject" 
                                               value="<?php echo html($notifications[1]['subject'] ?? LANG_TEMPLATE_FORMS_SETTINGS_DEFAULT_USER_SUBJECT); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'card-text', '16', '#000', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_MESSAGE; ?>
                                        </label>
                                        <textarea class="form-control" 
                                                  name="user_message" 
                                                  rows="4"><?php echo html($notifications[1]['message'] ?? LANG_TEMPLATE_FORMS_SETTINGS_DEFAULT_USER_MESSAGE); ?></textarea>
                                        <div class="form-text small">
                                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_MESSAGE_VARIABLES_SHORT; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'lightning', '20', '#000', 'me-2'); ?>
                            <?php echo LANG_TEMPLATE_FORMS_SETTINGS_ACTIONS_TITLE; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="redirect_enabled" 
                                       id="redirect_enabled"
                                       <?php echo !empty(array_filter($actions, function($a) { return $a['type'] === 'redirect' && !empty($a['enabled']); })) ? 'checked' : ''; ?>
                                       onchange="toggleRedirectSettings()">
                                <label class="form-check-label" for="redirect_enabled">
                                    <?php echo LANG_TEMPLATE_FORMS_SETTINGS_REDIRECT; ?>
                                </label>
                            </div>
                            
                            <div id="redirect_settings" style="<?php 
                                $redirectEnabled = !empty(array_filter($actions, function($a) { 
                                    return $a['type'] === 'redirect' && !empty($a['enabled']); 
                                })); 
                                echo $redirectEnabled ? '' : 'display: none;'; 
                            ?>">
                                <label class="form-label small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_REDIRECT_URL; ?></label>
                                <?php 
                                    $redirectAction = array_values(array_filter($actions, function($a) { 
                                        return $a['type'] === 'redirect'; 
                                    }))[0] ?? []; 
                                ?>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       name="redirect_url" 
                                       value="<?php echo html($redirectAction['url'] ?? ''); ?>"
                                       placeholder="https://example.com/thank-you">
                                <div class="form-text small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_REDIRECT_URL_HINT; ?></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="webhook_enabled" 
                                       id="webhook_enabled"
                                       <?php echo !empty(array_filter($actions, function($a) { return $a['type'] === 'webhook' && !empty($a['enabled']); })) ? 'checked' : ''; ?>
                                       onchange="toggleWebhookSettings()">
                                <label class="form-check-label" for="webhook_enabled">
                                    <?php echo LANG_TEMPLATE_FORMS_SETTINGS_WEBHOOK; ?>
                                </label>
                            </div>
                            
                            <div id="webhook_settings" style="<?php 
                                $webhookEnabled = !empty(array_filter($actions, function($a) { 
                                    return $a['type'] === 'webhook' && !empty($a['enabled']); 
                                })); 
                                echo $webhookEnabled ? '' : 'display: none;'; 
                            ?>">
                                <?php 
                                    $webhookAction = array_values(array_filter($actions, function($a) { 
                                        return $a['type'] === 'webhook'; 
                                    }))[0] ?? []; 
                                ?>
                                <div class="mb-2">
                                    <label class="form-label small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_WEBHOOK_URL; ?></label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           name="webhook_url" 
                                           value="<?php echo html($webhookAction['url'] ?? ''); ?>"
                                           placeholder="https://example.com/webhook">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_WEBHOOK_METHOD; ?></label>
                                    <select class="form-select form-select-sm" name="webhook_method">
                                        <option value="POST" <?php echo ($webhookAction['method'] ?? 'POST') === 'POST' ? 'selected' : ''; ?>>POST</option>
                                        <option value="GET" <?php echo ($webhookAction['method'] ?? '') === 'GET' ? 'selected' : ''; ?>>GET</option>
                                        <option value="PUT" <?php echo ($webhookAction['method'] ?? '') === 'PUT' ? 'selected' : ''; ?>>PUT</option>
                                    </select>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small"><?php echo LANG_TEMPLATE_FORMS_SETTINGS_WEBHOOK_HEADERS; ?></label>
                                    <textarea class="form-control form-control-sm" 
                                              name="webhook_headers" 
                                              rows="3"
                                              placeholder="Content-Type: application/json&#10;Authorization: Bearer token"><?php 
                                        if (!empty($webhookAction['headers'])) {
                                            foreach ($webhookAction['headers'] as $key => $value) {
                                                echo html("$key: $value\n");
                                            }
                                        }
                                    ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_SETTINGS_SAVE_BTN; ?>
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/forms/preview/<?php echo $form['id']; ?>" 
                               class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_SETTINGS_PREVIEW_BTN; ?>
                            </a>
                            <a href="<?php echo ADMIN_URL; ?>/forms" 
                               class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FORMS_SETTINGS_BACK_TO_LIST; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
    function toggleCaptchaSettings() {
        const enabled = document.getElementById('captcha_enabled').checked;
        document.getElementById('captcha_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleSpamSettings() {
        const enabled = document.getElementById('spam_protection').checked;
        document.getElementById('spam_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleLimitSettings() {
        const enabled = document.getElementById('limit_submissions').checked;
        document.getElementById('limit_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleAdminNotification() {
        const enabled = document.getElementById('notify_admin_enabled').checked;
        document.getElementById('admin_notification').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleUserNotification() {
        const enabled = document.getElementById('notify_user_enabled').checked;
        document.getElementById('user_notification').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleRedirectSettings() {
        const enabled = document.getElementById('redirect_enabled').checked;
        document.getElementById('redirect_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleWebhookSettings() {
        const enabled = document.getElementById('webhook_enabled').checked;
        document.getElementById('webhook_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function generateCaptchaSecret() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=';
        let secret = '';
        for (let i = 0; i < 32; i++) {
            secret += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('captcha_secret').value = secret;
    }
    
    function updateCaptchaExample() {
        const type = document.getElementById('captcha_type').value;
        const question = document.getElementById('captcha_question').value;
        const descriptions = {
            'math': '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_MATH_DESC; ?>',
            'text': '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_TEXT_DESC; ?>',
            'logic': '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_LOGIC_DESC; ?>'
        };
        document.getElementById('captcha_type_description').textContent = descriptions[type] || '';
        
        fetch('<?php echo ADMIN_URL; ?>/forms/generate-captcha-example', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=${encodeURIComponent(type)}&question=${encodeURIComponent(question)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('captcha_example').innerHTML = `
                    <p><strong><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_EXAMPLE_QUESTION; ?></strong> ${data.question}</p>
                    <p><strong><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_EXAMPLE_ANSWER; ?></strong> ${data.answer || '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_NO_ANSWER; ?>'}</p>
                `;
            }
        })
        .catch(error => {
            const examples = {
                'math': { question: '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_MATH_EXAMPLE_QUESTION; ?>', answer: '10' },
                'text': { question: '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_TEXT_EXAMPLE_QUESTION; ?>', answer: '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_TEXT_EXAMPLE_ANSWER; ?>' },
                'logic': { question: '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_LOGIC_EXAMPLE_QUESTION; ?>', answer: '<?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_LOGIC_EXAMPLE_ANSWER; ?>' }
            };
            
            const example = examples[type] || examples.math;
            document.getElementById('captcha_example').innerHTML = `
                <p><strong><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_EXAMPLE_QUESTION; ?></strong> ${example.question}</p>
                <p><strong><?php echo LANG_TEMPLATE_FORMS_SETTINGS_CAPTCHA_EXAMPLE_ANSWER; ?></strong> ${example.answer}</p>
            `;
        });
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>