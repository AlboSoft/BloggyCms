(() => {
    const ru = document.documentElement.lang.toLowerCase().startsWith('ru');
    const text = ru ? {
        drag: 'Перетащите изображение сюда', or: 'или', choose: 'Выбрать файл',
        replace: 'Заменить изображение', remove: 'Удалить изображение', current: 'Текущее изображение',
        invalid: 'Пожалуйста, выберите изображение'
    } : {
        drag: 'Drag an image here', or: 'or', choose: 'Select file',
        replace: 'Replace image', remove: 'Remove image', current: 'Current image',
        invalid: 'Please select an image'
    };

    function init(input) {
        if (input.dataset.imagePickerReady || input.id === 'featured-image-input') return;
        input.dataset.imagePickerReady = '1';
        const group = input.closest('[data-image-upload-group]');
        const legacy = group ? Array.from(group.querySelectorAll('[data-image-upload-legacy]')) : [];
        const remove = group && group.querySelector('[data-image-upload-legacy] input[type="checkbox"]');
        const currentImage = remove && legacy.map(el => el.querySelector('img[src]')).find(img => img && img.getAttribute('src'));
        const currentUrl = (group && group.dataset.imageUploadCurrent) || (currentImage && currentImage.getAttribute('src')) || '';
        const initiallyRequired = input.required;
        const initiallyRemoved = !!(remove && remove.checked);
        let revision = 0;

        const area = document.createElement('div');
        area.className = 'shared-image-upload border-2 border-dashed rounded-3 text-center position-relative';
        if (group && group.dataset.imageUploadSize === 'large') area.classList.add('shared-image-upload-large');
        area.innerHTML = `<div class="shared-image-default">
            <div class="mb-2"><i class="bi bi-cloud-arrow-up shared-image-icon" aria-hidden="true"></i></div>
            <div class="text-muted mb-1 shared-image-drag"></div>
            <p class="text-muted small mb-2 shared-image-or"></p>
            <button type="button" class="btn btn-primary btn-sm shared-image-select"></button>
        </div>
        <div class="shared-image-preview d-none">
            <div class="position-relative d-inline-block">
                <img class="rounded shadow-sm" alt="">
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 shared-image-remove"><i class="bi bi-x" aria-hidden="true"></i></button>
            </div>
            <div class="mt-2"><small class="text-muted shared-image-name"></small></div>
            <div class="mt-2"><button type="button" class="btn btn-outline-primary btn-sm shared-image-replace"><i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i><span></span></button></div>
        </div>`;
        area.querySelector('.shared-image-drag').textContent = text.drag;
        area.querySelector('.shared-image-or').textContent = text.or;
        area.querySelector('.shared-image-select').textContent = text.choose;
        area.querySelector('.shared-image-replace span').textContent = text.replace;
        const removeButton = area.querySelector('.shared-image-remove');
        removeButton.title = text.remove;
        removeButton.setAttribute('aria-label', text.remove);
        const image = area.querySelector('img');
        image.alt = text.current;
        const name = area.querySelector('.shared-image-name');
        const empty = area.querySelector('.shared-image-default');
        const preview = area.querySelector('.shared-image-preview');
        input.before(area);
        input.classList.add('shared-image-input');
        if (group) group.classList.add('image-upload-enhanced');

        function show(url, label) {
            image.src = url || '';
            name.textContent = label || '';
            empty.classList.toggle('d-none', !!url);
            preview.classList.toggle('d-none', !url);
            input.required = initiallyRequired && !url;
        }
        function select() { input.click(); }
        area.querySelector('.shared-image-select').addEventListener('click', select);
        area.querySelector('.shared-image-replace').addEventListener('click', select);
        removeButton.addEventListener('click', () => {
            revision++;
            input.value = '';
            if (remove) remove.checked = true;
            show('', '');
        });
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            queueMicrotask(() => {
                if (!input.files || input.files[0] !== file) {
                    revision++;
                    show(remove && remove.checked ? '' : currentUrl, text.current);
                    return;
                }
                if (!file.type.startsWith('image/') && !/\.(ico|svg)$/i.test(file.name)) {
                    input.value = '';
                    revision++;
                    show(remove && remove.checked ? '' : currentUrl, text.current);
                    alert(text.invalid);
                    return;
                }
                if (remove) remove.checked = false;
                const id = ++revision;
                const reader = new FileReader();
                reader.onload = () => { if (id === revision) show(reader.result, file.name); };
                reader.readAsDataURL(file);
            });
        });
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(type => area.addEventListener(type, e => {
            e.preventDefault();
            e.stopPropagation();
            if (type === 'dragenter' || type === 'dragover') area.classList.add('dragover');
            else area.classList.remove('dragover');
            if (type === 'drop' && e.dataTransfer.files.length) {
                if (input.multiple) input.files = e.dataTransfer.files;
                else {
                    const files = new DataTransfer();
                    files.items.add(e.dataTransfer.files[0]);
                    input.files = files.files;
                }
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }));
        if (input.form) input.form.addEventListener('reset', () => setTimeout(() => {
            revision++;
            if (remove) remove.checked = initiallyRemoved;
            show(initiallyRemoved ? '' : currentUrl, text.current);
        }, 0));
        show(initiallyRemoved ? '' : currentUrl, text.current);
    }
    function scan(root) {
        if (root.nodeType !== 1 && root.nodeType !== 9) return;
        if (root.matches && root.matches('input[type="file"][accept*="image"]')) init(root);
        root.querySelectorAll('input[type="file"][accept*="image"]').forEach(init);
    }
    function start() {
        scan(document);
        new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(scan)))
            .observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();