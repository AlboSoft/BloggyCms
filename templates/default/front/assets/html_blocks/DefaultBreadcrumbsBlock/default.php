<?php
/**
* Хлебные крошки
*/

$items = $settings['items'] ?? [];
$separator = $settings['separator_char'] ?? '›';
$containerClass = $settings['container_class'] ?? 'tg-breadcrumbs';
$schemaMarkup = $settings['schema_markup'] ?? '';
$homeIcon = $settings['home_icon'] ?? '';

if (empty($items)) {
    return;
}
?>

<?php if (!empty($containerClass)) { ?>
<div class="<?= html($containerClass ?? '') ?>" style="--breadcrumb-separator: '<?= html($separator ?? '') ?>';">
<?php } ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            
            <?php foreach ($items as $index => $item) { 
                $isLast = $index === array_key_last($items);
                $isHome = $index === 0;
            ?>
            
            <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>" 
                itemprop="itemListElement" 
                itemscope 
                itemtype="https://schema.org/ListItem">
                
                <?php if (!$isLast && !empty($item['url'])) { ?>
                    
                    <a href="<?= html($item['url'] ?? '') ?>" itemprop="item">
                        
                        <?php if ($isHome && !empty($homeIcon)) { 
                            $iconParts = explode(':', $homeIcon);
                            $iconSet = $iconParts[0] ?? 'bs';
                            $iconName = $iconParts[1] ?? 'house-door';
                            echo bloggy_icon($iconSet, $iconName, '16 16', 'currentColor', 'me-1 breadcrumb-svg');
                        } ?>
                        
                        <span itemprop="name"><?= html($item['title'] ?? '') ?></span>
                        
                    </a>
                    
                <?php } else { ?>
                    
                    <span itemprop="name">
                        <?= html($item['title'] ?? '') ?>
                    </span>
                    
                <?php } ?>
                
                <meta itemprop="position" content="<?= $index + 1 ?>">
                
            </li>
            
            <?php } ?>
            
        </ol>
    </nav>
    
    <?php if (!empty($schemaMarkup)) { ?>
        <?= $schemaMarkup ?>
    <?php } ?>

<?php if (!empty($containerClass)) { ?>
</div>
<?php } ?>