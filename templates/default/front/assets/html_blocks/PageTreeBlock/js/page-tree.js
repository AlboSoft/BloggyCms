(function() {
    'use strict';
    
    function initAccordion() {
        const accordions = document.querySelectorAll('.page-tree-accordion');
        
        accordions.forEach(accordion => {
            const items = accordion.querySelectorAll('.accordion-item');
            
            items.forEach(item => {
                const header = item.querySelector('.accordion-header');
                
                if (header) {
                    header.addEventListener('click', (e) => {
                        if (e.target.closest('.accordion-link')) {
                            return;
                        }
                        
                        item.classList.toggle('open');
                    });
                }
            });
        });
    }
    
    function highlightCurrentPage() {
        const currentUrl = window.location.pathname;
        const links = document.querySelectorAll('.page-tree-tree .tree-link, .page-tree-accordion .accordion-link');
        
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href !== '#' && href === currentUrl) {
                link.classList.add('active');
                
                let parent = link.closest('.accordion-item');
                while (parent) {
                    parent.classList.add('open');
                    parent = parent.parentElement?.closest('.accordion-item');
                }
            }
        });
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        initAccordion();
        highlightCurrentPage();
    });
})();