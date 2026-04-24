<?php
/**
 * Template Name: Страница поиска 
 */
?>

<div class="tg-search-page">
    <div class="tg-container">

        <div class="tg-page-header tg-mb-4">
            <h1 class="tg-page-title">
                <?php echo bloggy_icon('bs', 'search', '24', 'var(--tg-primary)', 'tg-mr-2'); ?>
                <?php echo LANG_TEMPLATE_SEARCH_TITLE; ?>
            </h1>
            <p class="tg-page-description tg-text-muted">
                <?php echo LANG_TEMPLATE_SEARCH_DESCRIPTION; ?>
            </p>
        </div>
        
        <div class="tg-search-form-container tg-mb-5">
            <div class="tg-card">
                <div class="tg-card-body">
                    <form action="<?php echo BASE_URL; ?>/search" method="GET" class="tg-search-form">
                        <div class="tg-search-wrapper">
                            <div class="tg-search-input-wrapper">
                                <?php echo bloggy_icon('bs', 'search', '18', 'var(--tg-text-secondary)', 'tg-search-icon'); ?>
                                <input type="text" 
                                       name="q" 
                                       class="tg-search-input" 
                                       placeholder="<?php echo LANG_TEMPLATE_SEARCH_PLACEHOLDER; ?>"
                                       value="<?php echo html($query ?? ''); ?>"
                                       autocomplete="off"
                                       autofocus>
                                <?php if (!empty($query)) { ?>
                                <button type="button" class="tg-search-clear" onclick="window.location.href='<?php echo BASE_URL; ?>/search'">
                                    <?php echo bloggy_icon('bs', 'x', '16', 'currentColor'); ?>
                                </button>
                                <?php } ?>
                            </div>
                            <button type="submit" class="tg-btn tg-btn-primary tg-search-submit">
                                <?php echo LANG_TEMPLATE_SEARCH_BTN; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <?php if (isset($error)) { ?>
        <div class="tg-alert tg-alert-error tg-mb-4">
            <div class="tg-alert-icon">
                <?php echo bloggy_icon('bs', 'exclamation-triangle', '20', '#dc3545'); ?>
            </div>
            <div class="tg-alert-content">
                <strong><?php echo LANG_TEMPLATE_SEARCH_ERROR_TITLE; ?></strong>
                <p><?php echo html($error); ?></p>
            </div>
        </div>
        <?php } ?>
        
        <?php if (empty($query)) { ?>
        <div class="tg-search-suggestions">
            <div class="row">
                <?php if (!empty($popularQueries) && is_array($popularQueries)) { ?>
                <div class="col-lg-6 tg-mb-4">
                    <div class="tg-card h-100">
                        <div class="tg-card-header">
                            <h3 class="tg-card-title">
                                <?php echo bloggy_icon('bs', 'graph-up', '18', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                <?php echo LANG_TEMPLATE_SEARCH_POPULAR_TITLE; ?>
                            </h3>
                        </div>
                        <div class="tg-card-body">
                            <div class="tg-popular-queries">
                                <?php foreach ($popularQueries as $popularQuery) { 
                                    $queryText = is_array($popularQuery) ? ($popularQuery['query'] ?? '') : $popularQuery;
                                    $queryCount = is_array($popularQuery) ? ($popularQuery['count'] ?? 1) : 1;
                                    
                                    if (empty($queryText)) continue;
                                ?>
                                <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($queryText); ?>" 
                                   class="tg-popular-query-item">
                                    <span class="tg-popular-query-text">
                                        <?php echo bloggy_icon('bs', 'search', '14', 'var(--tg-text-secondary)', 'tg-mr-2'); ?>
                                        <?php echo html($queryText); ?>
                                    </span>
                                    <span class="tg-popular-query-count"><?php echo $queryCount; ?></span>
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <?php if (!empty($suggestedSearches) && is_array($suggestedSearches)) { ?>
                <div class="col-lg-6 tg-mb-4">
                    <div class="tg-card h-100">
                        <div class="tg-card-header">
                            <h3 class="tg-card-title">
                                <?php echo bloggy_icon('bs', 'lightbulb', '18', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                <?php echo LANG_TEMPLATE_SEARCH_SUGGESTED_TITLE; ?>
                            </h3>
                        </div>
                        <div class="tg-card-body">
                            <div class="tg-suggested-searches">
                                <?php foreach ($suggestedSearches as $suggested) { 
                                    $suggestedText = is_array($suggested) ? ($suggested['query'] ?? '') : $suggested;
                                    
                                    if (empty($suggestedText)) continue;
                                ?>
                                <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($suggestedText); ?>" 
                                   class="tg-suggested-search-item">
                                    <?php echo bloggy_icon('bs', 'arrow-right', '14', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                    <?php echo html($suggestedText); ?>
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <div class="tg-quick-links tg-mt-4">
                <div class="tg-card">
                    <div class="tg-card-body">
                        <div class="tg-quick-links-header tg-mb-3">
                            <h4 class="tg-quick-links-title">
                                <?php echo bloggy_icon('bs', 'link', '16', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                <?php echo LANG_TEMPLATE_SEARCH_QUICK_LINKS_TITLE; ?>
                            </h4>
                        </div>
                        <div class="tg-quick-links-grid">
                            <a href="<?php echo BASE_URL; ?>/posts" class="tg-quick-link-item">
                                <span class="tg-quick-link-icon">
                                    <?php echo bloggy_icon('bs', 'file-text', '20', 'var(--tg-primary)'); ?>
                                </span>
                                <span class="tg-quick-link-text"><?php echo LANG_TEMPLATE_SEARCH_QUICK_LINK_POSTS; ?></span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/categories" class="tg-quick-link-item">
                                <span class="tg-quick-link-icon">
                                    <?php echo bloggy_icon('bs', 'folder', '20', 'var(--tg-primary)'); ?>
                                </span>
                                <span class="tg-quick-link-text"><?php echo LANG_TEMPLATE_SEARCH_QUICK_LINK_CATEGORIES; ?></span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/tags" class="tg-quick-link-item">
                                <span class="tg-quick-link-icon">
                                    <?php echo bloggy_icon('bs', 'tags', '20', 'var(--tg-primary)'); ?>
                                </span>
                                <span class="tg-quick-link-text"><?php echo LANG_TEMPLATE_SEARCH_QUICK_LINK_TAGS; ?></span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/archive" class="tg-quick-link-item">
                                <span class="tg-quick-link-icon">
                                    <?php echo bloggy_icon('bs', 'archive', '20', 'var(--tg-primary)'); ?>
                                </span>
                                <span class="tg-quick-link-text"><?php echo LANG_TEMPLATE_SEARCH_QUICK_LINK_ARCHIVE; ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php } elseif (!empty($results) && $total > 0) { ?>
        <div class="tg-search-results">
            <div class="tg-search-stats tg-mb-4">
                <div class="tg-card">
                    <div class="tg-card-body">
                        <div class="tg-search-stats-content">
                            <div class="tg-search-stats-info">
                                <?php echo bloggy_icon('bs', 'search', '16', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                <span>
                                    <?php echo sprintf(LANG_TEMPLATE_SEARCH_STATS_FOUND, html($query), $total, $total); ?>
                                </span>
                            </div>
                            <?php if (!empty($suggestedSearches) && is_array($suggestedSearches)) { ?>
                            <div class="tg-search-suggestions-links">
                                <span class="tg-text-muted tg-mr-2"><?php echo LANG_TEMPLATE_SEARCH_MAYBE_YOU_MEANT; ?></span>
                                <?php 
                                $suggestedCount = 0;
                                foreach ($suggestedSearches as $suggested) { 
                                    if ($suggestedCount >= 3) break;
                                    
                                    $suggestedText = is_array($suggested) ? ($suggested['query'] ?? '') : $suggested;
                                    if (empty($suggestedText)) continue;
                                    
                                    $suggestedCount++;
                                ?>
                                <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($suggestedText); ?>" 
                                   class="tg-suggestion-link">
                                    <?php echo html($suggestedText); ?>
                                </a>
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tg-results-list">
                <?php foreach ($results as $item) { 
                    $contentType = $item['content_type'] ?? $item['type'] ?? 'post';
                    $featuredImage = null;
                    if ($contentType == 'post' && !empty($item['featured_image'])) {
                        $featuredImage = BASE_URL . '/uploads/images/' . html($item['featured_image']);
                    }
                    
                    $isPasswordProtected = isset($item['password_protected']) && $item['password_protected'] == 1;
                    $title = html($item['title'] ?? '');
                    $description = html($item['description'] ?? $item['short_description'] ?? '');
                    
                    if (!empty($query)) {
                        $words = explode(' ', $query);
                        foreach ($words as $word) {
                            if (mb_strlen($word) > 2) {
                                $title = preg_replace('/(' . preg_quote($word, '/') . ')/iu', '<mark>$1</mark>', $title);
                                $description = preg_replace('/(' . preg_quote($word, '/') . ')/iu', '<mark>$1</mark>', $description);
                            }
                        }
                    }
                    
                    switch ($contentType) {
                        case 'post':
                            $url = BASE_URL . '/post/' . ($item['slug'] ?? '');
                            $typeLabel = LANG_TEMPLATE_SEARCH_TYPE_POST;
                            $typeClass = 'primary';
                            $metaInfo = '';
                            
                            if (!empty($item['category_name'])) {
                                $metaInfo .= '<a href="' . BASE_URL . '/category/' . html($item['category_slug'] ?? '') . '" class="tg-search-result-category">' . html($item['category_name']) . '</a>';
                            }
                            
                            $metaInfo .= '<span class="tg-search-result-date">' . 
                                bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1') . 
                                time_ago($item['created_at'] ?? '') . 
                                '</span>';
                            
                            if (!empty($item['views'])) {
                                $metaInfo .= '<span class="tg-search-result-views" title="' . LANG_TEMPLATE_SEARCH_VIEWS_TITLE . '">' . 
                                    bloggy_icon('bs', 'eye', '12', 'currentColor', 'tg-mr-1') . 
                                    $item['views'] . 
                                    '</span>';
                            }
                            
                            $stats = '';
                            if (!empty($item['comments_count'])) {
                                $stats .= '<span title="' . LANG_TEMPLATE_SEARCH_COMMENTS_TITLE . '">' . 
                                    bloggy_icon('bs', 'chat-dots', '12', 'currentColor', 'tg-mr-1') . 
                                    $item['comments_count'] . 
                                    '</span>';
                            }
                            break;
                            
                        case 'page':
                            $url = BASE_URL . '/page/' . ($item['slug'] ?? '');
                            $typeLabel = LANG_TEMPLATE_SEARCH_TYPE_PAGE;
                            $typeClass = 'info';
                            $metaInfo = '<span class="tg-search-result-date">' . 
                                bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1') . 
                                time_ago($item['created_at'] ?? '') . 
                                '</span>';
                            $stats = '';
                            break;
                            
                        case 'category':
                            $url = BASE_URL . '/category/' . ($item['slug'] ?? '');
                            $typeLabel = LANG_TEMPLATE_SEARCH_TYPE_CATEGORY;
                            $typeClass = 'success';
                            $metaInfo = '<span class="tg-search-result-count">' . 
                                bloggy_icon('bs', 'folder', '12', 'currentColor', 'tg-mr-1') . 
                                ($item['posts_count'] ?? 0) . ' ' . plural($item['posts_count'] ?? 0, [LANG_TEMPLATE_SEARCH_POSTS_1, LANG_TEMPLATE_SEARCH_POSTS_2, LANG_TEMPLATE_SEARCH_POSTS_3]) . 
                                '</span>';
                            $stats = '';
                            break;
                            
                        case 'tag':
                            $url = BASE_URL . '/tag/' . ($item['slug'] ?? '');
                            $typeLabel = LANG_TEMPLATE_SEARCH_TYPE_TAG;
                            $typeClass = 'warning';
                            $metaInfo = '<span class="tg-search-result-count">' . 
                                bloggy_icon('bs', 'tag', '12', 'currentColor', 'tg-mr-1') . 
                                ($item['posts_count'] ?? 0) . ' ' . plural($item['posts_count'] ?? 0, [LANG_TEMPLATE_SEARCH_POSTS_1, LANG_TEMPLATE_SEARCH_POSTS_2, LANG_TEMPLATE_SEARCH_POSTS_3]) . 
                                '</span>';
                            $stats = '';
                            break;
                            
                        case 'user':
                            $url = BASE_URL . '/profile/' . ($item['slug'] ?? '');
                            $typeLabel = LANG_TEMPLATE_SEARCH_TYPE_USER;
                            $typeClass = 'secondary';
                            $metaInfo = '<span class="tg-search-result-date">' . 
                                bloggy_icon('bs', 'person', '12', 'currentColor', 'tg-mr-1') . 
                                LANG_TEMPLATE_SEARCH_REGISTERED . ': ' . date('d.m.Y', strtotime($item['registered_at'] ?? $item['created_at'] ?? '')) . 
                                '</span>';
                            if (!empty($item['posts_count'])) {
                                $metaInfo .= '<span class="tg-search-result-count">' . 
                                    bloggy_icon('bs', 'file-text', '12', 'currentColor', 'tg-mr-1') . 
                                    $item['posts_count'] . ' ' . plural($item['posts_count'], [LANG_TEMPLATE_SEARCH_POSTS_1, LANG_TEMPLATE_SEARCH_POSTS_2, LANG_TEMPLATE_SEARCH_POSTS_3]) . 
                                    '</span>';
                            }
                            $stats = '';
                            break;
                            
                        default:
                            $url = '#';
                            $typeLabel = LANG_TEMPLATE_SEARCH_TYPE_POST;
                            $typeClass = 'primary';
                            $metaInfo = '';
                            $stats = '';
                    }
                ?>
                <div class="tg-search-result-item tg-mb-3">
                    <div class="tg-card">
                        <div class="tg-card-body">
                            <div class="tg-search-result-content">
                                <?php if ($contentType == 'post' && $featuredImage) { ?>
                                <a href="<?php echo $url; ?>" class="tg-search-result-image">
                                    <img src="<?php echo $featuredImage; ?>" 
                                         alt="<?php echo html($item['title'] ?? ''); ?>"
                                         loading="lazy">
                                </a>
                                <?php } ?>
                                
                                <div class="tg-search-result-info <?php echo ($contentType != 'post' || !$featuredImage) ? 'tg-full-width' : ''; ?>">
                                    <div class="tg-search-result-meta-top">
                                        <span class="tg-badge tg-badge-<?php echo $typeClass; ?>">
                                            <?php echo $typeLabel; ?>
                                        </span>
                                        <?php echo $metaInfo; ?>
                                    </div>
                                    
                                    <h2 class="tg-search-result-title">
                                        <a href="<?php echo $url; ?>">
                                            <?php echo $title; ?>
                                        </a>
                                        <?php if ($contentType == 'post' && $isPasswordProtected) { ?>
                                            <span class="tg-post-lock" title="<?php echo LANG_TEMPLATE_SEARCH_PASSWORD_PROTECTED_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'lock-fill', '14', 'currentColor'); ?>
                                            </span>
                                        <?php } ?>
                                    </h2>
                                    
                                    <?php if (!empty($description)) { ?>
                                    <p class="tg-search-result-excerpt">
                                        <?php echo mb_strimwidth($description, 0, 200, '...'); ?>
                                    </p>
                                    <?php } ?>
                                    
                                    <div class="tg-search-result-footer">
                                        <div class="tg-search-result-meta">
                                            <?php if ($contentType == 'post' && !empty($item['tags'])) { ?>
                                            <div class="tg-search-result-tags">
                                                <?php foreach (array_slice($item['tags'], 0, 3) as $tag) { ?>
                                                <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" 
                                                   class="tg-tag">
                                                    #<?php echo html($tag['name']); ?>
                                                </a>
                                                <?php } ?>
                                                <?php if (count($item['tags']) > 3) { ?>
                                                <span class="tg-tag-more">+<?php echo count($item['tags']) - 3; ?></span>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            
                                            <?php if (!empty($stats)) { ?>
                                            <div class="tg-search-result-stats">
                                                <?php echo $stats; ?>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        
                                        <a href="<?php echo $url; ?>" 
                                           class="tg-btn tg-btn-sm tg-btn-outline">
                                            <?php echo $contentType == 'post' ? LANG_TEMPLATE_SEARCH_READ_BTN : LANG_TEMPLATE_SEARCH_GO_BTN; ?>
                                            <?php echo bloggy_icon('bs', 'arrow-right', '12', 'currentColor', 'tg-ml-1'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <?php if ($pages > 1) { ?>
            <div class="tg-pagination tg-mt-5">
                <div class="tg-pagination-info tg-text-center tg-mb-3 tg-text-muted">
                    <?php echo sprintf(LANG_TEMPLATE_SEARCH_PAGINATION_INFO, $current_page, $pages); ?>
                </div>
                
                <div class="tg-pagination-links">
                    <?php if ($current_page > 1) { ?>
                    <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($query); ?>&page=<?php echo $current_page - 1; ?>" 
                       class="tg-pagination-prev">
                        <?php echo bloggy_icon('bs', 'arrow-left', '16', 'currentColor'); ?>
                    </a>
                    <?php } ?>
                    
                    <?php
                    $start = max(1, $current_page - 2);
                    $end = min($pages, $current_page + 2);
                    
                    for ($i = $start; $i <= $end; $i++) { 
                    ?>
                    <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>" 
                       class="tg-pagination-link <?php echo $i == $current_page ? 'tg-active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php } ?>
                    
                    <?php if ($current_page < $pages) { ?>
                    <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($query); ?>&page=<?php echo $current_page + 1; ?>" 
                       class="tg-pagination-next">
                        <?php echo bloggy_icon('bs', 'arrow-right', '16', 'currentColor'); ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            
        </div>
        
        <?php } elseif (!empty($query)) { ?>
        <div class="tg-search-empty">
            <div class="tg-card">
                <div class="tg-card-body tg-text-center tg-py-5">
                    <div class="tg-empty-state-icon tg-mb-4">
                        <?php echo bloggy_icon('bs', 'search', '48', 'var(--tg-text-secondary)'); ?>
                    </div>
                    <h3 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_SEARCH_NO_RESULTS_TITLE; ?></h3>
                    <p class="tg-empty-state-text tg-text-muted">
                        <?php echo sprintf(LANG_TEMPLATE_SEARCH_NO_RESULTS_TEXT, html($query)); ?>
                    </p>
                    
                    <?php if (!empty($suggestedSearches) && is_array($suggestedSearches)) { ?>
                    <div class="tg-search-alternatives tg-mt-4">
                        <h4 class="tg-alternatives-title tg-mb-3"><?php echo LANG_TEMPLATE_SEARCH_MAYBE_YOU_MEANT; ?></h4>
                        <div class="tg-alternatives-list">
                            <?php 
                            $suggestedCount = 0;
                            foreach ($suggestedSearches as $suggested) { 
                                if ($suggestedCount >= 6) break;
                                
                                $suggestedText = is_array($suggested) ? ($suggested['query'] ?? '') : $suggested;
                                if (empty($suggestedText)) continue;
                                
                                $suggestedCount++;
                            ?>
                            <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($suggestedText); ?>" 
                               class="tg-alternative-item">
                                <?php echo html($suggestedText); ?>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="tg-empty-actions tg-mt-4">
                        <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                            <?php echo bloggy_icon('bs', 'file-text', '16', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_SEARCH_ALL_POSTS_BTN; ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/search" class="tg-btn tg-btn-outline tg-ml-2">
                            <?php echo bloggy_icon('bs', 'search', '16', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_SEARCH_NEW_SEARCH_BTN; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>