let editor = null;
let currentTemplate = '';
let currentFile = null;
let allFiles = [];

document.addEventListener('DOMContentLoaded', function() {
    currentTemplate = CURRENT_TEMPLATE || 'default';
    
    editor = ace.edit("codeEditor");
    editor.setTheme("ace/theme/monokai");
    editor.session.setMode("ace/mode/php");
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true,
        enableSnippets: true,
        fontSize: "14px",
        showPrintMargin: false,
        tabSize: 4,
        useSoftTabs: true
    });

    initEventHandlers();
    loadTemplateFiles(currentTemplate);
});

function initEventHandlers() {
    document.querySelectorAll('.template-selector').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const template = this.getAttribute('data-template');
            loadTemplateFiles(template);
            document.querySelectorAll('.template-selector').forEach(el => {
                el.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    const saveBtn = document.getElementById('saveFile');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveFile);
    }
    
    const refreshBtn = document.getElementById('refreshFile');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            if (currentFile) {
                loadFileContent(currentTemplate, currentFile);
            }
        });
    }
    
    const refreshFilesBtn = document.getElementById('refreshFilesBtn');
    if (refreshFilesBtn) {
        refreshFilesBtn.addEventListener('click', function() {
            loadTemplateFiles(currentTemplate);
        });
    }
    
    const searchInput = document.getElementById('searchFiles');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            filterFiles(e.target.value);
        });
        
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
    
    const switchTreeBtn = document.getElementById('switchTree');
    const switchListBtn = document.getElementById('switchList');
    
    if (switchTreeBtn) {
        switchTreeBtn.addEventListener('click', function() {
            switchTreeBtn.classList.add('active');
            if (switchListBtn) switchListBtn.classList.remove('active');
            renderTreeView(allFiles, currentTemplate);
        });
    }
    
    if (switchListBtn) {
        switchListBtn.addEventListener('click', function() {
            switchListBtn.classList.add('active');
            if (switchTreeBtn) switchTreeBtn.classList.remove('active');
            renderFlatFileList(allFiles, currentTemplate);
        });
    }
    
    const uploadBtn = document.getElementById('uploadFileBtn');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            document.getElementById('fileUpload').click();
        });
    }
    
    const fileUpload = document.getElementById('fileUpload');
    if (fileUpload) {
        fileUpload.addEventListener('change', uploadFile);
    }
    
    const collapseAllBtn = document.getElementById('collapseAllFolders');
    const expandAllBtn = document.getElementById('expandAllFolders');
    
    if (collapseAllBtn) {
        collapseAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.tree-folder-content').forEach(content => {
                content.style.display = 'none';
            });
            document.querySelectorAll('.folder-toggle').forEach(toggle => {
                toggle.textContent = '▶';
            });
        });
    }
    
    if (expandAllBtn) {
        expandAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.tree-folder-content').forEach(content => {
                content.style.display = 'block';
            });
            document.querySelectorAll('.folder-toggle').forEach(toggle => {
                toggle.textContent = '▼';
            });
        });
    }
    
    const closeInfoPanel = document.getElementById('closeInfoPanel');
    if (closeInfoPanel) {
        closeInfoPanel.addEventListener('click', function() {
            const infoPanel = document.getElementById('fileInfoPanel');
            if (infoPanel) infoPanel.style.display = 'none';
        });
    }
    
    const downloadBtn = document.getElementById('downloadFileBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            if (currentFile) {
                window.location.href = `${ADMIN_URL}/templates/download-file?template=${currentTemplate}&file=${encodeURIComponent(currentFile)}`;
            }
        });
    }

    if (editor) {
        editor.session.on('change', function() {
            const saveBtn = document.getElementById('saveFile');
            if (saveBtn) saveBtn.disabled = false;
        });
    }
}

function filterFiles(searchTerm) {
    if (!searchTerm.trim()) {
        const switchTree = document.getElementById('switchTree');
        const isTreeView = switchTree && switchTree.classList.contains('active');
        
        if (isTreeView) {
            renderTreeView(allFiles, currentTemplate);
        } else {
            renderFlatFileList(allFiles, currentTemplate);
        }
        return;
    }
    
    const filtered = allFiles.filter(file => 
        file.path.toLowerCase().includes(searchTerm.toLowerCase()) ||
        file.name.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    const switchTree = document.getElementById('switchTree');
    const isTreeView = switchTree && switchTree.classList.contains('active');
    
    if (isTreeView) {
        renderTreeView(filtered, currentTemplate);
    } else {
        renderFlatFileList(filtered, currentTemplate);
    }
}

function loadTemplateFiles(template) {
    const fileList = document.getElementById('fileList');
    const fileCounter = document.getElementById('fileCounter');
    
    if (fileList) {
        fileList.innerHTML = `<div class="loading-state"><div class="spinner"></div><p>${lang === 'ru' ? 'Загрузка файлов...' : 'Loading files...'}</p></div>`;
    }

    fetch(`${ADMIN_URL}/templates/get-files?template=${template}`)
        .then(response => response.json())
        .then(files => {
            allFiles = files;
            
            if (fileCounter) {
                fileCounter.textContent = files.length;
            }
            
            if (files.length === 0) {
                if (fileList) {
                    fileList.innerHTML = `
                        <div class="empty-state">
                            <svg width="48" height="48" style="fill: #9ca3af"><use href="${BASE_URL}/templates/default/admin/icons/bs.svg#folder"></use></svg>
                            <h5>${lang === 'ru' ? 'Папка front не найдена или пуста' : 'front folder not found or empty'}</h5>
                            <p>${lang === 'ru' ? `Создайте папку <strong>front</strong> в шаблоне <strong>${template}</strong>` : `Create a <strong>front</strong> folder in the <strong>${template}</strong> template`}</p>
                            <button class="btn-create-folder" onclick="window.createFrontFolder('${template}')">
                                <svg width="14" height="14" style="fill: currentColor"><use href="${BASE_URL}/templates/default/admin/icons/bs.svg#folder-plus"></use></svg>
                                ${lang === 'ru' ? 'Создать папку front' : 'Create front folder'}
                            </button>
                        </div>
                    `;
                }
                return;
            }
            
            renderTreeView(files, template);
            currentTemplate = template;
        })
        .catch(error => {
            console.error('Error loading files:', error);
            if (fileList) {
                fileList.innerHTML = `<div class="error-state">${lang === 'ru' ? 'Ошибка загрузки файлов' : 'Error loading files'}</div>`;
            }
        });
}

window.createFrontFolder = function(template) {
    fetch(`${ADMIN_URL}/templates/create-front-folder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ template: template })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(lang === 'ru' ? 'Папка front успешно создана' : 'Front folder created successfully', 'success');
            loadTemplateFiles(template);
        } else {
            showToast((lang === 'ru' ? 'Ошибка: ' : 'Error: ') + data.error, 'danger');
        }
    })
    .catch(error => {
        showToast(lang === 'ru' ? 'Ошибка при создании папки' : 'Error creating folder', 'danger');
    });
};

function buildTree(files) {
    const tree = {};
    
    files.forEach(file => {
        const parts = file.path.split('/');
        let current = tree;
        
        for (let i = 0; i < parts.length; i++) {
            const part = parts[i];
            const isLast = i === parts.length - 1;
            
            if (isLast) {
                if (!current._files) current._files = [];
                current._files.push(file);
            } else {
                if (!current[part]) current[part] = {};
                current = current[part];
            }
        }
    });
    
    return tree;
}

function renderTreeView(files, template) {
    const fileList = document.getElementById('fileList');
    
    if (!fileList) return;
    
    if (files.length === 0) {
        fileList.innerHTML = `
            <div class="empty-state">
                <svg width="48" height="48" style="fill: #9ca3af"><use href="${BASE_URL}/templates/default/admin/icons/bs.svg#file-earmark-x"></use></svg>
                <h5>${lang === 'ru' ? 'Файлы не найдены' : 'No files found'}</h5>
                <p>${lang === 'ru' ? 'Нет файлов, соответствующих критериям поиска' : 'No files match the search criteria'}</p>
            </div>
        `;
        return;
    }
    
    const tree = buildTree(files);
    
    function renderNode(node, path = '') {
        let html = '<ul class="tree-view">';
        
        for (const key in node) {
            if (key === '_files') continue;
            
            const fullPath = path ? `${path}/${key}` : key;
            html += `
                <li class="tree-folder">
                    <div class="tree-folder-header" data-path="${fullPath}">
                        <svg width="16" height="16" class="folder-icon"><use href="${BASE_URL}/templates/default/admin/icons/bs.svg#folder"></use></svg>
                        <span class="folder-name">${escapeHtml(key)}</span>
                        <span class="folder-toggle">▼</span>
                    </div>
                    <div class="tree-folder-content">
                        ${renderNode(node[key], fullPath)}
                    </div>
                </li>
            `;
        }
        
        if (node._files) {
            node._files.forEach(file => {
                const isEditable = ['php', 'html', 'css', 'js', 'json', 'xml', 'txt', 'svg'].includes(file.extension);
                const fullFilePath = file.path;
                
                html += `
                    <li class="tree-file">
                        <a href="#" class="file-item-link" 
                        data-file="${escapeHtml(fullFilePath)}" 
                        data-template="${escapeHtml(template)}"
                        data-editable="${isEditable}">
                            <div class="file-item-content">
                                <div class="file-item-info">
                                    <svg width="16" height="16" class="file-icon"><use href="${BASE_URL}/templates/default/admin/icons/bs.svg#file-code"></use></svg>
                                    <div>
                                        <div class="file-name">${escapeHtml(file.name)}</div>
                                        ${file.description ? `<div class="file-description">${escapeHtml(file.description)}</div>` : ''}
                                        <div class="file-path-sm">front/${escapeHtml(file.path)}</div>
                                    </div>
                                </div>
                                <div class="file-meta">
                                    <span class="file-size">${escapeHtml(file.size)}</span>
                                    ${!isEditable ? `<span class="badge-readonly">${lang === 'ru' ? 'только чтение' : 'read-only'}</span>` : ''}
                                </div>
                            </div>
                        </a>
                    </li>
                `;
            });
        }
        
        html += '</ul>';
        return html;
    }
    
    fileList.innerHTML = renderNode(tree);
    
    document.querySelectorAll('.tree-folder-header').forEach(header => {
        header.addEventListener('click', function(e) {
            e.stopPropagation();
            const content = this.nextElementSibling;
            const toggle = this.querySelector('.folder-toggle');
            
            if (content && toggle) {
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    toggle.textContent = '▼';
                } else {
                    content.style.display = 'none';
                    toggle.textContent = '▶';
                }
            }
        });
    });
    
    document.querySelectorAll('.file-item-link').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filePath = this.getAttribute('data-file');
            const templateName = this.getAttribute('data-template');
            
            if (filePath && templateName) {
                loadFileContent(templateName, filePath);
                
                document.querySelectorAll('.file-item-link').forEach(el => {
                    el.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });
}

function renderFlatFileList(files, template) {
    const fileList = document.getElementById('fileList');
    
    if (!fileList) return;
    
    if (files.length === 0) {
        fileList.innerHTML = `
            <div class="empty-state">
                <svg width="48" height="48" style="fill: #9ca3af"><use href="${BASE_URL}/templates/default/admin/icons/bs.svg#file-earmark-x"></use></svg>
                <h5>${lang === 'ru' ? 'Файлы не найдены' : 'No files found'}</h5>
            </div>
        `;
        return;
    }

    let html = '<div class="flat-file-list">';
    files.forEach(file => {
        const isEditable = ['php', 'html', 'css', 'js', 'json', 'xml', 'txt', 'svg'].includes(file.extension);
        const fullFilePath = file.path;
        
        html += `
            <a href="#" class="flat-file-item file-item-link" 
               data-file="${escapeHtml(fullFilePath)}" 
               data-template="${escapeHtml(template)}"
               data-editable="${isEditable}">
                <div class="flat-file-content">
                    <div class="flat-file-info">
                        <svg width="20" height="20" class="file-icon"><use href="${BASE_URL}/templates/default/admin/icons/bs.svg#file-code"></use></svg>
                        <div>
                            <div class="file-name">${escapeHtml(file.name)}</div>
                            ${file.description ? `<div class="file-description">${escapeHtml(file.description)}</div>` : ''}
                            <div class="file-path">front/${escapeHtml(file.path)}</div>
                        </div>
                    </div>
                    <div class="flat-file-meta">
                        <span class="file-size">${escapeHtml(file.size)}</span>
                        ${!isEditable ? `<span class="badge-readonly">${lang === 'ru' ? 'только чтение' : 'read-only'}</span>` : ''}
                    </div>
                </div>
            </a>
        `;
    });
    html += '</div>';
    
    fileList.innerHTML = html;
    
    document.querySelectorAll('.file-item-link').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filePath = this.getAttribute('data-file');
            const templateName = this.getAttribute('data-template');
            
            if (filePath && templateName) {
                loadFileContent(templateName, filePath);
                
                document.querySelectorAll('.file-item-link').forEach(el => {
                    el.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function setEditorMode(extension) {
    const modes = {
        'php': 'php',
        'html': 'html',
        'css': 'css',
        'js': 'javascript',
        'json': 'json',
        'xml': 'xml',
        'txt': 'text',
        'svg': 'xml'
    };
    
    const mode = modes[extension] || 'text';
    editor.session.setMode(`ace/mode/${mode}`);
}

function loadFileContent(template, filePath) {
    currentFile = filePath;
    
    const editorContainer = document.getElementById('editorContainer');
    const editorPlaceholder = document.getElementById('editorPlaceholder');
    const fileInfoPanel = document.getElementById('fileInfoPanel');
    const editorActions = document.getElementById('editorActions');
    const currentFileTitle = document.getElementById('currentFileTitle');
    const saveBtn = document.getElementById('saveFile');
    const refreshBtn = document.getElementById('refreshFile');
    
    if (editorContainer) editorContainer.style.display = 'block';
    if (editorPlaceholder) editorPlaceholder.style.display = 'none';
    if (fileInfoPanel) fileInfoPanel.style.display = 'block';
    if (editorActions) editorActions.style.display = 'flex';
    
    const fileName = filePath.split('/').pop();
    if (currentFileTitle) {
        currentFileTitle.innerHTML = `
            <svg width="14" height="14" style="fill: #0d6efd"><use href="${BASE_URL}/templates/default/admin/icons/bs.svg#file-code"></use></svg>
            <span>${escapeHtml(fileName)}</span>
        `;
    }
    
    if (saveBtn) saveBtn.disabled = true;
    
    const extension = fileName.split('.').pop().toLowerCase();
    const isEditable = ['php', 'html', 'css', 'js', 'json', 'xml', 'txt', 'svg'].includes(extension);
    
    if (isEditable) {
        if (refreshBtn) refreshBtn.style.display = 'flex';
        if (saveBtn) saveBtn.style.display = 'flex';
        
        editor.setValue(lang === 'ru' ? '// Загрузка файла...' : '// Loading file...', -1);
        editor.setReadOnly(false);
        setEditorMode(extension);
        
        const url = `${ADMIN_URL}/templates/get-file?template=${template}&file=${encodeURIComponent(filePath)}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    editor.setValue(data.content, -1);
                    editor.session.getUndoManager().reset();
                    updateFileInfo(data.info, filePath);
                } else {
                    editor.setValue((lang === 'ru' ? '// Ошибка загрузки файла: ' : '// Error loading file: ') + data.error, -1);
                    showToast((lang === 'ru' ? 'Ошибка загрузки файла: ' : 'Error loading file: ') + data.error, 'danger');
                }
            })
            .catch(error => {
                editor.setValue((lang === 'ru' ? '// Ошибка загрузки файла: ' : '// Error loading file: ') + error.message, -1);
                showToast((lang === 'ru' ? 'Ошибка загрузки файла: ' : 'Error loading file: ') + error.message, 'danger');
            });
    } else {
        if (refreshBtn) refreshBtn.style.display = 'none';
        if (saveBtn) saveBtn.style.display = 'none';
        
        editor.setValue(lang === 'ru' 
        ? `// Этот файл (${extension}) не может быть отредактирован в текстовом редакторе\n// Вы можете скачать его` 
        : `// This file (${extension}) cannot be edited in the text editor\n// You can download it`, -1);
        editor.setReadOnly(true);
        
        const url = `${ADMIN_URL}/templates/get-file?template=${template}&file=${encodeURIComponent(filePath)}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateFileInfo(data.info, filePath);
                }
            });
    }
}

function updateFileInfo(info, filePath) {
    const infoFileName = document.getElementById('infoFileName');
    const infoFileSize = document.getElementById('infoFileSize');
    const infoFilePath = document.getElementById('infoFilePath');
    const infoFileDescription = document.getElementById('infoFileDescription');
    const infoFileUpdated = document.getElementById('infoFileUpdated');
    const infoDescRow = document.getElementById('infoDescRow');
    const downloadBtn = document.getElementById('downloadFileBtn');
    
    if (infoFileName) infoFileName.textContent = info.name || '-';
    if (infoFileSize) infoFileSize.textContent = info.size || '-';
    if (infoFilePath) infoFilePath.textContent = filePath || '-';
    
    if (infoFileDescription && infoDescRow) {
        if (info.description) {
            infoFileDescription.textContent = info.description;
            infoDescRow.style.display = 'block';
        } else {
            infoDescRow.style.display = 'none';
        }
    }
    
    if (infoFileUpdated) {
        if (info.updated_at) {
            const date = new Date(info.updated_at * 1000);
            infoFileUpdated.textContent = date.toLocaleString('ru-RU', { 
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit' 
            });
        } else {
            infoFileUpdated.textContent = '—';
        }
    }
    
    if (downloadBtn) {
        downloadBtn.onclick = function() {
            window.location.href = `${ADMIN_URL}/templates/download-file?template=${currentTemplate}&file=${encodeURIComponent(filePath)}`;
        };
    }
}

function saveFile() {
    if (!currentFile || !currentTemplate) return;

    const content = editor.getValue();
    
    fetch(`${ADMIN_URL}/templates/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            template: currentTemplate,
            file: currentFile,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const saveBtn = document.getElementById('saveFile');
            if (saveBtn) saveBtn.disabled = true;
            showToast(data.message || (lang === 'ru' ? 'Файл успешно сохранен' : 'File saved successfully'), 'success');
        } else {
            showToast((lang === 'ru' ? 'Ошибка сохранения: ' : 'Save error: ') + data.error, 'danger');
        }
    })
    .catch(error => {
        showToast(lang === 'ru' ? 'Ошибка сохранения' : 'Error saving', 'danger');
    });
}

function uploadFile(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const uploadPath = prompt(lang === 'ru' ? 'Введите путь для сохранения (относительно папки front):\n\nПример: images, css, js, или оставьте пустым для корня' : 'Enter path to save (relative to front folder):\n\nExample: images, css, js, or leave empty for root', '');
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('template', currentTemplate);
    formData.append('path', uploadPath || '');
    
    showToast(lang === 'ru' ? 'Загрузка файла...' : 'Uploading file...', 'info');
    
    fetch(`${ADMIN_URL}/templates/upload-file`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(lang === 'ru' ? 'Файл успешно загружен' : 'File uploaded successfully', 'success');
            loadTemplateFiles(currentTemplate);
        } else {
            showToast((lang === 'ru' ? 'Ошибка загрузки: ' : 'Upload error: ') + data.error, 'danger');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showToast(lang === 'ru' ? 'Ошибка загрузки' : 'Upload error', 'danger');
    });
    
    event.target.value = '';
}

function showToast(message, type) {
    const toastEl = document.getElementById('toast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        const toastBody = toastEl.querySelector('.toast-body');
        if (toastBody) toastBody.textContent = message;
        toastEl.className = 'toast align-items-center border-0';
        toastEl.classList.add(`bg-${type}`);
        toast.show();
    } else {
        console.log(message);
    }
}