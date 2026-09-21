<?php
/**
 * Tags Sidebar - Habr Pro - компактный
 */
$theme = $settings['theme'] ?? 'light';
$showPostCount = !empty($settings['show_post_count']);
$tags = $this->tags ?? [];
?>
<div class="tg-sidebar-tags">
    <?php if(!empty($settings['title'])) { ?>
        <div class="tg-sidebar-tags-header" style="margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid #e8e8e8">
            <h3 class="tg-sidebar-tags-title" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin:0;color:#000"><?php echo html($settings['title']); ?></h3>
            <?php if(!empty($settings['description'])) { ?><p style="font-size:11px;color:#6c6c6c;margin:4px 0 0"><?php echo html($settings['description']); ?></p><?php } ?>
        </div>
    <?php } ?>
    <?php if(!empty($tags)) { ?>
        <div class="tg-sidebar-tags-list" style="display:flex;flex-wrap:wrap;gap:4px">
            <?php foreach($tags as $tag) { ?>
                <a href="/tag/<?php echo html($tag['slug']); ?>" class="tg-sidebar-tag" style="display:inline-block;padding:2px 6px;background:#fff;border:1px solid #e8e8e8;font-size:11px;color:#000;text-decoration:none">
                    #<?php echo html($tag['name']); ?><?php if($showPostCount && ($tag['posts_count'] ?? 0) > 0) { ?><span style="opacity:.6;margin-left:3px"><?php echo $tag['posts_count']; ?></span><?php } ?>
                </a>
            <?php } ?>
        </div>
    <?php } else { ?><p style="font-size:11px;color:#8a8a8a">нет тегов</p><?php } ?>
</div>
