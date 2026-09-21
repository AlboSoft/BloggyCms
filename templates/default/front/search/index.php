<?php
/**
 * Template Name: Поиск - Habr Pro
 */
?>
<div class="tg-search-page">
    <div class="tg-container">
        <div class="tg-page-header">
            <h1 class="tg-page-title">поиск</h1>
            <p class="tg-page-description">найдите публикации по ключевым словам</p>
        </div>
        
        <div class="tg-search-form-container" style="margin-bottom:16px">
            <div class="tg-card"><div class="tg-card-body">
                <form action="<?php echo BASE_URL; ?>/search" method="GET" class="tg-search-form">
                    <div class="tg-search-wrapper">
                        <div class="tg-search-input-wrapper">
                            <span class="tg-search-icon">⌕</span>
                            <input type="text" name="q" class="tg-search-input" placeholder="что ищем?" value="<?php echo html($query ?? ''); ?>" autocomplete="off" autofocus>
                        </div>
                        <button type="submit" class="tg-btn tg-btn-primary tg-search-submit">найти</button>
                    </div>
                </form>
            </div></div>
        </div>
        
        <?php if (isset($error)) { ?><div class="tg-alert tg-alert-error"><div class="tg-alert-content"><strong>ошибка</strong><p><?php echo html($error); ?></p></div></div><?php } ?>
        
        <?php if (empty($query)) { ?>
        <div class="tg-search-suggestions">
            <?php if (!empty($popularQueries)) { ?>
                <div class="tg-card" style="margin-bottom:16px"><div class="tg-card-header"><h3 class="tg-card-title">популярные запросы</h3></div><div class="tg-card-body" style="padding:0"><div class="tg-popular-queries">
                    <?php foreach ($popularQueries as $pq) { $qt = is_array($pq) ? ($pq['query'] ?? '') : $pq; $qc = is_array($pq) ? ($pq['count'] ?? 1) : 1; if (empty($qt)) continue; ?>
                    <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($qt); ?>" class="tg-popular-query-item"><span class="tg-popular-query-text"><?php echo html($qt); ?></span><span class="tg-popular-query-count"><?php echo $qc; ?></span></a>
                    <?php } ?>
                </div></div></div>
            <?php } ?>
            <div class="tg-card"><div class="tg-card-body"><h4 style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px">быстрые ссылки</h4><div class="tg-quick-links-grid">
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-quick-link-item"><span>все посты</span></a>
                <a href="<?php echo BASE_URL; ?>/categories" class="tg-quick-link-item"><span>разделы</span></a>
                <a href="<?php echo BASE_URL; ?>/tags" class="tg-quick-link-item"><span>теги</span></a>
                <a href="<?php echo BASE_URL; ?>/archive" class="tg-quick-link-item"><span>архив</span></a>
            </div></div></div>
        </div>
        <?php } else { ?>
            <?php if (!empty($posts)) { ?>
                <div class="tg-search-stats-content"><span>найдено <?php echo count($posts); ?> публикаций по запросу «<?php echo html($query); ?>»</span></div>
                <div class="tg-posts-list" style="margin-top:16px">
                    <?php foreach ($posts as $post) {
                        $showCoverInList = !isset($post['show_cover_in_list']) || (int)$post['show_cover_in_list'] === 1;
                        $featuredImage = ($post['featured_image'] && $showCoverInList) ? BASE_URL . '/uploads/images/' . html($post['featured_image']) : null;
                    ?>
                    <article class="tg-search-result-item">
                        <div class="tg-search-result-content">
                            <?php if ($featuredImage) { ?><a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>" class="tg-search-result-image"><img src="<?php echo $featuredImage; ?>" alt="<?php echo html($post['title']); ?>"></a><?php } ?>
                            <div class="tg-search-result-info">
                                <div class="tg-search-result-meta-top"><span class="tg-search-result-date"><?php echo date('d.m.Y', strtotime($post['created_at'])); ?></span></div>
                                <h3 class="tg-search-result-title"><a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>"><?php echo html($post['title']); ?></a></h3>
                                <?php if (!empty($post['short_description'])) { ?><p class="tg-search-result-excerpt"><?php echo html($post['short_description']); ?></p><?php } ?>
                                <div class="tg-search-result-footer"><span style="font-size:11px;color:#8a8a8a">👁 <?php echo $post['views'] ?? 0; ?> · 💬 <?php echo $post['comments_count'] ?? 0; ?></span></div>
                            </div>
                        </div>
                    </article>
                    <?php } ?>
                </div>
                <?php if (!empty($pagination) && $pagination['total_pages'] > 1) { ?>
                    <div class="tg-pagination">
                        <?php for ($i=1;$i<=$pagination['total_pages'];$i++) { ?><a class="tg-pagination-link <?php echo $i==$pagination['current_page']?'tg-active':''; ?>" href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a><?php } ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="tg-empty-state"><h3 class="tg-empty-state-title">ничего не найдено</h3><p class="tg-empty-state-text">по запросу «<?php echo html($query); ?>» публикаций нет</p><div class="tg-empty-actions"><a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">все публикации</a></div></div>
            <?php } ?>
        <?php } ?>
    </div>
</div>
