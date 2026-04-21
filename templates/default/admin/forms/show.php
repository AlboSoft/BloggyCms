<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'envelope', '24', '#000', 'me-2'); ?>
            <?php echo sprintf(LANG_TEMPLATE_FORMS_SHOW_TITLE, html($form['name'])); ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/forms" class="btn btn-outline-secondary me-2">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_FORMS_SHOW_BACK_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/forms/edit/<?php echo $form['id']; ?>" class="btn btn-outline-primary me-2">
                <?php echo bloggy_icon('bs', 'pencil', '16', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_FORMS_SHOW_EDIT_BTN; ?>
            </a>
            <button type="button" class="btn btn-success" onclick="exportToCSV()">
                <?php echo bloggy_icon('bs', 'download', '16', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_FORMS_SHOW_EXPORT_BTN; ?>
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'envelope', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $submissionsCount; ?></h3>
                            <small><?php echo LANG_TEMPLATE_FORMS_SHOW_STAT_TOTAL; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'envelope-open', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $newCount; ?></h3>
                            <small><?php echo LANG_TEMPLATE_FORMS_SHOW_STAT_NEW; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'check-circle', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $processedCount; ?></h3>
                            <small><?php echo LANG_TEMPLATE_FORMS_SHOW_STAT_PROCESSED; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'shield-slash', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $spamCount; ?></h3>
                            <small><?php echo LANG_TEMPLATE_FORMS_SHOW_STAT_SPAM; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($submissions)) { ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <?php echo bloggy_icon('bs', 'inbox', '48', '#6C6C6C', 'mb-3'); ?>
            <h5 class="text-muted"><?php echo LANG_TEMPLATE_FORMS_SHOW_NO_SUBMISSIONS_TITLE; ?></h5>
            <p class="text-muted mb-4"><?php echo LANG_TEMPLATE_FORMS_SHOW_NO_SUBMISSIONS_TEXT; ?></p>
            <a href="<?php echo ADMIN_URL; ?>/forms/preview/<?php echo $form['id']; ?>" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'eye', '16', '#fff', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_FORMS_SHOW_PREVIEW_BTN; ?>
            </a>
        </div>
    </div>
    <?php } else { ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <?php echo bloggy_icon('bs', 'list-ul', '20', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_FORMS_SHOW_SUBMISSIONS_LIST; ?>
            </h5>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteAllSubmissions()">
                    <?php echo bloggy_icon('bs', 'trash', '16', '#000', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_SHOW_DELETE_ALL_BTN; ?>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?php echo LANG_TEMPLATE_FORMS_SHOW_TABLE_ID; ?></th>
                            <th><?php echo LANG_TEMPLATE_FORMS_SHOW_TABLE_DATE; ?></th>
                            <th><?php echo LANG_TEMPLATE_FORMS_SHOW_TABLE_IP; ?></th>
                            <th><?php echo LANG_TEMPLATE_FORMS_SHOW_TABLE_DATA; ?></th>
                            <th><?php echo LANG_TEMPLATE_FORMS_SHOW_TABLE_STATUS; ?></th>
                            <th class="end"><?php echo LANG_TEMPLATE_FORMS_SHOW_TABLE_ACTIONS; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission) { 
                            $dataPreview = array();
                            
                            $fieldInfo = [];
                            if (!empty($form['structure']) && is_array($form['structure'])) {
                                foreach ($form['structure'] as $field) {
                                    if (!empty($field['name'])) {
                                        $fieldInfo[$field['name']] = [
                                            'label' => $field['label'] ?? $field['name'],
                                            'type' => $field['type'] ?? 'text',
                                            'options' => $field['options'] ?? []
                                        ];
                                    }
                                }
                            }
                            
                            foreach ($submission['data'] as $key => $value) {
                                if (in_array($key, ['form_id', 'form_slug', 'csrf_token', '_files'])) {
                                    continue;
                                }
                                
                                $info = $fieldInfo[$key] ?? ['label' => $key, 'type' => 'text', 'options' => []];
                                $fieldLabel = $info['label'];
                                $fieldType = $info['type'];
                                $fieldOptions = $info['options'];
                                $displayValue = $value;
                                
                                if (in_array($fieldType, ['select', 'radio']) && !empty($fieldOptions)) {
                                    $displayValue = $value;
                                    foreach ($fieldOptions as $option) {
                                        if (isset($option['value']) && (string)$option['value'] === (string)$value) {
                                            $displayValue = $option['label'] ?? $option['value'];
                                            break;
                                        }
                                    }
                                } elseif ($fieldType === 'checkbox') {
                                    $displayValue = !empty($value) ? LANG_TEMPLATE_FORMS_SHOW_CHECKBOX_YES : LANG_TEMPLATE_FORMS_SHOW_CHECKBOX_NO;
                                }
                                
                                if (is_array($displayValue)) {
                                    $displayValue = implode(', ', $displayValue);
                                }
                                if (mb_strlen($displayValue) > 20) {
                                    $displayValue = mb_substr($displayValue, 0, 20) . '...';
                                }
                                
                                $dataPreview[] = '<strong>' . html($fieldLabel) . ':</strong> ' . html($displayValue);
                            }

                            if (!empty($submission['files'])) {
                                foreach ($submission['files'] as $file) {
                                    $fileLabel = $fieldInfo[$file['field_name']]['label'] ?? $file['field_name'];
                                    $dataPreview[] = '<strong>📎 ' . html($fileLabel) . ':</strong> ' . html($file['file_name']);
                                }
                            }
                            ?>
                        <tr>
                            <td>
                                <strong>#<?php echo html($submission['id']); ?></strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('d.m.Y H:i', strtotime($submission['created_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <code><?php echo html($submission['ip_address']); ?></code>
                            </td>
                            <td>
                                <small><?php echo implode('<br>', $dataPreview); ?></small>
                            </td>
                            <td>
                                <select class="form-select form-select-sm status-select" 
                                        data-id="<?php echo $submission['id']; ?>"
                                        data-original-value="<?php echo $submission['status']; ?>"
                                        style="width: 120px;">
                                    <option value="new" <?php echo $submission['status'] === 'new' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_NEW; ?></option>
                                    <option value="read" <?php echo $submission['status'] === 'read' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_READ; ?></option>
                                    <option value="processed" <?php echo $submission['status'] === 'processed' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_PROCESSED; ?></option>
                                    <option value="spam" <?php echo $submission['status'] === 'spam' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_SPAM; ?></option>
                                </select>
                            </td>
                            <td class="end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" 
                                            class="btn btn-outline-primary view-submission"
                                            data-id="<?php echo $submission['id']; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewSubmissionModal">
                                        <?php echo bloggy_icon('bs', 'eye', '16', '#000'); ?>
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-danger delete-submission"
                                        data-id="<?php echo $submission['id']; ?>"
                                        title="<?php echo LANG_TEMPLATE_FORMS_SHOW_DELETE_TITLE; ?>">
                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($totalPages > 1) { ?>
        <div class="card-footer bg-white border-0">
            <nav aria-label="<?php echo LANG_TEMPLATE_FORMS_SHOW_PAGINATION_ARIA; ?>">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubmissionModalLabel">
                    <?php echo bloggy_icon('bs', 'envelope-open', '20', '#000', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_SHOW_MODAL_TITLE; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo LANG_TEMPLATE_FORMS_SHOW_MODAL_CLOSE; ?>"></button>
            </div>
            <div class="modal-body" id="submission-details">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php echo LANG_TEMPLATE_FORMS_SHOW_LOADING; ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?php echo bloggy_icon('bs', 'x-circle', '16', '#000', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_SHOW_MODAL_CLOSE_BTN; ?>
                </button>
                <button type="button" class="btn btn-primary" onclick="printSubmission()">
                    <?php echo bloggy_icon('bs', 'printer', '16', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_SHOW_PRINT_BTN; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.status-select').forEach(select => {
            updateStatusSelectStyle(select);
            
            select.addEventListener('change', function() {
                const submissionId = this.dataset.id;
                const newStatus = this.value;
                const originalValue = this.dataset.originalValue;
                const originalColor = this.style.borderColor;
                this.style.borderColor = '#ffc107';
                fetch('<?php echo ADMIN_URL; ?>/forms/update-submission-status/' + submissionId + '?status=' + newStatus, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateStatusSelectStyle(this);
                        this.dataset.originalValue = newStatus;
                        showNotification('<?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_UPDATED; ?>', 'success');
                    } else {
                        this.value = originalValue;
                        updateStatusSelectStyle(this);
                        showNotification(data.message || '<?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_UPDATE_ERROR; ?>', 'error');
                    }
                })
                .catch(error => {
                    this.value = originalValue;
                    updateStatusSelectStyle(this);
                    showNotification('<?php echo LANG_TEMPLATE_FORMS_SHOW_NETWORK_ERROR; ?>' + error.message, 'error');
                })
                .finally(() => {
                    this.style.borderColor = originalColor;
                });
            });
        });
    });
    
    function updateStatusSelectStyle(select) {
        const statusColors = {
            'new': 'warning',
            'read': 'info',
            'processed': 'success',
            'spam': 'danger'
        };
        
        const color = statusColors[select.value] || 'secondary';
        select.className = `form-select form-select-sm status-select border-${color}`;
    }
    
    document.querySelectorAll('.view-submission').forEach(btn => {
        btn.addEventListener('click', function() {
            const submissionId = this.dataset.id;
            const modalBody = document.getElementById('submission-details');
            
            modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden"><?php echo LANG_TEMPLATE_FORMS_SHOW_LOADING; ?></span>
                </div>
            </div>
            `;
            
            const formId = <?php echo $form['id']; ?>;
            let fieldLabels = {};
            
            fetch('<?php echo ADMIN_URL; ?>/forms/get-structure/' + formId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(structureData => {
                if (structureData.success && structureData.structure) {
                    structureData.structure.forEach(field => {
                        if (field.name && field.label) {
                            fieldLabels[field.name] = field.label;
                        }
                    });
                }
                return fetch('<?php echo ADMIN_URL; ?>/forms/get-submission/' + submissionId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.submission) {
                    const submission = data.submission;
                    let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_ID; ?></strong> #${submission.id}
                            </div>
                            <div class="mb-3">
                                <strong><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_DATE; ?></strong> ${submission.created_at}
                            </div>
                            <div class="mb-3">
                                <strong><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_IP; ?></strong> ${submission.ip_address || '<?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_NOT_SPECIFIED; ?>'}
                            </div>
                            <div class="mb-3">
                                <strong><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_STATUS; ?></strong>
                                <span class="badge bg-${getStatusColor(submission.status)}">
                                    ${getStatusText(submission.status)}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_USER_AGENT; ?></strong>
                                <div class="small text-muted">${submission.user_agent || '<?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_NOT_SPECIFIED; ?>'}</div>
                            </div>
                            <div class="mb-3">
                                <strong><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_REFERER; ?></strong>
                                <div class="small text-muted">${submission.referer || '<?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_NOT_SPECIFIED; ?>'}</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-3"><?php echo bloggy_icon('bs', 'card-text', '16', '#000', 'me-2'); ?><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_FORM_DATA; ?></h6>
                    `;
                    
                    if (submission.data && Object.keys(submission.data).length > 0) {
                        html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                        
                        for (const [fieldName, value] of Object.entries(submission.data)) {
                            if (['form_id', 'form_slug', 'csrf_token'].includes(fieldName)) {
                                continue;
                            }
                            
                            const fieldLabel = fieldLabels[fieldName] || fieldName;
                            
                            let displayValue = value;
                            if (Array.isArray(value)) {
                                displayValue = value.join(', ');
                            }
                            
                            html += `
                            <tr>
                                <td style="width: 30%"><strong>${escapeHtml(fieldLabel)}</strong></td>
                                <td>${escapeHtml(displayValue)}</td>
                            </tr>
                            `;
                        }
                        
                        html += '</table></div>';
                    } else {
                        html += '<div class="alert alert-info"><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_NO_DATA; ?></div>';
                    }
                    
                    if (submission.files && submission.files.length > 0) {
                        html += `
                        <hr>
                        <h6 class="mb-3"><?php echo bloggy_icon('bs', 'paperclip', '16', '#000', 'me-2'); ?><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_FILES; ?></h6>
                        <div class="row">
                        `;
                        submission.files.forEach(file => {
                            html += `
                            <div class="col-md-6 mb-2">
                                <div class="card">
                                    <div class="card-body p-2">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <?php echo bloggy_icon('bs', 'file-earmark', '32', '#000'); ?>
                                            </div>
                                            <div>
                                                <div class="small"><strong>${escapeHtml(file.file_name)}</strong></div>
                                                <div class="small text-muted">${formatFileSize(file.file_size)}</div>
                                                <div class="small">
                                                    <a href="<?php echo BASE_URL; ?>/${escapeHtml(file.file_path)}" target="_blank" class="text-decoration-none">
                                                        <?php echo bloggy_icon('bs', 'download', '16', '#000', 'me-1'); ?><?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_DOWNLOAD; ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        });
                        html += '</div>';
                    }
                    
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-2'); ?>
                        ${data.message || '<?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_LOAD_ERROR; ?>'}
                    </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_FORMS_SHOW_DETAIL_ERROR; ?> ${error.message}
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                            <?php echo bloggy_icon('bs', 'arrow-clockwise', '16', '#000', 'me-1'); ?><?php echo LANG_TEMPLATE_FORMS_SHOW_RELOAD_BTN; ?>
                        </button>
                    </div>
                </div>
                `;
            });
        });
    });
    
    document.querySelectorAll('.delete-submission').forEach(btn => {
    btn.addEventListener('click', function() {
        const submissionId = this.dataset.id;
        const button = this;
        const row = button.closest('tr');
        
        if (confirm('<?php echo LANG_TEMPLATE_FORMS_SHOW_DELETE_CONFIRM; ?>')) {
            const originalIcon = button.innerHTML;
            button.innerHTML = '<?php echo bloggy_icon('bs', 'hourglass-split', '16', '#000'); ?>';
            button.disabled = true;
            
            console.log('<?php echo LANG_TEMPLATE_FORMS_SHOW_DELETE_LOG; ?>', submissionId);
            
            fetch('<?php echo ADMIN_URL; ?>/forms/delete-submission/' + submissionId, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('<?php echo LANG_TEMPLATE_FORMS_SHOW_RESPONSE_STATUS; ?>', response.status);
                
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                
                return response.text().then(text => {
                    console.log('<?php echo LANG_TEMPLATE_FORMS_SHOW_RESPONSE_TEXT; ?>', text);
                    
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('<?php echo LANG_TEMPLATE_FORMS_SHOW_JSON_PARSE_ERROR; ?>', e);
                        throw new Error('<?php echo LANG_TEMPLATE_FORMS_SHOW_INVALID_JSON; ?>' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                console.log('<?php echo LANG_TEMPLATE_FORMS_SHOW_PARSED_DATA; ?>', data);
                
                if (data.success) {
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0.5';
                    
                    showNotification('<?php echo LANG_TEMPLATE_FORMS_SHOW_DELETE_SUCCESS_NOTIFY; ?>', 'success');
                    
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    console.error('<?php echo LANG_TEMPLATE_FORMS_SHOW_SERVER_ERROR; ?>', data.message);
                    button.innerHTML = originalIcon;
                    button.disabled = false;
                    showNotification('<?php echo LANG_TEMPLATE_FORMS_SHOW_SERVER_ERROR_MSG; ?>' + (data.message || '<?php echo LANG_TEMPLATE_FORMS_SHOW_UNKNOWN_ERROR; ?>'), 'error');
                }
            })
            .catch(error => {
                console.error('<?php echo LANG_TEMPLATE_FORMS_SHOW_DETAILED_ERROR; ?>', error);
                console.error('<?php echo LANG_TEMPLATE_FORMS_SHOW_ERROR_STACK; ?>', error.stack);
                
                button.innerHTML = originalIcon;
                button.disabled = false;
                showNotification('<?php echo LANG_TEMPLATE_FORMS_SHOW_ERROR_MSG; ?>' + error.message, 'error');
            });
        }
    });
});
    
    function exportToCSV() {
        window.location.href = '<?php echo ADMIN_URL; ?>/forms/export/<?php echo $form['id']; ?>';
    }
    
    function deleteAllSubmissions() {
        if (confirm('<?php echo LANG_TEMPLATE_FORMS_SHOW_DELETE_ALL_CONFIRM; ?>')) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<?php echo bloggy_icon('bs', 'hourglass-split', '16', '#000', 'me-1'); ?><?php echo LANG_TEMPLATE_FORMS_SHOW_DELETING; ?>';
            button.disabled = true;
            
            fetch('<?php echo ADMIN_URL; ?>/forms/delete-all-submissions/<?php echo $form['id']; ?>', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showNotification(`<?php echo LANG_TEMPLATE_FORMS_SHOW_DELETED_COUNT; ?> ${data.count || 0} <?php echo LANG_TEMPLATE_FORMS_SHOW_SUBMISSIONS; ?>`, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showNotification(data.message || '<?php echo LANG_TEMPLATE_FORMS_SHOW_DELETE_ALL_ERROR; ?>', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalText;
                button.disabled = false;
                showNotification('<?php echo LANG_TEMPLATE_FORMS_SHOW_NETWORK_ERROR; ?>' + error.message, 'error');
            });
        }
    }
    
    function getStatusColor(status) {
        switch(status) {
            case 'new': return 'warning';
            case 'read': return 'info';
            case 'processed': return 'success';
            case 'spam': return 'danger';
            default: return 'secondary';
        }
    }
    
    function getStatusText(status) {
        switch(status) {
            case 'new': return '<?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_NEW; ?>';
            case 'read': return '<?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_READ; ?>';
            case 'processed': return '<?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_PROCESSED; ?>';
            case 'spam': return '<?php echo LANG_TEMPLATE_FORMS_SHOW_STATUS_SPAM; ?>';
            default: return status;
        }
    }
    
    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function printSubmission() {
        const printContent = document.getElementById('submission-details').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <html>
                <head>
                    <title><?php echo sprintf(LANG_TEMPLATE_FORMS_SHOW_PRINT_TITLE, html($form['name'])); ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                </head>
                <body>
                    <div class="container mt-4">
                        <h4><?php echo sprintf(LANG_TEMPLATE_FORMS_SHOW_PRINT_HEADER, html($form['name'])); ?></h4>
                        <hr>
                        ${printContent}
                    </div>
                </body>
            </html>
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }
    
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php echo LANG_TEMPLATE_FORMS_SHOW_CLOSE; ?>"></button>
        `;
        
        document.body.appendChild(notification);
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    function updateStatisticsAfterDelete() {
        const totalElement = document.querySelector('.col-md-3 .bg-info h3');
        const newElement = document.querySelector('.col-md-3 .bg-warning h3');
        const processedElement = document.querySelector('.col-md-3 .bg-success h3');
        const spamElement = document.querySelector('.col-md-3 .bg-danger h3');
        
        if (totalElement) {
            let total = parseInt(totalElement.textContent) || 0;
            totalElement.textContent = Math.max(0, total - 1);
        }
    }

    function updateTotalItemsCount() {
        const remainingRows = document.querySelectorAll('tbody tr').length;
        const totalItemsElement = document.querySelector('.col-md-3 .bg-info h3');
        if (totalItemsElement) {
            totalItemsElement.textContent = remainingRows;
        }
    }

</script>
<?php admin_bottom_js(ob_get_clean()); ?>