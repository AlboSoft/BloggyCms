<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="30%"><?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_TABLE_CONTROLLER; ?></th>
                <th width="15%" class="text-center"><?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_TABLE_ENABLED; ?></th>
                <th width="35%"><?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_TABLE_INFO; ?></th>
                <th width="25%"><?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_TABLE_STATUS; ?></th>
                <th width="10%" class="text-end"><?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_TABLE_ACTIONS; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($controllers as $controller) { ?>
                <tr class="controller-row">
                    <td>
                        <div class="d-flex align-items-center">
                            <?php if($controller['is_system']) { ?>
                                <span class="badge bg-info text-info me-2" title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_SYSTEM_TITLE; ?>" style="min-width: 32px; text-align: center;" data-bs-toggle="tooltip">
                                    <?php echo bloggy_icon('bs', 'shield-fill-check', '16', '#0dcaf0'); ?>
                                </span>
                            <?php } else { ?>
                                <span class="badge bg-secondary text-secondary me-2" style="min-width: 32px; text-align: center;">
                                    <?php echo bloggy_icon('bs', 'box', '16', '#6c757d'); ?>
                                </span>
                            <?php } ?>
                            <div>
                                <div class="controller-name">
                                    <strong><?php echo html($controller['name']) ?></strong>
                                </div>
                                <div class="controller-path">
                                    <code class="text-muted"><?php echo html($controller['path']) ?></code>
                                </div>
                            </div>
                        </div>
                    </div>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input type="checkbox" 
                                class="form-check-input controller-toggle" 
                                data-controller="<?php echo $controller['key']; ?>"
                                <?php echo $controller['is_enabled'] ? 'checked' : ''; ?>
                                <?php echo isset($controller['is_protected']) && $controller['is_protected'] ? 'disabled' : ''; ?>
                                style="width: 3em; height: 1.5em;">
                            <?php if (isset($controller['is_protected']) && $controller['is_protected']) { ?>
                                <span class="badge bg-danger" data-bs-toggle="tooltip" title="Protected">
                                    <?php echo bloggy_icon('bs', 'info-lg', '11', '#b20000'); ?>
                                </span>
                            <?php } ?>
                        </div>
                    </td>
                    <td>
                        <div class="controller-info">
                            <?php if(!empty($controller['description'])) { ?>
                                <div class="mb-2 text-primary small">
                                    <em><?php echo html($controller['description']) ?></em>
                                </div>
                            <?php } ?>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-light text-dark border" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_AUTHOR_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'person', '12', '#6c757d', 'me-1'); ?>
                                    <?php echo html($controller['author']) ?>
                                </span>
                                <span class="badge bg-light text-dark border" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_VERSION_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'tag', '12', '#6c757d', 'me-1'); ?>
                                    v<?php echo html($controller['version']) ?>
                                </span>
                                <?php if($controller['actions_count'] > 0) { ?>
                                    <span class="badge bg-light text-dark border" data-bs-toggle="tooltip" title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_ACTIONS_TITLE; ?>">
                                        <?php echo bloggy_icon('bs', 'lightning', '12', '#6c757d', 'me-1'); ?>
                                        <?php echo $controller['actions_count'] ?>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if($controller['has_settings']) { ?>
                                <span class="badge bg-success text-success border" title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_HAS_SETTINGS_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'gear-fill', '14', '#0a4a2cff', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_HAS_SETTINGS; ?>
                                </span>
                            <?php } ?>
                            
                            <?php if($controller['has_routing']) { ?>
                                <span class="badge bg-primary text-primary border" title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_HAS_ROUTING_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'signpost-split', '14', '#afcbf5ff', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_HAS_ROUTING; ?>
                                </span>
                            <?php } ?>
                            
                            <?php if($controller['is_system']) { ?>
                                <span class="badge bg-info text-info border" title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_SYSTEM_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'shield-fill-check', '14', '#0b7c92ff', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_SYSTEM; ?>
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                    <td>
                        <div class="d-flex justify-content-end gap-1">
                            <?php if($controller['has_settings']) { ?>
                                <a href="<?= ADMIN_URL ?>/settings?tab=components&controller=<?= $controller['path'] ?>"
                                   class="btn btn-sm btn-outline-primary border"
                                   title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_SETTINGS_BTN_TITLE; ?>"
                                   data-bs-toggle="tooltip">
                                    <?php echo bloggy_icon('bs', 'gear-fill', '16'); ?>
                                </a>
                            <?php } ?>
                            
                            <button type="button" 
                                    class="btn btn-sm btn-outline-secondary border controller-info-btn"
                                    title="<?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_INFO_BTN_TITLE; ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#controllerInfoModal"
                                    data-controller='<?= html(json_encode([
                                        'name' => $controller['name'],
                                        'path' => $controller['path'],
                                        'author' => $controller['author'],
                                        'version' => $controller['version'],
                                        'description' => $controller['description'],
                                        'is_system' => $controller['is_system'],
                                        'has_settings' => $controller['has_settings'],
                                        'has_routing' => $controller['has_routing'],
                                        'actions_count' => $controller['actions_count']
                                    ]), ENT_QUOTES) ?>'>
                                <?php echo bloggy_icon('bs', 'info-circle', '16'); ?>
                            </button>
                        </div>
                    </div>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="controllerInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="controllerInfoModalLabel"><?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_MODAL_TITLE; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="controllerInfoContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo LANG_TEMPLATE_CONTROLLERS_INDEX_MODAL_CLOSE; ?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    document.querySelectorAll('.controller-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const controller = this.dataset.controller;
            const enabled = this.checked ? 1 : 0;
            const originalState = this.checked;
            
            this.disabled = true;
            
            fetch(ADMIN_URL + '/controllers/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `controller=${controller}&enabled=${enabled}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Notification !== 'undefined' && Notification.success) {
                        Notification.success('Контроллер "' + controller + '" ' + (enabled ? 'включен' : 'отключен'));
                    } else {
                        showNotification('Контроллер "' + controller + '" ' + (enabled ? 'включен' : 'отключен'), 'success');
                    }
                } else {
                    this.checked = originalState;
                    if (typeof Notification !== 'undefined' && Notification.error) {
                        Notification.error(data.message || 'Ошибка при сохранении');
                    } else {
                        showNotification(data.message || 'Ошибка при сохранении', 'error');
                    }
                }
            })
            .catch(error => {
                this.checked = originalState;
                if (typeof Notification !== 'undefined' && Notification.error) {
                    Notification.error('Ошибка сети: ' + error.message);
                } else {
                    showNotification('Ошибка сети: ' + error.message, 'error');
                }
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
    
    const controllerInfoModal = document.getElementById('controllerInfoModal');
    if (controllerInfoModal) {
        controllerInfoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const controllerData = JSON.parse(button.getAttribute('data-controller'));
            const modalBody = document.getElementById('controllerInfoContent');
            
            let html = `
                <div class="row">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3">${controllerData.name}</h6>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="mb-2">
                            <small class="text-muted d-block">Путь</small>
                            <code>/controllers/${controllerData.path}</code>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Автор</small>
                            <div>${controllerData.author}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Версия</small>
                            <div>${controllerData.version}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <small class="text-muted d-block">Тип</small>
                            <div>
                                ${controllerData.is_system ? 
                                    '<span class="badge bg-info text-white">Системный</span>' : 
                                    '<span class="badge bg-secondary text-white">Пользовательский</span>'}
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Настройки</small>
                            <div>
                                ${controllerData.has_settings ? 
                                    '<span class="badge bg-success text-white">Есть</span>' : 
                                    '<span class="badge bg-secondary text-white">Нет</span>'}
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Роутинг</small>
                            <div>
                                ${controllerData.has_routing ? 
                                    '<span class="badge bg-primary text-white">Настроен</span>' : 
                                    '<span class="badge bg-secondary text-white">Не настроен</span>'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            if (controllerData.description) {
                html += `
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <small class="text-muted d-block">Описание</small>
                                <div class="alert alert-light bg-light border">
                                    ${controllerData.description}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            if (controllerData.actions_count > 0) {
                html += `
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <small class="text-muted d-block">Действий</small>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark border">
                                        ${controllerData.actions_count} шт.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            modalBody.innerHTML = html;
        });
    }
    
    function showNotification(message, type) {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) return;
        
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastEl.remove();
        });
    }
});
</script>