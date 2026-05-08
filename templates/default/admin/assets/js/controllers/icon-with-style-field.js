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
    
    class IconWithStyleField {
        constructor(fieldWrapper) {
            this.fieldWrapper = fieldWrapper;
            this.pickerId = fieldWrapper.dataset.pickerId;
            this.previewId = fieldWrapper.dataset.previewId;
            this.inputId = this.pickerId + '-input';
            
            this.hiddenInput = document.getElementById(this.inputId);
            this.previewBox = document.getElementById(this.previewId);
            
            this.currentSet = fieldWrapper.dataset.currentSet || '';
            this.currentName = fieldWrapper.dataset.currentName || '';
            this.currentColor = fieldWrapper.dataset.currentColor || '#000000';
            this.currentSize = parseInt(fieldWrapper.dataset.currentSize) || 24;
            
            if (this.hiddenInput && this.hiddenInput.value) {
                try {
                    const savedValue = JSON.parse(this.hiddenInput.value);
                    if (savedValue.set && savedValue.name) {
                        this.currentSet = savedValue.set;
                        this.currentName = savedValue.name;
                        this.currentColor = savedValue.color || this.currentColor;
                        this.currentSize = savedValue.size || this.currentSize;
                        fieldWrapper.dataset.currentSet = this.currentSet;
                        fieldWrapper.dataset.currentName = this.currentName;
                        fieldWrapper.dataset.currentColor = this.currentColor;
                        fieldWrapper.dataset.currentSize = this.currentSize;
                    }
                } catch(e) {
                    console.error('Error parsing saved icon value:', e);
                }
            }
            
            this.sizeMin = parseInt(fieldWrapper.dataset.sizeMin) || 8;
            this.sizeMax = parseInt(fieldWrapper.dataset.sizeMax) || 128;
            this.sizeStep = parseInt(fieldWrapper.dataset.sizeStep) || 2;
            
            this.allowColor = fieldWrapper.dataset.allowColor === '1';
            this.allowSize = fieldWrapper.dataset.allowSize === '1';
            this.hasIcons = fieldWrapper.dataset.hasIcons === '1';
            
            try {
                this.iconsData = JSON.parse(fieldWrapper.dataset.iconsData);
            } catch(e) {
                this.iconsData = {};
                this.hasIcons = false;
            }
            
            this.selectBtn = fieldWrapper.querySelector('.select-icon-btn');
            this.clearBtn = fieldWrapper.querySelector('.clear-icon-btn');
            this.colorInput = fieldWrapper.querySelector('.icon-color-input');
            this.colorText = fieldWrapper.querySelector('.icon-color-text');
            this.sizeSlider = fieldWrapper.querySelector('.icon-size-slider');
            this.sizeNumber = fieldWrapper.querySelector('.icon-size-number');
            this.sizeValueSpan = fieldWrapper.querySelector('.icon-size-value');
            
            this.init();
        }
        
        init() {
            if (this.colorInput && this.colorText) {
                const updateColor = (color) => {
                    if (this.colorInput) this.colorInput.value = color;
                    if (this.colorText) this.colorText.value = color;
                    this.updateIconProperty('color', color);
                };
                this.colorInput.addEventListener('input', (e) => updateColor(e.target.value));
                this.colorText.addEventListener('input', (e) => updateColor(e.target.value));
            }
            
            if (this.sizeSlider && this.sizeNumber) {
                const updateSize = (size) => {
                    size = parseInt(size);
                    if (this.sizeSlider) this.sizeSlider.value = size;
                    if (this.sizeNumber) this.sizeNumber.value = size;
                    if (this.sizeValueSpan) this.sizeValueSpan.textContent = size;
                    this.updateIconProperty('size', size);
                };
                this.sizeSlider.addEventListener('input', (e) => updateSize(e.target.value));
                this.sizeNumber.addEventListener('change', (e) => updateSize(e.target.value));
            }
            
            if (this.selectBtn) {
                this.selectBtn.addEventListener('click', () => this.openIconSelector());
            }
            
            if (this.clearBtn) {
                this.clearBtn.addEventListener('click', () => this.clearIcon());
            }

            if (this.currentSet && this.currentName) {
                this.updatePreview(this.currentSet, this.currentName, this.currentColor, this.currentSize);
            }
        }
        
        updateIconProperty(property, value) {
            if (!this.hiddenInput) return;
            let currentValue = {};
            try { 
                currentValue = JSON.parse(this.hiddenInput.value); 
            } catch(e) { 
                currentValue = {}; 
            }
            currentValue[property] = value;
            this.hiddenInput.value = JSON.stringify(currentValue);
            
            const set = currentValue.set || this.currentSet;
            const name = currentValue.name || this.currentName;
            const color = currentValue.color || this.currentColor;
            const size = currentValue.size || this.currentSize;
            if (set && name) {
                this.updatePreview(set, name, color, size);
            }
        }
        
        updatePreview(set, name, color, size) {
            if (!this.previewBox) return;
            const svgHtml = bloggy_icon(set, name, size + ' ' + size, color, '');
            this.previewBox.innerHTML = svgHtml;
            this.currentSet = set;
            this.currentName = name;
            this.currentColor = color;
            this.currentSize = size;
            if (this.clearBtn) {
                this.clearBtn.style.display = 'block';
            }
        }
        
        clearIcon() {
            if (!this.hiddenInput) return;
            this.hiddenInput.value = JSON.stringify({});
            if (this.previewBox) {
                this.previewBox.innerHTML = `<div class="text-muted small text-center">${lang === 'ru' ? 'Иконка<br>не выбрана' : 'Icon<br>not selected'}</div>`;
            }
            if (this.clearBtn) {
                this.clearBtn.style.display = 'none';
            }
            this.currentSet = '';
            this.currentName = '';
        }
        
        openIconSelector() {
            if (!this.hasIcons) {
                alert(lang === 'ru' ? 'Иконки не найдены. Проверьте папку templates/default/admin/icons/' : 'Icons not found. Check the templates/default/admin/icons/ folder.');
                return;
            }
            
            const self = this;
            const modalId = 'iconSelectorModal_' + Date.now();
            
            const modalHtml = `
            <div id="${modalId}" class="icon-selector-modal" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:10000;display:flex;align-items:center;justify-content:center;">
                <div style="background:white;border-radius:8px;width:90%;max-width:1000px;max-height:80%;overflow:hidden;display:flex;flex-direction:column;">
                    <div style="padding:15px;border-bottom:1px solid #dee2e6;display:flex;justify-content:space-between;align-items:center;">
                        <h5 style="margin:0;">${lang === 'ru' ? 'Выбор иконки' : 'Icon Selection'}</h5>
                        <button type="button" class="close-modal" style="background:none;border:none;font-size:24px;cursor:pointer;">&times;</button>
                    </div>
                    <div style="padding:15px;border-bottom:1px solid #dee2e6;">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" class="form-control icon-search-input" placeholder="${lang === 'ru' ? 'Поиск иконок...' : 'Search icons...'}">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select icon-set-filter">
                                    <option value="all">${lang === 'ru' ? 'Все наборы' : 'All sets'}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="${modalId}-content" style="padding:15px;overflow-y:auto;flex:1;"></div>
                    <div style="padding:15px;border-top:1px solid #dee2e6;display:flex;justify-content:flex-end;gap:10px;">
                        <button type="button" class="btn btn-secondary close-modal">${lang === 'ru' ? 'Отмена' : 'Cancel'}</button>
                        <button type="button" class="btn btn-primary icon-select-confirm-btn" disabled>${lang === 'ru' ? 'Выбрать' : 'Select'}</button>
                    </div>
                </div>
            </div>`;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modalElement = document.getElementById(modalId);
            const contentContainer = document.getElementById(modalId + '-content');
            let selectedIconData = null;
            
            modalElement.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', () => modalElement.remove());
            });
            
            const setFilter = modalElement.querySelector('.icon-set-filter');
            for (const set in this.iconsData) {
                const option = document.createElement('option');
                option.value = set;
                option.textContent = this.iconsData[set].name || set;
                setFilter.appendChild(option);
            }
            
            const renderIcons = () => {
                const selectedSet = setFilter.value;
                const searchTerm = modalElement.querySelector('.icon-search-input').value.toLowerCase();
                let html = '';
                let hasAnyIcons = false;
                
                for (const set in self.iconsData) {
                    if (selectedSet !== 'all' && selectedSet !== set) continue;
                    
                    const setData = self.iconsData[set];
                    const templateName = setData.name || set;
                    let setHasIcons = false;
                    let setHtml = '<h6 class="mt-3 mb-2">' + templateName + '</h6>';
                    setHtml += '<div class="row g-2 mb-3">';
                    
                    if (setData.icons && setData.icons.length) {
                        for (const icon of setData.icons) {
                            const iconId = icon.id;
                            const preview = icon.preview;
                            
                            if (searchTerm && !iconId.toLowerCase().includes(searchTerm)) {
                                continue;
                            }
                            
                            setHasIcons = true;
                            hasAnyIcons = true;
                            setHtml += `
                                <div class="col-md-2 col-lg-1">
                                    <div class="icon-item border rounded p-2 text-center" 
                                         data-set="${set}" 
                                         data-name="${iconId}"
                                         style="cursor:pointer;transition:all 0.2s;">
                                        ${preview}
                                        <div class="small text-muted mt-1 text-truncate">${iconId}</div>
                                    </div>
                                </div>
                            `;
                        }
                    }
                    setHtml += '</div>';
                    
                    if (setHasIcons) {
                        html += setHtml;
                    }
                }
                
                if (!hasAnyIcons) {
                    contentContainer.innerHTML = `<div class="alert alert-info text-center py-5">${lang === 'ru' ? 'Иконки не найдены' : 'Icons not found'}</div>`;
                    return;
                }
                
                contentContainer.innerHTML = html;
                
                const iconItems = contentContainer.querySelectorAll('.icon-item');
                iconItems.forEach(item => {
                    item.addEventListener('click', function() {
                        iconItems.forEach(i => i.classList.remove('border-primary', 'bg-light'));
                        this.classList.add('border-primary', 'bg-light');
                        selectedIconData = {
                            set: this.dataset.set,
                            name: this.dataset.name
                        };
                        const confirmBtn = modalElement.querySelector('.icon-select-confirm-btn');
                        if (confirmBtn) {
                            confirmBtn.disabled = false;
                        }
                    });
                });
            };
            
            setFilter.addEventListener('change', () => renderIcons());
            modalElement.querySelector('.icon-search-input').addEventListener('input', () => renderIcons());
            
            const confirmBtn = modalElement.querySelector('.icon-select-confirm-btn');
            confirmBtn.addEventListener('click', () => {
                if (selectedIconData) {
                    self.applySelectedIcon(selectedIconData.set, selectedIconData.name);
                    modalElement.remove();
                }
            });
            
            renderIcons();
        }
        
        applySelectedIcon(set, name) {
            if (!this.hiddenInput) return;
            let currentValue = {};
            try { 
                currentValue = JSON.parse(this.hiddenInput.value); 
            } catch(e) { 
                currentValue = {}; 
            }
            currentValue.set = set;
            currentValue.name = name;
            this.hiddenInput.value = JSON.stringify(currentValue);
            const color = currentValue.color || this.currentColor;
            const size = currentValue.size || this.currentSize;
            this.updatePreview(set, name, color, size);
        }
    }
    
    function initIconFields() {
        document.querySelectorAll('.icon-with-style-field').forEach(field => {
            try { 
                new IconWithStyleField(field); 
            } catch(e) { 
                console.error('Error initializing icon field:', e); 
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initIconFields);
    } else {
        initIconFields();
    }
    
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('icon-with-style-field')) {
                                try { new IconWithStyleField(node); } catch(e) {}
                            } else if (node.querySelectorAll) {
                                node.querySelectorAll('.icon-with-style-field').forEach(field => {
                                    try { new IconWithStyleField(field); } catch(e) {}
                                });
                            }
                        }
                    });
                }
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
})();