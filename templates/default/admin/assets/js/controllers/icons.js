function filterIcons(query) {
    query = query.toLowerCase();
    document.querySelectorAll(".icon-item").forEach(item => {
        const iconId = item.getAttribute("data-icon-id").toLowerCase();
        item.style.display = iconId.includes(query) ? "" : "none";
    });
}

function copyIconCode(code) {
    const textarea = document.createElement('textarea');
    textarea.value = code;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    textarea.style.pointerEvents = 'none';
    document.body.appendChild(textarea);
    textarea.select();
    textarea.setSelectionRange(0, 99999);
    
    try {
    document.execCommand('copy');
        if (window.notificationSystem) {
            window.notificationSystem.showNotification(lang === 'ru' ? 'Код иконки скопирован в буфер обмена' : 'Icon code copied to clipboard', 'success');
        }
    } catch (err) {
        console.error(lang === 'ru' ? 'Ошибка копирования:' : 'Copy error:', err);
        if (window.notificationSystem) {
            window.notificationSystem.showNotification(lang === 'ru' ? 'Не удалось скопировать код' : 'Failed to copy code', 'danger');
        }
    }
    
    document.body.removeChild(textarea);
}

document.addEventListener("DOMContentLoaded", function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length) {
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }

    const activeTab = localStorage.getItem("activeIconTab");
    if (activeTab) {
        const tabElement = document.querySelector(activeTab);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }
    
    document.querySelectorAll('#iconTabs button').forEach(button => {
        button.addEventListener("click", function() {
            localStorage.setItem("activeIconTab", `#${this.id}`);
        });
    });
});