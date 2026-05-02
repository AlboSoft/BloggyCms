<?php
namespace notifications;

class NotificationsSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_FIELDSET_COMMENTS, [
                'icon' => 'bi bi-palette',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::select('variables', [
                        'title' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_COMMENTS_VARIABLES,
                        'default' => 'pending',
                        'options' => [
                            'all' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_COMMENTS_OPTION_ALL,
                            'pending' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_COMMENTS_OPTION_PENDING,
                        ],
                    ]),
                ]
            ]),
            
            new \Fieldset(LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_FIELDSET_ERRORS, [
                'icon' => 'bi bi-bug',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::checkbox('notify_on_new_error', [
                        'title' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_NOTIFY_ON_NEW,
                        'hint' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_NOTIFY_ON_NEW_HINT,
                        'default' => true,
                        'switch' => true
                    ]),
                    
                    \FieldFactory::select('notify_on_error_types', [
                        'title' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPES,
                        'hint' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPES_HINT,
                        'default' => 'error,exception',
                        'options' => [
                            'error' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPE_ERROR,
                            'warning' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPE_WARNING,
                            'notice' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPE_NOTICE,
                            'exception' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPE_EXCEPTION
                        ],
                        'attributes' => [
                            'multiple' => true,
                            'size' => 3
                        ],
                        'show' => 'field:notify_on_new_error'
                    ]),
                    
                    \FieldFactory::checkbox('notify_only_unfixed', [
                        'title' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_ONLY_UNFIXED,
                        'hint' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_ONLY_UNFIXED_HINT,
                        'default' => true,
                        'switch' => true,
                        'show' => 'field:notify_on_new_error'
                    ]),
                    
                    \FieldFactory::number('error_notification_throttle', [
                        'title' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_THROTTLE,
                        'hint' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_THROTTLE_HINT,
                        'default' => 60,
                        'min' => 5,
                        'max' => 1440,
                        'show' => 'field:notify_on_new_error'
                    ]),
                ]
            ]),

            new \Fieldset(LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_FIELDSET_USERS, [
                'icon' => 'bi bi-people',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::checkbox('notify_on_user_registration', [
                        'title' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_USERS_NOTIFY_ON_REGISTRATION,
                        'hint' => LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_USERS_NOTIFY_ON_REGISTRATION_HINT,
                        'default' => true,
                        'switch' => true
                    ]),
                ]
            ]),
        ];
        
        ob_start();
        ?>
        <div class="row">
            <?php foreach ($fieldsets as $fieldset) { ?>
            <div class="col-md-12">
                <?= $fieldset->render($currentSettings) ?>
            </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
}