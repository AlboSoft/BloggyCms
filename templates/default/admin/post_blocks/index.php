<?php
add_admin_css('templates/default/admin/assets/css/controllers/postblocks-manager.css');
add_admin_js('templates/default/admin/assets/js/controllers/postblocks-manager.js');

$categoryLabels = [
    'basic'    => LANG_TEMPLATE_POSTBLOCKS_INDEX_CAT_BASIC,
    'text'     => LANG_TEMPLATE_POSTBLOCKS_INDEX_CAT_TEXT,
    'media'    => LANG_TEMPLATE_POSTBLOCKS_INDEX_CAT_MEDIA,
    'layout'   => LANG_TEMPLATE_POSTBLOCKS_INDEX_CAT_LAYOUT,
    'advanced' => LANG_TEMPLATE_POSTBLOCKS_INDEX_CAT_ADVANCED,
    'other'    => LANG_TEMPLATE_POSTBLOCKS_INDEX_CAT_OTHER,
    'general'  => LANG_TEMPLATE_POSTBLOCKS_INDEX_CAT_OTHER,
];

$knownCats = ['basic', 'text', 'media', 'layout', 'advanced', 'other'];
$categoryOrder = array_values(array_filter($knownCats,
    fn($cat) => isset($postBlocksByCategory[$cat]) && $postBlocksByCategory[$cat]));
foreach (array_keys($postBlocksByCategory) as $cat) {
    if (!in_array($cat, $categoryOrder, true)) {
        $categoryOrder[] = $cat;
    }
}
?>

<div class="pbm">
    <div class="pbm-head">
        <div>
            <h4 class="pbm-title">
                <?php echo bloggy_icon('bs', 'bricks', '24', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_TITLE; ?>
            </h4>
            <div class="pbm-subtitle"><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_SUBTITLE; ?></div>
        </div>
        <div class="pbm-stats">
            <div class="pbm-stat">
                <b><?php echo (int)$blocksStats['total']; ?></b>
                <span><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_STAT_TOTAL; ?></span>
            </div>
            <div class="pbm-stat">
                <b><?php echo (int)$blocksStats['enabled_posts']; ?></b>
                <span><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_STAT_POSTS; ?></span>
            </div>
            <div class="pbm-stat">
                <b><?php echo (int)$blocksStats['enabled_pages']; ?></b>
                <span><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_STAT_PAGES; ?></span>
            </div>
            <div class="pbm-stat is-accent">
                <b><?php echo (int)$blocksStats['presets']; ?></b>
                <span><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_STAT_PRESETS; ?></span>
            </div>
            <div class="pbm-stat">
                <b><?php echo (int)$blocksStats['custom_templates']; ?></b>
                <span><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_STAT_TEMPLATES; ?></span>
            </div>
        </div>
    </div>

    <div class="pbm-toolbar">
        <div class="pbm-search">
            <i class="bi bi-search"></i>
            <input type="text" id="pbm-search-input"
                   placeholder="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_SEARCH_PLACEHOLDER); ?>"
                   autocomplete="off">
            <button type="button" class="pbm-search-clear" id="pbm-search-clear"
                    aria-label="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_SEARCH_CLEAR); ?>">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="pbm-chips" id="pbm-category-chips">
            <button type="button" class="pbm-chip is-active" data-pbm-category="all">
                <?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_FILTER_ALL; ?>
                <span class="pbm-chip-count"><?php echo count($postBlocks); ?></span>
            </button>
            <?php foreach ($categoryOrder as $cat) {
                $catBlocks = $postBlocksByCategory[$cat] ?? [];
                if (!$catBlocks) continue;
                ?>
                <button type="button" class="pbm-chip" data-pbm-category="<?php echo html($cat); ?>">
                    <?php echo html($categoryLabels[$cat] ?? ucfirst($cat)); ?>
                    <span class="pbm-chip-count"><?php echo count($catBlocks); ?></span>
                </button>
            <?php } ?>
        </div>
    </div>

    <?php if (empty($postBlocks)) { ?>
        <div class="pbm-empty">
            <i class="bi bi-boxes"></i>
            <h5><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_EMPTY_TITLE; ?></h5>
            <p><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_EMPTY_TEXT; ?></p>
        </div>
    <?php } else { ?>
        <div class="pbm-grid" id="pbm-grid">
            <?php foreach ($postBlocks as $block) {
                $iconClass = $block['icon'] ?? 'bi bi-square';
                $category = $block['category'] ?? 'general';
                ?>
                <article class="pbm-card"
                         data-category="<?php echo html($category); ?>"
                         data-system-name="<?php echo html($block['system_name']); ?>"
                         data-search="<?php echo html(mb_strtolower($block['name'] . ' ' . $block['description'] . ' ' . $block['system_name'], 'UTF-8')); ?>">
                    <div class="pbm-card-top">
                        <div class="pbm-card-icon"><i class="<?php echo html($iconClass); ?>"></i></div>
                        <div class="pbm-card-heading">
                            <h5 class="pbm-card-title"><?php echo html($block['name']); ?></h5>
                            <div class="pbm-card-cat"><?php echo html($categoryLabels[$category] ?? ucfirst($category)); ?></div>
                        </div>
                        <span class="pbm-card-version">v<?php echo html($block['version']); ?></span>
                    </div>

                    <p class="pbm-card-desc"><?php echo html($block['description']); ?></p>

                    <div class="pbm-card-meta">
                        <code title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_SYSTEM_NAME); ?>"><?php echo html($block['system_name']); ?></code>
                    </div>

                    <div class="pbm-card-badges">
                        <?php if ($block['presets_count'] > 0) { ?>
                            <span class="pbm-badge is-preset" title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_BADGE_PRESETS_HINT); ?>">
                                <i class="bi bi-stars"></i><?php echo (int)$block['presets_count']; ?>
                            </span>
                        <?php } ?>
                        <?php if ($block['has_custom_template']) { ?>
                            <span class="pbm-badge is-template" title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_BADGE_CUSTOM_TEMPLATE_HINT); ?>">
                                <i class="bi bi-filetype-html"></i><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_BADGE_CUSTOM_TEMPLATE; ?>
                            </span>
                        <?php } else { ?>
                            <span class="pbm-badge is-muted"><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_BADGE_DEFAULT_TEMPLATE; ?></span>
                        <?php } ?>
                        <?php if ((int)$block['used_in_posts'] > 0) { ?>
                            <span class="pbm-badge is-usage" title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_USAGE_POSTS); ?>">
                                <i class="bi bi-file-text"></i><?php echo (int)$block['used_in_posts']; ?>
                            </span>
                        <?php } ?>
                        <?php if ((int)$block['used_in_pages'] > 0) { ?>
                            <span class="pbm-badge is-usage" title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_USAGE_PAGES); ?>">
                                <i class="bi bi-file-earmark"></i><?php echo (int)$block['used_in_pages']; ?>
                            </span>
                        <?php } ?>
                    </div>

                    <div class="pbm-card-foot">
                        <div class="pbm-switches">
                            <label class="pbm-switch" title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_SWITCH_POSTS_HINT); ?>">
                                <input type="checkbox" class="pbm-switch-input" data-target="enable_in_posts"
                                    <?php echo $block['can_use_in_posts'] ? 'checked' : ''; ?>>
                                <span class="pbm-switch-track"><span class="pbm-switch-thumb"></span></span>
                                <span class="pbm-switch-label"><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_SWITCH_POSTS; ?></span>
                            </label>
                            <label class="pbm-switch" title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_SWITCH_PAGES_HINT); ?>">
                                <input type="checkbox" class="pbm-switch-input" data-target="enable_in_pages"
                                    <?php echo $block['can_use_in_pages'] ? 'checked' : ''; ?>>
                                <span class="pbm-switch-track"><span class="pbm-switch-thumb"></span></span>
                                <span class="pbm-switch-label"><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_SWITCH_PAGES; ?></span>
                            </label>
                        </div>
                        <div class="pbm-card-actions">
                            <button type="button" class="pbm-act" data-pbm-action="preview"
                                    title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_PREVIEW_BTN); ?>"
                                    aria-label="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_PREVIEW_BTN); ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a class="pbm-act" href="<?php echo ADMIN_URL; ?>/post-blocks/edit?system_name=<?php echo html($block['system_name']); ?>"
                               title="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_SETTINGS_BTN); ?>"
                               aria-label="<?php echo html(LANG_TEMPLATE_POSTBLOCKS_INDEX_SETTINGS_BTN); ?>">
                                <i class="bi bi-sliders"></i>
                            </a>
                        </div>
                    </div>
                </article>
            <?php } ?>
        </div>

        <div class="pbm-nothing" id="pbm-nothing" style="display: none;">
            <i class="bi bi-search"></i>
            <div><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_NOTHING_FOUND; ?></div>
        </div>
    <?php } ?>
</div>

<?php if (!empty($postBlocks)) { ?>
<div class="modal fade pbm-modal" id="pbm-preview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i><span id="pbm-preview-title"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pbm-preview-state" class="pbm-preview-loading">
                    <span class="spinner-border spinner-border-sm text-primary me-2"></span>
                    <?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_PREVIEW_LOADING; ?>
                </div>
                <div id="pbm-preview-body" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <a href="#" id="pbm-preview-edit" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-sliders me-1"></i><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_SETTINGS_BTN; ?>
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_PREVIEW_CLOSE; ?>
                </button>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php ob_start(); ?>
<script>
window.pbmLang = {
    previewMain: <?php echo json_encode(LANG_TEMPLATE_POSTBLOCKS_INDEX_PREVIEW_MAIN_TEMPLATE, JSON_UNESCAPED_UNICODE); ?>,
    previewPresets: <?php echo json_encode(LANG_TEMPLATE_POSTBLOCKS_INDEX_PREVIEW_PRESETS_TITLE, JSON_UNESCAPED_UNICODE); ?>,
    previewPreset: <?php echo json_encode(LANG_TEMPLATE_POSTBLOCKS_INDEX_PREVIEW_PRESET_LABEL, JSON_UNESCAPED_UNICODE); ?>,
    previewError: <?php echo json_encode(LANG_TEMPLATE_POSTBLOCKS_INDEX_PREVIEW_ERROR, JSON_UNESCAPED_UNICODE); ?>,
    toggleSaved: <?php echo json_encode(LANG_TEMPLATE_POSTBLOCKS_INDEX_TOGGLE_SAVED, JSON_UNESCAPED_UNICODE); ?>,
    toggleError: <?php echo json_encode(LANG_TEMPLATE_POSTBLOCKS_INDEX_TOGGLE_ERROR, JSON_UNESCAPED_UNICODE); ?>,
    toggleOfflineWarn: <?php echo json_encode(LANG_TEMPLATE_POSTBLOCKS_INDEX_TOGGLE_DISABLED_WARNING, JSON_UNESCAPED_UNICODE); ?>
};
</script>
<?php admin_bottom_js(ob_get_clean()); ?>
