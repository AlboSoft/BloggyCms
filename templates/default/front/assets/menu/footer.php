<?php
/**
* Footer Menu Template
* Вертикальное меню для подвала сайта
*/

$currentUrl = $_SERVER['REQUEST_URI'];
?>

<ul class="footer-menu-list">
    <?php foreach ($menuItems as $item) { ?>
        <?php
        $processedUrl = MenuRenderer::processUrl($item['url'] ?? '');
        $hasChildren = !empty($item['children']);
        $isActive = MenuRenderer::isActiveUrl($processedUrl, $currentUrl);
        
        $title = html($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $target = $item['target'] ?? '_self';
        $itemClass = $item['class'] ?? '';
        $iconOnly = !empty($item['icon_only']);
        
        $iconHtml = '';
        if (!empty($item['icon']) && is_array($item['icon']) && !empty($item['icon']['id'])) {
            $iconSet = $item['icon']['set'] ?? 'bs';
            $iconId = $item['icon']['id'];
            $iconSize = !empty($item['icon']['size']) ? $item['icon']['size'] : 16;
            $iconColor = !empty($item['icon']['color']) ? $item['icon']['color'] : 'currentColor';
            
            $iconHtml = bloggy_icon($iconSet, $iconId, "$iconSize $iconSize", $iconColor, 'footer-menu-icon');
        }
        
        $liClasses = ['footer-menu-item'];
        if ($hasChildren) $liClasses[] = 'has-children';
        if ($isActive) $liClasses[] = 'active';
        if (!empty($itemClass)) $liClasses[] = html($itemClass, ENT_QUOTES, 'UTF-8');
        
        $itemUrl = html($processedUrl, ENT_QUOTES, 'UTF-8');
        ?>
        
        <li class="<?php echo implode(' ', $liClasses); ?>">
            <?php if ($hasChildren) { ?>
                <button type="button" 
                        class="footer-menu-link footer-menu-parent" 
                        aria-expanded="false"
                        aria-haspopup="true">
                    <?php if ($iconHtml) echo $iconHtml; ?>
                    <?php if (!$iconOnly) { ?>
                        <span class="footer-menu-title"><?php echo $title; ?></span>
                    <?php } else { ?>
                        <span class="visually-hidden"><?php echo $title; ?></span>
                    <?php } ?>
                    <?php echo bloggy_icon('bs', 'chevron-down', '14 14', 'currentColor', 'footer-menu-arrow'); ?>
                </button>
                
                <ul class="footer-submenu">
                    <?php foreach ($item['children'] as $child) { ?>
                        <?php
                        $childProcessedUrl = MenuRenderer::processUrl($child['url'] ?? '');
                        $childHasChildren = !empty($child['children']);
                        $childIsActive = MenuRenderer::isActiveUrl($childProcessedUrl, $currentUrl);
                        
                        $childTitle = html($child['title'] ?? '', ENT_QUOTES, 'UTF-8');
                        $childTarget = $child['target'] ?? '_self';
                        $childClass = $child['class'] ?? '';
                        $childIconOnly = !empty($child['icon_only']);
                        
                        $childIconHtml = '';
                        if (!empty($child['icon']) && is_array($child['icon']) && !empty($child['icon']['id'])) {
                            $childIconSet = $child['icon']['set'] ?? 'bs';
                            $childIconId = $child['icon']['id'];
                            $childIconSize = !empty($child['icon']['size']) ? $child['icon']['size'] : 14;
                            $childIconColor = !empty($child['icon']['color']) ? $child['icon']['color'] : 'currentColor';
                            
                            $childIconHtml = bloggy_icon($childIconSet, $childIconId, "$childIconSize $childIconSize", $childIconColor, 'footer-submenu-icon');
                        }
                        
                        $childClasses = ['footer-submenu-item'];
                        if ($childHasChildren) $childClasses[] = 'has-children';
                        if ($childIsActive) $childClasses[] = 'active';
                        if (!empty($childClass)) $childClasses[] = html($childClass, ENT_QUOTES, 'UTF-8');
                        
                        $childUrl = html($childProcessedUrl, ENT_QUOTES, 'UTF-8');
                        ?>
                        
                        <li class="<?php echo implode(' ', $childClasses); ?>">
                            <?php if ($childHasChildren) { ?>
                                <button type="button" 
                                        class="footer-submenu-link footer-menu-parent"
                                        aria-expanded="false">
                                    <?php if ($childIconHtml) echo $childIconHtml; ?>
                                    <?php if (!$childIconOnly) { ?>
                                        <span class="footer-menu-title"><?php echo $childTitle; ?></span>
                                    <?php } else { ?>
                                        <span class="visually-hidden"><?php echo $childTitle; ?></span>
                                    <?php } ?>
                                    <?php echo bloggy_icon('bs', 'chevron-right', '12 12', 'currentColor', 'footer-menu-arrow'); ?>
                                </button>
                                
                                <ul class="footer-submenu footer-submenu-nested">
                                    <?php foreach ($child['children'] as $subchild) { ?>
                                        <?php
                                        $subchildProcessedUrl = MenuRenderer::processUrl($subchild['url'] ?? '');
                                        $subchildIsActive = MenuRenderer::isActiveUrl($subchildProcessedUrl, $currentUrl);
                                        
                                        $subchildTitle = html($subchild['title'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $subchildTarget = $subchild['target'] ?? '_self';
                                        $subchildClass = $subchild['class'] ?? '';
                                        $subchildIconOnly = !empty($subchild['icon_only']);
                                        
                                        $subchildClasses = ['footer-submenu-item'];
                                        if ($subchildIsActive) $subchildClasses[] = 'active';
                                        if (!empty($subchildClass)) $subchildClasses[] = html($subchildClass, ENT_QUOTES, 'UTF-8');
                                        
                                        $subchildUrl = html($subchildProcessedUrl, ENT_QUOTES, 'UTF-8');
                                        ?>
                                        
                                        <li class="<?php echo implode(' ', $subchildClasses); ?>">
                                            <a href="<?php echo $subchildUrl; ?>" 
                                               class="footer-submenu-link <?php echo $subchildIsActive ? 'active' : ''; ?>"
                                               target="<?php echo $subchildTarget; ?>">
                                                <?php if (!$subchildIconOnly) { ?>
                                                    <span class="footer-menu-title"><?php echo $subchildTitle; ?></span>
                                                <?php } else { ?>
                                                    <span class="visually-hidden"><?php echo $subchildTitle; ?></span>
                                                <?php } ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                                
                            <?php } else { ?>
                                <a href="<?php echo $childUrl; ?>" 
                                   class="footer-submenu-link <?php echo $childIsActive ? 'active' : ''; ?>"
                                   target="<?php echo $childTarget; ?>">
                                    <?php if ($childIconHtml) echo $childIconHtml; ?>
                                    <?php if (!$childIconOnly) { ?>
                                        <span class="footer-menu-title"><?php echo $childTitle; ?></span>
                                    <?php } else { ?>
                                        <span class="visually-hidden"><?php echo $childTitle; ?></span>
                                    <?php } ?>
                                </a>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
                
            <?php } else { ?>
                <a href="<?php echo $itemUrl; ?>" 
                   class="footer-menu-link <?php echo $isActive ? 'active' : ''; ?>"
                   target="<?php echo $target; ?>">
                    <?php if ($iconHtml) echo $iconHtml; ?>
                    <?php if (!$iconOnly) { ?>
                        <span class="footer-menu-title"><?php echo $title; ?></span>
                    <?php } else { ?>
                        <span class="visually-hidden"><?php echo $title; ?></span>
                    <?php } ?>
                </a>
            <?php } ?>
        </li>
    <?php } ?>
</ul>