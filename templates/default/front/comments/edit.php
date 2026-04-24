<?php
/**
 * Template Name: Страница редактирования комментария
 */

$postId = $comment['post_id'] ?? 0;
$post = $postModel->getById($postId);
$postTitle = $post ? $post['title'] : LANG_TEMPLATE_COMMENT_EDIT_UNKNOWN_POST;
$postSlug = $post ? $post['slug'] : '';
$isAdmin = $isAdmin ?? false;
$canEdit = AuthHelper::canEditComment($comment['user_id'] ?? null);

if (!$canEdit) {
    echo '<div class="tg-container tg-mt-5"><div class="tg-alert tg-alert-danger">' . LANG_TEMPLATE_COMMENT_EDIT_NO_PERMISSION . '</div></div>';
    return;
}
?>

<div class="tg-comment-edit-page">
    <div class="tg-container">
        
        <div class="tg-page-header tg-mb-4">
            <h1 class="tg-page-title">
                <?php echo $isAdmin ? LANG_TEMPLATE_COMMENT_EDIT_TITLE_ADMIN : LANG_TEMPLATE_COMMENT_EDIT_TITLE; ?>
            </h1>
            <p class="tg-page-subtitle tg-text-muted">
                <?php echo html($postTitle); ?>
            </p>
        </div>
        
        <div class="tg-card">
            <div class="tg-card-body">
                
                <div class="tg-post-info-card tg-mb-4">
                    <div class="tg-post-info-icon">
                        <?php echo bloggy_icon('bs', 'file-text', '20', 'var(--tg-primary)'); ?>
                    </div>
                    <div class="tg-post-info-content">
                        <span class="tg-post-info-label"><?php echo LANG_TEMPLATE_COMMENT_EDIT_POST_LABEL; ?></span>
                        <a href="<?php echo BASE_URL; ?>/post/<?php echo $postSlug; ?>" class="tg-post-info-title">
                            <?php echo html($postTitle); ?>
                        </a>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/post/<?php echo $postSlug; ?>" class="tg-btn tg-btn-outline tg-btn-sm">
                        <?php echo bloggy_icon('bs', 'arrow-left', '14', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_COMMENT_EDIT_BACK_BTN; ?>
                    </a>
                </div>
                
                <?php if (!$isAdmin && $comment['status'] === 'pending') { ?>
                <div class="tg-alert tg-alert-warning tg-mb-4">
                    <div class="tg-alert-icon">
                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '20', 'currentColor'); ?>
                    </div>
                    <div class="tg-alert-content">
                        <strong><?php echo LANG_TEMPLATE_COMMENT_EDIT_MODERATION_TITLE; ?></strong>
                        <p class="tg-mb-0">
                            <?php if (!AuthHelper::can('comment_edit_no_moderations')) { ?>
                                <?php echo LANG_TEMPLATE_COMMENT_EDIT_MODERATION_TEXT; ?>
                            <?php } else { ?>
                                <?php echo LANG_TEMPLATE_COMMENT_EDIT_NO_MODERATION_TEXT; ?>
                            <?php } ?>
                        </p>
                    </div>
                </div>
                <?php } ?>
                
                <form action="<?php echo BASE_URL; ?>/comment/edit/<?php echo $comment['id']; ?>" method="post" id="tg-edit-comment-form">
                    <div class="tg-field tg-mb-4">
                        <label for="content" class="tg-label">
                            <?php echo bloggy_icon('bs', 'chat-text', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_COMMENT_EDIT_CONTENT_LABEL; ?> <span class="tg-required">*</span>
                        </label>
                        <textarea name="content" 
                                  id="content" 
                                  rows="8" 
                                  class="tg-input tg-textarea" 
                                  placeholder="<?php echo LANG_TEMPLATE_COMMENT_EDIT_CONTENT_PLACEHOLDER; ?>"
                                  required><?php echo html($comment['content'] ?? ''); ?></textarea>
                        <div class="tg-field-hint">
                            <?php echo bloggy_icon('bs', 'info-circle', '12', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_COMMENT_EDIT_MIN_LENGTH_HINT; ?>
                        </div>
                    </div>
                    
                    <?php if ($isAdmin) { ?>
                    <div class="tg-admin-section tg-mb-4">
                        <div class="tg-section-header">
                            <?php echo bloggy_icon('bs', 'gear', '16', 'currentColor', 'tg-mr-1'); ?>
                            <span><?php echo LANG_TEMPLATE_COMMENT_EDIT_ADMIN_SETTINGS; ?></span>
                        </div>
                        
                        <div class="tg-section-body">
                            <div class="tg-form-row">
                                <div class="tg-form-col">
                                    <label for="author_name" class="tg-label"><?php echo LANG_TEMPLATE_COMMENT_EDIT_AUTHOR_NAME_LABEL; ?></label>
                                    <input type="text" 
                                           name="author_name" 
                                           id="author_name" 
                                           class="tg-input" 
                                           value="<?php echo html($comment['author_name'] ?? ''); ?>">
                                </div>
                                <div class="tg-form-col">
                                    <label for="author_email" class="tg-label"><?php echo LANG_TEMPLATE_COMMENT_EDIT_AUTHOR_EMAIL_LABEL; ?></label>
                                    <input type="email" 
                                           name="author_email" 
                                           id="author_email" 
                                           class="tg-input" 
                                           value="<?php echo html($comment['author_email'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="tg-field">
                                <label for="status" class="tg-label"><?php echo LANG_TEMPLATE_COMMENT_EDIT_STATUS_LABEL; ?></label>
                                <select name="status" id="status" class="tg-select">
                                    <option value="pending" <?php echo ($comment['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_COMMENT_EDIT_STATUS_PENDING; ?></option>
                                    <option value="approved" <?php echo ($comment['status'] ?? 'pending') === 'approved' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_COMMENT_EDIT_STATUS_APPROVED; ?></option>
                                    <option value="spam" <?php echo ($comment['status'] ?? 'pending') === 'spam' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_COMMENT_EDIT_STATUS_SPAM; ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="tg-form-actions tg-border-top tg-pt-4">
                        <div class="tg-actions-left">
                            <a href="<?php echo BASE_URL; ?>/post/<?php echo $postSlug; ?>" class="tg-btn tg-btn-outline">
                                <?php echo bloggy_icon('bs', 'x', '14', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo LANG_TEMPLATE_COMMENT_EDIT_CANCEL_BTN; ?>
                            </a>
                        </div>
                        
                        <div class="tg-actions-right">
                            <?php if (AuthHelper::canDeleteComment($comment['user_id'] ?? null)) { ?>
                            <button type="button" 
                                    class="tg-btn tg-btn-outline tg-btn-danger"
                                    onclick="if(confirm('<?php echo LANG_TEMPLATE_COMMENT_EDIT_DELETE_CONFIRM; ?>')) window.location.href='<?php echo BASE_URL; ?>/comment/delete/<?php echo $comment['id']; ?>'">
                                <?php echo bloggy_icon('bs', 'trash', '14', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo LANG_TEMPLATE_COMMENT_EDIT_DELETE_BTN; ?>
                            </button>
                            <?php } ?>
                            <button type="submit" class="tg-btn tg-btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '14', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo LANG_TEMPLATE_COMMENT_EDIT_SAVE_BTN; ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="tg-info-section tg-mt-4 tg-pt-4 tg-border-top">
                        <div class="tg-info-grid">
                            <div class="tg-info-col">
                                <h6 class="tg-info-title">
                                    <?php echo bloggy_icon('bs', 'info-circle', '14', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_COMMENT_EDIT_INFO_TITLE; ?>
                                </h6>
                                <ul class="tg-info-list">
                                    <li>
                                        <?php echo bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1'); ?>
                                        <?php echo LANG_TEMPLATE_COMMENT_EDIT_CREATED_AT; ?> <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                    </li>
                                    <?php if (!empty($comment['updated_at']) && $comment['updated_at'] != $comment['created_at']) { ?>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'arrow-clockwise', '12', 'currentColor', 'tg-mr-1'); ?>
                                        <?php echo LANG_TEMPLATE_COMMENT_EDIT_UPDATED_AT; ?> <?php echo date('d.m.Y H:i', strtotime($comment['updated_at'])); ?>
                                    </li>
                                    <?php } ?>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'person', '12', 'currentColor', 'tg-mr-1'); ?>
                                        <?php echo LANG_TEMPLATE_COMMENT_EDIT_AUTHOR_LABEL; ?> <?php echo html($comment['author_name'] ?? LANG_TEMPLATE_COMMENT_EDIT_UNKNOWN_AUTHOR); ?>
                                    </li>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'shield', '12', 'currentColor', 'tg-mr-1'); ?>
                                        <?php echo LANG_TEMPLATE_COMMENT_EDIT_STATUS_LABEL; ?>: 
                                        <span class="tg-status-badge tg-status-<?php echo $comment['status'] ?? 'pending'; ?>">
                                            <?php 
                                            $statusText = [
                                                'pending' => LANG_TEMPLATE_COMMENT_EDIT_STATUS_PENDING,
                                                'approved' => LANG_TEMPLATE_COMMENT_EDIT_STATUS_APPROVED,
                                                'spam' => LANG_TEMPLATE_COMMENT_EDIT_STATUS_SPAM
                                            ];
                                            echo $statusText[$comment['status']] ?? $comment['status'];
                                            ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="tg-info-col">
                                <h6 class="tg-info-title">
                                    <?php echo bloggy_icon('bs', 'lightbulb', '14', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_COMMENT_EDIT_TIPS_TITLE; ?>
                                </h6>
                                <ul class="tg-info-list tg-tips-list">
                                    <li>
                                        <?php echo bloggy_icon('bs', 'check-circle', '12', '#31b131', 'tg-mr-1'); ?>
                                        <?php echo LANG_TEMPLATE_COMMENT_EDIT_TIP_1; ?>
                                    </li>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'check-circle', '12', '#31b131', 'tg-mr-1'); ?>
                                        <?php echo LANG_TEMPLATE_COMMENT_EDIT_TIP_2; ?>
                                    </li>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'check-circle', '12', '#31b131', 'tg-mr-1'); ?>
                                        <?php echo LANG_TEMPLATE_COMMENT_EDIT_TIP_3; ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>