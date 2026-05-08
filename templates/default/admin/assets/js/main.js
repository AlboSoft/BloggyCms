const lang = document.querySelector('meta[name="admin-language"]')?.content || 'ru';

document.addEventListener('DOMContentLoaded', function() {
    initBootstrapComponents();
    initProfileDropdown();
    initMobileMenu();
    initNotifications();
    initEventHandlers();
});

function initBootstrapComponents() {
    const dropdowns = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdowns.map(function(dropdownToggle) {
        return new bootstrap.Dropdown(dropdownToggle);
    });

    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    const toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 5000
        });
    });
}

function initProfileDropdown() {
    const profileDropdown = document.querySelector('.dropdown-toggle[data-bs-toggle="dropdown"]');
    
    if (profileDropdown) {
        document.addEventListener('click', function(event) {
            const isClickInside = profileDropdown.contains(event.target) || 
                                profileDropdown.nextElementSibling.contains(event.target);
            
            if (!isClickInside) {
                const dropdown = bootstrap.Dropdown.getInstance(profileDropdown);
                if (dropdown) {
                    dropdown.hide();
                }
            }
        });

        profileDropdown.addEventListener('show.bs.dropdown', function() {
            const dropdownMenu = this.nextElementSibling;
            dropdownMenu.style.opacity = '0';
            dropdownMenu.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                dropdownMenu.style.transition = 'all 0.3s ease';
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.transform = 'translateY(0)';
            }, 50);
        });

        profileDropdown.addEventListener('hide.bs.dropdown', function() {
            const dropdownMenu = this.nextElementSibling;
            dropdownMenu.style.transition = 'all 0.2s ease';
            dropdownMenu.style.opacity = '0';
            dropdownMenu.style.transform = 'translateY(-10px)';
        });
    }
}

function initMobileMenu() {
    const mobileMenuButton = document.querySelector('.mobile-menu-toggle');
    
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('mobile-open');
            document.querySelector('.admin-wrapper').classList.toggle('mobile-menu-open');
        });
    }
}

function initNotifications() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        toast.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    });
}

function initEventHandlers() {
    const templateSelectors = document.querySelectorAll('.template-selector');
    if (templateSelectors) {
        templateSelectors.forEach(item => {
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
    }

    const saveBtn = document.getElementById('saveFile');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveFile);
    }
    
    const refreshBtn = document.getElementById('refreshFile');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            if (typeof currentFile !== 'undefined' && currentFile) {
                loadFileContent(currentTemplate, currentFile);
            }
        });
    }

    const refreshFilesBtn = document.getElementById('refreshFiles');
    if (refreshFilesBtn) {
        refreshFilesBtn.addEventListener('click', function() {
            loadTemplateFiles(currentTemplate);
        });
    }

    if (typeof editor !== 'undefined' && editor && editor.session) {
        editor.session.on('change', function() {
            const saveBtn = document.getElementById('saveFile');
            if (saveBtn) {
                saveBtn.disabled = false;
            }
        });
    }
}

function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container');
    const toastId = 'toast-' + Date.now();
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function loadContent(url, container) {
    if (!container) container = document.querySelector('.content-wrapper');
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            initBootstrapComponents();
            initEventHandlers();
        })
        .catch(error => {
            showToast(lang === 'ru' ? 'Ошибка загрузки контента' : 'Error loading content', 'danger');
        });
}

function setLoadingState(isLoading) {
    const loader = document.getElementById('global-loader') || createGlobalLoader();
    
    if (isLoading) {
        loader.style.display = 'flex';
    } else {
        loader.style.display = 'none';
    }
}

function createGlobalLoader() {
    const loader = document.createElement('div');
    loader.id = 'global-loader';
    loader.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">${lang === 'ru' ? 'Загрузка...' : 'Loading...'}</span>
        </div>
    `;
    loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    document.body.appendChild(loader);
    return loader;
}

document.getElementById('faviconInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const validTypes = ['image/x-icon', 'image/png', 'image/svg+xml', 'image/vnd.microsoft.icon'];
        const validExtensions = ['.ico', '.png', '.svg'];
        
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
            alert(lang === 'ru' ? 'Пожалуйста, выберите файл в формате ICO, PNG или SVG' : 'Please select a file in ICO, PNG or SVG format');
            e.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('faviconPreview').src = event.target.result;
            document.getElementById('faviconFormat').textContent = fileExtension.substring(1).toUpperCase();
        };
        reader.readAsDataURL(file);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('page-title') || 
                       document.querySelector('input[name="title"]');
    const slugInput = document.getElementById('page-slug');
    const generateBtn = document.getElementById('generate-slug-btn');
    const form = document.getElementById('page-form');
    
    if (titleInput && slugInput && generateBtn) {
        function generateSlug(text) {
            const converter = {
                'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'e','ж':'zh','з':'z',
                'и':'i','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r',
                'с':'s','т':'t','у':'u','ф':'f','х':'h','ц':'c','ч':'ch','ш':'sh','щ':'sch',
                'ь':'','ы':'y','ъ':'','э':'e','ю':'yu','я':'ya',
                'А':'A','Б':'B','В':'V','Г':'G','Д':'D','Е':'E','Ё':'E','Ж':'Zh','З':'Z',
                'И':'I','Й':'Y','К':'K','Л':'L','М':'M','Н':'N','О':'O','П':'P','Р':'R',
                'С':'S','Т':'T','У':'U','Ф':'F','Х':'H','Ц':'C','Ч':'Ch','Ш':'Sh','Щ':'Sch',
                'Ь':'','Ы':'Y','Ъ':'','Э':'E','Ю':'Yu','Я':'Ya'
            };
            
            let slug = text.toLowerCase();
            slug = slug.split('').map(char => converter[char] || char).join('');
            slug = slug.replace(/[^a-z0-9\s-]/g, '');
            slug = slug.trim().replace(/\s+/g, '-').replace(/-+/g, '-');
            return slug;
        }
        
        generateBtn.addEventListener('click', function() {
            const title = titleInput.value.trim();
            if (title) {
                slugInput.value = generateSlug(title);
                slugInput.dataset.autoGenerated = 'true';
            }
        });
        
        titleInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                slugInput.value = generateSlug(this.value);
                slugInput.dataset.autoGenerated = 'true';
            }
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
    }
    
    if (form && slugInput) {
        form.addEventListener('submit', function(e) {
            const slug = slugInput.value.trim();
            if (slug && !/^[a-z0-9-]+$/.test(slug)) {
                e.preventDefault();
                alert(lang === 'ru' ? 'Slug должен содержать только латинские буквы, цифры и дефисы' : 'Slug can only contain Latin letters, numbers and hyphens');
                slugInput.focus();
                return false;
            }
        });
    }
});