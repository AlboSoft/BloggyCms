<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'tag', '24', '#000', 'me-2'); ?>
            <?php echo isset($tag) ? LANG_TEMPLATE_TAGS_FORM_EDIT_TITLE . html($tag['name']) : LANG_TEMPLATE_TAGS_FORM_CREATE_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/tags" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TAGFORM_BACK_TO_LIST; ?>
        </a>
    </div>

    <?php echo render_simple_form($form, $currentData ?? [], [
        'submit_text' => isset($tag) ? LANG_TAGFORM_SUBMIT_UPDATE : LANG_TAGFORM_SUBMIT_CREATE,
        'cancel_url' => ADMIN_URL . '/tags'
    ]); ?>
</div>