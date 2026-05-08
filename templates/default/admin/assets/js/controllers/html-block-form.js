document.addEventListener('DOMContentLoaded', function() {
    initHtmlBlockForm();
});

function initHtmlBlockForm() {
    initAceEditors();
    initAssetHandlers();
    initAssetSelector();
    initFragmentHandlers();
    initFormSubmit();
    initTooltips();
    focusNameField();
}

function initAceEditors() {
    initCssEditor();
    initJsEditor();
    initHtmlEditor();
}

function initCssEditor() {
    const cssEditorElement = document.getElementById('inline-css-editor');
    if (!cssEditorElement) return;
    
    window.inlineCssEditor = ace.edit("inline-css-editor", {
        theme: "ace/theme/monokai",
        mode: "ace/mode/css",
        showPrintMargin: false,
        fontSize: "14px",
        tabSize: 4,
        useSoftTabs: true,
        wrap: true,
        minLines: 8,
        maxLines: 20
    });
    
    const inlineCssField = document.getElementById('inline_css');
    if (inlineCssField) {
        window.inlineCssEditor.setValue(inlineCssField.value || '', -1);
    }
    
    configureAceEditor(window.inlineCssEditor, false);
}

function initJsEditor() {
    const jsEditorElement = document.getElementById('inline-js-editor');
    if (!jsEditorElement) return;
    
    window.inlineJsEditor = ace.edit("inline-js-editor", {
        theme: "ace/theme/monokai",
        mode: "ace/mode/javascript",
        showPrintMargin: false,
        fontSize: "14px",
        tabSize: 4,
        useSoftTabs: true,
        wrap: true,
        minLines: 8,
        maxLines: 20
    });
    
    const inlineJsField = document.getElementById('inline_js');
    if (inlineJsField) {
        window.inlineJsEditor.setValue(inlineJsField.value || '', -1);
    }
    
    configureAceEditor(window.inlineJsEditor, false);
}

function initHtmlEditor() {
    const htmlEditorElement = document.getElementById('default-block-html-editor');
    if (!htmlEditorElement) return;
    
    window.defaultBlockHtmlEditor = ace.edit("default-block-html-editor", {
        theme: "ace/theme/monokai",
        mode: "ace/mode/html",
        showPrintMargin: false,
        fontSize: "14px",
        tabSize: 4,
        useSoftTabs: true,
        wrap: true,
        minLines: 20,
        maxLines: 40,
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true,
        enableSnippets: true
    });
    
    window.defaultBlockHtmlEditor.session.setUseWrapMode(true);
    
    const htmlTextarea = document.getElementById('default-block-html');
    if (htmlTextarea) {
        window.defaultBlockHtmlEditor.setValue(htmlTextarea.value || '', -1);
        window.defaultBlockHtmlEditor.session.getUndoManager().reset();
    }
    
    configureAceEditor(window.defaultBlockHtmlEditor, true);
    
    window.htmlEditor = window.defaultBlockHtmlEditor;
}

function configureAceEditor(editor, enableCompletions = false) {
    if (!editor) return;
    
    editor.setOptions({
        enableBasicAutocompletion: enableCompletions,
        enableLiveAutocompletion: enableCompletions,
        enableSnippets: enableCompletions,
        behavioursEnabled: true,
        wrapBehavioursEnabled: true
    });
    
    editor.session.setUseWrapMode(true);
    editor.session.setTabSize(4);
    editor.session.setUseSoftTabs(true);
    editor.session.getUndoManager().reset();
}

function initFragmentHandlers() {
    const useFragmentCheckbox = document.getElementById('use_fragment');
    const fragmentSelector = document.getElementById('fragment-selector');
    const fragmentSelect = document.getElementById('selected_fragment');
    const fragmentShortcodes = document.getElementById('fragment-shortcodes');
    const shortcodesList = document.getElementById('shortcodes-list');
    
    if (!useFragmentCheckbox) return;
    
    let currentShortcode = '';
    
    function loadFragments() {
        const adminUrl = window.ADMIN_URL || '/admin';
        fetch(`${adminUrl}/html-blocks/get-fragments`)
            .then(response => response.json())
            .then(data => {
                if (data.success && fragmentSelect) {
                    const selectedValue = fragmentSelect.getAttribute('data-selected') || '';
                    fragmentSelect.innerHTML = '<option value="">' + (lang === 'ru' ? '-- Выберите фрагмент --' : '-- Select fragment --') + '</option>';
                    
                    data.fragments.forEach(fragment => {
                        const option = document.createElement('option');
                        option.value = fragment.system_name;
                        option.textContent = `${fragment.name} (${fragment.fields.length} полей)`;
                        if (option.value === selectedValue) {
                            option.selected = true;
                        }
                        fragmentSelect.appendChild(option);
                    });
                    
                    if (selectedValue) {
                        updateShortcodeDisplay(selectedValue);
                        loadFragmentShortcodes(selectedValue);
                        if (fragmentShortcodes) fragmentShortcodes.style.display = 'block';
                    }
                }
            })
            .catch(error => console.error('Error loading fragments:', error));
    }
    
    function updateShortcodeDisplay(systemName) {
        currentShortcode = `{${systemName}}`;
    }
    
    function loadFragmentShortcodes(systemName) {
        const adminUrl = window.ADMIN_URL || '/admin';
        fetch(`${adminUrl}/html-blocks/get-fragments`)
            .then(response => response.json())
            .then(data => {
                if (data.success && shortcodesList) {
                    const fragment = data.fragments.find(f => f.system_name === systemName);
                    if (fragment) {
                        const badge = document.getElementById('shortcode-badge');
                        if (badge) {
                            const totalShortcodes = 2 + (fragment.fields.length * 2);
                            badge.textContent = totalShortcodes;
                        }
                        
                        let html = `
                            <div class="mb-3">
                                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                    <span class="badge bg-primary px-3 py-2">${lang === 'ru' ? 'Базовые' : 'Basic'}</span>
                        `;
                        
                        const baseShortcodes = [
                            { code: fragment.shortcode_simple, desc: (lang === 'ru' ? 'Простой вывод всех записей' : 'Simple output of all posts') },
                            { code: fragment.shortcode_loop, desc: (lang === 'ru' ? 'Кастомный цикл по записям' : 'Custom posts loop') }
                        ];
                        
                        baseShortcodes.forEach(item => {
                            html += `
                                <div class="shortcode-item d-inline-flex align-items-center bg-white rounded border px-2 py-1">
                                    <code class="small me-1">${escapeHtml(item.code)}</code>
                                    <div class="btn-group btn-group-sm ms-1">
                                        <button class="btn btn-sm btn-link text-secondary p-0 copy-shortcode" data-shortcode="${escapeHtml(item.code)}" title="${lang === 'ru' ? 'Копировать' : 'Copy'}">
                                            <svg class="icon" width="12" height="12"><use href="/templates/default/admin/icons/bs.svg#copy"/></svg>
                                        </button>
                                        <button class="btn btn-sm btn-link text-primary p-0 ms-1 insert-shortcode" data-shortcode="${escapeHtml(item.code)}" title="${lang === 'ru' ? 'Вставить' : 'Insert'}">
                                            <svg class="icon" width="12" height="12"><use href="/templates/default/admin/icons/bs.svg#plus"/></svg>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += `</div>`;
                        
                        if (fragment.fields.length > 0) {
                            html += `
                                <div class="mt-3">
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                        <span class="badge bg-success px-3 py-2">${lang === 'ru' ? 'Поля фрагмента' : 'Fragment fields'}</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                            `;
                            
                            fragment.fields.forEach(field => {
                                html += `
                                    <div class="field-shortcodes-group d-inline-flex flex-column bg-white rounded border p-2" style="min-width: 180px;">
                                        <div class="small fw-semibold text-dark mb-1">${escapeHtml(field.name)}</div>
                                        <div class="d-flex gap-2">
                                            <div class="shortcode-item d-inline-flex align-items-center bg-light rounded px-2 py-1">
                                                <code class="small me-1" style="font-size: 0.7rem;">${escapeHtml(field.shortcode)}</code>
                                                <div class="btn-group btn-group-sm ms-1">
                                                    <button class="btn btn-sm btn-link text-secondary p-0 copy-shortcode" data-shortcode="${escapeHtml(field.shortcode)}" title="${lang === 'ru' ? 'Копировать' : 'Copy'}">
                                                        <svg class="icon" width="10" height="10"><use href="/templates/default/admin/icons/bs.svg#copy"/></svg>
                                                    </button>
                                                    <button class="btn btn-sm btn-link text-primary p-0 ms-1 insert-shortcode" data-shortcode="${escapeHtml(field.shortcode)}" title="${lang === 'ru' ? 'Вставить' : 'Insert'}">
                                                        <svg class="icon" width="10" height="10"><use href="/templates/default/admin/icons/bs.svg#plus"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="shortcode-item d-inline-flex align-items-center bg-light rounded px-2 py-1">
                                                <code class="small me-1" style="font-size: 0.7rem;">${escapeHtml(field.display_shortcode)}</code>
                                                <div class="btn-group btn-group-sm ms-1">
                                                    <button class="btn btn-sm btn-link text-secondary p-0 copy-shortcode" data-shortcode="${escapeHtml(field.display_shortcode)}" title="${lang === 'ru' ? 'Копировать' : 'Copy'}">
                                                        <svg class="icon" width="10" height="10"><use href="/templates/default/admin/icons/bs.svg#copy"/></svg>
                                                    </button>
                                                    <button class="btn btn-sm btn-link text-primary p-0 ms-1 insert-shortcode" data-shortcode="${escapeHtml(field.display_shortcode)}" title="${lang === 'ru' ? 'Вставить' : 'Insert'}">
                                                        <svg class="icon" width="10" height="10"><use href="/templates/default/admin/icons/bs.svg#plus"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-1" style="font-size: 0.65rem;">
                                            ${field.type === 'image' ? (lang === 'ru' ? 'Изображение' : 'Image') : (lang === 'ru' ? 'Текст' : 'Text')}
                                        </div>
                                    </div>
                                `;
                            });
                            
                            html += `</div></div>`;
                        }
                        
                        shortcodesList.innerHTML = html;
                        
                        document.querySelectorAll('.copy-shortcode').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                const shortcode = this.getAttribute('data-shortcode');
                                copyToClipboard(shortcode, this);
                                return false;
                            });
                        });
                        
                        document.querySelectorAll('.insert-shortcode').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                const shortcode = this.getAttribute('data-shortcode');
                                insertIntoEditor(shortcode);
                                return false;
                            });
                        });
                    }
                }
            })
            .catch(error => console.error('Error loading fragment shortcodes:', error));
    }
    
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = button.innerHTML;
            button.innerHTML = '<svg class="icon icon-check" width="14" height="14"><use href="/templates/default/admin/icons/bs.svg#check"/></svg>';
            setTimeout(() => {
                button.innerHTML = originalHtml;
            }, 1500);
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
    
    function insertIntoEditor(shortcode) {
        if (window.htmlEditor) {
            window.htmlEditor.insert(shortcode);
        } else if (window.defaultBlockHtmlEditor) {
            window.defaultBlockHtmlEditor.insert(shortcode);
        } else {
            console.warn('Editor not available');
        }
    }
    
    useFragmentCheckbox.addEventListener('change', function() {
        if (fragmentSelector) {
            fragmentSelector.style.display = this.checked ? 'block' : 'none';
        }
        if (!this.checked && fragmentShortcodes) {
            fragmentShortcodes.style.display = 'none';
        }
    });
    
    if (fragmentSelect) {
        fragmentSelect.addEventListener('change', function() {
            if (this.value) {
                updateShortcodeDisplay(this.value);
                loadFragmentShortcodes(this.value);
                if (fragmentShortcodes) fragmentShortcodes.style.display = 'block';
            } else {
                if (fragmentShortcodes) fragmentShortcodes.style.display = 'none';
                if (shortcodesList) {
                    shortcodesList.innerHTML = `
                        <div class="text-center text-muted py-3">
                            <svg class="icon" width="16" height="16"><use href="/templates/default/admin/icons/bs.svg#arrow-left"/></svg>
                            ${lang === 'ru' ? 'Выберите фрагмент' : 'Select fragment'}
                        </div>
                    `;
                }
                currentShortcode = '';
            }
        });
    }
    
    loadFragments();
}

function initAssetHandlers() {
    document.getElementById('add-css-file')?.addEventListener('click', () => addAssetRow('css'));
    document.getElementById('add-js-file')?.addEventListener('click', () => addAssetRow('js'));
    attachRemoveHandlers();
}

function addAssetRow(type) {
    const container = document.getElementById(`${type}-files-container`);
    if (!container) return;
    
    const newRow = document.createElement('div');
    newRow.className = `input-group mb-2 ${type}-file-row`;
    newRow.innerHTML = `
        <input type="text" name="${type}_files[]" class="form-control asset-path-input" value="" placeholder="templates/default/front/assets/${type}/my-block.${type}">
        <button type="button" class="btn btn-outline-primary select-asset-btn" 
                data-asset-type="${type}"
                data-bs-toggle="tooltip"
                title="${lang === 'ru' ? 'Выбрать из папки блока' : 'Select from block folder'}">
            <svg class="icon icon-folder" width="16" height="16">
                <use href="/templates/default/admin/icons/bs.svg#folder2-open"></use>
            </svg>
        </button>
        <button type="button" class="btn btn-outline-danger remove-asset" data-type="${type}">
            <svg class="icon icon-trash" width="16" height="16">
                <use href="/templates/default/admin/icons/bs.svg#trash"></use>
            </svg>
        </button>
    `;
    container.appendChild(newRow);
    
    const selectBtn = newRow.querySelector('.select-asset-btn');
    if (selectBtn) {
        selectBtn.addEventListener('click', handleAssetSelectClick);
    }
    
    attachRemoveHandlers();
    initTooltips();
}

function attachRemoveHandlers() {
    document.querySelectorAll('.remove-asset').forEach(button => {
        button.removeEventListener('click', handleRemoveAsset);
        button.addEventListener('click', handleRemoveAsset);
    });
}

function handleRemoveAsset(e) {
    const button = e.currentTarget;
    const type = button.getAttribute('data-type');
    const row = button.closest(`.${type}-file-row`);
    const container = document.getElementById(`${type}-files-container`);
    
    if (!row || !container) return;
    
    if (container.querySelectorAll(`.${type}-file-row`).length > 1) {
        row.remove();
    } else {
        const input = row.querySelector('input');
        if (input) input.value = '';
    }
}

let assetSelectorModal = null;
let currentAssetInput = null;
let currentAssetType = 'css';

function initAssetSelector() {
    const modalElement = document.getElementById('assetSelectorModal');
    if (modalElement) {
        assetSelectorModal = new bootstrap.Modal(modalElement);
    }
    
    document.querySelectorAll('.select-asset-btn').forEach(btn => {
        btn.addEventListener('click', handleAssetSelectClick);
    });
    
    const manualPathBtn = document.getElementById('asset-manual-path');
    if (manualPathBtn) {
        manualPathBtn.addEventListener('click', () => {
            if (assetSelectorModal) {
                assetSelectorModal.hide();
            }
            if (currentAssetInput) {
                currentAssetInput.focus();
            }
        });
    }
    
    const filesList = document.getElementById('asset-files-list');
    if (filesList) {
        filesList.addEventListener('click', handleFileSelect);
    }
}

function handleAssetSelectClick(e) {
    const btn = e.currentTarget;
    currentAssetType = btn.getAttribute('data-asset-type');
    currentAssetInput = btn.closest('.input-group').querySelector('.asset-path-input');
    const blockType = document.querySelector('input[name="block_type"]')?.value || 'DefaultBlock';
    const typeDisplay = document.getElementById('asset-type-display');
    const blockTypeDisplay = document.getElementById('asset-block-type');
    const modalTitle = document.getElementById('asset-modal-title');
    
    if (typeDisplay) typeDisplay.value = currentAssetType.toUpperCase();
    if (blockTypeDisplay) blockTypeDisplay.value = blockType;
    if (modalTitle) {
        modalTitle.textContent = lang === 'ru' ? `Выберите ${currentAssetType.toUpperCase()} файл для блока ${blockType}` : `Select ${currentAssetType.toUpperCase()} file for block ${blockType}`;
    }
    
    if (assetSelectorModal) {
        assetSelectorModal.show();
    }
    
    loadAssetFiles(blockType, currentAssetType);
}

async function loadAssetFiles(blockType, assetType) {
    const filesList = document.getElementById('asset-files-list');
    const noFilesAlert = document.getElementById('asset-no-files');
    
    if (!filesList) return;
    
    filesList.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">${lang === 'ru' ? 'Загрузка файлов...' : 'Loading files...'}</p>
        </div>
    `;
    
    if (noFilesAlert) {
        noFilesAlert.style.display = 'none';
    }
    
    try {
        const adminUrl = window.ADMIN_URL || '/admin';
        const response = await fetch(`${adminUrl}/html-blocks/get-block-assets?block_type=${encodeURIComponent(blockType)}&asset_type=${assetType}`);
        const data = await response.json();
        
        if (data.success && data.files && data.files.length > 0) {
            if (noFilesAlert) {
                noFilesAlert.style.display = 'none';
            }
            filesList.innerHTML = '';
            
            data.files.forEach(file => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                item.setAttribute('data-file-path', file.path);
                item.innerHTML = `
                    <div>
                        <svg class="icon icon-file me-2" width="16" height="16">
                            <use href="/templates/default/admin/icons/bs.svg#filetype-${assetType === 'css' ? 'scss' : 'code'}"></use>
                        </svg>
                        <strong>${escapeHtml(file.name)}</strong>
                        <div class="small text-muted">${escapeHtml(file.path)}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-secondary">${formatFileSize(file.size)}</span>
                        <div class="small text-muted mt-1">${file.modified}</div>
                    </div>
                `;
                filesList.appendChild(item);
            });
        } else {
            filesList.innerHTML = '';
            if (noFilesAlert) {
                noFilesAlert.style.display = 'block';
                noFilesAlert.innerHTML = `
                    <svg class="icon icon-warning me-2" width="16" height="16">
                        <use href="/templates/default/admin/icons/bs.svg#exclamation-triangle"></use>
                    </svg>
                    ${data.message || (lang === 'ru' ? 'Файлы не найдены. Вы можете указать путь вручную.' : 'Files not found. You can specify the path manually.')}
                `;
            }
        }
    } catch (error) {
        console.error('Error loading assets:', error);
        filesList.innerHTML = `
            <div class="alert alert-danger">
                <svg class="icon icon-error me-2" width="16" height="16">
                    <use href="/templates/default/admin/icons/bs.svg#exclamation-circle"></use>
                </svg>
                ${lang === 'ru' ? 'Ошибка загрузки:' : 'Error loading:'} ${escapeHtml(error.message)}
            </div>
        `;
    }
}

function handleFileSelect(e) {
    const item = e.target.closest('[data-file-path]');
    if (!item) return;
    
    const filePath = item.getAttribute('data-file-path');
    
    if (currentAssetInput) {
        currentAssetInput.value = filePath;
        currentAssetInput.classList.add('border-success');
        setTimeout(() => {
            currentAssetInput.classList.remove('border-success');
        }, 2000);
    }
    
    if (assetSelectorModal) {
        assetSelectorModal.hide();
    }
}

function initFormSubmit() {
    const form = document.getElementById("blockForm");
    if (!form) return;
    
    form.addEventListener("submit", function(e) {
        saveEditorValues();
    });
}

function saveEditorValues() {
    if (window.inlineCssEditor) {
        const inlineCssField = document.getElementById('inline_css');
        if (inlineCssField) {
            inlineCssField.value = window.inlineCssEditor.getValue();
        }
    }
    
    if (window.inlineJsEditor) {
        const inlineJsField = document.getElementById('inline_js');
        if (inlineJsField) {
            inlineJsField.value = window.inlineJsEditor.getValue();
        }
    }
    
    if (window.defaultBlockHtmlEditor) {
        const defaultBlockHtmlField = document.getElementById('default-block-html');
        if (defaultBlockHtmlField) {
            defaultBlockHtmlField.value = window.defaultBlockHtmlEditor.getValue();
        }
    }
}

function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (existingTooltip) {
            existingTooltip.dispose();
        }
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function focusNameField() {
    document.querySelector('input[name="name"]')?.focus();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.HtmlBlockForm = {
    init: initHtmlBlockForm,
    addAssetRow: addAssetRow,
    loadAssetFiles: loadAssetFiles,
    insertIntoEditor: function(shortcode) {
        if (window.htmlEditor) {
            window.htmlEditor.insert(shortcode);
        }
    }
};