<?php
namespace users;

class UsersSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_USERS_SETTINGS_FIELDSET_GENERAL, [
                'icon' => 'bi bi-palette',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::checkbox('show_filter', [
                        'title' => LANG_CONTROLLER_USERS_SETTINGS_SHOW_FILTER,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_info', [
                        'title' => LANG_CONTROLLER_USERS_SETTINGS_SHOW_INFO,
                        'hint' => LANG_CONTROLLER_USERS_SETTINGS_SHOW_INFO_HINT,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('admin_top', [
                        'title' => LANG_CONTROLLER_USERS_SETTINGS_ADMIN_TOP,
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