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
            new \Fieldset(LANG_CONTROLLER_POSTS_SETTINGS_FIELDSET_POST_VIEW, [
                'icon' => 'bi bi-eye',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::alert('alert', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_ALERT_TITLE,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_ALERT_HINT,
                        'type' => 'info',
                        'icon' => 'info-circle',
                        'dismissible' => false
                    ]),
                ]
            ]),
        ];
        
        ob_start();
        ?>
        <div class="row">
            <?php foreach ($fieldsets as $fieldset): ?>
            <div class="col-md-12">
                <?= $fieldset->render($currentSettings) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}