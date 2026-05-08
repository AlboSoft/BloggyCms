if (typeof FieldsManagement === 'undefined') {
    class FieldsManagement {
        constructor() {
            this.adminUrl = window.ADMIN_URL || '/admin';
            this.fieldTypeSelect = document.getElementById('field-type');
            this.fieldSettingsContent = document.getElementById('field-settings-content');
            this.nameInput = document.querySelector('input[name="name"]');
            this.systemNameInput = document.querySelector('input[name="system_name"]');
            this.isEditMode = document.querySelector('input[name="system_name"]')?.value ? true : false;
            
            this.init();
        }

        init() {
            this.initFieldSettings();
            this.initSystemNameGeneration();
            if (!this.isEditMode && this.fieldTypeSelect.value) {
                this.loadFieldSettings();
            }
        }

        initFieldSettings() {
            if (this.fieldTypeSelect && this.fieldSettingsContent) {
                this.fieldTypeSelect.addEventListener('change', this.loadFieldSettings.bind(this));
            }
        }

        async loadFieldSettings() {
            const type = this.fieldTypeSelect.value;
            
            if (!type) {
                this.fieldSettingsContent.innerHTML = `<div class="alert alert-info">${lang === 'ru' ? 'Выберите тип поля чтобы увидеть его настройки' : 'Select a field type to see its settings'}</div>`;
                return;
            }
            const currentFormData = this.collectFormData();
            
            this.fieldSettingsContent.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border" role="status"></div>
                    <div class="mt-2">${lang === 'ru' ? 'Загрузка настроек...' : 'Loading settings...'}</div>
                </div>
            `;

            try {
                const response = await fetch(`${this.adminUrl}/fields/get-settings/${type}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const html = await response.text();
                this.fieldSettingsContent.innerHTML = html;
                if (this.isEditMode) {
                    this.restoreFormData(currentFormData);
                }
                
            } catch (error) {
                console.error('Error loading field settings:', error);
                this.fieldSettingsContent.innerHTML = `<div class="alert alert-danger">${lang === 'ru' ? 'Ошибка загрузки настроек поля' : 'Error loading field settings'}</div>`;
            }
        }
 
        collectFormData() {
            const formData = {};
            const inputs = this.fieldSettingsContent.querySelectorAll('input[name^="config["], select[name^="config["], textarea[name^="config["]');
            
            inputs.forEach(input => {
                const name = input.name;
                if (input.type === 'checkbox' || input.type === 'radio') {
                    formData[name] = input.checked ? input.value : '';
                } else {
                    formData[name] = input.value;
                }
            });
            
            return formData;
        }
        
        restoreFormData(formData) {
            Object.keys(formData).forEach(name => {
                const element = this.fieldSettingsContent.querySelector(`[name="${name}"]`);
                if (element) {
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        element.checked = formData[name] === element.value;
                    } else {
                        element.value = formData[name];
                    }
                }
            });
        }

        initSystemNameGeneration() {
            if (this.nameInput && this.systemNameInput) {
                this.nameInput.addEventListener('blur', this.generateSystemName.bind(this));
            }
        }

        generateSystemName() {
            if (this.isEditMode) {
                return;
            }
            
            if (this.systemNameInput.value) {
                return;
            }

            const name = this.nameInput.value.toLowerCase()
                .replace(/[а-яё]/g, (match) => {
                    const cyr = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя';
                    const lat = 'abvgdeejziyklmnoprstufhcchshshhyeyuya';
                    return lat[cyr.indexOf(match)] || '';
                })
                .replace(/[^a-z0-9]/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '');
            
            this.systemNameInput.value = name;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        new FieldsManagement();
    });
}