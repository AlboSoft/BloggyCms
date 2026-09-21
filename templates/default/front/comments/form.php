<?php
/**
 * Template Name: Форма комментирования - Habr Pro
 */
$postId = $post['id'] ?? ($_POST['post_id'] ?? 0);
$parentId = $_POST['parent_id'] ?? 0;
$isLoggedIn = Auth::isLoggedIn();
$ajaxUrl = BASE_URL . '/comment/add';
$canComment = AuthHelper::canAddComment($postId);
$showEmoji = SettingsHelper::get('controller_comments', 'show_emodji', false);
$emojiList = SettingsHelper::get('controller_comments', 'emodji_list', []);

if (!$postId) { echo '<div class="tg-alert tg-alert-error">не указан пост</div>'; return; }

if (!$canComment && !$isLoggedIn) { ?>
<div class="tg-comment-login-prompt" style="margin-top:16px">
    <div class="tg-alert tg-alert-info">
        <div class="tg-alert-content">
            <strong>нужна авторизация</strong>
            <p class="tg-mb-0" style="margin-top:8px;display:flex;gap:8px">
                <a href="<?= BASE_URL ?>/login" class="tg-btn tg-btn-primary tg-btn-sm">войти</a>
                <a href="<?= BASE_URL ?>/register" class="tg-btn tg-btn-outline tg-btn-sm">регистрация</a>
            </p>
        </div>
    </div>
</div>
<?php } elseif (!$canComment) { ?>
<div class="tg-comment-restricted" style="margin-top:16px">
    <div class="tg-alert tg-alert-warning"><div class="tg-alert-content"><strong>доступ ограничен</strong><p class="tg-mb-0">вы не можете комментировать</p></div></div>
</div>
<?php } else { ?>
<div class="tg-comment-form" id="comment-form">
    <div class="tg-form-header">
        <h5 class="tg-form-title"><span id="comment-form-title"><?php echo $parentId ? 'ответить' : 'комментарий'; ?></span></h5>
    </div>
    <div class="tg-form-body">
        <form action="<?= $ajaxUrl ?>" method="post" id="comment-form-element">
            <input type="hidden" name="post_id" value="<?= $postId ?>">
            <input type="hidden" name="parent_id" value="<?= $parentId ?>" id="comment-parent-id">
            <div id="comment-form-status" style="display:none"></div>
            <?php if (!$isLoggedIn) { ?>
                <div class="tg-form-row">
                    <div class="tg-field"><label class="tg-label">имя *</label><input type="text" name="author_name" class="tg-input" required placeholder="ваше имя" value="<?= html($_POST['author_name'] ?? '') ?>"></div>
                    <div class="tg-field"><label class="tg-label">email *</label><input type="email" name="author_email" class="tg-input" required placeholder="email не публикуется" value="<?= html($_POST['author_email'] ?? '') ?>"></div>
                </div>
            <?php } ?>
            <div class="tg-field">
                <label class="tg-label">текст *</label>
                <div class="comment-input-wrapper" style="position:relative">
                    <textarea name="content" id="content" rows="4" class="tg-textarea" placeholder="напишите комментарий..." required></textarea>
                    <?php if ($showEmoji && !empty($emojiList)) { ?>
                    <button type="button" class="tg-input-toggle" id="emojiTrigger" title="эмодзи" style="bottom:8px;top:auto">☺</button>
                    <?php } ?>
                </div>
                <?php if ($showEmoji && !empty($emojiList)) { ?>
                <div class="emoji-picker-container" id="emojiPicker" style="display:none;position:absolute;background:#fff;border:1px solid #e8e8e8;padding:8px;z-index:10;margin-top:4px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><span style="font-size:11px;font-weight:700;text-transform:uppercase">эмодзи</span><button type="button" id="closeEmojiPicker" style="background:none;border:0;cursor:pointer">✕</button></div>
                    <div id="emojiPickerBody" style="display:grid;grid-template-columns:repeat(6,1fr);gap:4px;max-height:160px;overflow-y:auto">
                        <?php foreach ($emojiList as $emojiItem) { if (!empty($emojiItem['icon'])) { ?>
                            <button type="button" class="emoji-item" data-emoji="<?= html(trim($emojiItem['icon'])) ?>" style="background:#f5f5f5;border:1px solid #e8e8e8;padding:6px;cursor:pointer;font-size:16px"><?= trim($emojiItem['icon']) ?></button>
                        <?php } } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
                <div style="display:flex;gap:8px;align-items:center">
                    <div id="comment-loading" style="display:none;width:16px;height:16px;border:2px solid #e8e8e8;border-top-color:#000;animation:spin .6s linear infinite"></div>
                    <button type="submit" class="tg-btn tg-btn-primary" id="comment-submit-btn"><?php echo $parentId ? 'ответить' : 'отправить'; ?></button>
                </div>
            </div>
            <?php if (!AuthHelper::canAddCommentWithoutModeration() && !Auth::isAdmin()) { ?>
                <div class="tg-alert tg-alert-warning" style="margin-top:12px;margin-bottom:0"><div class="tg-alert-content"><strong>модерация</strong><p class="tg-mb-0">комментарий появится после проверки</p></div></div>
            <?php } ?>
        </form>
        <?php if ($parentId) { ?><div style="margin-top:12px"><button type="button" class="tg-btn tg-btn-outline tg-btn-sm" onclick="cancelReply()">отменить ответ</button></div><?php } ?>
    </div>
</div>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
<?php } ?>
