document.addEventListener('DOMContentLoaded', function() {
    const useCustomCheckbox = document.getElementById('use_custom_template');
    const customContainer = document.getElementById('custom-template-container');
    const templateContainer = document.getElementById('template-select-container');
    const templateSelect = document.querySelector('select[name="template"]');
    let customEditor = null;
    
    function initCustomEditor() {
        if (typeof ace === 'undefined') {
            setTimeout(initCustomEditor, 200);
            return;
        }
        
        if (customEditor) {
            customEditor.destroy();
        }
        
        customEditor = ace.edit("custom-template-editor", {
            theme: "ace/theme/monokai",
            mode: "ace/mode/html",
            showPrintMargin: false,
            fontSize: "14px",
            tabSize: 2,
            useSoftTabs: true,
            wrap: true,
            minLines: 20,
            maxLines: 40
        });
        
        const textarea = document.getElementById('custom_template');
        if (textarea && textarea.value) {
            customEditor.setValue(textarea.value);
        } else {
            customEditor.setValue(`<ul class="custom-menu">
    {li}
        <li class="{active_class}">
            <a href="{url}" target="{target}" class="{class}">
                {icon}<span>{title}</span>
                {desc}
            </a>
        </li>
    {/li}
    
    {li=sub}
        <li class="dropdown {active_class}">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                {icon}{title}
                {desc}
            </a>
            <ul class="dropdown-menu">
                {children}
            </ul>
        </li>
    {/li=sub}
    
    {li-extra}
        <li class="extra-item">
            <a href="{url}" class="btn btn-primary">
                {icon}{title}
                {desc}
            </a>
        </li>
    {/li-extra}
</ul>`);
        }
        
        customEditor.clearSelection();
        
        const form = document.getElementById('menu-form');
        if (form && !form._editorHandler) {
            form.addEventListener('submit', function() {
                if (customEditor) {
                    document.getElementById('custom_template').value = customEditor.getValue();
                }
            });
            form._editorHandler = true;
        }
    }
    
    function toggleCustomTemplate(useCustom) {
        if (useCustom) {
            customContainer.style.display = 'block';
            templateContainer.style.display = 'none';
            if (templateSelect) {
                templateSelect.removeAttribute('required');
                templateSelect.disabled = true;
            }
            initCustomEditor();
        } else {
            customContainer.style.display = 'none';
            templateContainer.style.display = 'block';
            if (templateSelect) {
                templateSelect.setAttribute('required', 'required');
                templateSelect.disabled = false;
            }
        }
    }
    
    if (useCustomCheckbox) {
        useCustomCheckbox.addEventListener('change', function(e) {
            toggleCustomTemplate(e.target.checked);
        });
        
        toggleCustomTemplate(useCustomCheckbox.checked);
    }
});