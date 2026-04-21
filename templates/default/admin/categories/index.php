<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'folder', '24', '#000', 'me-2 controller-svg'); ?> 
            <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_TITLE; ?>
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/fields/entity/category" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'input-cursor-text', '20', null, 'me-2'); ?>
                <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_FIELDS_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/settings?tab=components&controller=categories" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'gear-fill', '20', null, 'me-2'); ?>
                <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_SETTINGS_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/categories/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_CREATE_BTN; ?>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($categories)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3"><?php echo bloggy_icon('bs', 'folder2-open', '24', '#000'); ?></div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_NO_CATEGORIES_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_NO_CATEGORIES_TEXT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/categories/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_CREATE_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>

                <?php if(SettingsHelper::get('controller_categories', 'show_stat') == true) { ?>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h4 class="mb-0"><?php echo count($categories); ?></h4>
                                            <small><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_STAT_TOTAL_CATEGORIES; ?></small>
                                        </div>
                                        <?php echo bloggy_icon('bs', 'folder', '32', '#fff'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h4 class="mb-0"><?php echo array_sum(array_column($categories, 'posts_count')); ?></h4>
                                            <small><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_STAT_TOTAL_POSTS; ?></small>
                                        </div>
                                        <?php echo bloggy_icon('bs', 'file-text', '32', '#fff'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h4 class="mb-0"><?php echo count(array_filter($categories, function($cat) { return $cat['password_protected']; })); ?></h4>
                                            <small><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_STAT_PROTECTED; ?></small>
                                        </div>
                                        <?php echo bloggy_icon('bs', 'lock', '32', '#000'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h4 class="mb-0"><?php echo count(array_filter($categories, function($cat) { return $cat['noindex']; })); ?></h4>
                                            <small><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_STAT_NOINDEX; ?></small>
                                        </div>
                                        <?php echo bloggy_icon('bs', 'eye-slash', '32', '#000'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if(SettingsHelper::get('controller_categories', 'show_search') == true) { ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="flex-grow-1 me-3">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <?php echo bloggy_icon('bs', 'search', '20', '#6C6C6C'); ?>
                                </span>
                                <input type="text" class="form-control border-start-0 search-input" placeholder="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_SEARCH_PLACEHOLDER; ?>" id="categories-search">
                                <button class="btn btn-outline-secondary clear-search" type="button" style="display: none;">
                                    <?php echo bloggy_icon('bs', 'x-lg', '20', '#6C6C6C', 'controller-svg'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge bg-light text-dark fs-6" id="categories-count">
                                <?php 
                                echo count($categories) . ' ' . plural_form(count($categories), explode('|', LANG_TEMPLATE_CATEGORIES_INDEX_COUNT_ENDING)); 
                                ?>
                            </span>
                        </div>
                    </div>
                <?php } ?>

                <?php if(SettingsHelper::get('controller_categories', 'show_info') == true) { ?>
                    <div class="alert alert-info d-flex align-items-center mb-3">
                        <?php echo bloggy_icon('bs', 'info-circle', '16', '#5AAFC9', 'me-2'); ?>
                        <span><?php echo html($randomHint); ?></span>
                    </div>
                <?php } ?>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="categories-table">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">#</th>
                                <th width="80"><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_TABLE_IMAGE; ?></th>
                                <th><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_TABLE_CATEGORY; ?></th>
                                <?php if(SettingsHelper::get('controller_categories', 'show_stat_list') == true) { ?>
                                    <th><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_TABLE_STATS; ?></th>
                                <?php } ?>
                                <th width="120" class="text-center"><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_TABLE_STATUS; ?></th>
                                <th width="150" class="text-end"><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody id="sortable-categories">
                            <?php foreach($categories as $index => $category) { ?>
                                <tr data-category-id="<?php echo $category['id']; ?>" class="sortable-item">
                                    <td class="text-center">
                                        <div class="drag-handle text-muted cursor-move" title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_DRAG_TITLE; ?>">
                                            <?php echo bloggy_icon('bs', 'grip-vertical', '16', '#5AAFC9'); ?>
                                        </div>
                                        <span class="badge bg-secondary d-block mt-1"><?php echo $category['sort_order'] ?? ($index + 1); ?></span>
                                    </td>
                                    
                                    <td>
                                        <?php if(!empty($category['image'])) { ?>
                                            <img src="/uploads/images/<?php echo html($category['image']); ?>" class="img-thumbnail rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                        <?php } else { ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <?php echo bloggy_icon('bs', 'image', '32', 'currentColor', 'text-muted'); ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <?php echo html($category['name']); ?>
                                                    <?php if($category['password_protected']) { ?>
                                                        <span class="ms-1 controller-svg" title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_PASSWORD_TITLE; ?>">
                                                            <?php echo bloggy_icon('bs', 'lock', '14', null, 'text-danger'); ?>
                                                        </span>
                                                    <?php } ?>
                                                </h6>
                                                <div class="text-muted small">
                                                    <code title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_URL_TITLE; ?>">/category/<?php echo html($category['slug']); ?></code>
                                                </div>
                                                <?php if(!empty($category['description'])) { ?>
                                                    <div class="text-muted small mt-1">
                                                        <?php echo html(mb_substr($category['description'], 0, 100)); ?>
                                                        <?php echo mb_strlen($category['description']) > 100 ? '...' : ''; ?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <?php if(SettingsHelper::get('controller_categories', 'show_stat_list') == true) { ?>
                                        <td>
                                            <div class="small">
                                                <div class="mb-1">
                                                    <?php echo bloggy_icon('bs', 'file-text', '12', null, 'me-1 svg-controller'); ?>
                                                    <strong><?php echo $category['posts_count'] ?? 0; ?></strong>
                                                    <?php echo plural_form($category['posts_count'] ?? 0, explode('|', LANG_TEMPLATE_CATEGORIES_INDEX_POSTS_ENDING)); ?>
                                                </div>
                                                <div class="text-muted">
                                                    <?php echo bloggy_icon('bs', 'sort-numeric-up', '12', null, 'me-1 svg-controller'); ?>
                                                    <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_ORDER_LABEL; ?> <?php echo $category['sort_order'] ?? 0; ?>
                                                </div>
                                            </div>
                                        </td>
                                    <?php } ?>
                                    
                                    <td class="text-center">
                                        <div class="d-flex flex-column gap-1">
                                            <?php if($category['password_protected']) { ?>
                                                <span class="badge bg-warning" title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_PASSWORD_BADGE_TITLE; ?>" data-bs-toggle="tooltip">
                                                    <?php echo bloggy_icon('bs', 'lock', '12', null, 'me-1'); ?> <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_PASSWORD_BADGE; ?>
                                                </span>
                                            <?php } else { ?>
                                                <span class="badge bg-success" title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_OPEN_BADGE_TITLE; ?>" data-bs-toggle="tooltip">
                                                    <?php echo bloggy_icon('bs', 'unlock', '12', null, 'me-1'); ?> <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_OPEN_BADGE; ?>
                                                </span>
                                            <?php } ?>
                                            
                                            <?php if($category['noindex']) { ?>
                                                <span class="badge bg-danger" title="Noindex" data-bs-toggle="tooltip">
                                                    <?php echo bloggy_icon('bs', 'eye-slash', '12', null, 'me-1'); ?> Noindex
                                                </span>
                                            <?php } else { ?>
                                                <span class="badge bg-info" title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_INDEX_BADGE_TITLE; ?>" data-bs-toggle="tooltip">
                                                    <?php echo bloggy_icon('bs', 'eye', '12', null, 'me-1'); ?> <?php echo LANG_TEMPLATE_CATEGORIES_INDEX_INDEX_BADGE; ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?php echo BASE_URL; ?>/category/<?php echo $category['slug']; ?>" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               target="_blank" 
                                               title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_VIEW_TITLE; ?>"
                                               data-bs-toggle="tooltip">
                                                <?php echo bloggy_icon('bs', 'eye', '16', '#000'); ?>
                                            </a>
                                            <a href="<?php echo ADMIN_URL; ?>/categories/edit/<?php echo $category['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_EDIT_TITLE; ?>"
                                               data-bs-toggle="tooltip">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                            </a>
                                            <a href="<?php echo ADMIN_URL; ?>/categories/delete/<?php echo $category['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('<?php echo sprintf(LANG_TEMPLATE_CATEGORIES_INDEX_DELETE_CONFIRM, addslashes($category['name'])); ?>')"
                                               title="<?php echo LANG_TEMPLATE_CATEGORIES_INDEX_DELETE_TITLE; ?>"
                                               data-bs-toggle="tooltip">
                                                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div id="no-results" class="text-center py-5" style="display: none;">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'search', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_NO_RESULTS_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_CATEGORIES_INDEX_NO_RESULTS_TEXT; ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php
    add_admin_js('templates/default/admin/assets/js/controllers/category.js');
    add_admin_css('templates/default/admin/assets/css/controllers/category.css');
?>