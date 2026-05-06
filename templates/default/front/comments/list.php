<?php
/**
 * Template Name: Список комментариев
 */

function countAllNestedComments($comment) {
    $count = 0;
    if (!empty($comment['replies'])) {
        foreach ($comment['replies'] as $reply) {
            $count++;
            $count += countAllNestedComments($reply);
        }
    }
    return $count;
}

function renderCommentsTree($comments, $level = 0) {
    $maxDepth = SettingsHelper::get('controller_comments', 'max_depth', 4);
    $showGroups = SettingsHelper::get('controller_comments', 'show_groups', true);
    $showAdminBadge = SettingsHelper::get('controller_comments', 'show_admin_badge', false);
    $adminBadgeTitle = SettingsHelper::get('controller_comments', 'title_badge', LANG_TEMPLATE_COMMENTS_LIST_ADMIN_BADGE_DEFAULT);
    $adminBadgeIcon = SettingsHelper::get('controller_comments', 'icon_badge', 'bs:rocket');
    $adminBadgeBgColor = SettingsHelper::get('controller_comments', 'bg_badge', '#007bff');
    $adminBadgeTextColor = SettingsHelper::get('controller_comments', 'color_badge', '#ffffff');
    
    foreach ($comments as $comment) {
        $isPending = $comment['is_pending'];
        $isOwnComment = $comment['is_own_comment'];
        $isAdmin = $comment['is_admin'] ?? false;
        $userGroups = $comment['user_groups'] ?? [];
        $canEdit = $comment['can_edit'] ?? false;
        $canDelete = $comment['can_delete'] ?? false;
        $canReply = $comment['can_reply'] ?? false;
        $hasReplies = !empty($comment['replies']);
        $userAvatar = $comment['author_avatar'];
        $userName = $comment['author_name'];
        $commentId = $comment['id'];
        $parentId = $comment['parent_id'] ?? 0;
        $isDeepLevel = $level >= $maxDepth;
        $showToggle = $hasReplies && $isDeepLevel;
        
        $nestedCount = $showToggle ? countAllNestedComments($comment) : 0;
        
        $adminBadgeStyle = '';
        if ($showAdminBadge && $isAdmin && $adminBadgeBgColor && $adminBadgeTextColor) {
            $adminBadgeStyle = 'style="background-color: ' . html($adminBadgeBgColor) . '; color: ' . html($adminBadgeTextColor) . ';"';
        }

        $adminIconParts = explode(':', $adminBadgeIcon);
        $adminIconSet = $adminIconParts[0] ?? 'bs';
        $adminIconName = $adminIconParts[1] ?? 'rocket';
?>
        
        <div class="tg-comment-item <?php echo $level > 0 ? 'tg-comment-reply' : ''; ?> 
            <?php echo $isPending ? 'tg-comment-pending' : ''; ?> 
            tg-level-<?php echo min($level, $maxDepth); ?>"
            id="tg-comment-<?php echo $commentId; ?>"
            data-comment-id="<?php echo $commentId; ?>"
            data-parent-id="<?php echo $parentId; ?>"
            data-level="<?php echo $level; ?>">
            
            <div class="tg-comment-container">
                <div class="tg-comment-header">
                    <div class="tg-comment-avatar">
                        <img src="<?php echo $userAvatar; ?>" 
                             alt="<?php echo html($userName); ?>"
                             onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/uploads/avatars/default.jpg'">
                        
                        <?php if ($level > 0) { ?>
                            <div class="tg-reply-line"></div>
                        <?php } ?>
                    </div>
                    
                    <div class="tg-comment-info">
                        <div class="tg-comment-author">
                            
                            <?php if (!empty($comment['user_id'])) { ?>
                                <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($comment['author_username'] ?? ''); ?>" 
                                class="tg-author-name tg-author-link">
                                    <?php echo html($userName); ?>
                                </a>
                            <?php } else { ?>
                                <span class="tg-author-name"><?php echo html($userName); ?></span>
                            <?php } ?>

                            <?php if (!empty($comment['show_last_achievement']) && !empty($comment['last_achievement'])) { ?>
                                <span class="tg-badge tg-badge-achievement" title="<?php echo html($comment['last_achievement']['name']); ?><?php echo !empty($comment['last_achievement']['description']) ? ' - ' . html($comment['last_achievement']['description']) : ''; ?> (<?php echo $comment['last_achievement']['unlocked_formatted']; ?>)" data-bs-toggle="tooltip">
                                    <?php echo bloggy_icon('bs', 'trophy', '10', '#ffc107', 'tg-mr-1'); ?>
                                    <?php echo html($comment['last_achievement']['name']); ?>
                                </span>
                            <?php } ?>
                            
                            <?php if ($level > 0) { ?>
                                <span class="tg-badge tg-badge-reply" title="<?php echo LANG_TEMPLATE_COMMENTS_LIST_REPLY_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'reply', '10', 'currentColor'); ?>
                                </span>
                            <?php } ?>
                            
                            <?php if ($isPending && $isOwnComment) { ?>
                                <span class="tg-badge tg-badge-moderation" title="<?php echo LANG_TEMPLATE_COMMENTS_LIST_PENDING_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'clock', '10', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo SettingsHelper::get('controller_comments', 'z4', LANG_TEMPLATE_COMMENTS_LIST_PENDING_BADGE); ?>
                                </span>
                            <?php } ?>
                            
                            <?php if ($isOwnComment) { ?>
                                <span class="tg-badge tg-badge-own" title="<?php echo LANG_TEMPLATE_COMMENTS_LIST_YOUR_COMMENT_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'person-check', '10', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo SettingsHelper::get('controller_comments', 'z5', LANG_TEMPLATE_COMMENTS_LIST_YOU_BADGE); ?>
                                </span>
                            <?php } ?>
                            
                            <?php if ($showAdminBadge && $isAdmin) { ?>
                                <span class="tg-badge tg-badge-admin" title="<?php echo html($adminBadgeTitle); ?>" <?php echo $adminBadgeStyle; ?>>
                                    <?php echo bloggy_icon($adminIconSet, $adminIconName, '10', html($adminBadgeTextColor), 'tg-mr-1'); ?>
                                    <?php echo html($adminBadgeTitle); ?>
                                </span>
                            <?php } ?>
                        </div>
                        
                        <div class="tg-comment-meta">
                            <span class="tg-comment-date">
                                <?php echo bloggy_icon('bs', 'clock', '12', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo time_ago($comment['created_at']); ?>
                            </span>
                            
                            <?php if (!empty($comment['was_edited']) && $comment['was_edited']) { ?>
                                <span class="tg-comment-edited" title="<?php echo LANG_TEMPLATE_COMMENTS_LIST_EDITED_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'pencil', '10', 'currentColor', 'tg-mr-1'); ?>
                                    <span><?php echo LANG_TEMPLATE_COMMENTS_LIST_EDITED_BADGE; ?></span>
                                </span>
                            <?php } ?>
                            
                            <?php if ($showGroups && !empty($userGroups)) { ?>
                                <div class="tg-user-groups">
                                    <?php foreach ($userGroups as $group) { ?>
                                        <span class="tg-badge tg-badge-group" 
                                              title="<?php echo sprintf(LANG_TEMPLATE_COMMENTS_LIST_GROUP_TITLE, html($group['name'])); ?>">
                                            <?php echo html($group['name']); ?>
                                        </span>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <?php if (Auth::isAdmin()) { ?>
                        <div class="tg-admin-actions">
                            <?php if ($comment['status'] === 'pending') { ?>
                                <button class="tg-admin-btn tg-admin-approve" 
                                        data-comment-id="<?php echo $commentId; ?>"
                                        title="<?php echo LANG_TEMPLATE_COMMENTS_LIST_APPROVE_TITLE; ?>">
                                    <?php echo bloggy_icon('bs', 'check-lg', '14', 'currentColor'); ?>
                                </button>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                
                <div class="tg-comment-content">
                    <?php echo nl2br(html($comment['content'])); ?>
                </div>
                
                <div class="tg-comment-actions">
                    <?php if ($canReply) { ?>
                    <button type="button" class="tg-action-btn tg-btn-reply reply-btn" 
                            data-comment-id="<?php echo $commentId; ?>" 
                            data-comment-author="<?php echo html($userName); ?>">
                        <?php echo bloggy_icon('bs', 'reply', '14', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo SettingsHelper::get('controller_comments', 'z6', LANG_TEMPLATE_COMMENTS_LIST_REPLY_BTN); ?>
                    </button>
                    <?php } ?>
                    
                    <?php if ($canEdit) { ?>
                        <?php $editUrl = (Auth::isAdmin()) 
                            ? ADMIN_URL . '/comments/edit/' . $commentId
                            : BASE_URL . '/comment/edit/' . $commentId;
                        ?>
                        <a href="<?php echo $editUrl; ?>" class="tg-action-btn tg-btn-edit">
                            <?php echo bloggy_icon('bs', 'pencil', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo SettingsHelper::get('controller_comments', 'z7', LANG_TEMPLATE_COMMENTS_LIST_EDIT_BTN); ?>
                        </a>
                    <?php } ?>
                    
                    <?php if ($canDelete) { ?>
                        <a href="<?php echo BASE_URL; ?>/comment/delete/<?php echo $commentId; ?>" 
                           class="tg-action-btn tg-btn-delete"
                           data-comment-id="<?php echo $commentId; ?>"
                           onclick="return confirm('<?php echo LANG_TEMPLATE_COMMENTS_LIST_DELETE_CONFIRM; ?>')">
                            <?php echo bloggy_icon('bs', 'trash', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo SettingsHelper::get('controller_comments', 'z8', LANG_TEMPLATE_COMMENTS_LIST_DELETE_BTN); ?>
                        </a>
                    <?php } ?>
                    
                    <?php if (Auth::isAdmin()) { ?>
                        <a href="<?php echo ADMIN_URL; ?>/comments/edit/<?php echo $commentId; ?>" 
                                   class="tg-action-btn tg-btn-admin"
                                   title="<?php echo LANG_TEMPLATE_COMMENTS_LIST_ADMIN_EDIT_TITLE; ?>">
                            <?php echo bloggy_icon('bs', 'gear', '14', 'currentColor', 'tg-mr-1'); ?>
                            <span><?php echo LANG_TEMPLATE_COMMENTS_LIST_ADMIN_BTN; ?></span>
                        </a>
                    <?php } ?>
                </div>
                
                <?php if ($showToggle) { ?>
                    <div class="tg-deep-toggle">
                        <button type="button" class="tg-toggle-replies" 
                                data-target="tg-replies-<?php echo $commentId; ?>">
                            <?php echo bloggy_icon('bs', 'chevron-down', '12', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo SettingsHelper::get('controller_comments', 'z3', LANG_TEMPLATE_COMMENTS_LIST_SHOW_THREAD_TEXT); ?> (<?php echo sprintf(LANG_TEMPLATE_COMMENTS_LIST_MORE_COUNT, $nestedCount); ?>)
                        </button>
                        
                        <div class="tg-deep-replies" id="tg-replies-<?php echo $commentId; ?>" style="display: none;">
                            <?php renderCommentsTree($comment['replies'], $level + 1); ?>
                        </div>
                    </div>
                <?php } elseif ($hasReplies) { ?>
                    <div class="tg-comment-replies">
                        <?php renderCommentsTree($comment['replies'], $level + 1); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php }
}

$canComment = AuthHelper::canAddComment();
?>

<div class="tg-comments-block" id="tg-comments">
    
    <?php if (empty($comments)) { ?>
        <div class="tg-comments-empty">
            <div class="tg-empty-icon">
                <?php echo bloggy_icon('bs', 'chat-text', '48', 'var(--tg-text-secondary)'); ?>
            </div>
            <h4 class="tg-empty-title"><?php echo SettingsHelper::get('controller_comments', 'z1', LANG_TEMPLATE_COMMENTS_LIST_NO_COMMENTS_TITLE); ?></h4>
            <p class="tg-empty-text tg-text-muted">
                <?php echo SettingsHelper::get('controller_comments', 'z2', LANG_TEMPLATE_COMMENTS_LIST_NO_COMMENTS_HINT); ?>
            </p>
            
            <?php if (!$canComment && !Auth::isLoggedIn()) { ?>
                <div class="tg-empty-action">
                    <a href="<?php echo BASE_URL; ?>/login" class="tg-btn tg-btn-primary">
                        <?php echo bloggy_icon('bs', 'box-arrow-in-right', '16', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo LANG_TEMPLATE_COMMENTS_LIST_LOGIN_TO_COMMENT_BTN; ?>
                    </a>
                </div>
            <?php } ?>
        </div>
        
    <?php } else { ?>
        
        <div class="tg-comments-list">
            <?php renderCommentsTree($comments); ?>
        </div>
        
        <?php if (!$canComment) { ?>
            <div class="tg-comments-restricted tg-mt-4">
                <div class="tg-alert tg-alert-info">
                    <div class="tg-alert-icon">
                        <?php echo bloggy_icon('bs', 'info-circle', '18', 'currentColor'); ?>
                    </div>
                    <div class="tg-alert-content">
                        <strong><?php echo LANG_TEMPLATE_COMMENTS_LIST_RESTRICTED_TITLE; ?></strong>
                        <p class="tg-mb-0"><?php echo LANG_TEMPLATE_COMMENTS_LIST_RESTRICTED_TEXT; ?></p>
                    </div>
                </div>
            </div>
        <?php } ?>
        
    <?php } ?>
    
</div>

<?php
$js_config = [
    'base_url' => BASE_URL,
    'admin_url' => ADMIN_URL ?? '',
    'post_id' => $post['id'] ?? 0,
    'is_admin' => Auth::isAdmin(),
    'is_logged_in' => Auth::isLoggedIn(),
    'current_user_id' => Auth::getUserId() ?? 0,
    'max_depth' => SettingsHelper::get('controller_comments', 'max_depth', 4),
    'can_comment' => $canComment,
    'moderation_text' => SettingsHelper::get('controller_comments', 'z4', LANG_TEMPLATE_COMMENTS_LIST_PENDING_BADGE),
    'you_text' => SettingsHelper::get('controller_comments', 'z5', LANG_TEMPLATE_COMMENTS_LIST_YOU_BADGE),
    'reply_text' => SettingsHelper::get('controller_comments', 'z6', LANG_TEMPLATE_COMMENTS_LIST_REPLY_BTN),
    'edit_text' => SettingsHelper::get('controller_comments', 'z7', LANG_TEMPLATE_COMMENTS_LIST_EDIT_BTN),
    'delete_text' => SettingsHelper::get('controller_comments', 'z8', LANG_TEMPLATE_COMMENTS_LIST_DELETE_BTN),
    'show_groups' => SettingsHelper::get('controller_comments', 'show_groups', true),
    'show_admin_badge' => SettingsHelper::get('controller_comments', 'show_admin_badge', false),
    'admin_badge_title' => SettingsHelper::get('controller_comments', 'title_badge', LANG_TEMPLATE_COMMENTS_LIST_ADMIN_BADGE_DEFAULT),
    'admin_badge_icon' => SettingsHelper::get('controller_comments', 'icon_badge', 'bs:rocket'),
    'admin_badge_bg_color' => SettingsHelper::get('controller_comments', 'bg_badge', '#007bff'),
    'admin_badge_text_color' => SettingsHelper::get('controller_comments', 'color_badge', '#ffffff'),
    'show_emodji' => SettingsHelper::get('controller_comments', 'show_emodji', false),
    'emodji_list' => SettingsHelper::get('controller_comments', 'emodji_list', []),
];
?>

<?php echo add_frontend_js('/templates/default/front/assets/js/comments.js'); ?>

<?php ob_start(); ?>
<script>
    window.bloggyCommentsConfig = <?php echo json_encode($js_config); ?>;
</script>
<?php front_bottom_js(ob_get_clean()); ?>