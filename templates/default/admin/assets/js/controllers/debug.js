(function() {
    let currentPage = 1;
    let currentFilters = {
        type: '',
        only_unfixed: false
    };
    let totalPages = 1;
    let currentLogId = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadLogs();
        loadStats();
        initEventListeners();
        
        setInterval(function() {
            if (document.hasFocus()) {
                loadLogs(currentPage);
                loadStats();
            }
        }, 30000);
    });

    function initEventListeners() {
        const toggleSwitch = document.getElementById('debugModeToggle');
        if (toggleSwitch) {
            toggleSwitch.addEventListener('change', function() {
                toggleDebugMode(this.checked);
            });
        }

        document.getElementById('apply-filters').addEventListener('click', function() {
            currentFilters.type = document.getElementById('filter-type').value;
            currentFilters.only_unfixed = document.getElementById('filter-unfixed').checked;
            currentPage = 1;
            loadLogs();
            loadStats();
        });

        document.getElementById('delete-all-logs').addEventListener('click', function() {
            if (confirm(lang === 'ru' ? 'Вы уверены, что хотите удалить ВСЕ логи? Это действие нельзя отменить.' : 'Are you sure you want to delete ALL logs? This action cannot be undone.')) {
                deleteAllLogs();
            }
        });

        document.getElementById('mark-fixed-btn').addEventListener('click', function() {
            if (currentLogId) {
                markAsFixed(currentLogId);
            }
        });
    }

    function loadLogs(page = 1) {
        currentPage = page;
        const url = `${ADMIN_URL}/debug/logs?page=${page}&per_page=20&type=${currentFilters.type}&only_unfixed=${currentFilters.only_unfixed ? '1' : '0'}`;
        
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderLogsTable(data.logs);
                renderPagination(data.current_page, data.pages);
                document.getElementById('logs-count').textContent = data.total;
            }
        })
        .catch(error => console.error('Error loading logs:', error));
    }

    function renderLogsTable(logs) {
        const tbody = document.getElementById('logs-tbody');
        
        if (!logs || logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">' + (lang === 'ru' ? 'Нет записей' : 'No records') + '</td></tr>';
            return;
        }
        
        let html = '';
        logs.forEach(log => {
            const isFixed = log.is_fixed == 1 || log.is_fixed === '1' || log.is_fixed === true;
            const fixedClass = isFixed ? 'log-row-fixed' : '';
            const message = escapeHtml(log.message.length > 100 ? log.message.substring(0, 100) + '...' : log.message);
            const fileInfo = log.file ? `${log.file.split('/').pop()}:${log.line}` : '-';
            
            html += `
                <tr class="${fixedClass}" data-log-id="${log.id}">
                    <td class="text-center">
                        ${isFixed ? '<span class="text-success">✓</span>' : '<span class="text-muted">○</span>'}
                    </td>
                    <td>
                        <span class="log-type-badge ${log.type_class}">
                            ${getTypeIcon(log.type)} ${log.type_label}
                        </span>
                    </td>
                    <td>
                        <div class="log-message" title="${escapeHtml(log.message)}">${escapeHtml(message)}</div>
                    </td>
                    <td>
                        <div class="log-file" title="${escapeHtml(log.file || '-')}">${escapeHtml(fileInfo)}</div>
                    </td>
                    <td>
                        <small class="text-muted">${log.created_formatted}</small>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary view-log" data-id="${log.id}" title="${lang === 'ru' ? 'Просмотр' : 'View'}">
                                <i class="bi bi-eye"></i>
                            </button>
                            ${!isFixed ? `
                            <button class="btn btn-outline-success mark-fixed" data-id="${log.id}" title="${lang === 'ru' ? 'Отметить как исправленную' : 'Mark as fixed'}">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            ` : ''}
                            <button class="btn btn-outline-danger delete-log" data-id="${log.id}" title="${lang === 'ru' ? 'Удалить' : 'Delete'}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        
        document.querySelectorAll('.view-log').forEach(btn => {
            btn.addEventListener('click', () => viewLogDetail(btn.dataset.id));
        });
        
        document.querySelectorAll('.mark-fixed').forEach(btn => {
            btn.addEventListener('click', () => markAsFixed(btn.dataset.id));
        });
        
        document.querySelectorAll('.delete-log').forEach(btn => {
            btn.addEventListener('click', () => deleteLog(btn.dataset.id));
        });
    }

    function renderPagination(currentPage, totalPages) {
        const container = document.getElementById('pagination-container');
        if (!container || totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let html = '<ul class="pagination justify-content-center mb-0">';
        
        if (currentPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">«</a></li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">«</span></li>';
        }
        
        let start = Math.max(1, currentPage - 2);
        let end = Math.min(totalPages, start + 4);
        if (end - start < 4) start = Math.max(1, end - 4);
        
        for (let i = start; i <= end; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        
        if (currentPage < totalPages) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">»</a></li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">»</span></li>';
        }
        
        html += '</ul>';
        container.innerHTML = html;
        
        container.querySelectorAll('.page-link[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                loadLogs(parseInt(link.dataset.page));
            });
        });
    }

    function loadStats() {
        fetch(`${ADMIN_URL}/debug/stats`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-errors').textContent = data.stats.errors;
                document.getElementById('stat-warnings').textContent = data.stats.warnings;
                document.getElementById('stat-notices').textContent = data.stats.notices;
                document.getElementById('stat-exceptions').textContent = data.stats.exceptions;
                document.getElementById('stat-unfixed').textContent = data.stats.unfixed;
                document.getElementById('stat-total').textContent = data.stats.total;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
    }

    function viewLogDetail(id) {
        currentLogId = id;
        const modalBody = document.getElementById('log-detail-content');
        modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        
        fetch(`${ADMIN_URL}/debug/log/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderLogDetail(data.log);
                const modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
                modal.show();
            }
        })
        .catch(error => console.error('Error loading log detail:', error));
    }

    function renderLogDetail(log) {
        const modalBody = document.getElementById('log-detail-content');
        const markBtn = document.getElementById('mark-fixed-btn');

        const isFixed = log.is_fixed == 1 || log.is_fixed === '1' || log.is_fixed === true;
        
        markBtn.style.display = isFixed ? 'none' : 'inline-block';
        
        let traceHtml = '';
        if (log.trace_decoded && log.trace_decoded.length > 0) {
            traceHtml = '<div class="context-section"><h6>' + (lang === 'ru' ? 'Стек вызовов:' : 'Call stack:') + '</h6>';
            log.trace_decoded.forEach((trace, idx) => {
                if (idx < 15) {
                    traceHtml += `
                        <div class="trace-item">
                            <span class="text-muted">#${idx}</span> 
                            <span class="trace-file">${escapeHtml(trace.file || 'unknown')}</span>
                            :<span class="trace-line">${trace.line || '?'}</span>
                            <br>
                            <span class="text-secondary">${escapeHtml(trace.function || 'unknown')}()</span>
                        </div>
                    `;
                }
            });
            traceHtml += '</div>';
        }
        
        let contextHtml = '';
        if (log.context_decoded) {
            contextHtml = '<div class="context-section"><h6>' + (lang === 'ru' ? 'Контекст ошибки:' : 'Error context:') + '</h6>';
            
            if (log.context_decoded.get) {
                contextHtml += `<div class="mb-3"><strong>${lang === 'ru' ? 'GET параметры:' : 'GET parameters:'}</strong><pre class="context-pre">${escapeHtml(JSON.stringify(log.context_decoded.get, null, 2))}</pre></div>`;
            }
            if (log.context_decoded.post) {
                contextHtml += `<div class="mb-3"><strong>${lang === 'ru' ? 'POST параметры:' : 'POST parameters:'}</strong><pre class="context-pre">${escapeHtml(JSON.stringify(log.context_decoded.post, null, 2))}</pre></div>`;
            }
            if (log.context_decoded.session) {
                contextHtml += `<div class="mb-3"><strong>${lang === 'ru' ? 'Данные сессии:' : 'Session data:'}</strong><pre class="context-pre">${escapeHtml(JSON.stringify(log.context_decoded.session, null, 2))}</pre></div>`;
            }
            if (log.context_decoded.server) {
                contextHtml += `<div class="mb-3"><strong>${lang === 'ru' ? 'Информация о запросе:' : 'Request information:'}</strong><pre class="context-pre">${escapeHtml(JSON.stringify(log.context_decoded.server, null, 2))}</pre></div>`;
            }
            
            contextHtml += '</div>';
        }
        
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>${lang === 'ru' ? 'Тип ошибки:' : 'Error type:'}</strong><br>
                        <span class="log-type-badge ${log.type_class} mt-1">${getTypeIcon(log.type)} ${log.type_label}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>${lang === 'ru' ? 'Код ошибки:' : 'Error code:'}</strong><br>
                        <code>${log.code || '-'}</code>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <strong>${lang === 'ru' ? 'Сообщение:' : 'Message:'}</strong><br>
                <div class="alert alert-${log.type_class} mt-1">${escapeHtml(log.message)}</div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <strong>${lang === 'ru' ? 'Файл:' : 'File:'}</strong><br>
                        <code class="text-danger">${escapeHtml(log.file || '-')}</code>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <strong>${lang === 'ru' ? 'Строка:' : 'Line:'}</strong><br>
                        <code class="text-danger">${log.line || '-'}</code>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>${lang === 'ru' ? 'Дата и время:' : 'Date & time:'}</strong><br>
                        <code>${log.created_formatted}</code>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>${lang === 'ru' ? 'URL:' : 'URL:'}</strong><br>
                        <code class="small">${escapeHtml(log.url || '-')}</code>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>${lang === 'ru' ? 'IP адрес:' : 'IP address:'}</strong><br>
                        <code>${escapeHtml(log.ip || '-')}</code>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>${lang === 'ru' ? 'Метод запроса:' : 'Request method:'}</strong><br>
                        <code>${escapeHtml(log.method || '-')}</code>
                    </div>
                </div>
            </div>
            
            ${traceHtml}
            ${contextHtml}
        `;
        
        modalBody.innerHTML = html;
    }

    function markAsFixed(id) {
        fetch(`${ADMIN_URL}/debug/mark-fixed/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadLogs(currentPage);
                loadStats();
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('logDetailModal'));
                if (modal) modal.hide();
                
                showNotification(lang === 'ru' ? 'Ошибка отмечена как исправленная' : 'Error marked as fixed', 'success');
            }
        })
        .catch(error => console.error('Error marking as fixed:', error));
    }

    function deleteLog(id) {
        if (!confirm(lang === 'ru' ? 'Удалить эту запись?' : 'Delete this record?')) return;
        
        fetch(`${ADMIN_URL}/debug/delete/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadLogs(currentPage);
                loadStats();
                showNotification(lang === 'ru' ? 'Лог удален' : 'Log deleted', 'success');
            }
        })
        .catch(error => console.error('Error deleting log:', error));
    }

    function deleteAllLogs() {
        fetch(`${ADMIN_URL}/debug/delete-all`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadLogs(1);
                loadStats();
                showNotification(lang === 'ru' ? 'Все логи удалены' : 'All logs deleted', 'success');
            }
        })
        .catch(error => console.error('Error deleting all logs:', error));
    }

    function toggleDebugMode(enabled) {
        fetch(`${ADMIN_URL}/debug/toggle`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                const toggle = document.getElementById('debugModeToggle');
                if (toggle) toggle.checked = data.debug_enabled;
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message, 'danger');
            }
        })
        .catch(error => console.error('Error toggling debug mode:', error));
    }

    function getTypeIcon(type) {
        const icons = {
            error: '<i class="bi bi-exclamation-triangle-fill"></i>',
            warning: '<i class="bi bi-exclamation-triangle"></i>',
            notice: '<i class="bi bi-info-circle-fill"></i>',
            exception: '<i class="bi bi-bug-fill"></i>'
        };
        return icons[type] || '<i class="bi bi-question-circle-fill"></i>';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showNotification(message, type = 'info') {
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
})();