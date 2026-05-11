﻿class PostBlocksManager {
    constructor() {
        this.blocksContainer = document.getElementById('post-blocks-container');
        this.blockButtons = document.getElementById('post-block-buttons');
        this.blocksData = [];
        this.currentModal = null;
        this.currentCategory = 'all';
        this.currentSearch = '';
        this.previewCache = new Map();
        this.previewQueue = [];
        this.isProcessingQueue = false;
        this.searchTimeout = null;
        this.reinitTimeout = null;

        if (!this.blocksContainer || !this.blockButtons) {
            return;
        }

        this.ensureAdminUrl();
        this.init();
    }

    ensureAdminUrl() {
        if (typeof window.ADMIN_URL === 'undefined') {
            const path = window.location.pathname;
            if (path.includes('/admin/')) {
                const base = path.split('/admin/')[0];
                window.ADMIN_URL = base + '/admin';
            } else {
                window.ADMIN_URL = '/admin';
            }
        }
    }

    init() {
        this.loadInitialBlocks();
        this.renderBlockButtons();
        this.bindEvents();
        this.initSortable();
        this.processPreviewQueue();
    }

    renderBlockButtons() {
        if (!window.availablePostBlocks || Object.keys(window.availablePostBlocks).length === 0) {
            this.blockButtons.innerHTML = `<div class="text-center text-muted py-3 w-100">${lang === 'ru' ? 'Нет доступных блоков' : 'No blocks available'}</div>`;
            return;
        }

        const categories = {};
        Object.entries(window.availablePostBlocks).forEach(([systemName, block]) => {
            const category = block.category || 'other';
            if (!categories[category]) categories[category] = [];
            categories[category].push({ ...block, system_name: systemName });
        });

        let html = '';
        const categoryOrder = ['basic', 'text', 'media', 'layout', 'advanced', 'other'];
        const sortedCategories = Object.keys(categories).sort((a, b) => {
            const indexA = categoryOrder.indexOf(a);
            const indexB = categoryOrder.indexOf(b);
            return (indexA === -1 ? 999 : indexA) - (indexB === -1 ? 999 : indexB);
        });

        sortedCategories.forEach(category => {
            const filteredBlocks = this.filterBlocks(categories[category]);
            if (filteredBlocks.length > 0) {
                html += this.renderCategorySection(category, filteredBlocks);
            }
        });

        this.blockButtons.innerHTML = html || `<div class="text-center text-muted py-2 w-100">${lang === 'ru' ? 'Блоки не найдены' : 'No blocks found'}</div>`;
        this.initTooltips(this.blockButtons);
    }

    renderCategorySection(category, blocks) {
        const categoryName = this.getCategoryName(category);
        return `
            <div class="block-category-section mb-3" data-category="${category}">
                <div class="text-muted small text-uppercase fw-bold mb-2">${categoryName}</div>
                <div class="d-flex flex-wrap gap-1">
                    ${blocks.map(block => this.renderBlockButton(block)).join('')}
                </div>
            </div>`;
    }

    renderBlockButton(block) {
        return `
            <button type="button"
                class="btn btn-sm btn-light border add-post-block"
                data-type="${block.system_name}"
                data-category="${block.category || 'other'}"
                title="${block.name}${block.description ? ' - ' + block.description : ''}"
                data-bs-toggle="tooltip">
                <i class="${block.icon}"></i>
            </button>`;
    }

    filterBlocks(blocks) {
        return blocks.filter(block => {
            const categoryMatch = this.currentCategory === 'all' || block.category === this.currentCategory;
            const searchMatch = !this.currentSearch || 
                block.name.toLowerCase().includes(this.currentSearch.toLowerCase()) ||
                (block.description && block.description.toLowerCase().includes(this.currentSearch.toLowerCase()));
            return categoryMatch && searchMatch;
        });
    }

    getCategoryName(category) {
        const names = {
            'text': lang === 'ru' ? '📝 Текст' : '📝 Text',
            'media': lang === 'ru' ? '🖼️ Медиа' : '🖼️ Media',
            'layout': lang === 'ru' ? '📐 Компоновка' : '📐 Layout',
            'advanced': lang === 'ru' ? '⚙️ Расширенные' : '⚙️ Advanced',
            'basic': lang === 'ru' ? '🔧 Основные' : '🔧 Basic',
            'other': lang === 'ru' ? '📦 Другие' : '📦 Other'
        };
        return names[category] || category;
    }

    loadInitialBlocks() {
        if (window.initialPostBlocks && Array.isArray(window.initialPostBlocks)) {
            this.blocksData = window.initialPostBlocks.map(block => this.normalizeBlockData(block));
            this.renderBlocksStructureOnly();
            this.queueAllPreviews();
        }
    }

    async addBlock(blockType) {
        const blockId = `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        
        const [defaultContent, defaultSettings] = await Promise.all([
            this.getDefaultContent(blockType),
            this.getDefaultSettings(blockType)
        ]);

        const newBlock = {
            id: blockId,
            type: blockType,
            content: defaultContent,
            settings: defaultSettings,
            order: this.blocksData.length
        };

        this.blocksData.push(newBlock);
        
        const blockInfo = window.availablePostBlocks[blockType];
        if (blockInfo) {
            const html = await this.renderBlockWithPreview(newBlock, blockInfo, this.blocksData.length - 1);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            this.blocksContainer.appendChild(tempDiv.firstElementChild);
            this.blocksContainer.querySelector('.empty-state')?.remove();
            this.queuePreview(newBlock);
        }

        this.updateHiddenField();
        this.updateBlocksOrderNumbers();
        this.editBlock(blockId);
    }

    removeBlock(blockId) {
        if (confirm('Удалить этот блок?')) {
            this.blocksData = this.blocksData.filter(block => block.id !== blockId);
            const blockElement = document.querySelector(`.post-block-item[data-block-id="${blockId}"]`);
            if (blockElement) {
                blockElement.remove();
            }
            
            if (this.blocksData.length === 0) {
                this.renderBlocksStructureOnly();
            } else {
                this.updateBlocksOrderNumbers();
            }
            this.updateHiddenField();
        }
    }

    editBlock(blockId) {
        const block = this.blocksData.find(b => b.id === blockId);
        if (!block) return;

        const blockInfo = window.availablePostBlocks?.[block.type];
        if (!blockInfo) {
            alert(lang === 'ru' ? `Информация о блоке не найдена: ${block.type}` : `Block information not found: ${block.type}`);
            return;
        }

        this.openBlockSettingsModal(block, blockInfo);
    }

    openBlockSettingsModal(block, blockInfo) {
        this.closeCurrentModal();

        const modalId = 'post-block-settings-modal';
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="${blockInfo.icon} me-2"></i>${blockInfo.name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="post-block-settings-content">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">${lang === 'ru' ? 'Загрузка формы...' : 'Loading form...'}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${lang === 'ru' ? 'Отмена' : 'Cancel'}</button>
                        <button type="button" class="btn btn-primary" id="save-post-block-settings">
                            <i class="bi bi-check-lg me-1"></i>${lang === 'ru' ? 'Сохранить' : 'Save'}
                        </button>
                    </div>
                </div>
            </div>`;

        document.body.appendChild(modal);
        this.currentModal = modal;
        
        const bsModal = new bootstrap.Modal(modal, { backdrop: 'static' });
        bsModal.show();

        modal.addEventListener('hidden.bs.modal', () => this.closeCurrentModal(), { once: true });
        
        this.loadBlockSettingsForm(block, blockInfo, modal);
    }

    async loadBlockSettingsForm(block, blockInfo, modal) {
        const content = modal.querySelector('#post-block-settings-content');
        try {
            const [presets, response] = await Promise.all([
                this.getBlockPresets(block.type),
                fetch(`${window.ADMIN_URL}/post-blocks/get-settings-form`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        system_name: block.type,
                        current_settings: block.settings || {},
                        current_content: block.content || {}
                    })
                })
            ]);
            
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            let html = await response.text();
            if (presets.length > 0) {
                html = this.insertPresetSelectorIntoForm(html, this.createPresetSelector(presets, block));
            }
            
            content.innerHTML = html;
            this.applySavedBlockData(block);
            this.initPresetHandlers(block, presets);

            setTimeout(() => {
                if (typeof window.RichTextEditor !== 'undefined') {
                    const wrappers = document.querySelectorAll('.rich-text-wrapper');
                    wrappers.forEach(wrapper => {
                        const id = wrapper.id;
                        if (id && !wrapper.dataset.editorInitialized) {
                            new window.RichTextEditor(id);
                            wrapper.dataset.editorInitialized = 'true';
                        }
                    });
                }
                this.reinitializeAllBlocks();
            }, 150);
            
            this.bindSaveHandler(block.id, modal);
        } catch (error) {
            console.error('Form load error:', error);
            content.innerHTML = `<div class="alert alert-danger">${lang === 'ru' ? 'Ошибка загрузки формы: ' : 'Error loading form: '}${error.message}</div>`;
        }
    }

    applySavedBlockData(block) {
        const setContent = (name, value) => {
            const input = document.querySelector(`[name="${name}"]`);
            if (!input) return;
            
            if (input.type === 'checkbox') input.checked = Boolean(value);
            else if (input.type === 'radio') {
                const radio = document.querySelector(`[name="${name}"][value="${value}"]`);
                if (radio) radio.checked = true;
            } else input.value = value || '';
        };

        if (block.settings) {
            Object.entries(block.settings).forEach(([key, value]) => {
                setContent(`settings[${key}]`, value);
            });
        }
        if (block.content) {
            Object.entries(block.content).forEach(([key, value]) => {
                setContent(`content[${key}]`, value);
            });
        }
    }

    bindSaveHandler(blockId, modal) {
        const saveButton = document.getElementById('save-post-block-settings');
        if (!saveButton) return;

        saveButton.onclick = async () => {
            const richEditors = modal.querySelectorAll('.rich-text-editor');
            richEditors.forEach(editor => {
                const wrapper = editor.closest('.rich-text-wrapper');
                if (wrapper) {
                    const hiddenTextarea = wrapper.querySelector('textarea[name="content[content]"]');
                    if (hiddenTextarea) {
                        hiddenTextarea.value = editor.innerHTML;
                    }
                }
            });
            const originalText = saveButton.innerHTML;
            saveButton.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${lang === 'ru' ? 'Сохранение...' : 'Saving...'}`;
            saveButton.disabled = true;

            try {
                const block = this.blocksData.find(b => b.id === blockId);
                if (!block) throw new Error(lang === 'ru' ? 'Блок не найден' : 'Block not found');

                const form = modal.querySelector('form');
                if (!form) throw new Error(lang === 'ru' ? 'Форма не найдена' : 'Form not found');

                const formData = new FormData(form);
                const contentData = {};
                const settingsData = {};

                for (const [key, value] of formData.entries()) {
                    if (key.startsWith('content[')) contentData[key.match(/content\[(.*?)\]/)[1]] = value;
                    else if (key.startsWith('settings[')) settingsData[key.match(/settings\[(.*?)\]/)[1]] = value;
                }

                const presetSelect = document.getElementById('block-preset-select');
                if (presetSelect && presetSelect.value) {
                    settingsData.preset_id = presetSelect.value;
                    settingsData.preset_name = presetSelect.options[presetSelect.selectedIndex].getAttribute('data-name');
                }

                block.content = { ...block.content, ...contentData };
                block.settings = { ...block.settings, ...settingsData };

                const uploadFormData = new FormData(form);
                uploadFormData.append('block_id', blockId);
                uploadFormData.append('block_type', block.type);
                uploadFormData.append('content_json', JSON.stringify(block.content));
                uploadFormData.append('settings_json', JSON.stringify(block.settings));

                const response = await fetch(`${window.ADMIN_URL}/post-blocks/upload-block-files`, {
                    method: 'POST',
                    body: uploadFormData
                });

                const data = await response.json();
                if (data.success) {
                    if (data.block_data) {
                        block.content = data.block_data.content || block.content;
                        block.settings = data.block_data.settings || block.settings;
                    }
                    
                    this.closeCurrentModal();
                    this.invalidateBlockCache(block);
                    await this.updateBlockPreview(blockId);
                    this.updateHiddenField();
                    this.showNotification(lang === 'ru' ? 'Настройки сохранены' : 'Settings saved', 'success');
                } else {
                    throw new Error(data.message || (lang === 'ru' ? 'Ошибка сохранения' : 'Save error'));
                }
            } catch (error) {
                console.error('Save error:', error);
                this.showNotification((lang === 'ru' ? 'Ошибка: ' : 'Error: ') + error.message, 'error');
            } finally {
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
            }
        };
    }

    getCacheKey(block) {
        const str = `${block.type}:${JSON.stringify(block.content)}:${JSON.stringify(block.settings)}`;
        return this.simpleHash(str);
    }

    simpleHash(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = (hash << 5) - hash + str.charCodeAt(i);
            hash |= 0;
        }
        return hash.toString(36);
    }

    queueAllPreviews() {
        this.blocksData.forEach(block => this.queuePreview(block));
    }

    queuePreview(block) {
        this.previewQueue.push(block);
        if (!this.isProcessingQueue) {
            this.processPreviewQueue();
        }
    }

    async processPreviewQueue() {
        if (this.isProcessingQueue || this.previewQueue.length === 0) return;

        this.isProcessingQueue = true;

        while (this.previewQueue.length > 0) {
            const block = this.previewQueue.shift();
            await this.loadBlockPreview(block.id, block.type, block.content, block.settings);
            await new Promise(resolve => setTimeout(resolve, 50)); 
        }

        this.isProcessingQueue = false;
    }

    async loadBlockPreview(blockId, blockType, content, settings) {
        const previewContainer = document.getElementById(`preview-${blockId}`);
        if (!previewContainer) return;

        const tempBlock = { id: blockId, type: blockType, content, settings };
        const cacheKey = this.getCacheKey(tempBlock);

        if (this.previewCache.has(cacheKey)) {
            previewContainer.innerHTML = this.previewCache.get(cacheKey);
            this.initPreviewActions(blockId);
            return;
        }

        if (!previewContainer.innerHTML.trim()) {
            previewContainer.innerHTML = `
                <div class="text-center py-3 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>`;
        }

        try {
            const response = await fetch(`${window.ADMIN_URL}/post-blocks/get-preview`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    block_id: blockId,
                    block_type: blockType,
                    content: this.normalizeContentData(blockType, content),
                    settings: settings || {}
                })
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            if (data.success && data.html) {
                previewContainer.innerHTML = data.html;
                previewContainer.classList.add('preview-loaded');
                this.initPreviewActions(blockId);
                
                this.previewCache.set(cacheKey, data.html);
            } else {
                throw new Error(data.message || (lang === 'ru' ? 'Ошибка данных' : 'Data error'));
            }
        } catch (error) {
            console.warn(`Preview load failed for ${blockId}:`, error);
            previewContainer.innerHTML = this.renderErrorPreview(blockType, error.message);
        }
    }

    async updateBlockPreview(blockId) {
        const block = this.blocksData.find(b => b.id === blockId);
        if (!block) return;

        this.invalidateBlockCache(block);
        this.previewQueue.unshift(block);
        this.processPreviewQueue();
    }

    invalidateBlockCache(block) {
        const cacheKey = this.getCacheKey(block);
        this.previewCache.delete(cacheKey);
    }

    renderErrorPreview(blockType, error) {
        return `
            <div class="alert alert-warning small mb-0 py-2">
                <i class="bi bi-exclamation-circle me-1"></i>
                ${lang === 'ru' ? 'Ошибка превью: ' : 'Preview error: '}${this.escapeHtml(error)}
            </div>`;
    }

    renderBlocksStructureOnly() {
        this.blocksData.sort((a, b) => a.order - b.order);

        if (this.blocksData.length === 0) {
            this.blocksContainer.innerHTML = `
                <div class="text-center text-muted py-5 empty-state">
                    <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                    <p class="mb-1">${lang === 'ru' ? 'Нет добавленных блоков' : 'No blocks added'}</p>
                    <small class="text-muted">${lang === 'ru' ? 'Добавьте блоки из панели выше' : 'Add blocks from the panel above'}</small>
                </div>`;
            return;
        }

        this.blocksContainer.querySelector('.empty-state')?.remove();
        let html = '';
        this.blocksData.forEach((block, index) => {
            const blockInfo = window.availablePostBlocks?.[block.type];
            if (blockInfo) {
                html += this.renderBlockStructure(block, blockInfo, index);
            }
        });
        this.blocksContainer.innerHTML = html;
        this.initTooltips(this.blocksContainer);
    }

    async renderBlocks() {
        this.renderBlocksStructureOnly();
        this.queueAllPreviews();
    }

    renderBlockStructure(block, blockInfo, index) {
        const hasPreset = block.settings?.preset_id;
        const presetBadge = hasPreset ? 
            `<span class="badge bg-success text-dark"><i class="bi bi-gear me-1"></i>${this.escapeHtml(block.settings.preset_name || (lang === 'ru' ? 'Пресет' : 'Preset'))}</span>` : '';

        return `
            <div class="post-block-item ${hasPreset ? 'has-preset' : ''}" data-block-id="${block.id}" data-block-type="${block.type}">
                <div class="post-block-item-inner">
                    <div class="block-header d-flex align-items-center justify-content-between p-2 border-bottom bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <span class="block-order fw-bold text-white small">${index + 1}</span>
                            <div class="block-type-info">
                                <i class="${blockInfo.icon} me-1"></i>
                                <span class="block-type-name small fw-bold">${blockInfo.name}</span>
                                ${presetBadge}
                            </div>
                        </div>
                        <div class="block-actions d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-light edit-post-block" title="${lang === 'ru' ? 'Редактировать' : 'Edit'}"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-sm btn-light text-danger remove-post-block" title="${lang === 'ru' ? 'Удалить' : 'Delete'}"><i class="bi bi-trash"></i></button>
                            <span class="drag-handle btn btn-sm btn-light" title="${lang === 'ru' ? 'Перетащить' : 'Drag'}"><i class="bi bi-grip-vertical"></i></span>
                        </div>
                    </div>
                    <div class="block-preview-container p-3" id="preview-${block.id}">
                        <!-- ${lang === 'ru' ? 'Превью загрузится асинхронно' : 'Preview will load asynchronously'} -->
                    </div>
                </div>
            </div>`;
    }

    async renderBlockWithPreview(block, blockInfo, index) {
        return this.renderBlockStructure(block, blockInfo, index);
    }

    updateBlocksOrderNumbers() {
        const elements = this.blocksContainer.querySelectorAll('.post-block-item');
        elements.forEach((el, idx) => {
            const orderEl = el.querySelector('.block-order');
            if (orderEl) orderEl.textContent = idx + 1;
            
            const blockId = el.getAttribute('data-block-id');
            const block = this.blocksData.find(b => b.id === blockId);
            if (block) block.order = idx;
        });
        this.blocksData.sort((a, b) => a.order - b.order);
        this.updateHiddenField();
    }

    async getDefaultContent(blockType) {
        try {
            const res = await fetch(`${window.ADMIN_URL}/post-blocks/get-default-content`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ system_name: blockType })
            });
            const data = await res.json();
            return data.success ? data.content : {};
        } catch { return {}; }
    }

    async getDefaultSettings(blockType) {
        try {
            const res = await fetch(`${window.ADMIN_URL}/post-blocks/get-default-settings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ system_name: blockType })
            });
            const data = await res.json();
            return data.success ? data.settings : {};
        } catch { return {}; }
    }

    async getBlockPresets(blockType) {
        try {
            const res = await fetch(`${window.ADMIN_URL}/post-blocks/get-presets`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ system_name: blockType })
            });
            const data = await res.json();
            return data.success ? (data.presets || []) : [];
        } catch { return []; }
    }

    normalizeContentData(blockType, content) {
        if (!content) return {};
        const normalized = { ...content };
        
        if (blockType === 'TextBlock' && content.content && !content.text) {
            normalized.text = content.content;
        }
        if (blockType === 'ImageBlock') {
            if (content.image_url && !content.url) normalized.url = content.image_url;
            if (content.alt_text && !content.alt) normalized.alt = content.alt_text;
        }
        return normalized;
    }

    normalizeBlockData(block) {
        const normalized = { ...block };
        if (normalized.content) {
            if (normalized.type === 'TextBlock' && normalized.content.content && !normalized.content.text) {
                normalized.content.text = normalized.content.content;
            }
        }
        if (Array.isArray(normalized.settings) && normalized.settings.length === 0) {
            normalized.settings = {};
        }
        return normalized;
    }

    initPreviewActions(blockId) {
        const editBtn = this.blocksContainer.querySelector(`#preview-${blockId} .preview-edit-btn`);
        if (editBtn) {
            editBtn.onclick = (e) => { e.preventDefault(); e.stopPropagation(); this.editBlock(blockId); };
        }
    }

    createPresetSelector(presets, currentBlock) {
        const currentPresetId = currentBlock.settings?.preset_id || '';
        let options = `<option value="">${lang === 'ru' ? '-- Без пресета --' : '-- No preset --'}</option>`;
        
        presets.forEach(preset => {
            const selected = currentPresetId == preset.id ? 'selected' : '';
            options += `<option value="${preset.id}" ${selected} data-template="${this.escapeHtml(preset.preset_template || '')}" data-name="${this.escapeHtml(preset.preset_name)}">${this.escapeHtml(preset.preset_name)}</option>`;
        });

        return `
            <div class="mb-3 p-3 bg-light rounded">
                <label class="form-label small fw-bold mb-1">${lang === 'ru' ? 'Пресет оформления' : 'Design preset'}</label>
                <select class="form-select form-select-sm" id="block-preset-select">${options}</select>
            </div>`;
    }

    insertPresetSelectorIntoForm(html, presetSelector) {
        const formMatch = html.match(/<form[^>]*>([\s\S]*?)<\/form>/);
        if (!formMatch) return presetSelector + html;
        return html.replace(formMatch[1], presetSelector + formMatch[1]);
    }

    initPresetHandlers(block, presets) {
        const presetSelect = document.getElementById('block-preset-select');
        if (!presetSelect) return;

        presetSelect.addEventListener('change', (e) => {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const template = selectedOption.getAttribute('data-template');
            
            if (e.target.value && template) {
                if (!block.settings) block.settings = {};
                block.settings.preset_id = e.target.value;
                if (template.includes('text-danger')) {
                    const input = document.querySelector('[name="settings[custom_class]"]');
                    if (input && !input.value) input.value = 'text-danger';
                }
            } else {
                if (block.settings) delete block.settings.preset_id;
            }
        });
    }

    reinitializeAllBlocks() {
        if (typeof ListBlockAdmin !== 'undefined' && typeof ListBlockAdmin.reinitializeAll === 'function') {
            ListBlockAdmin.reinitializeAll();
        }
    }

    initTooltips(container) {
        const triggers = container.querySelectorAll('[data-bs-toggle="tooltip"]');
        triggers.forEach(el => {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Tooltip(el, { delay: { show: 300, hide: 100 } });
        });
    }

    initSortable() {
        if (!this.blocksContainer || typeof Sortable === 'undefined') return;

        if (this.sortableInstance) {
            this.sortableInstance.destroy();
        }

        this.sortableInstance = new Sortable(this.blocksContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.drag-handle',
            onEnd: () => {
                this.updateBlocksOrderNumbers();
            }
        });
    }

    updateHiddenField() {
        const field = document.getElementById('post_blocks_data');
        if (field) field.value = JSON.stringify(this.blocksData);
    }

    showNotification(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    closeCurrentModal() {
        if (this.currentModal) {
            const bsModal = bootstrap.Modal.getInstance(this.currentModal);
            if (bsModal) bsModal.hide();
            this.currentModal.remove();
            this.currentModal = null;
            
            setTimeout(() => {
                document.querySelector('.modal-backdrop')?.remove();
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
            }, 300);
        }
    }

    bindEvents() {
        this.blockButtons.addEventListener('click', (e) => {
            const btn = e.target.closest('.add-post-block');
            if (btn) {
                this.addBlock(btn.getAttribute('data-type'));
            }
        });

        const searchInput = document.getElementById('block-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.currentSearch = e.target.value;
                    this.renderBlockButtons();
                }, 300);
            });
        }

        const categoryFilter = document.getElementById('block-category-filter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                this.currentCategory = e.target.value;
                this.renderBlockButtons();
            });
        }

        const clearSearch = document.getElementById('clear-search');
        if (clearSearch) {
            clearSearch.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                this.currentSearch = '';
                this.renderBlockButtons();
            });
        }

        this.blocksContainer.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-post-block');
            if (removeBtn) {
                const blockId = removeBtn.closest('.post-block-item')?.getAttribute('data-block-id');
                if (blockId) this.removeBlock(blockId);
                return;
            }

            const editBtn = e.target.closest('.edit-post-block');
            if (editBtn) {
                const blockId = editBtn.closest('.post-block-item')?.getAttribute('data-block-id');
                if (blockId) this.editBlock(blockId);
                return;
            }
        });
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const blocksContainer = document.getElementById('post-blocks-container');
    const blockButtons = document.getElementById('post-block-buttons');
    
    if (!blocksContainer || !blockButtons) return;
    if (!window.availablePostBlocks || Object.keys(window.availablePostBlocks).length === 0) return;

    window.postBlocksManager = new PostBlocksManager();
});