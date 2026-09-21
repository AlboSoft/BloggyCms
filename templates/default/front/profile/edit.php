<?php /** Habr Pro - Profile Edit - flat 0 radius */ ?>
<div style="max-width:900px;margin:0 auto;padding:20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="border:1px solid #000;background:#fff;padding:0">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #000;background:#000;color:#fff">
      <h1 style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin:0"><?= LANG_TEMPLATE_PROFILE_EDIT_TITLE ?></h1>
      <a href="<?= BASE_URL ?>/profile/<?= html($user['username']) ?>" style="font-size:11px;color:#fff;text-decoration:none;border:1px solid #333;padding:4px 8px">← <?= LANG_TEMPLATE_PROFILE_EDIT_BACK_BTN ?></a>
    </div>
    <div style="padding:16px">
      <?php if(isset($_SESSION['error_message'])){ ?><div style="border:1px solid #000;border-left:3px solid #000;background:#f5f5f5;padding:8px 10px;font-size:11px;margin-bottom:12px"><?= html($_SESSION['error_message']) ?></div><?php unset($_SESSION['error_message']); } ?>
      <?php if(isset($_SESSION['success_message'])){ ?><div style="border:1px solid #000;background:#f5f5f5;padding:8px 10px;font-size:11px;margin-bottom:12px"><?= html($_SESSION['success_message']) ?></div><?php unset($_SESSION['success_message']); } ?>
      <div style="display:flex;gap:0;border:1px solid #e8e8e8;margin-bottom:16px">
        <button class="profile-tab active" data-tab="profile-info" style="flex:1;padding:8px;background:#000;color:#fff;border:0;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= LANG_TEMPLATE_PROFILE_EDIT_INFO_TAB ?></button>
        <button class="profile-tab" data-tab="profile-password" style="flex:1;padding:8px;background:#fff;color:#000;border:0;border-left:1px solid #e8e8e8;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= LANG_TEMPLATE_PROFILE_EDIT_PASSWORD_TAB ?></button>
        <button class="profile-tab" data-tab="profile-sessions" style="flex:1;padding:8px;background:#fff;color:#000;border:0;border-left:1px solid #e8e8e8;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= LANG_TEMPLATE_PROFILE_EDIT_SESSIONS_TAB ?></button>
        <button class="profile-tab" data-tab="profile-additional" style="flex:1;padding:8px;background:#fff;color:#000;border:0;border-left:1px solid #e8e8e8;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= LANG_TEMPLATE_PROFILE_EDIT_ADDITIONAL_TAB ?></button>
      </div>
      <form method="POST" action="<?= BASE_URL ?>/profile/update" enctype="multipart/form-data" id="profile-form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="action_type" id="action_type" value="update_profile">
        <div class="profile-pane" id="profile-info" style="display:block">
          <div style="display:grid;grid-template-columns:160px 1fr;gap:20px">
            <div style="text-align:center">
              <div style="width:120px;height:120px;border:1px solid #e8e8e8;background:#f5f5f5;margin:0 auto;overflow:hidden;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:32px">
                <?php if(!empty($user['avatar'])&&$user['avatar']!=='default.jpg'){ ?><img src="<?= BASE_URL ?>/uploads/avatars/<?= html($user['avatar']) ?>" alt="" id="avatar-preview" style="width:100%;height:100%;object-fit:cover"><?php }else{ ?><div id="avatar-preview"><?= strtoupper(substr($user['username'],0,1)) ?></div><?php } ?>
              </div>
              <label style="display:inline-block;margin-top:8px;padding:5px 10px;border:1px solid #000;background:#fff;font-size:10px;font-weight:700;text-transform:uppercase;cursor:pointer"><?= LANG_TEMPLATE_PROFILE_EDIT_UPLOAD_AVATAR_BTN ?><input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none" onchange="previewAvatar(this)"></label>
              <div style="font-size:10px;color:#8a8a8a;margin-top:6px"><?= LANG_TEMPLATE_PROFILE_EDIT_AVATAR_HINT ?></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px">
              <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_USERNAME_LABEL ?></label><input type="text" value="<?= html($user['username']) ?>" disabled style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;background:#f5f5f5;font-size:13px"></div>
              <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_DISPLAY_NAME_LABEL ?></label><input type="text" name="display_name" value="<?= html($user['display_name']??'') ?>" placeholder="<?= LANG_TEMPLATE_PROFILE_EDIT_DISPLAY_NAME_PLACEHOLDER ?>" style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px"></div>
              <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_EMAIL_LABEL ?> *</label><input type="email" name="email" value="<?= html($user['email']) ?>" required style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px"></div>
              <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_WEBSITE_LABEL ?></label><input type="url" name="website" value="<?= html($user['website']??'') ?>" placeholder="https://example.com" style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px"></div>
              <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_BIO_LABEL ?></label><textarea name="bio" rows="4" placeholder="<?= LANG_TEMPLATE_PROFILE_EDIT_BIO_PLACEHOLDER ?>" style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px;resize:vertical"><?= html($user['bio']??'') ?></textarea></div>
              <?php if(!empty($customFields)){ ?><div style="border-top:1px solid #e8e8e8;padding-top:12px;margin-top:4px"><div style="font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:8px"><?= LANG_TEMPLATE_PROFILE_EDIT_ADDITIONAL_INFO_TITLE ?></div><?php foreach($customFields as $field){ $config=json_decode($field['config']??'{}',true); $isRequired=(bool)$field['is_required']; ?><div style="margin-bottom:10px"><label style="font-size:10px;font-weight:700;text-transform:uppercase;display:block;margin-bottom:4px"><?= html($field['name']) ?><?= $isRequired?' *':'' ?></label><?php echo $fieldManager->renderFieldInput($field['type'],$field['system_name'],$field['value'],$config,'user',$user['id']); ?><?php if(!empty($field['description'])){ ?><div style="font-size:10px;color:#8a8a8a;margin-top:2px"><?= html($field['description']) ?></div><?php } ?></div><?php } ?></div><?php } ?>
            </div>
          </div>
        </div>
        <div class="profile-pane" id="profile-password" style="display:none">
          <div style="border:1px solid #e8e8e8;background:#f5f5f5;padding:8px 10px;font-size:11px;margin-bottom:12px"><?= LANG_TEMPLATE_PROFILE_EDIT_PASSWORD_INFO ?></div>
          <div style="display:flex;flex-direction:column;gap:10px;max-width:480px">
            <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_CURRENT_PASSWORD_LABEL ?></label><input type="password" name="current_password" autocomplete="current-password" style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
              <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_NEW_PASSWORD_LABEL ?></label><input type="password" name="new_password" id="new_password" autocomplete="new-password" style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px"></div>
              <div><label style="font-size:10px;font-weight:700;text-transform:uppercase;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_CONFIRM_PASSWORD_LABEL ?></label><input type="password" id="confirm_password" autocomplete="new-password" style="width:100%;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px"></div>
            </div>
          </div>
        </div>
        <div class="profile-pane" id="profile-sessions" style="display:none">
          <div style="border:1px solid #e8e8e8;background:#f5f5f5;padding:8px 10px;font-size:11px;margin-bottom:12px"><?= LANG_TEMPLATE_PROFILE_EDIT_SESSIONS_INFO ?></div>
          <div id="sessions-list" style="border:1px solid #e8e8e8;padding:12px;text-align:center;font-size:11px;color:#8a8a8a"><?= LANG_TEMPLATE_PROFILE_EDIT_LOADING_SESSIONS ?></div>
          <button type="button" id="terminate-all-sessions" style="margin-top:10px;padding:6px 12px;background:#fff;border:1px solid #000;color:#000;font-size:11px;font-weight:700;text-transform:uppercase;cursor:pointer"><?= LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ALL_BTN ?></button>
        </div>
        <div class="profile-pane" id="profile-additional" style="display:none">
          <div style="border:1px solid #000;border-left:3px solid #000;background:#f5f5f5;padding:8px 10px;font-size:11px;margin-bottom:12px"><strong><?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_WARNING_TITLE ?></strong> <?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_WARNING_TEXT ?></div>
          <div style="border:1px solid #000;padding:12px">
            <div style="font-size:12px;font-weight:800;text-transform:uppercase;margin-bottom:8px"><?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_ACCOUNT_TITLE ?></div>
            <p style="font-size:11px;color:#6c6c6c"><?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_DESCRIPTION ?></p>
            <div style="margin:12px 0"><label style="display:flex;gap:6px;font-size:11px"><input type="checkbox" id="confirm-delete" style="accent-color:#000"> <?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_CONFIRM_LABEL ?></label></div>
            <div style="margin-bottom:10px"><label style="font-size:10px;font-weight:700;text-transform:uppercase;display:block;margin-bottom:4px"><?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_PASSWORD_LABEL ?></label><input type="password" id="delete-password" placeholder="<?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_PASSWORD_PLACEHOLDER ?>" style="width:100%;max-width:320px;padding:8px 10px;border:1px solid #e8e8e8;font-size:13px"></div>
            <button type="button" id="delete-account-btn" disabled style="padding:6px 12px;background:#000;color:#fff;border:1px solid #000;font-size:11px;font-weight:700;text-transform:uppercase;opacity:.5;cursor:not-allowed"><?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_ACCOUNT_BTN ?></button>
          </div>
        </div>
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid #e8e8e8;display:flex;gap:8px">
          <button type="submit" id="submit-btn" style="padding:8px 16px;background:#000;color:#fff;border:1px solid #000;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= LANG_TEMPLATE_PROFILE_EDIT_SAVE_BTN ?></button>
          <a href="<?= BASE_URL ?>/profile/<?= html($user['username']) ?>" style="padding:8px 16px;background:#fff;color:#000;border:1px solid #e8e8e8;font-size:11px;font-weight:700;text-transform:uppercase;text-decoration:none"><?= LANG_TEMPLATE_PROFILE_EDIT_CANCEL_BTN ?></a>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function previewAvatar(input){
  if(input.files&&input.files[0]){
    var r=new FileReader();
    r.onload=function(e){
      var p=document.getElementById('avatar-preview');
      if(p.tagName==='IMG'){p.src=e.target.result;}else{var img=document.createElement('img');img.src=e.target.result;img.id='avatar-preview';img.style.width='100%';img.style.height='100%';img.style.objectFit='cover';p.parentNode.replaceChild(img,p);}
    };r.readAsDataURL(input.files[0]);
  }
}
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.profile-tab').forEach(btn=>{
    btn.addEventListener('click',function(){
      var tab=this.dataset.tab;
      document.querySelectorAll('.profile-tab').forEach(b=>{b.style.background='#fff';b.style.color='#000';b.classList.remove('active');});
      this.style.background='#000';this.style.color='#fff';this.classList.add('active');
      document.querySelectorAll('.profile-pane').forEach(p=>p.style.display='none');
      document.getElementById(tab).style.display='block';
    });
  });
  var np=document.getElementById('new_password'), cp=document.getElementById('confirm_password');
  if(np&&cp){
    function vp(){if(np.value!==cp.value){cp.style.borderColor='#000';}else{cp.style.borderColor='#e8e8e8';}}
    np.addEventListener('input',vp); cp.addEventListener('input',vp);
  }
  function loadSessions(){
    fetch('<?= BASE_URL ?>/profile/sessions').then(r=>r.json()).then(data=>{
      if(data.success){
        var c=document.getElementById('sessions-list'); if(!data.sessions||!data.sessions.length){c.innerHTML='<div style="padding:20px;text-align:center"><?= LANG_TEMPLATE_PROFILE_EDIT_NO_SESSIONS ?></div>';return;}
        var h=''; data.sessions.forEach(s=>{
          var cur=s.is_current;
          h+='<div style="display:flex;justify-content:space-between;align-items:center;padding:8px;border-bottom:1px solid #f0f0f0;text-align:left"><div><div style="font-size:11px;font-weight:700">'+(s.device||'<?= LANG_TEMPLATE_PROFILE_EDIT_UNKNOWN_DEVICE ?>')+(cur?' <span style="background:#000;color:#fff;padding:1px 4px;font-size:9px"><?= LANG_TEMPLATE_PROFILE_EDIT_CURRENT_BADGE ?></span>':'')+'</div><div style="font-size:10px;color:#8a8a8a">IP: '+s.ip+' · '+s.last_activity+'</div></div>'+(!cur?'<button type="button" class="terminate-session" data-session-id="'+s.id+'" style="padding:4px 8px;background:#fff;border:1px solid #e8e8e8;font-size:10px;cursor:pointer"><?= LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_BTN ?></button>':'')+'</div>';
        }); c.innerHTML=h; c.style.textAlign='left';
        document.querySelectorAll('.terminate-session').forEach(b=>{b.addEventListener('click',function(){var sid=this.dataset.sessionId; if(confirm('<?= LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_CONFIRM ?>')){terminateSession(sid);}});});
      }
    }).catch(()=>{document.getElementById('sessions-list').innerHTML='<div style="padding:12px;background:#f5f5f5;border:1px solid #e8e8e8"><?= LANG_TEMPLATE_PROFILE_EDIT_SESSIONS_LOAD_ERROR ?></div>';});
  }
  function terminateSession(id){
    fetch('<?= BASE_URL ?>/profile/terminate-session',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({session_id:id,csrf_token:'<?= $csrf_token ?>'})}).then(r=>r.json()).then(d=>{if(d.success) loadSessions(); else alert(d.message||'<?= LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ERROR ?>');}).catch(()=>alert('<?= LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ERROR ?>'));
  }
  document.getElementById('terminate-all-sessions')?.addEventListener('click',function(){if(confirm('<?= LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ALL_CONFIRM ?>')){fetch('<?= BASE_URL ?>/profile/terminate-all-sessions',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf_token:'<?= $csrf_token ?>'})}).then(r=>r.json()).then(d=>{if(d.success) loadSessions(); else alert(d.message||'<?= LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ALL_ERROR ?>');}).catch(()=>alert('<?= LANG_TEMPLATE_PROFILE_EDIT_TERMINATE_ALL_ERROR ?>'));}});
  var dc=document.getElementById('confirm-delete'), dp=document.getElementById('delete-password'), db=document.getElementById('delete-account-btn');
  if(dc&&dp&&db){
    function checkDel(){var ok=dc.checked&&dp.value.length>0; db.disabled=!ok; db.style.opacity=ok?'1':'.5'; db.style.cursor=ok?'pointer':'not-allowed';}
    dc.addEventListener('change',checkDel); dp.addEventListener('input',checkDel);
    db.addEventListener('click',function(){if(confirm('<?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_FINAL_CONFIRM ?>')){fetch('<?= BASE_URL ?>/profile/delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({password:dp.value,csrf_token:'<?= $csrf_token ?>'})}).then(r=>r.json()).then(d=>{if(d.success){alert('<?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_SUCCESS ?>');window.location.href='<?= BASE_URL ?>';}else alert(d.message||'<?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_ERROR ?>');}).catch(()=>alert('<?= LANG_TEMPLATE_PROFILE_EDIT_DELETE_ERROR ?>'));}});
  }
  loadSessions();
});
</script>
