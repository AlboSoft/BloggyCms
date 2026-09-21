<?php
    $isEdit = isset($tag);
    $tagData = $isEdit ? $tag : ($data ?? []);
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'tag', '24 24', null, 'me-2'); ?>
            <?php echo $isEdit ? LANG_TEMPLATE_TAGS_FORM_EDIT_TITLE : LANG_TEMPLATE_TAGS_FORM_CREATE_TITLE; ?>
            <?php if ($isEdit && !empty($tagData['name'])) { ?>
                <span class="text-muted ms-1"><?php echo '«' . html($tagData['name']) . '»'; ?></span>
            <?php } ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/tags" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16 16', null, 'me-1'); ?>
            <?php echo LANG_TEMPLATE_TAGS_FORM_BACK_BTN; ?>
        </a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TAGFORM_MAIN_SECTION; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="name" class="form-label"><?php echo LANG_TAGFORM_FIELD_NAME; ?> <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   class="form-control form-control-lg"
                                   value="<?php echo html($tagData['name'] ?? ''); ?>"
                                   placeholder="<?php echo LANG_TAGFORM_FIELD_NAME_PLACEHOLDER; ?>"
                                   maxlength="50"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label"><?php echo LANG_TAGFORM_FIELD_DESCRIPTION; ?></label>
                            <textarea name="description"
                                      id="description"
                                      class="form-control"
                                      rows="5"
                                      placeholder="<?php echo LANG_TAGFORM_FIELD_DESCRIPTION_PLACEHOLDER; ?>"><?php echo html($tagData['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><?php echo LANG_TAGFORM_FIELD_IMAGE; ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($isEdit && !empty($tagData['image'])) { ?>
                            <div class="mb-3 text-center">
                                <img src="/uploads/tags/<?php echo html($tagData['image']); ?>"
                                     class="img-thumbnail mb-2"
                                     style="max-height: 150px;"
                                     alt="<?php echo LANG_TAGFORM_FIELD_IMAGE; ?>">
                            </div>
                        <?php } ?>

                        <div class="mb-3">
                            <label for="image" class="form-label"><?php echo LANG_TAGFORM_FIELD_IMAGE; ?></label>
                            <input type="file"
                                   name="image"
                                   id="image"
                                   class="form-control"
                                   accept="image/*">
                            <div class="form-text">
                                <?php echo LANG_TAGFORM_FIELD_IMAGE_HINT; ?>
                            </div>
                        </div>

                        <?php if ($isEdit && !empty($tagData['image'])) { ?>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="delete_image"
                                       id="delete_image"
                                       value="1">
                                <label class="form-check-label text-danger" for="delete_image">
                                    <?php echo bloggy_icon('bs', 'trash', '16 16', null, 'me-1'); ?>
                                    <?php echo LANG_TEMPLATE_TAGS_FORM_DELETE_IMAGE; ?>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-footer bg-white border-0">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '16 16', null, 'me-1'); ?>
                                <?php echo $isEdit ? LANG_TAGFORM_SUBMIT_UPDATE : LANG_TAGFORM_SUBMIT_CREATE; ?>
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/tags" class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'x-lg', '16 16', null, 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_TAGS_FORM_CANCEL_BTN; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
