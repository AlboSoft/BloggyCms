<?php
/**
 * Template Name: Отдельный комментарий
 */

$isPending = $comment['status'] === 'pending';
$isOwnComment = isset($_SESSION['user_id']) && $comment['user_id'] == $_SESSION['user_id'];
$isAdmin = isset($_SESSION['is_admin']);
$canEdit = $isOwnComment || $isAdmin;
$authorName = html($comment['author_username'] ?? $comment['author_name'] ?? LANG_TEMPLATE_COMMENT_SINGLE_ANONYMOUS);
$commentDate = date('d.m.Y H:i', strtotime($comment['created_at']));
?>

<div class="tg-comment-item" id="tg-comment-<?php echo $comment['id']; ?>">
    
    <div class="tg-comment-container">
        <div class="tg-comment-header">
            
            <div class="tg-comment-avatar">
                <?php if (!empty($comment['author_avatar']) && $comment['author_avatar'] !== 'default.jpg') { ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($comment['author_avatar']); ?>" 
                         alt="<?php echo $authorName; ?>">
                <?php } else { ?>
                    <div class="tg-avatar-placeholder">
                        <?php echo strtoupper(substr($authorName, 0, 1)); ?>
                    </div>
                <?php } ?>
            </div>
            
            <div class="tg-comment-info">
                <div class="tg-comment-author-row">
                    <span class="tg-author-name"><?php echo $authorName; ?></span>
                    
                    <?php if ($isOwnComment) { ?>
                        <span class="tg-badge tg-badge-own" title="<?php echo LANG_TEMPLATE_COMMENT_SINGLE_YOUR_COMMENT_TITLE; ?>">
                            <?php echo bloggy_icon('bs', 'person-check', '10', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_COMMENT_SINGLE_YOU_BADGE; ?>
                        </span>
                    <?php } ?>
                    
                    <?php if ($isAdmin && !$isOwnComment) { ?>
                        <span class="tg-badge tg-badge-admin" title="<?php echo LANG_TEMPLATE_COMMENT_SINGLE_ADMIN_TITLE; ?>">
                            <?php echo bloggy_icon('bs', 'shield', '10', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_COMMENT_SINGLE_ADMIN_BADGE; ?>
                        </span>
                    <?php } ?>
                    
                    <?php if ($isPending) { ?>
                        <span class="tg-badge tg-badge-pending" title="<?php echo LANG_TEMPLATE_COMMENT_SINGLE_PENDING_TITLE; ?>">
                            <?php echo bloggy_icon('bs', 'clock', '10', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_COMMENT_SINGLE_PENDING_BADGE; ?>
                        </span>
                    <?php } ?>
                </div>
                
                <div class="tg-comment-meta">
                    <span class="tg-comment-date">
                        <?php echo bloggy_icon('bs', 'calendar', '10', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo $commentDate; ?>
                    </span>
                    
                    <?php if (!empty($comment['was_edited']) && $comment['was_edited']) { ?>
                        <span class="tg-comment-edited" title="<?php echo LANG_TEMPLATE_COMMENT_SINGLE_EDITED_TITLE; ?>">
                            • <?php echo LANG_TEMPLATE_COMMENT_SINGLE_EDITED_BADGE; ?>
                        </span>
                    <?php } ?>
                </div>
            </div>
            
            <?php if (!empty($comment['parent_id'])) { ?>
            <div class="tg-reply-indicator" title="<?php echo LANG_TEMPLATE_COMMENT_SINGLE_REPLY_TITLE; ?>">
                <?php echo bloggy_icon('bs', 'reply', '12', 'currentColor'); ?>
            </div>
            <?php } ?>
        </div>
        
        <div class="tg-comment-content">
            <?php echo nl2br(html($comment['content'])); ?>
        </div>
        
        <div class="tg-comment-actions">
            <button type="button" 
                    class="tg-action-btn tg-reply-btn"
                    data-comment-id="<?php echo $comment['id']; ?>"
                    data-comment-author="<?php echo $authorName; ?>">
                <?php echo bloggy_icon('bs', 'reply', '14', 'currentColor', 'tg-mr-1'); ?>
                <?php echo LANG_TEMPLATE_COMMENT_SINGLE_REPLY_BTN; ?>
            </button>
            
            <?php if ($canEdit) { ?>
                <a href="<?php echo BASE_URL; ?>/comment/edit/<?php echo $comment['id']; ?>" 
                   class="tg-action-btn tg-edit-btn">
                    <?php echo bloggy_icon('bs', 'pencil', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_COMMENT_SINGLE_EDIT_BTN; ?>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/comment/delete/<?php echo $comment['id']; ?>" 
                   class="tg-action-btn tg-delete-btn"
                   onclick="return confirm('<?php echo LANG_TEMPLATE_COMMENT_SINGLE_DELETE_CONFIRM; ?>')">
                    <?php echo bloggy_icon('bs', 'trash', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_COMMENT_SINGLE_DELETE_BTN; ?>
                </a>
                
            <?php } ?>
            
            <?php if ($isAdmin && $isPending) { ?>
                <a href="<?php echo ADMIN_URL; ?>/comments/approve/<?php echo $comment['id']; ?>" 
                   class="tg-action-btn tg-approve-btn"
                   title="<?php echo LANG_TEMPLATE_COMMENT_SINGLE_APPROVE_TITLE; ?>">
                    <?php echo bloggy_icon('bs', 'check-lg', '14', 'currentColor', 'tg-mr-1'); ?>
                    <?php echo LANG_TEMPLATE_COMMENT_SINGLE_APPROVE_BTN; ?>
                </a>
            <?php } ?>
        </div>
        
        <div class="tg-comment-replies" id="tg-replies-<?php echo $comment['id']; ?>"></div>
    </div>
</div>