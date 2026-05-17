document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'top',
            trigger: 'hover'
        });
    });
    
    const achievements = document.querySelectorAll('.achievement-item');
    achievements.forEach((achievement, index) => {
        setTimeout(() => {
            achievement.style.opacity = '0';
            achievement.style.transform = 'scale(0.5)';
            achievement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            setTimeout(() => {
                achievement.style.opacity = '1';
                achievement.style.transform = 'scale(1)';
            }, 10);
        }, index * 100);
    });
    
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        if (!isNaN(finalValue)) {
            animateCounter(stat, finalValue);
        }
    });
});

function animateCounter(element, finalValue) {
    let current = 0;
    const increment = finalValue / 30;
    const timer = setInterval(() => {
        current += increment;
        if (current >= finalValue) {
            element.textContent = finalValue;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 30);
}