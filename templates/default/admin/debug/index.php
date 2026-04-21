<?php
add_admin_js('templates/default/admin/assets/js/controllers/debug.js');
add_admin_css('templates/default/admin/assets/css/controllers/debug.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'bug', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_DEBUG_INDEX_TITLE; ?>
        </h4>
        <div class="d-flex gap-2">
            <div class="debug-toggle-switch">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="debugModeToggle" 
                           <?php echo $debug_enabled ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="debugModeToggle">
                        <?php echo bloggy_icon('bs', $debug_enabled ? 'eye' : 'eye-slash', '16', '#000', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_DEBUG_INDEX_DEBUG_MODE; ?>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="stats-container">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0" id="stat-errors"><?php echo $stats['errors']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_DEBUG_INDEX_STAT_ERRORS; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0" id="stat-warnings"><?php echo $stats['warnings']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_DEBUG_INDEX_STAT_WARNINGS; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'exclamation', '32', '#000'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0" id="stat-notices"><?php echo $stats['notices']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_DEBUG_INDEX_STAT_NOTICES; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'info-circle', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0" id="stat-exceptions"><?php echo $stats['exceptions']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_DEBUG_INDEX_STAT_EXCEPTIONS; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'bug', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0" id="stat-unfixed"><?php echo $stats['unfixed']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_DEBUG_INDEX_STAT_UNFIXED; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'wrench', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0" id="stat-total"><?php echo $stats['total']; ?></h4>
                            <small><?php echo LANG_TEMPLATE_DEBUG_INDEX_STAT_TOTAL; ?></small>
                        </div>
                        <?php echo bloggy_icon('bs', 'database', '32', '#fff'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label"><?php echo LANG_TEMPLATE_DEBUG_INDEX_FILTER_TYPE; ?></label>
                    <select class="form-select" id="filter-type">
                        <option value=""><?php echo LANG_TEMPLATE_DEBUG_INDEX_FILTER_ALL; ?></option>
                        <option value="error"><?php echo LANG_TEMPLATE_DEBUG_INDEX_FILTER_ERRORS; ?></option>
                        <option value="warning"><?php echo LANG_TEMPLATE_DEBUG_INDEX_FILTER_WARNINGS; ?></option>
                        <option value="notice"><?php echo LANG_TEMPLATE_DEBUG_INDEX_FILTER_NOTICES; ?></option>
                        <option value="exception"><?php echo LANG_TEMPLATE_DEBUG_INDEX_FILTER_EXCEPTIONS; ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="filter-unfixed" role="switch">
                        <label class="form-check-label" for="filter-unfixed">
                            <?php echo LANG_TEMPLATE_DEBUG_INDEX_FILTER_ONLY_UNFIXED; ?>
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" id="apply-filters">
                        <?php echo bloggy_icon('bs', 'funnel', '16', '#fff', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_DEBUG_INDEX_APPLY_FILTER_BTN; ?>
                    </button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-danger w-100" id="delete-all-logs">
                        <?php echo bloggy_icon('bs', 'trash', '16', '#000', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_DEBUG_INDEX_DELETE_ALL_BTN; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <?php echo bloggy_icon('bs', 'list-ul', '20', '#000', 'me-2'); ?>
                <?php echo LANG_TEMPLATE_DEBUG_INDEX_LOG_TITLE; ?>
            </h5>
            <span class="badge bg-secondary" id="logs-count">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="logs-table">
                    <thead class="table-light">
                        <tr>
                            <th width="50"></th>
                            <th><?php echo LANG_TEMPLATE_DEBUG_INDEX_TABLE_TYPE; ?></th>
                            <th><?php echo LANG_TEMPLATE_DEBUG_INDEX_TABLE_MESSAGE; ?></th>
                            <th><?php echo LANG_TEMPLATE_DEBUG_INDEX_TABLE_FILE; ?></th>
                            <th><?php echo LANG_TEMPLATE_DEBUG_INDEX_TABLE_DATE; ?></th>
                            <th width="120" class="text-end"><?php echo LANG_TEMPLATE_DEBUG_INDEX_TABLE_ACTIONS; ?></th>
                        </tr>
                    </thead>
                    <tbody id="logs-tbody">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted"><?php echo LANG_TEMPLATE_DEBUG_INDEX_LOADING; ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0">
            <nav id="pagination-container"></nav>
        </div>
    </div>
</div>

<div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?php echo bloggy_icon('bs', 'file-text', '18', '#000', 'me-2'); ?>
                    <?php echo LANG_TEMPLATE_DEBUG_INDEX_MODAL_TITLE; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="log-detail-content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo LANG_TEMPLATE_DEBUG_INDEX_MODAL_CLOSE; ?></button>
                <button type="button" class="btn btn-success" id="mark-fixed-btn" style="display: none;">
                    <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                    <?php echo LANG_TEMPLATE_DEBUG_INDEX_MODAL_MARK_FIXED; ?>
                </button>
            </div>
        </div>
    </div>
</div>