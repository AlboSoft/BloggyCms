<?php
/**
* Аккордеон
*/

$showIcons = $settings['show_icons'] ?? true;
$showStatus = $settings['show_page_status'] ?? true;
$showHome = $settings['show_home'] ?? true;
$expandAll = $settings['expand_all'] ?? false;
$homeTitle = $settings['home_title'] ?? 'Главная';
$homeUrl = $settings['home_url'] ?? BASE_URL;
$currentPageId = $settings['current_page_id'] ?? null;
$pageTree = $settings['page_tree'] ?? [];

function renderAccordionLevel($tree, $settings, $expandAll = false) {
    if (empty($tree)) {
        return '';
    }
    
    $html = '';
    
    foreach ($tree as $page) {
        $isActive = ($settings['currentPageId'] == $page['id']);
        $hasChildren = !empty($page['children']);
        $pageUrl = BASE_URL . '/page/' . $page['slug'];
        $isOpen = $expandAll || $isActive;
        
        $html .= '<div class="accordion-item' . ($isOpen ? ' open' : '') . '">';
        $html .= '<div class="accordion-header">';
        
        if ($hasChildren && $settings['showIcons']) {
            $html .= '<span class="accordion-icon">';
            $html .= bloggy_icon('bs', 'chevron-right', '14', 'currentColor');
            $html .= '</span>';
        } elseif ($settings['showIcons']) {
            $html .= '<span class="accordion-icon-placeholder" style="width: 20px;"></span>';
        }
        
        $html .= '<a href="' . $pageUrl . '" class="accordion-link' . ($isActive ? ' active' : '') . '">';
        
        if ($settings['showIcons']) {
            if ($hasChildren) {
                $html .= '<span class="item-icon">' . bloggy_icon('bs', 'folder', '16', 'currentColor') . '</span>';
            } else {
                $html .= '<span class="item-icon">' . bloggy_icon('bs', 'file-text', '16', 'currentColor') . '</span>';
            }
        }
        
        $html .= '<span class="item-title">' . html($page['title']) . '</span>';
        
        if ($settings['showStatus'] && ($page['status'] ?? '') === 'draft') {
            $html .= '<span class="page-status draft">Черновик</span>';
        }
        
        $html .= '</a>';
        $html .= '</div>';
        
        if ($hasChildren) {
            $html .= '<div class="accordion-content">';
            $html .= renderAccordionLevel($page['children'], $settings, $expandAll);
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    
    return $html;
}

$renderSettings = [
    'showIcons' => $showIcons,
    'showStatus' => $showStatus,
    'currentPageId' => $currentPageId
];
?>

<div class="page-tree-accordion">
    <?php if ($showHome) { ?>
        <div class="accordion-item">
            <div class="accordion-header">
                <?php if ($showIcons) { ?>
                    <span class="accordion-icon"><?php echo bloggy_icon('bs', 'chevron-right', '14', 'currentColor'); ?></span>
                <?php } ?>
                <a href="<?php echo $homeUrl; ?>" class="accordion-link<?php echo (empty($currentPageId)) ? ' active' : ''; ?>">
                    <?php if ($showIcons) { ?>
                        <span class="item-icon"><?php echo bloggy_icon('bs', 'house-door', '16', 'currentColor'); ?></span>
                    <?php } ?>
                    <span class="item-title"><?php echo html($homeTitle); ?></span>
                </a>
            </div>
        </div>
    <?php } ?>
    
    <?php echo renderAccordionLevel($pageTree, $renderSettings, $expandAll); ?>
</div>