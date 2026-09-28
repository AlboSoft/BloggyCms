(function () {
    'use strict';

    var LOCALE = (function () {
        try {
            if (typeof lang !== 'undefined' && lang) return String(lang);
        } catch (e) { /* main.js ещё не загружен */ }
        var meta = document.querySelector('meta[name="admin-language"]');
        return meta && meta.content ? meta.content : 'ru';
    })();

    var I18N = {
        ru: {
            blocks: 'Блоки',
            words: 'слов',
            readTime: 'мин чтения',
            addBlock: 'Блок',
            canvas: 'Полотно',
            outline: 'Структура',
            undo: 'Отменить',
            redo: 'Вернуть',
            search: 'Поиск по контенту…',
            save: 'Сохранить',
            saved: 'Черновик сохранён',
            unsaved: 'Есть несохранённые изменения',
            allSaved: 'Нет изменений',
            emptyTitle: 'Контент пока пуст',
            emptyText: 'Наведите на линию между блоками и нажмите «+», нажмите клавишу / или начните с готового шаблона.',
            emptyAdd: 'Добавить первый блок',
            templates: 'Готовые наборы',
            pickerTitle: 'Добавить блок',
            pickerPlaceholder: 'Найдите блок… например: текст, картинка, видео',
            recent: 'Недавние',
            nothingFound: 'Ничего не найдено',
            pasteBlock: 'Вставить скопированный блок',
            navHint: 'навигация',
            enterHint: 'добавить',
            escHint: 'закрыть',
            insertHere: 'Добавить блок',
            drag: 'Перетащить',
            moveUp: 'Выше',
            moveDown: 'Ниже',
            duplicate: 'Дублировать',
            settings: 'Настройки блока',
            remove: 'Удалить',
            removeConfirm: 'Удалить блок «%s»?',
            preset: 'Пресет',
            emptyBlock: 'не заполнен',
            blockAdded: 'Блок «%s» добавлен',
            blockRemoved: 'Блок удалён',
            blockDuplicated: 'Блок продублирован',
            blockCopied: 'Блок скопирован',
            blockPasted: 'Блок вставлен',
            blocksInserted: 'Добавлено блоков: %s',
            nothingToUndo: 'Нечего отменять',
            nothingToRedo: 'Нечего возвращать',
            changesReverted: 'Изменения блока отменены',
            inlineSaved: 'Текст обновлён',
            inlineEditHint: 'Двойной клик — редактировать текст прямо здесь',
            apply: 'Готово',
            revert: 'Отменить изменения',
            close: 'Закрыть',
            content: 'Контент',
            settingsTab: 'Настройки',
            presetsTab: 'Оформление',
            livePreview: 'Живое превью',
            formLoading: 'Загрузка формы…',
            formError: 'Не удалось загрузить форму блока',
            previewError: 'Ошибка превью',
            noPresets: 'Для этого блока пресеты не заданы. Их можно создать в разделе «Пост-блоки».',
            presetHint: 'Оформление блока применяется только к этой записи.',
            presetNone: '— Без пресета —',
            presetApplied: 'Пресет применён',
            draftTitle: 'Найден несохранённый черновик',
            draftText: 'Контент был изменён, но не сохранён. Восстановить его?',
            draftRestore: 'Восстановить',
            draftDiscard: 'Удалить',
            draftRestored: 'Черновик восстановлен',
            noBlocksAvailable: 'Нет доступных блоков. Включите их в разделе «Пост-блоки».',
            loading: 'Загрузка…',
            done: 'Готово',
            cancel: 'Отмена',
            blockInfo: 'Блок',
            of: 'из',
            copy: 'Копировать',
            structureEmpty: 'Блоков пока нет',
            filterHint: 'Совпадений: %s'
        },
        en: {
            blocks: 'Blocks',
            words: 'words',
            readTime: 'min read',
            addBlock: 'Block',
            canvas: 'Canvas',
            outline: 'Outline',
            undo: 'Undo',
            redo: 'Redo',
            search: 'Search in content…',
            save: 'Save',
            saved: 'Draft saved',
            unsaved: 'Unsaved changes',
            allSaved: 'No changes',
            emptyTitle: 'Nothing here yet',
            emptyText: 'Hover the line between blocks and press “+”, hit the / key, or start from a template.',
            emptyAdd: 'Add the first block',
            templates: 'Quick templates',
            pickerTitle: 'Add a block',
            pickerPlaceholder: 'Find a block… e.g. text, image, video',
            recent: 'Recent',
            nothingFound: 'Nothing found',
            pasteBlock: 'Paste copied block',
            navHint: 'navigate',
            enterHint: 'insert',
            escHint: 'close',
            insertHere: 'Add block',
            drag: 'Drag',
            moveUp: 'Move up',
            moveDown: 'Move down',
            duplicate: 'Duplicate',
            settings: 'Block settings',
            remove: 'Delete',
            removeConfirm: 'Delete the “%s” block?',
            preset: 'Preset',
            emptyBlock: 'empty',
            blockAdded: '“%s” block added',
            blockRemoved: 'Block deleted',
            blockDuplicated: 'Block duplicated',
            blockCopied: 'Block copied',
            blockPasted: 'Block pasted',
            blocksInserted: '%s blocks added',
            nothingToUndo: 'Nothing to undo',
            nothingToRedo: 'Nothing to redo',
            changesReverted: 'Block changes reverted',
            inlineSaved: 'Text updated',
            inlineEditHint: 'Double-click to edit the text right here',
            apply: 'Done',
            revert: 'Revert changes',
            close: 'Close',
            content: 'Content',
            settingsTab: 'Settings',
            presetsTab: 'Design',
            livePreview: 'Live preview',
            formLoading: 'Loading form…',
            formError: 'Could not load the block form',
            previewError: 'Preview error',
            noPresets: 'No presets for this block yet. Create them in “Post blocks”.',
            presetHint: 'The design preset applies to this post only.',
            presetNone: '— No preset —',
            presetApplied: 'Preset applied',
            draftTitle: 'Unsaved draft found',
            draftText: 'The content was changed but never saved. Restore it?',
            draftRestore: 'Restore',
            draftDiscard: 'Discard',
            draftRestored: 'Draft restored',
            noBlocksAvailable: 'No blocks available. Enable them in “Post blocks”.',
            loading: 'Loading…',
            done: 'Done',
            cancel: 'Cancel',
            blockInfo: 'Block',
            of: 'of',
            copy: 'Copy',
            structureEmpty: 'No blocks yet',
            filterHint: 'Matches: %s'
        }
    };

    function t(key) {
        var dict = I18N[LOCALE] || I18N.ru;
        return dict[key] || I18N.ru[key] || key;
    }

    function tf(key) {
        var args = Array.prototype.slice.call(arguments, 1);
        return t(key).replace(/%s/g, function () { return args.shift(); });
    }

    var CATEGORY_ORDER = ['basic', 'text', 'media', 'layout', 'advanced', 'other'];

    var CATEGORY_LABELS = {
        ru: { all: 'Все', basic: 'Основные', text: 'Текст', media: 'Медиа', layout: 'Компоновка', advanced: 'Расширенные', other: 'Прочее' },
        en: { all: 'All', basic: 'Basic', text: 'Text', media: 'Media', layout: 'Layout', advanced: 'Advanced', other: 'Other' }
    };

    var CATEGORY_ICONS = {
        basic: 'bi-box', text: 'bi-type', media: 'bi-image', layout: 'bi-layout-wtf', advanced: 'bi-cpu', other: 'bi-grid'
    };

    var QUICK_TEMPLATES = [
        { id: 'article', icon: 'bi-journal-text', ru: 'Статья', en: 'Article', types: ['HeaderBlock', 'TextBlock', 'ImageBlock', 'TextBlock'] },
        { id: 'longread', icon: 'bi-book', ru: 'Лонгрид', en: 'Longread', types: ['HeaderBlock', 'TextBlock', 'QuoteBlock', 'TextBlock', 'ListBlock'] },
        { id: 'review', icon: 'bi-list-check', ru: 'Подборка', en: 'Roundup', types: ['HeaderBlock', 'TextBlock', 'ListBlock', 'ButtonBlock'] },
        { id: 'media', icon: 'bi-images', ru: 'Галерея', en: 'Gallery', types: ['HeaderBlock', 'GalleryBlock', 'TextBlock'] },
        { id: 'video', icon: 'bi-play-btn', ru: 'Видео', en: 'Video', types: ['HeaderBlock', 'VideoBlock', 'TextBlock'] },
        { id: 'faq', icon: 'bi-question-circle', ru: 'FAQ', en: 'FAQ', types: ['HeaderBlock', 'SpoilerBlock', 'SpoilerBlock'] }
    ];

    var INLINE_EDITABLE = {
        TextBlock: { field: 'content', rich: true },
        HeaderBlock: { field: 'text', tag: 'h2' },
        QuoteBlock: { field: 'text' },
        AlertBlock: { field: 'content' },
        SpoilerBlock: { field: 'title' },
        ButtonBlock: { field: 'text' },
        ImageWithTextBlock: { field: 'text_content' },
        CodeBlock: { field: 'code', mono: true }
    };

    var STORE_PREFIX = 'bloggy.builder.';

    function uid() {
        return 'block_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 9);
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function stripTags(value) {
        if (value === null || value === undefined) return '';
        return String(value).replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function deepClone(value) {
        try {
            return JSON.parse(JSON.stringify(value));
        } catch (e) {
            return value;
        }
    }

    function hashOf(value) {
        var str = typeof value === 'string' ? value : JSON.stringify(value);
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            hash = (hash << 5) - hash + str.charCodeAt(i);
            hash |= 0;
        }
        return hash.toString(36);
    }

    function storeGet(key, fallback) {
        try {
            var raw = window.localStorage.getItem(STORE_PREFIX + key);
            return raw ? JSON.parse(raw) : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function storeSet(key, value) {
        try {
            window.localStorage.setItem(STORE_PREFIX + key, JSON.stringify(value));
            return true;
        } catch (e) {
            return false;
        }
    }

    function storeRemove(key) {
        try {
            window.localStorage.removeItem(STORE_PREFIX + key);
        } catch (e) { /* noop */ }
    }

    function debounce(fn, wait) {
        var timer = null;
        return function () {
            var args = arguments;
            var self = this;
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(self, args); }, wait);
        };
    }

    function isEditableTarget(target) {
        if (!target || !target.tagName) return false;
        var tag = target.tagName.toLowerCase();
        return tag === 'input' || tag === 'textarea' || tag === 'select' || target.isContentEditable === true;
    }

    function collectText(value) {
        if (value === null || value === undefined) return '';
        if (typeof value === 'string' || typeof value === 'number') return stripTags(value);
        if (Array.isArray(value)) return value.map(collectText).join(' ');
        if (typeof value === 'object') return Object.keys(value).map(function (k) { return collectText(value[k]); }).join(' ');
        return '';
    }

    function countWords(text) {
        var words = String(text).trim().split(/\s+/).filter(Boolean);
        return words.length;
    }

    class PostBlocksManager {
        constructor(options) {
            options = options || {};

            this.root = document.getElementById('post-builder');
            this.hiddenField = document.getElementById('post_blocks_data');
            this.available = window.availablePostBlocks || {};
            this.storageKey = (this.root && this.root.dataset.storageKey) || window.location.pathname || 'post';

            this.blocks = [];
            this.selectedId = null;
            this.view = 'canvas';
            this.search = '';
            this.matches = null;

            this.previewCache = new Map();
            this.pendingPreviewIds = new Set();
            this.formCache = new Map();
            this.presetsCache = new Map();
            this.previewQueue = [];
            this.previewRunning = false;

            this.history = { past: [], future: [] };
            this.inspectorSnapshot = null;
            this.inspectorBlockId = null;

            this.picker = null;
            this.pickerBackdrop = null;
            this.pickerIndex = 0;
            this.pickerInsertAt = 0;
            this.pickerCursor = null;

            this.dirty = false;
            this.lastDraftSerialized = null;
            this.autosaveTimer = null;
            this.toastTimer = null;

            this.ensureAdminUrl();
            this.buildChrome();
            this.loadInitialBlocks();
            this.bindEvents();
            this.render();
            this.initSortable();
            this.checkDraft();
            this.startAutosave();
        }

        ensureAdminUrl() {
            if (typeof window.ADMIN_URL === 'undefined' || !window.ADMIN_URL) {
                var path = window.location.pathname;
                window.ADMIN_URL = path.indexOf('/admin/') !== -1
                    ? path.split('/admin/')[0] + '/admin'
                    : '/admin';
            }
        }

        get hasBlocks() {
            return this.available && Object.keys(this.available).length > 0;
        }

        buildChrome() {
            if (!this.root) return;

            this.root.innerHTML = this.renderToolbar() + '<div class="pbe-canvas" id="pbe-canvas"></div>';
            this.canvas = this.root.querySelector('#pbe-canvas');
            this.toolbar = this.root.querySelector('.pbe-toolbar');
            this.statsEl = this.root.querySelector('#pbe-stats');
            this.savedEl = this.root.querySelector('#pbe-saved');
            this.searchWrap = this.root.querySelector('.pbe-search');
            this.noticeEl = this.root.querySelector('#pbe-notice');

            this.inspector = this.buildInspector();
            document.body.appendChild(this.inspector);
        }

        renderToolbar() {
            var canUndo = false;
            return '' +
                '<div class="pbe-toolbar" role="toolbar" aria-label="' + escapeHtml(t('blocks')) + '">' +
                    '<div class="pbe-toolbar-group">' +
                        '<button type="button" class="pbe-btn is-primary" data-pbe="add">' +
                            '<i class="bi bi-plus-lg"></i>' + escapeHtml(t('addBlock')) +
                        '</button>' +
                        '<div class="pbe-segmented" role="group">' +
                            '<button type="button" class="pbe-btn is-icon is-active" data-pbe="view" data-view="canvas" title="' + escapeHtml(t('canvas')) + '"><i class="bi bi-columns-gap"></i></button>' +
                            '<button type="button" class="pbe-btn is-icon" data-pbe="view" data-view="outline" title="' + escapeHtml(t('outline')) + '"><i class="bi bi-list-ul"></i></button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="pbe-toolbar-group">' +
                        '<button type="button" class="pbe-btn is-icon" data-pbe="undo" title="' + escapeHtml(t('undo')) + ' (Ctrl+Z)"' + (canUndo ? '' : ' disabled') + '><i class="bi bi-arrow-counterclockwise"></i></button>' +
                        '<button type="button" class="pbe-btn is-icon" data-pbe="redo" title="' + escapeHtml(t('redo')) + ' (Ctrl+Shift+Z)" disabled><i class="bi bi-arrow-clockwise"></i></button>' +
                    '</div>' +
                    '<div class="pbe-toolbar-group">' +
                        '<div class="pbe-search">' +
                            '<i class="bi bi-search"></i>' +
                            '<input type="text" id="pbe-search-input" placeholder="' + escapeHtml(t('search')) + '" autocomplete="off">' +
                            '<button type="button" class="pbe-search-clear" data-pbe="clear-search" aria-label="' + escapeHtml(t('close')) + '"><i class="bi bi-x"></i></button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="pbe-toolbar-group is-right">' +
                        '<div class="pbe-meta is-hideable" id="pbe-stats"></div>' +
                        '<span class="pbe-saved-badge" id="pbe-saved"></span>' +
                        '<button type="button" class="pbe-btn" data-pbe="submit"><i class="bi bi-check2"></i>' + escapeHtml(t('save')) + '</button>' +
                    '</div>' +
                '</div>' +
                '<div id="pbe-notice"></div>';
        }

        buildInspector() {
            var el = document.createElement('aside');
            el.className = 'pbe-inspector';
            el.id = 'pbe-inspector';
            el.setAttribute('role', 'complementary');
            el.setAttribute('aria-label', t('settings'));
            el.innerHTML = '' +
                '<div class="pbe-inspector-head">' +
                    '<div class="pbe-inspector-icon" id="pbe-insp-icon"><i class="bi bi-blockquote-left"></i></div>' +
                    '<div class="pbe-inspector-titles">' +
                        '<div class="pbe-inspector-title" id="pbe-insp-title"></div>' +
                        '<div class="pbe-inspector-sub" id="pbe-insp-sub"></div>' +
                    '</div>' +
                    '<button type="button" class="pbe-btn is-icon" data-pbe="inspector-close" title="' + escapeHtml(t('close')) + ' (Esc)"><i class="bi bi-x-lg"></i></button>' +
                '</div>' +
                '<div class="pbe-inspector-tabs" id="pbe-insp-tabs"></div>' +
                '<div class="pbe-inspector-body" id="pbe-insp-body"></div>' +
                '<div class="pbe-inspector-foot">' +
                    '<button type="button" class="pbe-btn is-icon" data-pbe="revert" title="' + escapeHtml(t('revert')) + '"><i class="bi bi-arrow-counterclockwise"></i></button>' +
                    '<button type="button" class="pbe-btn" data-pbe="duplicate-inspector" title="' + escapeHtml(t('duplicate')) + '"><i class="bi bi-files"></i></button>' +
                    '<button type="button" class="pbe-btn is-icon is-danger" data-pbe="delete-inspector" title="' + escapeHtml(t('remove')) + '"><i class="bi bi-trash3"></i></button>' +
                    '<button type="button" class="pbe-btn is-primary" id="save-post-block-settings" data-pbe="apply"><i class="bi bi-check2"></i>' + escapeHtml(t('apply')) + '</button>' +
                '</div>';
            return el;
        }

        loadInitialBlocks() {
            var initial = window.initialPostBlocks;
            if (!Array.isArray(initial)) {
                initial = this.readHiddenBlocks();
            }
            this.blocks = initial.map(function (block, index) {
                var normalized = this.normalizeBlock(block);
                if (typeof normalized.order !== 'number') normalized.order = index;
                return normalized;
            }.bind(this));
            this.blocks.sort(function (a, b) { return a.order - b.order; });
            this.serverSignature = this.signature(this.blocks);
        }

        readHiddenBlocks() {
            if (!this.hiddenField || !this.hiddenField.value) return [];
            try {
                var parsed = JSON.parse(this.hiddenField.value);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        normalizeBlock(block) {
            var content = block.content;
            var settings = block.settings;
            if (typeof content === 'string') {
                try { content = JSON.parse(content); } catch (e) {
                    content = block.type === 'TextBlock' ? { content: content } : { text: content };
                }
            }
            if (typeof settings === 'string') {
                try { settings = JSON.parse(settings); } catch (e) { settings = {}; }
            }
            return {
                id: block.id !== null && block.id !== undefined && block.id !== '' ? String(block.id) : uid(),
                type: block.type,
                content: content && typeof content === 'object' ? content : {},
                settings: settings && typeof settings === 'object' ? settings : {},
                order: Number.isFinite(Number(block.order)) ? Number(block.order) : 0
            };
        }

        render(options) {
            options = options || {};
            if (!this.root) return;

            var scrollTop = window.scrollY;
            if (this.view === 'outline') {
                this.renderOutline();
            } else {
                this.renderCanvas();
            }
            this.updateOrderNumbers();
            this.updateStats();
            this.updateHistoryButtons();
            this.updateSavedBadge();
            this.syncHiddenField();
            if (options.scrollTo) this.scrollToBlock(options.scrollTo);
            if (window.scrollY !== scrollTop && !options.scrollTo) window.scrollTo(0, scrollTop);
        }

        renderCanvas() {
            this.canvas.classList.toggle('is-outline', false);
            var fragment = document.createDocumentFragment();

            if (!this.blocks.length) {
                this.canvas.classList.add('is-empty');
                fragment.appendChild(this.buildEmptyState());
                this.canvas.replaceChildren(fragment);
                return;
            }

            this.canvas.classList.remove('is-empty');
            for (var i = 0; i < this.blocks.length; i++) {
                fragment.appendChild(this.buildSlot(i));
                fragment.appendChild(this.buildBlock(this.blocks[i], i));
            }
            fragment.appendChild(this.buildSlot(this.blocks.length));
            this.canvas.replaceChildren(fragment);
            this.fillPreviews();
        }

        buildSlot(index) {
            var slot = document.createElement('div');
            slot.className = 'pbe-slot';
            slot.dataset.index = String(index);
            slot.innerHTML = '<button type="button" class="pbe-slot-btn" data-pbe="slot" data-index="' + index + '" title="' +
                escapeHtml(t('insertHere')) + '"><i class="bi bi-plus-lg"></i></button>';
            return slot;
        }

        buildBlock(block, index) {
            var info = this.blockInfo(block.type);
            var el = document.createElement('div');
            el.className = 'pbe-block';
            el.dataset.blockId = block.id;
            el.dataset.blockType = block.type;
            el.setAttribute('tabindex', '0');
            el.setAttribute('role', 'group');
            el.setAttribute('aria-label', (index + 1) + '. ' + info.name);
            if (this.selectedId === block.id) el.classList.add('is-selected');

            var presetBadge = block.settings && block.settings.preset_id
                ? '<span class="pbe-badge is-preset"><i class="bi bi-stars"></i>' + escapeHtml(block.settings.preset_name || t('preset')) + '</span>'
                : '';
            var emptyBadge = this.isBlockEmpty(block)
                ? '<span class="pbe-badge is-empty">' + escapeHtml(t('emptyBlock')) + '</span>'
                : '';

            el.innerHTML = '' +
                '<div class="pbe-block-rail">' +
                    '<button type="button" class="pbe-rail-btn is-handle" title="' + escapeHtml(t('drag')) + '"><i class="bi bi-grip-vertical"></i></button>' +
                    '<button type="button" class="pbe-rail-btn" data-pbe="move" data-dir="-1" title="' + escapeHtml(t('moveUp')) + '"><i class="bi bi-arrow-up"></i></button>' +
                    '<button type="button" class="pbe-rail-btn" data-pbe="move" data-dir="1" title="' + escapeHtml(t('moveDown')) + '"><i class="bi bi-arrow-down"></i></button>' +
                '</div>' +
                '<div class="pbe-block-head">' +
                    '<span class="pbe-block-index">' + (index + 1) + '</span>' +
                    '<span class="pbe-block-type"><i class="' + escapeHtml(info.icon) + '"></i>' + escapeHtml(info.name) + '</span>' +
                    presetBadge + emptyBadge +
                    '<div class="pbe-block-actions">' +
                        '<button type="button" class="pbe-act" data-pbe="duplicate" title="' + escapeHtml(t('duplicate')) + '"><i class="bi bi-files"></i></button>' +
                        '<button type="button" class="pbe-act" data-pbe="edit" title="' + escapeHtml(t('settings')) + '"><i class="bi bi-sliders"></i></button>' +
                        '<button type="button" class="pbe-act is-danger" data-pbe="delete" title="' + escapeHtml(t('remove')) + '"><i class="bi bi-trash3"></i></button>' +
                    '</div>' +
                '</div>' +
                '<div class="pbe-block-body" id="preview-' + block.id + '" title="' + escapeHtml(t('inlineEditHint')) + '"></div>';

            if (this.matches && this.matches.indexOf(block.id) === -1) el.classList.add('is-dimmed');
            if (this.matches && this.matches.indexOf(block.id) !== -1) el.classList.add('is-matched');
            return el;
        }

        buildEmptyState() {
            var wrap = document.createElement('div');
            wrap.className = 'pbe-empty';

            var templates = QUICK_TEMPLATES.filter(function (tpl) {
                return tpl.types.some(function (type) { return !!this.available[type]; }.bind(this));
            }.bind(this)).slice(0, 5);

            wrap.innerHTML = '' +
                '<div class="pbe-empty-icon"><i class="bi bi-bricks"></i></div>' +
                '<h5>' + escapeHtml(t('emptyTitle')) + '</h5>' +
                '<p>' + escapeHtml(t('emptyText')) + '</p>' +
                '<button type="button" class="pbe-btn is-primary" data-pbe="add"><i class="bi bi-plus-lg"></i>' + escapeHtml(t('emptyAdd')) + '</button>' +
                (this.hasBlocks && templates.length ? '<div class="pbe-empty-templates" style="margin-top:18px">' +
                    '<div style="width:100%;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#98a3ae">' + escapeHtml(t('templates')) + '</div>' +
                    templates.map(function (tpl) {
                        return '<button type="button" class="pbe-template-chip" data-pbe="template" data-template="' + tpl.id + '">' +
                            '<i class="' + tpl.icon + '"></i>' + escapeHtml(LOCALE === 'ru' ? tpl.ru : tpl.en) + '</button>';
                    }).join('') + '</div>' : '');
            return wrap;
        }

        renderOutline() {
            this.canvas.classList.add('is-outline');
            if (!this.blocks.length) {
                this.canvas.innerHTML = '<div class="pbe-empty"><div class="pbe-empty-icon"><i class="bi bi-list-ul"></i></div>' +
                    '<h5>' + escapeHtml(t('structureEmpty')) + '</h5></div>';
                return;
            }

            var html = '<div class="pbe-outline" id="pbe-outline">';
            this.blocks.forEach(function (block, index) {
                var info = this.blockInfo(block.type);
                var excerpt = collectText(block.content).slice(0, 120);
                var dimmed = this.matches && this.matches.indexOf(block.id) === -1;
                html += '<div class="pbe-outline-item' + (this.selectedId === block.id ? ' is-selected' : '') + (dimmed ? ' is-dimmed' : '') + '"' +
                    ' data-block-id="' + block.id + '" tabindex="0" role="button">' +
                    '<i class="bi bi-grip-vertical pbe-outline-handle"></i>' +
                    '<div class="pbe-outline-icon"><i class="' + escapeHtml(info.icon) + '"></i></div>' +
                    '<div class="pbe-outline-text">' +
                        '<div class="pbe-outline-title"><span class="num">' + (index + 1) + '</span>' + escapeHtml(info.name) + '</div>' +
                        '<div class="pbe-outline-excerpt">' + escapeHtml(excerpt || '—') + '</div>' +
                    '</div>' +
                    '<button type="button" class="pbe-act" data-pbe="edit" title="' + escapeHtml(t('settings')) + '"><i class="bi bi-sliders"></i></button>' +
                    '<button type="button" class="pbe-act" data-pbe="duplicate" title="' + escapeHtml(t('duplicate')) + '"><i class="bi bi-files"></i></button>' +
                    '<button type="button" class="pbe-act is-danger" data-pbe="delete" title="' + escapeHtml(t('remove')) + '"><i class="bi bi-trash3"></i></button>' +
                '</div>';
            }.bind(this));
            html += '</div>';
            this.canvas.innerHTML = html;
        }

        fillPreviews() {
            var missing = [];
            this.blocks.forEach(function (block) {
                var container = document.getElementById('preview-' + block.id);
                if (!container) return;
                var key = this.cacheKey(block);
                if (this.previewCache.has(key)) {
                    container.innerHTML = this.previewCache.get(key);
                } else {
                    container.innerHTML = '<div class="pbe-loading"><span class="spinner-border text-primary" role="status"></span>' +
                        escapeHtml(t('loading')) + '</div><div class="pbe-skeleton" style="width:80%"></div>' +
                        '<div class="pbe-skeleton" style="width:55%"></div>';
                    missing.push(block);
                }
            }.bind(this));

            if (missing.length) this.queuePreviews(missing);
        }

        updateOrderNumbers() {
            var nodes = this.canvas.querySelectorAll('.pbe-block');
            Array.prototype.forEach.call(nodes, function (node, index) {
                var badge = node.querySelector('.pbe-block-index');
                if (badge) badge.textContent = String(index + 1);
            });
        }

        updateStats() {
            if (!this.statsEl) return;
            var words = 0;
            this.blocks.forEach(function (block) { words += countWords(collectText(block.content)); });
            var minutes = Math.max(words ? 1 : 0, Math.round(words / 180));
            this.statsEl.innerHTML = '<b>' + this.blocks.length + '</b> ' + escapeHtml(t('blocks')) +
                (words ? '<span class="pbe-meta-dot"></span><b>' + words + '</b> ' + escapeHtml(t('words')) +
                    '<span class="pbe-meta-dot"></span>~<b>' + minutes + '</b> ' + escapeHtml(t('readTime')) : '');
            if (this.matches) {
                this.statsEl.innerHTML += '<span class="pbe-meta-dot"></span>' + escapeHtml(tf('filterHint', this.matches.length));
            }
        }

        updateSavedBadge() {
            if (!this.savedEl) return;
            this.savedEl.className = 'pbe-saved-badge' + (this.dirty ? ' is-dirty' : '');
            this.savedEl.innerHTML = this.dirty
                ? '<i class="bi bi-pencil"></i>' + escapeHtml(t('unsaved'))
                : '<i class="bi bi-check2-circle"></i>' + escapeHtml(t('allSaved'));
        }

        updateHistoryButtons() {
            if (!this.toolbar) return;
            var undo = this.toolbar.querySelector('[data-pbe="undo"]');
            var redo = this.toolbar.querySelector('[data-pbe="redo"]');
            if (undo) undo.disabled = this.history.past.length === 0;
            if (redo) redo.disabled = this.history.future.length === 0;
        }

        blockInfo(type) {
            var info = this.available[type];
            return info || { name: type, icon: 'bi bi-box', description: '', category: 'other' };
        }

        isBlockEmpty(block) {
            return collectText(block.content).length === 0;
        }

        cacheKey(block) {
            return block.type + ':' + hashOf(block.content) + ':' + hashOf(block.settings);
        }

        pushHistory() {
            this.history.past.push(JSON.stringify(this.blocks));
            if (this.history.past.length > 60) this.history.past.shift();
            this.history.future = [];
            this.markDirty();
        }

        undo() {
            if (!this.history.past.length) {
                this.toast(t('nothingToUndo'));
                return;
            }
            this.history.future.push(JSON.stringify(this.blocks));
            this.blocks = JSON.parse(this.history.past.pop());
            this.closeInspector();
            this.markDirty();
            this.render();
        }

        redo() {
            if (!this.history.future.length) {
                this.toast(t('nothingToRedo'));
                return;
            }
            this.history.past.push(JSON.stringify(this.blocks));
            this.blocks = JSON.parse(this.history.future.pop());
            this.closeInspector();
            this.markDirty();
            this.render();
        }

        markDirty() {
            this.dirty = true;
            this.updateSavedBadge();
            this.syncHiddenField();
        }

        syncHiddenField() {
            if (!this.hiddenField) return;
            this.blocks.forEach(function (block, index) { block.order = index; });
            this.hiddenField.value = JSON.stringify(this.blocks);
        }

        findBlock(id) {
            for (var i = 0; i < this.blocks.length; i++) {
                if (this.blocks[i].id === id) return this.blocks[i];
            }
            return null;
        }

        indexOfBlock(id) {
            for (var i = 0; i < this.blocks.length; i++) {
                if (this.blocks[i].id === id) return i;
            }
            return -1;
        }

        async addBlock(type, index) {
            if (!this.available[type]) return null;

            var blueprint = await this.getBlockBlueprint(type);
            var block = {
                id: uid(),
                type: type,
                content: blueprint.content,
                settings: blueprint.settings,
                order: 0
            };

            this.pushHistory();
            var at = typeof index === 'number' ? Math.max(0, Math.min(index, this.blocks.length)) : this.blocks.length;
            this.blocks.splice(at, 0, block);
            this.pushRecent(type);
            this.closeInspector();
            this.render({ scrollTo: block.id });
            this.select(block.id, { openInspector: true });
            this.toast(tf('blockAdded', this.blockInfo(type).name), 'success');
            return block;
        }

        async insertTemplate(templateId) {
            var tpl = QUICK_TEMPLATES.filter(function (item) { return item.id === templateId; })[0];
            if (!tpl) return;

            var types = tpl.types.filter(function (type) { return !!this.available[type]; }.bind(this));
            if (!types.length) return;

            this.pushHistory();
            var inserted = 0;
            for (var i = 0; i < types.length; i++) {
                var blueprint = await this.getBlockBlueprint(types[i]);
                this.blocks.push({
                    id: uid(),
                    type: types[i],
                    content: blueprint.content,
                    settings: blueprint.settings,
                    order: this.blocks.length
                });
                inserted++;
            }
            this.closeInspector();
            this.render();
            this.toast(tf('blocksInserted', inserted), 'success');
        }

        duplicateBlock(id) {
            var block = this.findBlock(id);
            if (!block) return;

            var copy = deepClone(block);
            copy.id = uid();
            this.pushHistory();
            this.blocks.splice(this.indexOfBlock(id) + 1, 0, copy);
            this.render({ scrollTo: copy.id });
            this.select(copy.id, { openInspector: false });
            this.toast(t('blockDuplicated'), 'success');
        }

        removeBlock(id, options) {
            var block = this.findBlock(id);
            if (!block) return;
            options = options || {};

            if (!options.silent) {
                var message = tf('removeConfirm', this.blockInfo(block.type).name);
                if (!window.confirm(message)) return;
            }

            this.pushHistory();
            this.blocks = this.blocks.filter(function (item) { return item.id !== id; });
            if (this.inspectorBlockId === id) this.closeInspector();
            if (this.selectedId === id) this.selectedId = null;
            this.render();
            this.toast(t('blockRemoved'));
        }

        moveBlock(id, direction) {
            var index = this.indexOfBlock(id);
            var target = index + direction;
            if (index === -1 || target < 0 || target >= this.blocks.length) return;

            this.pushHistory();
            var block = this.blocks.splice(index, 1)[0];
            this.blocks.splice(target, 0, block);
            this.render({ scrollTo: id });
        }

        copyBlock(id) {
            var block = this.findBlock(id);
            if (!block) return;
            storeSet('clipboard', { type: block.type, content: block.content, settings: block.settings });
            this.toast(t('blockCopied'), 'success');
        }

        pasteBlock(index) {
            var clip = storeGet('clipboard', null);
            if (!clip || !this.available[clip.type]) {
                this.openPicker(index);
                return;
            }
            this.pushHistory();
            var block = {
                id: uid(),
                type: clip.type,
                content: deepClone(clip.content || {}),
                settings: deepClone(clip.settings || {}),
                order: 0
            };
            this.blocks.splice(index, 0, block);
            this.render({ scrollTo: block.id });
            this.select(block.id, { openInspector: true });
            this.toast(t('blockPasted'), 'success');
        }

        select(id, options) {
            options = options || {};
            this.selectedId = id;

            Array.prototype.forEach.call(this.canvas.querySelectorAll('.pbe-block'), function (node) {
                node.classList.toggle('is-selected', node.dataset.blockId === id);
            });
            Array.prototype.forEach.call(this.canvas.querySelectorAll('.pbe-outline-item'), function (node) {
                node.classList.toggle('is-selected', node.dataset.blockId === id);
            });

            if (options.openInspector !== false) this.openInspector(id);
        }

        scrollToBlock(id) {
            var node = this.canvas.querySelector('.pbe-block[data-block-id="' + id + '"]') ||
                this.canvas.querySelector('.pbe-outline-item[data-block-id="' + id + '"]');
            if (node && typeof node.scrollIntoView === 'function') {
                node.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        async api(action, payload) {
            var response = await fetch(window.ADMIN_URL + '/post-blocks/' + action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload || {})
            });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        }

        async getBlockBlueprint(type) {
            try {
                var fast = await this.api('get-block-blueprint', { system_name: type });
                if (fast && fast.success) {
                    this.presetsCache.set(type + ':presets', fast.presets || []);
                    return { content: fast.content || {}, settings: fast.settings || {}, presets: fast.presets || [] };
                }
            } catch (e) { /* Запасной путь для более старой версии сервера. */ }

            var results = await Promise.all([
                this.getDefaultContent(type),
                this.getDefaultSettings(type),
                this.getBlockPresets(type)
            ]);
            return { content: results[0], settings: results[1], presets: results[2] };
        }

        async getDefaultContent(type) {
            try {
                var data = await this.api('get-default-content', { system_name: type });
                return data && data.success ? (data.content || {}) : {};
            } catch (e) { return {}; }
        }

        async getDefaultSettings(type) {
            try {
                var data = await this.api('get-default-settings', { system_name: type });
                return data && data.success ? (data.settings || {}) : {};
            } catch (e) { return {}; }
        }

        async getBlockPresets(type) {
            if (this.presetsCache.has(type + ':presets')) return this.presetsCache.get(type + ':presets');
            var presets = [];
            try {
                var data = await this.api('get-presets', { system_name: type });
                presets = data && data.success ? (data.presets || []) : [];
            } catch (e) { presets = []; }
            this.presetsCache.set(type + ':presets', presets);
            return presets;
        }

        /** Совместимость со старым API */
        editBlock(blockId) {
            this.select(blockId, { openInspector: true });
            this.scrollToBlock(blockId);
        }

        normalizeContentData(type, content) {
            if (!content) return {};
            var normalized = Object.assign({}, content);
            if (type === 'TextBlock' && normalized.content && !normalized.text) normalized.text = normalized.content;
            if (type === 'ImageBlock') {
                if (normalized.image_url && !normalized.url) normalized.url = normalized.image_url;
                if (normalized.alt_text && !normalized.alt) normalized.alt = normalized.alt_text;
            }
            if (type === 'ListBlock' && Array.isArray(normalized.items)) {
                normalized.items = normalized.items.map(function (item) {
                    return typeof item === 'string' ? { text: item } : item;
                });
            }
            return normalized;
        }

        queuePreviews(blocks) {
            blocks.forEach(function (block) {
                if (this.pendingPreviewIds.has(block.id)) return;
                this.pendingPreviewIds.add(block.id);
                this.previewQueue.push(block.id);
            }.bind(this));
            if (!this.previewRunning) this.processPreviewQueue();
        }

        async processPreviewQueue() {
            if (this.previewRunning || !this.previewQueue.length) return;
            this.previewRunning = true;

            while (this.previewQueue.length) {
                var batchIds = this.previewQueue.splice(0, 8);
                var batch = [];
                batchIds.forEach(function (id) {
                    var block = this.findBlock(id);
                    if (block) batch.push(block);
                }.bind(this));
                if (!batch.length) continue;

                var signatures = {};
                batch.forEach(function (block) { signatures[block.id] = this.cacheKey(block); }.bind(this));

                var rendered = null;
                try {
                    rendered = await this.fetchBatchPreviews(batch);
                } catch (e) {
                    rendered = null;
                }

                if (rendered) {
                    for (var i = 0; i < batch.length; i++) {
                        var item = batch[i];
                        if (rendered[item.id]) this.applyPreview(item, rendered[item.id], signatures[item.id]);
                        else await this.fetchSinglePreview(item);
                    }
                } else {
                    for (var j = 0; j < batch.length; j++) {
                        await this.fetchSinglePreview(batch[j]);
                    }
                }
                batchIds.forEach(function (id) { this.pendingPreviewIds.delete(id); }.bind(this));
                batch.forEach(function (block) {
                    var current = this.findBlock(block.id);
                    if (current && this.cacheKey(current) !== signatures[block.id]) {
                        this.queuePreviews([current]);
                    }
                }.bind(this));
            }

            this.previewRunning = false;
        }

        async fetchBatchPreviews(blocks) {
            var payload = {
                blocks: blocks.map(function (block) {
                    return {
                        block_id: block.id,
                        block_type: block.type,
                        content: this.normalizeContentData(block.type, block.content),
                        settings: block.settings || {}
                    };
                }.bind(this))
            };
            var data = await this.api('get-previews', payload);
            if (!data || !data.success || !data.previews) throw new Error('batch previews unavailable');
            return data.previews;
        }

        async fetchSinglePreview(block) {
            var container = document.getElementById('preview-' + block.id);
            if (!container) return;

            var key = this.cacheKey(block);
            if (this.previewCache.has(key)) {
                this.applyPreview(block, this.previewCache.get(key), key);
                return;
            }

            try {
                var data = await this.api('get-preview', {
                    block_id: block.id,
                    block_type: block.type,
                    content: this.normalizeContentData(block.type, block.content),
                    settings: block.settings || {}
                });
                if (data && data.success && data.html) {
                    this.applyPreview(block, data.html, key);
                } else {
                    throw new Error((data && data.message) || 'preview error');
                }
            } catch (error) {
                if (key !== this.cacheKey(block) || container.querySelector('.pbe-inline')) return;
                container.innerHTML = '<div class="pbe-block-error"><i class="bi bi-exclamation-triangle me-1"></i>' +
                    escapeHtml(t('previewError') + ': ' + error.message) + '</div>';
            }
        }

        applyPreview(block, html, requestKey) {
            var key = requestKey || this.cacheKey(block);
            this.previewCache.set(key, html);
            if (key !== this.cacheKey(block)) return;
            var container = document.getElementById('preview-' + block.id);
            if (container && !container.querySelector('.pbe-inline')) container.innerHTML = html;
        }

        async refreshPreview(block) {
            this.previewCache.delete(this.cacheKey(block));
            await this.fetchSinglePreview(block);
        }

        async openInspector(blockId) {
            var block = this.findBlock(blockId);
            if (!block) return;

            var info = this.blockInfo(block.type);
            this.inspectorBlockId = blockId;
            this.inspectorSnapshot = JSON.stringify({ content: block.content, settings: block.settings });
            this.inspectorHistoryCaptured = false;

            this.inspector.querySelector('#pbe-insp-icon').innerHTML = '<i class="' + escapeHtml(info.icon) + '"></i>';
            this.inspector.querySelector('#pbe-insp-title').textContent = info.name;
            this.inspector.querySelector('#pbe-insp-sub').textContent = (this.indexOfBlock(blockId) + 1) + ' ' + t('of') + ' ' + this.blocks.length + ' · ' + block.type;
            this.inspector.classList.add('is-open');
            document.body.classList.add('pbe-inspector-open');

            var body = this.inspector.querySelector('#pbe-insp-body');
            var cacheKey = blockId + ':' + hashOf(this.inspectorSnapshot);
            if (this.formCache.has(cacheKey)) {
                this.renderInspectorForm(this.formCache.get(cacheKey), block);
            } else {
                body.innerHTML = '<div class="pbe-loading"><span class="spinner-border text-primary"></span>' + escapeHtml(t('formLoading')) + '</div>' +
                    '<div class="pbe-skeleton" style="width:90%"></div><div class="pbe-skeleton" style="width:70%"></div><div class="pbe-skeleton" style="width:40%"></div>';
                try {
                    var response = await fetch(window.ADMIN_URL + '/post-blocks/get-settings-form', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            system_name: block.type,
                            current_settings: block.settings || {},
                            current_content: block.content || {}
                        })
                    });
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    var html = await response.text();
                    this.formCache.set(cacheKey, html);
                    if (this.inspectorBlockId !== blockId) return;
                    this.renderInspectorForm(html, block);
                } catch (error) {
                    body.innerHTML = '<div class="pbe-block-error"><i class="bi bi-exclamation-triangle me-1"></i>' +
                        escapeHtml(t('formError') + ': ' + error.message) + '</div>';
                }
            }
        }

        async renderInspectorForm(rawHtml, block) {
            var body = this.inspector.querySelector('#pbe-insp-body');
            body.innerHTML = rawHtml;

            var form = body.querySelector('form');
            if (form) form.removeAttribute('id');

            var contentPane = body.querySelector('#content-tab-pane');
            var settingsPane = body.querySelector('#settings-tab-pane');
            if (contentPane) contentPane.classList.add('is-active');
            if (settingsPane) settingsPane.classList.remove('is-active');

            this.applySavedValues(form, block);

            var tabs = [];
            if (contentPane) tabs.push({ id: 'content', pane: contentPane, label: t('content'), icon: 'bi-text-left' });
            if (settingsPane) tabs.push({ id: 'settings', pane: settingsPane, label: t('settingsTab'), icon: 'bi-gear' });

            var presets = await this.getBlockPresets(block.type);
            if (this.inspectorBlockId !== block.id) return;
            this.inspectorPresets = presets;
            if (presets.length) {
                var presetPane = document.createElement('div');
                presetPane.className = 'tab-pane';
                presetPane.id = 'presets-tab-pane';
                presetPane.innerHTML = this.renderPresetSelector(presets, block);
                (body.querySelector('.tab-content') || body).appendChild(presetPane);
                tabs.push({ id: 'presets', pane: presetPane, label: t('presetsTab'), icon: 'bi-stars' });
            }

            this.inspectorTabs = tabs;
            this.renderInspectorTabs();
            this.initFormWidgets(body);
            this.bindInspectorLivePreview(body, block);
        }

        renderInspectorTabs() {
            var tabsEl = this.inspector.querySelector('#pbe-insp-tabs');
            tabsEl.innerHTML = (this.inspectorTabs || []).map(function (tab, index) {
                return '<button type="button" class="pbe-inspector-tab' + (index === 0 ? ' is-active' : '') + '"' +
                    ' data-pbe="tab" data-tab="' + tab.id + '"><i class="' + tab.icon + ' me-1"></i>' + escapeHtml(tab.label) + '</button>';
            }).join('');

            (this.inspectorTabs || []).forEach(function (tab, index) {
                tab.pane.classList.toggle('is-active', index === 0);
            });
        }

        switchTab(tabId) {
            (this.inspectorTabs || []).forEach(function (tab) {
                tab.pane.classList.toggle('is-active', tab.id === tabId);
            });
            Array.prototype.forEach.call(this.inspector.querySelectorAll('[data-pbe="tab"]'), function (button) {
                button.classList.toggle('is-active', button.dataset.tab === tabId);
            });
        }

        renderPresetSelector(presets, block) {
            var current = (block.settings && block.settings.preset_id) || '';
            var options = '<option value="">' + escapeHtml(t('presetNone')) + '</option>';
            presets.forEach(function (preset) {
                options += '<option value="' + escapeHtml(preset.id) + '"' + (String(current) === String(preset.id) ? ' selected' : '') +
                    ' data-name="' + escapeHtml(preset.preset_name) + '">' + escapeHtml(preset.preset_name) + '</option>';
            });
            return '<div class="mb-3"><label class="form-label">' + escapeHtml(t('preset')) + '</label>' +
                '<select class="form-select form-select-sm" id="block-preset-select">' + options + '</select>' +
                '<div class="form-text">' + escapeHtml(t('presetHint')) + '</div></div>';
        }

        applySavedValues(form, block) {
            if (!form) return;

            var setValue = function (name, value) {
                var input = form.querySelector('[name="' + name + '"]');
                if (!input) return;
                if (input.type === 'checkbox') input.checked = Boolean(value) && value !== '0' && value !== '';
                else if (input.type === 'radio') {
                    var radio = form.querySelector('[name="' + name + '"][value="' + value + '"]');
                    if (radio) radio.checked = true;
                } else if (value !== null && value !== undefined) {
                    input.value = typeof value === 'object' ? JSON.stringify(value) : value;
                }
            };

            Object.keys(block.settings || {}).forEach(function (key) { setValue('settings[' + key + ']', block.settings[key]); });
            Object.keys(block.content || {}).forEach(function (key) {
                var value = block.content[key];
                if (typeof value === 'object') return;
                setValue('content[' + key + ']', value);
            });

            var editor = form.querySelector('.rich-text-editor');
            if (editor && block.content && block.content.content) editor.innerHTML = block.content.content;
        }

        initFormWidgets(scope) {
            if (window.RichTextEditor) {
                Array.prototype.forEach.call(scope.querySelectorAll('.rich-text-wrapper'), function (wrapper) {
                    if (wrapper.id && !wrapper.dataset.pbeRich) {
                        new window.RichTextEditor(wrapper.id);
                        wrapper.dataset.pbeRich = '1';
                    }
                });
            }

            var listContainer = scope.querySelector('#list-items-container');
            if (listContainer && window.ListBlockAdmin && !listContainer.hasAttribute('data-initialized')) {
                new window.ListBlockAdmin(scope);
            }

            var galleryContainer = scope.querySelector('#gallery-items-container');
            if (galleryContainer && window.GalleryBlockAdmin && !galleryContainer.hasAttribute('data-initialized')) {
                new window.GalleryBlockAdmin(scope);
            }

            if (scope.querySelector('.image-file-input') && window.ImageBlockAdmin) {
                new window.ImageBlockAdmin(scope);
            }

            if (scope.querySelector('[data-video-block]') && typeof window.initVideoBlockForm === 'function') {
                Array.prototype.forEach.call(scope.querySelectorAll('[data-video-block]'), function (node) {
                    window.initVideoBlockForm(node);
                });
            }

            if (window.ace) {
                var codeContainer = scope.querySelector('#code-editor-container');
                if (codeContainer && !codeContainer.dataset.pbeAce) {
                    if (typeof window.initCodeBlockAdmin === 'function') window.initCodeBlockAdmin();
                    codeContainer.dataset.pbeAce = '1';
                }

                var htmlContainer = scope.querySelector('#html-editor-container');
                var htmlTextarea = scope.querySelector('#html-editor-textarea');
                if (htmlContainer && htmlTextarea && !htmlContainer.dataset.pbeAce) {
                    try {
                        var editor = window.ace.edit(htmlContainer);
                        editor.setOptions({ mode: 'ace/mode/html', theme: 'ace/theme/monokai', fontSize: '14px', showPrintMargin: false });
                        editor.setValue(htmlTextarea.value || '', -1);
                        editor.getSession().on('change', function () { htmlTextarea.value = editor.getValue(); });
                        htmlContainer.dataset.pbeAce = '1';
                    } catch (e) { /* ace не смог инициализироваться — остаётся textarea */ }
                }
            }
        }

        bindInspectorLivePreview(scope, block) {
            var self = this;
            var handler = debounce(function () {
                var current = self.findBlock(block.id);
                if (!current) return;
                if (self.inspectorBlockId !== block.id) return;
                var values = self.collectForm(scope);
                var nextContent = Object.assign({}, current.content, values.content);
                var nextSettings = Object.assign({}, current.settings, values.settings);
                if (JSON.stringify(current.content) === JSON.stringify(nextContent) &&
                    JSON.stringify(current.settings) === JSON.stringify(nextSettings)) return;
                if (!self.inspectorHistoryCaptured) {
                    self.pushHistory();
                    self.inspectorHistoryCaptured = true;
                }
                current.content = nextContent;
                current.settings = nextSettings;
                self.markDirty();
                self.refreshPreview(current);
            }, 500);

            scope.removeEventListener('input', this._inspectorInputHandler);
            scope.removeEventListener('change', this._inspectorInputHandler);
            this._inspectorInputHandler = handler;
            scope.addEventListener('input', handler);
            scope.addEventListener('change', handler);
        }

        syncSpecialEditors(scope) {
            Array.prototype.forEach.call(scope.querySelectorAll('.rich-text-editor'), function (editor) {
                var wrapper = editor.closest('.rich-text-wrapper');
                if (!wrapper) return;
                var textarea = wrapper.querySelector('textarea[name="content[content]"]');
                if (textarea) textarea.value = editor.innerHTML;
            });

            if (window.ace) {
                var codeContainer = scope.querySelector('#code-editor-container');
                var codeTextarea = scope.querySelector('#code-editor-textarea');
                if (codeContainer && codeTextarea) {
                    try { codeTextarea.value = window.ace.edit(codeContainer).getValue(); } catch (e) { /* noop */ }
                }
                var htmlContainer = scope.querySelector('#html-editor-container');
                var htmlTextarea = scope.querySelector('#html-editor-textarea');
                if (htmlContainer && htmlTextarea) {
                    try { htmlTextarea.value = window.ace.edit(htmlContainer).getValue(); } catch (e) { /* noop */ }
                }
            }
        }

        collectForm(scope) {
            this.syncSpecialEditors(scope);
            var form = scope.querySelector('form');
            var content = {};
            var settings = {};
            if (!form) return { content: content, settings: settings };

            var formData = new FormData(form);
            formData.forEach(function (value, key) {
                // Файлы передаются отдельно в FormData при применении блока.
                if (typeof value !== 'string') return;
                var group = key.match(/^(content|settings)(?:\[[^\]]*\])+$/);
                if (!group) return;
                var root = group[1] === 'content' ? content : settings;
                var parts = [];
                var pattern = /\[([^\]]*)\]/g;
                var found;
                while ((found = pattern.exec(key)) !== null) parts.push(found[1]);
                var cursor = root;
                parts.forEach(function (part, index) {
                    var last = index === parts.length - 1;
                    if (last) {
                        if (part === '' && Array.isArray(cursor)) cursor.push(value);
                        else cursor[part] = value;
                    } else {
                        var next = parts[index + 1];
                        if (!cursor[part] || typeof cursor[part] !== 'object') {
                            cursor[part] = next === '' || /^\d+$/.test(next) ? [] : {};
                        }
                        cursor = cursor[part];
                    }
                });
            });

            Array.prototype.forEach.call(form.querySelectorAll('input[type="checkbox"][name^="settings["]'), function (checkbox) {
                var key = checkbox.name.replace(/^settings\[/, '').replace(/\]$/, '');
                settings[key] = checkbox.checked ? (checkbox.value || '1') : '';
            });

            var presetSelect = scope.querySelector('#block-preset-select');
            if (presetSelect) {
                if (presetSelect.value) {
                    settings.preset_id = presetSelect.value;
                    var selected = presetSelect.options[presetSelect.selectedIndex];
                    settings.preset_name = selected ? selected.getAttribute('data-name') : '';
                } else {
                    settings.preset_id = '';
                    settings.preset_name = '';
                }
            }

            return { content: content, settings: settings };
        }

        async applyInspector() {
            var blockId = this.inspectorBlockId;
            var block = this.findBlock(blockId);
            if (!block) {
                this.closeInspector();
                return;
            }

            var body = this.inspector.querySelector('#pbe-insp-body');
            var applyButton = this.inspector.querySelector('#save-post-block-settings');
            var originalHtml = applyButton ? applyButton.innerHTML : '';
            if (applyButton) {
                applyButton.disabled = true;
                applyButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + escapeHtml(t('loading'));
            }

            try {
                var form = body.querySelector('form');
                var values = this.collectForm(body);
                if (!this.inspectorHistoryCaptured) {
                    this.pushHistory();
                    this.inspectorHistoryCaptured = true;
                }
                block.content = Object.assign({}, block.content, values.content);
                block.settings = Object.assign({}, block.settings, values.settings);
                var formData = form ? new FormData(form) : new FormData();
                formData.append('block_id', block.id);
                formData.append('block_type', block.type);
                formData.append('content_json', JSON.stringify(block.content));
                formData.append('settings_json', JSON.stringify(block.settings));

                var response = await fetch(window.ADMIN_URL + '/post-blocks/upload-block-files', {
                    method: 'POST',
                    body: formData
                });
                var data = await response.json();
                if (!data || !data.success) throw new Error((data && data.message) || 'save error');

                if (data.block_data) {
                    block.content = data.block_data.content || block.content;
                    block.settings = data.block_data.settings || block.settings;
                }

                this.markDirty();
                this.formCache.delete(blockId + ':' + hashOf(this.inspectorSnapshot));
                this.inspectorSnapshot = JSON.stringify({ content: block.content, settings: block.settings });
                this.render();
                this.select(block.id, { openInspector: false });
                await this.refreshPreview(block);
                this.closeInspector();
                this.toast(t('done'), 'success');
                return true;
            } catch (error) {
                this.toast(error.message, 'error');
                return false;
            } finally {
                if (applyButton) {
                    applyButton.disabled = false;
                    applyButton.innerHTML = originalHtml;
                }
            }
        }

        revertInspector() {
            var block = this.findBlock(this.inspectorBlockId);
            if (!block || !this.inspectorSnapshot) return;
            var snapshot = JSON.parse(this.inspectorSnapshot);
            if (JSON.stringify({ content: block.content, settings: block.settings }) === this.inspectorSnapshot) return;
            this.pushHistory();
            block.content = snapshot.content;
            block.settings = snapshot.settings;
            this.render();
            this.openInspector(block.id);
            this.refreshPreview(block);
            this.toast(t('changesReverted'));
        }

        closeInspector() {
            if (!this.inspector) return;
            this.inspector.classList.remove('is-open');
            document.body.classList.remove('pbe-inspector-open');
            this.inspectorBlockId = null;
            this.inspectorSnapshot = null;
            this.inspectorHistoryCaptured = false;
            var body = this.inspector.querySelector('#pbe-insp-body');
            if (body) body.innerHTML = '';
        }

        startInlineEdit(blockId) {
            var block = this.findBlock(blockId);
            if (!block) return;
            var config = INLINE_EDITABLE[block.type];
            if (!config) {
                this.select(blockId, { openInspector: true });
                return;
            }

            this.closeInspector();
            this.select(blockId, { openInspector: false });

            var container = document.getElementById('preview-' + blockId);
            if (!container || container.querySelector('.pbe-inline')) return;

            var value = block.content && block.content[config.field] ? String(block.content[config.field]) : '';
            var original = value;

            container.innerHTML = '';
            var editor = document.createElement('div');
            editor.className = 'pbe-inline' + (config.mono ? ' is-mono' : '');
            editor.setAttribute('contenteditable', 'true');
            editor.setAttribute('role', 'textbox');
            if (config.rich) editor.innerHTML = value; else editor.textContent = value;

            var toolbar = document.createElement('div');
            toolbar.className = 'pbe-inline-toolbar';
            toolbar.innerHTML = '<span class="pbe-kbd">Ctrl+Enter</span><small class="text-muted ms-1">' + escapeHtml(t('done')) + '</small>' +
                '<span class="spacer"></span>' +
                '<button type="button" class="pbe-btn" data-pbe="inline-cancel">' + escapeHtml(t('cancel')) + '</button>' +
                '<button type="button" class="pbe-btn is-primary" data-pbe="inline-save"><i class="bi bi-check2"></i>' + escapeHtml(t('done')) + '</button>';

            container.appendChild(editor);
            container.appendChild(toolbar);
            editor.focus();

            var self = this;
            var placeCaretAtEnd = function () {
                var range = document.createRange();
                range.selectNodeContents(editor);
                range.collapse(false);
                var selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
            };
            placeCaretAtEnd();

            var finished = false;
            var finish = function (save) {
                if (finished) return;
                finished = true;
                container.removeEventListener('click', inlineClick);
                if (save) {
                    var next = config.rich ? editor.innerHTML : (editor.innerText || editor.textContent || '');
                    if (next !== original) {
                        self.pushHistory();
                        block.content[config.field] = next;
                        self.markDirty();
                        self.toast(t('inlineSaved'), 'success');
                    }
                }
                self.refreshPreview(block);
            };

            var inlineClick = function (event) {
                var cancel = event.target.closest('[data-pbe="inline-cancel"]');
                if (cancel) { finish(false); return; }
                var save = event.target.closest('[data-pbe="inline-save"]');
                if (save) finish(true);
            };
            container.addEventListener('click', inlineClick);

            editor.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                    event.preventDefault();
                    finish(true);
                }
                if (event.key === 'Escape') {
                    event.preventDefault();
                    finish(false);
                }
                event.stopPropagation();
            });
        }

        openPicker(index, anchor) {
            if (!this.hasBlocks) {
                this.toast(t('noBlocksAvailable'), 'error');
                return;
            }
            this.closePicker();

            this.pickerInsertAt = typeof index === 'number' ? index : this.blocks.length;
            this.pickerAnchor = anchor || null;

            this.pickerBackdrop = document.createElement('div');
            this.pickerBackdrop.className = 'pbe-picker-backdrop';
            this.pickerBackdrop.addEventListener('click', function () { this.closePicker(); }.bind(this));
            document.body.appendChild(this.pickerBackdrop);

            this.picker = document.createElement('div');
            this.picker.className = 'pbe-picker';
            this.picker.setAttribute('role', 'dialog');
            this.picker.setAttribute('aria-modal', 'true');
            this.picker.setAttribute('aria-label', t('pickerTitle'));
            this.picker.innerHTML = '' +
                '<div class="pbe-picker-head">' +
                    '<div class="pbe-picker-search">' +
                        '<i class="bi bi-search"></i>' +
                        '<input type="text" id="pbe-picker-input" placeholder="' + escapeHtml(t('pickerPlaceholder')) + '" autocomplete="off">' +
                        '<span class="pbe-kbd">Esc</span>' +
                    '</div>' +
                    '<div class="pbe-picker-tabs" id="pbe-picker-tabs"></div>' +
                '</div>' +
                '<div class="pbe-picker-body" id="pbe-picker-body"></div>' +
                '<div class="pbe-picker-foot">' +
                    '<span><span class="pbe-kbd">↑↓</span>' + escapeHtml(t('navHint')) + '</span>' +
                    '<span><span class="pbe-kbd">Enter</span>' + escapeHtml(t('enterHint')) + '</span>' +
                    '<span><span class="pbe-kbd">Esc</span>' + escapeHtml(t('escHint')) + '</span>' +
                '</div>';
            document.body.appendChild(this.picker);

            this.pickerCategory = 'all';
            this.pickerQuery = '';
            this.renderPicker();
            this.positionPicker();

            var input = this.picker.querySelector('#pbe-picker-input');
            input.focus();
            input.addEventListener('input', function (event) {
                this.pickerQuery = event.target.value;
                this.renderPicker();
            }.bind(this));
            input.addEventListener('keydown', function (event) { this.handlePickerKeydown(event); }.bind(this));

            this.picker.addEventListener('click', function (event) {
                var tab = event.target.closest('[data-pbe="picker-tab"]');
                if (tab) {
                    this.pickerCategory = tab.dataset.category;
                    this.renderPicker();
                    this.picker.querySelector('#pbe-picker-input').focus();
                    return;
                }
                var item = event.target.closest('[data-pbe="picker-item"]');
                if (item) {
                    var at = this.pickerInsertAt;
                    this.closePicker();
                    this.addBlock(item.dataset.type, at);
                    return;
                }
                var template = event.target.closest('[data-pbe="picker-template"]');
                if (template) {
                    var templateId = template.dataset.template;
                    this.closePicker();
                    this.insertTemplate(templateId);
                    return;
                }
                var paste = event.target.closest('[data-pbe="picker-paste"]');
                if (paste) {
                    var pasteAt = this.pickerInsertAt;
                    this.closePicker();
                    this.pasteBlock(pasteAt);
                }
            }.bind(this));
        }

        positionPicker() {
            if (!this.picker) return;
            var rect = this.picker.getBoundingClientRect();
            var anchorRect = this.pickerAnchor ? this.pickerAnchor.getBoundingClientRect() : null;

            var left = anchorRect
                ? anchorRect.left + anchorRect.width / 2 - rect.width / 2
                : window.innerWidth / 2 - rect.width / 2;
            left = Math.max(12, Math.min(left, window.innerWidth - rect.width - 12));

            var top = anchorRect ? anchorRect.bottom + 8 : 90;
            if (top + rect.height > window.innerHeight - 12) {
                top = anchorRect ? anchorRect.top - rect.height - 8 : Math.max(12, window.innerHeight - rect.height - 24);
            }
            top = Math.max(12, top);

            this.picker.style.left = left + 'px';
            this.picker.style.top = top + 'px';
        }

        renderPicker() {
            var query = (this.pickerQuery || '').trim().toLowerCase();
            var blocks = Object.keys(this.available).map(function (type) {
                return Object.assign({ system_name: type }, this.available[type]);
            }.bind(this));

            var categories = {};
            blocks.forEach(function (block) {
                var category = block.category || 'other';
                categories[category] = categories[category] || [];
                categories[category].push(block);
            });

            var ordered = CATEGORY_ORDER.filter(function (category) { return categories[category]; });
            Object.keys(categories).forEach(function (category) {
                if (ordered.indexOf(category) === -1) ordered.push(category);
            });

            var labels = CATEGORY_LABELS[LOCALE] || CATEGORY_LABELS.ru;
            var tabsEl = this.picker.querySelector('#pbe-picker-tabs');
            tabsEl.innerHTML = '<button type="button" class="pbe-picker-tab' + (this.pickerCategory === 'all' ? ' is-active' : '') +
                '" data-pbe="picker-tab" data-category="all">' + escapeHtml(labels.all) + '</button>' +
                ordered.map(function (category) {
                    return '<button type="button" class="pbe-picker-tab' + (this.pickerCategory === category ? ' is-active' : '') +
                        '" data-pbe="picker-tab" data-category="' + category + '"><i class="' + (CATEGORY_ICONS[category] || 'bi-box') + ' me-1"></i>' +
                        escapeHtml(labels[category] || category) + '</button>';
                }.bind(this)).join('');

            var filtered = blocks.filter(function (block) {
                var inCategory = this.pickerCategory === 'all' || (block.category || 'other') === this.pickerCategory;
                if (!inCategory) return false;
                if (!query) return true;
                return (block.name || '').toLowerCase().indexOf(query) !== -1 ||
                    (block.description || '').toLowerCase().indexOf(query) !== -1 ||
                    (block.system_name || '').toLowerCase().indexOf(query) !== -1;
            }.bind(this));

            var html = '';
            var recent = storeGet('recent', []).filter(function (type) {
                return this.available[type] && (!query || (this.available[type].name || '').toLowerCase().indexOf(query) !== -1);
            }.bind(this));

            if (recent.length && !this.pickerQuery) {
                html += '<div class="pbe-picker-section"><div class="pbe-picker-section-title">' + escapeHtml(t('recent')) + '</div>' +
                    '<div class="pbe-picker-grid">' + recent.map(function (type) { return this.renderPickerItem(this.available[type], type); }.bind(this)).join('') +
                    '</div></div>';
            }

            var clipboard = storeGet('clipboard', null);
            if (clipboard && this.available[clipboard.type] && !this.pickerQuery) {
                var clipInfo = this.available[clipboard.type];
                html += '<div class="pbe-picker-section"><button type="button" class="pbe-picker-item" data-pbe="picker-paste" style="width:100%">' +
                    '<i class="bi bi-clipboard"></i><span class="pbe-picker-item-text"><span class="pbe-picker-item-name">' + escapeHtml(t('pasteBlock')) +
                    '</span><span class="pbe-picker-item-desc">' + escapeHtml(clipInfo.name) + '</span></span></button></div>';
            }

            if (filtered.length) {
                ordered.forEach(function (category) {
                    var items = filtered.filter(function (block) { return (block.category || 'other') === category; });
                    if (!items.length) return;
                    html += '<div class="pbe-picker-section"><div class="pbe-picker-section-title">' +
                        escapeHtml(labels[category] || category) + '</div><div class="pbe-picker-grid">' +
                        items.map(function (block) { return this.renderPickerItem(block, block.system_name); }.bind(this)).join('') +
                        '</div></div>';
                }.bind(this));
            }

            if (!this.pickerQuery) {
                var templates = QUICK_TEMPLATES.filter(function (tpl) {
                    return tpl.types.some(function (type) { return !!this.available[type]; }.bind(this));
                }.bind(this));
                if (templates.length) {
                    html += '<div class="pbe-picker-section"><div class="pbe-picker-section-title">' + escapeHtml(t('templates')) + '</div>' +
                        '<div class="pbe-picker-grid">' + templates.map(function (tpl) {
                            return '<button type="button" class="pbe-picker-item" data-pbe="picker-template" data-template="' + tpl.id + '">' +
                                '<i class="' + tpl.icon + '"></i><span class="pbe-picker-item-text"><span class="pbe-picker-item-name">' +
                                escapeHtml(LOCALE === 'ru' ? tpl.ru : tpl.en) + '</span><span class="pbe-picker-item-desc">' +
                                tpl.types.length + ' ' + escapeHtml(t('blocks')).toLowerCase() + '</span></span></button>';
                        }).join('') + '</div></div>';
                }
            }

            if (!html) html = '<div class="pbe-picker-empty">' + escapeHtml(t('nothingFound')) + '</div>';

            this.picker.querySelector('#pbe-picker-body').innerHTML = html;
            this.pickerCursor = null;
            this.pickerIndex = 0;
            this.setPickerCursor(0);
        }

        renderPickerItem(block, type) {
            return '<button type="button" class="pbe-picker-item" data-pbe="picker-item" data-type="' + escapeHtml(type) + '">' +
                '<i class="' + escapeHtml(block.icon || 'bi bi-box') + '"></i>' +
                '<span class="pbe-picker-item-text">' +
                    '<span class="pbe-picker-item-name">' + escapeHtml(block.name || type) + '</span>' +
                    (block.description ? '<span class="pbe-picker-item-desc">' + escapeHtml(block.description) + '</span>' : '') +
                '</span></button>';
        }

        setPickerCursor(index) {
            if (!this.picker) return;
            var items = this.picker.querySelectorAll('[data-pbe="picker-item"], [data-pbe="picker-template"]');
            if (!items.length) {
                this.pickerCursor = null;
                return;
            }
            this.pickerIndex = (index + items.length) % items.length;
            Array.prototype.forEach.call(items, function (item, i) {
                item.classList.toggle('is-cursor', i === this.pickerIndex);
            }.bind(this));
            this.pickerCursor = items[this.pickerIndex];
            if (this.pickerCursor && this.pickerCursor.scrollIntoView) {
                this.pickerCursor.scrollIntoView({ block: 'nearest' });
            }
        }

        handlePickerKeydown(event) {
            var items = this.picker.querySelectorAll('[data-pbe="picker-item"], [data-pbe="picker-template"]');
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.setPickerCursor(this.pickerIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.setPickerCursor(this.pickerIndex - 1);
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (this.pickerCursor) this.pickerCursor.click();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                this.closePicker();
            } else if (items.length === 0 && event.key === 'Backspace' && !this.pickerQuery) {
                this.closePicker();
            }
        }

        closePicker() {
            if (this.picker) {
                this.picker.remove();
                this.picker = null;
            }
            if (this.pickerBackdrop) {
                this.pickerBackdrop.remove();
                this.pickerBackdrop = null;
            }
        }

        pushRecent(type) {
            var recent = storeGet('recent', []).filter(function (item) { return item !== type; });
            recent.unshift(type);
            storeSet('recent', recent.slice(0, 6));
        }

        applySearch(query) {
            this.search = (query || '').trim().toLowerCase();
            if (this.searchWrap) this.searchWrap.classList.toggle('has-value', !!this.search);

            if (!this.search) {
                this.matches = null;
            } else {
                this.matches = this.blocks.filter(function (block) {
                    var info = this.blockInfo(block.type);
                    return collectText(block.content).toLowerCase().indexOf(this.search) !== -1 ||
                        (info.name || '').toLowerCase().indexOf(this.search) !== -1;
                }.bind(this)).map(function (block) { return block.id; });
            }
            this.render();
        }

        signature(blocks) {
            return hashOf((blocks || []).map(function (block) {
                return [block.type, JSON.stringify(block.content || {}), JSON.stringify(block.settings || {})].join('|');
            }));
        }

        checkDraft() {
            var draft = storeGet('draft:' + this.storageKey, null);
            if (!draft || !Array.isArray(draft.blocks)) return;
            if (this.signature(draft.blocks) === this.serverSignature) {
                storeRemove('draft:' + this.storageKey);
                return;
            }

            if (!this.noticeEl) return;
            this.noticeEl.innerHTML = '<div class="pbe-notice">' +
                '<i class="bi bi-clock-history"></i>' +
                '<div><strong>' + escapeHtml(t('draftTitle')) + '</strong><br>' + escapeHtml(t('draftText')) + '</div>' +
                '<span class="spacer"></span>' +
                '<button type="button" class="pbe-btn" data-pbe="draft-restore">' + escapeHtml(t('draftRestore')) + '</button>' +
                '<button type="button" class="pbe-btn" data-pbe="draft-discard">' + escapeHtml(t('draftDiscard')) + '</button>' +
                '</div>';
            this.pendingDraft = draft.blocks;
        }

        restoreDraft() {
            if (!this.pendingDraft) return;
            this.pushHistory();
            this.blocks = this.pendingDraft.map(function (block) { return this.normalizeBlock(block); }.bind(this));
            this.pendingDraft = null;
            storeRemove('draft:' + this.storageKey);
            this.noticeEl.innerHTML = '';
            this.closeInspector();
            this.render();
            this.toast(t('draftRestored'), 'success');
        }

        discardDraft() {
            storeRemove('draft:' + this.storageKey);
            this.pendingDraft = null;
            if (this.noticeEl) this.noticeEl.innerHTML = '';
        }

        startAutosave() {
            var self = this;
            this.autosaveTimer = setInterval(function () {
                if (!self.dirty) return;
                var serialized = JSON.stringify(self.blocks);
                if (serialized === self.lastDraftSerialized) return;
                if (storeSet('draft:' + self.storageKey, { blocks: self.blocks, at: Date.now() })) {
                    self.lastDraftSerialized = serialized;
                    self.flashSaved();
                }
            }, 4000);

            window.addEventListener('beforeunload', function () {
                if (self.dirty) {
                    storeSet('draft:' + self.storageKey, { blocks: self.blocks, at: Date.now() });
                }
            });
        }

        flashSaved() {
            if (!this.savedEl) return;
            this.savedEl.classList.add('is-saved');
            this.savedEl.innerHTML = '<i class="bi bi-cloud-check"></i>' + escapeHtml(t('saved'));
            setTimeout(function () { this.updateSavedBadge(); }.bind(this), 1800);
        }

        toast(message, type) {
            var existing = document.querySelector('.pbe-toast');
            if (existing) existing.remove();

            var el = document.createElement('div');
            el.className = 'pbe-toast' + (type ? ' is-' + type : '');
            el.setAttribute('role', 'status');
            el.textContent = message;
            document.body.appendChild(el);
            requestAnimationFrame(function () { el.classList.add('is-visible'); });

            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(function () {
                el.classList.remove('is-visible');
                setTimeout(function () { el.remove(); }, 220);
            }, 2600);
        }

        bindEvents() {
            var self = this;

            if (this.root) {
                this.root.addEventListener('click', function (event) {
                    var target = event.target;

                    var slotButton = target.closest('[data-pbe="slot"]');
                    if (slotButton) {
                        event.preventDefault();
                        var slot = slotButton.closest('.pbe-slot');
                        self.openPicker(parseInt(slotButton.dataset.index, 10), slot);
                        return;
                    }

                    var action = target.closest('[data-pbe]');
                    if (!action) {
                        var blockNode = target.closest('.pbe-block');
                        if (blockNode && !target.closest('a') && !target.closest('.pbe-inline')) {
                            self.select(blockNode.dataset.blockId, {
                                openInspector: !INLINE_EDITABLE[blockNode.dataset.blockType]
                            });
                        }
                        return;
                    }

                    var block = action.closest('.pbe-block') || action.closest('.pbe-outline-item');
                    var blockId = block ? block.dataset.blockId : null;

                    switch (action.dataset.pbe) {
                        case 'add':
                            event.preventDefault();
                            self.openPicker(self.blocks.length, action);
                            break;
                        case 'view':
                            self.setView(action.dataset.view);
                            break;
                        case 'undo':
                            self.undo();
                            break;
                        case 'redo':
                            self.redo();
                            break;
                        case 'clear-search':
                            var input = self.root.querySelector('#pbe-search-input');
                            if (input) input.value = '';
                            self.applySearch('');
                            break;
                        case 'submit':
                            self.submitForm();
                            break;
                        case 'move':
                            self.moveBlock(blockId, parseInt(action.dataset.dir, 10));
                            break;
                        case 'duplicate':
                            self.duplicateBlock(blockId);
                            break;
                        case 'edit':
                            self.select(blockId, { openInspector: true });
                            break;
                        case 'delete':
                            self.removeBlock(blockId);
                            break;
                        case 'template':
                            self.insertTemplate(action.dataset.template);
                            break;
                        case 'draft-restore':
                            self.restoreDraft();
                            break;
                        case 'draft-discard':
                            self.discardDraft();
                            break;
                        default:
                            break;
                    }
                });

                this.root.addEventListener('dblclick', function (event) {
                    if (event.target.closest('.pbe-inline') || event.target.closest('button')) return;
                    var body = event.target.closest('.pbe-block-body');
                    if (!body) return;
                    var blockNode = body.closest('.pbe-block');
                    if (blockNode) self.startInlineEdit(blockNode.dataset.blockId);
                });

                this.root.addEventListener('click', function (event) {
                    var link = event.target.closest('.pbe-block-body a');
                    if (link) event.preventDefault();
                });

                this.root.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' && isEditableTarget(event.target) &&
                        !event.target.closest('#pbe-canvas') && event.target.id !== 'pbe-search-input') {
                        return;
                    }
                    if (event.key === 'Enter' && event.target.matches && event.target.matches('.pbe-toolbar input')) {
                        event.preventDefault();
                    }
                    var blockNode = event.target.closest ? event.target.closest('.pbe-block') : null;
                    if (!blockNode) return;

                    if (event.key === 'Enter' || event.key === ' ') {
                        if (event.target === blockNode) {
                            event.preventDefault();
                            self.select(blockNode.dataset.blockId, { openInspector: true });
                        }
                    }
                    if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                        event.preventDefault();
                        self.startInlineEdit(blockNode.dataset.blockId);
                    }
                });

                var searchInput = this.root.querySelector('#pbe-search-input');
                if (searchInput) {
                    searchInput.addEventListener('input', debounce(function (event) {
                        self.applySearch(event.target.value);
                    }, 250));
                }
            }

            if (this.inspector) {
                this.inspector.addEventListener('click', function (event) {
                    var action = event.target.closest('[data-pbe]');
                    if (!action) return;

                    switch (action.dataset.pbe) {
                        case 'inspector-close':
                            self.closeInspector();
                            break;
                        case 'apply':
                            event.preventDefault();
                            self.applyInspector();
                            break;
                        case 'revert':
                            self.revertInspector();
                            break;
                        case 'duplicate-inspector':
                            if (self.inspectorBlockId) self.duplicateBlock(self.inspectorBlockId);
                            break;
                        case 'delete-inspector':
                            if (self.inspectorBlockId) self.removeBlock(self.inspectorBlockId);
                            break;
                        case 'tab':
                            self.switchTab(action.dataset.tab);
                            break;
                        default:
                            break;
                    }
                });
            }

            document.addEventListener('keydown', function (event) { self.handleGlobalKeydown(event); });

            var form = document.getElementById('post-form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (self.inspectorBlockId) {
                        event.preventDefault();
                        self.applyInspector().then(function (ok) {
                            if (ok) {
                                if (typeof form.requestSubmit === 'function') form.requestSubmit();
                                else form.submit();
                            }
                        });
                        return;
                    }
                    self.syncHiddenField();
                    storeRemove('draft:' + self.storageKey);
                    self.dirty = false;
                });
            }

            window.addEventListener('resize', debounce(function () {
                if (self.picker) self.positionPicker();
            }, 120));
        }

        setView(view) {
            if (this.view === view) return;
            this.view = view;
            Array.prototype.forEach.call(this.toolbar.querySelectorAll('[data-pbe="view"]'), function (button) {
                button.classList.toggle('is-active', button.dataset.view === view);
            });
            this.render();
            this.initSortable();
        }

        flushInspector() {
            var block = this.findBlock(this.inspectorBlockId);
            if (!block) return;
            var body = this.inspector.querySelector('#pbe-insp-body');
            if (!body || !body.querySelector('form')) return;
            var values = this.collectForm(body);
            block.content = Object.assign({}, block.content, values.content);
            block.settings = Object.assign({}, block.settings, values.settings);
        }

        submitForm() {
            this.flushInspector();
            this.syncHiddenField();
            var form = document.getElementById('post-form');
            if (!form) return;
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else form.submit();
        }

        handleGlobalKeydown(event) {
            var meta = event.ctrlKey || event.metaKey;
            var editable = isEditableTarget(event.target);

            if (event.key === 'Escape') {
                if (this.picker) { this.closePicker(); return; }
                if (this.inspector && this.inspector.classList.contains('is-open')) { this.closeInspector(); return; }
            }

            if (this.picker) return;

            if (meta && (event.key === 'z' || event.key === 'Z' || event.key === 'я' || event.key === 'Я')) {
                if (editable) return;
                event.preventDefault();
                if (event.shiftKey) this.redo(); else this.undo();
                return;
            }
            if (meta && (event.key === 'y' || event.key === 'Y' || event.key === 'н' || event.key === 'Н')) {
                if (editable) return;
                event.preventDefault();
                this.redo();
                return;
            }

            if (editable) return;

            if (event.key === '/' ) {
                event.preventDefault();
                var index = this.selectedId ? this.indexOfBlock(this.selectedId) + 1 : 0;
                this.openPicker(index, this.canvas);
                return;
            }

            if (meta && (event.key === 'd' || event.key === 'D' || event.key === 'в' || event.key === 'В')) {
                if (!this.selectedId) return;
                event.preventDefault();
                this.duplicateBlock(this.selectedId);
                return;
            }
            if (meta && (event.key === 'c' || event.key === 'C' || event.key === 'с' || event.key === 'С')) {
                if (!this.selectedId) return;
                event.preventDefault();
                this.copyBlock(this.selectedId);
                return;
            }
            if (meta && (event.key === 'v' || event.key === 'V' || event.key === 'м' || event.key === 'М')) {
                event.preventDefault();
                this.pasteBlock(this.selectedId ? this.indexOfBlock(this.selectedId) + 1 : this.blocks.length);
                return;
            }
            if (meta && event.key === 'ArrowUp' && this.selectedId) {
                event.preventDefault();
                this.moveBlock(this.selectedId, -1);
                return;
            }
            if (meta && event.key === 'ArrowDown' && this.selectedId) {
                event.preventDefault();
                this.moveBlock(this.selectedId, 1);
                return;
            }
            if ((event.key === 'Delete' || event.key === 'Backspace') && this.selectedId) {
                event.preventDefault();
                this.removeBlock(this.selectedId);
            }
        }

        initSortable() {
            if (typeof Sortable === 'undefined' || !this.canvas) return;

            if (this.sortableCanvas) {
                this.sortableCanvas.destroy();
                this.sortableCanvas = null;
            }
            if (this.sortableOutline) {
                this.sortableOutline.destroy();
                this.sortableOutline = null;
            }

            var self = this;

            if (this.view === 'outline') {
                var outline = this.canvas.querySelector('#pbe-outline');
                if (!outline) return;
                this.sortableOutline = new Sortable(outline, {
                    handle: '.pbe-outline-handle',
                    animation: 160,
                    ghostClass: 'sortable-ghost',
                    onEnd: function () { self.syncOrderFromDom('.pbe-outline-item'); }
                });
                return;
            }

            this.sortableCanvas = new Sortable(this.canvas, {
                draggable: '.pbe-block',
                handle: '.pbe-rail-btn.is-handle',
                animation: 170,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'is-dragging',
                onEnd: function () { self.syncOrderFromDom('.pbe-block'); }
            });
        }

        syncOrderFromDom(selector) {
            var nodes = this.canvas.querySelectorAll(selector);
            if (!nodes.length) return;

            var order = Array.prototype.map.call(nodes, function (node) { return node.dataset.blockId; });
            var map = {};
            this.blocks.forEach(function (block) { map[block.id] = block; });

            var next = order.map(function (id) { return map[id]; }).filter(Boolean);
            if (next.length !== this.blocks.length) return;

            var changed = next.some(function (block, index) { return this.blocks[index] !== block; }.bind(this));
            if (changed) this.pushHistory();
            this.blocks = next;
            this.render({ scrollTo: this.selectedId });
        }

        reinitializeAllBlocks() {
            if (window.ListBlockAdmin && typeof window.ListBlockAdmin.reinitializeAll === 'function') {
                window.ListBlockAdmin.reinitializeAll();
            }
        }

        showNotification(message, type) {
            this.toast(message, type === 'error' ? 'error' : 'success');
        }

        getBlocks() {
            return this.blocks;
        }
    }

    function boot() {
        if (!document.getElementById('post-builder') || window.postBlocksManager) return;
        window.postBlocksManager = new PostBlocksManager();
        window.PostBlocksManager = PostBlocksManager;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
