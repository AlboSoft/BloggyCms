if (typeof TagsAutocomplete !== 'undefined') {

} else { 
    
    class TagsAutocomplete {
        constructor() {
            this.tagSearch = document.getElementById('tag-search');
            this.tagsSuggestions = document.getElementById('tags-suggestions');
            this.tagsContainer = document.getElementById('tags-container');
            this.tagsJson = document.getElementById('tags-json');
            this.selectedTags = [];
            this.searchTimeout = null;
            this.adminUrl = window.ADMIN_URL || '/admin';
            this.maxTags = this.getMaxTags();
            
            this.init();
        }

        getMaxTags() {
            const cardBody = document.querySelector('.card-body[data-max-tags]');
            if (cardBody && cardBody.dataset.maxTags) {
                return parseInt(cardBody.dataset.maxTags);
            }
            
            const maxTagsElement = document.getElementById('max-tags-count');
            if (maxTagsElement && maxTagsElement.textContent) {
                return parseInt(maxTagsElement.textContent);
            }
            
            return window.MAX_TAGS_PER_POST || 10;
        }

        init() {
            if (!this.validateElements()) {
                return;
            }
            this.loadInitialTags();
            this.setupEventListeners();
            this.renderSelectedTags();
            this.updateTagsCounter();
        }

        validateElements() {
            return !!(this.tagSearch && this.tagsSuggestions && this.tagsContainer && this.tagsJson);
        }

        updateTagsCounter() {
            const currentCount = this.selectedTags.length;
            const counterElement = document.getElementById('tags-counter');
            const currentCountElement = document.getElementById('current-tags-count');
            const maxTagsElement = document.getElementById('max-tags-count');
            
            if (counterElement && currentCountElement && maxTagsElement) {
                currentCountElement.textContent = currentCount;
                const currentMaxTags = this.getMaxTags();
                maxTagsElement.textContent = currentMaxTags;
                this.maxTags = currentMaxTags;
                
                if (currentCount >= this.maxTags) {
                    counterElement.className = 'badge bg-danger';
                    this.tagSearch.disabled = true;
                    this.tagSearch.placeholder = lang === 'ru' ? 'Достигнут лимит тегов' : 'Tag limit reached';
                } else if (currentCount >= this.maxTags - 2) {
                    counterElement.className = 'badge bg-warning text-dark';
                    this.tagSearch.disabled = false;
                    this.tagSearch.placeholder = lang === 'ru' ? 'Начните вводить название тега...' : 'Start typing tag name...';
                } else {
                    counterElement.className = 'badge bg-light text-dark';
                    this.tagSearch.disabled = false;
                    this.tagSearch.placeholder = lang === 'ru' ? 'Начните вводить название тега...' : 'Start typing tag name...';
                }
            }
        }

        loadInitialTags() {
            try {
                const existingTags = this.collectExistingTags();
                
                const tagIds = JSON.parse(this.tagsJson.value || '[]');
                this.selectedTags = tagIds.map(tagId => ({
                    id: tagId,
                    name: existingTags[tagId] || `${lang === 'ru' ? 'Тег' : 'Tag'} ${tagId}`
                }));
                
            } catch (e) {
                this.selectedTags = [];
            }
        }

        collectExistingTags() {
            const existingTags = {};
            document.querySelectorAll('.tag-badge[data-tag-id]').forEach(badge => {
                const tagId = badge.dataset.tagId;
                const tagName = badge.textContent.trim().replace('×', '').trim();
                existingTags[tagId] = tagName;
            });
            return existingTags;
        }

        setupEventListeners() {
            this.tagSearch.addEventListener('input', this.handleInput.bind(this));
            this.tagSearch.addEventListener('focus', this.handleFocus.bind(this));
            this.tagSearch.addEventListener('keydown', this.handleKeydown.bind(this));
            
            document.addEventListener('click', this.handleDocumentClick.bind(this));
        }

        handleInput() {
            if (this.selectedTags.length >= this.maxTags) {
                this.hideSuggestions();
                return;
            }
            
            clearTimeout(this.searchTimeout);
            const query = this.tagSearch.value.trim();
            
            if (query.length >= 2) {
                this.searchTimeout = setTimeout(() => {
                    this.searchTags(query);
                }, 300);
            } else {
                this.hideSuggestions();
            }
        }

        handleFocus() {
            if (this.selectedTags.length >= this.maxTags) {
                this.hideSuggestions();
                return;
            }
            
            const query = this.tagSearch.value.trim();
            if (query.length >= 2) {
                this.searchTags(query);
            }
        }

        handleKeydown(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (this.selectedTags.length >= this.maxTags) {
                    alert(lang === 'ru' ? `Максимальное количество тегов: ${this.maxTags}` : `Maximum number of tags: ${this.maxTags}`);
                    return;
                }
                
                const query = this.tagSearch.value.trim();
                if (query.length > 0) {
                    this.createNewTag(query);
                }
            }
            
            if (e.key === 'Escape') {
                this.hideSuggestions();
            }
        }

        handleDocumentClick(e) {
            if (!this.tagSearch.contains(e.target) && !this.tagsSuggestions.contains(e.target)) {
                this.hideSuggestions();
            }
        }

        updateHiddenField() {
            const tagIds = this.selectedTags.map(tag => tag.id);
            this.tagsJson.value = JSON.stringify(tagIds);
            this.updateTagsCounter();
        }

        renderSelectedTags() {
            this.tagsContainer.innerHTML = '';
            
            this.selectedTags.forEach(tag => {
                const tagBadge = this.createTagBadge(tag);
                this.tagsContainer.appendChild(tagBadge);
            });
            
            this.updateHiddenField();
        }

        createTagBadge(tag) {
            const tagBadge = document.createElement('span');
            tagBadge.className = 'badge bg-primary me-2 mb-2 tag-badge';
            tagBadge.innerHTML = `
                ${tag.name}
                <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 0.7rem;" aria-label="${lang === 'ru' ? 'Удалить' : 'Delete'}"></button>
            `;
            tagBadge.dataset.tagId = tag.id;
            
            tagBadge.querySelector('button').addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.removeTag(tag.id);
            });
            
            return tagBadge;
        }

        removeTag(tagId) {
            this.selectedTags = this.selectedTags.filter(tag => tag.id !== tagId);
            this.renderSelectedTags();
        }

        addExistingTag(tagId, tagName) {
            if (this.selectedTags.length >= this.maxTags) {
                alert(lang === 'ru' ? `Максимальное количество тегов: ${this.maxTags}` : `Maximum number of tags: ${this.maxTags}`);
                return;
            }
            
            if (!this.selectedTags.some(tag => tag.id === tagId)) {
                this.selectedTags.push({ id: tagId, name: tagName });
                this.renderSelectedTags();
            }
            
            this.tagSearch.value = '';
            this.hideSuggestions();
        }

        async createNewTag(tagName) {
            if (this.selectedTags.length >= this.maxTags) {
                alert(lang === 'ru' ? `Максимальное количество тегов: ${this.maxTags}` : `Maximum number of tags: ${this.maxTags}`);
                return;
            }
            
            this.showLoading(tagName);
            
            try {
                const response = await fetch(`${this.adminUrl}/tags/create-ajax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `name=${encodeURIComponent(tagName)}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success && data.tag) {
                    this.selectedTags.push({ 
                        id: data.tag.id, 
                        name: data.tag.name 
                    });
                    this.renderSelectedTags();
                    this.tagSearch.value = '';
                } else {
                    this.showError(data.message || (lang === 'ru' ? 'Ошибка создания тега' : 'Error creating tag'));
                }
            } catch (error) {
                this.showError(lang === 'ru' ? 'Ошибка создания тега. Проверите консоль для подробностей.' : 'Error creating tag. Check console for details.');
            } finally {
                this.hideLoading();
                this.hideSuggestions();
            }
        }

        showLoading(tagName) {
            this.tagSearch.disabled = true;
            this.tagSearch.value = lang === 'ru' ? 'Создание тега...' : 'Creating tag...';
        }

        hideLoading() {
            this.tagSearch.disabled = false;
            this.updateTagsCounter();
        }

        showError(message) {
            alert(message);
        }

        showSuggestions(suggestions, showCreateOption = false, newTagName = '') {
            if (this.selectedTags.length >= this.maxTags) {
                this.tagsSuggestions.innerHTML = `<div class="dropdown-item text-muted">${lang === 'ru' ? 'Достигнут лимит тегов' : 'Tag limit reached'}</div>`;
                this.tagsSuggestions.style.display = 'block';
                return;
            }
            
            this.tagsSuggestions.innerHTML = '';
            
            if (suggestions.length === 0 && !showCreateOption) {
                this.tagsSuggestions.innerHTML = `<div class="dropdown-item text-muted">${lang === 'ru' ? 'Теги не найдены' : 'No tags found'}</div>`;
            } else {
                suggestions.forEach(tag => {
                    const item = this.createSuggestionItem(tag);
                    this.tagsSuggestions.appendChild(item);
                });
                
                if (showCreateOption && newTagName) {
                    this.addCreateOption(newTagName);
                }
            }
            
            this.tagsSuggestions.style.display = 'block';
        }

        createSuggestionItem(tag) {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'dropdown-item';
            item.innerHTML = `
                <i class="bi bi-tag me-2"></i>
                ${tag.name}
                <small class="text-muted ms-2">${tag.slug}</small>
            `;
            item.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.addExistingTag(tag.id, tag.name);
            });
            return item;
        }

        addCreateOption(newTagName) {
            const separator = document.createElement('div');
            separator.className = 'dropdown-divider';
            this.tagsSuggestions.appendChild(separator);
            
            const createItem = document.createElement('button');
            createItem.type = 'button';
            createItem.className = 'dropdown-item text-success';
            createItem.innerHTML = `
                <i class="bi bi-plus-circle me-2"></i>
                ${lang === 'ru' ? 'Создать новый тег:' : 'Create new tag:'} "${newTagName}"
            `;
            createItem.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.createNewTag(newTagName);
            });
            this.tagsSuggestions.appendChild(createItem);
        }

        hideSuggestions() {
            this.tagsSuggestions.style.display = 'none';
        }

        async searchTags(query) {
            if (this.selectedTags.length >= this.maxTags) {
                this.hideSuggestions();
                return;
            }
            
            if (query.length < 2) {
                this.hideSuggestions();
                return;
            }

            const url = `${this.adminUrl}/tags/search?q=${encodeURIComponent(query)}`;

            try {
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const tags = await response.json();
                
                const exactMatch = tags.some(tag => 
                    tag.name.toLowerCase() === query.toLowerCase()
                );
                
                const showCreateOption = !exactMatch && query.length > 1;
                this.showSuggestions(tags, showCreateOption, query);
                
            } catch (error) {
                this.hideSuggestions();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        new TagsAutocomplete();
    });
}