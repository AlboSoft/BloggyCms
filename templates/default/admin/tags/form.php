<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'tag', '24', '#000', 'me-2 controller-svg'); ?>
            <?php echo isset($tag) ? LANG_TEMPLATE_TAGS_FORM_EDIT_TITLE : LANG_TEMPLATE_TAGS_FORM_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/tags" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_TAGS_FORM_BACK_BTN; ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_TAGS_FORM_NAME_LABEL; ?></label>
                            <input type="text" name="name" class="form-control form-control-lg" value="<?php echo isset($tag) ? html($tag['name']) : ''; ?>" placeholder="<?php echo LANG_TEMPLATE_TAGS_FORM_NAME_PLACEHOLDER; ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="mb-4">
                            <label class="form-label"><?php echo LANG_TEMPLATE_TAGS_FORM_IMAGE_LABEL; ?></label>
                            
                            <?php if (isset($tag) && !empty($tag['image'])) { ?>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo LANG_TEMPLATE_TAGS_FORM_CURRENT_IMAGE_LABEL; ?></label>
                                    <div class="border rounded p-3 text-center">
                                        <img src="<?php echo BASE_URL; ?>/uploads/tags/<?php echo $tag['image']; ?>" alt="<?php echo html($tag['name']); ?>" class="img-fluid rounded" style="max-height: 150px;">
                                        <div class="mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                                                <label class="form-check-label text-danger" for="remove_image">
                                                    <?php echo LANG_TEMPLATE_TAGS_FORM_REMOVE_IMAGE_LABEL; ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <?php echo isset($tag) && !empty($tag['image']) ? LANG_TEMPLATE_TAGS_FORM_REPLACE_IMAGE_LABEL : LANG_TEMPLATE_TAGS_FORM_UPLOAD_IMAGE_LABEL; ?>
                                </label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <div class="form-text">
                                    <?php echo LANG_TEMPLATE_TAGS_FORM_IMAGE_HINT; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-1'); ?> <?php echo LANG_TEMPLATE_TAGS_FORM_SAVE_BTN; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/tags" class="btn btn-outline-secondary">
                        <?php echo bloggy_icon('bs', 'x-lg', '20', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_TAGS_FORM_CANCEL_BTN; ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>