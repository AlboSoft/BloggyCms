<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-title-wrapper">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-1">
                        <h1 class="dashboard-title mb-0">
                            <?php echo bloggy_icon('bs', 'speedometer2', '28', '#0088cc', 'me-2'); ?>
                            <?php echo LANG_DASHBOARD_TITLE; ?>
                        </h1>
                    </div>
                    <p class="dashboard-subtitle mb-0"><?php echo LANG_DASHBOARD_WELCOME; ?></p>
                </div>
                <div class="header-actions">
                    <div class="debug-icon-wrapper" id="debugToggleBtn" title="<?php echo LANG_DASHBOARD_DEBUG_MODE; ?>">
                        <?php 
                        $debugEnabled = SettingsHelper::get('general', 'debug_mode', false);
                        $bugColor = $debugEnabled ? '#dc3545' : '#6c757d';
                        ?>
                        <div class="bug-icon">
                            <?php echo bloggy_icon('bs', 'bug', '28', $bugColor); ?>
                        </div>
                        <span class="debug-status <?php echo $debugEnabled ? 'active' : ''; ?>"></span>
                    </div>
                    <a href="<?php echo ADMIN_URL; ?>/language" class="language-icon-wrapper" title="<?php echo LANG_DASHBOARD_LANGUAGE_SETTINGS; ?>">
                        <div class="language-icon">
                            <?php 
                            $currentLang = $_SESSION['admin_language'] ?? SettingsHelper::get('general', 'admin_language', 'ru_RU');
                            $langFlag = $currentLang === 'ru_RU' ? '🇷🇺' : ($currentLang === 'en_EN' ? '🇬🇧' : '🌐');
                            ?>
                            <div class="language-icon-inner">
                                <?php echo bloggy_icon('bs', 'translate', '24', '#6c757d'); ?>
                                <span class="language-badge"><?php echo $langFlag; ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="/admin/settings?tab=components&controller=admin" class="settings-icon-link" title="<?php echo LANG_DASHBOARD_COMPONENTS_SETTINGS; ?>">
                        <?php echo bloggy_icon('bs', 'gear', '28', '#0004ff'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    $installPath = ROOT_PATH . '/install';
    $installExists = is_dir($installPath);

    if ($installExists) {
        $days = 0;
        $installDate = @filemtime($installPath);
        if ($installDate) {
            $days = floor((time() - $installDate) / 86400);
        }
        
        $messages = [LANG_DASHBOARD_INSTALL_WARNING_1, LANG_DASHBOARD_INSTALL_WARNING_2,
            LANG_DASHBOARD_INSTALL_WARNING_3, LANG_DASHBOARD_INSTALL_WARNING_4,
            LANG_DASHBOARD_INSTALL_WARNING_5, LANG_DASHBOARD_INSTALL_WARNING_6,
        ];
        
        $randomMessage = $messages[array_rand($messages)];
        ?>
        
        <div class="alert alert-danger alert-dismissible fade show install-alert" role="alert">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <?php echo bloggy_icon('bs', 'exclamation-triangle-fill', '28', '#fff'); ?>
                    </div>
                    <div>
                        <strong class="fs-6"><?php echo LANG_DASHBOARD_INSTALL_CRITICAL; ?></strong><br>
                        <span id="install-warning-message"><?php echo $randomMessage; ?></span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-danger btn-sm" id="delete-install-folder-btn" style = "margin-right: 20px">
                        <?php echo bloggy_icon('bs', 'trash', '16', '#fff', 'me-1'); ?>
                        <?php echo LANG_DASHBOARD_INSTALL_DELETE_BTN; ?>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="<?php echo LANG_DASHBOARD_CLOSE; ?>"></button>
                </div>
            </div>
        </div>
        
    <?php } ?>

    <div class="stats-grid">
        <?php if(SettingsHelper::get('controller_admin', 'all_posts') == true) { ?>
            <div class="stat-card stat-card-posts">
                <div class="stat-icon">
                    <?php echo bloggy_icon('bs', 'pencil', '28', '#0088cc'); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-label"><?php echo LANG_DASHBOARD_STATS_POSTS; ?></span>
                    <span class="stat-number"><?= $stats['posts'] ?? 0 ?></span>
                </div>
                <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                    <a href="<?= ADMIN_URL ?>/posts" class="stat-link btn btn-sm btn-primary"><?php echo bloggy_icon('bs', 'arrow-right', '12', '#fff'); ?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'categories') == true) { ?>
            <div class="stat-card stat-card-categories">
                <div class="stat-icon">
                    <?php echo bloggy_icon('bs', 'folder2-open', '28', '#10b981'); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-label"><?php echo LANG_DASHBOARD_STATS_CATEGORIES; ?></span>
                    <span class="stat-number"><?= $stats['categories'] ?? 0 ?></span>
                </div>
                <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                    <a href="<?= ADMIN_URL ?>/categories" class="stat-link btn btn-sm btn-primary"><?php echo bloggy_icon('bs', 'arrow-right', '12', '#fff'); ?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'tags') == true) { ?>
            <div class="stat-card stat-card-tags">
                <div class="stat-icon">
                    <?php echo bloggy_icon('bs', 'hash', '28', '#f59e0b'); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-label"><?php echo LANG_DASHBOARD_STATS_TAGS; ?></span>
                    <span class="stat-number"><?= $stats['tags'] ?? 0 ?></span>
                </div>
                <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                    <a href="<?= ADMIN_URL ?>/tags" class="stat-link btn btn-sm btn-primary"><?php echo bloggy_icon('bs', 'arrow-right', '12', '#fff'); ?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'pages') == true) { ?>
            <div class="stat-card stat-card-pages">
                <div class="stat-icon">
                    <?php echo bloggy_icon('bs', 'file-earmark-richtext', '28', '#8b5cf6'); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-label"><?php echo LANG_DASHBOARD_STATS_PAGES; ?></span>
                    <span class="stat-number"><?= $stats['pages'] ?? 0 ?></span>
                </div>
                <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                    <a href="<?= ADMIN_URL ?>/pages" class="stat-link btn btn-sm btn-primary"><?php echo bloggy_icon('bs', 'arrow-right', '12', '#fff'); ?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'comments') == true) { ?>
            <div class="stat-card stat-card-comments">
                <div class="stat-icon">
                    <?php echo bloggy_icon('bs', 'chat-left-dots', '28', '#ef4444'); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-label"><?php echo LANG_DASHBOARD_STATS_COMMENTS; ?></span>
                    <span class="stat-number"><?= $stats['comments'] ?? 0 ?></span>
                </div>
                <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                    <a href="<?= ADMIN_URL ?>/comments" class="stat-link btn btn-sm btn-primary"><?php echo bloggy_icon('bs', 'arrow-right', '12', '#fff'); ?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'users') == true) { ?>
            <div class="stat-card stat-card-users">
                <div class="stat-icon">
                    <?php echo bloggy_icon('bs', 'person', '28', '#06b6d4'); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-label"><?php echo LANG_DASHBOARD_STATS_USERS; ?></span>
                    <span class="stat-number"><?= $stats['users'] ?? 0 ?></span>
                </div>
                <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                    <a href="<?= ADMIN_URL ?>/users" class="stat-link btn btn-sm btn-primary"><?php echo bloggy_icon('bs', 'arrow-right', '12', '#fff'); ?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'content_blocks') == true) { ?>
            <div class="stat-card stat-card-blocks">
                <div class="stat-icon">
                    <?php echo bloggy_icon('bs', 'layout-wtf', '28', '#ec489a'); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-label"><?php echo LANG_DASHBOARD_STATS_CONTENT_BLOCKS; ?></span>
                    <span class="stat-number"><?= $stats['content_blocks'] ?? 0 ?></span>
                </div>
                <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                    <a href="<?= ADMIN_URL ?>/html-blocks" class="stat-link btn btn-sm btn-primary"><?php echo bloggy_icon('bs', 'arrow-right', '12', '#fff'); ?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php 
            $extraCards = Event::trigger('admin.dashboard.stats_cards', [$stats, $this]);
            if (!empty($extraCards) && is_string($extraCards)) {
                echo $extraCards;
            }
        ?>
    </div>

    <div class="content-grid">
        <?php if(SettingsHelper::get('controller_admin', 'last_posts') == true) { ?>
            <div class="content-card">
                <div class="card-header-modern">
                    <?php echo bloggy_icon('bs', 'clock', '20', '#0088cc', 'me-2'); ?>
                    <h3><?php echo LANG_DASHBOARD_NEW_POSTS; ?></h3>
                </div>
                <div class="card-body-modern">
                    <?php if(!empty($recentPosts)) { ?>
                        <div class="post-list">
                            <?php foreach($recentPosts as $post) { ?>
                                <a href="<?= BASE_URL ?>/post/<?= $post['slug'] ?>" target="_blank" class="post-item">
                                    <div class="post-content">
                                        <h4><?php echo html($post['title']) ?></h4>
                                        <div class="post-meta">
                                            <?php echo bloggy_icon('bs', 'calendar', '12', '#6c757d', 'me-1'); ?>
                                            <span><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <?php echo bloggy_icon('bs', 'chevron-right', '16', '#cbd5e1'); ?>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="empty-state-modern">
                            <?php echo bloggy_icon('bs', 'file-text', '48', '#cbd5e1', 'mb-3'); ?>
                            <p><?php echo LANG_DASHBOARD_NO_POSTS; ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'popular_posts') == true) { ?>
            <div class="content-card">
                <div class="card-header-modern">
                    <?php echo bloggy_icon('bs', 'fire', '20', '#10b981', 'me-2'); ?>
                    <h3><?php echo LANG_DASHBOARD_POPULAR_POSTS; ?></h3>
                </div>
                <div class="card-body-modern">
                    <?php if(!empty($popularPosts)) { ?>
                        <div class="post-list">
                            <?php foreach($popularPosts as $post) { ?>
                                <a href="<?= BASE_URL ?>/post/<?= $post['slug'] ?>" target="_blank" class="post-item">
                                    <div class="post-content">
                                        <h4><?php echo html($post['title']) ?></h4>
                                        <div class="post-meta">
                                            <?php echo bloggy_icon('bs', 'eye', '12', '#10b981', 'me-1'); ?>
                                            <span><?php echo html($post['views']) ?> <?php echo LANG_DASHBOARD_VIEWS; ?></span>
                                        </div>
                                    </div>
                                    <?php echo bloggy_icon('bs', 'chevron-right', '16', '#cbd5e1'); ?>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="empty-state-modern">
                            <?php echo bloggy_icon('bs', 'bar-chart', '48', '#cbd5e1', 'mb-3'); ?>
                            <p><?php echo LANG_DASHBOARD_NO_POPULAR_POSTS; ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'comments_posts') == true) { ?>
            <div class="content-card">
                <div class="card-header-modern">
                    <?php echo bloggy_icon('bs', 'chat-dots', '20', '#ef4444', 'me-2'); ?>
                    <h3><?php echo LANG_DASHBOARD_MOST_COMMENTED; ?></h3>
                </div>
                <div class="card-body-modern">
                    <?php if(!empty($commentedPosts)) { ?>
                        <div class="post-list">
                            <?php foreach($commentedPosts as $post) { ?>
                                <a href="<?= BASE_URL ?>/post/<?= $post['slug'] ?>" target="_blank" class="post-item">
                                    <div class="post-content">
                                        <h4><?php echo html($post['title']) ?></h4>
                                        <div class="post-meta">
                                            <?php echo bloggy_icon('bs', 'chat', '12', '#ef4444', 'me-1'); ?>
                                            <span><?php echo html($post['comments_count']) ?> <?php echo LANG_DASHBOARD_COMMENTS; ?></span>
                                        </div>
                                    </div>
                                    <?php echo bloggy_icon('bs', 'chevron-right', '16', '#cbd5e1'); ?>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="empty-state-modern">
                            <?php echo bloggy_icon('bs', 'chat', '48', '#cbd5e1', 'mb-3'); ?>
                            <p><?php echo LANG_DASHBOARD_NO_COMMENTED_POSTS; ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if(SettingsHelper::get('controller_admin', 'show_drafts') == true) { ?>
            <div class="content-card">
                <div class="card-header-modern">
                    <?php echo bloggy_icon('bs', 'file-earmark', '20', '#f59e0b', 'me-2'); ?>
                    <h3><?php echo LANG_DASHBOARD_DRAFTS; ?></h3>
                </div>
                <div class="card-body-modern">
                    <?php if(!empty($draftPosts)) { ?>
                        <div class="post-list">
                            <?php foreach($draftPosts as $post) { ?>
                                <div class="post-item draft-item">
                                    <div class="post-content">
                                        <h4><?php echo html($post['title']) ?></h4>
                                        <div class="post-meta">
                                            <?php echo bloggy_icon('bs', 'clock', '12', '#f59e0b', 'me-1'); ?>
                                            <span><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="draft-actions">
                                        <a href="<?= ADMIN_URL ?>/posts/edit/<?= $post['id'] ?>" class="action-btn" title="<?php echo LANG_DASHBOARD_EDIT; ?>">
                                            <?php echo bloggy_icon('bs', 'pencil', '14', '#0088cc'); ?>
                                        </a>
                                        <a href="<?= ADMIN_URL ?>/posts/toggle-status/<?= $post['id'] ?>" 
                                           class="action-btn" 
                                           title="<?php echo LANG_DASHBOARD_PUBLISH; ?>"
                                           onclick="return confirm('<?php echo LANG_DASHBOARD_PUBLISH_CONFIRM; ?>')">
                                            <?php echo bloggy_icon('bs', 'check-lg', '14', '#10b981'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="empty-state-modern">
                            <?php echo bloggy_icon('bs', 'file-earmark', '48', '#cbd5e1', 'mb-3'); ?>
                            <p><?php echo LANG_DASHBOARD_NO_DRAFTS; ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if(SettingsHelper::get('controller_admin', 'show_search') == true || SettingsHelper::get('controller_admin', 'show_popular_search') == true) { ?>
        <div class="search-grid">
            <?php if(SettingsHelper::get('controller_admin', 'show_search') == true) { ?>
                <div class="content-card search-card">
                    <div class="card-header-modern">
                        <?php echo bloggy_icon('bs', 'search', '20', '#0088cc', 'me-2'); ?>
                        <h3><?php echo LANG_DASHBOARD_RECENT_SEARCHES; ?></h3>
                    </div>
                    <div class="card-body-modern">
                        <?php if(!empty($recentSearches)) { ?>
                            <div class="search-list">
                                <?php foreach($recentSearches as $search) { ?>
                                    <div class="search-item">
                                        <a href="<?= BASE_URL ?>/search?q=<?= urlencode($search['query']) ?>" target="_blank" class="search-query">
                                            <?php echo bloggy_icon('bs', 'search', '14', '#0088cc', 'me-2'); ?>
                                            <?= htmlspecialchars($search['query']) ?>
                                        </a>
                                        <div class="search-stats">
                                            <span class="search-count">
                                                <?php echo bloggy_icon('bs', 'arrow-repeat', '12', '#6c757d', 'me-1'); ?>
                                                <?= $search['count'] ?> <?= plural_form($search['count'], explode('|', LANG_DASHBOARD_TIMES)); ?>
                                            </span>
                                            <span class="search-time">
                                                <?php echo bloggy_icon('bs', 'clock', '12', '#6c757d', 'me-1'); ?>
                                                <?= date('d.m.Y H:i', strtotime($search['last_searched_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="card-footer-modern">
                                <a href="<?= ADMIN_URL ?>/search-history" class="link-btn"><?php echo LANG_DASHBOARD_ALL_REQUESTS; ?> →</a>
                            </div>
                        <?php } else { ?>
                            <div class="empty-state-modern">
                                <?php echo bloggy_icon('bs', 'search', '48', '#cbd5e1', 'mb-3'); ?>
                                <p><?php echo LANG_DASHBOARD_NO_SEARCHES; ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <?php if(SettingsHelper::get('controller_admin', 'show_popular_search') == true) { ?>
                <div class="content-card search-card">
                    <div class="card-header-modern">
                        <?php echo bloggy_icon('bs', 'star', '20', '#f59e0b', 'me-2'); ?>
                        <h3><?php echo LANG_DASHBOARD_POPULAR_SEARCHES; ?></h3>
                    </div>
                    <div class="card-body-modern">
                        <?php if(!empty($popularSearches)) { ?>
                            <div class="search-list">
                                <?php foreach($popularSearches as $search) { ?>
                                    <div class="search-item popular">
                                        <a href="<?= BASE_URL ?>/search?q=<?= urlencode($search['query']) ?>" target="_blank" class="search-query">
                                            <?php echo bloggy_icon('bs', 'fire', '14', '#f59e0b', 'me-2'); ?>
                                            <?= htmlspecialchars($search['query']) ?>
                                        </a>
                                        <div class="search-stats">
                                            <span class="search-count">
                                                <?php echo bloggy_icon('bs', 'arrow-repeat', '12', '#6c757d', 'me-1'); ?>
                                                <?= $search['count'] ?> <?= plural_form($search['count'], explode('|', LANG_DASHBOARD_TIMES)); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="card-footer-modern">
                                <a href="<?= ADMIN_URL ?>/search-history" class="link-btn"><?php echo LANG_DASHBOARD_ALL_REQUESTS; ?> →</a>
                            </div>
                        <?php } else { ?>
                            <div class="empty-state-modern">
                                <?php echo bloggy_icon('bs', 'star', '48', '#cbd5e1', 'mb-3'); ?>
                                <p><?php echo LANG_DASHBOARD_NO_POPULAR_SEARCHES; ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php ob_start(); ?>
<script>
    (function() {
        const debugBtn = document.getElementById('debugToggleBtn');
        if (!debugBtn) return;
        
        const bugIcon = debugBtn.querySelector('.bug-icon svg');
        const statusDot = debugBtn.querySelector('.debug-status');
        const currentState = <?php echo SettingsHelper::get('general', 'debug_mode', false) ? 'true' : 'false'; ?>;
        
        function showNotification(message, isError = false) {
            const toast = document.createElement('div');
            toast.className = 'custom-toast-debug';
            
            const iconSvg = isError 
                ? '<?php echo bloggy_icon("bs", "exclamation-triangle-fill", "16", "#ffc107"); ?>'
                : '<?php echo bloggy_icon("bs", "bug-fill", "16", "#ff7818"); ?>';
            
            const closeIconSvg = '<?php echo bloggy_icon("bs", "x-lg", "12", "#6c757d"); ?>';
            
            toast.innerHTML = `
                <div class="toast-content">
                    <div class="toast-icon">${iconSvg}</div>
                    <span class="toast-message">${message}</span>
                    <div class="toast-close">${closeIconSvg}</div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            toast.querySelector('.toast-close').addEventListener('click', () => {
                toast.remove();
            });
            
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 3000);
        }
        
        function updateIconState(enabled) {
            const svg = bugIcon;
            if (svg) {
                const paths = svg.querySelectorAll('path, circle, rect');
                const newColor = enabled ? '#dc3545' : '#6c757d';
                paths.forEach(path => {
                    path.setAttribute('fill', newColor);
                    if (path.hasAttribute('stroke')) {
                        path.setAttribute('stroke', newColor);
                    }
                });
            }
            
            if (statusDot) {
                if (enabled) {
                    statusDot.classList.add('active');
                    statusDot.classList.add('pulse');
                    setTimeout(() => statusDot.classList.remove('pulse'), 500);
                } else {
                    statusDot.classList.remove('active');
                }
            }
        }
        
        function toggleDebugMode() {
            const wasEnabled = <?php echo SettingsHelper::get('general', 'debug_mode', false) ? 'true' : 'false'; ?>;
            
            fetch(`${ADMIN_URL}/debug/toggle`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const isEnabled = data.debug_enabled;
                    updateIconState(isEnabled);
                    
                    if (isEnabled) {
                        showNotification('<?php echo LANG_DASHBOARD_DEBUG_ENABLED_NOTIFY; ?>');
                    } else {
                        showNotification('<?php echo LANG_DASHBOARD_DEBUG_DISABLED_NOTIFY; ?>');
                    }
                    
                    const headerToggle = document.getElementById('headerDebugToggle');
                    const pageToggle = document.getElementById('debugModeToggle');
                    if (headerToggle) headerToggle.checked = isEnabled;
                    if (pageToggle) pageToggle.checked = isEnabled;
                } else {
                    showNotification('<?php echo LANG_DASHBOARD_DEBUG_ERROR; ?>' + (data.message || '<?php echo LANG_DASHBOARD_UNKNOWN_ERROR; ?>'), true);
                }
            })
            .catch(error => {
                console.error('Error toggling debug mode:', error);
                showNotification('<?php echo LANG_DASHBOARD_NETWORK_ERROR; ?>', true);
            });
        }
        
        debugBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleDebugMode();
        });
        
        debugBtn.addEventListener('mouseenter', function() {
            const isEnabled = statusDot && statusDot.classList.contains('active');
            debugBtn.title = isEnabled ? '<?php echo LANG_DASHBOARD_DEBUG_ON_TITLE; ?>' : '<?php echo LANG_DASHBOARD_DEBUG_OFF_TITLE; ?>';
        });
    })();

    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtn = document.getElementById('delete-install-folder-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                if (confirm('<?php echo LANG_DASHBOARD_DELETE_INSTALL_CONFIRM; ?>')) {
                    const btn = this;
                    const originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> <?php echo LANG_DASHBOARD_DELETING; ?>...';
                    
                    fetch(ADMIN_URL + '/delete-install-folder', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const alert = document.querySelector('.install-alert');
                            if (alert) {
                                alert.style.transition = 'opacity 0.5s';
                                alert.style.opacity = '0';
                                setTimeout(() => alert.remove(), 500);
                            }
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            console.error(data.message);
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    });
                }
            });
        }
    });
</script>
<?php admin_bottom_js(ob_get_clean()); ?>

<?php if(SettingsHelper::get('controller_admin', 'show_detailed_stats', true)) { ?>
    <?php add_admin_js('templates/default/admin/assets/js/controllers/chart.umd.min.js'); ?>
    <?php add_admin_js('templates/default/admin/assets/js/controllers/dashboard-stats.js'); ?>
<?php } ?>