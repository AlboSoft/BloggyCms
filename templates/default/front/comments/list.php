<?php
/**
 * Template Name: Список комментариев - Habr Pro
 */
function countAllNestedComments($comment) {
    $count = 0;
    if (!empty($comment['replies'])) {
        foreach ($comment['replies'] as $reply) {
            $count++; $count += countAllNestedComments($reply);
        }
    }
    return $count;
}
function renderCommentsTree($comments, $level = 0) {
    $maxDepth = SettingsHelper::get('controller_comments', 'max_depth', 4);
    $showGroups = SettingsHelper::get('controller_comments', 'show_groups', true);
    $showAdminBadge = SettingsHelper::get('controller_comments', 'show_admin_badge', false);
    $adminBadgeTitle = SettingsHelper::get('controller_comments', 'title_badge', 'админ');
    $adminBadgeBgColor = SettingsHelper::get('controller_comments', 'bg_badge', '#000');
    $adminBadgeTextColor = SettingsHelper::get('controller_comments', 'color_badge', '#fff');
    
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
?>
        <div class="tg-comment-item <?php echo $level > 0 ? 'tg-comment-reply' : ''; ?> <?php echo $isPending ? 'tg-comment-pending' : ''; ?> tg-level-<?php echo min($level, $maxDepth); ?>"
            id="tg-comment-<?php echo $commentId; ?>" data-comment-id="<?php echo $commentId; ?>" data-parent-id="<?php echo $parentId; ?>" data-level="<?php echo $level; ?>">
            <div class="tg-comment-container">
                <div class="tg-comment-header">
                    <div class="tg-comment-avatar">
                        <img src="<?php echo $userAvatar; ?>" alt="<?php echo html($userName); ?>" onerror="this.src='<?php echo BASE_URL; ?>/uploads/avatars/default.jpg'">
                        <?php if ($level > 0) { ?><div class="tg-reply-line"></div><?php } ?>
                    </div>
                    <div class="tg-comment-info">
                        <div class="tg-comment-author">
                            <?php if (!empty($comment['user_id'])) { ?>
                                <a href="<?php echo BASE_URL; ?>/profile/<?php echo html($comment['author_username'] ?? ''); ?>" class="tg-author-name tg-author-link"><?php echo html($userName); ?></a>
                            <?php } else { ?><span class="tg-author-name"><?php echo html($userName); ?></span><?php } ?>
                            <?php if ($level > 0) { ?><span class="tg-badge tg-badge-reply">↳ ответ</span><?php } ?>
                            <?php if ($isPending && $isOwnComment) { ?><span class="tg-badge tg-badge-moderation">на модерации</span><?php } ?>
                            <?php if ($isOwnComment) { ?><span class="tg-badge tg-badge-own">вы</span><?php } ?>
                            <?php if ($showAdminBadge && $isAdmin) { ?><span class="tg-badge tg-badge-admin"><?php echo html($adminBadgeTitle); ?></span><?php } ?>
                        </div>
                        <div class="tg-comment-meta">
                            <span class="tg-comment-date"><?php echo time_ago($comment['created_at']); ?></span>
                            <?php if (!empty($comment['was_edited']) && $comment['was_edited']) { ?><span class="tg-comment-edited">изм.</span><?php } ?>
                            <?php if ($showGroups && !empty($userGroups)) { ?>
                                <div class="tg-user-groups"><?php foreach ($userGroups as $group) { ?><span class="tg-badge tg-badge-group"><?php echo html($group['name']); ?></span><?php } ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if (Auth::isAdmin() && $comment['status'] === 'pending') { ?>
                        <div class="tg-admin-actions"><button class="tg-admin-btn tg-admin-approve" data-comment-id="<?php echo $commentId; ?>" title="одобрить">✓</button></div>
                    <?php } ?>
                </div>
                <div class="tg-comment-content"><?php echo nl2br(html($comment['content'])); ?></div>
                <div class="tg-comment-actions">
                    <?php if ($canReply) { ?><button type="button" class="tg-action-btn tg-btn-reply reply-btn" data-comment-id="<?php echo $commentId; ?>" data-comment-author="<?php echo html($userName); ?>">ответить</button><?php } ?>
                    <?php if ($canEdit) { $editUrl = (Auth::isAdmin()) ? ADMIN_URL . '/comments/edit/' . $commentId : BASE_URL . '/comment/edit/' . $commentId; ?>
                        <a href="<?php echo $editUrl; ?>" class="tg-action-btn tg-btn-edit">править</a>
                    <?php } ?>
                    <?php if ($canDelete) { ?>
                        <a href="<?php echo BASE_URL; ?>/comment/delete/<?php echo $commentId; ?>" class="tg-action-btn tg-btn-delete" onclick="return confirm('удалить?')">удалить</a>
                    <?php } ?>
                    <?php if (Auth::isAdmin()) { ?><a href="<?php echo ADMIN_URL; ?>/comments/edit/<?php echo $commentId; ?>" class="tg-action-btn tg-btn-admin">админ</a><?php } ?>
                </div>
                <?php if ($showToggle) { ?>
                    <div class="tg-deep-toggle">
                        <button type="button" class="tg-toggle-replies" data-target="tg-replies-<?php echo $commentId; ?>">показать ветку (<?php echo $nestedCount; ?>)</button>
                        <div class="tg-deep-replies" id="tg-replies-<?php echo $commentId; ?>" style="display:none"><?php renderCommentsTree($comment['replies'], $level + 1); ?></div>
                    </div>
                <?php } elseif ($hasReplies) { ?><div class="tg-comment-replies"><?php renderCommentsTree($comment['replies'], $level + 1); ?></div><?php } ?>
            </div>
        </div>
    <?php }
}
$canComment = AuthHelper::canAddComment();
?>
<div class="tg-comments-block" id="tg-comments">
    <?php if (empty($comments)) { ?>
        <div class="tg-comments-empty">
            <h4 class="tg-empty-title">пока пусто</h4>
            <p class="tg-empty-text">будьте первым, кто оставит комментарий</p>
            <?php if (!$canComment && !Auth::isLoggedIn()) { ?>
                <div class="tg-empty-action"><a href="<?php echo BASE_URL; ?>/login" class="tg-btn tg-btn-primary tg-btn-sm">войти чтобы комментировать</a></div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="tg-comments-list"><?php renderCommentsTree($comments); ?></div>
        <?php if (!$canComment) { ?>
            <div class="tg-comments-restricted" style="margin-top:16px">
                <div class="tg-alert tg-alert-info"><div class="tg-alert-content"><strong>комментирование ограничено</strong><p class="tg-mb-0">у вас нет прав для комментирования</p></div></div>
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
];
?>
<?php echo add_frontend_js('/templates/default/front/assets/js/comments.js'); ?>
<?php ob_start(); ?><script>window.bloggyCommentsConfig = <?php echo json_encode($js_config); ?>;</script><?php front_bottom_js(ob_get_clean()); ?>
