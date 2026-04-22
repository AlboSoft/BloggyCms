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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label"><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_FILTER_LABEL; ?></label>
                    <select class="form-select" id="template-filter">
                        <?php foreach ($availableTemplates as $templateValue => $templateName) { ?>
                        <option value="<?php echo $templateValue; ?>">
                            <?php echo html($templateName); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">
                        <?php echo bloggy_icon('bs', 'info-circle', '16', '#6c757d', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_FILTER_HINT; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="blocks-container">
        <?php foreach ($blockTypes as $systemName => $type) { ?>
        <div class="col-md-6 col-lg-4 mb-4 block-item" 
             data-template="<?php echo html($type['template'] ?? 'all'); ?>">
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
                            <p class="text-muted small mb-0"><?php echo html($type['short_description'] ?? $type['description']); ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted"><?php echo html($type['description']); ?></small>
                    </div>
                    
                    <?php if (!empty($type['template']) && $type['template'] !== 'all') { ?>
                        <div class="mb-2">
                            <span class="badge bg-info">
                                <?php echo bloggy_icon('bs', 'palette', '16', '#35acb2', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_TEMPLATE_LABEL; ?> <?php echo html($type['template']); ?>
                            </span>
                        </div>
                    <?php } ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <div><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_AUTHOR_LABEL; ?> <?php echo html($type['author'] ?? 'BloggyCMS'); ?></div>
                            <div><?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_VERSION_LABEL; ?> <?php echo html($type['version'] ?? '1.0.0'); ?></div>
                            <?php if (!empty($type['author_website'])) { ?>
                            <div>
                                <a href="<?php echo html($type['author_website']); ?>" target="_blank" class="text-muted">
                                    <?php echo html($type['author_website']); ?>
                                </a>
                            </div>
                            <?php } ?>
                        </div>
                        <a href="<?php echo ADMIN_URL; ?>/html-blocks/create?type=<?php echo $systemName; ?>" 
                           class="btn btn-primary">
                            <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-1'); ?>
                            <?php echo LANG_TEMPLATE_HTMLBLOCKS_SELECT_TYPE_CREATE_BTN; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<?php ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const templateFilter = document.getElementById('template-filter');
        const blocksContainer = document.getElementById('blocks-container');
        const blockItems = document.querySelectorAll('.block-item');

        templateFilter.addEventListener('change', function() {
            const selectedTemplate = this.value;
            
            blockItems.forEach(function(item) {
                const blockTemplate = item.getAttribute('data-template');
                
                if (selectedTemplate === 'all' || blockTemplate === selectedTemplate) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>
<?php admin_bottom_js(ob_get_clean()); ?>