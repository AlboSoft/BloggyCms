<?php /** Habr Pro - Bookmarks - flat */ ?>
<div style="max-width:1220px;margin:0 auto;padding:20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #000">
    <div><div style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a8a8a;margin-bottom:6px">bookmarks / <?= (int)($bookmarks_count??0) ?></div><h1 style="font-size:22px;font-weight:800;letter-spacing:-.02em;margin:0"><?= LANG_TEMPLATE_BOOKMARKS_TITLE ?></h1><p style="font-size:12px;color:#6c6c6c;margin:6px 0 0"><?= (int)($bookmarks_count??0) ?> <?= plural((int)($bookmarks_count??0),[LANG_TEMPLATE_BOOKMARKS_POST_1,LANG_TEMPLATE_BOOKMARKS_POST_2,LANG_TEMPLATE_BOOKMARKS_POST_3]) ?></p></div>
    <?php if(!empty($posts)){ ?><a href="<?= BASE_URL ?>/posts" style="padding:6px 12px;background:#fff;color:#000;border:1px solid #e8e8e8;font-size:11px;font-weight:700;text-transform:uppercase;text-decoration:none">all posts</a><?php } ?>
  </div>
  <?php if(empty($posts)){ ?>
    <div style="border:1px solid #e8e8e8;padding:32px;text-align:center"><div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px"><?= LANG_TEMPLATE_BOOKMARKS_EMPTY_TITLE ?></div><p style="font-size:13px;color:#6c6c6c"><?= LANG_TEMPLATE_BOOKMARKS_EMPTY_TEXT ?></p><a href="<?= BASE_URL ?>/posts" style="display:inline-block;margin-top:12px;padding:6px 12px;background:#000;color:#fff;border:1px solid #000;font-size:11px;font-weight:700;text-transform:uppercase;text-decoration:none"><?= LANG_TEMPLATE_BOOKMARKS_FIND_POSTS_BTN ?></a></div>
  <?php }else{ ?>
    <div style="display:grid;gap:0;border:1px solid #e8e8e8">
      <?php foreach($posts as $post){
        $showCoverInList=!isset($post['show_cover_in_list'])||(int)$post['show_cover_in_list']===1;
        $featuredImage=($post['featured_image']&&$showCoverInList)?BASE_URL.'/uploads/images/'.html($post['featured_image']):null;
      ?>
      <div style="display:flex;gap:12px;padding:12px;border-bottom:1px solid #f0f0f0;background:#fff" class="tg-bookmark-item" data-post-id="<?= $post['id'] ?>">
        <?php if($featuredImage){ ?><a href="<?= BASE_URL.'/post/'.html($post['slug']) ?>" style="flex:0 0 80px;height:48px;border:1px solid #e8e8e8;overflow:hidden;display:block"><img src="<?= $featuredImage ?>" alt="<?= html($post['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover"></a><?php } ?>
        <div style="flex:1;min-width:0">
          <?php if(!empty($post['category_name'])){ ?><a href="<?= BASE_URL ?>/category/<?= html($post['category_slug']) ?>" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6c6c6c;text-decoration:none"><?= html($post['category_name']) ?></a><?php } ?>
          <h3 style="font-size:13px;font-weight:700;margin:4px 0;line-height:1.3"><a href="<?= BASE_URL.'/post/'.html($post['slug']) ?>" style="color:#000;text-decoration:none"><?= html($post['title']) ?></a></h3>
          <div style="font-size:11px;color:#8a8a8a"><?= sprintf(LANG_TEMPLATE_BOOKMARKS_SAVED_AT,time_ago($post['bookmarked_at'])) ?> · <?= $post['views']??0 ?> views</div>
        </div>
        <button class="tg-bookmark-remove" data-post-id="<?= $post['id'] ?>" title="<?= LANG_TEMPLATE_BOOKMARKS_REMOVE_TITLE ?>" style="flex:0 0 28px;height:28px;border:1px solid #e8e8e8;background:#fff;cursor:pointer;font-size:14px">×</button>
      </div>
      <?php } ?>
    </div>
  <?php } ?>
</div>
<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.tg-bookmark-remove').forEach(btn=>{
    btn.addEventListener('click',function(){
      var postId=this.dataset.postId, item=this.closest('.tg-bookmark-item');
      if(!confirm('<?= LANG_TEMPLATE_BOOKMARKS_REMOVE_CONFIRM ?>')) return;
      item.style.opacity='0.5'; item.style.pointerEvents='none';
      fetch(`<?= BASE_URL ?>/post/bookmark/${postId}`,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(data=>{
        if(data.success){item.style.transition='all .3s';item.style.transform='translateX(100%)';item.style.opacity='0';setTimeout(()=>{item.remove();if(!document.querySelectorAll('.tg-bookmark-item').length) location.reload();},300);}
        else{item.style.opacity='1';item.style.pointerEvents='auto';alert('<?= LANG_TEMPLATE_BOOKMARKS_REMOVE_ERROR ?>');}
      }).catch(()=>{item.style.opacity='1';item.style.pointerEvents='auto';alert('<?= LANG_TEMPLATE_BOOKMARKS_REMOVE_ERROR ?>');});
    });
  });
});
</script>
<?php front_bottom_js(ob_get_clean()); ?>
