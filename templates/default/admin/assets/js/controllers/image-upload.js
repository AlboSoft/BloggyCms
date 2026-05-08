if (typeof ImageUploader !== 'undefined') {

} else {
    class ImageUploader {
        constructor(options = {}) {
            this.options = {
                uploadAreaId: 'imageUploadArea',
                fileInputId: 'featured-image-input',
                imagePreviewId: 'imagePreview',
                uploadDefaultId: 'uploadDefault',
                uploadPreviewId: 'uploadPreview',
                fileNameId: 'fileName',
                uploadProgressId: 'uploadProgress',
                progressBarId: 'progressBar',
                progressTextId: 'progressText',
                removeImageFieldId: 'removeFeaturedImage',
                keepImageCheckboxId: 'keep_current_image',
                ...options
            };

            this.init();
        }

        init() {
            this.elements = {};
            
            for (const [key, value] of Object.entries(this.options)) {
                if (key.endsWith('Id') && value) {
                    const elementName = key.replace('Id', '');
                    this.elements[elementName] = document.getElementById(value);
                }
            }

            if (!this.elements.uploadArea || !this.elements.fileInput) {
                return;
            }

            this.setupEventListeners();
            this.setupEditMode();
        }

        setupEventListeners() {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                this.elements.uploadArea.addEventListener(eventName, this.preventDefaults.bind(this), false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                this.elements.uploadArea.addEventListener(eventName, this.highlight.bind(this), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                this.elements.uploadArea.addEventListener(eventName, this.unhighlight.bind(this), false);
            });

            this.elements.uploadArea.addEventListener('drop', this.handleDrop.bind(this), false);
            this.elements.fileInput.addEventListener('change', this.handleFileInput.bind(this));

            if (this.elements.keepImageCheckbox) {
                this.elements.keepImageCheckbox.addEventListener('change', this.handleKeepImageChange.bind(this));
            }

            this.setupHoverEffects();
        }

        setupEditMode() {
            if (this.elements.uploadPreview && !this.elements.uploadPreview.classList.contains('d-none')) {
                this.setupEditModeButtons();
            }
        }

        setupEditModeButtons() {
            let buttonsContainer = this.elements.uploadPreview.querySelector('.mt-2:last-child');
            
            if (!buttonsContainer || !buttonsContainer.querySelector('button')) {
                buttonsContainer = document.createElement('div');
                buttonsContainer.className = 'mt-2';
                this.elements.uploadPreview.appendChild(buttonsContainer);
            }
            
            buttonsContainer.innerHTML = `
                <button type="button" class="btn btn-outline-primary btn-sm replace-image-btn" onclick="imageUploader.handleReplaceImage()">
                    <i class="bi bi-arrow-repeat me-1"></i>${lang === 'ru' ? 'Заменить изображение' : 'Replace image'}
                </button>
            `;
        }

        setupHoverEffects() {
            if (this.elements.uploadArea) {
                this.elements.uploadArea.addEventListener('mouseenter', () => {
                    this.elements.uploadArea.style.transform = 'translateY(-2px)';
                    this.elements.uploadArea.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
                });
                
                this.elements.uploadArea.addEventListener('mouseleave', () => {
                    this.elements.uploadArea.style.transform = 'translateY(0)';
                    this.elements.uploadArea.style.boxShadow = 'none';
                });
            }
        }

        preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        highlight() {
            if (this.elements.uploadArea) {
                this.elements.uploadArea.style.borderColor = '#0d6efd';
                this.elements.uploadArea.style.background = '#e3f2fd';
            }
        }

        unhighlight() {
            if (this.elements.uploadArea) {
                this.elements.uploadArea.style.borderColor = '#dee2e6';
                this.elements.uploadArea.style.background = '#f8f9fa';
            }
        }

        handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            this.handleFiles(files);
        }

        handleFileInput() {
            this.handleFiles(this.elements.fileInput.files);
        }

        handleReplaceImage() {
            this.elements.fileInput.click();
        }

        handleKeepImageChange(e) {
            if (!e.target.checked) {
                this.showUploadForm();
                if (this.elements.removeImageField) {
                    this.elements.removeImageField.value = '1';
                }
            } else {
                this.showCurrentImage();
                if (this.elements.removeImageField) {
                    this.elements.removeImageField.value = '0';
                }
            }
        }

        handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                
                if (!file.type.match('image.*')) {
                    this.showError(lang === 'ru' ? 'Пожалуйста, выберите изображение' : 'Please select an image');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    this.showError(lang === 'ru' ? 'Файл слишком большой. Максимальный размер: 5MB' : 'File is too large. Maximum size: 5MB');
                    return;
                }
                
                if (this.elements.keepImageCheckbox) {
                    this.elements.keepImageCheckbox.checked = false;
                    if (this.elements.removeImageField) {
                        this.elements.removeImageField.value = '0';
                    }
                }
                
                this.showUploadProgress();
                this.processFile(file);
            }
        }

        processFile(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                if (this.elements.imagePreview) {
                    this.elements.imagePreview.src = e.target.result;
                }
                if (this.elements.fileName) {
                    this.elements.fileName.textContent = file.name;
                }
                
                this.showPreview();
                this.hideProgress();
                this.uploadToServer(file);
                
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                this.elements.fileInput.files = dataTransfer.files;
                
                this.setupEditModeButtons();
            };
            reader.readAsDataURL(file);
        }

        async uploadToServer(file) {
            const formData = new FormData();
            formData.append('featured_image', file);
            
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 30000);
                
                const response = await fetch('/admin/posts/upload-featured-image', {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                const result = await response.json();
                
                if (result.success) {
                    if (this.elements.imagePreview && document.body.contains(this.elements.imagePreview)) {
                        this.elements.imagePreview.src = result.url;
                    }
                    
                    this.createHiddenInput('uploaded_image_path', result.path);
                    this.createHiddenInput('uploaded_image_url', result.url);
                    
                } else {
                    this.showError(result.message || (lang === 'ru' ? 'Ошибка загрузки изображения' : 'Image upload error'));
                    this.showUploadForm();
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                } else {
                    this.showError(lang === 'ru' ? 'Ошибка сети при загрузке изображения' : 'Network error during image upload');
                    this.showUploadForm();
                }
            }
        }

        createHiddenInput(name, value) {
            let existingInput = document.querySelector(`input[name="${name}"]`);
            if (existingInput) {
                existingInput.remove();
            }
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            document.getElementById('post-form').appendChild(input);
        }

        showUploadProgress() {
            if (this.elements.uploadProgress) {
                this.elements.uploadProgress.classList.remove('d-none');
            }
            if (this.elements.progressBar && this.elements.progressText) {
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    this.elements.progressBar.style.width = progress + '%';
                    
                    if (progress >= 100) {
                        clearInterval(interval);
                        this.elements.progressText.textContent = lang === 'ru' ? 'Загрузка завершена!' : 'Upload complete!';
                    }
                }, 100);
            }
        }

        hideProgress() {
            if (this.elements.uploadProgress) {
                this.elements.uploadProgress.classList.add('d-none');
            }
        }

        showPreview() {
            if (this.elements.uploadDefault) {
                this.elements.uploadDefault.classList.add('d-none');
            }
            if (this.elements.uploadPreview) {
                this.elements.uploadPreview.classList.remove('d-none');
            }
        }

        showUploadForm() {
            if (this.elements.uploadPreview) {
                this.elements.uploadPreview.classList.add('d-none');
            }
            if (this.elements.uploadDefault) {
                this.elements.uploadDefault.classList.remove('d-none');
            }
        }

        showCurrentImage() {
            if (this.elements.uploadDefault) {
                this.elements.uploadDefault.classList.add('d-none');
            }
            if (this.elements.uploadPreview) {
                this.elements.uploadPreview.classList.remove('d-none');
            }
        }

        removeImage() {
            if (this.elements.keepImageCheckbox) {
                this.elements.keepImageCheckbox.checked = false;
                this.showUploadForm();
                if (this.elements.removeImageField) {
                    this.elements.removeImageField.value = '1';
                }
            } else {
                this.showUploadForm();
                this.elements.fileInput.value = '';
            }
        }

        showError(message) {
            alert(message);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const uploadAreas = document.querySelectorAll('.image-upload-area');
        
        uploadAreas.forEach((area, index) => {
            const areaId = area.id || `imageUploadArea-${index}`;
            area.id = areaId;
            
            const fileInput = area.querySelector('input[type="file"]') || document.getElementById('featured-image-input');
            const imagePreview = area.querySelector('img') || document.getElementById('imagePreview');
            const uploadDefault = area.querySelector('.upload-default') || document.getElementById('uploadDefault');
            const uploadPreview = area.querySelector('.upload-preview') || document.getElementById('uploadPreview');
            const fileName = area.querySelector('#fileName') || document.getElementById('fileName');
            const uploadProgress = area.querySelector('.upload-progress') || document.getElementById('uploadProgress');
            const progressBar = area.querySelector('.progress-bar') || document.getElementById('progressBar');
            const progressText = area.querySelector('#progressText') || document.getElementById('progressText');
            const removeImageField = document.getElementById('removeFeaturedImage');
            const keepImageCheckbox = document.getElementById('keep_current_image');
            
            window.imageUploader = new ImageUploader({
                uploadAreaId: areaId,
                fileInputId: fileInput?.id,
                imagePreviewId: imagePreview?.id,
                uploadDefaultId: uploadDefault?.id,
                uploadPreviewId: uploadPreview?.id,
                fileNameId: fileName?.id,
                uploadProgressId: uploadProgress?.id,
                progressBarId: progressBar?.id,
                progressTextId: progressText?.id,
                removeImageFieldId: removeImageField?.id,
                keepImageCheckboxId: keepImageCheckbox?.id
            });
        });
    });

    window.removeImage = function() {
        if (window.imageUploader) {
            window.imageUploader.removeImage();
        }
    };

    window.handleReplaceImage = function() {
        if (window.imageUploader) {
            window.imageUploader.handleReplaceImage();
        }
    };
}