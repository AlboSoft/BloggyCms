<?php /** Habr Pro - Users - flat 0 radius */
$totalPosts=0;$isAdmin=isset($_SESSION['is_admin'])&&$_SESSION['is_admin'];
if(!empty($users)){foreach($users as $u){$totalPosts+=$u['posts_count']??0;}}
?>
<div style="max-width:1220px;margin:0 auto;padding:20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #000">
    <div>
      <div style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a8a8a;margin-bottom:6px"><?= (int)($online_count??0) ?> online / <?= (int)($total_users??0) ?> members</div>
      <h1 style="font-size:22px;font-weight:800;margin:0;letter-spacing:-.02em"><?= LANG_TEMPLATE_USERS_TITLE ?></h1>
    </div>
    <?php if($isAdmin){ ?><div style="font-size:12px;color:#6c6c6c;border:1px solid #e8e8e8;padding:6px 10px;background:#f5f5f5"><strong style="color:#000"><?= (int)($totalPosts??0) ?></strong> posts</div><?php } ?>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:0;border:1px solid #e8e8e8">
    <?php if(!empty($users)){ foreach($users as $user){ ?>
      <div style="padding:14px;border-right:1px solid #e8e8e8;border-bottom:1px solid #e8e8e8;background:#fff;display:flex;flex-direction:column">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
          <a href="<?= BASE_URL ?>/profile/<?= html($user['username']) ?>" style="position:relative;display:block;width:40px;height:40px;border:1px solid #e8e8e8;background:#f5f5f5;overflow:hidden;text-align:center;line-height:38px;font-weight:800;font-size:14px;color:#000;text-decoration:none">
            <?php if(!empty($user['avatar'])&&$user['avatar']!=='default.jpg'){ ?><img src="<?= BASE_URL ?>/uploads/avatars/<?= html($user['avatar']) ?>" alt="<?= html($user['display_name']??$user['username']) ?>" style="width:100%;height:100%;object-fit:cover"><?php }else{ ?><?= strtoupper(substr($user['username'],0,1)) ?><?php } ?>
            <?php if($user['is_online']??false){ ?><span style="position:absolute;bottom:-2px;right:-2px;width:8px;height:8px;background:#000;border:1px solid #fff;display:block"></span><?php } ?>
          </a>
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;line-height:1.2"><a href="<?= BASE_URL ?>/profile/<?= html($user['username']) ?>" style="color:#000;text-decoration:none"><?= html($user['display_name']??$user['username']) ?></a></div>
            <div style="font-size:11px;color:#8a8a8a">@<?= html($user['username']) ?></div>
          </div>
        </div>
        <?php if(!empty($user['groups'])){ ?><div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:8px"><?php foreach(array_slice($user['groups'],0,2) as $g){ ?><span style="font-size:10px;background:#f5f5f5;border:1px solid #e8e8e8;padding:2px 6px;text-transform:uppercase;letter-spacing:.04em;font-weight:600"><?= html($g['name']) ?></span><?php } ?><?php if(count($user['groups'])>2){ ?><span style="font-size:10px;color:#8a8a8a">+<?= count($user['groups'])-2 ?></span><?php } ?></div><?php } ?>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border:1px solid #e8e8e8;margin-bottom:8px;text-align:center">
          <div style="padding:6px;border-right:1px solid #e8e8e8"><div style="font-size:13px;font-weight:800"><?= (int)($user['posts_count']??0) ?></div><div style="font-size:9px;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a">posts</div></div>
          <div style="padding:6px;border-right:1px solid #e8e8e8"><div style="font-size:13px;font-weight:800"><?= $user['comments_count']??0 ?></div><div style="font-size:9px;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a">comments</div></div>
          <div style="padding:6px"><div style="font-size:13px;font-weight:800"><?= (int)($user['unlocked_achievements_count']??0) ?></div><div style="font-size:9px;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a">achiev</div></div>
        </div>
        <?php if(!empty($user['bio'])){ ?><p style="font-size:11px;color:#6c6c6c;line-height:1.4;margin:0 0 8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= html(truncate_text(strip_tags($user['bio']),80)) ?></p><?php } ?>
        <div style="margin-top:auto;padding-top:8px;border-top:1px solid #f0f0f0;display:flex;justify-content:space-between;font-size:10px;color:#8a8a8a">
          <?php if($user['is_online']??false){ ?><span style="color:#000;font-weight:700">● online</span><?php }elseif(!empty($user['last_activity_human'])){ ?><span><?= sprintf(LANG_TEMPLATE_USERS_WAS_ONLINE,$user['last_activity_human']) ?></span><?php } ?>
          <?php if(!empty($user['registration_days'])){ ?><span><?= sprintf(LANG_TEMPLATE_USERS_DAYS,$user['registration_days']) ?></span><?php } ?>
        </div>
        <a href="<?= BASE_URL ?>/profile/<?= html($user['username']) ?>" style="display:block;text-align:center;margin-top:10px;padding:6px;background:#000;color:#fff;border:1px solid #000;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;text-decoration:none">profile</a>
      </div>
    <?php } }else{ ?>
      <div style="grid-column:1/-1;padding:32px;text-align:center;border:1px solid #e8e8e8"><div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px"><?= LANG_TEMPLATE_USERS_EMPTY_TITLE ?></div><p style="font-size:13px;color:#6c6c6c"><?= LANG_TEMPLATE_USERS_EMPTY_TEXT ?></p></div>
    <?php } ?>
  </div>
  <?php if(isset($pagination)&&$pagination['total_pages']>1){ ?>
    <div style="display:flex;gap:4px;justify-content:center;margin-top:20px">
      <?php if($pagination['current_page']>1){ ?><a href="?page=<?= $pagination['current_page']-1 ?>" style="padding:6px 10px;border:1px solid #e8e8e8;background:#fff;color:#000;text-decoration:none;font-size:12px">‹</a><?php } ?>
      <?php for($i=1;$i<=$pagination['total_pages'];$i++){ ?><a href="?page=<?= $i ?>" style="padding:6px 10px;border:1px solid <?= $i==$pagination['current_page']?'#000':'#e8e8e8' ?>;background:<?= $i==$pagination['current_page']?'#000':'#fff' ?>;color:<?= $i==$pagination['current_page']?'#fff':'#000' ?>;text-decoration:none;font-size:12px;font-weight:700"><?= $i ?></a><?php } ?>
      <?php if($pagination['current_page']<$pagination['total_pages']){ ?><a href="?page=<?= $pagination['current_page']+1 ?>" style="padding:6px 10px;border:1px solid #e8e8e8;background:#fff;color:#000;text-decoration:none;font-size:12px">›</a><?php } ?>
    </div>
  <?php } ?>
</div>
