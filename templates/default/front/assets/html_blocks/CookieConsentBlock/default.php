<?php
/**
 * Habr Pro - Cookie Consent - flat 0 radius
 */
$message = nl2br(html($settings['message'] ?? ''));
$acceptText = html($settings['accept_button_text'] ?? 'Принять');
$declineText = html($settings['decline_button_text'] ?? 'Отклонить');
$policyLinkText = html($settings['policy_link_text'] ?? 'Политика конфиденциальности');
$policyUrl = html($settings['policy_url'] ?? '/privacy');
$showPolicyLink = !empty($settings['show_policy_link']);
$position = $settings['position'] ?? 'bottom';
$cookieName = $settings['cookie_name'] ?? 'cookie_consent';
$autoShow = !empty($settings['auto_show']);
$cookieExpiryDays = (int)($settings['cookie_expiry_days'] ?? 365);
$customId = !empty($settings['custom_id']) ? html($settings['custom_id']) : 'cookie-consent';
$customClass = !empty($settings['custom_css_class']) ? ' '.html($settings['custom_css_class']) : '';
?>
<div id="<?= $customId ?>" class="cookie-consent-container<?= $customClass ?>" style="position:fixed;z-index:10000;left:0;right:0;display:flex;justify-content:center;padding:12px 20px;background:#000;color:#fff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:12px;line-height:1.5;transform:translateY(100%);transition:transform .2s;<?= $position==='bottom'?'bottom:0':'top:0;bottom:auto;transform:translateY(-100%)' ?>" data-cookie-name="<?= $cookieName ?>" data-cookie-expiry="<?= $cookieExpiryDays ?>" data-auto-show="<?= $autoShow?'1':'0' ?>" data-position="<?= $position ?>">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;max-width:1220px;width:100%;flex-wrap:wrap">
    <div style="flex:1;min-width:200px">
      <?= $message ?>
      <?php if($showPolicyLink && $policyUrl && $policyLinkText){ ?><a href="<?= $policyUrl ?>" target="_blank" style="color:#fff;text-decoration:underline;margin-left:8px;font-weight:700"><?= $policyLinkText ?></a><?php } ?>
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0">
      <button type="button" class="cookie-accept-btn" style="padding:6px 14px;background:#fff;color:#000;border:1px solid #fff;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= $acceptText ?></button>
      <button type="button" class="cookie-decline-btn" style="padding:6px 14px;background:transparent;color:#fff;border:1px solid #555;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= $declineText ?></button>
    </div>
  </div>
</div>
<script>
(function(){
  var el=document.getElementById('<?= $customId ?>');
  if(!el) return;
  var name=el.dataset.cookieName, exp=parseInt(el.dataset.cookieExpiry)||365, auto=el.dataset.autoShow==='1', pos=el.dataset.position||'bottom';
  function getC(n){var m=document.cookie.match(new RegExp('(?:^|; )'+n.replace(/([.$?*|{}()\[\]\\\/\+^])/g,'\\$1')+'=([^;]*)'));return m?decodeURIComponent(m[1]):null}
  function setC(n,v,d){var e=new Date();e.setDate(e.getDate()+d);document.cookie=n+'='+encodeURIComponent(v)+'; expires='+e.toUTCString()+'; path=/';}
  if(getC(name)) return;
  function show(){el.style.transform='translateY(0)';}
  if(auto) setTimeout(show,500); else show();
  el.querySelector('.cookie-accept-btn').addEventListener('click',function(){setC(name,'accepted',exp);el.style.transform=pos==='bottom'?'translateY(100%)':'translateY(-100%)';});
  el.querySelector('.cookie-decline-btn').addEventListener('click',function(){setC(name,'declined',exp);el.style.transform=pos==='bottom'?'translateY(100%)':'translateY(-100%)';});
})();
</script>
