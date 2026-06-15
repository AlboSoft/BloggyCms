<?php
/**
* Дерево
*/

$showIcons = $settings['show_icons'] ?? true;
$showStatus = $settings['show_page_status'] ?? true;
$showDescriptions = $settings['show_page_descriptions'] ?? false;
$showHome = $settings['show_home'] ?? true;
$homeTitle = $settings['home_title'] ?? 'Главная';
$homeUrl = $settings['home_url'] ?? BASE_URL;
$currentPageId = $settings['current_page_id'] ?? null;
$pageTree = $settings['page_tree'] ?? [];

function renderTreeItems($items, $showIcons, $showStatus, $showDescriptions, $currentPageId, $level = 0) {
    if (empty($items)) {
        return '';
    }
    
    $html = '';
    $indent = $level * 24;
    
    foreach ($items as $item) {
        $isActive = ($currentPageId == $item['id']);
        $hasChildren = !empty($item['children']);
        $itemUrl = BASE_URL . '/page/' . $item['slug'];
        
        $html .= '<li class="tree-item" style="margin-left: ' . $indent . 'px;">';
        $html .= '<a href="' . $itemUrl . '" class="tree-link' . ($isActive ? ' active' : '') . '">';
        
        if ($showIcons) {
            $html .= '<span class="tree-icon">';
            if ($hasChildren) {
                $html .= bloggy_icon('bs', 'folder', '16', 'currentColor');
            } else {
                $html .= bloggy_icon('bs', 'file-text', '16', 'currentColor');
            }
            $html .= '</span>';
        }
        
        $html .= '<div class="tree-info">';
        $html .= '<div class="tree-title">' . html($item['title']);
        
        if ($hasChildren) {
            $childCount = count($item['children']);
            $html .= '<span class="children-count">(' . $childCount . ')</span>';
        }
        
        if ($showStatus && ($item['status'] ?? '') === 'draft') {
            $html .= '<span class="page-status draft">Черновик</span>';
        }
        
        $html .= '</div>';
        
        if ($showDescriptions && !empty($item['description'])) {
            $html .= '<div class="tree-description">' . html(mb_substr($item['description'], 0, 100)) . '</div>';
        }
        
        $html .= '</div>';
        $html .= '</a>';
        
        if ($hasChildren) {
            $html .= renderTreeItems($item['children'], $showIcons, $showStatus, $showDescriptions, $currentPageId, $level + 1);
        }
        
        $html .= '</li>';
    }
    
    return $html;
}
?>

<div class="page-tree-wrapper">
    
    <?php if ($showHome) { ?>
        <ul class="page-tree-tree">
            <li class="tree-item" style="margin-left: 0px;">
                <a href="<?php echo $homeUrl; ?>" class="tree-link<?php echo (empty($currentPageId)) ? ' active' : ''; ?>">
                    <?php if ($showIcons) { ?>
                        <span class="tree-icon"><?php echo bloggy_icon('bs', 'house-door', '16', 'currentColor'); ?></span>
                    <?php } ?>
                    <div class="tree-info">
                        <div class="tree-title"><?php echo html($homeTitle); ?></div>
                    </div>
                </a>
            </li>
        </ul>
    <?php } ?>
    
    <ul class="page-tree-tree">
        <?php echo renderTreeItems($pageTree, $showIcons, $showStatus, $showDescriptions, $currentPageId, 0); ?>
    </ul>
    
</div>