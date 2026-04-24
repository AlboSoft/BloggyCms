<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'search', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_TITLE; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/search-history/clear" 
           class="btn btn-danger"
           onclick="return confirm('<?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_CLEAR_CONFIRM; ?>')">
            <?php echo bloggy_icon('bs', 'trash', '16', '#fff', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_CLEAR_BTN; ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($queries)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'search', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_EMPTY_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_EMPTY_HINT; ?></p>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_TABLE_QUERY; ?></th>
                                <th><?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_TABLE_COUNT; ?></th>
                                <th><?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_TABLE_LAST_SEARCH; ?></th>
                                <th><?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_TABLE_FIRST_SEARCH; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queries as $query) { ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($query['query']); ?>" 
                                           class="text-decoration-none"
                                           target="_blank">
                                            <?php echo bloggy_icon('bs', 'search', '14', '#6C6C6C', 'me-2'); ?>
                                            <?php echo html($query['query']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo sprintf(LANG_TEMPLATE_SEARCH_ADMININDEX_TIMES, $query['count']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d.m.Y H:i', strtotime($query['last_searched_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d.m.Y H:i', strtotime($query['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <a href="<?php echo ADMIN_URL; ?>/search-history/delete/<?php echo $query['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('<?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_DELETE_CONFIRM; ?>')"
                                               title="<?php echo LANG_TEMPLATE_SEARCH_ADMININDEX_DELETE_TITLE; ?>">
                                                <?php echo bloggy_icon('bs', 'trash', '14', '#000'); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                     </table>
                </div>

                <?php if (isset($pages) && $pages > 1) { ?>
                    <nav aria-label="Pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page > 1) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo ADMIN_URL; ?>/search-history?page=<?php echo $current_page - 1; ?>">
                                        <?php echo bloggy_icon('bs', 'chevron-left', '14', '#000'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                            
                            <?php
                            $start = max(1, $current_page - 2);
                            $end = min($pages, $start + 4);
                            if ($end - $start < 4) {
                                $start = max(1, $end - 4);
                            }
                            
                            for ($i = $start; $i <= $end; $i++) {
                                $isActive = $i == $current_page;
                            ?>
                                <li class="page-item <?php echo $isActive ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo ADMIN_URL; ?>/search-history?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php } ?>
                            
                            <?php if ($current_page < $pages) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo ADMIN_URL; ?>/search-history?page=<?php echo $current_page + 1; ?>">
                                        <?php echo bloggy_icon('bs', 'chevron-right', '14', '#000'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </nav>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>