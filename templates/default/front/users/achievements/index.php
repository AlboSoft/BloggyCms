<?php /** Habr Pro - Achievements Index - flat */ $currentUserId=$_SESSION['user_id']??null; ?>
<div style="max-width:1220px;margin:0 auto;padding:20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="border:1px solid #000;padding:16px;margin-bottom:20px;background:#fff">
    <div style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a8a8a;margin-bottom:6px">achievements / <?= $totalAchievements??0 ?></div>
    <h1 style="font-size:22px;font-weight:800;margin:0;letter-spacing:-.02em"><?= LANG_TEMPLATE_ACHIEVEMENTS_SYSTEM_TITLE ?></h1>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border:1px solid #e8e8e8;margin-top:12px">
      <div style="padding:10px;border-right:1px solid #e8e8e8"><div style="font-size:18px;font-weight:800"><?= $totalAchievements??0 ?></div><div style="font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a"><?= LANG_TEMPLATE_ACHIEVEMENTS_STATS_TOTAL ?></div></div>
      <div style="padding:10px;border-right:1px solid #e8e8e8"><div style="font-size:18px;font-weight:800"><?= (int)($totalUsers??0) ?></div><div style="font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a"><?= plural((int)($totalUsers??0),[LANG_TEMPLATE_ACHIEVEMENTS_USER_1,LANG_TEMPLATE_ACHIEVEMENTS_USER_2,LANG_TEMPLATE_ACHIEVEMENTS_USER_3]) ?></div></div>
      <div style="padding:10px"><div style="font-size:18px;font-weight:800"><?= $totalUnlockedAchievements??0 ?></div><div style="font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a"><?= LANG_TEMPLATE_ACHIEVEMENTS_STATS_UNLOCKED ?></div></div>
    </div>
  </div>
  <div style="display:grid;gap:0;border:1px solid #e8e8e8">
    <?php if(!empty($achievements)){ foreach($achievements as $a){ $isUnlocked=isset($userAchievements[$a['id']])&&$userAchievements[$a['id']]; ?>
      <div style="display:grid;grid-template-columns:80px 1fr 160px;gap:16px;padding:14px;border-bottom:1px solid #f0f0f0;background:#fff">
        <div style="text-align:center">
          <?php if(!empty($a['image'])){ ?><img src="<?= BASE_URL ?>/uploads/achievements/<?= html($a['image']) ?>" alt="<?= html($a['name']) ?>" style="width:56px;height:56px;border:1px solid #e8e8e8;object-fit:cover"><?php }else{ ?><div style="width:56px;height:56px;border:1px solid #000;background:#000;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;margin:0 auto"><?= strtoupper(substr($a['name'],0,1)) ?></div><?php } ?>
          <?php if($isUnlocked){ ?><div style="margin-top:6px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;background:#000;color:#fff;padding:2px 6px;display:inline-block"><?= LANG_TEMPLATE_ACHIEVEMENTS_UNLOCKED_BADGE ?></div><?php } ?>
        </div>
        <div>
          <h3 style="font-size:14px;font-weight:700;margin:0 0 4px"><a href="<?= BASE_URL ?>/achievement/<?= $a['id'] ?>" style="color:#000;text-decoration:none"><?= html($a['name']) ?></a></h3>
          <p style="font-size:12px;color:#6c6c6c;margin:0 0 8px;line-height:1.4"><?= html($a['description']) ?></p>
          <?php if(!empty($a['formatted_conditions'])){ ?><div style="font-size:10px;color:#8a8a8a;text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:4px"><?= LANG_TEMPLATE_ACHIEVEMENTS_CONDITIONS_TITLE ?></div><ul style="margin:0;padding:0;list-style:none"><?php foreach($a['formatted_conditions'] as $c){ ?><li style="font-size:11px;color:#6c6c6c;margin-bottom:2px">— <?= html($c) ?></li><?php } ?></ul><?php } ?>
        </div>
        <div style="border-left:1px solid #f0f0f0;padding-left:12px">
          <div style="text-align:center;margin-bottom:8px"><div style="font-size:18px;font-weight:800"><?= $a['unlocked_count'] ?></div><div style="font-size:10px;text-transform:uppercase;color:#8a8a8a"><?= LANG_TEMPLATE_ACHIEVEMENTS_UNLOCKED_STATS ?></div></div>
          <div style="text-align:center;margin-bottom:8px"><div style="font-size:16px;font-weight:800"><?= $a['percent'] ?>%</div><div style="font-size:10px;text-transform:uppercase;color:#8a8a8a"><?= LANG_TEMPLATE_ACHIEVEMENTS_PERCENT_STATS ?></div></div>
          <div style="height:3px;background:#e8e8e8;margin-bottom:10px"><div style="height:100%;background:#000;width:<?= $a['percent'] ?>%"></div></div>
          <a href="<?= BASE_URL ?>/achievement/<?= $a['id'] ?>" style="display:block;text-align:center;padding:5px;background:#fff;border:1px solid #000;color:#000;font-size:10px;font-weight:700;text-transform:uppercase;text-decoration:none"><?= LANG_TEMPLATE_ACHIEVEMENTS_DETAILS_BTN ?></a>
        </div>
      </div>
    <?php } }else{ ?><div style="padding:24px;text-align:center"><div style="font-size:12px;font-weight:700;text-transform:uppercase"><?= LANG_TEMPLATE_ACHIEVEMENTS_EMPTY_TITLE ?></div><p style="font-size:13px;color:#6c6c6c"><?= LANG_TEMPLATE_ACHIEVEMENTS_EMPTY_TEXT ?></p></div><?php } ?>
  </div>
</div>
