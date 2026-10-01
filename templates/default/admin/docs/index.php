<?php
    add_admin_css('templates/default/admin/assets/css/controllers/docs.css');
    add_admin_js('templates/default/admin/assets/js/controllers/docs.js');
?>

<div class="container-fluid p-0 docs-page-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0 d-flex align-items-center">
                <?php echo bloggy_icon('bs', 'book-half', '24', '#0d6efd', 'me-2'); ?>
                <span><?php echo LANG_TEMPLATE_DOCS_INDEX_PAGE_TITLE; ?></span>
            </h4>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                BloggyCMS Docs
            </span>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/settings?tab=components&controller=docs" class="btn btn-outline-secondary d-flex align-items-center">
                <?php echo bloggy_icon('bs', 'gear', '18', null, 'me-1'); ?>
                <span><?php echo LANG_TEMPLATE_DOCS_INDEX_SETTINGS_BTN; ?></span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-3 col-md-4">
            <div class="card border-0 shadow-sm docs-nav-card sticky-top" style="top: 20px; z-index: 10;">
                <div class="card-body p-3">
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <?php echo bloggy_icon('bs', 'search', '14', '#6c757d'); ?>
                            </span>
                            <input type="text" id="docsSearchInput" class="form-control border-start-0" placeholder="<?php echo LANG_TEMPLATE_DOCS_INDEX_SEARCH_PLACEHOLDER; ?>">
                        </div>
                    </div>

                    <div class="docs-navigation-tree">
                        <?php foreach ($structure as $secKey => $secData) { ?>
                            <?php if (empty($secData['files'])) continue; ?>
                            <div class="docs-nav-group mb-3">
                                <div class="docs-nav-group-title text-uppercase fw-bold text-muted small mb-2 d-flex align-items-center">
                                    <?php echo bloggy_icon('bs', $secData['icon'] ?? 'folder', '14', '#6c757d', 'me-2'); ?>
                                    <span><?php echo html($secData['title']); ?></span>
                                </div>
                                <ul class="list-unstyled mb-0 ps-2 docs-nav-list">
                                    <?php foreach ($secData['files'] as $fileItem) { 
                                        $isActive = ($fileItem['key'] === $currentDoc);
                                    ?>
                                        <li class="docs-nav-item mb-1">
                                            <a href="<?php echo ADMIN_URL; ?>/docs?doc=<?php echo rawurlencode($fileItem['key']); ?>" 
                                               class="docs-nav-link d-flex align-items-center py-1 px-2 rounded <?php echo $isActive ? 'active' : ''; ?>"
                                               data-doc="<?php echo html($fileItem['key']); ?>"
                                               title="<?php echo html($fileItem['title']); ?>">
                                                <?php echo bloggy_icon('bs', $isActive ? 'file-earmark-text-fill' : 'file-earmark-text', '14', $isActive ? '#0d6efd' : '#6c757d', 'me-2 flex-shrink-0'); ?>
                                                <span class="text-truncate"><?php echo html($fileItem['title']); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm docs-content-card">
                <div class="card-body p-4 p-lg-5">
                    <div class="docs-content-meta d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                        <div class="docs-badge-path text-muted small d-flex align-items-center">
                            <?php echo bloggy_icon('bs', 'folder2', '14', '#6c757d', 'me-1'); ?>
                            <code>docs/<?php echo html($currentDoc); ?></code>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/docs/<?php echo html($currentDoc); ?>" target="_blank" class="btn btn-sm btn-outline-light text-secondary border d-flex align-items-center" title="<?php echo LANG_TEMPLATE_DOCS_INDEX_OPEN_FULL_PAGE; ?>">
                            <?php echo bloggy_icon('bs', 'box-arrow-up-right', '12', null, 'me-1'); ?>
                            <span class="small"><?php echo LANG_TEMPLATE_DOCS_INDEX_OPEN_FULL_PAGE; ?></span>
                        </a>
                    </div>

                    <article class="docs-markdown-body" id="docsArticleBody">
                        <?php echo $docData['html']; ?>
                    </article>
                </div>
            </div>
        </div>
    </div>
</div>
