<?php
    add_admin_css('templates/default/admin/assets/css/controllers/html-blocks-cards.css');

    $totalTypes = count($blockTypes);
    $activeCount = 0;
    $disabledCount = 0;
    $systemCount = 0;
    $templateCount = 0;
    $totalBlocksUsed = 0;

    foreach ($blockTypes as $sn => $t) {
        if (!empty($t['is_active'])) {
            $activeCount++;
        } else {
            $disabledCount++;
        }
        if (($t['source'] ?? 'system') === 'template') {
            $templateCount++;
        } else {
            $systemCount++;
        }
        $totalBlocksUsed += (int)($t['blocks_count'] ?? 0);
    }

    $accents = ['sky', 'indigo', 'emerald', 'violet', 'amber', 'teal', 'rose'];
?>

<div class="container-fluid p-0 hbc-page">
    <div class="hbc-hero">
        <div class="hbc-hero-main">
            <div class="hbc-hero-icon">
                <?php echo bloggy_icon('bs', 'grid-1x2-fill', '24', '#ffffff'); ?>
            </div>
            <div>
                <h1 class="hbc-hero-title"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TITLE; ?></h1>
                <p class="hbc-hero-subtitle"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ALERT_TEXT; ?></p>
                <?php if (!empty($currentTemplate)) { ?>
                    <div class="hbc-hero-meta">
                        <span class="hbc-theme-pill">
                            <i class="bi bi-palette2"></i>
                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_CURRENT_TEMPLATE; ?>
                            <code><?php echo html($currentTemplate); ?></code>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="hbc-hero-actions">
            <a href="<?php echo ADMIN_URL; ?>/html-blocks" class="hbc-btn hbc-btn-outline">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', 'currentColor'); ?>
                <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_BACK_TO_BLOCKS; ?></span>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks/select-type" class="hbc-btn hbc-btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#ffffff'); ?>
                <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_CREATE_BLOCK; ?></span>
            </a>
        </div>
    </div>

    <?php if (!empty($blockTypes)) { ?>
        <div class="hbc-stats-row">
            <div class="hbc-stat-card">
                <div class="hbc-stat-icon is-blue">
                    <i class="bi bi-boxes"></i>
                </div>
                <div>
                    <div class="hbc-stat-value"><?php echo $totalTypes; ?></div>
                    <div class="hbc-stat-label"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STAT_TOTAL; ?></div>
                </div>
            </div>
            <div class="hbc-stat-card">
                <div class="hbc-stat-icon is-green">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <div class="hbc-stat-value"><?php echo $activeCount; ?></div>
                    <div class="hbc-stat-label"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STAT_ACTIVE; ?></div>
                </div>
            </div>
            <div class="hbc-stat-card">
                <div class="hbc-stat-icon is-amber">
                    <i class="bi bi-pause-circle"></i>
                </div>
                <div>
                    <div class="hbc-stat-value"><?php echo $disabledCount; ?></div>
                    <div class="hbc-stat-label"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STAT_DISABLED; ?></div>
                </div>
            </div>
            <div class="hbc-stat-card">
                <div class="hbc-stat-icon is-violet">
                    <i class="bi bi-layers"></i>
                </div>
                <div>
                    <div class="hbc-stat-value"><?php echo $totalBlocksUsed; ?></div>
                    <div class="hbc-stat-label"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STAT_BLOCKS_USED; ?></div>
                </div>
            </div>
        </div>

        <div class="hbc-toolbar">
            <div class="hbc-search-wrap">
                <i class="bi bi-search hbc-search-icon"></i>
                <input type="text"
                       id="hbcTypesSearch"
                       class="hbc-search-input"
                       placeholder="<?php echo html(LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_SEARCH_PLACEHOLDER); ?>"
                       autocomplete="off">
                <button type="button" id="hbcTypesSearchClear" class="hbc-search-clear" aria-label="Clear">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="hbc-filter-group" id="hbcTypesFilters">
                <button type="button" class="hbc-filter-btn is-active" data-filter="all">
                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_FILTER_ALL; ?></span>
                    <span class="hbc-filter-count"><?php echo $totalTypes; ?></span>
                </button>
                <button type="button" class="hbc-filter-btn" data-filter="active">
                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_FILTER_ACTIVE; ?></span>
                    <span class="hbc-filter-count"><?php echo $activeCount; ?></span>
                </button>
                <button type="button" class="hbc-filter-btn" data-filter="disabled">
                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_FILTER_DISABLED; ?></span>
                    <span class="hbc-filter-count"><?php echo $disabledCount; ?></span>
                </button>
                <button type="button" class="hbc-filter-btn" data-filter="system">
                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_FILTER_SYSTEM; ?></span>
                    <span class="hbc-filter-count"><?php echo $systemCount; ?></span>
                </button>
                <?php if ($templateCount > 0) { ?>
                    <button type="button" class="hbc-filter-btn" data-filter="template">
                        <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_FILTER_TEMPLATE; ?></span>
                        <span class="hbc-filter-count"><?php echo $templateCount; ?></span>
                    </button>
                <?php } ?>
            </div>
        </div>

        <div class="hbc-grid" id="hbcTypesGrid">
            <?php
            $idx = 0;
            foreach ($blockTypes as $systemName => $type) {
                $isActive = !empty($type['is_active']);
                $isTemplateOk = $type['is_template_compatible'] ?? true;
                $source = $type['source'] ?? 'system';
                $accent = $accents[$idx % count($accents)];
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
                $searchText = mb_strtolower(($type['name'] ?? '') . ' ' . $systemName . ' ' . $displayDesc . ' ' . ($type['author'] ?? '') . ' ' . $templateScope);

                $cardClasses = ['hbc-card'];
                if (!$isActive) {
                    $cardClasses[] = 'is-disabled';
                } elseif (!$isTemplateOk) {
                    $cardClasses[] = 'is-mismatch';
                } else {
                    $cardClasses[] = 'is-active';
                }
            ?>
                <article class="<?php echo implode(' ', $cardClasses); ?>"
                         data-accent="<?php echo $accent; ?>"
                         data-status="<?php echo $isActive ? 'active' : 'disabled'; ?>"
                         data-source="<?php echo html($source); ?>"
                         data-search="<?php echo html($searchText); ?>">

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

                        <?php if (!$isActive) { ?>
                            <span class="hbc-status-badge status-disabled">
                                <span class="hbc-status-dot"></span>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STATUS_DISABLED; ?>
                            </span>
                        <?php } elseif (!$isTemplateOk) { ?>
                            <span class="hbc-status-badge status-mismatch">
                                <span class="hbc-status-dot"></span>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STATUS_TEMPLATE_MISMATCH; ?>
                            </span>
                        <?php } else { ?>
                            <span class="hbc-status-badge status-active">
                                <span class="hbc-status-dot"></span>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_STATUS_ACTIVE; ?>
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

                            <?php if (!$isActive) { ?>
                                <div class="hbc-card-note">
                                    <i class="bi bi-eye-slash"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_HIDDEN_ON_CREATE; ?></span>
                                </div>
                            <?php } elseif (!$isTemplateOk) { ?>
                                <div class="hbc-card-note">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <span><?php echo sprintf(LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ONLY_FOR_TEMPLATE, html($templateScope)); ?></span>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="hbc-meta-chips">
                            <?php if ($templateScope === 'all' || $templateScope === '' || $templateScope === null) { ?>
                                <span class="hbc-chip chip-theme-all" title="<?php echo html(LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_TEMPLATE); ?>">
                                    <i class="bi bi-globe2"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ALL_TEMPLATES; ?></span>
                                </span>
                            <?php } else { ?>
                                <span class="hbc-chip chip-theme-custom" title="<?php echo html(LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_TABLE_TEMPLATE); ?>">
                                    <i class="bi bi-palette"></i>
                                    <span><?php echo html($templateScope); ?></span>
                                </span>
                            <?php } ?>

                            <?php if ($source === 'template' && !empty($type['source_template'])) { ?>
                                <span class="hbc-chip chip-source-theme">
                                    <i class="bi bi-folder-symlink"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_PROVIDED_BY_THEME; ?> <?php echo html($type['source_template']); ?></span>
                                </span>
                            <?php } else { ?>
                                <span class="hbc-chip">
                                    <i class="bi bi-hdd-stack"></i>
                                    <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_SYSTEM_BADGE; ?></span>
                                </span>
                            <?php } ?>

                            <span class="hbc-chip <?php echo $blocksCount > 0 ? 'chip-usage-active' : ''; ?>">
                                <i class="bi bi-layers"></i>
                                <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_BLOCKS_COUNT; ?> <?php echo $blocksCount; ?></span>
                            </span>

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
                                <a href="<?php echo html($type['author_website']); ?>" target="_blank" rel="noopener" class="hbc-author-name">
                                    <?php echo html($type['author'] ?? 'BloggyCMS'); ?>
                                </a>
                            <?php } else { ?>
                                <span class="hbc-author-name"><?php echo html($type['author'] ?? 'BloggyCMS'); ?></span>
                            <?php } ?>
                        </div>

                        <div class="hbc-card-actions">
                            <?php if ($isActive && $isTemplateOk) { ?>
                                <a href="<?php echo ADMIN_URL; ?>/html-blocks/create?type=<?php echo urlencode($systemName); ?>"
                                   class="hbc-action-btn btn-create-quick icon-only"
                                   title="<?php echo html(LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_CREATE_BLOCK); ?>">
                                    <i class="bi bi-plus-lg"></i>
                                </a>
                            <?php } ?>

                            <form method="post"
                                  action="<?php echo ADMIN_URL; ?>/html-blocks/types/toggle/<?php echo urlencode($systemName); ?>"
                                  class="d-inline">
                                <button type="submit"
                                        class="hbc-action-btn <?php echo $isActive ? 'btn-toggle-off' : 'btn-toggle-on'; ?>"
                                        title="<?php echo $isActive ? LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_DISABLE_TITLE : LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ENABLE_TITLE; ?>">
                                    <?php if ($isActive) { ?>
                                        <i class="bi bi-pause-fill"></i>
                                        <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_DISABLE_TITLE; ?></span>
                                    <?php } else { ?>
                                        <i class="bi bi-play-fill"></i>
                                        <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_ENABLE_TITLE; ?></span>
                                    <?php } ?>
                                </button>
                            </form>

                            <?php if ($systemName !== 'DefaultBlock') { ?>
                                <form method="post"
                                      action="<?php echo ADMIN_URL; ?>/html-blocks/types/delete/<?php echo urlencode($systemName); ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('<?php echo sprintf(LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_DELETE_CONFIRM, addslashes($systemName)); ?>')">
                                    <button type="submit"
                                            class="hbc-action-btn btn-delete icon-only"
                                            title="<?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_DELETE_TITLE; ?>">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php } ?>
        </div>

        <div class="hbc-empty-state" id="hbcTypesNoResults" style="display: none;">
            <div class="hbc-empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <h4 class="hbc-empty-title"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_NO_RESULTS_TITLE; ?></h4>
            <p class="hbc-empty-text"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_NO_RESULTS_TEXT; ?></p>
            <button type="button" class="hbc-btn hbc-btn-outline" id="hbcTypesResetBtn">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_RESET_FILTER; ?></span>
            </button>
        </div>
    <?php } else { ?>
        <div class="hbc-empty-state">
            <div class="hbc-empty-icon">
                <i class="bi bi-puzzle"></i>
            </div>
            <h4 class="hbc-empty-title"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_NO_TYPES_TITLE; ?></h4>
            <p class="hbc-empty-text mb-0"><?php echo LANG_TEMPLATE_HTMLBLOCKS_TYPES_INDEX_NO_TYPES_TEXT; ?></p>
        </div>
    <?php } ?>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('hbcTypesSearch');
    const clearBtn = document.getElementById('hbcTypesSearchClear');
    const filterBtns = document.querySelectorAll('#hbcTypesFilters .hbc-filter-btn');
    const cards = document.querySelectorAll('#hbcTypesGrid .hbc-card');
    const noResults = document.getElementById('hbcTypesNoResults');
    const resetBtn = document.getElementById('hbcTypesResetBtn');

    if (!cards.length) return;

    let currentFilter = 'all';

    function applyFilter() {
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        if (clearBtn) {
            clearBtn.style.display = query.length > 0 ? 'inline-flex' : 'none';
        }

        let visibleCount = 0;

        cards.forEach(function(card) {
            const status = card.getAttribute('data-status');
            const source = card.getAttribute('data-source');
            const search = card.getAttribute('data-search') || '';

            let matchesFilter = true;
            if (currentFilter === 'active') {
                matchesFilter = (status === 'active');
            } else if (currentFilter === 'disabled') {
                matchesFilter = (status === 'disabled');
            } else if (currentFilter === 'system') {
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
