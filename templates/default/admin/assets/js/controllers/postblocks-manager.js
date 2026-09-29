(function () {
    'use strict';

    var root = document.querySelector('.pbm');
    if (!root) return;

    var adminUrl = window.ADMIN_URL || '/admin';
    var searchInput = document.getElementById('pbm-search-input');
    var searchClear = document.getElementById('pbm-search-clear');
    var chips = document.getElementById('pbm-category-chips');
    var grid = document.getElementById('pbm-grid');
    var nothing = document.getElementById('pbm-nothing');
    var lang = window.pbmLang || {};

    var cards = grid ? Array.prototype.slice.call(grid.querySelectorAll('.pbm-card')) : [];
    var activeCategory = 'all';
    var searchTerm = '';

    function notify(message, type) {
        if (window.notificationSystem && typeof window.notificationSystem.showNotification === 'function') {
            window.notificationSystem.showNotification(message, type === 'error' ? 'danger' : type || 'success');
            return;
        }
        if (window.console && type === 'error') console.error(message);
    }

    function applyFilters() {
        var visible = 0;
        cards.forEach(function (card) {
            var matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
            var matchesSearch = !searchTerm ||
                (card.dataset.search || '').indexOf(searchTerm) !== -1;
            var show = matchesCategory && matchesSearch;
            card.classList.toggle('is-hidden', !show);
            if (show) visible++;
        });
        if (nothing) nothing.style.display = (visible === 0 && cards.length) ? 'block' : 'none';
    }

    if (searchInput) {
        var debounceTimer = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                searchTerm = searchInput.value.trim().toLowerCase();
                root.querySelector('.pbm-search').classList.toggle('is-filled', !!searchTerm);
                applyFilters();
            }, 140);
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            searchTerm = '';
            root.querySelector('.pbm-search').classList.remove('is-filled');
            applyFilters();
        });
    }

    if (chips) {
        chips.addEventListener('click', function (e) {
            var chip = e.target.closest('.pbm-chip');
            if (!chip) return;
            Array.prototype.forEach.call(chips.querySelectorAll('.pbm-chip'), function (el) {
                el.classList.toggle('is-active', el === chip);
            });
            activeCategory = chip.dataset.pbmCategory || 'all';
            applyFilters();
        });
    }

    function setBusy(card, busy) {
        Array.prototype.forEach.call(card.querySelectorAll('.pbm-switch-input'), function (input) {
            input.disabled = !!busy;
        });
        card.classList.toggle('is-busy', !!busy);
    }

    function refreshOfflineState(card) {
        var posts = card.querySelector('[data-target="enable_in_posts"]');
        var pages = card.querySelector('[data-target="enable_in_pages"]');
        card.classList.toggle('is-offline', posts && pages && !posts.checked && !pages.checked);
    }

    root.addEventListener('change', function (e) {
        var input = e.target.closest('.pbm-switch-input');
        if (!input) return;

        var card = input.closest('.pbm-card');
        var payload = { system_name: card.dataset.systemName };
        payload[input.dataset.target] = input.checked ? 1 : 0;

        setBusy(card, true);

        fetch(adminUrl + '/post-blocks/save-settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function (data) {
                if (!data || !data.success) throw new Error((data && data.message) || 'error');
                refreshOfflineState(card);
                notify(data.message || lang.toggleSaved || 'OK', 'success');
            })
            .catch(function () {
                input.checked = !input.checked;
                refreshOfflineState(card);
                notify(lang.toggleError || 'Не удалось сохранить настройку', 'error');
            })
            .finally(function () {
                setBusy(card, false);
            });
    });

    cards.forEach(refreshOfflineState);

    var modalEl = document.getElementById('pbm-preview-modal');
    var modal = null;

    function ensureModal() {
        if (!modal && modalEl && window.bootstrap && window.bootstrap.Modal) {
            modal = new window.bootstrap.Modal(modalEl);
        }
        return modal;
    }

    function post(endpoint, payload) {
        return fetch(adminUrl + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        });
    }

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function section(title, innerHtml) {
        return '<div class="pbm-preview-section">' +
            '<div class="pbm-preview-section-title">' + title + '</div>' +
            '<div class="pbm-preview-stage">' + innerHtml + '</div>' +
            '</div>';
    }

    function renderSampleFailure(error) {
        return '<div class="pbm-preview-error"><i class="bi bi-exclamation-triangle me-1"></i>' +
            escapeHtml((lang.previewError || 'Preview error') + ': ' + (error && error.message ? error.message : error)) +
            '</div>';
    }

    function openPreview(card) {
        var instance = ensureModal();
        if (!instance) return;

        var systemName = card.dataset.systemName;
        var title = (card.querySelector('.pbm-card-title') || {}).textContent || systemName;

        var titleEl = document.getElementById('pbm-preview-title');
        var stateEl = document.getElementById('pbm-preview-state');
        var bodyEl = document.getElementById('pbm-preview-body');
        var editLink = document.getElementById('pbm-preview-edit');

        if (titleEl) titleEl.textContent = title;
        if (editLink) editLink.href = adminUrl + '/post-blocks/edit?system_name=' + encodeURIComponent(systemName);
        if (stateEl) stateEl.style.display = 'block';
        if (bodyEl) { bodyEl.style.display = 'none'; bodyEl.innerHTML = ''; }

        instance.show();

        var mainTemplateLabel = '<i class="bi bi-filetype-html"></i>' + escapeHtml(lang.previewMain || 'Template');
        var presetsLabel = '<i class="bi bi-stars"></i>' + escapeHtml(lang.previewPresets || 'Presets');

        Promise.all([
            post('/post-blocks/render-sample', { system_name: systemName }).catch(function (e) { return { error: e }; }),
            post('/post-blocks/get-presets', { system_name: systemName }).catch(function () { return { presets: [] }; })
        ]).then(function (results) {
            var main = results[0];
            var presetsData = results[1];
            var html = '';

            if (main && main.success) {
                html += section(mainTemplateLabel, main.html || '<span class="text-muted small">—</span>');
            } else {
                html += section(mainTemplateLabel, renderSampleFailure(main && main.error ? main.error : (main && main.message) || 'error'));
            }

            var presets = (presetsData && presetsData.success && Array.isArray(presetsData.presets)) ? presetsData.presets : [];

            if (presets.length) {
                var presetHtml = '<div class="pbm-preview-preset-grid"></div>';
                html += section(presetsLabel, presetHtml);

                var queue = Promise.resolve();
                var gridBox = null;

                presets.forEach(function (preset) {
                    queue = queue.then(function () {
                        return post('/post-blocks/render-sample', { system_name: systemName, preset_id: preset.id })
                            .then(function (data) {
                                gridBox = gridBox || (bodyEl && bodyEl.querySelector('.pbm-preview-preset-grid'));
                                var snippet = data && data.success
                                    ? (data.html || '')
                                    : renderSampleFailure((data && data.message) || 'error');
                                var block = document.createElement('div');
                                block.className = 'pbm-preview-preset-item';
                                block.innerHTML =
                                    '<div class="pbm-preview-preset-name"><i class="bi bi-star-fill"></i>' + escapeHtml(preset.preset_name) + '</div>' +
                                    '<div class="pbm-preview-stage">' + snippet + '</div>';
                                if (gridBox) gridBox.appendChild(block);
                            });
                    });
                });

                return queue;
            }
        }).then(function () {
            if (stateEl) stateEl.style.display = 'none';
            if (bodyEl) bodyEl.style.display = 'block';
        }).catch(function (error) {
            if (bodyEl) {
                bodyEl.innerHTML = section(mainTemplateLabel, renderSampleFailure(error));
                bodyEl.style.display = 'block';
            }
            if (stateEl) stateEl.style.display = 'none';
        });
    }

    root.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-pbm-action="preview"]');
        if (!btn) return;
        e.preventDefault();
        var card = btn.closest('.pbm-card');
        if (card) openPreview(card);
    });
})();
