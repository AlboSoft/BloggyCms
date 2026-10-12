<?php
    add_admin_css('templates/default/admin/assets/css/controllers/html-blocks-cards.css');

    $totalAvailable = count($blockTypes);
    $systemCount = 0;
    $themeCount = 0;

    foreach ($blockTypes as $sn => $t) {
        if (($t['source'] ?? 'system') === 'template') {
            $themeCount++;
        } else {
            $systemCount++;
        }
    }

    $accents = ['sky', 'indigo', 'emerald', 'violet', 'amber', 'teal', 'rose'];
?>

<div class="container-fluid p-0 hbc-page">
    <div class="hbc-hero">
        <div class="hbc-hero-main">
            <div class="hbc-hero-icon">
                <?php echo bloggy_icon('bs', 'plus-square-dotted', '24', '#ffffff'); ?>
            </div>
            <div>
                <h1 class="hbc-hero-title"><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_TITLE; ?></h1>
                <p class="hbc-hero-subtitle"><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_COMPATIBILITY_HINT; ?></p>
                <?php if (!empty($currentTemplate)) { ?>
                    <div class="hbc-hero-meta">
                        <span class="hbc-theme-pill">
                            <i class="bi bi-palette2"></i>
                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_ACTIVE_TEMPLATE; ?>
                            <code><?php echo html($currentTemplate); ?></code>
                        </span>
                        <span class="hbc-theme-pill">
                            <i class="bi bi-boxes"></i>
                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_STAT_AVAILABLE; ?>:
                            <strong><?php echo $totalAvailable; ?></strong>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="hbc-hero-actions">
            <a href="<?php echo ADMIN_URL; ?>/html-blocks/types" class="hbc-btn hbc-btn-outline">
                <?php echo bloggy_icon('bs', 'sliders', '15', 'currentColor'); ?>
                <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_MANAGE_TYPES_BTN; ?></span>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks" class="hbc-btn hbc-btn-outline">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', 'currentColor'); ?>
                <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_BACK_BTN; ?></span>
            </a>
        </div>
    </div>

    <?php if (empty($blockTypes)) { ?>
        <div class="hbc-empty-state">
            <div class="hbc-empty-icon">
                <i class="bi bi-inbox"></i>
            </div>
            <h4 class="hbc-empty-title"><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_TITLE; ?></h4>
            <p class="hbc-empty-text mb-0"><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_NO_TYPES; ?></p>
        </div>
    <?php } else { ?>
        <div class="hbc-toolbar">
            <div class="hbc-search-wrap">
                <i class="bi bi-search hbc-search-icon"></i>
                <input type="text"
                       id="hbcSelectSearch"
                       class="hbc-search-input"
                       placeholder="<?php echo html(LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_SEARCH_PLACEHOLDER); ?>"
                       autocomplete="off">
                <button type="button" id="hbcSelectSearchClear" class="hbc-search-clear" aria-label="Clear">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="hbc-filter-group" id="hbcSelectFilters">
                <button type="button" class="hbc-filter-btn is-active" data-filter="all">
                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_FILTER_ALL; ?></span>
                    <span class="hbc-filter-count"><?php echo $totalAvailable; ?></span>
                </button>
                <button type="button" class="hbc-filter-btn" data-filter="system">
                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_FILTER_SYSTEM; ?></span>
                    <span class="hbc-filter-count"><?php echo $systemCount; ?></span>
                </button>
                <?php if ($themeCount > 0) { ?>
                    <button type="button" class="hbc-filter-btn" data-filter="template">
                        <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_FILTER_THEME; ?></span>
                        <span class="hbc-filter-count"><?php echo $themeCount; ?></span>
                    </button>
                <?php } ?>
            </div>
        </div>

        <div class="hbc-grid" id="hbcSelectGrid">
            <?php
            $idx = 0;
            foreach ($blockTypes as $type) {
                $systemName = $type['system_name'] ?? '';
                $isFeatured = ($systemName === 'DefaultBlock');
                $source = $type['source'] ?? 'system';
                $accent = $isFeatured ? 'sky' : $accents[$idx % count($accents)];
                $idx++;

                $rawIcon = trim($type['icon'] ?? 'bi-puzzle');
                if (strpos($rawIcon, 'bi ') !== 0 && strpos($rawIcon, 'bi-') === 0) {
                    $iconClass = 'bi ' . $rawIcon;
                } elseif (strpos($rawIcon, 'bi') !== 0) {
                    $iconClass = 'bi bi-' . $rawIcon;
                } else {
                    $iconClass = $rawIcon;
                }

                $desc = trim($type['description'] ?? '');
                $shortDesc = trim($type['short_description'] ?? '');
                $displayDesc = $desc !== '' ? $desc : $shortDesc;

                $blocksCount = (int)($type['blocks_count'] ?? 0);
                $templateScope = $type['template'] ?? 'all';
                $createUrl = ADMIN_URL . '/html-blocks/create?type=' . urlencode($systemName);
                $searchText = mb_strtolower(($type['name'] ?? '') . ' ' . $systemName . ' ' . $displayDesc . ' ' . $shortDesc . ' ' . ($type['author'] ?? ''));

                $cardClasses = ['hbc-card', 'hbc-card-selectable'];
                if ($isFeatured) {
                    $cardClasses[] = 'is-featured';
                }
            ?>
                <article class="<?php echo implode(' ', $cardClasses); ?>"
                         data-accent="<?php echo $accent; ?>"
                         data-source="<?php echo html($source); ?>"
                         data-search="<?php echo html($searchText); ?>"
                         data-create-url="<?php echo html($createUrl); ?>">

                    <div class="hbc-card-top">
                        <div class="hbc-card-identity">
                            <div class="hbc-card-icon">
                                <i class="<?php echo html($iconClass); ?>"></i>
                            </div>
                            <div class="hbc-card-titles">
                                <h3 class="hbc-card-name"><?php echo html($type['name'] ?? $systemName); ?></h3>
                                <span class="hbc-card-sysname" title="<?php echo html($systemName); ?>">
                                    <?php echo html($systemName); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($isFeatured) { ?>
                            <span class="hbc-status-badge status-universal">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_UNIVERSAL_BADGE; ?>
                            </span>
                        <?php } ?>
                    </div>

                    <div class="hbc-card-body">
                        <div>
                            <?php if ($displayDesc !== '') { ?>
                                <p class="hbc-card-desc" title="<?php echo html($displayDesc); ?>">
                                    <?php echo html($displayDesc); ?>
                                </p>
                            <?php } ?>
                        </div>

                        <div class="hbc-meta-chips">
                            <?php if ($templateScope === 'all' || $templateScope === '' || $templateScope === null) { ?>
                                <span class="hbc-chip chip-theme-all">
                                    <i class="bi bi-globe2"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_ALL_TEMPLATES; ?></span>
                                </span>
                            <?php } else { ?>
                                <span class="hbc-chip chip-theme-custom">
                                    <i class="bi bi-palette"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_TEMPLATE_LABEL; ?> <?php echo html($templateScope); ?></span>
                                </span>
                            <?php } ?>

                            <?php if ($source === 'template' && !empty($type['source_template'])) { ?>
                                <span class="hbc-chip chip-source-theme">
                                    <i class="bi bi-folder-symlink"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_PROVIDED_BY_THEME; ?> <?php echo html($type['source_template']); ?></span>
                                </span>
                            <?php } else { ?>
                                <span class="hbc-chip">
                                    <i class="bi bi-hdd-stack"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_SYSTEM_SOURCE; ?></span>
                                </span>
                            <?php } ?>

                            <?php if ($blocksCount > 0) { ?>
                                <span class="hbc-chip chip-usage-active">
                                    <i class="bi bi-layers"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_USED_BLOCKS; ?> <?php echo $blocksCount; ?></span>
                                </span>
                            <?php } ?>

                            <span class="hbc-chip">
                                <i class="bi bi-tag"></i>
                                <span>v<?php echo html($type['version'] ?? '1.0.0'); ?></span>
                            </span>
                        </div>
                    </div>

                    <div class="hbc-card-footer">
                        <div class="hbc-author-box">
                            <span class="hbc-author-avatar">
                                <i class="bi bi-person"></i>
                            </span>
                            <?php if (!empty($type['author_website'])) { ?>
                                <a href="<?php echo html($type['author_website']); ?>"
                                   target="_blank"
                                   rel="noopener"
                                   class="hbc-author-name"
                                   onclick="event.stopPropagation();">
                                    <?php echo html($type['author'] ?? 'BloggyCMS'); ?>
                                </a>
                            <?php } else { ?>
                                <span class="hbc-author-name"><?php echo html($type['author'] ?? 'BloggyCMS'); ?></span>
                            <?php } ?>
                        </div>

                        <a href="<?php echo html($createUrl); ?>" class="hbc-select-cta">
                            <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_CREATE_BTN; ?></span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </article>
            <?php } ?>
        </div>

        <div class="hbc-empty-state" id="hbcSelectNoResults" style="display: none;">
            <div class="hbc-empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <h4 class="hbc-empty-title"><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_NO_RESULTS_TITLE; ?></h4>
            <p class="hbc-empty-text"><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_NO_RESULTS_TEXT; ?></p>
            <button type="button" class="hbc-btn hbc-btn-outline" id="hbcSelectResetBtn">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_RESET_FILTER; ?></span>
            </button>
        </div>
    <?php } ?>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('hbcSelectSearch');
    const clearBtn = document.getElementById('hbcSelectSearchClear');
    const filterBtns = document.querySelectorAll('#hbcSelectFilters .hbc-filter-btn');
    const cards = document.querySelectorAll('#hbcSelectGrid .hbc-card');
    const noResults = document.getElementById('hbcSelectNoResults');
    const resetBtn = document.getElementById('hbcSelectResetBtn');

    if (!cards.length) return;

    let currentFilter = 'all';

    cards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            if (e.target.closest('a, button, input')) return;
            const url = this.getAttribute('data-create-url');
            if (url) {
                window.location.href = url;
            }
        });
    });

    function applyFilter() {
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        if (clearBtn) {
            clearBtn.style.display = query.length > 0 ? 'inline-flex' : 'none';
        }

        let visibleCount = 0;

        cards.forEach(function(card) {
            const source = card.getAttribute('data-source');
            const search = card.getAttribute('data-search') || '';

            let matchesFilter = true;
            if (currentFilter === 'system') {
                matchesFilter = (source === 'system');
            } else if (currentFilter === 'template') {
                matchesFilter = (source === 'template');
            }

            const matchesQuery = !query || search.indexOf(query) !== -1;

            if (matchesFilter && matchesQuery) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
    }

    if (clearBtn && searchInput) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            applyFilter();
        });
    }

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterBtns.forEach(function(b) { b.classList.remove('is-active'); });
            this.classList.add('is-active');
            currentFilter = this.getAttribute('data-filter') || 'all';
            applyFilter();
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            currentFilter = 'all';
            filterBtns.forEach(function(b) {
                b.classList.toggle('is-active', b.getAttribute('data-filter') === 'all');
            });
            applyFilter();
        });
    }
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>
