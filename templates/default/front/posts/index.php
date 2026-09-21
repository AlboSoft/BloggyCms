<?php
/**
 * Template Name: Список постов - Habr Pro
 * Лента статей в стиле Хабра
 */
?>
<div class="tg-posts-page">
    <div class="tg-container">
        <div class="tg-two-columns">
            <div class="tg-posts-main">
                <div class="tg-page-header">
                    <h1 class="tg-page-title"><?php echo LANG_TEMPLATE_POSTS_LIST_TITLE; ?></h1>
                    <p class="tg-page-subtitle"><?php echo LANG_TEMPLATE_POSTS_LIST_SUBTITLE; ?></p>
                </div>

                <div class="tg-posts-list">
                    <?php if (!empty($posts)) { ?>
                        <?php foreach ($posts as $post) {
                            $showCoverInList = !isset($post['show_cover_in_list']) || (int)$post['show_cover_in_list'] === 1;
                            $featuredImage = ($post['featured_image'] && $showCoverInList)
                                ? BASE_URL . '/uploads/images/' . html($post['featured_image'])
                                : null;
                            $isPasswordProtected = isset($post['password_protected']) && $post['password_protected'] == 1;
                            $hasUpdated = $post['updated_at'] && $post['updated_at'] !== $post['created_at'];
                        ?>
                            <article class="tg-post-card">
                                <div class="tg-post-meta">
                                    <?php if (!empty($post['category_name'])) { ?>
                                        <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" class="tg-post-category">
                                            <?php echo html($post['category_name']); ?>
                                        </a>
                                    <?php } ?>
                                    <span class="tg-post-date">
                                        <?php echo date('d.m.Y', strtotime($post['created_at'])); ?>
                                    </span>
                                    <?php if ($isPasswordProtected) { ?>
                                        <span class="tg-post-lock" title="закрыто">🔒</span>
                                    <?php } ?>
                                </div>

                                <div class="tg-post-title-row">
                                    <?php if ($featuredImage) { ?>
                                        <div class="tg-post-thumb">
                                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                                <img src="<?php echo $featuredImage; ?>" alt="<?php echo html($post['title']); ?>" loading="lazy">
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <h2 class="tg-post-title">
                                        <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                            <?php echo html($post['title']); ?>
                                        </a>
                                        <?php if (isset($post['is_adult']) && $post['is_adult']) { ?>
                                            <span class="tg-adult-badge">18+</span>
                                        <?php } ?>
                                    </h2>
                                </div>

                                <?php if (!empty($post['short_description'])) { ?>
                                    <p class="tg-post-excerpt"><?php echo html($post['short_description']); ?></p>
                                <?php } ?>

                                <div class="tg-post-actions">
                                    <button class="tg-action-btn tg-like-btn <?php echo isset($post['userLiked']) && $post['userLiked'] ? 'tg-active' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                                        ♥ <span><?php echo $post['likes_count'] ?? 0; ?></span>
                                    </button>
                                    <a href="<?php echo BASE_URL . '/post/' . html($post['slug']) . '#comments'; ?>" class="tg-action-btn">
                                        💬 <span><?php echo $post['comments_count'] ?? 0; ?></span>
                                    </a>
                                    <button class="tg-action-btn tg-bookmark-btn <?php echo isset($post['userBookmarked']) && $post['userBookmarked'] ? 'tg-active' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                                        ★
                                    </button>
                                    <span class="tg-post-views">👁 <?php echo $post['views'] ?? 0; ?></span>
                                </div>

                                <?php if (!empty($post['tags'])) { ?>
                                    <div class="tg-post-tags">
                                        <?php foreach ($post['tags'] as $tag) { ?>
                                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" class="tg-tag"><?php echo html($tag['name']); ?></a>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </article>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="tg-empty-state">
                            <div class="tg-empty-state-title">нет публикаций</div>
                            <p class="tg-empty-state-text"><?php echo LANG_TEMPLATE_POSTS_LIST_EMPTY_TEXT; ?></p>
                        </div>
                    <?php } ?>
                </div>

                <?php if (!empty($pagination) && $pagination['total_pages'] > 1) { ?>
                    <div class="tg-pagination">
                        <?php if ($pagination['current_page'] > 1) { ?>
                            <a class="tg-pagination-link" href="<?php echo BASE_URL; ?>/posts?page=<?php echo $pagination['current_page'] - 1; ?>">‹</a>
                        <?php } ?>
                        <?php
                        $start = max(1, $pagination['current_page'] - 2);
                        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        if ($start > 1) { ?>
                            <a class="tg-pagination-link" href="<?php echo BASE_URL; ?>/posts?page=1">1</a>
                            <?php if ($start > 2) { ?><span class="tg-pagination-link">…</span><?php }
                        }
                        for ($i = $start; $i <= $end; $i++) { ?>
                            <a class="tg-pagination-link <?php echo $i == $pagination['current_page'] ? 'tg-active' : ''; ?>" href="<?php echo BASE_URL; ?>/posts?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php }
                        if ($end < $pagination['total_pages']) { ?>
                            <?php if ($end < $pagination['total_pages'] - 1) { ?><span class="tg-pagination-link">…</span><?php } ?>
                            <a class="tg-pagination-link" href="<?php echo BASE_URL; ?>/posts?page=<?php echo $pagination['total_pages']; ?>"><?php echo $pagination['total_pages']; ?></a>
                        <?php } ?>
                        <?php if ($pagination['current_page'] < $pagination['total_pages']) { ?>
                            <a class="tg-pagination-link" href="<?php echo BASE_URL; ?>/posts?page=<?php echo $pagination['current_page'] + 1; ?>">›</a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

            <div class="tg-posts-sidebar">
                <div class="tg-sidebar-card">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:8px;color:#000">навигация</div>
                    <div style="font-size:12px;color:#6c6c6c;line-height:1.5">
                        Лучшие публикации, свежие статьи и разборы. Без воды, только суть.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    document.querySelectorAll('.tg-like-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!isLoggedIn) { window.location.href = '<?php echo BASE_URL; ?>/login'; return; }
            const postId = this.dataset.postId;
            const countSpan = this.querySelector('span');
            const wasActive = this.classList.contains('tg-active');
            this.classList.toggle('tg-active');
            if (countSpan) {
                let current = parseInt(countSpan.textContent) || 0;
                countSpan.textContent = wasActive ? current - 1 : current + 1;
            }
            fetch(`<?php echo BASE_URL; ?>/post/like/${postId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(() => {
                this.classList.toggle('tg-active');
                if (countSpan) countSpan.textContent = wasActive ? (parseInt(countSpan.textContent)+1) : (parseInt(countSpan.textContent)-1);
            });
        });
    });
    document.querySelectorAll('.tg-bookmark-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!isLoggedIn) { window.location.href = '<?php echo BASE_URL; ?>/login'; return; }
            const postId = this.dataset.postId;
            this.classList.toggle('tg-active');
            fetch(`<?php echo BASE_URL; ?>/post/bookmark/${postId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(() => this.classList.toggle('tg-active'));
        });
    });
});
</script>
