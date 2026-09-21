<?php /** Habr Pro - Feedback Default Form - flat */ ?>
<form action="<?= html($actionUrl) ?>" method="POST" class="feedback-form" enctype="multipart/form-data" id="feedback-form-<?= html($formSlug) ?>" data-ajax="true" style="display:flex;flex-direction:column;gap:0">
  <input type="hidden" name="form_id" value="<?= html($formId) ?>">
  <input type="hidden" name="form_slug" value="<?= html($formSlug) ?>">
  <?php if(!empty($csrfToken)): ?><input type="hidden" name="csrf_token" value="<?= html($csrfToken) ?>"><?php endif; ?>
  <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0;border:1px solid #e8e8e8">
    <?php foreach($structure as $field): ?>
      <?php if($field['type']==='hidden'): ?><input type="hidden" name="<?= html($field['name']) ?>" value="<?= html($field['default_value']??'') ?>">
      <?php elseif($field['type']==='submit'): ?>
        <?php if(!empty($captchaHtml)): ?><div style="grid-column:1/-1;padding:10px;border-top:1px solid #e8e8e8"><?= $captchaHtml ?></div><?php endif; ?>
        <div style="grid-column:1/-1;padding:0"><button type="submit" style="width:100%;padding:10px;background:#000;color:#fff;border:0;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= html($field['label']??'Отправить') ?></button></div>
      <?php else: $col=$field['type']==='textarea'?'1/-1':'auto'; $value=html($field['default_value']??''); $ph=html($field['placeholder']??''); $req=!empty($field['required'])?'required':''; ?>
        <div style="grid-column:<?= $col ?>;border-right:1px solid #e8e8e8;border-bottom:1px solid #e8e8e8;padding:0">
          <?php if($field['type']==='textarea'){ ?><textarea name="<?= html($field['name']) ?>" placeholder="<?= $ph ?>" <?= $req ?> rows="4" style="width:100%;border:0;padding:10px;font-size:13px;resize:vertical;border-radius:0"><?= $value ?></textarea><?php }else{ ?><input type="<?= html($field['type']) ?>" name="<?= html($field['name']) ?>" placeholder="<?= $ph ?>" value="<?= $value ?>" <?= $req ?> style="width:100%;border:0;padding:10px;font-size:13px;border-radius:0"><?php } ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</form>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var form=document.getElementById('feedback-form-<?= html($formSlug) ?>'); if(!form) return;
  form.addEventListener('submit',function(e){
    e.preventDefault(); var btn=form.querySelector('button[type="submit"]'); var orig=btn.innerHTML; btn.disabled=true; btn.innerHTML='Отправка...';
    var fd=new FormData(form);
    fetch(form.action,{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
      if(data.success){form.innerHTML='<div style="border:1px solid #000;background:#f5f5f5;padding:10px;font-size:12px">'+data.message+'</div>'; if(data.redirect) setTimeout(()=>window.location.href=data.redirect,2000);}
      else{if(data.message.indexOf('токен')!==-1||data.message.indexOf('CSRF')!==-1){alert('Сессия истекла. Страница будет перезагружена.'); window.location.reload();}else{alert(data.message); btn.disabled=false; btn.innerHTML=orig;}}
    }).catch(()=>{alert('Ошибка отправки'); btn.disabled=false; btn.innerHTML=orig;});
  });
});
</script>
