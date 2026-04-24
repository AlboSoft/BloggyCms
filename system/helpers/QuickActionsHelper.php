<?php
class QuickActionsHelper {
    /**
    * Проверяет, есть ли активные быстрые действия
    */
    public static function hasQuickActions() {
        $actions = [
            'add_post',
            'add_page', 
            'add_category',
            'add_tag',
            'add_user',
            'add_content_block',
            'add_field',
            'add_form'
        ];
        
        foreach ($actions as $action) {
            if (SettingsHelper::get('controller_admin', $action, false)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
    * Получает классы позиции для кнопки
    */
    private static function getButtonPositionClass() {
        $position = SettingsHelper::get('controller_admin', 'position_btn', 'bottom-right');
        
        switch ($position) {
            case 'bottom-right-center':
                return 'bottom-0 start-50 translate-middle-x p-4';
            case 'bottom-right':
            default:
                return 'bottom-0 end-0 p-4';
        }
    }
    
    /**
    * Получает HTML для плавающей кнопки и модального окна
    */
    public static function renderQuickActions() {
        if (!self::hasQuickActions()) {
            return '';
        }
        
        $positionClass = self::getButtonPositionClass();
        
        ob_start();
        ?>
        <div class="position-fixed <?= $positionClass ?>" style="z-index: 1050;">
            <button type="button" 
                    class="btn btn-<?php echo SettingsHelper::get('controller_admin', 'color_btn', 'primary'); ?> btn-lg rounded-pill shadow-lg pulse-animation"
                    data-bs-toggle="modal" 
                    data-bs-target="#quickActionsModal"
                    style="width: 60px; height: 60px;">
                <?php echo bloggy_icon('bs', 'lightning-charge-fill', '24', 'white'); ?>
            </button>
        </div>

        <div class="modal fade" id="quickActionsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title">
                            <?php echo bloggy_icon('bs', 'lightning-charge-fill', '20', '#0d6efd', 'me-2'); ?>
                            <?php echo LANG_HELPER_QUICKACTIONS_TITLE; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo LANG_HELPER_QUICKACTIONS_CLOSE; ?>"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <?php if(SettingsHelper::get('controller_admin', 'add_post') == true) { ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/posts/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'file-earmark-plus', '32', '#0d6efd'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_POST; ?></div>
                                            <small class="text-muted"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_POST_DESC; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_page') == true) { ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/pages/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'file-text', '32', '#6c757d'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_PAGE; ?></div>
                                            <small class="text-muted"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_PAGE_DESC; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_category') == true) { ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/categories/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'folder-plus', '32', '#198754'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_CATEGORY; ?></div>
                                            <small class="text-muted"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_CATEGORY_DESC; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_tag') == true) { ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/tags/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'tag', '32', '#0dcaf0'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_TAG; ?></div>
                                            <small class="text-muted"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_TAG_DESC; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_user') == true) { ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/users/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'person-plus', '32', '#ffc107'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_USER; ?></div>
                                            <small class="text-muted"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_USER_DESC; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_content_block') == true) { ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/html-blocks/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'box', '32', '#dc3545'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_CONTENT_BLOCK; ?></div>
                                            <small class="text-muted"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_CONTENT_BLOCK_DESC; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_form') == true) { ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/forms/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'mailbox', '32', '#b07a1dff'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_FORM; ?></div>
                                            <small class="text-muted"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_FORM_DESC; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_field') == true) { ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/fields/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'input-cursor-text', '32', '#2148d5ff'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_FIELD; ?></div>
                                            <small class="text-muted"><?php echo LANG_HELPER_QUICKACTIONS_CREATE_FIELD_DESC; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}