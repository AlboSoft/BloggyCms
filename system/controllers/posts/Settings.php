<?php
namespace posts;

class PostSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_POSTS_SETTINGS_FIELDSET_HOME_PAGE, [
                'icon' => 'bi bi-house-door',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::select('homepage_type', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_HOMEPAGE_TYPE,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_HOMEPAGE_TYPE_HINT,
                        'default' => 'default',
                        'options' => [
                            'default' => LANG_CONTROLLER_POSTS_SETTINGS_HOMEPAGE_TYPE_DEFAULT,
                            'posts_list' => LANG_CONTROLLER_POSTS_SETTINGS_HOMEPAGE_TYPE_POSTS_LIST
                        ]
                    ]),
                    \FieldFactory::number('homepage_posts_per_page', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_POSTS_PER_PAGE,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_POSTS_PER_PAGE_HINT,
                        'default' => 10,
                        'min' => 1,
                        'max' => 50,
                        'show' => 'field:homepage_type = posts_list'
                    ]),
                    \FieldFactory::alert('homepage_info', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_HOMEPAGE_INFO_TITLE,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_HOMEPAGE_INFO_HINT,
                        'type' => 'info',
                        'icon' => 'info-circle',
                        'full_width' => true
                    ])
                ]
            ]),
            new \Fieldset(LANG_CONTROLLER_POSTS_SETTINGS_FIELDSET_ADULT_CONTENT, [
                'icon' => 'bi bi-18-plus',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::select('adult_content_action', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_CONTENT_ACTION,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_CONTENT_ACTION_HINT,
                        'default' => 'none',
                        'options' => [
                            'none' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_ACTION_NONE,
                            'age_check' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_ACTION_AGE_CHECK,
                            'redirect_login' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_ACTION_REDIRECT_LOGIN
                        ]
                    ]),
                    \FieldFactory::number('adult_min_age', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_MIN_AGE,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_MIN_AGE_HINT,
                        'default' => 18,
                        'min' => 16,
                        'max' => 21,
                        'show' => 'field:adult_content_action != none'
                    ]),
                    \FieldFactory::checkbox('adult_remember_decision', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_REMEMBER,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_REMEMBER_HINT,
                        'default' => true,
                        'switch' => true,
                        'show' => 'field:adult_content_action = age_check'
                    ]),
                    \FieldFactory::alert('adult_info', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_INFO_TITLE,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_ADULT_INFO_HINT,
                        'type' => 'warning',
                        'icon' => 'info-circle',
                        'full_width' => true
                    ])
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