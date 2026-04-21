<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'chat-left-text', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_COMMENTS_EDIT_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/comments" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            <?php echo LANG_TEMPLATE_COMMENTS_EDIT_BACK_BTN; ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="<?php echo ADMIN_URL; ?>/comments/edit/<?php echo $comment['id']; ?>" method="post">
                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_TEMPLATE_COMMENTS_EDIT_COMMENT_LABEL; ?></label>
                    <textarea 
                        name="content" 
                        id="content" 
                        rows="5" 
                        class="form-control"
                        placeholder="<?php echo LANG_TEMPLATE_COMMENTS_EDIT_COMMENT_PLACEHOLDER; ?>"
                    ><?php echo html($comment['content']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label"><?php echo LANG_TEMPLATE_COMMENTS_EDIT_STATUS_LABEL; ?></label>
                    <select name="status" id="status" class="form-select">
                        <option value="pending" <?php echo $comment['status'] === 'pending' ? 'selected' : ''; ?>>
                            🕒 <?php echo LANG_TEMPLATE_COMMENTS_EDIT_STATUS_PENDING; ?>
                        </option>
                        <option value="approved" <?php echo $comment['status'] === 'approved' ? 'selected' : ''; ?>>
                            ✅ <?php echo LANG_TEMPLATE_COMMENTS_EDIT_STATUS_APPROVED; ?>
                        </option>
                        <option value="spam" <?php echo $comment['status'] === 'spam' ? 'selected' : ''; ?>>
                            ⚠️ <?php echo LANG_TEMPLATE_COMMENTS_EDIT_STATUS_SPAM; ?>
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_COMMENTS_EDIT_SAVE_BTN; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/comments" class="btn btn-outline-secondary">
                        <?php echo bloggy_icon('bs', 'x-lg', '16', '#000', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_COMMENTS_EDIT_CANCEL_BTN; ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>