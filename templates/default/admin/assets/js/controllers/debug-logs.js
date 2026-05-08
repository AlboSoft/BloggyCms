let currentPage = 1;
let totalPages = 1;
let allLogs = [];

function loadErrorLogs() {
    const tbody = document.getElementById('logs-tbody');
    tbody.innerHTML = `
        <tr><td colspan="3" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">${lang === 'ru' ? 'Загрузка логов...' : 'Loading logs...'}</p>
        </td></tr>
    `;
    
    fetch(ADMIN_URL + '/debug/get-error-logs', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            allLogs = data.logs;
            document.getElementById('log-file-path').textContent = data.log_file_path;
            document.getElementById('log-file-size').textContent = `(${data.log_file_size})`;
            applyFiltersAndRender();
        } else {
            showError(data.message || (lang === 'ru' ? 'Ошибка загрузки системных логов' : 'Failed to load system logs'));
        }
    })
    .catch(error => {
        console.error('Error loading logs:', error);
        showError(lang === 'ru' ? 'Ошибка загрузки системных логов' : 'Error loading system logs');
    });
}

function applyFiltersAndRender() {
    const typeFilter = document.getElementById('log-type-filter').value;
    const searchQuery = document.getElementById('log-search').value.toLowerCase();
    const sortOrder = document.getElementById('log-sort').value;
    
    let filteredLogs = [...allLogs];
    
    if (typeFilter !== 'all') {
        filteredLogs = filteredLogs.filter(log => log.type === typeFilter);
    }
    
    if (searchQuery) {
        filteredLogs = filteredLogs.filter(log => 
            log.message.toLowerCase().includes(searchQuery)
        );
    }
    
    if (sortOrder === 'asc') {
        filteredLogs.reverse();
    }
    
    const perPage = 50;
    totalPages = Math.ceil(filteredLogs.length / perPage);
    const start = (currentPage - 1) * perPage;
    const paginatedLogs = filteredLogs.slice(start, start + perPage);
    
    renderLogsTable(paginatedLogs);
    renderPagination();
    document.getElementById('logs-count').textContent = filteredLogs.length;
}

function renderLogsTable(logs) {
    const tbody = document.getElementById('logs-tbody');
    
    if (!logs || logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-5 text-muted">' + (lang === 'ru' ? 'Нет записей' : 'No items') + '</td></tr>';
        return;
    }
    
    let html = '';
    logs.forEach(log => {
        const typeClass = getTypeClass(log.type);
        const typeLabel = log.type.toUpperCase();
        
        html += `
            <tr>
                <td><small class="text-muted">${escapeHtml(log.date)}</small></td>
                <td><span class="badge bg-${typeClass}">${typeLabel}</span></td>
                <td><code class="small" style="white-space: pre-wrap; word-break: break-all;">${escapeHtml(log.message)}</code></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function renderPagination() {
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
            currentPage = parseInt(link.dataset.page);
            applyFiltersAndRender();
        });
    });
}

function getTypeClass(type) {
    switch(type) {
        case 'error': return 'danger';
        case 'warning': return 'warning';
        case 'debug': return 'info';
        default: return 'secondary';
    }
}

function clearErrorLogs() {
    if (confirm(lang === 'ru' ? 'Вы уверены, что хотите очистить системный лог? Это действие нельзя отменить.' : 'Are you sure you want to clear the system log? This action cannot be undone.')) {
        fetch(ADMIN_URL + '/debug/clear-error-logs', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(lang === 'ru' ? 'Системный лог очищен' : 'System log cleared', 'success');
                } else {
                    alert(lang === 'ru' ? 'Системный лог очищен' : 'System log cleared');
                }
                loadErrorLogs();
            } else {
                const msg = data.message || (lang === 'ru' ? 'Ошибка при очистке лога' : 'Error clearing log');
                if (typeof showNotification === 'function') {
                    showNotification(msg, 'error');
                } else {
                    alert(msg);
                }
            }
        })
        .catch(error => {
            console.error('Error clearing logs:', error);
            if (typeof showNotification === 'function') {
                showNotification(lang === 'ru' ? 'Ошибка сети при очистке лога' : 'Network error while clearing log', 'error');
            } else {
                alert(lang === 'ru' ? 'Ошибка сети при очистке лога' : 'Network error while clearing log');
            }
        });
    }
}

function showError(message) {
    const tbody = document.getElementById('logs-tbody');
    tbody.innerHTML = `<tr><td colspan="3" class="text-center py-5 text-danger">${escapeHtml(message)}</td></tr>`;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    loadErrorLogs();
    
    const applyBtn = document.getElementById('apply-log-filters');
    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            currentPage = 1;
            applyFiltersAndRender();
        });
    }
    
    const refreshBtn = document.getElementById('refresh-logs');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            loadErrorLogs();
        });
    }
    
    const clearBtn = document.getElementById('clear-error-logs');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearErrorLogs);
    }
    
    const searchInput = document.getElementById('log-search');
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                currentPage = 1;
                applyFiltersAndRender();
            }
        });
    }
});