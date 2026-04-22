<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'list-ul', '24', '#000', 'me-2'); ?>
            <?php echo sprintf(LANG_TEMPLATE_FRAGMENTS_ENTRIES_TITLE, html($fragment['name'])); ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/fragments/edit/<?php echo $fragment['id']; ?>" class="btn btn-outline-secondary btn-sm me-2">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
                <?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_BACK_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/fragments/entry/create/<?php echo $fragment['id']; ?>" class="btn btn-primary btn-sm">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-1'); ?>
                <?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_ADD_BTN; ?>
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_STAT_TOTAL; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'list-ul', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?php echo $stats['active']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_STAT_ACTIVE; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'check-circle', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?php echo $stats['inactive']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_STAT_INACTIVE; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'x-circle', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?php echo count($fields); ?></h4>
                            <small><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_STAT_FIELDS; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'input-cursor-text', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($entries)) { ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <?php echo bloggy_icon('bs', 'inbox', '48', '#6C6C6C', 'mb-3'); ?>
                <h5 class="text-muted"><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_NO_ENTRIES_TITLE; ?></h5>
                <p class="text-muted"><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_NO_ENTRIES_TEXT; ?></p>
                <a href="<?php echo ADMIN_URL; ?>/fragments/entry/create/<?php echo $fragment['id']; ?>" class="btn btn-primary">
                    <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_ADD_BTN; ?>
                </a>
            </div>
        </div>
    <?php } else { ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div id="sortable-entries" class="sortable-entries">
                    <?php foreach ($entries as $entry) { ?>
                        <div class="entry-item border-bottom p-3" data-id="<?php echo $entry['id']; ?>">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle me-3 cursor-move">
                                    <?php echo bloggy_icon('bs', 'grip-vertical', '20', '#9ca3af'); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo sprintf(LANG_TEMPLATE_FRAGMENTS_ENTRIES_ENTRY_NUMBER, $entry['id']); ?></strong>
                                            <?php if ($entry['status'] !== 'active') { ?>
                                                <span class="badge bg-secondary ms-2"><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_STATUS_INACTIVE; ?></span>
                                            <?php } ?>
                                            
                                            <?php if (!empty($fields)) { ?>
                                                <div class="text-muted small mt-1">
                                                    <?php 
                                                    $displayedFields = 0;
                                                    foreach ($fields as $field) {
                                                        if (!empty($field['show_in_list']) && isset($entry['data'][$field['system_name']])) {
                                                            $value = $entry['data'][$field['system_name']];
                                                            if (!empty($value) || $value === '0') {
                                                                $displayedFields++;
                                                                
                                                                $displayValue = $value;
                                                                
                                                                if ($field['type'] === 'image') {
                                                                    $displayValue = basename($value);
                                                                }
                                                                
                                                                if (strlen($displayValue) > 50) {
                                                                    $displayValue = mb_substr($displayValue, 0, 50) . '...';
                                                                }
                                                                
                                                                echo '<div class="preview-field mb-1"><strong>' . html($field['name']) . ':</strong> ' . html($displayValue) . '</div>';
                                                            }
                                                        }
                                                    }
                                                    
                                                    if ($displayedFields === 0) {
                                                        echo '<em class="text-muted">' . LANG_TEMPLATE_FRAGMENTS_ENTRIES_NO_DISPLAY_DATA . '</em>';
                                                    }
                                                    ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo ADMIN_URL; ?>/fragments/entry/edit/<?php echo $entry['id']; ?>" 
                                               class="btn btn-outline-primary"
                                               title="<?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_EDIT_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                            </a>
                                            <a href="<?php echo ADMIN_URL; ?>/fragments/entry/delete/<?php echo $entry['id']; ?>" 
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('<?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_DELETE_CONFIRM; ?>')"
                                               title="<?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_DELETE_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="small text-muted mt-2">
                                        <span><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_CREATED_LABEL; ?> <?php echo date('d.m.Y H:i', strtotime($entry['created_at'])); ?></span>
                                        <?php if ($entry['updated_at'] != $entry['created_at']) { ?>
                                            <span class="ms-3"><?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_UPDATED_LABEL; ?> <?php echo date('d.m.Y H:i', strtotime($entry['updated_at'])); ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        
        <div class="mt-3 text-muted small">
            <?php echo bloggy_icon('bs', 'arrows-move', '14', '#6c757d', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_DRAG_HINT; ?>
        </div>
    <?php } ?>
</div>

<?php if (!empty($entries)) { ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Sortable !== 'undefined') {
                const container = document.getElementById('sortable-entries');
                Sortable.create(container, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function() {
                        const order = [];
                        document.querySelectorAll('.entry-item').forEach((item, idx) => {
                            order.push({
                                id: item.dataset.id,
                                order: idx
                            });
                        });
                        
                        fetch('<?php echo ADMIN_URL; ?>/fragments/reorder-entries', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ order: order })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('<?php echo LANG_TEMPLATE_FRAGMENTS_ENTRIES_ORDER_UPDATED; ?>', 'success');
                            }
                        });
                    }
                });
            }
            
            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);
            }
        });
    </script>
<?php } ?>