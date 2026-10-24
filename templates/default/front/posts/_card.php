<?php
/**
* Частичный шаблон: карточка поста в списке
* Ожидает: $post (array)
*/

if (empty($post) || !is_array($post)) {
    return;
}

$showCoverInList = !isset($post['show_cover_in_list']) || (int)$post['show_cover_in_list'] === 1;
$featuredImage = (!empty($post['featured_image']) && $showCoverInList)
    ? BASE_URL . '/uploads/images/' . html($post['featured_image'])
    : null;

$postUrl = BASE_URL . '/post/' . html($post['slug'] ?? '');
$isPasswordProtected = !empty($post['password_protected']);
$isAdult = !empty($post['is_adult']);
$hasUpdated = !empty($post['updated_at']) && !empty($post['created_at']) && $post['updated_at'] !== $post['created_at'];
$userLiked = !empty($post['userLiked']);
$userBookmarked = !empty($post['userBookmarked']);
$postTags = !empty($post['tags']) && is_array($post['tags']) ? $post['tags'] : [];
?>
<article class="tg-post-card">
    <div class="tg-post-meta">
        <?php if (!empty($post['category_name'])) { ?>
            <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug'] ?? ''); ?>" class="tg-post-category">
                <?php echo html($post['category_name']); ?>
            </a>
        <?php } ?>
        <span class="tg-post-date">
            <?php echo bloggy_icon('bs', 'calendar', '12', '#94a3b8'); ?>
            <?php echo !empty($post['created_at']) ? date('d.m.Y', strtotime($post['created_at'])) : ''; ?>
        </span>
        <?php if ($hasUpdated) { ?>
            <span class="tg-post-updated">
                <?php echo bloggy_icon('bs', 'pencil', '12', '#94a3b8'); ?>
                <?php echo sprintf(LANG_TEMPLATE_POSTS_LIST_UPDATED, date('d.m.Y', strtotime($post['updated_at']))); ?>
            </span>
        <?php } ?>
        <?php if ($isPasswordProtected) { ?>
            <span class="tg-post-lock" title="<?php echo LANG_TEMPLATE_SEARCH_PASSWORD_PROTECTED_TITLE; ?>">
                <?php echo bloggy_icon('bs', 'lock-fill', '12', '#f59e0b'); ?>
            </span>
        <?php } ?>
    </div>

    <div class="tg-post-title-row">
        <?php if ($featuredImage) { ?>
            <div class="tg-post-thumb">
                <a href="<?php echo $postUrl; ?>">
                    <img src="<?php echo $featuredImage; ?>" alt="<?php echo html($post['title']); ?>" loading="lazy">
                </a>
            </div>
        <?php } else { ?>
            <div class="tg-post-thumb tg-post-thumb-empty">
                <?php echo bloggy_icon('bs', 'image', '24', '#cbd5e0'); ?>
            </div>
        <?php } ?>
        <h2 class="tg-post-title">
            <a href="<?php echo $postUrl; ?>">
                <?php echo html($post['title']); ?>
            </a>
            <?php if ($isAdult) { ?>
                <span class="tg-adult-badge" title="<?php echo LANG_TEMPLATE_ADULT_BADGE_TITLE; ?>">
                    <?php echo bloggy_icon('bs', 'cone-striped', '16', '#dc3545'); ?>
                    18+
                </span>
            <?php } ?>
        </h2>
    </div>

    <?php if (!empty($post['short_description'])) { ?>
        <p class="tg-post-excerpt">
            <?php echo html($post['short_description']); ?>
        </p>
    <?php } ?>

    <div class="tg-post-actions">
        <button class="tg-action-btn tg-like-btn <?php echo $userLiked ? 'tg-active' : ''; ?>"
                data-post-id="<?php echo (int)$post['id']; ?>">
            <?php echo bloggy_icon('bs', $userLiked ? 'heart-fill' : 'heart', '14', 'currentColor'); ?>
            <span><?php echo (int)($post['likes_count'] ?? 0); ?></span>
        </button>

        <a href="<?php echo $postUrl . '#comments'; ?>" class="tg-action-btn">
            <?php echo bloggy_icon('bs', 'chat-dots', '14', 'currentColor'); ?>
            <span><?php echo (int)($post['comments_count'] ?? 0); ?></span>
        </a>

        <button class="tg-action-btn tg-bookmark-btn <?php echo $userBookmarked ? 'tg-active' : ''; ?>"
                data-post-id="<?php echo (int)$post['id']; ?>">
            <?php echo bloggy_icon('bs', $userBookmarked ? 'bookmark-fill' : 'bookmark', '14', 'currentColor'); ?>
        </button>

        <span class="tg-post-views">
            <?php echo bloggy_icon('bs', 'eye', '13', '#94a3b8'); ?>
            <span><?php echo (int)($post['views'] ?? 0); ?></span>
        </span>
    </div>

    <?php if (!empty($postTags)) { ?>
        <div class="tg-post-tags">
            <?php foreach ($postTags as $tag) { ?>
                <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug'] ?? ''); ?>" class="tg-tag">
                    #<?php echo html($tag['name'] ?? ''); ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</article>