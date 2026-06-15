<?php
/**
* Основное меню
* Основное меню для шаблона хэдера
*/
$currentUrl = $_SERVER['REQUEST_URI'];
?>

<ul class="menu">
<?php foreach ($menuItems as $item) { ?>
<?php
    $url = MenuRenderer::processUrl($item['url'] ?? '');
    $hasChildren = !empty($item['children']);
    $isActive = MenuRenderer::isActiveUrl($url, $currentUrl);
    $title = html($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
    $target = $item['target'] ?? '_self';
    $iconOnly = !empty($item['icon_only']);
    
    $iconHtml = '';
    if (!empty($item['icon']) && is_array($item['icon']) && !empty($item['icon']['id'])) {
        $iconHtml = bloggy_icon($item['icon']['set'] ?? 'bs', $item['icon']['id'], '18 18', $item['icon']['color'] ?? 'currentColor', 'menu-icon');
    }
    $classes = ['menu-item'];
    if ($hasChildren) $classes[] = 'has-children';
    if ($isActive) $classes[] = 'active';
?>
<li class="<?= implode(' ', $classes) ?>">
    <?php if ($hasChildren) { ?>
    <a href="#" role="button" aria-expanded="false" data-submenu-toggle>
        <?= $iconHtml ?>
        <?php if (!$iconOnly) { ?>
            <span class="menu-title"><?= $title ?></span>
        <?php } else { ?>
            <span class="visually-hidden"><?= $title ?></span>
        <?php } ?>
        <?= bloggy_icon('bs', 'chevron-down', '16 16', 'currentColor', 'menu-arrow') ?>
    </a>
    <ul class="submenu">
        <?php foreach ($item['children'] as $child) { ?>
            <?php
                $childUrl = MenuRenderer::processUrl($child['url'] ?? '');
                $childHasChildren = !empty($child['children']);
                $childActive = MenuRenderer::isActiveUrl($childUrl, $currentUrl);
                $childTitle = html($child['title'] ?? '', ENT_QUOTES, 'UTF-8');
                $childIconOnly = !empty($child['icon_only']);
                $childIcon = !empty($child['icon']) && is_array($child['icon']) ? bloggy_icon($child['icon']['set'] ?? 'bs', $child['icon']['id'], '16 16', $child['icon']['color'] ?? 'currentColor', 'submenu-icon') : '';
                $childClasses = ['submenu-item'];
                if ($childHasChildren) $childClasses[] = 'has-children';
                if ($childActive) $childClasses[] = 'active';
            ?>
            <li class="<?= implode(' ', $childClasses) ?>">
                <?php if ($childHasChildren) { ?>
                <a href="#" role="button" aria-expanded="false" data-submenu-toggle>
                    <?= $childIcon ?>
                    <?php if (!$childIconOnly) { ?>
                        <span class="menu-title"><?= $childTitle ?></span>
                    <?php } else { ?>
                        <span class="visually-hidden"><?= $childTitle ?></span>
                    <?php } ?>
                    <?= bloggy_icon('bs', 'chevron-right', '14 14', 'currentColor', 'menu-arrow') ?>
                </a>
                <ul class="submenu">
                <?php foreach ($child['children'] as $subchild) { ?>
                <?php
                    $subUrl = MenuRenderer::processUrl($subchild['url'] ?? '');
                    $subActive = MenuRenderer::isActiveUrl($subUrl, $currentUrl);
                    $subTitle = html($subchild['title'] ?? '', ENT_QUOTES, 'UTF-8');
                    $subIconOnly = !empty($subchild['icon_only']);
                    $subIcon = !empty($subchild['icon']) && is_array($subchild['icon']) ? bloggy_icon($subchild['icon']['set'] ?? 'bs', $subchild['icon']['id'], '16 16', $subchild['icon']['color'] ?? 'currentColor', 'submenu-icon') : '';
                ?>
                <li class="submenu-item <?= $subActive ? 'active' : '' ?>">
                    <a href="<?= html($subUrl, ENT_QUOTES, 'UTF-8') ?>" target="<?= $subchild['target'] ?? '_self' ?>">
                        <?= $subIcon ?>
                        <?php if (!$subIconOnly) { ?>
                            <span class="menu-title"><?= $subTitle ?></span>
                        <?php } else { ?>
                            <span class="visually-hidden"><?= $subTitle ?></span>
                        <?php } ?>
                    </a>
                </li>
                <?php } ?>
                </ul>
                <?php } else { ?>
                <a href="<?= html($childUrl, ENT_QUOTES, 'UTF-8') ?>" target="<?= $child['target'] ?? '_self' ?>">
                    <?= $childIcon ?>
                    <?php if (!$childIconOnly) { ?>
                        <span class="menu-title"><?= $childTitle ?></span>
                    <?php } else { ?>
                        <span class="visually-hidden"><?= $childTitle ?></span>
                    <?php } ?>
                </a>
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
    <?php } else { ?>
        <a href="<?= html($url, ENT_QUOTES, 'UTF-8') ?>" target="<?= $target ?>">
            <?= $iconHtml ?>
            <?php if (!$iconOnly) { ?>
                <span class="menu-title"><?= $title ?></span>
            <?php } else { ?>
                <span class="visually-hidden"><?= $title ?></span>
            <?php } ?>
        </a>
    <?php } ?>
</li>
<?php } ?>
</ul>