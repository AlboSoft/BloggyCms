<?php
    add_admin_js('templates/default/admin/assets/js/controllers/settings.js');
    add_admin_js('templates/default/admin/assets/js/controllers/conditional-fields.js');
    add_admin_js('templates/default/admin/assets/js/controllers/icon-field.js');
    add_admin_css('templates/default/admin/assets/css/controllers/settings.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><?php echo bloggy_icon('bs', 'gear', '24', '#000', 'me-2 controller-svg'); ?> <?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_TITLE; ?></h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pb-0">
            <ul class="nav nav-tabs nav-tabs-custom">
                <li class="nav-item">
                    <a class="nav-link <?php echo $activeTab === 'general' ? 'active' : ''; ?>" 
                       href="<?php echo ADMIN_URL; ?>/settings?tab=general">
                       <?php echo bloggy_icon('bs', 'sliders', '14', '#000', 'me-1 controller-svg'); ?> <?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_TAB_GENERAL; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activeTab === 'site' ? 'active' : ''; ?>" 
                       href="<?php echo ADMIN_URL; ?>/settings?tab=site">
                       <?php echo bloggy_icon('bs', 'globe', '14', '#000', 'me-1 controller-svg'); ?> <?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_TAB_SITE; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activeTab === 'components' ? 'active' : ''; ?>" 
                       href="<?php echo ADMIN_URL; ?>/settings?tab=components">
                       <?php echo bloggy_icon('bs', 'puzzle', '14', '#000', 'me-1 controller-svg'); ?> <?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_TAB_COMPONENTS; ?>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <?php if ($activeTab === 'general' || $activeTab === 'site') { ?>
                <form method="POST" enctype="multipart/form-data">
                    <?php
                    $tabFile = __DIR__ . '/tabs/' . $activeTab . '.php';
                    if (file_exists($tabFile)) {
                        include $tabFile;
                    } else {
                        echo '<div class="alert alert-warning">' . sprintf(LANG_TEMPLATE_SETTINGS_ADMININDEX_TAB_NOT_FOUND, html($activeTab)) . '</div>';
                    }
                    ?>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-1'); ?> <?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_SAVE_BTN; ?>
                        </button>
                    </div>
                </form>
                
            <?php } elseif ($activeTab === 'components') { ?>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="components-sidebar">
                            <h6 class="components-sidebar-title"><?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_COMPONENTS_TITLE; ?></h6>
                            
                            <?php $controllersWithSettings = $controllerManager->getControllersWithSettings(); ?>
                            
                            <?php if (!empty($controllersWithSettings)) { ?>
                                <div class="components-list">
                                    <?php foreach ($controllersWithSettings as $controller) { ?>
                                        <a href="<?php echo ADMIN_URL; ?>/settings?tab=components&controller=<?php echo $controller['key']; ?>" 
                                           class="component-item <?php echo $selectedController === $controller['key'] ? 'active' : ''; ?>">
                                            <div class="component-content">
                                                <div class="component-name"><?php echo $controller['name']; ?></div>
                                                <div class="component-meta">
                                                    <span class="component-author"><?php echo $controller['author']; ?></span>
                                                </div>
                                                <?php if (!empty($controller['description'])) { ?>
                                                    <div class="component-description"><?php echo $controller['description']; ?></div>
                                                <?php } ?>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <div class="components-empty">
                                    <?php echo bloggy_icon('bs', 'inboxes', '24', '#6C6C6C', 'mb-2'); ?>
                                    <p><?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_NO_COMPONENTS; ?></p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <?php if ($selectedController) { ?>
                            <?php 
                                $controller = $controllerManager->getController($selectedController);
                                $settingsForm = $controllerManager->getControllerSettingsForm($selectedController, $settings);
                            ?>
                            
                            <?php if ($controller && !empty($settingsForm)) { ?>
                                <form method="POST" class="component-settings" enctype="multipart/form-data">
                                    <div class="component-header">
                                        <div class="component-title-section">
                                            <h5 class="component-title"><?php echo $controller['name']; ?></h5>
                                            <div class="component-meta-large">
                                                <span class="component-author"><?php echo sprintf(LANG_TEMPLATE_SETTINGS_ADMININDEX_AUTHOR, $controller['author']); ?></span>
                                                <span class="component-version"><?php echo sprintf(LANG_TEMPLATE_SETTINGS_ADMININDEX_VERSION, $controller['version']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($controller['description'])) { ?>
                                            <div class="component-description-panel">
                                                <?php echo $controller['description']; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    
                                    <div class="component-settings-form">
                                        <?php echo $settingsForm; ?>
                                    </div>
                                    
                                    <div class="component-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?><?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_SAVE_BTN; ?>
                                        </button>
                                    </div>
                                </form>
                            <?php } else { ?>
                                <div class="component-not-found">
                                    <?php echo bloggy_icon('bs', 'gear', '48', '#6C6C6C', 'mb-3'); ?>
                                    <h5><?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_NO_SETTINGS_TITLE; ?></h5>
                                    <p><?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_NO_SETTINGS_DESC; ?></p>
                                    <a href="<?php echo ADMIN_URL; ?>/settings?tab=components" class="btn btn-outline-secondary">
                                        <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?><?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_BACK_BTN; ?>
                                    </a>
                                </div>
                            <?php } ?>
                            
                        <?php } else { ?>
                            <div class="component-welcome">
                                <?php echo bloggy_icon('bs', 'puzzle', '48', '#6C6C6C', 'my-3'); ?>
                                <h5><?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_SELECT_COMPONENT_TITLE; ?></h5>
                                <p><?php echo LANG_TEMPLATE_SETTINGS_ADMININDEX_SELECT_COMPONENT_DESC; ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>