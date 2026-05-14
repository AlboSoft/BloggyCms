<?php
namespace admin;

class AdminSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset(LANG_CONTROLLER_ADMINSETTINGS_FIELDSET_STATS, [
                'icon' => 'bi bi-bar-chart-fill',
                'columns' => '4',
                'fields' => [
                    \FieldFactory::checkbox('all_posts', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ALL_POSTS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('categories', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_CATEGORIES,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('tags', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_TAGS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('comments', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_COMMENTS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('users', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_USERS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('pages', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_PAGES,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('content_blocks', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_CONTENT_BLOCKS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_button', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_BUTTON,
                        'hint' => LANG_CONTROLLER_ADMINSETTINGS_HINT_SHOW_BUTTON,
                        'default' => false,
                        'switch' => true
                    ]),
                ]
            ]),

            new \Fieldset(LANG_CONTROLLER_ADMINSETTINGS_FIELDSET_POSTS_STATS, [
                'icon' => 'bi bi-pencil-fill',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::checkbox('last_posts', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_LAST_POSTS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('popular_posts', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_POPULAR_POSTS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('comments_posts', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_COMMENTS_POSTS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_drafts', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_DRAFTS,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::number('count_posts', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_COUNT_POSTS,
                        'default' => 4,
                        'max' => 10
                    ]),
                ]
            ]),

            new \Fieldset(LANG_CONTROLLER_ADMINSETTINGS_FIELDSET_DETAILED_STATS, [
                'icon' => 'bi bi-graph-up',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::checkbox('show_detailed_stats', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_DETAILED_STATS,
                        'hint' => LANG_CONTROLLER_ADMINSETTINGS_HINT_SHOW_DETAILED_STATS,
                        'default' => true,
                        'switch' => true
                    ]),
                    
                    \FieldFactory::select('stats_period', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_STATS_PERIOD,
                        'default' => 'month',
                        'options' => [
                            'week' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_WEEK,
                            'month' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_MONTH,
                            'quarter' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_QUARTER,
                            'year' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_YEAR,
                            'all' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_ALL
                        ],
                        'show' => 'field:show_detailed_stats'
                    ]),
                    
                    \FieldFactory::checkbox('show_publications_chart', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_PUBLICATIONS_CHART,
                        'default' => true,
                        'switch' => true,
                        'show' => 'field:show_detailed_stats'
                    ]),
                    
                    \FieldFactory::checkbox('show_popular_posts_chart', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_POPULAR_POSTS_CHART,
                        'default' => true,
                        'switch' => true,
                        'show' => 'field:show_detailed_stats'
                    ]),
                    
                    \FieldFactory::checkbox('show_liked_posts_chart', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_LIKED_POSTS_CHART,
                        'default' => true,
                        'switch' => true,
                        'show' => 'field:show_detailed_stats'
                    ]),
                    
                    \FieldFactory::checkbox('show_comments_chart', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_COMMENTS_CHART,
                        'default' => true,
                        'switch' => true,
                        'show' => 'field:show_detailed_stats'
                    ]),
                    
                    \FieldFactory::number('top_posts_limit', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_TOP_POSTS_LIMIT,
                        'default' => 5,
                        'min' => 3,
                        'max' => 20,
                        'show' => 'field:show_detailed_stats'
                    ]),
                    
                    \FieldFactory::select('chart_theme', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_CHART_THEME,
                        'default' => 'modern',
                        'options' => [
                            'modern' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_MODERN,
                            'pastel' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_PASTEL,
                            'dark' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_DARK,
                            'corporate' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_CORPORATE
                        ],
                        'show' => 'field:show_detailed_stats'
                    ])
                ]
            ]),

            new \Fieldset(LANG_CONTROLLER_ADMINSETTINGS_FIELDSET_QUICK_ACTIONS, [
                'icon' => 'bi bi-lightning-charge-fill',
                'columns' => '3',
                'fields' => [
                    \FieldFactory::checkbox('add_post', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ADD_POST,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_page', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ADD_PAGE,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_category', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ADD_CATEGORY,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_tag', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ADD_TAG,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_user', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ADD_USER,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_content_block', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ADD_CONTENT_BLOCK,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_field', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ADD_FIELD,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_form', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_ADD_FORM,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::select('position_btn', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_POSITION_BTN, 
                        'default' => 'bottom-right',
                        'options' => [
                            'bottom-right' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_BOTTOM_RIGHT,
                            'bottom-right-center' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_BOTTOM_CENTER
                        ]
                    ]),
                    \FieldFactory::select('color_btn', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_COLOR_BTN, 
                        'default' => 'primary',
                        'options' => [
                            'success text-dark' => 'Success',
                            'primary' => 'Primary',
                            'dark' => 'Dark',
                            'danger' => 'Danger',
                            'warning' => 'Warning',
                        ]
                    ]),
                    \FieldFactory::alert('field_name', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_ALERT_TITLE,
                        'hint' => LANG_CONTROLLER_ADMINSETTINGS_ALERT_HINT,
                        'type' => 'success',
                        'icon' => 'info-circle',
                        'full_width' => true
                    ])
                ]
            ]),

            new \Fieldset(LANG_CONTROLLER_ADMINSETTINGS_FIELDSET_SEARCH, [
                'icon' => 'bi bi-search',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::checkbox('show_search', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_SEARCH,
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_popular_search', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_POPULAR_SEARCH,
                        'default' => true,
                        'switch' => true,
                        'show' => 'field:show_search'
                    ]),
                ]
            ]),

            new \Fieldset(LANG_CONTROLLER_ADMINSETTINGS_FIELDSET_HEADER, [
                'icon' => 'bi bi-grid-1x2',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::alert('header_info', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_ALERT_TITLE,
                        'hint' => LANG_CONTROLLER_ADMINSETTINGS_BRANDING_HINT,
                        'type' => 'success',
                        'icon' => 'info-circle',
                        'full_width' => true
                    ]),
                    \FieldFactory::checkbox('show_site_name_in_logo', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_SHOW_SITE_NAME_IN_LOGO,
                        'hint' => LANG_CONTROLLER_ADMINSETTINGS_HINT_SHOW_SITE_NAME_IN_LOGO,
                        'default' => true,
                        'switch' => true,
                        'column' => 12
                    ]),
                    
                    \FieldFactory::image('logo_image', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_LOGO_IMAGE,
                        'upload_path' => 'uploads/settings/admin/',
                        'preview_size' => '64px',
                        'show' => 'field:show_site_name_in_logo',
                        'column' => 6
                    ]),
                    
                    \FieldFactory::string('custom_site_name', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_CUSTOM_SITE_NAME,
                        'hint' => LANG_CONTROLLER_ADMINSETTINGS_HINT_CUSTOM_SITE_NAME,
                        'placeholder' => LANG_CONTROLLER_ADMINSETTINGS_PLACEHOLDER_CUSTOM_SITE_NAME,
                        'maxlength' => 100,
                        'show' => 'field:show_site_name_in_logo',
                        'column' => 6
                    ]),
                    \FieldFactory::image('bg_panel', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_BG_PANEL,
                        'upload_path' => 'uploads/settings/admin/',
                        'column' => 6
                    ]),
                    \FieldFactory::select('notification_position', [
                        'title' => LANG_CONTROLLER_ADMINSETTINGS_FIELD_NOTIFICATION_POSITION,
                        'options' => [
                            'top-left' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_TOP_LEFT,
                            'top-right' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_TOP_RIGHT,
                            'bottom-left' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_BOTTOM_LEFT,
                            'bottom-right' => LANG_CONTROLLER_ADMINSETTINGS_OPTION_BOTTOM_RIGHT
                        ],
                        'default' => 'top-right',
                        'column' => 6
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