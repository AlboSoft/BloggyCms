<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'chat-dots', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_COMMENTS_INDEX_TITLE; ?>
        </h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($comments)) { ?>
                <div class="text-center py-5">
                    <?php echo bloggy_icon('bs', 'chat-dots', '48', '#6c757d', 'mb-3'); ?>
                    <h5 class="text-muted mb-2"><?php echo LANG_TEMPLATE_COMMENTS_INDEX_NO_COMMENTS_TITLE; ?></h5>
                    <p class="text-muted small mb-0"><?php echo LANG_TEMPLATE_COMMENTS_INDEX_NO_COMMENTS_TEXT; ?></p>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                             <tr>
                                <th><?php echo LANG_TEMPLATE_COMMENTS_INDEX_TABLE_ID; ?></th>
                                <th><?php echo LANG_TEMPLATE_COMMENTS_INDEX_TABLE_POST; ?></th>
                                <th><?php echo LANG_TEMPLATE_COMMENTS_INDEX_TABLE_AUTHOR; ?></th>
                                <th><?php echo LANG_TEMPLATE_COMMENTS_INDEX_TABLE_COMMENT; ?></th>
                                <th><?php echo LANG_TEMPLATE_COMMENTS_INDEX_TABLE_STATUS; ?></th>
                                <th><?php echo LANG_TEMPLATE_COMMENTS_INDEX_TABLE_DATE; ?></th>
                                <th class="text-end"><?php echo LANG_TEMPLATE_COMMENTS_INDEX_TABLE_ACTIONS; ?></th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment) { ?>
                                <tr>
                                    <td class="text-muted">#<?php echo $comment['id']; ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <?php echo html($comment['post_title']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo html($comment['author_username'] ?? $comment['author_name']); ?>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;">
                                            <?php echo nl2br(html($comment['content'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusLabels = [
                                            'pending' => LANG_TEMPLATE_COMMENTS_INDEX_STATUS_PENDING,
                                            'approved' => LANG_TEMPLATE_COMMENTS_INDEX_STATUS_APPROVED,
                                            'spam' => LANG_TEMPLATE_COMMENTS_INDEX_STATUS_SPAM
                                        ];
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'spam' => 'danger'
                                        ][$comment['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo $statusLabels[$comment['status']] ?? $comment['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo admin_action_group([
                                            ['type' => 'edit', 'url' => ADMIN_URL . '/comments/edit/' . $comment['id'], 'title' => LANG_TEMPLATE_COMMENTS_INDEX_ACTION_EDIT],
                                            $comment['status'] === 'pending'
                                                ? ['type' => 'approve', 'url' => ADMIN_URL . '/comments/approve/' . $comment['id'], 'title' => LANG_TEMPLATE_COMMENTS_INDEX_ACTION_APPROVE]
                                                : null,
                                            ['type' => 'delete', 'url' => ADMIN_URL . '/comments/delete/' . $comment['id'], 'title' => LANG_TEMPLATE_COMMENTS_INDEX_ACTION_DELETE, 'confirm' => LANG_TEMPLATE_COMMENTS_INDEX_CONFIRM_DELETE],
                                        ]); ?>
                                     </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                     </table>
                </div>

                <?php if ($pages > 1) { ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $pages; $i++) { ?>
                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo ADMIN_URL; ?>/comments?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </nav>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>