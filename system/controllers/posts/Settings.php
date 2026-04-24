<?php
namespace posts;

class PostSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_POSTS_SETTINGS_FIELDSET_POST_VIEW, [
                'icon' => 'bi bi-eye',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::alert('alert', [
                        'title' => LANG_CONTROLLER_POSTS_SETTINGS_ALERT_TITLE,
                        'hint' => LANG_CONTROLLER_POSTS_SETTINGS_ALERT_HINT,
                        'type' => 'info',
                        'icon' => 'info-circle',
                        'dismissible' => false, 
                        'column' => '12'
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