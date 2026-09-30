<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'plus-square', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/html-blocks" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_BACK_BTN; ?>
        </a>
    </div>

    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-start">
            <?php echo bloggy_icon('bs', 'palette', '18', '#0c5460', 'me-2 mt-1'); ?>
            <div>
                <strong><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_ACTIVE_TEMPLATE; ?> <?php echo html($currentTemplate); ?></strong>
                <div class="small mt-1"><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_COMPATIBILITY_HINT; ?></div>
            </div>
        </div>
    </div>

    <?php if (empty($blockTypes)) { ?>
        <div class="alert alert-warning">
            <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_NO_TYPES; ?>
        </div>
    <?php } else { ?>
        <div class="row" id="blocks-container">
            <?php foreach ($blockTypes as $systemName => $type) { ?>
            <div class="col-md-6 col-lg-4 mb-4 block-item">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-primary p-3 rounded me-3">
                                <?php
                                $iconClass = $type['icon'] ?? 'bi bi-box';
                                $iconName = str_replace('bi bi-', '', $iconClass);
                                echo bloggy_icon('bs', $iconName, '24', '#fff');
                                ?>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1"><?php echo html($type['name']); ?></h5>
                                <p class="text-muted small mb-0"><?php echo html($type['short_description'] ?? $type['description'] ?? ''); ?></p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted"><?php echo html($type['description'] ?? ''); ?></small>
                        </div>

                        <div class="mb-3">
                            <?php if (empty($type['template']) || strcasecmp($type['template'], 'all') === 0) { ?>
                                <span class="badge bg-light text-dark">
                                    <?php echo bloggy_icon('bs', 'globe', '14', '#6c757d', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_ALL_TEMPLATES; ?>
                                </span>
                            <?php } else { ?>
                                <span class="badge bg-info">
                                    <?php echo bloggy_icon('bs', 'palette', '14', '#0c5460', 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_TEMPLATE_LABEL; ?> <?php echo html($type['template']); ?>
                                </span>
                            <?php } ?>
                            <?php if (($type['source'] ?? '') === 'theme' && !empty($type['source_template'])) { ?>
                                <span class="badge bg-secondary ms-1">
                                    <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_PROVIDED_BY_THEME; ?> <?php echo html($type['source_template']); ?>
                                </span>
                            <?php } ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <div><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_AUTHOR_LABEL; ?> <?php echo html($type['author'] ?? 'BloggyCMS'); ?></div>
                                <div><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_VERSION_LABEL; ?> <?php echo html($type['version'] ?? '1.0.0'); ?></div>
                                <?php if (!empty($type['author_website'])) { ?>
                                    <div>
                                        <a href="<?php echo html($type['author_website']); ?>" target="_blank" rel="noopener noreferrer" class="text-muted">
                                            <?php echo html($type['author_website']); ?>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                            <a href="<?php echo ADMIN_URL; ?>/html-blocks/create?type=<?php echo rawurlencode($systemName); ?>" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_CREATE_BTN; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
