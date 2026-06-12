<?php
    add_admin_js('templates/default/admin/assets/js/controllers/menu-items.js');
    add_admin_css('templates/default/admin/assets/css/controllers/menu-items.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'list-ul', '24', '#000', 'me-2'); ?>
            <?php echo sprintf(LANG_TEMPLATE_MENU_ITEMS_TITLE, html($menu['name'])); ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/menu/item/create/<?php echo $menu['id']; ?>" class="btn btn-primary me-2">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_MENU_ITEMS_ADD_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/menu" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_MENU_ITEMS_BACK_BTN; ?>
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_MENU_ITEMS_STAT_TOTAL; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'list-ul', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?php echo $stats['max_level']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_MENU_ITEMS_STAT_MAX_LEVEL; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'diagram-3', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?php echo $stats['with_icon']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_MENU_ITEMS_STAT_WITH_ICON; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'image', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?php echo $stats['with_visibility']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_MENU_ITEMS_STAT_WITH_VISIBILITY; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'shield-lock', '32', '#171db7'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <?php echo bloggy_icon('bs', 'sort-numeric-down', '20', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_MENU_ITEMS_LIST_TITLE; ?>
            </h5>
            <div class="text-muted small">
                <?php echo bloggy_icon('bs', 'arrows-move', '14', '#6c757d', 'me-1'); ?>
                <?php echo LANG_TEMPLATE_MENU_ITEMS_DRAG_HINT; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($items)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'inbox', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_MENU_ITEMS_NO_ITEMS_TITLE; ?></h5>
                    <p class="text-muted mb-4"><?php echo LANG_TEMPLATE_MENU_ITEMS_NO_ITEMS_TEXT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/menu/item/create/<?php echo $menu['id']; ?>" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        <?php echo LANG_TEMPLATE_MENU_ITEMS_ADD_FIRST_BTN; ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="menu-items-table">
                        <thead class="table-light">
                            <tr>
                                <th width="40"></th>
                                <th><?php echo LANG_TEMPLATE_MENU_ITEMS_TABLE_TITLE; ?></th>
                                <th><?php echo LANG_TEMPLATE_MENU_ITEMS_TABLE_URL; ?></th>
                                <th><?php echo LANG_TEMPLATE_MENU_ITEMS_TABLE_TARGET; ?></th>
                                <th><?php echo LANG_TEMPLATE_MENU_ITEMS_TABLE_VISIBILITY; ?></th>
                                <th width="120" class="text-end"><?php echo LANG_TEMPLATE_MENU_ITEMS_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody id="sortable-items" data-menu-id="<?php echo $menu['id']; ?>">
                            <?php
                            foreach ($items as $item) {
                                $indent = $item['level'] * 30;
                                $hasChildren = !empty($item['has_children']);
                                $iconHtml = '';

                                if (!empty($item['icon']) && is_array($item['icon']) && !empty($item['icon']['id'])) {
                                    $iconSet = $item['icon']['set'] ?? 'bs';
                                    $iconId = $item['icon']['id'];
                                    
                                    $iconSize = intval($item['icon']['size'] ?? 20);
                                    $iconSize = min(max($iconSize, 16), 24);
                                    
                                    $iconColor = $item['icon']['color'] ?? '#000000';

                                    if (strpos($iconId, '/') !== false) {
                                        $parts = explode('/', $iconId);
                                        $iconSet = $parts[0];
                                        $iconId = implode('/', array_slice($parts, 1));
                                    }

                                    $iconHtml = bloggy_icon($iconSet, $iconId, "{$iconSize} {$iconSize}", $iconColor);
                                }
                            ?>
                                <tr data-id="<?php echo $item['id']; ?>"
                                    data-parent-id="<?php echo $item['parent_id'] ?? ''; ?>"
                                    data-level="<?php echo $item['level']; ?>">
                                    <td class="text-center">
                                        <span class="drag-handle text-muted cursor-move">
                                            <?php echo bloggy_icon('bs', 'grip-vertical', '18', '#9ca3af'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="padding-left: <?php echo $indent; ?>px;" class="d-flex align-items-center">
                                            <?php if ($hasChildren) { ?>
                                                <span class="expand-icon me-2 text-warning">
                                                    <?php echo bloggy_icon('bs', 'folder-fill', '16', '#ffc107'); ?>
                                                </span>
                                            <?php } else { ?>
                                                <span class="me-2 text-primary">
                                                    <?php echo bloggy_icon('bs', 'link-45deg', '16', '#0d6efd'); ?>
                                                </span>
                                            <?php } ?>

                                            <?php if (!empty($iconHtml)) { ?>
                                                <span class="menu-item-icon me-2" style="display: inline-flex; align-items: center;">
                                                    <?php echo $iconHtml; ?>
                                                </span>
                                            <?php } ?>

                                            <div>
                                                <strong><?php echo html($item['title']); ?></strong>

                                                <?php if (!empty($item['is_extra'])) { ?>
                                                    <span class="badge bg-warning ms-2">extra</span>
                                                <?php } ?>

                                                <?php if (!empty($item['icon_only'])) { ?>
                                                    <span class="badge bg-info ms-2"><?php echo LANG_TEMPLATE_MENU_ITEMS_BADGE_ICON_ONLY; ?></span>
                                                <?php } ?>

                                                <?php if (!empty($item['description'])) { ?>
                                                    <span class="badge bg-secondary ms-2">desc</span>
                                                <?php } ?>

                                                <?php if ($item['level'] > 0) { ?>
                                                    <span class="badge bg-light text-dark ms-2">
                                                        <?php echo sprintf(LANG_TEMPLATE_MENU_ITEMS_LEVEL_BADGE, $item['level']); ?>
                                                    </span>
                                                <?php } ?>

                                                <?php if (!empty($item['class'])) { ?>
                                                    <div class="small text-muted mt-1">
                                                        <code>class: <?php echo html($item['class']); ?></code>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="small"><?php echo html($item['url']); ?></code>
                                    </td>
                                    <td>
                                        <?php if (($item['target'] ?? '_self') === '_blank') { ?>
                                            <span class="badge bg-light text-dark">
                                                <?php echo bloggy_icon('bs', 'box-arrow-up-right', '12', '#000', 'me-1'); ?>
                                                <?php echo LANG_TEMPLATE_MENU_ITEMS_TARGET_BLANK; ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="badge bg-light text-dark">
                                                <?php echo bloggy_icon('bs', 'link-45deg', '12', '#000', 'me-1'); ?>
                                                <?php echo LANG_TEMPLATE_MENU_ITEMS_TARGET_SELF; ?>
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['visibility']) && is_array($item['visibility'])) { ?>
                                            <span class="badge bg-info" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_MENU_ITEMS_VISIBILITY_TOOLTIP; ?>">
                                                <?php echo bloggy_icon('bs', 'shield-lock', '12', '#28249e', 'me-1'); ?>
                                                <?php echo LANG_TEMPLATE_MENU_ITEMS_VISIBILITY_BADGE; ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="badge bg-light text-dark">
                                                <?php echo bloggy_icon('bs', 'globe', '12', '#000', 'me-1'); ?>
                                                <?php echo LANG_TEMPLATE_MENU_ITEMS_PUBLIC_BADGE; ?>
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo ADMIN_URL; ?>/menu/item/edit/<?php echo $item['id']; ?>"
                                               class="btn btn-outline-primary"
                                               title="<?php echo LANG_TEMPLATE_MENU_ITEMS_ACTION_EDIT; ?>"
                                               data-bs-toggle="tooltip">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                            </a>
                                            <a href="<?php echo ADMIN_URL; ?>/menu/item/create/<?php echo $menu['id']; ?>?parent_id=<?php echo $item['id']; ?>"
                                               class="btn btn-outline-success"
                                               title="<?php echo LANG_TEMPLATE_MENU_ITEMS_ACTION_ADD_CHILD; ?>"
                                               data-bs-toggle="tooltip">
                                                <?php echo bloggy_icon('bs', 'plus-circle', '16', '#000'); ?>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-danger delete-item"
                                                    data-id="<?php echo $item['id']; ?>"
                                                    data-title="<?php echo html($item['title']); ?>"
                                                    title="<?php echo LANG_TEMPLATE_MENU_ITEMS_ACTION_DELETE; ?>"
                                                    data-bs-toggle="tooltip">
                                                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php ob_start(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
<?php admin_bottom_js(ob_get_clean()); ?>