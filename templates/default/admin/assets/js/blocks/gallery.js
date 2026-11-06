class GalleryBlockAdmin {
    constructor(container = document) {
        this.container = container;
        this.itemsContainer = this.container.querySelector('#gallery-items-container');
        this.addButton = this.container.querySelector('#add-gallery-item');
        this.template = this.container.querySelector('#gallery-template');
        
        if (this.itemsContainer && !this.itemsContainer.hasAttribute('data-initialized')) {
            this.itemsContainer.setAttribute('data-initialized', 'true');
            this.init();
        }
    }

    init() {
        this.bindEvents();
        this.initSortable();
        this.updateIndices();
        this.attachPreviewToExisting();
    }

    bindEvents() {
        if (this.addButton && !this.addButton.hasAttribute('data-event-bound')) {
            this.addButton.addEventListener('click', () => this.addGalleryItem());
            this.addButton.setAttribute('data-event-bound', 'true');
        }

        if (this.itemsContainer && !this.itemsContainer.hasAttribute('data-event-bound')) {
            this.itemsContainer.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-gallery-item');
                if (removeBtn) {
                    this.removeGalleryItem(removeBtn.closest('.gallery-item'));
                }
            });
            
            this.itemsContainer.addEventListener('change', (e) => {
                const fileInput = e.target.closest('.gallery-image-input');
                if (fileInput) {
                    this.showImagePreview(fileInput);
                }
            });
            
            this.itemsContainer.setAttribute('data-event-bound', 'true');
        }
    }

    initSortable() {
        if (typeof Sortable !== 'undefined' && this.itemsContainer) {
            try {
                new Sortable(this.itemsContainer, {
                    handle: '.gallery-item-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    animation: 150,
                    onEnd: () => this.updateIndices()
                });
            } catch (e) {}
        }
    }

    attachPreviewToExisting() {
        if (!this.itemsContainer) return;
        
        this.itemsContainer.querySelectorAll('.gallery-image-input').forEach(input => {
            if (input.files && input.files.length > 0 && input.files[0]) {
                this.showImagePreview(input);
            }
        });
    }

    addGalleryItem() {
        if (!this.itemsContainer || !this.template) return;
        
        let nextIndex = this.itemsContainer.querySelectorAll('.gallery-item').length;
        let html = this.template.innerHTML.replace(/__INDEX__/g, nextIndex);
        
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newItem = temp.firstElementChild;
        
        this.itemsContainer.appendChild(newItem);
        this.updateIndices();
        
        const altInput = newItem.querySelector('input[name*="[alt_text]"]');
        if (altInput) altInput.focus();
    }

    removeGalleryItem(itemElement) {
        if (!this.itemsContainer) return;
        
        const items = this.itemsContainer.querySelectorAll('.gallery-item');
        if (items.length > 1) {
            itemElement.remove();
            this.updateIndices();
        } else {
            alert('Нельзя удалить последнее изображение');
        }
    }

    showImagePreview(fileInput) {
        if (!fileInput || !fileInput.files || !fileInput.files[0]) return;
        
        const item = fileInput.closest('.gallery-item');
        const previewContainer = item?.querySelector('.new-image-preview');
        const previewImg = previewContainer?.querySelector('.preview-image');
        
        if (previewImg) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
        
    }

    updateIndices() {
        if (!this.itemsContainer) return;
        
        const items = this.itemsContainer.querySelectorAll('.gallery-item');
        
        items.forEach((item, idx) => {
            item.setAttribute('data-index', idx);
            
            const fileInput = item.querySelector('.gallery-image-input');
            if (fileInput) fileInput.name = fileInput.name.replace(/gallery_image_\d+/, `gallery_image_${idx}`);
            
            const urlInput = item.querySelector('.gallery-image-url');
            if (urlInput) urlInput.name = `content[images][${idx}][image_url]`;
            
            const altInput = item.querySelector('input[name*="[alt_text]"]');
            if (altInput) altInput.name = `content[images][${idx}][alt_text]`;
            
            const captionInput = item.querySelector('input[name*="[caption]"]');
            if (captionInput) captionInput.name = `content[images][${idx}][caption]`;
            
            const removeCheckbox = item.querySelector('input[type="checkbox"][name*="remove_gallery_image"]');
            if (removeCheckbox) {
                removeCheckbox.name = `remove_gallery_image_${idx}`;
                removeCheckbox.id = `removeImage${idx}`;
                const label = removeCheckbox.nextElementSibling;
                if (label) label.setAttribute('for', `removeImage${idx}`);
            }
        });
    }

    static reinitializeAll() {
        document.querySelectorAll('#gallery-items-container').forEach(container => {
            container.removeAttribute('data-initialized');
            if (container.closest('.modal')) {
                new GalleryBlockAdmin(container.closest('.modal'));
            } else {
                new GalleryBlockAdmin();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('gallery-items-container')) {
        new GalleryBlockAdmin();
    }
});

function initGalleryBlockInModal(modal) {
    if (modal) {
        const galleryContainer = modal.querySelector('#gallery-items-container');
        if (galleryContainer && !galleryContainer.hasAttribute('data-initialized')) {
            new GalleryBlockAdmin(modal);
            galleryContainer.setAttribute('data-initialized', 'true');
        }
    }
}

const galleryObserver = new MutationObserver(() => {
    if (document.querySelector('#gallery-items-container:not([data-initialized])')) {
        GalleryBlockAdmin.reinitializeAll();
    }
});
galleryObserver.observe(document.body, { childList: true, subtree: true });

if (typeof window !== 'undefined') {
    window.GalleryBlockAdmin = GalleryBlockAdmin;
    window.initGalleryBlockInModal = initGalleryBlockInModal;
}