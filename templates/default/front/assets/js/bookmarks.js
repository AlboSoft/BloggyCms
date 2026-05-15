class BookmarksManager {
    constructor() {
        this.init();
    }
    
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setupRemoveHandlers();
        });
    }
    
    setupRemoveHandlers() {
        const buttons = document.querySelectorAll('.remove-bookmark');

        buttons.forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
        });
        
        document.querySelectorAll('.remove-bookmark').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleRemove(e, btn);
            });
        });
        
    }
    
    async handleRemove(e, button) {
        e.preventDefault();
        e.stopPropagation();
        
        const postId = button.dataset.postId;
        const card = button.closest('.bookmark-item');
        const originalHTML = button.innerHTML;
        button.innerHTML = '⌛';
        button.disabled = true;
        
        try {
            const response = await fetch(`${window.baseUrl}/post/bookmark/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.success && !data.bookmarked) {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateX(-100px)';
                card.style.height = '0';
                card.style.margin = '0';
                card.style.padding = '0';
                card.style.overflow = 'hidden';
                
                setTimeout(() => {
                    card.remove();
                    this.updateCounter();
                    
                    if (document.querySelectorAll('.bookmark-item').length === 0) {
                        setTimeout(() => location.reload(), 500);
                    }
                }, 400);
                
            } else {
                button.innerHTML = originalHTML;
                button.disabled = false;
            }
            
        } catch (error) {
            button.innerHTML = originalHTML;
            button.disabled = false;
        }
    }
    
    updateCounter() {
        const counter = document.querySelector('.text-muted.small.mb-0');
        if (counter) {
            const count = document.querySelectorAll('.bookmark-item').length;
            counter.textContent = `📚 ${count} ${lang === 'ru' ? 'сохранённых публикаций' : 'saved publications'}`;
        }
    }
}

const bookmarksManager = new BookmarksManager();