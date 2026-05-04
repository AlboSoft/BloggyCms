class MenuBuilder {
    constructor() {
        this.itemCounter = 0;
        this.currentEditIndex = null;
        this.currentParentIndex = null;
        this.isEditing = false;
        this.customEditor = null;
        this.templateSelectClone = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadInitialData();
        this.initializeSortable();
        this.updateStatistics();
        this.setupShortcodeHints();
        this.addAdditionalShortcodeButtons();
        
        const useCustomCheckbox = document.getElementById('use_custom_template');
        if (useCustomCheckbox && useCustomCheckbox.checked) {
            this.toggleCustomTemplate(true);
            setTimeout(() => this.initCustomTemplateEditor(), 200);
        }
    }

    setupEventListeners() {
        document.getElementById('add-menu-item')?.addEventListener('click', () => {
            this.openModalForNewItem();
        });

        document.getElementById('add-first-item')?.addEventListener('click', () => {
            this.openModalForNewItem();
        });

        document.getElementById('save-menu-item')?.addEventListener('click', () => {
            this.saveMenuItemFromModal();
        });

        document.querySelectorAll('.quick-url').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.getElementById('item-url').value = e.target.dataset.url;
                bootstrap.Modal.getInstance(document.getElementById('quickUrlModal')).hide();
            });
        });

        document.getElementById('menu-items-container')?.addEventListener('click', (e) => {
            this.handleContainerClick(e);
        });

        document.getElementById('expand-all')?.addEventListener('click', () => {
            this.expandAll();
        });

        document.getElementById('collapse-all')?.addEventListener('click', () => {
            this.collapseAll();
        });

        document.getElementById('menu-form')?.addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });

        const customTemplateCheckbox = document.getElementById('use_custom_template');
        if (customTemplateCheckbox) {
            customTemplateCheckbox.addEventListener('change', (e) => {
                this.toggleCustomTemplate(e.target.checked);
            });
        }

        const menuItemModal = document.getElementById('menuItemModal');
        if (menuItemModal) {
            menuItemModal.addEventListener('show.bs.modal', () => {});
            menuItemModal.addEventListener('hidden.bs.modal', () => {
                this.isEditing = false;
            });
        }
    }

    toggleCustomTemplate(useCustom) {
        const container = document.getElementById('custom-template-container');
        const templateSelectContainer = document.getElementById('template-select-container');
        const templateSelect = document.querySelector('select[name="template"]');
        
        if (container) {
            container.style.display = useCustom ? 'block' : 'none';
        }
        
        if (useCustom) {
            if (templateSelectContainer) {
                templateSelectContainer.style.display = 'none';
            }
            
            if (templateSelect) {
                templateSelect.removeAttribute('required');
                templateSelect.value = '';
            }
            
            if (!this.customEditor) {
                setTimeout(() => this.initCustomTemplateEditor(), 100);
            }
        } else {
            if (templateSelectContainer) {
                templateSelectContainer.style.display = 'block';
            }
            
            if (templateSelect) {
                templateSelect.setAttribute('required', 'required');
                if (!templateSelect.value) {
                    const firstOption = templateSelect.querySelector('option[value!=""]');
                    if (firstOption) {
                        templateSelect.value = firstOption.value;
                    }
                }
            }
        }
    }

    initCustomTemplateEditor() {
        const editorElement = document.getElementById('custom-template-editor');
        if (!editorElement) return;
        
        if (typeof ace === 'undefined') {
            setTimeout(() => this.initCustomTemplateEditor(), 200);
            return;
        }
        
        try {
            if (this.customEditor) {
                this.customEditor.destroy();
            }
            
            this.customEditor = ace.edit("custom-template-editor", {
                theme: "ace/theme/monokai",
                mode: "ace/mode/html",
                showPrintMargin: false,
                fontSize: "14px",
                tabSize: 2,
                useSoftTabs: true,
                wrap: true,
                minLines: 15,
                maxLines: 30
            });
            
            this.customEditor.session.setUseWrapMode(true);
            
            const textarea = document.getElementById('custom_template');
            let content = '';
            
            if (textarea && textarea.value) {
                content = textarea.value;
            } else {
                content = `<ul class="custom-menu">
    {li}
        <li class="{active_class}">
            <a href="{url}" target="{target}" class="{class}">
                {icon}<span>{title}</span>
            </a>
        </li>
    {/li}
    
    {li=sub}
        <li class="dropdown {active_class}">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                {icon}{title}
            </a>
            <ul class="dropdown-menu">
                {children}
            </ul>
        </li>
    {/li=sub}
    
    {li-extra}
        <li class="extra-item">
            <a href="{url}" class="btn btn-primary">
                {icon}{title}
            </a>
        </li>
    {/li-extra}
</ul>`;
            }
            
            this.customEditor.setValue(content);
            this.customEditor.clearSelection();
            
            const form = document.getElementById('menu-form');
            if (form && !this._submitHandlerAttached) {
                form.addEventListener('submit', () => {
                    if (this.customEditor) {
                        const ta = document.getElementById('custom_template');
                        if (ta) {
                            ta.value = this.customEditor.getValue();
                        }
                    }
                });
                this._submitHandlerAttached = true;
            }
            
        } catch (error) {}
    }

    loadInitialData() {
        const structureField = document.getElementById('menu-structure');
        if (!structureField || !structureField.value) {
            this.showEmptyMessage();
            return;
        }

        try {
            const structure = JSON.parse(structureField.value);
            if (structure && structure.length > 0) {
                this.renderMenuStructure(structure);
            } else {
                this.showEmptyMessage();
            }
        } catch (error) {
            console.error('Error parsing menu structure:', error);
            this.showEmptyMessage();
        }
    }

    renderMenuStructure(structure) {
        const container = document.getElementById('menu-items-container');
        container.innerHTML = '';
        this.itemCounter = 0;
        this.findMaxIndexInStructure(structure);

        structure.forEach(item => {
            this.createMenuItemElement(container, item, 0);
        });

        this.hideEmptyMessage();
        this.initializeSortable();
        this.updateStatistics();
    }

    findMaxIndexInStructure(structure) {
        structure.forEach(item => {
            this.itemCounter++;
            if (item.children && item.children.length > 0) {
                this.findMaxIndexInStructure(item.children);
            }
        });
    }

    createMenuItemElement(container, itemData, level = 0) {
        const index = this.itemCounter++;
        const hasChildren = itemData.children && itemData.children.length > 0;
        const levelClass = 'level-' + Math.min(level, 4);
        const isExtra = itemData.is_extra === true;
        const extraBadge = isExtra ? '<span class="badge bg-warning ms-2">extra</span>' : '';

        const itemJson = JSON.stringify({
            title: itemData.title || '',
            url: itemData.url || '',
            class: itemData.class || '',
            target: itemData.target || '_self',
            icon: itemData.icon || null,
            visibility: itemData.visibility || null,
            icon_only: itemData.icon_only || false,
            is_extra: isExtra || false
        });

        let iconHtml = '';
        if (itemData.icon) {
            const iconStyle = this.getIconStyle(itemData.icon);
            iconHtml = `
                <div class="menu-icon-preview me-2" style="${iconStyle}">
                    <i class="bi bi-check-circle text-success"></i>
                </div>
            `;
        }

        const itemHTML = `
            <div class="menu-item-card card mb-2 ${levelClass}" 
                data-index="${index}" 
                data-level="${level}"
                data-item='${itemJson}'>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center flex-grow-1">
                            <div class="menu-level-indicator me-3">
                                ${'<span class="level-line"></span>'.repeat(level)}
                                <span class="level-dot"></span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    ${iconHtml}
                                    ${hasChildren ? 
                                        '<i class="bi bi-folder-fill text-warning me-2"></i>' : 
                                        '<i class="bi bi-link-45deg text-primary me-2"></i>'
                                    }
                                    <div>
                                        <h6 class="mb-1">${this.escapeHtml(itemData.title || 'Без названия')} 
                                            ${itemData.icon_only ? '<span class="badge bg-info ms-2">только иконка</span>' : ''}
                                            ${extraBadge}
                                        </h6>
                                        <small class="text-muted">${this.escapeHtml(itemData.url || '')}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary menu-item-handle" title="Перетащить">
                                <i class="bi bi-arrows-move"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary edit-menu-item" 
                                    title="Редактировать"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#menuItemModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success add-child-item" 
                                    title="Добавить подпункт"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#menuItemModal"
                                    data-parent-index="${index}">
                                <i class="bi bi-patch-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger remove-menu-item" title="Удалить">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="menu-children-container mt-3" style="${hasChildren ? '' : 'display: none;'}">
                        <div class="border-top pt-3">
                            <div class="menu-children sortable-menu"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', itemHTML);
        if (hasChildren) {
            const childrenContainer = container.querySelector(`[data-index="${index}"] .menu-children`);
            itemData.children.forEach(child => {
                this.createMenuItemElement(childrenContainer, child, level + 1);
            });
        }
    }

    getIconStyle(iconData) {
        let style = '';
        if (iconData.color) {
            style += `color: ${iconData.color}; `;
        }
        if (iconData.size) {
            style += `font-size: ${iconData.size}px; `;
        }
        return style;
    }

    handleContainerClick(e) {
        const target = e.target;
        const menuItem = target.closest('.menu-item-card');

        if (!menuItem) return;

        if (target.closest('.remove-menu-item')) {
            this.removeMenuItem(menuItem);
        } else if (target.closest('.edit-menu-item')) {
            this.openModalForEdit(menuItem);
        } else if (target.closest('.add-child-item')) {
            const parentIndex = target.closest('.add-child-item').dataset.parentIndex;
            this.openModalForNewItem(parentIndex);
        } else if (target.closest('.menu-item-handle')) {
        } else {
            this.openModalForEdit(menuItem);
        }
    }

    openModalForNewItem(parentIndex = null) {
        this.currentEditIndex = null;
        this.currentParentIndex = parentIndex;
        this.isEditing = false;
        
        document.getElementById('menuItemModalLabel').innerHTML = 
            '<i class="bi bi-plus-circle me-2"></i>' + 
            (parentIndex ? 'Добавить подпункт' : 'Добавить пункт меню');
        
        document.getElementById('menu-item-form').reset();
        document.getElementById('edit-item-index').value = '';
        document.getElementById('parent-item-index').value = parentIndex || '';
        document.getElementById('item-target').value = '_self';
        
        const extraCheckbox = document.getElementById('item-extra');
        if (extraCheckbox) extraCheckbox.checked = false;
        
        if (window.menuIconManager) {
            window.menuIconManager.clearSelectedIcon();
        }
    }

    openModalForEdit(itemElement) {
        const itemData = JSON.parse(itemElement.dataset.item);
        this.currentEditIndex = itemElement.dataset.index;
        this.isEditing = true;
        
        document.getElementById('menuItemModalLabel').innerHTML = 
            '<i class="bi bi-pencil me-2"></i>Редактировать пункт меню';
        document.getElementById('item-title').value = itemData.title || '';
        document.getElementById('item-url').value = itemData.url || '';
        document.getElementById('item-target').value = itemData.target || '_self';
        document.getElementById('item-class').value = itemData.class || '';
        document.getElementById('edit-item-index').value = this.currentEditIndex;
        document.getElementById('parent-item-index').value = '';
        
        const extraCheckbox = document.getElementById('item-extra');
        if (extraCheckbox) {
            extraCheckbox.checked = itemData.is_extra || false;
        }
        
        this.fillVisibilitySettings(itemData.visibility);
        if (window.menuIconManager) {
            window.menuIconManager.clearSelectedIcon();
            if (itemData.icon && itemData.icon.id) {
                window.menuIconManager.setIconData(itemData.icon);
            }
        }
    }

    fillVisibilitySettings(visibility) {
        const showToSelect = document.getElementById('item-show-to');
        const hideFromSelect = document.getElementById('item-hide-from');
        
        Array.from(showToSelect.options).forEach(option => option.selected = false);
        Array.from(hideFromSelect.options).forEach(option => option.selected = false);
        
        if (visibility) {
            if (visibility.show_to_groups) {
                visibility.show_to_groups.forEach(groupId => {
                    const option = Array.from(showToSelect.options).find(opt => opt.value === groupId);
                    if (option) option.selected = true;
                });
            }
            
            if (visibility.hide_from_groups) {
                visibility.hide_from_groups.forEach(groupId => {
                    const option = Array.from(hideFromSelect.options).find(opt => opt.value === groupId);
                    if (option) option.selected = true;
                });
            }
        }
    }

    saveMenuItemFromModal() {
        const title = document.getElementById('item-title').value.trim();
        const url = document.getElementById('item-url').value.trim();
        const target = document.getElementById('item-target').value;
        const cssClass = document.getElementById('item-class').value.trim();
        const isExtra = document.getElementById('item-extra')?.checked || false;

        if (!title || !url) {
            alert('Пожалуйста, заполните название и URL');
            return;
        }

        const visibility = this.collectVisibilitySettings();
        
        let iconData = null;
        if (window.menuIconManager) {
            iconData = window.menuIconManager.getIconData();
            if (iconData && (!iconData.id || iconData.id === '')) {
                iconData = null;
            }
        }

        const itemData = {
            title: title,
            url: url,
            target: target,
            class: cssClass,
            visibility: visibility,
            icon: iconData,
            icon_only: iconData ? (iconData.icon_only || false) : false,
            is_extra: isExtra
        };

        if (this.currentEditIndex) {
            this.updateMenuItem(this.currentEditIndex, itemData);
        } else {
            this.addNewMenuItem(itemData, this.currentParentIndex);
        }

        const menuModal = bootstrap.Modal.getInstance(document.getElementById('menuItemModal'));
        if (menuModal) {
            menuModal.hide();
        }
    }

    collectVisibilitySettings() {
        const showToSelect = document.getElementById('item-show-to');
        const hideFromSelect = document.getElementById('item-hide-from');
        
        const showToGroups = Array.from(showToSelect.selectedOptions)
            .map(option => option.value)
            .filter(value => value !== '');
            
        const hideFromGroups = Array.from(hideFromSelect.selectedOptions)
            .map(option => option.value)
            .filter(value => value !== '');
        
        if (showToGroups.length === 0 && hideFromGroups.length === 0) {
            return null;
        }
        
        return {
            show_to_groups: showToGroups,
            hide_from_groups: hideFromGroups
        };
    }

    addNewMenuItem(itemData, parentIndex = null) {
        let container;
        let level = 0;

        if (parentIndex) {
            const parentItem = this.findMenuItemByIndex(parentIndex);
            if (!parentItem) {
                console.error('Parent item not found for index:', parentIndex);
                return;
            }

            let childrenContainer = parentItem.querySelector('.menu-children');
            if (!childrenContainer) {
                const childrenContainerHTML = `
                    <div class="menu-children-container mt-3">
                        <div class="border-top pt-3">
                            <div class="menu-children sortable-menu"></div>
                        </div>
                    </div>
                `;
                
                const cardBody = parentItem.querySelector('.card-body');
                if (cardBody) {
                    const existingEmptyContainer = parentItem.querySelector('.menu-children-container[style*="display: none"]');
                    if (existingEmptyContainer) {
                        existingEmptyContainer.remove();
                    }
                    
                    cardBody.insertAdjacentHTML('beforeend', childrenContainerHTML);
                    childrenContainer = parentItem.querySelector('.menu-children');
                }
            }

            container = childrenContainer;
            level = parseInt(parentItem.dataset.level) + 1;
            
            const childrenContainerElement = parentItem.querySelector('.menu-children-container');
            if (childrenContainerElement) {
                childrenContainerElement.style.display = 'block';
            }

            const parentIcon = parentItem.querySelector('.bi');
            if (parentIcon) {
                parentIcon.className = 'bi bi-folder-fill text-warning me-2';
            }
        } else {
            container = document.getElementById('menu-items-container');
            level = 0;
        }

        if (!container) {
            console.error('Container not found for parent index:', parentIndex);
            return;
        }

        const index = this.itemCounter++;
        const fullIndex = parentIndex ? parentIndex + '_' + index : index;

        let iconHtml = '';
        if (itemData.icon && itemData.icon.id) {
            const iconStyle = this.getIconStyle(itemData.icon);
            iconHtml = `
                <div class="menu-icon-preview me-2" style="${iconStyle}">
                    <i class="bi bi-check-circle text-success"></i>
                </div>
            `;
        }

        const itemHTML = `
            <div class="menu-item-card card mb-2 level-${Math.min(level, 4)}" 
                data-index="${fullIndex}" 
                data-level="${level}"
                data-item='${JSON.stringify({
                    title: itemData.title || '',
                    url: itemData.url || '',
                    class: itemData.class || '',
                    target: itemData.target || '_self',
                    icon: itemData.icon || null,
                    visibility: itemData.visibility || null,
                    icon_only: itemData.icon_only || false,
                    is_extra: itemData.is_extra || false
                })}'>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center flex-grow-1">
                            <div class="menu-level-indicator me-3">
                                ${'<span class="level-line"></span>'.repeat(level)}
                                <span class="level-dot"></span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    ${iconHtml}
                                    <i class="bi bi-link-45deg text-primary me-2"></i>
                                    <div>
                                        <h6 class="mb-1">${this.escapeHtml(itemData.title || 'Новый пункт')} 
                                            ${itemData.icon_only ? '<span class="badge bg-info ms-2">только иконка</span>' : ''}
                                        </h6>
                                        <small class="text-muted">${this.escapeHtml(itemData.url || '')}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary menu-item-handle" title="Перетащить">
                                <i class="bi bi-arrows-move"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary edit-menu-item" 
                                    title="Редактировать"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#menuItemModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success add-child-item" 
                                    title="Добавить подпункт"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#menuItemModal"
                                    data-parent-index="${fullIndex}">
                                <i class="bi bi-patch-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger remove-menu-item" title="Удалить">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="menu-children-container mt-3" style="display: none;">
                        <div class="border-top pt-3">
                            <div class="menu-children sortable-menu"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', itemHTML);

        if (!parentIndex) {
            this.hideEmptyMessage();
        }

        this.updateMenuStructure();
        this.initializeSortable();
        this.updateStatistics();
    }

    updateMenuItem(index, itemData) {
        const itemElement = this.findMenuItemByIndex(index);
        if (!itemElement) return;

        itemElement.dataset.item = JSON.stringify(itemData);
        const titleElement = itemElement.querySelector('h6');
        const urlElement = itemElement.querySelector('small');
        const iconElement = itemElement.querySelector('.bi');

        if (titleElement) titleElement.textContent = itemData.title || 'Без названия';
        if (urlElement) urlElement.textContent = itemData.url;

        const hasChildren = itemElement.querySelector('.menu-children')?.children.length > 0;
        if (iconElement) {
            iconElement.className = hasChildren ? 
                'bi bi-folder-fill text-warning me-2' : 
                'bi bi-link-45deg text-primary me-2';
        }

        this.updateMenuStructure();
        this.updateStatistics();
    }

    findMenuItemByIndex(index) {
        return document.querySelector(`[data-index="${index}"]`);
    }

    removeMenuItem(menuItem) {
        if (!confirm('Удалить этот пункт меню?')) return;

        const children = menuItem.querySelectorAll('.menu-item-card');
        children.forEach(child => child.remove());

        menuItem.remove();
        
        const container = document.getElementById('menu-items-container');
        if (container.children.length === 0) {
            this.showEmptyMessage();
        }
        
        this.updateMenuStructure();
        this.updateStatistics();
    }

    expandAll() {
        document.querySelectorAll('.menu-children-container').forEach(container => {
            container.style.display = 'block';
        });
    }

    collapseAll() {
        document.querySelectorAll('.menu-children-container').forEach(container => {
            container.style.display = 'none';
        });
    }

    initializeSortable() {
        if (typeof Sortable === 'undefined') return;

        const mainContainer = document.getElementById('menu-items-container');
        if (mainContainer) {
            new Sortable(mainContainer, {
                handle: '.menu-item-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: () => this.updateMenuStructure()
            });
        }

        document.querySelectorAll('.menu-children').forEach(container => {
            new Sortable(container, {
                handle: '.menu-item-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: () => this.updateMenuStructure()
            });
        });
    }

    updateMenuStructure() {
        const structure = this.collectMenuStructure();
        document.getElementById('menu-structure').value = JSON.stringify(structure);
        this.updateStatistics();
    }

    collectMenuStructure() {
        const structure = [];
        const container = document.getElementById('menu-items-container');
        
        if (!container) return structure;
        
        container.querySelectorAll(':scope > .menu-item-card').forEach(item => {
            if (!item.closest('.menu-children')) {
                structure.push(this.collectMenuItemData(item));
            }
        });
        
        return structure;
    }

    collectMenuItemData(menuItem) {
        const itemData = JSON.parse(menuItem.dataset.item);
        const childrenContainer = menuItem.querySelector('.menu-children');
        if (childrenContainer && childrenContainer.children.length > 0) {
            itemData.children = [];
            childrenContainer.querySelectorAll(':scope > .menu-item-card').forEach(childItem => {
                itemData.children.push(this.collectMenuItemData(childItem));
            });
        } else {
            itemData.children = [];
        }
        
        return itemData;
    }

    updateStatistics() {
        const totalItems = document.querySelectorAll('.menu-item-card').length;
        const nestedItems = document.querySelectorAll('.menu-children .menu-item-card').length;
        
        const totalSpan = document.getElementById('total-items');
        const nestedSpan = document.getElementById('nested-items');
        
        if (totalSpan) totalSpan.textContent = totalItems;
        if (nestedSpan) nestedSpan.textContent = nestedItems;
    }

    hideEmptyMessage() {
        const emptyMessage = document.getElementById('menu-empty');
        if (emptyMessage) {
            emptyMessage.classList.add('d-none');
        }
    }

    showEmptyMessage() {
        const emptyMessage = document.getElementById('menu-empty');
        if (emptyMessage) {
            emptyMessage.classList.remove('d-none');
        }
    }

    handleFormSubmit(e) {
        const useCustomCheckbox = document.getElementById('use_custom_template');
        const templateSelect = document.querySelector('select[name="template"]');
        
        if (templateSelect) {
            if (useCustomCheckbox && useCustomCheckbox.checked) {
                templateSelect.removeAttribute('required');
            } else {
                templateSelect.setAttribute('required', 'required');
            }
        }
        
        this.updateMenuStructure();
        
        if (this.customEditor) {
            const textarea = document.getElementById('custom_template');
            if (textarea) {
                textarea.value = this.customEditor.getValue();
            }
        }
        
        const structure = this.collectMenuStructure();
        let isValid = true;
        const errors = [];
        
        const validateItem = (item, path = '') => {
            if (!item.title || item.title.trim() === '') {
                isValid = false;
                errors.push(`${path}Не заполнено название`);
            }
            if (!item.url || item.url.trim() === '') {
                isValid = false;
                errors.push(`${path}Не заполнен URL`);
            }
            
            if (item.children && item.children.length > 0) {
                item.children.forEach((child, index) => {
                    validateItem(child, `${path}Пункт ${index + 1} → `);
                });
            }
        };
        
        structure.forEach((item, index) => {
            validateItem(item, `Пункт ${index + 1}: `);
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Ошибки в форме:\n' + errors.join('\n'));
            return;
        }
        
    }

    escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    setupShortcodeHints() {
        const urlInput = document.getElementById('item-url');
        if (!urlInput) return;
        
        document.querySelectorAll('.shortcode-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const shortcode = e.currentTarget.dataset.shortcode;
                this.insertShortcodeAtCursor(shortcode);
            });
        });
        
        urlInput.addEventListener('input', () => {
            this.updateShortcodePreview();
        });
        
        urlInput.addEventListener('focus', () => {
            this.updateShortcodePreview();
        });
        
        this.setupCustomFieldShortcode();
    }
    
    insertShortcodeAtCursor(shortcode) {
        const urlInput = document.getElementById('item-url');
        if (!urlInput) return;
        
        const start = urlInput.selectionStart;
        const end = urlInput.selectionEnd;
        const text = urlInput.value;
        const before = text.substring(0, start);
        const after = text.substring(end);
        
        urlInput.value = before + shortcode + after;
        urlInput.focus();
        
        const newCursorPos = start + shortcode.length;
        urlInput.setSelectionRange(newCursorPos, newCursorPos);
        
        this.updateShortcodePreview();
    }
    
    updateShortcodePreview() {
        const urlInput = document.getElementById('item-url');
        const previewElement = document.getElementById('shortcode-preview');
        const previewTextElement = document.getElementById('preview-text');
        
        if (!urlInput || !previewElement || !previewTextElement) return;
        
        const url = urlInput.value;
        
        if (!url.trim()) {
            previewElement.style.display = 'none';
            return;
        }
        
        let preview = url
            .replace(/\{username\}/g, 'vasya')
            .replace(/\{user_id\}/g, '123')
            .replace(/\{email\}/g, 'user@example.com')
            .replace(/\{first_name\}/g, 'Вася')
            .replace(/\{last_name\}/g, 'Пупкин')
            .replace(/\{display_name\}/g, 'Василий')
            .replace(/\{slug\}/g, 'vasya-pupkin')
            .replace(/\{base_url\}/g, window.location.origin)
            .replace(/\{admin_url\}/g, window.location.origin + '/admin')
            .replace(/\{year\}/g, new Date().getFullYear())
            .replace(/\{month\}/g, String(new Date().getMonth() + 1).padStart(2, '0'))
            .replace(/\{day\}/g, String(new Date().getDate()).padStart(2, '0'));
        
        preview = preview.replace(/\{user_field:([^}]+)\}/g, 'значение_поля');
        
        previewTextElement.textContent = preview;
        previewElement.style.display = 'block';
    }
    
    setupCustomFieldShortcode() {
        const customFieldBtn = document.createElement('button');
        customFieldBtn.type = 'button';
        customFieldBtn.className = 'btn btn-outline-info btn-sm mt-1';
        customFieldBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i>Добавить кастомное поле';
        customFieldBtn.addEventListener('click', () => {
            const fieldName = prompt('Введите название поля пользователя (например: phone, city):');
            if (fieldName) {
                this.insertShortcodeAtCursor(`{user_field:${fieldName}}`);
            }
        });
        
        const shortcodeButtons = document.getElementById('shortcode-buttons');
        if (shortcodeButtons) {
            shortcodeButtons.parentNode.appendChild(customFieldBtn);
        }
    }
    
    addAdditionalShortcodeButtons() {
        const additionalShortcodes = [
            { code: '{first_name}', label: '{first_name}' },
            { code: '{last_name}', label: '{last_name}' },
            { code: '{display_name}', label: '{display_name}' },
            { code: '{slug}', label: '{slug}' },
            { code: '{admin_url}', label: '{admin_url}' },
            { code: '{year}', label: '{year}' },
            { code: '{month}', label: '{month}' },
            { code: '{day}', label: '{day}' }
        ];
        
        const container = document.getElementById('shortcode-buttons');
        if (!container) return;
        
        additionalShortcodes.forEach(shortcode => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-secondary btn-sm shortcode-btn';
            btn.dataset.shortcode = shortcode.code;
            btn.textContent = shortcode.label;
            btn.title = 'Нажмите чтобы вставить шорткод';
            
            btn.addEventListener('click', (e) => {
                this.insertShortcodeAtCursor(e.currentTarget.dataset.shortcode);
            });
            
            container.appendChild(btn);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.menuBuilder = new MenuBuilder();
    window.menuIconManager = new MenuIconManager();
});