(function() {
    if (typeof bloggy_icon === 'undefined') {
        window.bloggy_icon = function(set, icon, size, color, className) {
            
            const [width, height] = size ? size.split(' ') : ['48', '48'];
            const baseUrl = window.BASE_URL || '';
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('width', width);
            svg.setAttribute('height', height);
            svg.setAttribute('style', `fill: ${color || 'currentColor'}`);
            svg.setAttribute('class', className || '');
            
            const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
            use.setAttribute('href', `${baseUrl}/templates/default/admin/icons/${set}.svg#${icon}`);
            
            svg.appendChild(use);
            
            const serializer = new XMLSerializer();
            return serializer.serializeToString(svg);
        };
    }
})();
class IconField {
    constructor(fieldWrapper) {
        this.fieldWrapper = fieldWrapper;
        this.fieldName = fieldWrapper.dataset.fieldName;
        this.iconsPageUrl = fieldWrapper.dataset.iconsPageUrl;
        this.modalId = fieldWrapper.dataset.modalId;
        
        this.hiddenInput = fieldWrapper.querySelector('.icon-hidden-input');
        this.previewContainer = fieldWrapper.querySelector('.icon-preview-container');
        this.selectBtn = fieldWrapper.querySelector('.icon-select-btn');
        this.clearBtn = fieldWrapper.querySelector('.icon-clear-btn');
        this.modalElement = document.getElementById(this.modalId);
        this.modalContent = fieldWrapper.querySelector('.icon-modal-content');
        this.confirmBtn = fieldWrapper.querySelector('.icon-select-confirm-btn');
        this.searchInput = fieldWrapper.querySelector('.icon-search-input');
        
        this.selectedIconData = null;
        
        this.init();
    }
    
    init() {

        if (this.selectBtn) {
            this.selectBtn.addEventListener('click', () => this.openIconPicker());
        }
    
        if (this.clearBtn) {
            this.clearBtn.addEventListener('click', () => this.clearIcon());
        }
        
        if (this.confirmBtn) {
            this.confirmBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.confirmSelection();
                
                if (this.modalElement) {
                    const modalInstance = bootstrap.Modal.getInstance(this.modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    } else {
                        this.modalElement.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        const modalBackdrop = document.querySelector('.modal-backdrop');
                        if (modalBackdrop) {
                            modalBackdrop.remove();
                        }
                    }
                }
            });
        }
        
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => this.filterIcons(e.target.value));
        }
        
        if (this.hiddenInput.value) {
            this.updatePreview(this.hiddenInput.value);
        }
        
        if (this.modalElement) {
            this.modalElement.addEventListener('show.bs.modal', () => {
                if (!this.modalContent.classList.contains('loaded')) {
                    this.loadIconsPage();
                }
            });
            
            this.modalElement.addEventListener('hidden.bs.modal', () => {
                this.selectedIconData = null;
                if (this.confirmBtn) {
                    this.confirmBtn.disabled = true;
                }
                const selectedItems = this.modalContent.querySelectorAll('.icon-item.selected');
                selectedItems.forEach(item => item.classList.remove('selected'));
            });
        }
    }
    
    openIconPicker() {}
    
    async loadIconsPage() {
        try {
            const response = await fetch(this.iconsPageUrl);
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const tabsContainer = doc.querySelector('#iconTabs');
            const tabsContent = doc.querySelector('#iconTabsContent');
            
            if (tabsContainer && tabsContent) {
                const container = document.createElement('div');
                container.appendChild(tabsContainer);
                container.appendChild(tabsContent);
                const tabButtons = container.querySelectorAll('#iconTabs button[data-bs-toggle="tab"]');
                tabButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        const targetId = button.getAttribute('data-bs-target');
                        const tabPane = container.querySelector(targetId);
                        if (tabPane) {
                            container.querySelectorAll('.tab-pane').forEach(pane => {
                                pane.classList.remove('show', 'active');
                            });
                            container.querySelectorAll('#iconTabs .nav-link').forEach(link => {
                                link.classList.remove('active');
                            });
                            
                            tabPane.classList.add('show', 'active');
                            button.classList.add('active');
                            localStorage.setItem('activeIconTab', button.id);
                        }
                    });
                });
                
                const activeTabId = localStorage.getItem('activeIconTab');
                if (activeTabId) {
                    const cleanTabId = activeTabId.replace(/^#/, '');
                    const activeTab = container.querySelector(`#${cleanTabId}`);
                    if (activeTab) {
                        const targetId = activeTab.getAttribute('data-bs-target');
                        const tabPane = container.querySelector(targetId);
                        if (tabPane) {
                            activeTab.click();
                        }
                    }
                }
                
                const iconItems = container.querySelectorAll('.icon-item');
                iconItems.forEach(item => {
                    item.addEventListener('click', () => this.selectIconItem(item));
                });
                
                this.modalContent.innerHTML = '';
                this.modalContent.appendChild(container);
                this.modalContent.classList.add('loaded');
                
                this.setupSearch();
                
            } else {
                this.modalContent.innerHTML = `<div class="alert alert-danger m-3">${lang === 'ru' ? 'Не удалось загрузить иконки' : 'Failed to load icons'}</div>`;
            }
        } catch (error) {
            this.modalContent.innerHTML = `<div class="alert alert-danger m-3">${lang === 'ru' ? 'Ошибка загрузки иконок' : 'Failed to load icons'}</div>`;
        }
    }
    
    setupSearch() {
        const searchInput = this.modalContent.querySelector('#iconSearch');
        if (searchInput) {
            searchInput.style.display = 'none';
        }
        if (this.searchInput) {
            this.searchInput.value = '';
            this.searchInput.addEventListener('input', (e) => {
                this.filterIcons(e.target.value);
            });
        }
    }
    
    filterIcons(query) {
        query = query.toLowerCase();
        const iconItems = this.modalContent.querySelectorAll('.icon-item');
        
        iconItems.forEach(item => {
            const iconId = item.getAttribute('data-icon-id').toLowerCase();
            if (iconId.includes(query)) {
                item.style.display = '';
                const tabPane = item.closest('.tab-pane');
                if (tabPane && !tabPane.classList.contains('show')) {
                    const tabId = tabPane.id.replace('-content', '-tab');
                    const tabButton = this.modalContent.querySelector(`#${tabId}`);
                    if (tabButton) {
                        tabButton.click();
                    }
                }
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    selectIconItem(item) {
        const selectedItems = this.modalContent.querySelectorAll('.icon-item.selected');
        selectedItems.forEach(selectedItem => selectedItem.classList.remove('selected'));
        item.classList.add('selected');
        
        const iconId = item.getAttribute('data-icon-id');
        
        const iconPreview = item.querySelector('.mb-2').innerHTML;
        
        let fullIconId = iconId;
        if (!iconId.includes(':')) {
            const activeTab = this.modalContent.querySelector('#iconTabs .nav-link.active');
            if (activeTab) {
                const tabId = activeTab.id;
                const set = tabId.replace('-tab', '');
                fullIconId = `${set}:${iconId}`;
            } else {
                fullIconId = `bs:${iconId}`;
            }
        }
        
        this.selectedIconData = {
            id: fullIconId,
            preview: iconPreview
        };
        
        if (this.confirmBtn) {
            this.confirmBtn.disabled = false;
            this.confirmBtn.classList.remove('btn-secondary');
            this.confirmBtn.classList.add('btn-primary');
        }
    }
    
    confirmSelection() {
        
        if (this.selectedIconData) {
            const iconValue = this.selectedIconData.id;
            this.hiddenInput.value = iconValue;
            this.updatePreview(iconValue);
            if (this.clearBtn) {
                this.clearBtn.style.display = 'inline-block';
            }
            this.selectedIconData = null;
            if (this.confirmBtn) {
                this.confirmBtn.disabled = true;
                this.confirmBtn.classList.remove('btn-primary');
                this.confirmBtn.classList.add('btn-secondary');
            }
        }
    }
    
    clearIcon() {
        this.hiddenInput.value = '';
        this.updatePreview('');
        if (this.clearBtn) {
            this.clearBtn.style.display = 'none';
        }
    }
    
    updatePreview(iconValue) {

        if (!iconValue) {
            this.previewContainer.innerHTML = `
                <div class="empty-icon-placeholder text-muted text-center py-3">
                    <i class="bi bi-question-circle fs-3"></i>
                    <div class="mt-1">
                        <small>${lang === 'ru' ? 'Иконка не выбрана' : 'Icon not selected'}</small>
                    </div>
                </div>
            `;
            return;
        }
        
        const parts = iconValue.split(':');
        let set, name;
        
        if (parts.length === 2) {
            [set, name] = parts;
        } else {
            name = iconValue;
            set = 'bs';
            iconValue = `${set}:${name}`;
            this.hiddenInput.value = iconValue;
        }
        
        const iconHtml = this.getIconPreviewHtml(set, name);
        
        this.previewContainer.innerHTML = `
            <div class="current-icon-preview">
                <div class="icon-preview-large" style="font-size: 2rem;">
                    ${iconHtml}
                </div>
                <div class="mt-1">
                    <small class="text-muted icon-code">${set}:${name}</small>
                </div>
            </div>
        `;
    }

    getIconPreviewHtml(set, name) {

        if (this.modalContent && this.modalContent.classList.contains('loaded')) {
            const iconElement = this.modalContent.querySelector(`[data-icon-id="${set}:${name}"], 
                                                            [data-icon-id="${name}"]`);
            if (iconElement) {
                const previewDiv = iconElement.querySelector('.mb-2');
                if (previewDiv) {
                    return previewDiv.innerHTML;
                }
            }
        }

        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', '48');
        svg.setAttribute('height', '48');
        svg.setAttribute('style', 'fill: currentColor');
        svg.setAttribute('class', 'icon-preview');
        
        const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
        const baseUrl = window.BASE_URL || '';

        const paths = [
            `${baseUrl}/templates/default/admin/icons/${set}.svg#${name}`,
            `${baseUrl}/admin/icons/${set}.svg#${name}`,
            `${baseUrl}/icons/${set}.svg#${name}`
        ];

        use.setAttribute('href', paths[0]);
        svg.appendChild(use);
        const serializer = new XMLSerializer();
        return serializer.serializeToString(svg);
    }
    
    generateIconPreview(set, name) {
 
        if (typeof bloggy_icon === 'function') {
            try {
                return bloggy_icon(set, name, '48 48', 'currentColor', '');
            } catch (error) {
                console.error('Error in bloggy_icon:', error);
            }
        }
        
        if (this.modalContent) {
            const iconElement = this.modalContent.querySelector(`[data-icon-id="${set}:${name}"]`);
            if (iconElement) {
                const preview = iconElement.querySelector('.mb-2');
                if (preview) {
                    return preview.innerHTML;
                }
            }
        }
        
        const baseUrl = window.BASE_URL || '';
        return `
            <svg width="48" height="48" style="fill: currentColor" class="icon-preview">
                <use href="${baseUrl}/templates/default/admin/icons/${set}.svg#${name}"/>
            </svg>
        `;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const iconFields = document.querySelectorAll('.icon-field-wrapper');
    
    iconFields.forEach(fieldWrapper => {
        try {
            new IconField(fieldWrapper);
        } catch (error) {
            console.error('Error initializing icon field:', error, fieldWrapper);
        }
    });
});