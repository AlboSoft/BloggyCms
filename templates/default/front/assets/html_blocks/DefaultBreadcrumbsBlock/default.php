<?php
/**
* Habr Pro - Breadcrumbs - 0 radius flat
*/
$items = $settings['items'] ?? [];
$separator = $settings['separator_char'] ?? '/';
$containerClass = $settings['container_class'] ?? 'tg-breadcrumbs';
if(empty($items)) return;
?>
<div class="<?= html($containerClass) ?>" style="background:#fff;border-bottom:1px solid #e8e8e8;padding:8px 0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="max-width:1220px;margin:0 auto;padding:0 20px">
    <nav aria-label="breadcrumb">
      <ol style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;list-style:none;margin:0;padding:0" itemscope itemtype="https://schema.org/BreadcrumbList">
        <?php foreach($items as $i=>$item){
          $isLast = $i===array_key_last($items);
          $isHome = $i===0;
        ?>
        <li style="display:inline-flex;align-items:center;gap:6px;font-size:11px" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
          <?php if(!$isLast && !empty($item['url'])){ ?>
            <a href="<?= html($item['url']) ?>" itemprop="item" style="color:#6c6c6c;text-decoration:none;text-transform:uppercase;letter-spacing:.04em;font-weight:600;font-size:11px"><span itemprop="name"><?= html($item['title']) ?></span></a>
            <span style="color:#d4d4d4;font-size:10px"><?= html($separator) ?></span>
          <?php }else{ ?>
            <span itemprop="name" style="color:#000;font-weight:700;text-transform:uppercase;letter-spacing:.04em;font-size:11px"><?= html($item['title']) ?></span>
          <?php } ?>
          <meta itemprop="position" content="<?= $i+1 ?>">
        </li>
        <?php } ?>
      </ol>
    </nav>
  </div>
</div>
