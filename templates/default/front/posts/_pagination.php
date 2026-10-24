<?php
/**
* Частичный шаблон: пагинация списка
*/

if (empty($pagination) || (int)($pagination['total_pages'] ?? 0) <= 1) {
    return;
}

$paginationBase = $paginationBase ?? (BASE_URL . '/posts');
$currentPage = (int)($pagination['current_page'] ?? 1);
$totalPages = (int)$pagination['total_pages'];

$pageUrl = function ($page) use ($paginationBase) {
    return $paginationBase . '?page=' . (int)$page;
};

$start = max(1, $currentPage - 2);
$end = min($totalPages, $currentPage + 2);
?>
<div class="tg-pagination">
    <ul class="pagination justify-content-center">
        <?php if ($currentPage > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="<?php echo $pageUrl($currentPage - 1); ?>" aria-label="Previous">
                    <span aria-hidden="true">«</span>
                </a>
            </li>
        <?php } else { ?>
            <li class="page-item disabled">
                <span class="page-link">«</span>
            </li>
        <?php } ?>

        <?php if ($start > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="<?php echo $pageUrl(1); ?>">1</a>
            </li>
            <?php if ($start > 2) { ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php } ?>
        <?php } ?>

        <?php for ($i = $start; $i <= $end; $i++) { ?>
            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo $pageUrl($i); ?>"><?php echo $i; ?></a>
            </li>
        <?php } ?>

        <?php if ($end < $totalPages) { ?>
            <?php if ($end < $totalPages - 1) { ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php } ?>
            <li class="page-item">
                <a class="page-link" href="<?php echo $pageUrl($totalPages); ?>"><?php echo $totalPages; ?></a>
            </li>
        <?php } ?>

        <?php if ($currentPage < $totalPages) { ?>
            <li class="page-item">
                <a class="page-link" href="<?php echo $pageUrl($currentPage + 1); ?>" aria-label="Next">
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