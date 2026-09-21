<?php /** Habr Pro - Password Post - 0 radius */ ?>
<div style="max-width:440px;margin:40px auto;padding:0 20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="border:1px solid #000;background:#fff;padding:20px">
    <div style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a8a8a;margin-bottom:8px">protected</div>
    <h1 style="font-size:18px;font-weight:800;margin:0 0 6px;letter-spacing:-.02em"><?= LANG_TEMPLATE_POST_PASSWORD_TITLE ?></h1>
    <div style="font-size:12px;color:#6c6c6c;margin-bottom:12px"><span style="text-transform:uppercase;letter-spacing:.06em;font-size:10px"><?= LANG_TEMPLATE_POST_PASSWORD_RESTRICTED_LABEL ?></span> <strong style="color:#000"><?= html($post['title']) ?></strong></div>
    <?php if(!empty($post['short_description'])){ ?><p style="font-size:12px;color:#6c6c6c;line-height:1.5;margin:0 0 12px"><?= html($post['short_description']) ?></p><?php } ?>
    <?php if($error){ ?><div style="border:1px solid #000;border-left:3px solid #000;background:#f5f5f5;padding:8px 10px;font-size:11px;margin-bottom:12px;font-weight:700"><?= LANG_TEMPLATE_POST_PASSWORD_ERROR_TITLE ?> — <?= LANG_TEMPLATE_POST_PASSWORD_ERROR_TEXT ?></div><?php } ?>
    <form method="post" action="<?= BASE_URL ?>/post/check-password/<?= $post['id'] ?>" style="display:flex;flex-direction:column;gap:10px">
      <input type="hidden" name="redirect" value="<?= BASE_URL ?>/post/<?= html($post['slug']) ?>">
      <div><label for="password" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_POST_PASSWORD_LABEL ?></label><input type="password" id="password" name="password" placeholder="<?= LANG_TEMPLATE_POST_PASSWORD_PLACEHOLDER ?>" required autofocus style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px;border-radius:0"></div>
      <div style="font-size:11px;color:#8a8a8a"><?= LANG_TEMPLATE_POST_PASSWORD_HINT ?></div>
      <button type="submit" style="padding:8px 16px;background:#000;color:#fff;border:1px solid #000;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= LANG_TEMPLATE_POST_PASSWORD_UNLOCK_BTN ?></button>
      <a href="<?= BASE_URL ?>/posts" style="font-size:11px;color:#6c6c6c;text-decoration:none;text-align:center;text-transform:uppercase;letter-spacing:.04em;font-weight:700">← <?= LANG_TEMPLATE_POST_PASSWORD_BACK_BTN ?></a>
    </form>
  </div>
  <div style="text-align:center;margin-top:12px;font-size:11px;color:#8a8a8a"><?= LANG_TEMPLATE_POST_PASSWORD_HELP_TEXT ?></div>
</div>
