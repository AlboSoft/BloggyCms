<?php
namespace comments;

class CommentsSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_COMMENTS_SETTINGS_FIELDSET_GENERAL, [
                'icon' => 'bi bi-palette',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::number('max_depth', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_MAX_DEPTH,
                        'default' => 4,
                        'hint' => LANG_CONTROLLER_COMMENTS_SETTINGS_MAX_DEPTH_HINT
                    ]),
                    \FieldFactory::checkbox('show_groups', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_SHOW_GROUPS,
                        'default' => true,
                        'switch' => true,
                    ]),
                    \FieldFactory::checkbox('show_admin_badge', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_SHOW_ADMIN_BADGE,
                        'default' => false,
                        'switch' => true,
                    ]),
                    \FieldFactory::string('title_badge', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_TITLE_BADGE,
                        'show' => 'field:show_admin_badge'
                    ]),
                    \FieldFactory::icon('icon_badge', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_ICON_BADGE,
                        'default' => 'bs:rocket',
                        'icons_page_url' => '/admin/icons',
                        'show' => 'field:show_admin_badge'
                    ]),
                    \FieldFactory::color('bg_badge', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_BG_BADGE,
                        'default' => '#007bff',
                        'preset' => 'basic',
                        'show' => 'field:show_admin_badge',
                    ]),
                    \FieldFactory::color('color_badge', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_COLOR_BADGE,
                        'default' => '#ffffff',
                        'preset' => 'basic',
                        'show' => 'field:show_admin_badge',
                    ]),
                    \FieldFactory::checkbox('show_emodji', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_SHOW_EMODJI,
                        'default' => false,
                        'switch' => true,
                    ]),
                    \FieldFactory::repeater('emodji_list', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_EMODJI_LIST,
                        'hint' => LANG_CONTROLLER_COMMENTS_SETTINGS_EMODJI_LIST_HINT,
                        'show' => 'field:show_emodji',
                        'repeater_columns' => 4,
                        'fields' => [
                            [
                                'name' => 'icon',
                                'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_EMODJI_ICON,
                                'type' => 'string'
                            ]
                        ]
                    ])
                ]
            ]),

            new \Fieldset(LANG_CONTROLLER_COMMENTS_SETTINGS_FIELDSET_HEADERS, [
                'icon' => 'bi bi-input-cursor-text',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::string('z17', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_COMMENTS,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_COMMENTS
                    ]),
                    \FieldFactory::string('z18', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_WRITE_COMMENT,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_WRITE_COMMENT
                    ]),
                    \FieldFactory::string('z1', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_NO_COMMENTS,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_NO_COMMENTS
                    ]),
                    \FieldFactory::string('z2', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_BE_FIRST,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_BE_FIRST
                    ]),
                    \FieldFactory::string('z3', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_SHOW_THREAD,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_SHOW_THREAD
                    ]),
                    \FieldFactory::string('z4', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_ON_MODERATION,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_ON_MODERATION
                    ]),
                    \FieldFactory::string('z5', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_YOU,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_YOU,
                        'hint' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_YOU_HINT
                    ]),
                    \FieldFactory::string('z6', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_REPLY,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_REPLY
                    ]),
                    \FieldFactory::string('z7', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_EDIT,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_EDIT
                    ]),
                    \FieldFactory::string('z8', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_DELETE,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_DELETE
                    ]),
                    \FieldFactory::string('z10', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_NAME,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_NAME
                    ]),
                    \FieldFactory::string('z9', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_ENTER_NAME,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_ENTER_NAME
                    ]),
                    \FieldFactory::string('z11', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_EMAIL,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_EMAIL
                    ]),
                    \FieldFactory::string('z12', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_ENTER_EMAIL,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_ENTER_EMAIL
                    ]),
                    \FieldFactory::string('z13', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_EMAIL_NOT_PUBLISHED,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_EMAIL_NOT_PUBLISHED
                    ]),
                    \FieldFactory::string('z14', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_COMMENTING_AS,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_COMMENTING_AS
                    ]),
                    \FieldFactory::string('z15', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_COMMENT,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_COMMENT
                    ]),
                    \FieldFactory::string('z16', [
                        'title' => LANG_CONTROLLER_COMMENTS_SETTINGS_HEADER_WRITE_COMMENT_PLACEHOLDER,
                        'default' => LANG_CONTROLLER_COMMENTS_SETTINGS_DEFAULT_WRITE_COMMENT_PLACEHOLDER
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