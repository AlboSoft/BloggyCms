<?php
/**
 * Footer Menu - Habr Pro - минималистичный
 */
$currentUrl = $_SERVER['REQUEST_URI'];
?>
<ul class="footer-menu-list">
    <?php foreach ($menuItems as $item) { 
        $processedUrl = MenuRenderer::processUrl($item['url'] ?? '');
        $hasChildren = !empty($item['children']);
        $isActive = MenuRenderer::isActiveUrl($processedUrl, $currentUrl);
        $title = html($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $target = $item['target'] ?? '_self';
        $iconOnly = !empty($item['icon_only']);
        $iconHtml = '';
        if (!empty($item['icon']) && is_array($item['icon']) && !empty($item['icon']['id'])) {
            $iconHtml = bloggy_icon($item['icon']['set'] ?? 'bs', $item['icon']['id'], "12 12", $item['icon']['color'] ?? 'currentColor', 'footer-menu-icon');
        }
        $liClasses = ['footer-menu-item'];
        if ($hasChildren) $liClasses[] = 'has-children';
        if ($isActive) $liClasses[] = 'active';
        $itemUrl = html($processedUrl, ENT_QUOTES, 'UTF-8');
    ?>
        <li class="<?php echo implode(' ', $liClasses); ?>">
            <?php if ($hasChildren) { ?>
                <span class="footer-menu-link footer-menu-parent" style="font-weight:700"><?php if ($iconHtml) echo $iconHtml; ?><?php if (!$iconOnly) { ?><span class="footer-menu-title"><?php echo $title; ?></span><?php } ?></span>
                <ul class="footer-submenu" style="margin-top:6px;display:flex;flex-direction:column;gap:4px">
                    <?php foreach ($item['children'] as $child) {
                        $childProcessedUrl = MenuRenderer::processUrl($child['url'] ?? '');
                        $childTitle = html($child['title'] ?? '', ENT_QUOTES, 'UTF-8');
                        $childUrl = html($childProcessedUrl, ENT_QUOTES, 'UTF-8');
                    ?>
                        <li class="footer-submenu-item"><a href="<?php echo $childUrl; ?>" class="footer-submenu-link"><?php echo $childTitle; ?></a></li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <a href="<?php echo $itemUrl; ?>" class="footer-menu-link <?php echo $isActive ? 'active' : ''; ?>" target="<?php echo $target; ?>"><?php if ($iconHtml) echo $iconHtml; ?><?php if (!$iconOnly) { ?><span class="footer-menu-title"><?php echo $title; ?></span><?php } ?></a>
            <?php } ?>
        </li>
    <?php } ?>
</ul>
