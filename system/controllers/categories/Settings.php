<?php

namespace categories;

class CategoriesSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_CATEGORIES_SETTINGS_FIELDSET_GENERAL, [
                'icon' => 'bi bi-palette',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::checkbox('show_stat', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_STAT,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_search', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_SEARCH,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_info', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_INFO,
                        'hint' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_INFO_HINT,
                        'default' => false,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_stat_list', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_STAT_LIST,
                        'hint' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_STAT_LIST_HINT,
                        'default' => false,
                        'switch' => true
                    ]),
                ]
            ]),
            new \Fieldset(LANG_CONTROLLER_CATEGORIES_SETTINGS_FIELDSET_FRONTEND, [
                'icon' => 'bi bi-display',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::select('category_layout', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_CATEGORY_LAYOUT,
                        'default' => 'grid',
                        'options' => [
                            'grid' => LANG_CONTROLLER_CATEGORIES_SETTINGS_LAYOUT_GRID,
                            'list' => LANG_CONTROLLER_CATEGORIES_SETTINGS_LAYOUT_LIST,
                            'cards' => LANG_CONTROLLER_CATEGORIES_SETTINGS_LAYOUT_CARDS
                        ]
                    ]),
                    \FieldFactory::number('categories_per_page', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_CATEGORIES_PER_PAGE,
                        'default' => 12,
                        'min' => 1,
                        'max' => 100
                    ]),
                    \FieldFactory::checkbox('show_category_images', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_CATEGORY_IMAGES,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_category_descriptions', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_CATEGORY_DESCRIPTIONS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_post_counts', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_SHOW_POST_COUNTS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::select('categories_order', [
                        'title' => LANG_CONTROLLER_CATEGORIES_SETTINGS_CATEGORIES_ORDER,
                        'default' => 'name',
                        'options' => [
                            'name' => LANG_CONTROLLER_CATEGORIES_SETTINGS_ORDER_NAME,
                            'posts_count' => LANG_CONTROLLER_CATEGORIES_SETTINGS_ORDER_POSTS_COUNT,
                            'created_at' => LANG_CONTROLLER_CATEGORIES_SETTINGS_ORDER_CREATED_AT,
                            'sort_order' => LANG_CONTROLLER_CATEGORIES_SETTINGS_ORDER_SORT_ORDER
                        ]
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