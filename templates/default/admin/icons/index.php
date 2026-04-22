<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'emoji-smile', '24 24', null, 'me-2'); ?>
            <?php echo LANG_TEMPLATE_ICONS_INDEX_TITLE; ?>
        </h4>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text border-0 bg-light">
                    <?php echo bloggy_icon('bs', 'search', '20 20'); ?>
                </span>
                <input type="text" 
                       id="iconSearch" 
                       class="form-control border-0 bg-light" 
                       placeholder="<?php echo LANG_TEMPLATE_ICONS_INDEX_SEARCH_PLACEHOLDER; ?>"
                       oninput="filterIcons(this.value)">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-custom" id="iconTabs" role="tablist">
                <?php 
                $isFirst = true;
                foreach($icons as $set) { 
                ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $isFirst ? 'active' : ''; ?>" 
                            id="<?php echo $set['name']; ?>-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#<?php echo $set['name']; ?>-content" 
                            type="button" 
                            role="tab" 
                            aria-controls="<?php echo $set['name']; ?>-content" 
                            aria-selected="<?php echo $isFirst ? 'true' : 'false'; ?>">
                        <?php echo html(ucfirst($set['name'])); ?>
                        <span class="badge bg-secondary rounded-pill ms-2"><?php echo $set['count']; ?></span>
                    </button>
                </li>
                <?php 
                    $isFirst = false;
                } 
                ?>
            </ul>

            <div class="tab-content pt-4" id="iconTabsContent">
                <?php 
                $isFirst = true;
                foreach($icons as $set) { 
                ?>
                <div class="tab-pane fade <?php echo $isFirst ? 'show active' : ''; ?>" 
                     id="<?php echo $set['name']; ?>-content" 
                     role="tabpanel" 
                     aria-labelledby="<?php echo $set['name']; ?>-tab">
                    
                    <div class="row g-3">
                        <?php foreach($set['icons'] as $icon) { ?>
                            <div class="col-md-3 col-lg-2 icon-item" data-icon-id="<?php echo html($icon['id']); ?>">
                                <div class="card border-0 shadow-sm h-100 position-relative">
                                    <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" 
                                            onclick="copyIconCode('<?php echo addslashes($icon['code']); ?>')"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="left" 
                                            title="<?php echo LANG_TEMPLATE_ICONS_INDEX_COPY_CODE_TITLE; ?>">
                                        <?php echo bloggy_icon('bs', 'clipboard', '16 16'); ?>
                                    </button>
                                    <div class="card-body text-center p-3">
                                        <div class="mb-2" style="font-size: 2rem;">
                                            <?php echo $icon['preview']; ?>
                                        </div>
                                        <div class="small text-muted">
                                            <code><?php echo html($icon['id']); ?></code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php 
                    $isFirst = false;
                } 
                ?>
            </div>
        </div>
    </div>
</div>

<?php
    add_admin_js('templates/default/admin/assets/js/controllers/icons.js');
?>