<?php
/**
 * Page Tree Accordion - Habr Pro
 */
$showIcons = $settings['show_icons'] ?? false;
$showStatus = $settings['show_page_status'] ?? true;
$showHome = $settings['show_home'] ?? true;
$expandAll = $settings['expand_all'] ?? false;
$homeTitle = $settings['home_title'] ?? 'главная';
$homeUrl = $settings['home_url'] ?? BASE_URL;
$currentPageId = $settings['current_page_id'] ?? null;
$pageTree = $settings['page_tree'] ?? [];

function renderAccordionLevelHabr($tree, $settings, $expandAll = false) {
    if (empty($tree)) return '';
    $html = '';
    foreach ($tree as $page) {
        $isActive = ($settings['currentPageId'] == $page['id']);
        $hasChildren = !empty($page['children']);
        $pageUrl = BASE_URL . '/page/' . $page['slug'];
        $isOpen = $expandAll || $isActive;
        $html .= '<div class="accordion-item' . ($isOpen ? ' open' : '') . '"><div class="accordion-header">';
        if ($hasChildren) $html .= '<span class="accordion-icon">▸</span>';
        $html .= '<a href="' . $pageUrl . '" class="accordion-link' . ($isActive ? ' active' : '') . '"><span class="item-title">' . html($page['title']) . '</span>';
        if ($settings['showStatus'] && ($page['status'] ?? '') === 'draft') $html .= '<span class="page-status draft">черновик</span>';
        $html .= '</a></div>';
        if ($hasChildren) $html .= '<div class="accordion-content">' . renderAccordionLevelHabr($page['children'], $settings, $expandAll) . '</div>';
        $html .= '</div>';
    }
    return $html;
}
$renderSettings = ['showStatus' => $showStatus, 'currentPageId' => $currentPageId];
?>
<div class="page-tree-accordion">
    <?php if ($showHome) { ?><div class="accordion-item"><div class="accordion-header"><a href="<?php echo $homeUrl; ?>" class="accordion-link<?php echo empty($currentPageId) ? ' active' : ''; ?>"><span class="item-title"><?php echo html($homeTitle); ?></span></a></div></div><?php } ?>
    <?php echo renderAccordionLevelHabr($pageTree, $renderSettings, $expandAll); ?>
</div>
