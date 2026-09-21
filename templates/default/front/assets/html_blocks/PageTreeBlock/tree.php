<?php
/**
 * Page Tree - Habr Pro - строгий
 */
$showIcons = $settings['show_icons'] ?? false;
$showStatus = $settings['show_page_status'] ?? true;
$showDescriptions = $settings['show_page_descriptions'] ?? false;
$showHome = $settings['show_home'] ?? true;
$homeTitle = $settings['home_title'] ?? 'главная';
$homeUrl = $settings['home_url'] ?? BASE_URL;
$currentPageId = $settings['current_page_id'] ?? null;
$pageTree = $settings['page_tree'] ?? [];

function renderTreeItemsHabr($items, $showStatus, $showDescriptions, $currentPageId, $level = 0) {
    if (empty($items)) return '';
    $html = '';
    $indent = $level * 12;
    foreach ($items as $item) {
        $isActive = ($currentPageId == $item['id']);
        $hasChildren = !empty($item['children']);
        $itemUrl = BASE_URL . '/page/' . $item['slug'];
        $html .= '<li class="tree-item" style="margin-left:' . $indent . 'px"><a href="' . $itemUrl . '" class="tree-link' . ($isActive ? ' active' : '') . '">';
        $html .= '<span class="tree-title">' . html($item['title']);
        if ($hasChildren) $html .= ' <span class="children-count">(' . count($item['children']) . ')</span>';
        if ($showStatus && ($item['status'] ?? '') === 'draft') $html .= ' <span class="page-status draft">черновик</span>';
        $html .= '</span></a>';
        if ($hasChildren) $html .= renderTreeItemsHabr($item['children'], $showStatus, $showDescriptions, $currentPageId, $level + 1);
        $html .= '</li>';
    }
    return $html;
}
?>
<div class="page-tree-wrapper">
    <?php if ($showHome) { ?><ul class="page-tree-tree"><li class="tree-item"><a href="<?php echo $homeUrl; ?>" class="tree-link<?php echo empty($currentPageId) ? ' active' : ''; ?>"><span class="tree-title"><?php echo html($homeTitle); ?></span></a></li></ul><?php } ?>
    <ul class="page-tree-tree"><?php echo renderTreeItemsHabr($pageTree, $showStatus, $showDescriptions, $currentPageId, 0); ?></ul>
</div>
