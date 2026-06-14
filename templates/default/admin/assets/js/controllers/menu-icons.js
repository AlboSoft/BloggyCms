class MenuIconManager {
    constructor() {
        this.selectedIcon = null;
        this.iconsCache = null;
        this.init();
    }

    init() {
        const clearBtn = document.getElementById('clear-icon-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => this.clearSelectedIcon());
        }

        const sizeInput = document.getElementById('item-icon-size');
        const colorInput = document.getElementById('item-icon-color');
        if (sizeInput) sizeInput.addEventListener('change', () => this.updateIconPreview());
        if (colorInput) colorInput.addEventListener('input', () => this.updateIconPreview());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isIconSelectorOpen()) {
                this.closeIconSelector();
            }
        });

        this.loadIcons();
    }

    isIconSelectorOpen() {
        const modal = document.getElementById('iconSelectorModal');
        return modal && modal.style.display !== 'none';
    }

    openIconSelector() {
        const modal = document.getElementById('iconSelectorModal');
        if (!modal) return;
        
        modal.style.display = 'flex';

        const contentContainer = document.getElementById('iconSelectorTabsContent');
        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Загрузка иконок...</p>
                </div>
            `;
        }
        const tabsContainer = document.getElementById('iconSelectorTabs');
        if (tabsContainer) tabsContainer.innerHTML = '';
        
        let overlay = document.getElementById('iconSelectorOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'iconSelectorOverlay';
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1040';
            overlay.onclick = () => this.closeIconSelector();
            document.body.appendChild(overlay);
        } else {
            overlay.style.display = 'block';
        }
        
        document.body.style.overflow = 'hidden';
        
        if (!this.iconsCache) {
            this.loadIcons().then(() => this.populateIconSelector());
        } else {
            this.populateIconSelector();
        }
        
        setTimeout(() => {
            const input = document.getElementById('iconSearchModal');
            if (input) input.focus();
            if (this.selectedIcon) this.highlightSelectedIcon();
        }, 100);
    }

    closeIconSelector() {
        const modal = document.getElementById('iconSelectorModal');
        const overlay = document.getElementById('iconSelectorOverlay');
        
        if (modal) modal.style.display = 'none';
        if (overlay) overlay.style.display = 'none';
        document.body.style.overflow = '';
        
        const searchInput = document.getElementById('iconSearchModal');
        if (searchInput) searchInput.value = '';
        
        const noResultsMsg = document.getElementById('noResultsMsg');
        if (noResultsMsg) noResultsMsg.remove();
    }

    populateIconSelector() {
        const tabsContainer = document.getElementById('iconSelectorTabs');
        const contentContainer = document.getElementById('iconSelectorTabsContent');
        if (!tabsContainer || !contentContainer) return;

        tabsContainer.innerHTML = '';
        contentContainer.innerHTML = '';

        let isFirst = true;

        for (const [template, sets] of Object.entries(this.iconsCache)) {
            for (const [setName, setData] of Object.entries(sets)) {
                if (!setData?.icons?.length) continue;

                const contentId = `icon-content-${template}-${setName}`;

                const tab = document.createElement('li');
                tab.className = 'nav-item';
                tab.role = 'presentation';
                
                const tabBtn = document.createElement('button');
                tabBtn.className = `nav-link ${isFirst ? 'active' : ''}`;
                tabBtn.setAttribute('data-bs-toggle', 'tab');
                tabBtn.setAttribute('data-bs-target', `#${contentId}`);
                tabBtn.type = 'button';
                tabBtn.role = 'tab';
                tabBtn.textContent = setName;
                tab.appendChild(tabBtn);
                tabsContainer.appendChild(tab);

                const pane = document.createElement('div');
                pane.className = `tab-pane fade ${isFirst ? 'show active' : ''}`;
                pane.id = contentId;
                pane.role = 'tabpanel';
                
                const grid = document.createElement('div');
                grid.className = 'row g-2';
                
                setData.icons.forEach(icon => {
                    const cleanId = icon.id.split('/').pop();
                    const card = document.createElement('div');
                    card.className = 'col-3 col-md-2 icon-selector-card';
                    card.setAttribute('data-set-name', setName);
                    card.setAttribute('data-clean-icon-id', cleanId);
                    card.setAttribute('data-icon-id', icon.id);
                    card.setAttribute('data-template', template);
                    card.style.cursor = 'pointer';
                    card.onclick = () => this.selectIconInModal(card);
                    card.innerHTML = `
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-2">
                                <div style="font-size:1.5rem;margin-bottom:4px">${icon.preview}</div>
                                <div class="small text-muted text-truncate">${cleanId}</div>
                            </div>
                        </div>
                    `;
                    grid.appendChild(card);
                });
                
                pane.appendChild(grid);
                contentContainer.appendChild(pane);
                isFirst = false;
            }
        }
        
        const searchInput = document.getElementById('iconSearchModal');
        if (searchInput) {
            const newHandler = (e) => this.filterIconsInModal(e.target.value);
            searchInput.removeEventListener('input', this._searchHandler);
            this._searchHandler = newHandler;
            searchInput.addEventListener('input', this._searchHandler);
        }
    }

    selectIconInModal(card) {
        document.querySelectorAll('.icon-selector-card').forEach(c => {
            c.classList.remove('selected');
            const cardBody = c.querySelector('.card');
            if (cardBody) cardBody.classList.remove('border-primary', 'shadow');
        });
        
        card.classList.add('selected');
        const cardBody = card.querySelector('.card');
        if (cardBody) cardBody.classList.add('border-primary', 'shadow');
        
        this.selectedIcon = {
            set: card.getAttribute('data-set-name'),
            id: card.getAttribute('data-clean-icon-id'),
            template: card.getAttribute('data-template') || 'default'
        };
    }

    highlightSelectedIcon() {
        if (!this.selectedIcon) return;
        const card = document.querySelector(`.icon-selector-card[data-set-name="${this.selectedIcon.set}"][data-clean-icon-id="${this.selectedIcon.id}"]`);
        if (card) this.selectIconInModal(card);
    }

    filterIconsInModal(query) {
        const searchTerm = query.toLowerCase().trim();
        let visible = 0;
        
        document.querySelectorAll('.icon-selector-card').forEach(card => {
            const id = (card.getAttribute('data-clean-icon-id') || '').toLowerCase();
            const set = (card.getAttribute('data-set-name') || '').toLowerCase();
            const matches = searchTerm === '' || id.includes(searchTerm) || set.includes(searchTerm);
            card.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });
        
        let msg = document.getElementById('noResultsMsg');
        if (visible === 0 && searchTerm !== '') {
            if (!msg) {
                msg = document.createElement('div');
                msg.id = 'noResultsMsg';
                msg.className = 'alert alert-warning text-center mt-3';
                msg.innerHTML = `<i class="bi bi-search me-2"></i>${window.lang === 'ru' ? 'Ничего не найдено' : 'No results found'}`;
                const activePane = document.querySelector('.tab-pane.active');
                if (activePane) activePane.appendChild(msg);
            }
        } else if (msg) {
            msg.remove();
        }
    }

    confirmIconSelection() {
        if (this.selectedIcon) {
            this.setSelectedIcon(this.selectedIcon);
            this.closeIconSelector();
        } else {
            alert(window.lang === 'ru' ? 'Выберите иконку' : 'Select an icon');
        }
    }

    setSelectedIcon(iconData) {
        const idInput = document.getElementById('item-icon-id');
        const setInput = document.getElementById('item-icon-set');
        if (idInput) idInput.value = iconData.id;
        if (setInput) setInput.value = iconData.set;
        
        this.selectedIcon = iconData;
        this.updateIconPreview();
        
        const previewContainer = document.getElementById('icon-preview');
        if (previewContainer) previewContainer.style.display = 'block';
    }

    updateIconPreview() {
        if (!this.selectedIcon) return;
        
        const size = document.getElementById('item-icon-size')?.value || 48;
        const color = document.getElementById('item-icon-color')?.value || '#000000';
        const preview = document.getElementById('selected-icon-preview');
        const nameSpan = document.getElementById('icon-name');
        
        if (preview) {
            preview.innerHTML = window.bloggyIcon(this.selectedIcon.set, this.selectedIcon.id, `${size} ${size}`, color);
        }
        if (nameSpan) nameSpan.textContent = `${this.selectedIcon.set}/${this.selectedIcon.id}`;
    }

    clearSelectedIcon() {
        this.selectedIcon = null;
        
        const idInput = document.getElementById('item-icon-id');
        const setInput = document.getElementById('item-icon-set');
        if (idInput) idInput.value = '';
        if (setInput) setInput.value = 'bs';
        
        const preview = document.getElementById('icon-preview');
        if (preview) preview.style.display = 'none';
        
        const previewInner = document.getElementById('selected-icon-preview');
        if (previewInner) previewInner.innerHTML = '';
        
        const nameSpan = document.getElementById('icon-name');
        if (nameSpan) nameSpan.textContent = '';
        
        const sizeInput = document.getElementById('item-icon-size');
        if (sizeInput) sizeInput.value = 20;
    }

    setIconData(iconData) {
        if (!iconData?.id) return;
        
        const cleanId = iconData.id.split('/').pop();
        this.setSelectedIcon({
            set: iconData.set || 'bs',
            id: cleanId,
            template: iconData.template || 'default'
        });
    }

    getIconData() {
        if (!this.selectedIcon) return null;
        return {
            set: this.selectedIcon.set,
            id: this.selectedIcon.id,
            size: parseInt(document.getElementById('item-icon-size')?.value || 20),
            color: document.getElementById('item-icon-color')?.value || null,
            icon_only: document.getElementById('item-icon-only')?.checked || false
        };
    }

    async loadIcons() {
        try {
            const res = await fetch(window.ADMIN_URL + '/icons/data');
            const data = await res.json();
            this.iconsCache = data.success ? data.data : data;
        } catch (err) {
            console.error('Failed to load icons:', err);
            this.iconsCache = {};
        }
    }
}

let menuIconManagerInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    menuIconManagerInstance = new MenuIconManager();
    
    window.menuIconManager = {
        openIconSelector: () => menuIconManagerInstance?.openIconSelector(),
        closeIconSelector: () => menuIconManagerInstance?.closeIconSelector(),
        confirmIconSelection: () => menuIconManagerInstance?.confirmIconSelection(),
        setIconData: (data) => menuIconManagerInstance?.setIconData(data),
        getIconData: () => menuIconManagerInstance?.getIconData()
    };
});

window.bloggyIcon = function(set, icon, size, color) {
    const sizeAttr = size ? `width="${size.split(' ')[0]}" height="${size.split(' ')[1] || size.split(' ')[0]}"` : '';
    const colorAttr = color ? `style="fill: ${color}"` : '';
    return `<svg ${sizeAttr} ${colorAttr} class="icon icon-${icon}"><use href="${window.BASE_URL}/templates/default/admin/icons/${set}.svg#${icon}"></use></svg>`;
};