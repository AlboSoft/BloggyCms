<?php
/** Habr Pro - Archive - flat 0 radius */
$monthNames = [1=>LANG_TEMPLATE_ARCHIVE_MONTH_JANUARY,2=>LANG_TEMPLATE_ARCHIVE_MONTH_FEBRUARY,3=>LANG_TEMPLATE_ARCHIVE_MONTH_MARCH,4=>LANG_TEMPLATE_ARCHIVE_MONTH_APRIL,5=>LANG_TEMPLATE_ARCHIVE_MONTH_MAY,6=>LANG_TEMPLATE_ARCHIVE_MONTH_JUNE,7=>LANG_TEMPLATE_ARCHIVE_MONTH_JULY,8=>LANG_TEMPLATE_ARCHIVE_MONTH_AUGUST,9=>LANG_TEMPLATE_ARCHIVE_MONTH_SEPTEMBER,10=>LANG_TEMPLATE_ARCHIVE_MONTH_OCTOBER,11=>LANG_TEMPLATE_ARCHIVE_MONTH_NOVEMBER,12=>LANG_TEMPLATE_ARCHIVE_MONTH_DECEMBER];
$totalYears = count($postsByMonth ?? []);
$totalMonths=0;$totalPosts=0;$firstYear=null;$lastYear=null;
if(!empty($postsByMonth)){
  $years=array_keys($postsByMonth);
  if(!empty($years)){$firstYear=min($years);$lastYear=max($years);}
  foreach($postsByMonth as $yr=>$mos){$totalMonths+=count($mos);foreach($mos as $mo=>$ps){$totalPosts+=count($ps);}}
}
?>
<div style="max-width:1220px;margin:0 auto;padding:20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #000">
    <div>
      <div style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a8a8a;margin-bottom:6px">archive / <?= $totalPosts ?> posts</div>
      <h1 style="font-size:28px;font-weight:800;letter-spacing:-.03em;margin:0"><?= LANG_TEMPLATE_ARCHIVE_TITLE ?></h1>
      <p style="font-size:13px;color:#6c6c6c;margin:6px 0 0"><?= LANG_TEMPLATE_ARCHIVE_SUBTITLE ?></p>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid #e8e8e8;margin-bottom:20px">
    <div style="padding:12px;border-right:1px solid #e8e8e8"><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a"><?= LANG_TEMPLATE_ARCHIVE_STATS_PERIOD_LABEL ?></div><div style="font-size:14px;font-weight:800;margin-top:4px"><?= $firstYear&&$lastYear?"$firstYear — $lastYear":LANG_TEMPLATE_ARCHIVE_STATS_NO_DATA ?></div></div>
    <div style="padding:12px;border-right:1px solid #e8e8e8"><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a"><?= LANG_TEMPLATE_ARCHIVE_STATS_YEARS_LABEL ?></div><div style="font-size:14px;font-weight:800;margin-top:4px"><?= $totalYears ?></div></div>
    <div style="padding:12px;border-right:1px solid #e8e8e8"><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a"><?= LANG_TEMPLATE_ARCHIVE_STATS_MONTHS_LABEL ?></div><div style="font-size:14px;font-weight:800;margin-top:4px"><?= $totalMonths ?></div></div>
    <div style="padding:12px"><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#8a8a8a"><?= LANG_TEMPLATE_ARCHIVE_STATS_POSTS_LABEL ?></div><div style="font-size:14px;font-weight:800;margin-top:4px"><?= $totalPosts ?></div></div>
  </div>
  <?php if(!empty($postsByMonth)){ ?>
    <?php foreach($postsByMonth as $year=>$months){
      $yearCount=0;foreach($months as $m=>$ps){$yearCount+=count($ps);}
    ?>
    <div style="margin-bottom:24px">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #e8e8e8">
        <h2 style="font-size:18px;font-weight:800;margin:0"><?= $year ?> <?= LANG_TEMPLATE_ARCHIVE_YEAR_SUFFIX ?></h2>
        <span style="font-size:11px;color:#6c6c6c;background:#f5f5f5;border:1px solid #e8e8e8;padding:2px 6px"><?= $yearCount ?> <?= plural_form($yearCount,[LANG_TEMPLATE_ARCHIVE_POST_1,LANG_TEMPLATE_ARCHIVE_POST_2,LANG_TEMPLATE_ARCHIVE_POST_3]) ?></span>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:0;border:1px solid #e8e8e8">
        <?php foreach($months as $month=>$posts){
          if(empty($posts)) continue;
          $monthName=$monthNames[$month]??LANG_TEMPLATE_ARCHIVE_MONTH_DEFAULT;
        ?>
        <div style="border-right:1px solid #e8e8e8;border-bottom:1px solid #e8e8e8;background:#fff">
          <div style="padding:10px 12px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em"><?= $monthName ?></span>
            <span style="font-size:11px;color:#8a8a8a;border:1px solid #e8e8e8;padding:1px 5px;background:#f5f5f5"><?= count($posts) ?></span>
          </div>
          <div style="padding:0">
            <?php foreach($posts as $idx=>$post){ ?>
            <div style="display:flex;gap:8px;padding:8px 12px;border-bottom:1px solid #f5f5f5;<?= $idx>=3?'display:none':'' ?>" class="<?= $idx>=3?'tg-hidden':'' ?> archive-post-item" data-month="<?= $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT) ?>">
              <div style="font-size:10px;color:#8a8a8a;min-width:32px"><?= date('d',strtotime($post['created_at'])) ?> <?= mb_substr($monthNames[date('n',strtotime($post['created_at']))],0,3,'UTF-8') ?></div>
              <div style="flex:1;min-width:0"><a href="<?= BASE_URL ?>/post/<?= html($post['slug']) ?>" style="font-size:12px;font-weight:600;color:#000;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= html($post['title']) ?></a><div style="font-size:10px;color:#8a8a8a;margin-top:2px"><?= html($post['category_name']??'') ?> · <?= $post['views']??0 ?> views</div></div>
            </div>
            <?php } ?>
          </div>
          <?php if(count($posts)>3){ ?><button style="width:100%;padding:6px;background:#f5f5f5;border:0;border-top:1px solid #e8e8e8;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;cursor:pointer" class="tg-show-more-btn" data-month="<?= $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT) ?>"><?= sprintf(LANG_TEMPLATE_ARCHIVE_SHOW_ALL,count($posts)) ?> ▾</button><?php } ?>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
  <?php }else{ ?>
    <div style="border:1px solid #e8e8e8;padding:32px;text-align:center"><div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px"><?= LANG_TEMPLATE_ARCHIVE_EMPTY_TITLE ?></div><p style="font-size:13px;color:#6c6c6c"><?= LANG_TEMPLATE_ARCHIVE_EMPTY_TEXT ?></p><div style="margin-top:12px;display:flex;gap:8px;justify-content:center"><a href="<?= BASE_URL ?>/posts" style="padding:6px 12px;background:#000;color:#fff;border:1px solid #000;font-size:11px;font-weight:700;text-transform:uppercase;text-decoration:none"><?= LANG_TEMPLATE_ARCHIVE_EMPTY_ALL_POSTS_BTN ?></a><a href="<?= BASE_URL ?>/categories" style="padding:6px 12px;background:#fff;color:#000;border:1px solid #e8e8e8;font-size:11px;font-weight:700;text-transform:uppercase;text-decoration:none"><?= LANG_TEMPLATE_ARCHIVE_EMPTY_CATEGORIES_BTN ?></a></div></div>
  <?php } ?>
</div>
<script>
document.querySelectorAll('.tg-show-more-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    var m=btn.dataset.month;
    document.querySelectorAll('.archive-post-item[data-month="'+m+'"]').forEach(el=>el.style.display='flex');
    btn.style.display='none';
  });
});
</script>
