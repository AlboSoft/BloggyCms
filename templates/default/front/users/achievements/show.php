<?php /** Habr Pro - Achievement Detail - flat */ $currentUserId=$_SESSION['user_id']??null; ?>
<div style="max-width:1220px;margin:0 auto;padding:20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="display:grid;grid-template-columns:280px 1fr;gap:20px">
    <div>
      <div style="border:1px solid #000;background:#fff;padding:16px;margin-bottom:12px;text-align:center">
        <?php if(!empty($achievement['image'])){ ?><img src="<?= BASE_URL ?>/uploads/achievements/<?= html($achievement['image']) ?>" alt="<?= html($achievement['name']) ?>" style="width:80px;height:80px;border:1px solid #e8e8e8;object-fit:cover;margin:0 auto;display:block"><?php }else{ ?><div style="width:80px;height:80px;background:#000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:800;margin:0 auto"><?= strtoupper(substr($achievement['name'],0,1)) ?></div><?php } ?>
        <?php if($userHasAchievement){ ?><div style="margin-top:10px;padding:4px 8px;background:#000;color:#fff;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:inline-block"><?= LANG_TEMPLATE_ACHIEVEMENT_DETAIL_YOU_HAVE ?></div><?php } ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid #e8e8e8;margin-top:12px">
          <div style="padding:8px;border-right:1px solid #e8e8e8;text-align:center"><div style="font-size:16px;font-weight:800"><?= (int)($achievement['unlocked_count']??0) ?></div><div style="font-size:9px;text-transform:uppercase;color:#8a8a8a">users</div></div>
          <div style="padding:8px;text-align:center"><div style="font-size:16px;font-weight:800"><?= $achievement['percent'] ?>%</div><div style="font-size:9px;text-transform:uppercase;color:#8a8a8a">percent</div></div>
        </div>
        <div style="height:3px;background:#e8e8e8;margin-top:8px"><div style="height:100%;background:#000;width:<?= $achievement['percent'] ?>%"></div></div>
      </div>
      <?php if(!empty($achievement['formatted_conditions'])){ ?><div style="border:1px solid #e8e8e8;background:#fff;padding:12px"><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px"><?= LANG_TEMPLATE_ACHIEVEMENT_DETAIL_CONDITIONS_TITLE ?></div><ul style="margin:0;padding:0;list-style:none"><?php foreach($achievement['formatted_conditions'] as $c){ ?><li style="font-size:11px;color:#6c6c6c;margin-bottom:4px">— <?= html($c) ?></li><?php } ?></ul></div><?php } ?>
    </div>
    <div>
      <div style="border:1px solid #000;background:#fff;padding:16px;margin-bottom:12px">
        <h1 style="font-size:20px;font-weight:800;margin:0 0 8px;letter-spacing:-.02em"><?= html($achievement['name']) ?></h1>
        <p style="font-size:13px;color:#6c6c6c;line-height:1.5;margin:0 0 12px"><?= html($achievement['description']) ?></p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:11px;color:#8a8a8a;border-top:1px solid #f0f0f0;padding-top:10px">
          <span><strong style="color:#000;text-transform:uppercase;font-size:10px"><?= LANG_TEMPLATE_ACHIEVEMENT_DETAIL_TYPE_LABEL ?></strong> <?= $achievement['type']=='auto'?LANG_TEMPLATE_ACHIEVEMENT_DETAIL_TYPE_AUTO:LANG_TEMPLATE_ACHIEVEMENT_DETAIL_TYPE_MANUAL ?></span>
          <span><strong style="color:#000;text-transform:uppercase;font-size:10px"><?= LANG_TEMPLATE_ACHIEVEMENT_DETAIL_CREATED_LABEL ?></strong> <?= date('d.m.Y',strtotime($achievement['created_at'])) ?></span>
          <span><strong style="color:#000;text-transform:uppercase;font-size:10px"><?= LANG_TEMPLATE_ACHIEVEMENT_DETAIL_PRIORITY_LABEL ?></strong> <?= $achievement['priority'] ?></span>
        </div>
      </div>
      <div style="border:1px solid #e8e8e8;background:#fff;padding:12px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #e8e8e8"><h3 style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin:0"><?= LANG_TEMPLATE_ACHIEVEMENT_DETAIL_USERS_TITLE ?></h3><span style="font-size:11px;background:#f5f5f5;border:1px solid #e8e8e8;padding:2px 6px"><?= $pagination['total']??0 ?></span></div>
        <?php if(!empty($users)){ ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0;border:1px solid #e8e8e8">
            <?php foreach($users as $user){ ?>
              <div style="padding:10px;border-right:1px solid #f0f0f0;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:8px">
                <a href="<?= BASE_URL ?>/profile/<?= html($user['username']) ?>" style="width:32px;height:32px;border:1px solid #e8e8e8;background:#f5f5f5;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;color:#000;text-decoration:none;overflow:hidden"><?php if(!empty($user['avatar'])&&$user['avatar']!=='default.jpg'){ ?><img src="<?= BASE_URL ?>/uploads/avatars/<?= html($user['avatar']) ?>" style="width:100%;height:100%;object-fit:cover"><?php }else{ ?><?= strtoupper(substr($user['username'],0,1)) ?><?php } ?></a>
                <div style="flex:1;min-width:0"><div style="font-size:11px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><a href="<?= BASE_URL ?>/profile/<?= html($user['username']) ?>" style="color:#000;text-decoration:none"><?= html($user['display_name']??$user['username']) ?></a></div><div style="font-size:10px;color:#8a8a8a">@<?= html($user['username']) ?> · <?= date('d.m.Y',strtotime($user['unlocked_at'])) ?></div></div>
              </div>
            <?php } ?>
          </div>
        <?php }else{ ?><div style="padding:20px;text-align:center;font-size:12px;color:#6c6c6c"><?= LANG_TEMPLATE_ACHIEVEMENT_DETAIL_NO_USERS_TEXT ?></div><?php } ?>
      </div>
    </div>
  </div>
</div>
