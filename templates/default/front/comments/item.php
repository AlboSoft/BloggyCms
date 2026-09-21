<?php /** Habr Pro - Comment Item - flat 0 radius */
$isPending=$comment['status']==='pending'; $isOwnComment=isset($_SESSION['user_id'])&&$comment['user_id']==$_SESSION['user_id']; $isAdmin=isset($_SESSION['is_admin']); $canEdit=$isOwnComment||$isAdmin;
$authorName=html($comment['author_username']??$comment['author_name']??LANG_TEMPLATE_COMMENT_SINGLE_ANONYMOUS);
$commentDate=date('d.m.Y H:i',strtotime($comment['created_at']));
?>
<div style="border:1px solid #e8e8e8;border-left:2px solid <?= $isPending?'#8a8a8a':'#000' ?>;background:#fff;padding:10px 12px;margin-bottom:8px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif" id="tg-comment-<?= $comment['id'] ?>">
  <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:8px">
    <div style="width:28px;height:28px;border:1px solid #e8e8e8;background:#f5f5f5;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11px">
      <?php if(!empty($comment['author_avatar'])&&$comment['author_avatar']!=='default.jpg'){ ?><img src="<?= BASE_URL ?>/uploads/avatars/<?= html($comment['author_avatar']) ?>" alt="<?= $authorName ?>" style="width:100%;height:100%;object-fit:cover"><?php }else{ ?><?= strtoupper(substr($authorName,0,1)) ?><?php } ?>
    </div>
    <div style="flex:1;min-width:0">
      <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap"><span style="font-size:12px;font-weight:700"><?= $authorName ?></span><?php if($isOwnComment){ ?><span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;background:#000;color:#fff;padding:1px 4px">you</span><?php } ?><?php if($isPending){ ?><span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;background:#f5f5f5;border:1px solid #e8e8e8;padding:1px 4px;color:#8a8a8a">pending</span><?php } ?><?php if(!empty($comment['parent_id'])){ ?><span style="font-size:10px;color:#8a8a8a">↳ reply</span><?php } ?></div>
      <div style="font-size:10px;color:#8a8a8a;margin-top:2px"><?= $commentDate ?><?php if(!empty($comment['was_edited'])&&$comment['was_edited']){ ?> · edited<?php } ?></div>
    </div>
  </div>
  <div style="font-size:13px;line-height:1.5;color:#212121;margin-bottom:8px;word-break:break-word"><?= nl2br(html($comment['content'])) ?></div>
  <div style="display:flex;gap:6px;flex-wrap:wrap">
    <button type="button" class="tg-reply-btn" data-comment-id="<?= $comment['id'] ?>" data-comment-author="<?= $authorName ?>" style="padding:4px 8px;background:#fff;border:1px solid #e8e8e8;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;cursor:pointer">reply</button>
    <?php if($canEdit){ ?>
      <a href="<?= BASE_URL ?>/comment/edit/<?= $comment['id'] ?>" style="padding:4px 8px;background:#fff;border:1px solid #e8e8e8;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;text-decoration:none;color:#000">edit</a>
      <a href="<?= BASE_URL ?>/comment/delete/<?= $comment['id'] ?>" onclick="return confirm('<?= LANG_TEMPLATE_COMMENT_SINGLE_DELETE_CONFIRM ?>')" style="padding:4px 8px;background:#fff;border:1px solid #e8e8e8;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;text-decoration:none;color:#8a8a8a">delete</a>
    <?php } ?>
    <?php if($isAdmin&&$isPending){ ?><a href="<?= ADMIN_URL ?>/comments/approve/<?= $comment['id'] ?>" style="padding:4px 8px;background:#000;color:#fff;border:1px solid #000;font-size:10px;font-weight:700;text-transform:uppercase;text-decoration:none">approve</a><?php } ?>
  </div>
  <div id="tg-replies-<?= $comment['id'] ?>"></div>
</div>
