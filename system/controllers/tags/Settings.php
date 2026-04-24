<?php
namespace tags;

class TagSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_TAGS_SETTINGS_FIELDSET_DISPLAY, [
                'icon' => 'bi bi-eye',
                'fields' => [
                    \FieldFactory::string('tag_prefix', [
                        'title' => LANG_CONTROLLER_TAGS_SETTINGS_TAG_PREFIX,
                        'hint' => LANG_CONTROLLER_TAGS_SETTINGS_TAG_PREFIX_HINT,
                        'default' => '#',
                        'placeholder' => '#',
                        'attributes' => ['maxlength' => '5']
                    ]),
                    
                    \FieldFactory::number('min_posts_to_show', [
                        'title' => LANG_CONTROLLER_TAGS_SETTINGS_MIN_POSTS,
                        'hint' => LANG_CONTROLLER_TAGS_SETTINGS_MIN_POSTS_HINT,
                        'default' => 1,
                        'min' => 0,
                    ]),

                    \FieldFactory::number('cont_tags_in_front', [
                        'title' => LANG_CONTROLLER_TAGS_SETTINGS_TAGS_PER_PAGE,
                        'hint' => LANG_CONTROLLER_TAGS_SETTINGS_TAGS_PER_PAGE_HINT,
                        'default' => 12,
                        'min' => 0
                    ]),

                    \FieldFactory::select('tags_order', [
                        'title' => LANG_CONTROLLER_TAGS_SETTINGS_ORDER,
                        'hint' => LANG_CONTROLLER_TAGS_SETTINGS_ORDER_HINT,
                        'default' => 'name',
                        'options' => [
                            'name' => LANG_CONTROLLER_TAGS_SETTINGS_ORDER_NAME,
                            'posts_count' => LANG_CONTROLLER_TAGS_SETTINGS_ORDER_POSTS_COUNT,
                            'created_at' => LANG_CONTROLLER_TAGS_SETTINGS_ORDER_CREATED_AT
                        ]
                    ]),

                    \FieldFactory::image('default_tag_image', [
                        'title' => LANG_CONTROLLER_TAGS_SETTINGS_DEFAULT_IMAGE,
                        'upload_path' => 'uploads/settings/tags/'
                    ]),

                ]
            ]),
            
            new \Fieldset(LANG_CONTROLLER_TAGS_SETTINGS_FIELDSET_POST_CREATION, [
                'icon' => 'bi bi-gear',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::number('max_tags_per_post', [
                        'title' => LANG_CONTROLLER_TAGS_SETTINGS_MAX_TAGS,
                        'hint' => LANG_CONTROLLER_TAGS_SETTINGS_MAX_TAGS_HINT,
                        'default' => 10,
                        'min' => 1,
                        'max' => 50
                    ]),
                    \FieldFactory::checkbox('show_info', [
                        'title' => LANG_CONTROLLER_TAGS_SETTINGS_SHOW_INFO,
                        'hint' => LANG_CONTROLLER_TAGS_SETTINGS_SHOW_INFO_HINT,
                        'default' => false,
                        'switch' => true
                    ])
                ]
            ])
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