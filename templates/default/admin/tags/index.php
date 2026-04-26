<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><?php echo bloggy_icon('bs', 'tag', '24', '#000', 'me-2 controller-svg'); ?><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_TITLE; ?></h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/settings?tab=components&controller=tags" class="btn btn-outline-secondary"><?php echo bloggy_icon('bs', 'gear-fill', '20', '#000', 'me-2'); ?><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_SETTINGS_BTN; ?></a>
            <a href="<?php echo ADMIN_URL; ?>/tags/create" class="btn btn-primary"><?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_ADD_BTN; ?></a>
        </div>
    </div>

    <?php if (SettingsHelper::get('controller_tags', 'show_info') == true) { ?>
        <div class="alert alert-info d-flex align-items-center mb-3">
            <?php echo bloggy_icon('bs', 'info-circle', '16', '#5AAFC9', 'me-2'); ?>
            <span><?php echo html($randomHint); ?></span>
        </div>
    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($tags)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3"><?php echo bloggy_icon('bs', 'tags', '48', '#6C6C6C'); ?></div>
                    <h5 class="text-muted"><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_EMPTY_TITLE; ?></h5>
                    <p class="text-muted"><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_EMPTY_HINT; ?></p>
                    <a href="<?php echo ADMIN_URL; ?>/tags/create" class="btn btn-primary"><?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_ADD_BTN; ?></a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_TABLE_IMAGE; ?></th>
                                <th><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_TABLE_TAG; ?></th>
                                <th><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_TABLE_URL; ?></th>
                                <th><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_TABLE_POSTS; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_TAGS_ADMININDEX_TABLE_ACTIONS; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tags as $tag) { ?>
                                <tr>
                                    <td style="width: 60px;">
                                        <?php if (!empty($tag['image'])) { ?>
                                            <img src="/uploads/tags/<?php echo html($tag['image']); ?>" 
                                                 alt="<?php echo html($tag['name']); ?>" 
                                                 class="rounded"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php } else { ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <?php echo bloggy_icon('bs', 'tag', '20', '#999'); ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <strong><?php echo SettingsHelper::get('controller_tags', 'tag_prefix', '#'); ?><?php echo html($tag['name']); ?></strong>
                                        <?php if (!empty($tag['description'])) { ?>
                                            <span class="badge bg-primary text-white ms-2" 
                                                  style="font-size: 12px; cursor: help; font-weight: 500;"
                                                  title="<?php echo html($tag['description']); ?>" 
                                                  data-bs-toggle="tooltip"
                                                  data-bs-placement="top">
                                                <?php echo bloggy_icon('bs', 'file-text', '13', '#fff', 'me-1'); ?>
                                                Описание
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <code class="text-muted"><?php echo html($tag['slug']); ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo $tag['posts_count'] ?? 0; ?> <?php echo LANG_TEMPLATE_TAGS_ADMININDEX_POSTS; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo $tag['slug']; ?>" class="btn btn-sm btn-secondary" target="_blank" title="<?php echo LANG_TEMPLATE_TAGS_ADMININDEX_VIEW_TITLE; ?>"><?php echo bloggy_icon('bs', 'eye', '16', '#000'); ?></a>
                                            <a href="<?php echo ADMIN_URL; ?>/tags/edit/<?php echo $tag['id']; ?>" class="btn btn-sm btn-success" title="<?php echo LANG_TEMPLATE_TAGS_ADMININDEX_EDIT_TITLE; ?>"><?php echo bloggy_icon('bs', 'pencil', '16', '#fff'); ?></a>
                                            <a href="<?php echo ADMIN_URL; ?>/tags/delete/<?php echo $tag['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo LANG_TEMPLATE_TAGS_ADMININDEX_DELETE_CONFIRM; ?>')" title="<?php echo LANG_TEMPLATE_TAGS_ADMININDEX_DELETE_TITLE; ?>"><?php echo bloggy_icon('bs', 'trash', '16', '#fff'); ?></a>
                                        </div>
                                     </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                     </table>
                </div>
            <?php } ?>
        </div>
    </div>
</div>