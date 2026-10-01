<?php

namespace docs;

class DocsSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_DOCS_SETTINGS_FIELDSET_GENERAL, [
                'icon' => 'bi bi-book',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::alert('docs_info', [
                        'title' => LANG_CONTROLLER_DOCS_SETTINGS_ALERT_TITLE,
                        'hint' => LANG_CONTROLLER_DOCS_SETTINGS_ALERT_HINT,
                        'type' => 'info',
                        'icon' => 'info-circle',
                        'full_width' => true
                    ]),
                    \FieldFactory::checkbox('show_button_in_controllers', [
                        'title' => LANG_CONTROLLER_DOCS_SETTINGS_SHOW_BUTTON_IN_CONTROLLERS,
                        'hint' => LANG_CONTROLLER_DOCS_SETTINGS_SHOW_BUTTON_IN_CONTROLLERS_HINT,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_button_on_dashboard', [
                        'title' => LANG_CONTROLLER_DOCS_SETTINGS_SHOW_BUTTON_ON_DASHBOARD,
                        'hint' => LANG_CONTROLLER_DOCS_SETTINGS_SHOW_BUTTON_ON_DASHBOARD_HINT,
                        'default' => true,
                        'switch' => true
                    ]),
                ]
            ])
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
