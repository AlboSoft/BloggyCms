<?php /** Habr Pro - Login - flat 0 radius */ ?>
<div style="max-width:400px;margin:40px auto;padding:0 20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="border:1px solid #000;background:#fff;padding:20px">
    <div style="margin-bottom:16px"><div style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a8a8a;margin-bottom:6px">auth / login</div><h1 style="font-size:20px;font-weight:800;margin:0;letter-spacing:-.02em"><?= LANG_TEMPLATE_AUTH_LOGIN_TITLE ?></h1><p style="font-size:12px;color:#6c6c6c;margin:6px 0 0"><?= LANG_TEMPLATE_AUTH_LOGIN_SUBTITLE ?></p></div>
    <?php if(isset($currentAttempts)&&$currentAttempts>0){ ?><div style="border:1px solid #000;border-left:3px solid #000;background:#f5f5f5;padding:8px 10px;font-size:11px;margin-bottom:12px"><strong><?= LANG_TEMPLATE_AUTH_LOGIN_ATTEMPT ?></strong> <?= sprintf(LANG_TEMPLATE_AUTH_LOGIN_ATTEMPT_COUNT,$currentAttempts,$maxAttempts) ?><?php if($currentAttempts>=$maxAttempts-1){ ?><div style="margin-top:4px;color:#6c6c6c"><?= LANG_TEMPLATE_AUTH_LOGIN_ATTEMPT_WARNING ?></div><?php } ?></div><?php } ?>
    <form method="post" action="" style="display:flex;flex-direction:column;gap:10px">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      <div><label for="email" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_AUTH_EMAIL_LABEL ?></label><input type="email" id="email" name="email" placeholder="<?= LANG_TEMPLATE_AUTH_EMAIL_PLACEHOLDER ?>" required value="<?= html($email??'') ?>" autofocus style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px;border-radius:0"></div>
      <div><label for="password" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_AUTH_PASSWORD_LABEL ?></label><input type="password" id="password" name="password" placeholder="<?= LANG_TEMPLATE_AUTH_PASSWORD_PLACEHOLDER ?>" required style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px;border-radius:0"></div>
      <label style="display:flex;align-items:center;gap:6px;font-size:11px;cursor:pointer"><input type="checkbox" name="remember_me" id="remember_me" style="accent-color:#000"> <?= LANG_TEMPLATE_AUTH_REMEMBER_ME_LABEL ?></label>
      <button type="submit" style="padding:8px 16px;background:#000;color:#fff;border:1px solid #000;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.04em;cursor:pointer;width:100%"><?= LANG_TEMPLATE_AUTH_LOGIN_BTN ?></button>
    </form>
    <div style="margin-top:16px;padding-top:12px;border-top:1px solid #e8e8e8;display:flex;gap:12px;flex-wrap:wrap;font-size:11px">
      <a href="<?= BASE_URL ?>/register" style="color:#000;text-decoration:none;font-weight:700;text-transform:uppercase;letter-spacing:.04em"><?= LANG_TEMPLATE_AUTH_CREATE_ACCOUNT_LINK ?></a>
      <?php if(!$disable_restore){ ?><span style="color:#d4d4d4">/</span><a href="<?= BASE_URL ?>/forgot-password" style="color:#6c6c6c;text-decoration:none;font-weight:600"><?= LANG_TEMPLATE_AUTH_FORGOT_PASSWORD_LINK ?></a><?php } ?>
    </div>
    <div style="margin-top:12px;font-size:10px;color:#8a8a8a;text-transform:uppercase;letter-spacing:.06em"><?= LANG_TEMPLATE_AUTH_SECURE_LOGIN ?></div>
  </div>
</div>
