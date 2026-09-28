const fs = require('fs');
const path = require('path');
const assert = require('assert').strict;
const { JSDOM } = require('jsdom');

const builderScript = fs.readFileSync(path.join(__dirname, '../templates/default/admin/assets/js/controllers/post-builder.js'), 'utf8');

const blocks = {
    TextBlock: { name: 'Текст', description: 'Текстовый блок', icon: 'bi bi-text-paragraph', category: 'text' },
    HeaderBlock: { name: 'Заголовок', description: 'Заголовок', icon: 'bi bi-type-h1', category: 'text' },
    ImageBlock: { name: 'Изображение', description: 'Фото', icon: 'bi bi-image', category: 'media' },
    QuoteBlock: { name: 'Цитата', description: 'Цитата', icon: 'bi bi-quote', category: 'text' },
    SpoilerBlock: { name: 'Спойлер', description: 'Спойлер', icon: 'bi bi-question-circle', category: 'advanced' },
    ListBlock: { name: 'Список', description: 'Список', icon: 'bi bi-list-ul', category: 'text' },
    GalleryBlock: { name: 'Галерея', description: 'Галерея', icon: 'bi bi-images', category: 'media' },
    VideoBlock: { name: 'Видео', description: 'Видео', icon: 'bi bi-play', category: 'media' },
    ButtonBlock: { name: 'Кнопка', description: 'Кнопка', icon: 'bi bi-box', category: 'basic' }
};

let requests = [];
async function mockFetch(url, init) {
    const action = url.split('/').pop();
    requests.push({ action, init });
    let data = {};
    const payload = typeof init.body === 'string' ? JSON.parse(init.body || '{}') : {};
    switch (action) {
        case 'get-block-blueprint':
            data = {
                success: true,
                content: payload.system_name === 'TextBlock' ? { content: '<p>Начните писать...</p>' } : { text: 'Начните писать...' },
                settings: { text_align: 'left' },
                presets: [{ id: 1, preset_name: 'Базовый', preset_template: '' }]
            };
            break;
        case 'get-previews':
            data = { success: true, previews: {} };
            for (const block of payload.blocks) {
                data.previews[block.block_id] = `<div class="post-block-preview"><span class="preview-text">${block.content.text || block.content.content || ''}</span><button type="button" class="preview-edit-btn" onclick="postBlocksManager.editBlock('${block.block_id}')">✎</button></div>`;
            }
            break;
        case 'get-preview':
            data = { success: true, html: `<div>${payload.content.text || payload.content.content || ''}</div>` };
            break;
        case 'get-presets':
            data = { success: true, presets: [{ id: 1, preset_name: 'Базовый', preset_template: '' }] };
            break;
        case 'upload-block-files':
            data = { success: true };
            break;
        case 'get-settings-form':
            return {
                ok: true,
                text: async () => '<form id="post-block-form"><ul class="nav nav-tabs"><li>Контент</li></ul><div class="tab-content"><div id="content-tab-pane" class="tab-pane"><input name="content[text]" value="Начните писать..."></div><div id="settings-tab-pane" class="tab-pane"><select name="settings[text_align]"><option value="left">Left</option><option value="center">Center</option></select></div></div></form>'
            };
    }
    return { ok: true, json: async () => data };
}

async function tick(ms = 10) { return new Promise(resolve => setTimeout(resolve, ms)); }

function createDom(initial = []) {
    requests = [];
    const dom = new JSDOM(`<!doctype html><html><head><meta name="admin-language" content="ru"></head><body>
        <form id="post-form"><input type="hidden" name="post_blocks" id="post_blocks_data" value="[]"><input name="title" value="Тест"><div id="post-builder" class="post-builder" data-storage-key="test-post"></div></form>
        </body></html>`, { url: 'https://example.com/admin/posts/create', runScripts: 'dangerously', pretendToBeVisual: true });
    const { window } = dom;
    window.availablePostBlocks = blocks;
    window.initialPostBlocks = initial;
    window.ADMIN_URL = '/admin';
    window.fetch = (url, init) => mockFetch(url, init);
    window.confirm = () => true;
    window.scrollTo = () => {};
    window.HTMLElement.prototype.scrollIntoView = () => {};
    window.eval(builderScript);
    window.document.dispatchEvent(new window.Event('DOMContentLoaded', { bubbles: true }));
    assert(window.postBlocksManager, 'Manager not initialized');
    return { window, dom, manager: window.postBlocksManager };
}

function sync(manager) { return JSON.parse(manager.hiddenField.value); }

(async () => {
    // Создание, вставка в нужное место, история, дублирование, удаление.
    {
        const { window, dom, manager: m } = createDom();
        assert.equal(m.blocks.length, 0);
        assert(window.document.querySelector('.pbe-empty'));
        assert.equal(m.picker, null);

        let b1 = await m.addBlock('TextBlock');
        await tick(10);
        assert.equal(m.blocks.length, 1);
        assert.equal(sync(m)[0].type, 'TextBlock');
        assert(window.document.querySelector('.pbe-block'));
        assert(window.document.querySelector('#pbe-inspector').classList.contains('is-open'));
        assert.equal(requests.filter(x => x.action === 'get-block-blueprint').length, 1);
        m.closeInspector();
        window.document.querySelector('.pbe-block-body').click();
        assert(!m.inspector.classList.contains('is-open'), 'single click selects text without opening an inspector');

        let b2 = await m.addBlock('HeaderBlock', 0);
        await tick(10);
        assert.equal(sync(m)[0].type, 'HeaderBlock');
        assert.equal(sync(m)[1].type, 'TextBlock');
        m.duplicateBlock(b2.id);
        assert.equal(m.blocks.length, 3);
        assert.equal(m.blocks[1].type, 'HeaderBlock');
        assert.notEqual(m.blocks[0].id, m.blocks[1].id);
        m.undo();
        assert.equal(m.blocks.length, 2, 'undo duplication');
        m.redo();
        assert.equal(m.blocks.length, 3, 'redo duplication');
        m.moveBlock(b1.id, -1);
        assert.equal(m.blocks[1].id, b1.id);
        m.undo();
        assert.equal(m.blocks[2].id, b1.id, 'undo move');
        m.removeBlock(b1.id, { silent: true });
        assert.equal(m.blocks.length, 2);
        m.undo();
        assert.equal(m.blocks.length, 3, 'undo delete');
        assert.equal(sync(m).length, 3);

        // Поиск и структура.
        m.applySearch('Текст');
        assert.equal(m.matches.length, 1);
        m.setView('outline');
        assert(window.document.querySelector('.pbe-outline-item'));
        m.applySearch('');
        m.setView('canvas');
        assert.equal(window.document.querySelectorAll('.pbe-slot').length, 4);

        // Вставка через палитру.
        m.openPicker(1);
        assert(m.picker);
        assert(m.picker.querySelector('[data-type="ImageBlock"]'));
        assert.equal(m.picker.querySelector('[data-category="all"]').textContent, 'Все');
        m.picker.querySelector('#pbe-picker-input').value = 'изо';
        m.picker.querySelector('#pbe-picker-input').dispatchEvent(new window.Event('input', { bubbles: true }));
        assert.equal(m.picker.querySelectorAll('[data-pbe="picker-item"]').length, 1);
        m.closePicker();

        // Готовые шаблоны используют только включённые в постах блоки.
        const beforeTemplate = m.blocks.length;
        await m.insertTemplate('article');
        assert.equal(m.blocks.length, beforeTemplate + 4);
        assert.equal(sync(m).length, beforeTemplate + 4);
        m.undo();
        assert.equal(m.blocks.length, beforeTemplate, 'undo template insertion');

        // Вложенные поля блоков (например, галерея и список) сериализуются.
        m.editBlock(b2.id);
        await tick(10);
        const form = m.inspector.querySelector('form');
        form.insertAdjacentHTML('beforeend', `
            <input name="content[images][0][image_url]" value="/uploads/a.jpg">
            <input name="content[images][0][alt_text]" value="Первое фото">
            <input name="content[images][1][image_url]" value="/uploads/b.jpg">
            <input name="content[items][]" value="Пункт А">
            <input name="content[items][]" value="Пункт Б">`);
        const collected = m.collectForm(m.inspector.querySelector('#pbe-insp-body'));
        assert.equal(collected.content.images[0].image_url, '/uploads/a.jpg');
        assert.equal(collected.content.images[0].alt_text, 'Первое фото');
        assert.equal(collected.content.images[1].image_url, '/uploads/b.jpg');
        assert.deepEqual(Array.from(collected.content.items), ['Пункт А', 'Пункт Б']);
        m.closeInspector();

        // Отправка сохраняет порядок и данные.
        assert.equal(sync(m).length, 3);
        assert.equal(sync(m)[2].order, 2);
        clearInterval(m.autosaveTimer);
        await tick(40);
        dom.window.close();
    }

    // Редактирование и инспектор (в т.ч. id из БД).
    {
        const initial = [
            { id: 17, type: 'HeaderBlock', content: { text: 'Старый заголовок' }, settings: { text_align: 'left' }, order: 1 },
            { id: 18, type: 'TextBlock', content: { content: '<p>Оригинал</p>' }, settings: {}, order: 0 }
        ];
        const { window, dom, manager: m } = createDom(initial);
        assert.equal(m.blocks[0].id, '18');
        assert.equal(m.blocks[1].id, '17');
        m.editBlock('17');
        await tick(10);
        assert.equal(m.inspectorBlockId, '17');
        assert.equal(m.inspector.querySelector('[name="content[text]"]').value, 'Старый заголовок');
        let select = m.inspector.querySelector('#block-preset-select');
        assert(select, 'preset selector');
        assert(select.closest('.tab-content'), 'preset tab participates in inspector tab switching');
        select.value = '1';
        select.dispatchEvent(new window.Event('change', { bubbles: true }));
        await tick(530);
        assert.equal(m.findBlock('17').settings.preset_id, '1');
        select.value = '';
        select.dispatchEvent(new window.Event('change', { bubbles: true }));
        await tick(530);
        assert.equal(m.findBlock('17').settings.preset_id, '', 'No preset clears previous preset');
        let input = m.inspector.querySelector('[name="content[text]"]');
        input.value = 'Новый заголовок';
        input.dispatchEvent(new window.Event('input', { bubbles: true }));
        await tick(530);
        assert.equal(m.findBlock('17').content.text, 'Новый заголовок');
        assert.equal(sync(m)[1].content.text, 'Новый заголовок');
        m.undo();
        assert.equal(m.findBlock('17').content.text, 'Старый заголовок', 'undo live edit');
        m.redo();
        assert.equal(m.findBlock('17').content.text, 'Новый заголовок', 'redo live edit');

        // inline edit (блочный заголовок без модалки)
        m.startInlineEdit('17');
        let inline = m.canvas.querySelector('.pbe-inline');
        assert(inline);
        inline.textContent = 'Инлайн заголовок';
        m.canvas.querySelector('[data-pbe="inline-save"]').click();
        await tick(10);
        assert.equal(m.findBlock('17').content.text, 'Инлайн заголовок');
        assert.equal(sync(m)[1].content.text, 'Инлайн заголовок');
        m.undo();
        assert.equal(m.findBlock('17').content.text, 'Новый заголовок', 'undo inline edit');

        m.editBlock('17');
        await tick(10);
        input = m.inspector.querySelector('[name="content[text]"]');
        input.value = 'После применения';
        const applied = await m.applyInspector();
        assert.equal(applied, true);
        assert.equal(sync(m)[1].content.text, 'После применения');
        assert(requests.some(x => x.action === 'upload-block-files'));

        // Сохранение поста сначала применяет открытый инспектор, затем
        // сериализует обновлённые блоки и отправляет общую форму.
        m.editBlock('17');
        await tick(10);
        input = m.inspector.querySelector('[name="content[text]"]');
        input.value = 'Последняя правка перед отправкой';
        window.document.getElementById('post-form').addEventListener('submit', event => event.preventDefault());
        window.document.getElementById('post-form').requestSubmit();
        await tick(35);
        assert.equal(sync(m)[1].content.text, 'Последняя правка перед отправкой');
        assert.equal(m.inspectorBlockId, null);

        clearInterval(m.autosaveTimer);
        await tick(40);
        dom.window.close();
    }

    // Восстановление черновика не перетирает данные без разрешения.
    {
        const { window, dom, manager: m } = createDom([]);
        window.localStorage.setItem('bloggy.builder.draft:test-post', JSON.stringify({
            blocks: [{ id: 'draft-1', type: 'TextBlock', content: { content: 'Черновик' }, settings: {}, order: 0 }],
            at: Date.now()
        }));
        m.checkDraft();
        assert.equal(m.blocks.length, 0, 'draft is not restored silently');
        assert(m.noticeEl.querySelector('[data-pbe="draft-restore"]'));
        m.restoreDraft();
        assert.equal(sync(m)[0].content.content, 'Черновик');
        clearInterval(m.autosaveTimer);
        await tick(40);
        dom.window.close();
    }

    console.log('PASS builder integration: create/edit, ordering, history, search, picker, inspector, nested fields, drafts, templates, serialization');
})().catch(e => { console.error(e); process.exit(1); });
