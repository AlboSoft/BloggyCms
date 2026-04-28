<?php
/**
 * Template Name: Список постов
 */
?>

<div class="tg-posts-page">
    <div class="tg-container">
        <div class="tg-two-columns">
            <div class="tg-posts-main">
                <div class="tg-posts-card">
                    <div class="tg-page-header">
                        <h1 class="tg-page-title"><?php echo LANG_TEMPLATE_POSTS_LIST_TITLE; ?></h1>
                        <p class="tg-page-subtitle"><?php echo LANG_TEMPLATE_POSTS_LIST_SUBTITLE; ?></p>
                    </div>

                    <div class="tg-posts-list">
                        <?php if (!empty($posts)) { ?>
                            <?php foreach ($posts as $post) {
                                $featuredImage = $post['featured_image']
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
                                            <?php echo bloggy_icon('bs', 'calendar', '12', '#94a3b8'); ?>
                                            <?php echo date('d.m.Y', strtotime($post['created_at'])); ?>
                                        </span>
                                        <?php if ($hasUpdated) { ?>
                                            <span class="tg-post-updated">
                                                <?php echo bloggy_icon('bs', 'pencil', '12', '#94a3b8'); ?>
                                                <?php echo sprintf(LANG_TEMPLATE_POSTS_LIST_UPDATED, date('d.m.Y', strtotime($post['updated_at']))); ?>
                                            </span>
                                        <?php } ?>
                                        <?php if ($isPasswordProtected) { ?>
                                            <span class="tg-post-lock">
                                                <?php echo bloggy_icon('bs', 'lock-fill', '12', '#f59e0b'); ?>
                                            </span>
                                        <?php } ?>
                                    </div>

                                    <div class="tg-post-title-row">
                                        <?php if ($featuredImage) { ?>
                                            <div class="tg-post-thumb">
                                                <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                                    <img src="<?php echo $featuredImage; ?>" alt="<?php echo html($post['title']); ?>">
                                                </a>
                                            </div>
                                        <?php } else { ?>
                                            <div class="tg-post-thumb tg-post-thumb-empty">
                                                <?php echo bloggy_icon('bs', 'image', '24', '#cbd5e0'); ?>
                                            </div>
                                        <?php } ?>
                                        <h2 class="tg-post-title">
                                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                                <?php echo html($post['title']); ?>
                                            </a>
                                        </h2>
                                    </div>

                                    <?php if (!empty($post['short_description'])) { ?>
                                        <p class="tg-post-excerpt">
                                            <?php echo html($post['short_description']); ?>
                                        </p>
                                    <?php } ?>

                                    <div class="tg-post-actions">
                                        <button class="tg-action-btn tg-like-btn <?php echo isset($post['userLiked']) && $post['userLiked'] ? 'tg-active' : ''; ?>" 
                                                data-post-id="<?php echo $post['id']; ?>">
                                            <?php 
                                            $heartIcon = (isset($post['userLiked']) && $post['userLiked']) ? 'heart-fill' : 'heart';
                                            echo bloggy_icon('bs', $heartIcon, '14', 'currentColor');
                                            ?>
                                            <span><?php echo $post['likes_count'] ?? 0; ?></span>
                                        </button>
                                        
                                        <a href="<?php echo BASE_URL . '/post/' . html($post['slug']) . '#comments'; ?>" class="tg-action-btn">
                                            <?php echo bloggy_icon('bs', 'chat-dots', '14', 'currentColor'); ?>
                                            <span><?php echo $post['comments_count'] ?? 0; ?></span>
                                        </a>
                                        
                                        <button class="tg-action-btn tg-bookmark-btn <?php echo isset($post['userBookmarked']) && $post['userBookmarked'] ? 'tg-active' : ''; ?>" 
                                                data-post-id="<?php echo $post['id']; ?>">
                                            <?php 
                                            $bookmarkIcon = (isset($post['userBookmarked']) && $post['userBookmarked']) ? 'bookmark-fill' : 'bookmark';
                                            echo bloggy_icon('bs', $bookmarkIcon, '14', 'currentColor');
                                            ?>
                                        </button>
                                        
                                        <span class="tg-post-views">
                                            <?php echo bloggy_icon('bs', 'eye', '13', '#94a3b8'); ?>
                                            <span><?php echo $post['views'] ?? 0; ?></span>
                                        </span>
                                    </div>

                                    <?php if (!empty($post['tags'])) { ?>
                                        <div class="tg-post-tags">
                                            <?php foreach ($post['tags'] as $tag) { ?>
                                                <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" class="tg-tag">
                                                    #<?php echo html($tag['name']); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </article>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="tg-empty-state">
                                <?php echo bloggy_icon('bs', 'file-text', '48', '#cbd5e0'); ?>
                                <p><?php echo LANG_TEMPLATE_POSTS_LIST_EMPTY_TEXT; ?></p>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if (!empty($pagination) && $pagination['total_pages'] > 1) { ?>
                        <div class="tg-pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1) { ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>/posts?page=<?php echo $pagination['current_page'] - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">«</span>
                                        </a>
                                    </li>
                                <?php } else { ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">«</span>
                                    </li>
                                <?php } ?>
                                
                                <?php
                                $start = max(1, $pagination['current_page'] - 2);
                                $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                
                                if ($start > 1) { ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>/posts?page=1">1</a>
                                    </li>
                                    <?php if ($start > 2) { ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php }
                                }
                                
                                for ($i = $start; $i <= $end; $i++) { ?>
                                    <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>/posts?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php }
                                
                                if ($end < $pagination['total_pages']) { ?>
                                    <?php if ($end < $pagination['total_pages'] - 1) { ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php } ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>/posts?page=<?php echo $pagination['total_pages']; ?>"><?php echo $pagination['total_pages']; ?></a>
                                    </li>
                                <?php } ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']) { ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>/posts?page=<?php echo $pagination['current_page'] + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">»</span>
                                        </a>
                                    </li>
                                <?php } else { ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">»</span>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="tg-posts-sidebar">
                <div class="tg-sidebar-card">
                    <div class="tg-sidebar-placeholder">
                        расположите тут контент-блоки...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeBtns = document.querySelectorAll('.tg-like-btn');
    const bookmarkBtns = document.querySelectorAll('.tg-bookmark-btn');
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    
    likeBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!isLoggedIn) {
                window.location.href = '<?php echo BASE_URL; ?>/login';
                return;
            }
            const postId = this.dataset.postId;
            const countSpan = this.querySelector('span');
            const icon = this.querySelector('svg use');
            const wasActive = this.classList.contains('tg-active');
            
            this.classList.toggle('tg-active');
            let current = parseInt(countSpan.textContent);
            countSpan.textContent = wasActive ? current - 1 : current + 1;
            
            if (icon) {
                const href = icon.getAttribute('href');
                icon.setAttribute('href', href.replace(/heart(-fill)?/, wasActive ? 'heart' : 'heart-fill'));
            }
            
            fetch(`<?php echo BASE_URL; ?>/post/like/${postId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .catch(() => {
                    this.classList.toggle('tg-active');
                    countSpan.textContent = wasActive ? current + 1 : current - 1;
                });
        });
    });
    
    bookmarkBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!isLoggedIn) {
                window.location.href = '<?php echo BASE_URL; ?>/login';
                return;
            }
            const postId = this.dataset.postId;
            const icon = this.querySelector('svg use');
            const wasActive = this.classList.contains('tg-active');
            
            this.classList.toggle('tg-active');
            
            if (icon) {
                const href = icon.getAttribute('href');
                icon.setAttribute('href', href.replace(/bookmark(-fill)?/, wasActive ? 'bookmark' : 'bookmark-fill'));
            }
            
            fetch(`<?php echo BASE_URL; ?>/post/bookmark/${postId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .catch(() => this.classList.toggle('tg-active'));
        });
    });
});
</script>