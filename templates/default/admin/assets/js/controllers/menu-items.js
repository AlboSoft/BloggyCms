(function() {
    let sortableInstance = null;
    let currentMenuId = null;

    document.addEventListener('DOMContentLoaded', function() {
        initSortable();
        initDeleteButtons();
        initExpandCollapse();
        updateStatistics();
    });

    function initSortable() {
        const container = document.getElementById('sortable-items');
        if (!container || typeof Sortable === 'undefined') return;

        currentMenuId = container.dataset.menuId;

        sortableInstance = new Sortable(container, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function() {
                saveOrder();
            }
        });
    }

    function saveOrder() {
        const items = [];
        const rows = document.querySelectorAll('#sortable-items tr');

        rows.forEach((row, index) => {
            items.push({
                id: row.dataset.id,
                parent_id: row.dataset.parentId || null,
                order: index
            });
        });

        fetch(ADMIN_URL + '/menu/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                menu_id: currentMenuId,
                order: items
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(lang === 'ru' ? 'Порядок сохранен' : 'Order saved', 'success');
                updateLevelIndicators();
            } else {
                showNotification(data.message || (lang === 'ru' ? 'Ошибка при сохранении порядка' : 'Error saving order'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(lang === 'ru' ? 'Ошибка сети' : 'Network error', 'error');
        });
    }

    function updateLevelIndicators() {
        const rows = document.querySelectorAll('#sortable-items tr');
        
        const updateRecursive = (rowsList, currentLevel = 0, parentId = null) => {
            let found = true;
            
            while (found) {
                found = false;
                for (let i = 0; i < rowsList.length; i++) {
                    const row = rowsList[i];
                    const rowParentId = row.dataset.parentId || null;
                    
                    if (rowParentId === parentId && !row.dataset.processed) {
                        row.dataset.level = currentLevel;
                        row.dataset.processed = 'true';
                        
                        const titleCell = row.cells[1];
                        const indent = currentLevel * 30;
                        const titleDiv = titleCell.querySelector('div:first-child');
                        if (titleDiv) {
                            titleDiv.style.paddingLeft = indent + 'px';
                        }
                        
                        const levelBadge = titleDiv.querySelector('.badge.bg-light');
                        if (levelBadge) {
                            levelBadge.textContent = (lang === 'ru' ? 'Уровень' : 'Level') + ' ' + currentLevel;
                        }
                        
                        found = true;
                        updateRecursive(rowsList, currentLevel + 1, row.dataset.id);
                        break;
                    }
                }
            }
        };

        rows.forEach(row => {
            row.dataset.processed = 'false';
        });
        
        updateRecursive(rows, 0, null);
        
        rows.forEach(row => {
            delete row.dataset.processed;
        });
    }

    function initDeleteButtons() {
        document.querySelectorAll('.delete-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const itemId = btn.dataset.id;
                const itemTitle = btn.dataset.title;
                
                const confirmMsg = (lang === 'ru' ? 'Вы уверены, что хотите удалить пункт' : 'Are you sure you want to delete item') + ' "' + itemTitle + '"?"';
                
                if (confirm(confirmMsg)) {
                    deleteItem(itemId, btn);
                }
            });
        });
    }

    function deleteItem(itemId, button) {
        const row = button.closest('tr');
        
        fetch(ADMIN_URL + '/menu/item/delete/' + itemId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                removeItemWithChildren(row);
                showNotification(lang === 'ru' ? 'Пункт удален' : 'Item deleted', 'success');
                updateStatistics();
            } else {
                showNotification(data.message || (lang === 'ru' ? 'Ошибка при удалении' : 'Error deleting item'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(lang === 'ru' ? 'Ошибка сети' : 'Network error', 'error');
        });
    }

    function removeItemWithChildren(row) {
        const itemId = row.dataset.id;
        const allRows = document.querySelectorAll('#sortable-items tr');
        
        const toRemove = [row];
        
        allRows.forEach(r => {
            if (r.dataset.parentId === itemId) {
                toRemove.push(r);
                removeChildrenRecursive(r, allRows, toRemove);
            }
        });
        
        toRemove.forEach(r => r.remove());
        updateLevelIndicators();
    }

    function removeChildrenRecursive(parentRow, allRows, toRemove) {
        const parentId = parentRow.dataset.id;
        
        allRows.forEach(r => {
            if (r.dataset.parentId === parentId && !toRemove.includes(r)) {
                toRemove.push(r);
                removeChildrenRecursive(r, allRows, toRemove);
            }
        });
    }

    function initExpandCollapse() {
        const expandBtn = document.getElementById('expand-all');
        const collapseBtn = document.getElementById('collapse-all');
        
        if (expandBtn) {
            expandBtn.addEventListener('click', function() {
                const rows = document.querySelectorAll('#sortable-items tr');
                rows.forEach(row => {
                    row.style.backgroundColor = '#f8f9fa';
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                    }, 500);
                });
            });
        }
        
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function() {
                const rows = document.querySelectorAll('#sortable-items tr');
                rows.forEach(row => {
                    row.style.backgroundColor = '';
                });
            });
        }
    }

    function updateStatistics() {
        const totalItems = document.querySelectorAll('#sortable-items tr').length;
        const totalStatElement = document.querySelector('.card.bg-primary h4');
        if (totalStatElement) {
            totalStatElement.textContent = totalItems;
        }
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        setTimeout(() => {
            if (notification.parentNode) notification.remove();
        }, 3000);
    }
})();