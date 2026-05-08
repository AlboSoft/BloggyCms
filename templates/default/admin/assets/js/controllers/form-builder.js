class FormBuilder {
    constructor() {
        this.fieldCounter = 0;
        this.currentEditIndex = null;
        this.currentFieldType = null;
        this.selectedFieldTypeCard = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadInitialData();
        this.initializeSortable();
        this.updateFieldsCount();
    }

    setupEventListeners() {
        const typeSelector = document.getElementById('field-type-selector');
        if (typeSelector) {
            typeSelector.addEventListener('click', (e) => {
                const fieldCard = e.target.closest('.field-type-card');
                if (fieldCard) {
                    this.selectFieldType(fieldCard);
                }
            });
        } else {
            console.error(lang === 'ru' ? 'Контейнер выбора типа поля не найден!' : 'Field type selector container not found!');
        }

        const saveBtn = document.getElementById('save-field-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveField();
            });
        }

        const fieldsContainer = document.getElementById('form-fields-container');
        if (fieldsContainer) {
            fieldsContainer.addEventListener('click', (e) => {
                this.handleFieldClick(e);
            });
        }

        const form = document.getElementById('form-builder-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                this.handleFormSubmit(e);
            });
        }

        const fieldLabel = document.getElementById('field-label');
        if (fieldLabel) {
            fieldLabel.addEventListener('input', () => {
                this.generateFieldName();
            });
        }

        const addFieldModal = document.getElementById('addFieldModal');
        if (addFieldModal) {
            addFieldModal.addEventListener('hidden.bs.modal', () => {
                this.resetFieldModal();
            });
        }
    }

    loadInitialData() {
        const structureField = document.getElementById('form-structure');
        if (!structureField || !structureField.value) {
            this.showEmptyMessage();
            return;
        }

        try {
            const structure = JSON.parse(structureField.value);
            if (structure && structure.length > 0) {
                this.renderFormStructure(structure);
            } else {
                this.showEmptyMessage();
            }
        } catch (error) {
            console.error(lang === 'ru' ? 'Ошибка при разборе структуры формы:' : 'Error parsing form structure:', error);
            this.showEmptyMessage();
        }
    }

    renderFormStructure(structure) {
        const container = document.getElementById('form-fields-container');
        if (!container) {
            console.error(lang === 'ru' ? 'Контейнер полей не найден!' : 'Fields container not found!');
            return;
        }
        
        container.innerHTML = '';
        this.fieldCounter = 0;

        structure.forEach((field, index) => {
            this.addFieldToContainer(field, index);
        });

        this.hideEmptyMessage();
        this.initializeSortable();
        this.updateFieldsCount();
    }

    addFieldToContainer(fieldData, index = null) {
        const container = document.getElementById('form-fields-container');
        if (!container) return;
        
        if (index === null) {
            index = this.fieldCounter++;
        } else {
            this.fieldCounter = Math.max(this.fieldCounter, index + 1);
        }

        const fieldHtml = this.createFieldHtml(fieldData, index);
        container.insertAdjacentHTML('beforeend', fieldHtml);

        if (container.children.length === 1) {
            this.hideEmptyMessage();
        }

        this.updateFormStructure();
        this.updateFieldsCount();
    }

    createFieldHtml(fieldData, index) {
        const type = fieldData.type || 'text';
        const label = fieldData.label || '';
        const name = fieldData.name || '';
        const description = fieldData.description || '';
        const required = fieldData.required || false;
        const placeholder = fieldData.placeholder || '';
        let previewHtml = this.createFieldPreview(fieldData);

        let badges = '';
        if (required) badges += '<span class="badge bg-danger ms-1">' + (lang === 'ru' ? 'Обязательное' : 'Required') + '</span>';
        if (fieldData.validation && Object.keys(fieldData.validation).length > 0) {
            badges += '<span class="badge bg-info ms-1">' + (lang === 'ru' ? 'Валидация' : 'Validation') + '</span>';
        }

        return `
            <div class="form-field card mb-2" data-index="${index}" data-type="${type}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="card-title mb-1">
                                <i class="bi bi-${this.getFieldTypeIcon(type)} me-2"></i>
                                ${this.escapeHtml(label) || (lang === 'ru' ? 'Без названия' : 'Untitled')} ${badges}
                            </h6>
                            <small class="text-muted">
                                <code>name="${this.escapeHtml(name)}"</code>
                                ${description ? `- ${this.escapeHtml(description)}` : ''}
                            </small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary field-handle">
                                <i class="bi bi-arrows-move"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary edit-field">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger remove-field">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="preview-area">
                        ${previewHtml}
                    </div>
                    
                    <!-- ${lang === 'ru' ? 'Скрытые данные' : 'Hidden data'} -->
                    <input type="hidden" class="field-data" 
                           value='${this.escapeHtml(JSON.stringify(fieldData))}'>
                </div>
            </div>
        `;
    }

    createFieldPreview(fieldData) {
        const type = fieldData.type || 'text';
        const label = fieldData.label || '';
        const placeholder = fieldData.placeholder || '';
        const required = fieldData.required || false;
        const options = fieldData.options || [];
        
        switch(type) {
            case 'text':
            case 'email':
            case 'tel':
            case 'number':
            case 'date':
            case 'password':
                return `
                    <input type="${type}" 
                           class="form-control form-control-sm" 
                           placeholder="${this.escapeHtml(placeholder)}"
                           ${required ? 'required' : ''}
                           disabled>
                    ${required ? `<div class="invalid-feedback d-none">${lang === 'ru' ? 'Обязательное поле' : 'Required field'}</div>` : ''}
                `;
                
            case 'textarea':
                return `
                    <textarea class="form-control form-control-sm" 
                              rows="${fieldData.rows || 2}"
                              placeholder="${this.escapeHtml(placeholder)}"
                              ${required ? 'required' : ''}
                              disabled></textarea>
                    ${required ? `<div class="invalid-feedback d-none">${lang === 'ru' ? 'Обязательное поле' : 'Required field'}</div>` : ''}
                `;
                
            case 'select':
                return `
                    <select class="form-select form-select-sm" ${required ? 'required' : ''} disabled>
                        <option value="">${this.escapeHtml(placeholder || (lang === 'ru' ? 'Выберите...' : 'Select...'))}</option>
                        ${options.map(opt => `
                            <option value="${this.escapeHtml(opt.value || '')}">
                                ${this.escapeHtml(opt.label || '')}
                            </option>
                        `).join('')}
                    </select>
                    ${required ? `<div class="invalid-feedback d-none">${lang === 'ru' ? 'Обязательное поле' : 'Required field'}</div>` : ''}
                `;
                
            case 'checkbox':
                return `
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               ${required ? 'required' : ''}
                               disabled>
                        <label class="form-check-label">${this.escapeHtml(label)}</label>
                    </div>
                    ${required ? `<div class="invalid-feedback d-none">${lang === 'ru' ? 'Обязательное поле' : 'Required field'}</div>` : ''}
                `;
                
            case 'radio':
                return `
                    <div class="radio-group">
                        ${options.map((opt, i) => `
                            <div class="form-check">
                                <input type="radio" 
                                       class="form-check-input" 
                                       name="preview_radio_${Date.now()}"
                                       ${required ? 'required' : ''}
                                       disabled>
                                <label class="form-check-label">${this.escapeHtml(opt.label || '')}</label>
                            </div>
                        `).join('')}
                    </div>
                    ${required ? `<div class="invalid-feedback d-none">${lang === 'ru' ? 'Обязательное поле' : 'Required field'}</div>` : ''}
                `;
                
            case 'file':
                return `
                    <input type="file" 
                           class="form-control form-control-sm" 
                           ${required ? 'required' : ''}
                           disabled>
                    ${required ? `<div class="invalid-feedback d-none">${lang === 'ru' ? 'Обязательное поле' : 'Required field'}</div>` : ''}
                `;
                
            case 'submit':
                return `
                    <button type="button" 
                            class="btn btn-primary btn-sm ${fieldData.class || ''}" 
                            disabled>
                        ${this.escapeHtml(label || (lang === 'ru' ? 'Отправить' : 'Submit'))}
                    </button>
                `;
                
            default:
                return `<div class="alert alert-warning small p-2">${lang === 'ru' ? 'Превью недоступно для типа' : 'Preview unavailable for type'} "${type}"</div>`;
        }
    }

    getFieldTypeIcon(type) {
        const icons = {
            'text': 'input-cursor-text',
            'textarea': 'textarea-t',
            'email': 'envelope',
            'tel': 'telephone',
            'number': '123',
            'date': 'calendar',
            'select': 'menu-down',
            'checkbox': 'check-square',
            'radio': 'circle',
            'file': 'paperclip',
            'password': 'key',
            'hidden': 'eye-slash',
            'submit': 'send'
        };
        return icons[type] || 'input-cursor';
    }

    selectFieldType(card) {
        document.querySelectorAll('.field-type-card').forEach(c => {
            c.classList.remove('selected', 'border-primary');
            c.classList.add('border-transparent');
        });

        card.classList.add('selected', 'border-primary');
        card.classList.remove('border-transparent');
        
        this.selectedFieldTypeCard = card;
        const type = card.dataset.type;
        this.currentFieldType = type;
        const hasOptions = card.dataset.hasOptions === '1';
        const hasPlaceholder = card.dataset.hasPlaceholder === '1';
        const fieldTypeInput = document.getElementById('field-type');
        if (fieldTypeInput) {
            fieldTypeInput.value = type;
        }

        const fieldSettings = document.getElementById('field-settings');
        if (fieldSettings) {
            fieldSettings.style.display = 'block';
        }

        this.updateDynamicSettings(type, hasOptions, hasPlaceholder);

        const saveBtn = document.getElementById('save-field-btn');
        if (saveBtn) {
            saveBtn.disabled = false;
        }

        setTimeout(() => {
            const labelInput = document.getElementById('field-label');
            if (labelInput) {
                labelInput.focus();
            }
        }, 100);
    }

    updateDynamicSettings(type, hasOptions, hasPlaceholder) {
        const container = document.getElementById('dynamic-settings');
        if (!container) return;
        
        let html = '';

        if (hasPlaceholder) {
            html += `
                <div class="col-md-12">
                    <label class="form-label">
                        <i class="bi bi-textarea-t me-1"></i>${lang === 'ru' ? 'Плейсхолдер' : 'Placeholder'}
                    </label>
                    <input type="text" 
                        class="form-control" 
                        id="field-placeholder" 
                        name="placeholder"
                        placeholder="${lang === 'ru' ? 'Текст-подсказка' : 'Hint text'}">
                </div>
            `;
        }

        if (hasOptions) {
            html += `
                <div class="col-md-12">
                    <label class="form-label">
                        <i class="bi bi-list-ul me-1"></i>${lang === 'ru' ? 'Опции' : 'Options'}
                    </label>
                    <div id="field-options-container">
                        <div class="option-item mb-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">${lang === 'ru' ? 'Значение' : 'Value'}</span>
                                <input type="text" class="form-control option-value" placeholder="${lang === 'ru' ? 'значение1' : 'value1'}">
                                <span class="input-group-text">${lang === 'ru' ? 'Текст' : 'Label'}</span>
                                <input type="text" class="form-control option-label" placeholder="${lang === 'ru' ? 'Опция 1' : 'Option 1'}">
                                <button type="button" class="btn btn-outline-danger remove-option" onclick="formBuilder.removeOption(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="formBuilder.addOption()">
                        <i class="bi bi-plus-circle me-1"></i>${lang === 'ru' ? 'Добавить опцию' : 'Add option'}
                    </button>
                </div>
            `;
        }

        switch(type) {
            case 'textarea':
                html += `
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-textarea-resize me-1"></i>${lang === 'ru' ? 'Количество строк' : 'Number of rows'}
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="field-rows" 
                               name="rows"
                               value="3"
                               min="1"
                               max="10">
                    </div>
                `;
                break;
                
            case 'file':
                html += `
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-check2-square me-1"></i>${lang === 'ru' ? 'Разрешить несколько файлов' : 'Allow multiple files'}
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="field-multiple" 
                                   name="multiple">
                            <label class="form-check-label" for="field-multiple">
                                ${lang === 'ru' ? 'Да' : 'Yes'}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-files me-1"></i>${lang === 'ru' ? 'Разрешенные типы файлов' : 'Allowed file types'}
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="field-accept" 
                               name="accept"
                               placeholder=".jpg,.png,.pdf">
                        <div class="form-text small">${lang === 'ru' ? 'Через запятую: .jpg, .png, .pdf' : 'Comma separated: .jpg, .png, .pdf'}</div>
                    </div>
                `;
                break;
                
            case 'submit':
                html += `
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-palette me-1"></i>${lang === 'ru' ? 'CSS классы' : 'CSS classes'}
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="field-class" 
                               name="class"
                               placeholder="btn btn-primary">
                    </div>
                `;
                break;
                
            case 'hidden':
                html += `
                    <div class="col-md-12">
                        <label class="form-label">
                            <i class="bi bi-eye-slash me-1"></i>${lang === 'ru' ? 'Значение по умолчанию' : 'Default value'}
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="field-default-value" 
                               name="default_value"
                               placeholder="${lang === 'ru' ? 'Значение скрытого поля' : 'Hidden field value'}">
                    </div>
                `;
                break;
        }

        container.innerHTML = html;

        if (hasOptions) {
            this.setupOptionsEvents();
        }
    }

    setupOptionsEvents() {
        document.querySelectorAll('.remove-option').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.closest('.option-item').remove();
            });
        });
    }

    addOption() {
        const container = document.getElementById('field-options-container');
        if (!container) return;
        
        const optionHtml = `
            <div class="option-item mb-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">${lang === 'ru' ? 'Значение' : 'Value'}</span>
                    <input type="text" class="form-control option-value" placeholder="${lang === 'ru' ? 'значение' : 'value'}">
                    <span class="input-group-text">${lang === 'ru' ? 'Текст' : 'Label'}</span>
                    <input type="text" class="form-control option-label" placeholder="${lang === 'ru' ? 'Опция' : 'Option'}">
                    <button type="button" class="btn btn-outline-danger remove-option" onclick="formBuilder.removeOption(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', optionHtml);
    }

    removeOption(button) {
        if (button && button.closest) {
            button.closest('.option-item').remove();
        }
    }

    generateFieldName() {
        const labelInput = document.getElementById('field-label');
        const nameInput = document.getElementById('field-name');
        
        if (!labelInput || !nameInput) return;
        
        const label = labelInput.value.trim();
        if (!label) return;

        let name = label.toLowerCase()
            .replace(/[а-яё]/g, function(ch) {
                const map = {
                    'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
                    'е': 'e', 'ё': 'yo', 'ж': 'zh', 'з': 'z', 'и': 'i',
                    'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
                    'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
                    'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch',
                    'ш': 'sh', 'щ': 'sch', 'ъ': '', 'ы': 'y', 'ь': '',
                    'э': 'e', 'ю': 'yu', 'я': 'ya'
                };
                return map[ch] || ch;
            })
            .replace(/[^a-z0-9_]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_+|_+$/g, '');

        if (/^\d/.test(name)) {
            name = 'field_' + name;
        }

        if (!name) {
            name = 'field_' + Date.now();
        }

        nameInput.value = name;
    }

    saveField() {
        
        const form = document.getElementById('field-settings-form');
        if (!form) {
            console.error(lang === 'ru' ? 'Форма настроек поля не найдена!' : 'Field settings form not found!');
            return;
        }
        
        const formData = new FormData(form);
        
        const fieldData = {
            type: formData.get('type') || 'text',
            label: formData.get('label') || '',
            name: formData.get('name') || '',
            description: formData.get('description') || '',
            required: formData.get('required') === 'on',
            placeholder: formData.get('placeholder') || '',
            class: formData.get('class') || '',
            validation: this.collectValidationRules()
        };

        if (fieldData.type === 'textarea') {
            fieldData.rows = parseInt(formData.get('rows')) || 3;
        }

        if (fieldData.type === 'file') {
            if (formData.get('multiple') === 'on') {
                fieldData.multiple = true;
            }
            if (formData.get('accept')) {
                fieldData.accept = formData.get('accept');
            }
        }

        if (fieldData.type === 'select' || fieldData.type === 'radio') {
            fieldData.options = this.collectOptions();
        }

        if (fieldData.type === 'submit') {
            fieldData.button_text = formData.get('button_text') || (lang === 'ru' ? 'Отправить' : 'Submit');
        }

        const defaultValue = formData.get('default_value') || '';
        if (fieldData.type !== 'submit' && defaultValue) {
            fieldData.default_value = defaultValue;
        }

        if (!fieldData.label.trim()) {
            alert(lang === 'ru' ? 'Пожалуйста, заполните заголовок поля' : 'Please enter the field label');
            document.getElementById('field-label').focus();
            return;
        }
        
        if (!fieldData.name.trim() && fieldData.type !== 'submit') {
            alert(lang === 'ru' ? 'Пожалуйста, заполните имя поля (атрибут name)' : 'Please enter the field name (name attribute)');
            document.getElementById('field-name').focus();
            return;
        }

        if (this.currentEditIndex !== null) {
            this.updateField(this.currentEditIndex, fieldData);
        } else {
            this.addFieldToContainer(fieldData);
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('addFieldModal'));
        if (modal) {
            modal.hide();
        }

        this.resetFieldModal();
    }

    collectOptions() {
        const options = [];
        document.querySelectorAll('.option-item').forEach(item => {
            const valueInput = item.querySelector('.option-value');
            const labelInput = item.querySelector('.option-label');
            
            if (valueInput && labelInput) {
                const value = valueInput.value.trim();
                const label = labelInput.value.trim();
                
                if (value || label) {
                    options.push({ 
                        value: value || label.toLowerCase().replace(/\s+/g, '_'),
                        label: label || value 
                    });
                }
            }
        });
        return options;
    }

    collectValidationRules() {
        const rules = {};
        document.querySelectorAll('.validation-rule').forEach(rule => {
            const typeSelect = rule.querySelector('.rule-type');
            const paramInput = rule.querySelector('.rule-param');
            
            if (typeSelect && typeSelect.value) {
                const type = typeSelect.value;
                const param = paramInput ? paramInput.value.trim() : null;
                
                if (param && param !== '') {
                    rules[type] = param;
                } else {
                    rules[type] = true;
                }
            }
        });
        return rules;
    }

    handleFieldClick(e) {
        const fieldCard = e.target.closest('.form-field');
        if (!fieldCard) return;

        if (e.target.closest('.remove-field')) {
            this.removeField(fieldCard);
        } else if (e.target.closest('.edit-field')) {
            this.editField(fieldCard);
        }
    }

    removeField(fieldCard) {
        if (!confirm(lang === 'ru' ? 'Удалить это поле из формы?' : 'Delete this field from the form?')) return;

        fieldCard.remove();
        
        const container = document.getElementById('form-fields-container');
        if (container && container.children.length === 0) {
            this.showEmptyMessage();
        }
        
        this.updateFormStructure();
        this.updateFieldsCount();
    }

    editField(fieldCard) {
        
        const fieldDataElement = fieldCard.querySelector('.field-data');
        if (!fieldDataElement) {
            console.error(lang === 'ru' ? 'Данные поля не найдены' : 'Field data not found');
            return;
        }
        
        let fieldData;
        try {
            fieldData = JSON.parse(fieldDataElement.value);
        } catch (e) {
            console.error(lang === 'ru' ? 'Ошибка при разборе данных поля:' : 'Error parsing field data:', e);
            return;
        }
        
        const index = fieldCard.dataset.index;
        this.currentEditIndex = index;
        
        const typeCard = document.querySelector(`[data-type="${fieldData.type}"]`);
        if (typeCard) {
            this.selectFieldType(typeCard);
            setTimeout(() => {
                this.fillFieldForm(fieldData);
            }, 100);
        }
        

        const modal = new bootstrap.Modal(document.getElementById('addFieldModal'));
        modal.show();
    }

    fillFieldForm(fieldData) {
        
        const labelInput = document.getElementById('field-label');
        const nameInput = document.getElementById('field-name');
        const descInput = document.getElementById('field-description');
        const placeholderInput = document.getElementById('field-placeholder');
        const requiredCheckbox = document.getElementById('field-required');
        const classInput = document.getElementById('field-class');
        
        if (labelInput) labelInput.value = fieldData.label || '';
        if (nameInput) nameInput.value = fieldData.name || '';
        if (descInput) descInput.value = fieldData.description || '';
        if (placeholderInput) placeholderInput.value = fieldData.placeholder || '';
        if (requiredCheckbox) requiredCheckbox.checked = fieldData.required || false;
        if (classInput) {
            classInput.value = fieldData.class || '';
        }
        
        const defaultValueInput = document.getElementById('field-default-value');
        if (defaultValueInput && fieldData.default_value !== undefined) {
            defaultValueInput.value = fieldData.default_value || '';
        }
        
        if (fieldData.type === 'textarea' && document.getElementById('field-rows')) {
            document.getElementById('field-rows').value = fieldData.rows || 3;
        }
        
        if (fieldData.type === 'file') {
            if (document.getElementById('field-multiple')) {
                document.getElementById('field-multiple').checked = fieldData.multiple || false;
            }
            if (document.getElementById('field-accept')) {
                document.getElementById('field-accept').value = fieldData.accept || '';
            }
        }
        
        if (fieldData.type === 'submit' && document.getElementById('field-button-text')) {
            document.getElementById('field-button-text').value = fieldData.button_text || (lang === 'ru' ? 'Отправить' : 'Submit');
        }
        
        if ((fieldData.type === 'select' || fieldData.type === 'radio') && fieldData.options) {
            const optionsContainer = document.getElementById('field-options-container');
            if (optionsContainer) {
                optionsContainer.innerHTML = '';
                
                fieldData.options.forEach(option => {
                    this.addOption();
                    const lastOption = optionsContainer.lastElementChild;
                    if (lastOption) {
                        const valueInput = lastOption.querySelector('.option-value');
                        const labelInput = lastOption.querySelector('.option-label');
                        if (valueInput) valueInput.value = option.value || '';
                        if (labelInput) labelInput.value = option.label || '';
                    }
                });
            }
        }
        
        if (fieldData.validation) {
            this.fillValidationRules(fieldData.validation);
        }
    }

    fillValidationRules(rules) {
        const container = document.getElementById('validation-rules');
        if (!container) return;
        
        container.innerHTML = '';
        
        for (const [type, param] of Object.entries(rules)) {
            this.addValidationRule(type, param);
        }
    }

    addValidationRule(type = '', param = '') {
        const container = document.getElementById('validation-rules');
        if (!container) return;
        
        const ruleId = 'rule_' + Date.now();
        const hasParam = ['min', 'max', 'regex'].includes(type);
        
        let paramHtml = '';
        if (hasParam) {
            const paramValue = (param === true ? '' : param);
            paramHtml = `
                <div class="col-md-4">
                    <input type="text" 
                           class="form-control form-control-sm rule-param" 
                           placeholder="${lang === 'ru' ? 'Параметр' : 'Parameter'}"
                           value="${this.escapeHtml(paramValue)}">
                </div>
            `;
        }
        
        const ruleHtml = `
            <div class="row mb-2 validation-rule" id="${ruleId}">
                <div class="col-md-${hasParam ? '4' : '8'}">
                    <select class="form-select form-select-sm rule-type">
                        <option value="">${lang === 'ru' ? 'Выберите правило' : 'Select a rule'}</option>
                        <option value="required" ${type === 'required' ? 'selected' : ''}>${lang === 'ru' ? 'Обязательное' : 'Required'}</option>
                        <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="url" ${type === 'url' ? 'selected' : ''}>URL</option>
                        <option value="numeric" ${type === 'numeric' ? 'selected' : ''}>${lang === 'ru' ? 'Число' : 'Number'}</option>
                        <option value="min" ${type === 'min' ? 'selected' : ''}>${lang === 'ru' ? 'Минимальное' : 'Minimum'}</option>
                        <option value="max" ${type === 'max' ? 'selected' : ''}>${lang === 'ru' ? 'Максимальное' : 'Maximum'}</option>
                        <option value="regex" ${type === 'regex' ? 'selected' : ''}>${lang === 'ru' ? 'Регулярное выражение' : 'Regular expression'}</option>
                    </select>
                </div>
                ${paramHtml}
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                            onclick="document.getElementById('${ruleId}').remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', ruleHtml);
    }

    updateField(index, fieldData) {
        const fieldCard = document.querySelector(`.form-field[data-index="${index}"]`);
        if (!fieldCard) return;
        const fieldDataElement = fieldCard.querySelector('.field-data');
        if (fieldDataElement) {
            fieldDataElement.value = JSON.stringify(fieldData);
        }

        this.updateFieldDisplay(fieldCard, fieldData);

        this.updateFormStructure();
        this.updateFieldsCount();
    }

    updateFieldDisplay(fieldCard, fieldData) {
        const title = fieldCard.querySelector('.card-title');
        const subtitle = fieldCard.querySelector('small');
        const preview = fieldCard.querySelector('.preview-area');
        const icon = fieldCard.querySelector('.bi');

        if (!title || !subtitle || !preview) return;

        if (icon) {
            icon.className = `bi bi-${this.getFieldTypeIcon(fieldData.type)} me-2`;
        }

        let badges = '';
        if (fieldData.required) badges += '<span class="badge bg-danger ms-1">' + (lang === 'ru' ? 'Обязательное' : 'Required') + '</span>';
        if (fieldData.validation && Object.keys(fieldData.validation).length > 0) {
            badges += '<span class="badge bg-info ms-1">' + (lang === 'ru' ? 'Валидация' : 'Validation') + '</span>';
        }

        title.innerHTML = `<i class="bi bi-${this.getFieldTypeIcon(fieldData.type)} me-2"></i>${this.escapeHtml(fieldData.label) || (lang === 'ru' ? 'Без названия' : 'Untitled')} ${badges}`;
        subtitle.innerHTML = `<code>name="${this.escapeHtml(fieldData.name)}"</code> ${fieldData.description ? `- ${this.escapeHtml(fieldData.description)}` : ''}`;
        preview.innerHTML = this.createFieldPreview(fieldData);
    }

    initializeSortable() {

        const container = document.getElementById('form-fields-container');
        if (!container) return;

        try {
            new Sortable(container, {
                handle: '.field-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: () => {
                    this.updateFormStructure();
                }
            });
        } catch (e) {
            console.error(lang === 'ru' ? 'Ошибка при инициализации Sortable:' : 'Error initializing Sortable:', e);
        }
    }

    updateFormStructure() {
        const structure = [];
        const container = document.getElementById('form-fields-container');
        
        if (!container) return;
        
        container.querySelectorAll('.form-field').forEach(fieldCard => {
            const fieldDataElement = fieldCard.querySelector('.field-data');
            if (fieldDataElement) {
                try {
                    const fieldData = JSON.parse(fieldDataElement.value);
                    structure.push(fieldData);
                } catch (e) {
                    console.error(lang === 'ru' ? 'Ошибка при разборе данных поля:' : 'Error parsing field data:', e);
                }
            }
        });
        
        const structureField = document.getElementById('form-structure');
        if (structureField) {
            structureField.value = JSON.stringify(structure);
        }
    }

    updateFieldsCount() {
        const totalItems = document.querySelectorAll('.form-field').length;
        const totalItemsElement = document.getElementById('total-items');
        
        if (totalItemsElement) {
            totalItemsElement.textContent = totalItems;
        }
        
    }

    showEmptyMessage() {
        const emptyMessage = document.getElementById('form-empty');
        if (emptyMessage) {
            emptyMessage.style.display = 'block';
        }
    }

    hideEmptyMessage() {
        const emptyMessage = document.getElementById('form-empty');
        if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }
    }

    resetFieldModal() {
        
        const form = document.getElementById('field-settings-form');
        if (form) {
            form.reset();
        }
        
        if (this.selectedFieldTypeCard) {
            this.selectedFieldTypeCard.classList.remove('selected', 'border-primary');
            this.selectedFieldTypeCard.classList.add('border-transparent');
            this.selectedFieldTypeCard = null;
        }
        
        const fieldSettings = document.getElementById('field-settings');
        if (fieldSettings) {
            fieldSettings.style.display = 'none';
        }
        
        const saveBtn = document.getElementById('save-field-btn');
        if (saveBtn) {
            saveBtn.disabled = true;
        }
        
        const dynamicSettings = document.getElementById('dynamic-settings');
        if (dynamicSettings) {
            dynamicSettings.innerHTML = '';
        }
        
        const validationRules = document.getElementById('validation-rules');
        if (validationRules) {
            validationRules.innerHTML = '';
        }
        
        this.currentEditIndex = null;
        this.currentFieldType = null;
    }

    handleFormSubmit(e) {
        const structure = JSON.parse(document.getElementById('form-structure').value || '[]');
        
        if (structure.length === 0) {
            e.preventDefault();
            alert(lang === 'ru' ? 'Добавьте хотя бы одно поле в форму' : 'Add at least one field to the form');
            return;
        }

        const hasSubmit = structure.some(field => field.type === 'submit');
        if (!hasSubmit) {
            e.preventDefault();
            alert(lang === 'ru' ? 'Добавьте кнопку отправки (поле типа "submit")' : 'Add a submit button (field type "submit")');
            return;
        }

        const fieldNames = [];
        const errors = [];
        
        structure.forEach((field, index) => {
            if (field.name && field.type !== 'submit') {
                if (fieldNames.includes(field.name)) {
                    errors.push(lang === 'ru' 
                        ? `Поле "${field.label || field.name}" (№${index + 1}): имя "${field.name}" уже используется другим полем`
                        : `Field "${field.label || field.name}" (#${index + 1}): name "${field.name}" is already used by another field`
                    );
                }
                fieldNames.push(field.name);
            }
        });
        
        if (errors.length > 0) {
            e.preventDefault();
            alert((lang === 'ru' ? 'Ошибки в форме:\n' : 'Form errors:\n') + errors.join('\n'));
        }
    }

    escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

function addValidationRule() {
    if (window.formBuilder) {
        window.formBuilder.addValidationRule();
    }
}

function removeOption(button) {
    if (window.formBuilder) {
        window.formBuilder.removeOption(button);
    }
}

function addOption() {
    if (window.formBuilder) {
        window.formBuilder.addOption();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.formBuilder = new FormBuilder();
});