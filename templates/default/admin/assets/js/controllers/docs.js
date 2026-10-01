document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('docsSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.docs-nav-item');
            const groups = document.querySelectorAll('.docs-nav-group');

            items.forEach(function(item) {
                const text = item.textContent.toLowerCase();
                if (!query || text.includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });

            groups.forEach(function(group) {
                const visibleItems = group.querySelectorAll('.docs-nav-item:not([style*="display: none"])');
                group.style.display = (visibleItems.length > 0) ? '' : 'none';
            });
        });
    }
});
