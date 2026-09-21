<?php /** Habr Pro - Feedback Iconic Form - flat */ echo add_frontend_css('/templates/default/front/assets/html_blocks/FeedbackBlock/css/iconic-form.css'); ?>
<form action="<?= html($actionUrl) ?>" method="POST" class="feedback-form iconic-form" id="feedback-form-<?= html($formSlug) ?>" data-ajax="true" style="border:1px solid #e8e8e8;background:#fff">
  <input type="hidden" name="form_id" value="<?= html($formId) ?>">
  <input type="hidden" name="form_slug" value="<?= html($formSlug) ?>">
  <?php if(!empty($csrfToken)): ?><input type="hidden" name="csrf_token" value="<?= html($csrfToken) ?>"><?php endif; ?>
  <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0">
    <?php foreach($structure as $field): ?>
      <?php if($field['type']==='hidden'): ?><input type="hidden" name="<?= html($field['name']) ?>" value="<?= html($field['default_value']??'') ?>">
      <?php elseif($field['type']==='submit'): ?>
        <?php if(!empty($captchaHtml)): ?><div style="grid-column:1/-1;padding:10px;border-top:1px solid #e8e8e8"><?= $captchaHtml ?></div><?php endif; ?>
        <div style="grid-column:1/-1"><button type="submit" style="width:100%;padding:10px;background:#000;color:#fff;border:0;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.04em;cursor:pointer"><?= html($field['label']??'Отправить') ?></button></div>
      <?php else: $col=$field['type']==='textarea'?'1/-1':'auto'; $value=html($field['default_value']??''); $ph=html($field['placeholder']??''); $req=!empty($field['required'])?'required':''; ?>
        <div style="grid-column:<?= $col ?>;border-right:1px solid #e8e8e8;border-bottom:1px solid #e8e8e8;display:flex;align-items:center;gap:0">
          <span style="padding:0 8px;font-size:10px;color:#8a8a8a;text-transform:uppercase;font-weight:700;min-width:60px"><?= html($field['name']) ?></span>
          <?php if($field['type']==='textarea'){ ?><textarea name="<?= html($field['name']) ?>" placeholder="<?= $ph ?>" <?= $req ?> rows="3" style="flex:1;border:0;padding:10px;font-size:13px;resize:vertical;border-radius:0;border-left:1px solid #e8e8e8"><?= $value ?></textarea><?php }else{ ?><input type="<?= html($field['type']) ?>" name="<?= html($field['name']) ?>" placeholder="<?= $ph ?>" value="<?= $value ?>" <?= $req ?> style="flex:1;border:0;padding:10px;font-size:13px;border-radius:0;border-left:1px solid #e8e8e8"><?php } ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</form>
