<?php /** Habr Pro - Age Check - 0 radius */ ?>
<div class="tg-age-page" style="max-width:480px;margin:40px auto;padding:0 20px">
  <div style="border:1px solid #000;background:#fff;padding:24px">
    <div style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a8a8a;margin-bottom:12px">18+ — <?= html($min_age ?? 18) ?>+</div>
    <h1 style="font-size:18px;font-weight:800;letter-spacing:-.02em;margin:0 0 8px;text-transform:uppercase"><?= LANG_TEMPLATE_AGE_CHECK_TITLE ?></h1>
    <p style="font-size:13px;color:#6c6c6c;line-height:1.5;margin:0 0 16px"><?= sprintf(LANG_TEMPLATE_AGE_CHECK_TEXT, $min_age) ?></p>
    <?php if(isset($error)){ ?><div style="border:1px solid #000;border-left:3px solid #000;background:#f5f5f5;padding:8px 10px;font-size:12px;margin-bottom:12px"><?= html($error) ?></div><?php } ?>
    <form method="POST" style="display:flex;flex-direction:column;gap:12px">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
        <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_AGE_CHECK_DAY ?></label><select name="day" required style="width:100%;padding:8px;border:1px solid #e8e8e8;font-size:13px;border-radius:0;background:#fff"><option value="">--</option><?php for($i=1;$i<=31;$i++){ ?><option value="<?= $i ?>"><?= $i ?></option><?php } ?></select></div>
        <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_AGE_CHECK_MONTH ?></label><select name="month" required style="width:100%;padding:8px;border:1px solid #e8e8e8;font-size:13px;border-radius:0;background:#fff"><option value="">--</option><?php for($i=1;$i<=12;$i++){ ?><option value="<?= $i ?>"><?= $i ?></option><?php } ?></select></div>
        <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_AGE_CHECK_YEAR ?></label><select name="year" required style="width:100%;padding:8px;border:1px solid #e8e8e8;font-size:13px;border-radius:0;background:#fff"><option value="">----</option><?php for($i=date('Y');$i>=date('Y')-100;$i--){ ?><option value="<?= $i ?>"><?= $i ?></option><?php } ?></select></div>
      </div>
      <button type="submit" style="padding:8px 16px;background:#000;color:#fff;border:1px solid #000;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= LANG_TEMPLATE_AGE_CHECK_BTN ?></button>
    </form>
    <div style="margin-top:16px;padding-top:12px;border-top:1px solid #e8e8e8"><a href="<?= BASE_URL ?>" style="font-size:11px;color:#6c6c6c;text-decoration:none;text-transform:uppercase;letter-spacing:.04em;font-weight:700">← <?= LANG_TEMPLATE_AGE_CHECK_BACK ?></a></div>
  </div>
</div>
