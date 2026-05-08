if (typeof CategoriesManagement === 'undefined') {
    class CategoriesManagement {
        constructor() {
            this.adminUrl = window.ADMIN_URL || '/admin';
            this.searchInput = document.getElementById('categories-search');
            this.clearButton = document.querySelector('.clear-search');
            this.categoriesCount = document.getElementById('categories-count');
            this.noResults = document.getElementById('no-results');
            this.tableBody = document.getElementById('sortable-categories');
            this.allRows = document.querySelectorAll('#sortable-categories .sortable-item');
            this.originalCount = this.allRows.length;
            
            this.init();
        }

        init() {
            this.initTooltips();
            this.initSearch();
            this.initSortable();
        }

        initTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }

        initSearch() {
            if (this.searchInput && this.clearButton) {
                this.searchInput.addEventListener('input', this.performSearch.bind(this));
                
                this.clearButton.addEventListener('click', () => {
                    this.searchInput.value = '';
                    this.performSearch();
                    this.searchInput.focus();
                });

                this.searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.searchInput.value = '';
                        this.performSearch();
                    }
                });
            }
        }

        performSearch() {
            const searchTerm = this.searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            this.allRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (this.categoriesCount) {
                this.categoriesCount.textContent = `${visibleCount} из ${this.originalCount} категорий`;
            }

            if (this.noResults) {
                if (visibleCount === 0 && searchTerm !== '') {
                    this.noResults.style.display = 'block';
                    document.querySelector('.table-responsive').style.display = 'none';
                } else {
                    this.noResults.style.display = 'none';
                    document.querySelector('.table-responsive').style.display = 'block';
                }
            }

            if (this.clearButton) {
                this.clearButton.style.display = searchTerm !== '' ? 'block' : 'none';
            }
        }

        initSortable() {
            if (!this.tableBody) return;

            const sortable = new Sortable(this.tableBody, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                
                onEnd: (evt) => {
                    this.updateCategoriesOrder();
                }
            });
        }

        updateCategoriesOrder() {
            const categoryRows = document.querySelectorAll('#sortable-categories .sortable-item');
            const orderData = [];
            
            categoryRows.forEach((row, index) => {
                const categoryId = row.getAttribute('data-category-id');
                const orderNumber = index + 1;
                
                const badge = row.querySelector('.badge.bg-secondary');
                if (badge) {
                    badge.textContent = orderNumber;
                }
                
                orderData.push({
                    id: categoryId,
                    order: orderNumber
                });
            });
            
            this.sendOrderToServer(orderData);
        }

        async sendOrderToServer(orderData) {
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            
            if (submitBtn) {
                submitBtn.innerHTML = `<i class="bi bi-arrow-repeat spinner"></i> ${lang === 'ru' ? 'Сохранение...' : 'Saving...'}`;
                submitBtn.disabled = true;
            }
            
            try {
                const response = await fetch(`${this.adminUrl}/categories/reorder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order: orderData })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showNotification(lang === 'ru' ? 'Порядок категорий успешно обновлен' : 'Category order updated successfully', 'success');
                } else {
                    this.showNotification(`${lang === 'ru' ? 'Ошибка при обновлении порядка:' : 'Error updating order:'} ${data.message}`, 'error');
                    setTimeout(() => location.reload(), 2000);
                }
            } catch (error) {
                this.showNotification(lang === 'ru' ? 'Ошибка при обновлении порядка категорий' : 'Error updating category order', 'error');
                setTimeout(() => location.reload(), 2000);
            } finally {
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }
        }

        showNotification(message, type) {
            document.querySelectorAll('.custom-notification').forEach(notification => {
                notification.remove();
            });
            
            const alertClass = type === 'success' ? 'alert-success bg-success text-white' : 'alert-danger';
            const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
            
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show custom-notification position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
            notification.innerHTML = `
                <i class="bi ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        new CategoriesManagement();
    });
}