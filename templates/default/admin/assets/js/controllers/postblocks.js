(function() {
    'use strict';
    let presetEditorInstance = null;
    let blockTemplateEditor = null;
    let adminUrl = '';
    let systemName = '';
    
    function init() {
        const dataElement = document.getElementById('postblock-data');
        if (dataElement) {
            systemName = dataElement.getAttribute('data-system-name') || '';
            adminUrl = dataElement.getAttribute('data-admin-url') || '';
        }
        
        initEditors();
        initEventHandlers();
        loadPresets();
    }
    
    function initEditors() {
        if (typeof ace !== 'undefined') {
            ace.config.set('workerPath', null);
            ace.config.set('modePath', null);
            ace.config.set('themePath', null);
        }
        
        const templateEditorEl = document.getElementById('template-editor');
        if (templateEditorEl) {
            try {
                blockTemplateEditor = ace.edit("template-editor", {
                    theme: "ace/theme/monokai",
                    mode: "ace/mode/html",
                    showPrintMargin: false,
                    fontSize: "14px",
                    tabSize: 4,
                    useSoftTabs: true,
                    wrap: true,
                    minLines: 10,
                    maxLines: 50
                });
                
                const initialTemplate = document.getElementById('template').value;
                blockTemplateEditor.setValue(initialTemplate || '', -1);
                blockTemplateEditor.session.getUndoManager().reset();
                blockTemplateEditor.session.setUseWrapMode(true);
                blockTemplateEditor.session.setTabSize(4);
                blockTemplateEditor.session.setUseSoftTabs(true);
                
                const form = document.getElementById("blockSettingsForm");
                const templateField = document.getElementById("template");
                if (form && templateField) {
                    form.addEventListener("submit", function(e) {
                        templateField.value = blockTemplateEditor.getValue();
                    });
                }
            } catch (error) {}
        }
        
        const presetEditorEl = document.getElementById('preset-template-editor');
        if (presetEditorEl) {
            try {
                presetEditorInstance = ace.edit("preset-template-editor", {
                    theme: "ace/theme/monokai",
                    mode: "ace/mode/html",
                    showPrintMargin: false,
                    fontSize: "14px",
                    tabSize: 4,
                    useSoftTabs: true,
                    wrap: true,
                    minLines: 8,
                    maxLines: 30
                });
                
                presetEditorInstance.setValue('', -1);
                presetEditorInstance.session.getUndoManager().reset();
                presetEditorInstance.session.setUseWrapMode(true);
                presetEditorInstance.session.setTabSize(4);
                presetEditorInstance.session.setUseSoftTabs(true);
            } catch (error) {}
        }
    }
    
    function initEventHandlers() {
        const addPresetBtn = document.getElementById('add-preset-btn');
        if (addPresetBtn) {
            addPresetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openPresetModal();
            });
        }
        
        const savePresetBtn = document.getElementById('save-preset-btn');
        if (savePresetBtn) {
            savePresetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                savePreset();
            });
        }
        
        const deletePresetBtn = document.getElementById('delete-preset-btn');
        if (deletePresetBtn) {
            deletePresetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm(lang === 'ru' ? 'Вы уверены, что хотите удалить этот пресет?' : 'Are you sure you want to delete this preset?')) {
                    deletePreset();
                }
            });
        }
        
        const loadTemplateBtn = document.getElementById('load-template');
        if (loadTemplateBtn) {
            loadTemplateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                loadDefaultTemplate();
            });
        }
        
        document.querySelectorAll('.shortcode-insert').forEach(function(element) {
            element.addEventListener('click', function() {
                const shortcode = this.getAttribute('data-shortcode') || this.textContent;
                if (blockTemplateEditor && shortcode) {
                    blockTemplateEditor.insert(shortcode);
                    blockTemplateEditor.focus();
                }
            });
        });
        
        const presetModal = document.getElementById('presetModal');
        if (presetModal) {
            presetModal.addEventListener('hidden.bs.modal', function() {
                resetPresetForm();
            });
        }
        
        window.addEventListener('resize', function() {
            if (blockTemplateEditor) {
                try { blockTemplateEditor.resize(); } catch (e) {}
            }
            if (presetEditorInstance) {
                try { presetEditorInstance.resize(); } catch (e) {}
            }
        });
    }
    
    function loadPresets() {
        if (!adminUrl || !systemName) {
            return;
        }
        
        fetch(adminUrl + '/post-blocks/get-presets?system_name=' + encodeURIComponent(systemName))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderPresets(data.presets);
                } else {
                    if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                        window.notificationSystem.showNotification(data.message || (lang === 'ru' ? 'Ошибка при загрузке пресетов' : 'Error loading presets'), 'danger');
                    }
                }
            })
            .catch(error => {
                if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                    window.notificationSystem.showNotification(lang === 'ru' ? 'Ошибка при загрузке пресетов' : 'Error loading presets', 'danger');
                }
            });
    }
    
    function renderPresets(presets) {
        const container = document.getElementById('presets-container');
        const noPresetsMessage = document.getElementById('no-presets-message');
        
        if (!container) return;
        
        if (!presets || presets.length === 0) {
            if (noPresetsMessage) {
                noPresetsMessage.style.display = 'block';
            }
            container.innerHTML = '';
            return;
        }
        
        if (noPresetsMessage) {
            noPresetsMessage.style.display = 'none';
        }
        
        let html = '<div class="row g-3">';
        
        presets.forEach(preset => {
            const preview = preset.preset_template ? 
                preset.preset_template.substring(0, 100) + (preset.preset_template.length > 100 ? '...' : '') : '';
            const date = preset.updated_at ? new Date(preset.updated_at).toLocaleDateString() : '';
            
            const templateForData = encodeURIComponent(preset.preset_template || '');
            
            html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${escapeHtml(preset.preset_name)}</h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item edit-preset" href="#" data-id="${preset.id}">
                                        <i class="bi bi-pencil me-2"></i>${lang === 'ru' ? 'Редактировать' : 'Edit'}
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item delete-preset text-danger" href="#" data-id="${preset.id}">
                                        <i class="bi bi-trash me-2"></i>${lang === 'ru' ? 'Удалить' : 'Delete'}
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        <p class="card-text small text-muted mb-2" style="font-family: monospace; font-size: 12px;">
                            ${escapeHtml(preview)}
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">${lang === 'ru' ? 'Обновлен: ' : 'Updated: '}${date}</small>
                            <button type="button" class="btn btn-sm btn-outline-primary use-preset" 
                                    data-id="${preset.id}" 
                                    data-template-encoded="${templateForData}">
                                ${lang === 'ru' ? 'Использовать' : 'Use'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        
        html += '</div>';
        container.innerHTML = html;
        
        container.querySelectorAll('.edit-preset').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const presetId = this.getAttribute('data-id');
                editPreset(presetId);
            });
        });
        
        container.querySelectorAll('.delete-preset').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const presetId = this.getAttribute('data-id');
                if (confirm(lang === 'ru' ? 'Вы уверены, что хотите удалить этот пресет?' : 'Are you sure you want to delete this preset?')) {
                    deletePreset(presetId);
                }
            });
        });
        
        container.querySelectorAll('.use-preset').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const encodedTemplate = this.getAttribute('data-template-encoded');
                if (blockTemplateEditor && encodedTemplate) {
                    const template = decodeURIComponent(encodedTemplate);
                    blockTemplateEditor.setValue(template, -1);
                    
                    if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                        window.notificationSystem.showNotification(lang === 'ru' ? 'Шаблон блока обновлен из пресета' : 'Block template updated from preset', 'success');
                    }
                }
            });
        });
    }
    
    function openPresetModal(presetId = null) {
        const modalElement = document.getElementById('presetModal');
        if (!modalElement) {
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        const deleteBtn = document.getElementById('delete-preset-btn');
        
        if (presetId) {
            document.getElementById('presetModalLabel').textContent = lang === 'ru' ? 'Редактирование пресета' : 'Edit preset';
            document.getElementById('preset_id').value = presetId;
            if (deleteBtn) deleteBtn.style.display = 'inline-block';
            
            fetch(adminUrl + '/post-blocks/get-presets?system_name=' + encodeURIComponent(systemName))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const preset = data.presets.find(p => p.id == presetId);
                        if (preset && presetEditorInstance) {
                            document.getElementById('preset_name').value = preset.preset_name;
                            presetEditorInstance.setValue(preset.preset_template || '', -1);
                        }
                    } else {
                        if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                            window.notificationSystem.showNotification(data.message || (lang === 'ru' ? 'Ошибка при загрузке пресета' : 'Error loading preset'), 'danger');
                        }
                    }
                })
                .catch(error => {
                    if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                        window.notificationSystem.showNotification(lang === 'ru' ? 'Ошибка при загрузке пресета' : 'Error loading preset', 'danger');
                    }
                });
        } else {
            document.getElementById('presetModalLabel').textContent = lang === 'ru' ? 'Создание пресета' : 'Create preset';
            document.getElementById('preset_id').value = '';
            document.getElementById('preset_name').value = '';
            if (presetEditorInstance) presetEditorInstance.setValue('', -1);
            if (deleteBtn) deleteBtn.style.display = 'none';
        }
        
        modal.show();
        if (presetEditorInstance) {
            setTimeout(() => presetEditorInstance.focus(), 100);
        }
    }
    
    function savePreset() {
        const form = document.getElementById('presetForm');
        if (!form || !adminUrl) {
            return;
        }
        
        const formData = new FormData(form);
        formData.append('preset_template', presetEditorInstance ? presetEditorInstance.getValue() : '');
        
        const presetId = document.getElementById('preset_id').value;
        const url = presetId ? 
            adminUrl + '/post-blocks/update-preset' : 
            adminUrl + '/post-blocks/create-preset';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                    window.notificationSystem.showNotification(data.message, 'success');
                }
                
                loadPresets();
                const modal = bootstrap.Modal.getInstance(document.getElementById('presetModal'));
                if (modal) {
                    modal.hide();
                }
                resetPresetForm();
            } else {
                if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                    window.notificationSystem.showNotification(data.message || (lang === 'ru' ? 'Ошибка при сохранении пресета' : 'Error saving preset'), 'danger');
                }
            }
        })
        .catch(error => {
            if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                window.notificationSystem.showNotification(lang === 'ru' ? 'Ошибка при сохранении пресета' : 'Error saving preset', 'danger');
            }
        });
    }
    
    function deletePreset(presetId = null) {
        if (!presetId) {
            presetId = document.getElementById('preset_id').value;
        }
        
        if (!presetId || !adminUrl) return;
        
        const formData = new FormData();
        formData.append('preset_id', presetId);
        
        fetch(adminUrl + '/post-blocks/delete-preset', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                    window.notificationSystem.showNotification(data.message || 'Пресет успешно удален', 'success');
                }
                
                loadPresets();
                const modal = bootstrap.Modal.getInstance(document.getElementById('presetModal'));
                if (modal) {
                    modal.hide();
                }
            } else {
                if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                    window.notificationSystem.showNotification(data.message || (lang === 'ru' ? 'Ошибка при удалении пресета' : 'Error deleting preset'), 'danger');
                }
            }
        })
        .catch(error => {
            if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                window.notificationSystem.showNotification(lang === 'ru' ? 'Ошибка при удалении пресета' : 'Error deleting preset', 'danger');
            }
        });
    }
    
    function editPreset(presetId) {
        openPresetModal(presetId);
    }
    
    function resetPresetForm() {
        const form = document.getElementById('presetForm');
        if (form) form.reset();
        const presetIdField = document.getElementById('preset_id');
        if (presetIdField) presetIdField.value = '';
        if (presetEditorInstance) presetEditorInstance.setValue('', -1);
    }
    
    function loadDefaultTemplate() {
        if (!adminUrl || !systemName || !blockTemplateEditor) return;
        
        const xhr = new XMLHttpRequest();
        xhr.open('GET', adminUrl + '/post-blocks/get-template?system_name=' + encodeURIComponent(systemName));
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success && response.template) {
                    if (confirm(lang === 'ru' ? 'Загрузить стандартный шаблон? Текущий шаблон будет заменен.' : 'Load default template? The current template will be replaced.')) {
                        blockTemplateEditor.setValue(response.template, -1);
                        
                        if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                            window.notificationSystem.showNotification(lang === 'ru' ? 'Стандартный шаблон загружен' : 'Default template loaded', 'success');
                        }
                    }
                } else {
                    if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                        window.notificationSystem.showNotification(response.message || (lang === 'ru' ? 'Не удалось загрузить шаблон' : 'Failed to load template'), 'danger');
                    }
                }
            } else {
                if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                    window.notificationSystem.showNotification(lang === 'ru' ? 'Ошибка при загрузке шаблона' : 'Error loading template', 'danger');
                }
            }
        };
        xhr.onerror = function() {
            if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
                window.notificationSystem.showNotification(lang === 'ru' ? 'Ошибка при загрузке шаблона' : 'Error loading template', 'danger');
            }
        };
        xhr.send();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    document.addEventListener('DOMContentLoaded', init);
    
    window.PostBlocksEditor = {
        init: init,
        loadPresets: loadPresets
    };
    
})();