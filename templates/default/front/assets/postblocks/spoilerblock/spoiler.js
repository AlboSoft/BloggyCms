document.addEventListener('DOMContentLoaded', function() {
    initSpoilers();
});

function initSpoilers() {
    const spoilers = document.querySelectorAll('[data-spoiler]');
    
    spoilers.forEach(function(spoiler) {
        const toggle = spoiler.querySelector('.spoiler-toggle');
        const content = spoiler.querySelector('.spoiler-content');
        
        if (!toggle || !content) return;
        
        const isOpen = content.classList.contains('show');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        content.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        
        if (isOpen) {
            content.style.maxHeight = content.scrollHeight + 'px';
            content.style.opacity = '1';
            content.style.transform = 'translateY(0)';
        }
        
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSpoiler(spoiler, toggle, content);
        });
    });
}

function toggleSpoiler(spoiler, toggle, content) {
    const isOpen = content.classList.contains('show');
    const hasAnimation = !spoiler.classList.contains('no-animation');
    const motionReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    const shouldAnimate = hasAnimation && !motionReduced;
    
    if (isOpen) {
        if (shouldAnimate) {
            content.style.opacity = '0';
            content.style.transform = 'translateY(-8px)';
            
            setTimeout(function() {
                content.style.maxHeight = '0';
            }, 200);
            
            setTimeout(function() {
                content.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
                content.setAttribute('aria-hidden', 'true');
            }, 400);
        } else {
            content.classList.remove('show');
            content.style.maxHeight = '0';
            content.style.opacity = '0';
            content.style.transform = 'translateY(-8px)';
            toggle.setAttribute('aria-expanded', 'false');
            content.setAttribute('aria-hidden', 'true');
        }
    } else {
        content.classList.add('show');
        toggle.setAttribute('aria-expanded', 'true');
        content.setAttribute('aria-hidden', 'false');
        
        if (shouldAnimate) {
            content.style.maxHeight = '0';
            content.style.opacity = '0';
            content.style.transform = 'translateY(-8px)';
            
            requestAnimationFrame(function() {
                const height = content.scrollHeight;
                
                requestAnimationFrame(function() {
                    content.style.maxHeight = height + 'px';
                    content.style.opacity = '1';
                    content.style.transform = 'translateY(0)';
                });
            });
        } else {
            content.style.maxHeight = content.scrollHeight + 'px';
            content.style.opacity = '1';
            content.style.transform = 'translateY(0)';
        }
    }
}