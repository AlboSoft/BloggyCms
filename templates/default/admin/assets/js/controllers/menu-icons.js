class MenuIconManager {
    constructor() {
        this.selectedIcon = null;
        this.iconsCache = null;
        this.searchInputHandler = null;
        this.init();
    }

    init() {
        this.setupStaticEventListeners();
        this.loadIcons();
    }

    setupStaticEventListeners() {
        const clearIconBtn = document.getElementById('clear-icon-btn');
        if (clearIconBtn) {
            clearIconBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.clearSelectedIcon();
            });
        }

        const sizeInput = document.getElementById('item-icon-size');
        if (sizeInput) {
            sizeInput.addEventListener('change', () => {
                this.updateIconPreview();
            });
        }

        const colorInput = document.getElementById('item-icon-color');
        if (colorInput) {
            colorInput.addEventListener('input', () => {
                this.updateIconPreview();
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isIconSelectorOpen()) {
                this.closeIconSelector();
            }
        });
    }

    isIconSelectorOpen() {
        const modal = document.getElementById('iconSelectorModal');
        return modal && modal.style.display !== 'none';
    }

    openIconSelector() {
        
        this.createOverlay();
        this.createModal();
        
        if (!this.iconsCache) {
            this.loadIconsForModal();
        } else {
            this.populateIconSelector();
        }
        
        this.focusSearchInputWithRetry();
        
        setTimeout(() => {
            if (this.selectedIcon) {
                this.highlightSelectedIcon();
            }
        }, 100);
    }

    createOverlay() {
        let overlay = document.getElementById('iconSelectorOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'iconSelectorOverlay';
            overlay.className = 'custom-modal-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                display: block;
            `;
            
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    this.closeIconSelector();
                }
            });
            
            document.body.appendChild(overlay);
        } else {
            overlay.style.display = 'block';
        }
        
        document.body.style.overflow = 'hidden';
    }

    createModal() {
        let modal = document.getElementById('iconSelectorModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'iconSelectorModal';
            modal.className = 'custom-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1050;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                display: flex !important;
            `;
            
            modal.innerHTML = `
                <div class="custom-modal-dialog" style="max-width: 900px; width: 100%; max-height: 90vh;">
                    <div class="custom-modal-content" style="background: white; border-radius: 8px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); display: flex; flex-direction: column; max-height: 90vh;">
                        <div class="custom-modal-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                            <h5 class="custom-modal-title" style="margin: 0; font-size: 1.25rem;">
                                <i class="bi bi-images me-2"></i>${lang === 'ru' ? 'Выбор иконки' : 'Icon Selection'}
                            </h5>
                            <button type="button" class="btn-close" onclick="window.menuIconManager.closeIconSelector()" aria-label="Close"></button>
                        </div>
                        <div class="custom-modal-body" style="padding: 1.5rem; overflow-y: auto; flex: 1;">
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                        id="iconSearchModalInput" 
                                        class="form-control border-0 bg-light" 
                                        placeholder="${lang === 'ru' ? 'Поиск иконок...' : 'Search icons...'}"
                                        autocomplete="off"
                                        autocorrect="off"
                                        autocapitalize="none"
                                        spellcheck="false"
                                        style="pointer-events: auto; cursor: text;">
                                </div>
                            </div>
                            
                            <div class="icon-selector-container">
                                <ul class="nav nav-tabs" id="iconSelectorTabs" role="tablist">
                                    <!-- Вкладки будут заполнены JavaScript -->
                                </ul>
                                
                                <div class="tab-content pt-3" id="iconSelectorTabsContent">
                                    <!-- Контент вкладок будет заполнен JavaScript -->
                                </div>
                            </div>
                        </div>
                        <div class="custom-modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem; flex-shrink: 0;">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.menuIconManager.closeIconSelector()">
                                <i class="bi bi-x-circle me-1"></i>${lang === 'ru' ? 'Отмена' : 'Cancel'}
                            </button>
                            <button type="button" class="btn btn-primary" onclick="window.menuIconManager.confirmIconSelection()">
                                <i class="bi bi-check-lg me-1"></i>${lang === 'ru' ? 'Выбрать' : 'Select'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeIconSelector();
                }
            });
            
            this.setupSearchInput();
            
        } else {
            modal.style.display = 'flex';
        }
    }

    setupSearchInput() {
        const searchInput = document.getElementById('iconSearchModalInput');
        if (searchInput) {
            if (this.searchInputHandler) {
                searchInput.removeEventListener('input', this.searchInputHandler);
            }
            
            this.searchInputHandler = (e) => {
                this.filterIconsInModal(e.target.value);
            };
            
            searchInput.addEventListener('input', this.searchInputHandler);
            
            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
            });
            
            searchInput.addEventListener('mousedown', (e) => {
                e.stopPropagation();
            });
            
            searchInput.addEventListener('keydown', (e) => {
                e.stopPropagation();
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        }
    }

    focusSearchInputWithRetry() {
        const searchInput = document.getElementById('iconSearchModalInput');
        if (!searchInput) return;

        const tryFocus = (attempt = 0) => {
            if (attempt > 10) {
                return;
            }
            
            searchInput.disabled = false;
            searchInput.readOnly = false;
            searchInput.style.pointerEvents = 'auto';
            searchInput.style.cursor = 'text';
            searchInput.focus({ preventScroll: true });
            
            requestAnimationFrame(() => {
                if (document.activeElement === searchInput) {
                    searchInput.select();
                } else {
                    setTimeout(() => tryFocus(attempt + 1), 50);
                }
            });
        };
        
        setTimeout(() => tryFocus(), 50);
    }

    setupIconClickHandlers() {
        document.querySelectorAll('.icon-selector-card').forEach(card => {
            card.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectIconInModal(card);
            }, { once: true });
        });
    }

    closeIconSelector() {
        
        const overlay = document.getElementById('iconSelectorOverlay');
        const modal = document.getElementById('iconSelectorModal');
        
        if (overlay) {
            overlay.style.display = 'none';
        }
        
        if (modal) {
            modal.style.display = 'none';
        }
        
        document.body.style.overflow = '';

        const searchInput = document.getElementById('iconSearchModalInput');
        if (searchInput) {
            searchInput.value = '';
            this.filterIconsInModal('');
        }
    }

    selectIconInModal(iconCard) {
        document.querySelectorAll('.icon-selector-card').forEach(card => {
            card.classList.remove('selected');
        });

        iconCard.classList.add('selected');

        const setParts = iconCard.dataset.set.split('/');
        const template = setParts[0];
        const iconSet = setParts[1];
        
        this.selectedIcon = {
            template: template,
            set: iconSet,
            fullSet: iconCard.dataset.set,
            id: iconCard.dataset.iconId
        };
    }

    highlightSelectedIcon() {
        if (!this.selectedIcon) return;

        const iconCard = document.querySelector(
            `.icon-selector-card[data-set="${this.selectedIcon.fullSet}"][data-icon-id="${this.selectedIcon.id}"]`
        );

        if (iconCard) {
            document.querySelectorAll('.icon-selector-card').forEach(card => {
                card.classList.remove('selected');
            });
            iconCard.classList.add('selected');
        }
    }

    confirmIconSelection() {
        
        if (this.selectedIcon) {
            this.setSelectedIcon(this.selectedIcon);
            this.closeIconSelector();
        } else {
            alert(lang === 'ru' ? 'Пожалуйста, выберите иконку' : 'Please select an icon');
        }
    }

    setSelectedIcon(iconData) {
        this.selectedIcon = iconData;
        
        const iconIdInput = document.getElementById('item-icon-id');
        if (iconIdInput) {
            iconIdInput.value = `${iconData.template}/${iconData.set}/${iconData.id}`;
        }
        
        this.updateIconPreview();
        
        const previewContainer = document.getElementById('icon-preview');
        if (previewContainer) {
            previewContainer.style.display = 'block';
        }
    }

    updateIconPreview() {
        if (!this.selectedIcon) return;

        const sizeInput = document.getElementById('item-icon-size');
        const colorInput = document.getElementById('item-icon-color');
        const iconPreview = document.getElementById('selected-icon-preview');
        const iconName = document.getElementById('icon-name');
        
        if (!sizeInput || !colorInput || !iconPreview || !iconName) return;

        const size = sizeInput.value || 48;
        const color = colorInput.value || '#000000';
        
        if (this.iconsCache && 
            this.iconsCache[this.selectedIcon.template] && 
            this.iconsCache[this.selectedIcon.template][this.selectedIcon.set]) {
            
            const setData = this.iconsCache[this.selectedIcon.template][this.selectedIcon.set];
            const icon = setData.icons.find(i => i.id === this.selectedIcon.id);
            
            if (icon) {
                let updatedPreview = icon.preview;
                updatedPreview = updatedPreview.replace(/width="[^"]*"/, `width="${size}"`);
                updatedPreview = updatedPreview.replace(/height="[^"]*"/, `height="${size}"`);
                
                if (updatedPreview.includes('style="')) {
                    updatedPreview = updatedPreview.replace(/style="[^"]*"/, `style="fill: ${color}"`);
                } else {
                    updatedPreview = updatedPreview.replace('<svg', `<svg style="fill: ${color}"`);
                }
                
                iconPreview.innerHTML = updatedPreview;
                iconName.textContent = `${this.selectedIcon.set}/${this.selectedIcon.id}`;
            }
        }
    }

    clearSelectedIcon() {
        
        this.selectedIcon = null;
        
        const iconIdInput = document.getElementById('item-icon-id');
        if (iconIdInput) {
            iconIdInput.value = '';
        }
        
        const previewContainer = document.getElementById('icon-preview');
        if (previewContainer) {
            previewContainer.style.display = 'none';
        }
        
        const sizeInput = document.getElementById('item-icon-size');
        if (sizeInput) {
            sizeInput.value = '';
        }
        
        const colorInput = document.getElementById('item-icon-color');
        if (colorInput) {
            colorInput.value = '#000000';
        }
        
        const iconOnlyCheckbox = document.getElementById('item-icon-only');
        if (iconOnlyCheckbox) {
            iconOnlyCheckbox.checked = false;
        }
        
        const iconPreview = document.getElementById('selected-icon-preview');
        if (iconPreview) {
            iconPreview.innerHTML = '';
        }
        
        const iconName = document.getElementById('icon-name');
        if (iconName) {
            iconName.textContent = '';
        }
    }

    setIconData(iconData) {
        this.clearSelectedIcon();
        
        if (!iconData || !iconData.id) {
            return;
        }

        let template = iconData.template || 'default';
        let iconSet = iconData.set || 'bs';
        
        if (iconData.fullSet) {
            const parts = iconData.fullSet.split('/');
            template = parts[0];
            iconSet = parts[1];
        }

        this.selectedIcon = {
            template: template,
            set: iconSet,
            fullSet: `${template}/${iconSet}`,
            id: iconData.id
        };

        const iconIdInput = document.getElementById('item-icon-id');
        if (iconIdInput) {
            iconIdInput.value = `${template}/${iconSet}/${iconData.id}`;
        }
        
        const sizeInput = document.getElementById('item-icon-size');
        if (sizeInput && iconData.size) {
            sizeInput.value = iconData.size;
        }
        
        const colorInput = document.getElementById('item-icon-color');
        if (colorInput && iconData.color) {
            colorInput.value = iconData.color;
        }
        
        const iconOnlyCheckbox = document.getElementById('item-icon-only');
        if (iconOnlyCheckbox && iconData.icon_only !== undefined) {
            iconOnlyCheckbox.checked = Boolean(iconData.icon_only);
        }
        
        this.updateIconPreview();
        
        const previewContainer = document.getElementById('icon-preview');
        if (previewContainer) {
            previewContainer.style.display = 'block';
        }
    }

    getIconData() {
        if (!this.selectedIcon || !this.selectedIcon.id) {
            return null;
        }

        const sizeInput = document.getElementById('item-icon-size');
        const colorInput = document.getElementById('item-icon-color');
        const iconOnlyCheckbox = document.getElementById('item-icon-only');
        
        const size = sizeInput?.value;
        const color = colorInput?.value;
        const iconOnly = iconOnlyCheckbox?.checked;

        const iconData = {
            template: this.selectedIcon.template || 'default',
            set: this.selectedIcon.set || 'bs',
            id: this.selectedIcon.id,
            size: size ? parseInt(size) : null,
            color: color && color !== '#000000' ? color : null,
            icon_only: iconOnly || false
        };
        
        return iconData;
    }

    async loadIcons() {
        try {
            const iconsUrl = window.location.origin + '/admin/icons/data';
            const response = await fetch(iconsUrl);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            
            try {
                const responseData = JSON.parse(text);
                
                if (responseData.success && responseData.data) {
                    this.iconsCache = responseData.data;
                } else if (responseData.data) {
                    this.iconsCache = responseData.data;
                } else {
                    this.iconsCache = responseData;
                }
                
                if (this.iconsCache && typeof this.iconsCache === 'object') {
                    const sets = Object.keys(this.iconsCache);
                    
                    sets.forEach(setName => {
                        const setData = this.iconsCache[setName];
                    });
                }
                
            } catch (parseError) {
                throw parseError;
            }
            
        } catch (error) {
            this.iconsCache = this.createTestIconsData();
        }
    }

    async loadIconsForModal() {
        if (!this.iconsCache) {
            await this.loadIcons();
        }
        this.populateIconSelector();
    }

    populateIconSelector() {
        if (!this.iconsCache) return;

        const tabsContainer = document.getElementById('iconSelectorTabs');
        const contentContainer = document.getElementById('iconSelectorTabsContent');

        if (!tabsContainer || !contentContainer) {
            return;
        }

        tabsContainer.innerHTML = '';
        contentContainer.innerHTML = '';

        let isFirst = true;
        
        for (const [templateName, templateData] of Object.entries(this.iconsCache)) {
            
            for (const [setName, setData] of Object.entries(templateData)) {
                
                if (!setData || !setData.icons || !Array.isArray(setData.icons)) {
                    continue;
                }

                const tabLabel = this.capitalizeFirstLetter(setName);
                const tabId = `${templateName}-${setName}-selector-tab`;
                const contentId = `${templateName}-${setName}-selector-content`;

                const tabButton = document.createElement('button');
                tabButton.className = `nav-link ${isFirst ? 'active' : ''}`;
                tabButton.id = tabId;
                tabButton.type = 'button';
                tabButton.role = 'tab';
                tabButton.textContent = tabLabel;
                
                tabButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    contentContainer.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });
                    
                    tabsContainer.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                    });
                    
                    const target = document.getElementById(contentId);
                    if (target) {
                        target.classList.add('show', 'active');
                        tabButton.classList.add('active');
                    }
                });

                const tabLi = document.createElement('li');
                tabLi.className = 'nav-item';
                tabLi.role = 'presentation';
                tabLi.appendChild(tabButton);
                tabsContainer.appendChild(tabLi);
                
                const tabContent = document.createElement('div');
                tabContent.className = `tab-pane fade ${isFirst ? 'show active' : ''}`;
                tabContent.id = contentId;
                tabContent.role = 'tabpanel';
                const iconsGrid = document.createElement('div');
                iconsGrid.className = 'row g-2';

                setData.icons.forEach(icon => {
                    const iconCard = document.createElement('div');
                    iconCard.className = 'col-3 col-md-2 icon-selector-card';
                    iconCard.dataset.set = `${templateName}/${setName}`;
                    iconCard.dataset.iconId = icon.id;
                    iconCard.title = icon.id;
                    iconCard.style.cursor = 'pointer';
                    iconCard.innerHTML = `
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-2">
                                <div class="mb-1" style="font-size: 1.5rem;">
                                    ${icon.preview}
                                </div>
                                <div class="small text-muted text-truncate">
                                    ${icon.id}
                                </div>
                            </div>
                        </div>
                    `;
                    iconsGrid.appendChild(iconCard);
                });

                tabContent.appendChild(iconsGrid);
                contentContainer.appendChild(tabContent);

                isFirst = false;
            }
        }
        
        if (tabsContainer.children.length === 0) {
            contentContainer.innerHTML = `
                <div class="alert alert-info text-center p-4">
                    <div class="mb-2">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <p class="mb-0">${lang === 'ru' ? 'Иконки не найдены' : 'Icons not found'}</p>
                </div>
            `;
        }
        
        setTimeout(() => this.setupIconClickHandlers(), 0);
    }

    filterIconsInModal(query) {
        query = query.toLowerCase();
        document.querySelectorAll('.icon-selector-card').forEach(card => {
            const iconId = card.dataset.iconId.toLowerCase();
            const iconSet = card.dataset.set.toLowerCase();
            const searchText = `${iconSet}/${iconId}`;
            
            card.style.display = searchText.includes(query) ? '' : 'none';
        });
    }

    createTestIconsData() {
        return {
            'bs': {
                name: 'bs',
                icons: [
                    { id: 'house', preview: '<svg width="24" height="24"><use href="/templates/default/admin/icons/bs.svg#house"/></svg>' },
                    { id: 'gear', preview: '<svg width="24" height="24"><use href="/templates/default/admin/icons/bs.svg#gear"/></svg>' },
                    { id: 'person', preview: '<svg width="24" height="24"><use href="/templates/default/admin/icons/bs.svg#person"/></svg>' },
                    { id: 'envelope', preview: '<svg width="24" height="24"><use href="/templates/default/admin/icons/bs.svg#envelope"/></svg>' }
                ]
            }
        };
    }

    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
}