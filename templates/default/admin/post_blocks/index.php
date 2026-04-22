<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'grid-3x3-gap', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_TITLE; ?>
        </h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php foreach ($postBlocksByCategory as $category => $blocks) { ?>
            <div class="border-bottom">
                <div class="p-4 bg-light">
                    <h5 class="mb-0 text-dark">
                        <?php echo html(ucfirst($category)); ?> 
                        <span class="badge bg-secondary ms-2"><?php echo count($blocks); ?></span>
                    </h5>
                </div>
                
                <div class="p-4">
                    <div class="row g-3">
                        <?php foreach ($blocks as $block) { ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="post-block-icon me-3">
                                            <?php 
                                            $iconClass = $block['icon'] ?? 'bi bi-puzzle';
                                            $iconName = str_replace('bi bi-', '', $iconClass);
                                            echo bloggy_icon('bs', $iconName, '24', '#000');
                                            ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1"><?php echo html($block['name']); ?></h6>
                                            <p class="card-text text-muted small mb-2">
                                                <?php echo html($block['description']); ?>
                                            </p>
                                            <div class="d-flex gap-2 mb-2">
                                                <span class="badge bg-<?php echo $block['can_use_in_posts'] ? 'success' : 'danger'; ?>">
                                                    <?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_BADGE_POSTS; ?>
                                                </span>
                                                <span class="badge bg-<?php echo $block['can_use_in_pages'] ? 'success' : 'danger'; ?>">
                                                    <?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_BADGE_PAGES; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="small text-muted mb-3">
                                        <div><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_SYSTEM_NAME; ?> <code><?php echo $block['system_name']; ?></code></div>
                                        <div><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_VERSION; ?> <?php echo $block['version']; ?></div>
                                        <div><?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_AUTHOR; ?> <?php echo $block['author']; ?></div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="<?php echo ADMIN_URL; ?>/post-blocks/edit?system_name=<?php echo $block['system_name']; ?>" 
                                       class="btn btn-sm btn-success">
                                        <?php echo bloggy_icon('bs', 'gear', '14', '#fefefe', 'me-1'); ?>
                                        <?php echo LANG_TEMPLATE_POSTBLOCKS_INDEX_SETTINGS_BTN; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>