<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'input-cursor-text', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_FIELDS_INDEX_TITLE; ?>
        </h4>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0"><?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_TYPES; ?></h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="<?php echo ADMIN_URL; ?>/fields/entity/post" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo bloggy_icon('bs', 'file-text', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_POSTS; ?>
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $fieldModel->getCountByEntityType('post'); ?>
                            </span>
                        </a>
                        <a href="<?php echo ADMIN_URL; ?>/fields/entity/page" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo bloggy_icon('bs', 'file-earmark', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_PAGES; ?>
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $fieldModel->getCountByEntityType('page'); ?>
                            </span>
                        </a>
                        <a href="<?php echo ADMIN_URL; ?>/fields/entity/category" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo bloggy_icon('bs', 'folder', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_CATEGORIES; ?>
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $fieldModel->getCountByEntityType('category'); ?>
                            </span>
                        </a>
                        <a href="<?php echo ADMIN_URL; ?>/fields/entity/user" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo bloggy_icon('bs', 'person', '16', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_USERS; ?>
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $fieldModel->getCountByEntityType('user'); ?>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><?php echo LANG_TEMPLATE_FIELDS_INDEX_ALL_FIELDS; ?></h6>
                </div>
                <div class="card-body">
                    <?php if(empty($fields)) { ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <?php echo bloggy_icon('bs', 'input-cursor-text', '48', '#6C6C6C'); ?>
                            </div>
                            <h5 class="text-muted mb-4"><?php echo LANG_TEMPLATE_FIELDS_INDEX_NO_FIELDS_TITLE; ?></h5>
                            <p class="text-muted mb-4"><?php echo LANG_TEMPLATE_FIELDS_INDEX_NO_FIELDS_TEXT; ?></p>
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm text-center">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <?php echo bloggy_icon('bs', 'file-text', '32', '#0d6efd'); ?>
                                        </div>
                                        <h6 class="card-title"><?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_POSTS; ?></h6>
                                        <a href="<?php echo ADMIN_URL; ?>/fields/create/post" class="btn btn-primary btn-sm">
                                            <?php echo bloggy_icon('bs', 'plus-lg', '16', '#ffffff', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FIELDS_INDEX_CREATE_BTN; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm text-center">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <?php echo bloggy_icon('bs', 'file-earmark', '32', '#198754'); ?>
                                        </div>
                                        <h6 class="card-title"><?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_PAGES; ?></h6>
                                        <a href="<?php echo ADMIN_URL; ?>/fields/create/page" class="btn btn-success btn-sm">
                                            <?php echo bloggy_icon('bs', 'plus-lg', '16', '#ffffff', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FIELDS_INDEX_CREATE_BTN; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm text-center">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <?php echo bloggy_icon('bs', 'folder', '32', '#dc3545'); ?>
                                        </div>
                                        <h6 class="card-title"><?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_CATEGORIES; ?></h6>
                                        <a href="<?php echo ADMIN_URL; ?>/fields/create/category" class="btn btn-danger btn-sm">
                                            <?php echo bloggy_icon('bs', 'plus-lg', '16', '#ffffff', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FIELDS_INDEX_CREATE_BTN; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm text-center">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <?php echo bloggy_icon('bs', 'person', '32', '#ffc107'); ?>
                                        </div>
                                        <h6 class="card-title"><?php echo LANG_TEMPLATE_FIELDS_INDEX_ENTITY_USERS; ?></h6>
                                        <a href="<?php echo ADMIN_URL; ?>/fields/create/user" class="btn btn-warning btn-sm">
                                            <?php echo bloggy_icon('bs', 'plus-lg', '16', '#ffffff', 'me-1'); ?>
                                            <?php echo LANG_TEMPLATE_FIELDS_INDEX_CREATE_BTN; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo LANG_TEMPLATE_FIELDS_INDEX_TABLE_NAME; ?></th>
                                        <th><?php echo LANG_TEMPLATE_FIELDS_INDEX_TABLE_SYSTEM_NAME; ?></th>
                                        <th><?php echo LANG_TEMPLATE_FIELDS_INDEX_TABLE_TYPE; ?></th>
                                        <th><?php echo LANG_TEMPLATE_FIELDS_INDEX_TABLE_ENTITY; ?></th>
                                        <th><?php echo LANG_TEMPLATE_FIELDS_INDEX_TABLE_STATUS; ?></th>
                                        <th class="text-end"><?php echo LANG_TEMPLATE_FIELDS_INDEX_TABLE_ACTIONS; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($fields as $field) { ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo html($field['name']); ?></strong>
                                            <?php if(!empty($field['description'])) { ?>
                                                <br><small class="text-muted"><?php echo html($field['description']); ?></small>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <code class="text-muted"><?php echo html($field['system_name']); ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $field['type']; ?></span>
                                        </td>
                                        <td>
                                            <?php echo $field['entity_type']; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $field['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $field['is_active'] ? LANG_TEMPLATE_FIELDS_INDEX_ACTIVE : LANG_TEMPLATE_FIELDS_INDEX_INACTIVE; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?php echo ADMIN_URL; ?>/fields/entity/<?php echo $field['entity_type']; ?>" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="<?php echo LANG_TEMPLATE_FIELDS_INDEX_TO_ENTITY_TITLE; ?>">
                                                    <?php echo bloggy_icon('bs', 'arrow-right', '16', '#000'); ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>