<?php

$robots_settings = $robots_settings ?? [
    'enabled' => true,
    'disallow_paths' => ['/admin/', '/system/'],
    'allow_paths' => [],
    'crawl_delay' => 0,
    'sitemap_url' => ''
];

$sitemap_settings = $sitemap_settings ?? [
    'enabled' => true,
    'include_posts' => true,
    'include_pages' => true,
    'include_categories' => true,
    'include_tags' => true,
    'max_posts' => 1000,
    'cache_enabled' => true,
    'cache_lifetime' => 3600
];

$rss_settings = $rss_settings ?? [
    'enabled' => true,
    'posts_limit' => 20,
    'include_full_content' => false,
    'copyright' => '',
    'language' => 'ru-ru'
];

$indexnow_settings = $indexnow_settings ?? [];
if (empty($indexnow_settings)) {
    $settingsModel = new SettingsModel($this->db);
    $indexnow_settings = $settingsModel->get('seo_indexnow');
}
$indexnow_settings = array_merge([
    'enabled' => false,
    'ya_key' => '',
    'bing_key' => '',
    'seznam_key' => '',
    'auto_submit' => true,
    'submit_delay' => 0,
    'notify_error' => true
], $indexnow_settings);

?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <?php echo bloggy_icon('bs', 'graph-up', '20', 'var(--bs-primary)', 'me-2') ?>
                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_TITLE; ?>
            </h1>
            <p class="text-muted mb-0">
                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_DESCRIPTION; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL ?>/sitemap.xml" target="_blank" class="btn btn-outline-secondary btn-sm">
                <?php echo bloggy_icon('bs', 'file-earmark-code', '14', 'currentColor', 'me-1') ?>
                sitemap.xml
            </a>
            <a href="<?php echo BASE_URL ?>/robots.txt" target="_blank" class="btn btn-outline-secondary btn-sm">
                <?php echo bloggy_icon('bs', 'file-text', '14', 'currentColor', 'me-1') ?>
                robots.txt
            </a>
            <a href="<?php echo BASE_URL ?>/rss.xml" target="_blank" class="btn btn-outline-secondary btn-sm">
                <?php echo bloggy_icon('bs', 'rss', '14', 'currentColor', 'me-1') ?>
                RSS
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo ADMIN_URL ?>/seo/settings" enctype="multipart/form-data" id="seo-settings-form">
        <?php echo \CsrfToken::field('seo_settings') ?>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <ul class="nav nav-tabs nav-tabs-custom" id="seoSettingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="robots-tab" data-bs-toggle="tab" data-bs-target="#robots" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'file-text', '14', 'currentColor', 'me-1') ?>
                            Robots.txt
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sitemap-tab" data-bs-toggle="tab" data-bs-target="#sitemap" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'map', '14', 'currentColor', 'me-1') ?>
                            Sitemap.xml
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rss-tab" data-bs-toggle="tab" data-bs-target="#rss" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'rss', '14', 'currentColor', 'me-1') ?>
                            RSS Ленты
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="indexnow-tab" data-bs-toggle="tab" data-bs-target="#indexnow" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'rocket', '14', 'currentColor', 'me-1') ?>
                            IndexNow
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="schema-tab" data-bs-toggle="tab" data-bs-target="#schema" type="button" role="tab">
                            <?php echo bloggy_icon('bs', 'diagram-3', '14', 'currentColor', 'me-1') ?><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_TAB; ?>
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="seoSettingsTabContent">
                    <div class="tab-pane fade show active" id="robots" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="robots_enabled" id="robots_enabled" value="1" <?php echo $robots_settings['enabled'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="robots_enabled">
                                        <?php echo LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_ENABLE; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_CRAWL_DELAY; ?></label>
                                <input type="number" class="form-control" name="robots_crawl_delay" value="<?php echo $robots_settings['crawl_delay'] ?>">
                                <div class="form-text"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_CRAWL_DELAY_HINT; ?></div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_DISALLOW; ?></label>
                                <textarea class="form-control" name="robots_disallow" rows="5" placeholder="/admin/&#10;/system/"><?php echo html(implode("\n", $robots_settings['disallow_paths'])) ?></textarea>
                                <div class="form-text"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_PATH_HINT; ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_ALLOW; ?></label>
                                <textarea class="form-control" name="robots_allow" rows="5" placeholder="/assets/&#10;/images/"><?php echo html(implode("\n", $robots_settings['allow_paths'])) ?></textarea>
                                <div class="form-text"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_PATH_HINT; ?></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_SITEMAP_URL; ?></label>
                            <input type="url" class="form-control" name="robots_sitemap_url" value="<?php echo html($robots_settings['sitemap_url']) ?>" placeholder="https://example.com/sitemap.xml">
                            <div class="form-text"><?php echo sprintf(LANG_TEMPLATE_SEO_ADMININDEX_ROBOTS_SITEMAP_HINT, BASE_URL); ?></div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="sitemap" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="sitemap_enabled" id="sitemap_enabled" value="1" <?php echo $sitemap_settings['enabled'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="sitemap_enabled">
                                        <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_ENABLE; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="sitemap_cache_enabled" id="sitemap_cache_enabled" value="1" <?php echo $sitemap_settings['cache_enabled'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="sitemap_cache_enabled">
                                        <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_CACHE_ENABLE; ?>
                                    </label>
                                </div>
                                <?php if ($sitemap_settings['cache_enabled']) { ?>
                                    <div class="mt-2">
                                        <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_CACHE_LIFETIME; ?></label>
                                        <input type="number" class="form-control" name="sitemap_cache_lifetime" value="<?php echo $sitemap_settings['cache_lifetime'] ?>">
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_INCLUDE; ?></label>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sitemap_include_posts" id="sitemap_include_posts" value="1" <?php echo $sitemap_settings['include_posts'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sitemap_include_posts"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_INCLUDE_POSTS; ?></label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sitemap_include_pages" id="sitemap_include_pages" value="1" <?php echo $sitemap_settings['include_pages'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sitemap_include_pages"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_INCLUDE_PAGES; ?></label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sitemap_include_categories" id="sitemap_include_categories" value="1" <?php echo $sitemap_settings['include_categories'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sitemap_include_categories"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_INCLUDE_CATEGORIES; ?></label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sitemap_include_tags" id="sitemap_include_tags" value="1" <?php echo $sitemap_settings['include_tags'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sitemap_include_tags"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_INCLUDE_TAGS; ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_MAX_POSTS; ?></label>
                            <input type="number" class="form-control" name="sitemap_max_posts" value="<?php echo $sitemap_settings['max_posts'] ?>" min="1" max="50000">
                            <div class="form-text"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SITEMAP_MAX_POSTS_HINT; ?></div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="rss" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="rss_enabled" id="rss_enabled" value="1" <?php echo $rss_settings['enabled'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="rss_enabled">
                                        <?php echo LANG_TEMPLATE_SEO_ADMININDEX_RSS_ENABLE; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="rss_full_content" id="rss_full_content" value="1" <?php echo $rss_settings['include_full_content'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="rss_full_content">
                                        <?php echo LANG_TEMPLATE_SEO_ADMININDEX_RSS_FULL_CONTENT; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_RSS_POSTS_LIMIT; ?></label>
                                <input type="number" class="form-control" name="rss_limit" value="<?php echo $rss_settings['posts_limit'] ?>" min="1" max="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_RSS_LANGUAGE; ?></label>
                                <input type="text" class="form-control" name="rss_language" value="<?php echo html($rss_settings['language']) ?>" placeholder="ru-ru">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_RSS_COPYRIGHT; ?></label>
                            <input type="text" class="form-control" name="rss_copyright" value="<?php echo html($rss_settings['copyright']) ?>" placeholder="© 2025 My Blog">
                        </div>
                    </div>

                    <div class="tab-pane fade" id="indexnow" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="card-title mb-0">
                                    <?php echo bloggy_icon('bs', 'rocket', '20', 'var(--bs-primary)', 'me-2') ?>
                                    <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_TITLE; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong><?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_WHAT_TITLE; ?></strong><br>
                                    <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_WHAT_DESC; ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                name="indexnow_enabled" id="indexnow_enabled" value="1" 
                                                <?php echo $indexnow_settings['enabled'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="indexnow_enabled">
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_ENABLE; ?>
                                            </label>
                                            <div class="form-text">
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_ENABLE_HINT; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                name="indexnow_auto_submit" id="indexnow_auto_submit" value="1" 
                                                <?php echo $indexnow_settings['auto_submit'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="indexnow_auto_submit">
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_AUTO_SUBMIT; ?>
                                            </label>
                                            <div class="form-text">
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_AUTO_SUBMIT_HINT; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'clock', '14', 'currentColor', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_SUBMIT_DELAY; ?>
                                        </label>
                                        <input type="number" class="form-control" 
                                            name="indexnow_submit_delay" 
                                            value="<?php echo (int)$indexnow_settings['submit_delay'] ?>" 
                                            min="0" max="300">
                                        <div class="form-text">
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_SUBMIT_DELAY_HINT; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                name="indexnow_notify_error" id="indexnow_notify_error" value="1" 
                                                <?php echo $indexnow_settings['notify_error'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="indexnow_notify_error">
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_NOTIFY_ERROR; ?>
                                            </label>
                                            <div class="form-text">
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_NOTIFY_ERROR_HINT; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h6 class="mb-3">
                                    <?php echo bloggy_icon('bs', 'key', '18', 'currentColor', 'me-2') ?>
                                    <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_VERIFICATION_KEYS; ?>
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'yandex', '14', '#fc3f1d', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_YANDEX_KEY; ?>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" 
                                                name="indexnow_ya_key" 
                                                value="<?php echo html($indexnow_settings['ya_key'] ?? '') ?>" 
                                                placeholder="<?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_KEY_PLACEHOLDER; ?>" 
                                                pattern="[a-zA-Z0-9-]+">
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="generateKey(this, 'ya_key')">
                                                <?php echo bloggy_icon('bs', 'arrow-repeat', '14', 'currentColor') ?>
                                            </button>
                                        </div>
                                        <div class="form-text">
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_KEY_HINT; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'microsoft', '14', '#00a4ef', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_BING_KEY; ?>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" 
                                                name="indexnow_bing_key" 
                                                value="<?php echo html($indexnow_settings['bing_key'] ?? '') ?>" 
                                                placeholder="<?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_KEY_PLACEHOLDER; ?>" 
                                                pattern="[a-zA-Z0-9-]+">
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="generateKey(this, 'bing_key')">
                                                <?php echo bloggy_icon('bs', 'arrow-repeat', '14', 'currentColor') ?>
                                            </button>
                                        </div>
                                        <div class="form-text">
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_KEY_HINT; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3" id="indexnow_keys_info"  style="<?php echo !$indexnow_settings['enabled'] ? 'display: none;' : '' ?>">
                                    <div class="d-flex align-items-start">
                                        <?php echo bloggy_icon('bs', 'info-circle', '18', 'currentColor', 'me-2 mt-1') ?>
                                        <div>
                                            <strong><?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_KEY_FILES_TITLE; ?></strong><br>
                                            <?php
                                            $hasKeys = false;
                                            if (!empty($indexnow_settings['ya_key'])) {
                                                $hasKeys = true;
                                                $keyExists = $indexnow_settings['ya_key_exists'] ?? false;
                                                $statusIcon = $keyExists ? 
                                                    bloggy_icon('bs', 'check-circle-fill', '14', '#198754', 'me-1') : 
                                                    bloggy_icon('bs', 'x-circle-fill', '14', '#dc3545', 'me-1');
                                                $statusText = $keyExists ? LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_FILE_EXISTS : LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_FILE_NOT_FOUND;
                                                echo '<div class="d-flex align-items-center mt-1">' . $statusIcon . 
                                                    '<code><a href="' . BASE_URL . '/' . $indexnow_settings['ya_key'] . '.txt" target="_blank">' . 
                                                    BASE_URL . '/' . $indexnow_settings['ya_key'] . '.txt</a></code> (Яндекс) ' .
                                                    '<span class="badge ' . ($keyExists ? 'bg-success' : 'bg-danger') . ' ms-2">' . $statusText . '</span></div>';
                                            }
                                            if (!empty($indexnow_settings['bing_key'])) {
                                                $hasKeys = true;
                                                $keyExists = $indexnow_settings['bing_key_exists'] ?? false;
                                                $statusIcon = $keyExists ? 
                                                    bloggy_icon('bs', 'check-circle-fill', '14', '#198754', 'me-1') : 
                                                    bloggy_icon('bs', 'x-circle-fill', '14', '#dc3545', 'me-1');
                                                $statusText = $keyExists ? LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_FILE_EXISTS : LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_FILE_NOT_FOUND;
                                                echo '<div class="d-flex align-items-center mt-1">' . $statusIcon . 
                                                    '<code><a href="' . BASE_URL . '/' . $indexnow_settings['bing_key'] . '.txt" target="_blank">' . 
                                                    BASE_URL . '/' . $indexnow_settings['bing_key'] . '.txt</a></code> (Bing) ' .
                                                    '<span class="badge ' . ($keyExists ? 'bg-success' : 'bg-danger') . ' ms-2">' . $statusText . '</span></div>';
                                            }
                                            if (!$hasKeys) {
                                                echo '<span class="text-muted">' . LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_NO_KEYS . '</span>';
                                            }
                                            ?>
                                            <div class="mt-2 small text-muted">
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_KEY_FILES_HINT; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-light border mt-3" id="indexnow_test_block" style="<?php echo !$indexnow_settings['enabled'] ? 'display: none;' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php echo bloggy_icon('bs', 'flask', '18', 'currentColor', 'me-2') ?>
                                            <strong><?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_TEST_TITLE; ?></strong>
                                            <div class="small text-muted mt-1">
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_TEST_DESC; ?>
                                            </div>
                                            <?php if (!empty($indexnow_settings['is_localhost'])) { ?>
                                            <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                                <?php echo bloggy_icon('bs', 'exclamation-triangle', '14', '#856404', 'me-1') ?>
                                                <strong><?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_LOCALHOST_WARNING_TITLE; ?></strong> 
                                                <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_LOCALHOST_WARNING; ?>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <a href="<?php echo ADMIN_URL ?>/seo/test-indexnow" 
                                        class="btn btn-sm btn-outline-primary"
                                        onclick="return confirm('<?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_TEST_CONFIRM; ?>')">
                                            <?php echo bloggy_icon('bs', 'send', '14', 'currentColor', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_TEST_BTN; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="schema" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="card-title mb-0">
                                    <?php echo bloggy_icon('bs', 'building', '20', 'var(--bs-primary)', 'me-2') ?>
                                    <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_INFO; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_TITLE; ?></strong><br>
                                    <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_DESC; ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'building', '14', 'currentColor', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_NAME; ?>
                                        </label>
                                        <input type="text" class="form-control" 
                                            name="schema_org_name" 
                                            value="<?php echo html($schema_settings['org_name'] ?? '') ?>" 
                                            placeholder="Например: My Company LLC">
                                        <div class="form-text"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_NAME_HINT; ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'diagram-3', '14', 'currentColor', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_TYPE; ?>
                                        </label>
                                        <select class="form-select" name="schema_org_type">
                                            <option value="Organization" <?php echo ($schema_settings['org_type'] ?? '') === 'Organization' ? 'selected' : '' ?>>Organization</option>
                                            <option value="Corporation" <?php echo ($schema_settings['org_type'] ?? '') === 'Corporation' ? 'selected' : '' ?>>Corporation</option>
                                            <option value="LocalBusiness" <?php echo ($schema_settings['org_type'] ?? '') === 'LocalBusiness' ? 'selected' : '' ?>>LocalBusiness</option>
                                            <option value="NewsMediaOrganization" <?php echo ($schema_settings['org_type'] ?? '') === 'NewsMediaOrganization' ? 'selected' : '' ?>>NewsMediaOrganization</option>
                                            <option value="Person" <?php echo ($schema_settings['org_type'] ?? '') === 'Person' ? 'selected' : '' ?>><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_TYPE_PERSON; ?></option>
                                        </select>
                                        <div class="form-text"><?php echo sprintf(LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_TYPE_HINT, '<a href="https://schema.org/Organization" target="_blank">schema.org</a>'); ?></div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'image', '14', 'currentColor', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_LOGO; ?>
                                        </label>
                                        <input type="text" class="form-control" 
                                            name="schema_org_logo" 
                                            value="<?php echo html($schema_settings['org_logo'] ?? '') ?>" 
                                            placeholder="https://yoursite.com/logo.png">
                                        <div class="form-text"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_LOGO_HINT; ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'globe', '14', 'currentColor', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_URL; ?>
                                        </label>
                                        <input type="url" class="form-control" 
                                            name="schema_org_url" 
                                            value="<?php echo html($schema_settings['org_url'] ?? BASE_URL) ?>">
                                        <div class="form-text"><?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_ORG_URL_HINT; ?></div>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h6 class="mb-3">
                                    <?php echo bloggy_icon('bs', 'share', '18', 'currentColor', 'me-2') ?>
                                    <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_SOCIAL_PROFILES; ?>
                                </h6>
                                <p class="text-muted small mb-3">
                                    <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_SOCIAL_PROFILES_HINT; ?>
                                </p>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('brands', 'facebook', '14', '#1877F2', 'me-1') ?>
                                            Facebook
                                        </label>
                                        <input type="url" class="form-control" 
                                            name="schema_social_facebook" 
                                            value="<?php echo html($schema_settings['social_facebook'] ?? '') ?>" 
                                            placeholder="https://facebook.com/yourpage">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('brands', 'twitter', '14', '#1DA1F2', 'me-1') ?>
                                            Twitter / X
                                        </label>
                                        <input type="url" class="form-control" 
                                            name="schema_social_twitter" 
                                            value="<?php echo html($schema_settings['social_twitter'] ?? '') ?>" 
                                            placeholder="https://twitter.com/yourprofile">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('brands', 'instagram', '14', '#E4405F', 'me-1') ?>
                                            Instagram
                                        </label>
                                        <input type="url" class="form-control" 
                                            name="schema_social_instagram" 
                                            value="<?php echo html($schema_settings['social_instagram'] ?? '') ?>" 
                                            placeholder="https://instagram.com/yourprofile">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('brands', 'telegram', '14', '#0088cc', 'me-1') ?>
                                            Telegram
                                        </label>
                                        <input type="url" class="form-control" 
                                            name="schema_social_telegram" 
                                            value="<?php echo html($schema_settings['social_telegram'] ?? '') ?>" 
                                            placeholder="https://t.me/yourchannel">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('brands', 'vk', '14', '#0077FF', 'me-1') ?>
                                            ВКонтакте
                                        </label>
                                        <input type="url" class="form-control" 
                                            name="schema_social_vk" 
                                            value="<?php echo html($schema_settings['social_vk'] ?? '') ?>" 
                                            placeholder="https://vk.com/yourpage">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('brands', 'youtube', '14', '#FF0000', 'me-1') ?>
                                            YouTube
                                        </label>
                                        <input type="url" class="form-control" 
                                            name="schema_social_youtube" 
                                            value="<?php echo html($schema_settings['social_youtube'] ?? '') ?>" 
                                            placeholder="https://youtube.com/yourchannel">
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h6 class="mb-3">
                                    <?php echo bloggy_icon('bs', 'envelope', '18', 'currentColor', 'me-2') ?>
                                    <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_CONTACT_INFO; ?>
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'envelope', '14', 'currentColor', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_CONTACT_EMAIL; ?>
                                        </label>
                                        <input type="email" class="form-control" 
                                            name="schema_contact_email" 
                                            value="<?php echo html($schema_settings['contact_email'] ?? '') ?>" 
                                            placeholder="info@yoursite.com">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo bloggy_icon('bs', 'telephone', '14', 'currentColor', 'me-1') ?>
                                            <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SCHEMA_CONTACT_PHONE; ?>
                                        </label>
                                        <input type="tel" class="form-control" 
                                            name="schema_contact_phone" 
                                            value="<?php echo html($schema_settings['contact_phone'] ?? '') ?>" 
                                            placeholder="+7 999 123-45-67">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="card-footer bg-white border-0">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'save', '14', 'currentColor', 'me-1') ?>
                        <?php echo LANG_TEMPLATE_SEO_ADMININDEX_SAVE_BTN; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL ?>/seo/clear-cache" 
                       class="btn btn-outline-danger"
                       onclick="return confirm('<?php echo LANG_TEMPLATE_SEO_ADMININDEX_CLEAR_CACHE_CONFIRM; ?>')">
                        <?php echo bloggy_icon('bs', 'trash', '14', 'currentColor', 'me-1') ?>
                        <?php echo LANG_TEMPLATE_SEO_ADMININDEX_CLEAR_CACHE_BTN; ?>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const triggerTabList = document.querySelectorAll('#seoSettingsTab button');
            triggerTabList.forEach(triggerEl => {
                const tabTrigger = new bootstrap.Tab(triggerEl);
                triggerEl.addEventListener('click', event => {
                    event.preventDefault();
                    tabTrigger.show();
                });
            });
            
            const cacheCheckbox = document.getElementById('sitemap_cache_enabled');
            if (cacheCheckbox) {
                const cacheSettings = cacheCheckbox.closest('.col-md-6').querySelector('.mt-2');
                if (cacheSettings) {
                    const toggleCacheSettings = () => {
                        cacheSettings.style.display = cacheCheckbox.checked ? 'block' : 'none';
                    };
                    cacheCheckbox.addEventListener('change', toggleCacheSettings);
                    toggleCacheSettings();
                }
            }
        });

        function generateKey(btn, fieldName) {
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-';
            let key = '';
            for (let i = 0; i < 32; i++) {
                key += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            const input = btn.closest('.input-group').querySelector('input');
            input.value = key;

            const toast = document.createElement('div');
            toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            toast.innerHTML = '<?php echo LANG_TEMPLATE_SEO_ADMININDEX_KEY_GENERATED; ?>';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const enabledCheckbox = document.getElementById('indexnow_enabled');
            const keysInfo = document.getElementById('indexnow_keys_info');
            const testBlock = document.getElementById('indexnow_test_block');
            
            if (enabledCheckbox) {
                const toggleBlocks = () => {
                    const isEnabled = enabledCheckbox.checked;
                    if (keysInfo) keysInfo.style.display = isEnabled ? 'block' : 'none';
                    if (testBlock) testBlock.style.display = isEnabled ? 'block' : 'none';
                };
                
                enabledCheckbox.addEventListener('change', toggleBlocks);
                toggleBlocks();
            }
            
            const yaKeyInput = document.querySelector('input[name="indexnow_ya_key"]');
            const bingKeyInput = document.querySelector('input[name="indexnow_bing_key"]');
            
            if (yaKeyInput && !yaKeyInput.value) {
                yaKeyInput.placeholder = '<?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_KEY_PLACEHOLDER; ?>';
            }
            if (bingKeyInput && !bingKeyInput.value) {
                bingKeyInput.placeholder = '<?php echo LANG_TEMPLATE_SEO_ADMININDEX_INDEXNOW_KEY_PLACEHOLDER; ?>';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const googleEnabled = document.getElementById('google_enabled');
            const googleTestBlock = document.getElementById('google_test_block');
            
            if (googleEnabled) {
                const toggleTestBlock = () => {
                    if (googleTestBlock) {
                        googleTestBlock.style.display = googleEnabled.checked ? 'block' : 'none';
                    }
                };
                
                googleEnabled.addEventListener('change', toggleTestBlock);
                toggleTestBlock();
            }
        });
    </script>
<?php admin_bottom_js(ob_get_clean()); ?>