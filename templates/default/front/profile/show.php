<?php
/**
 * Template Name: Профиль - Habr Pro
 */
$fieldModel = new FieldModel($this->db);
?>
<div class="tg-profile">
    <div class="tg-container">
        <div class="tg-profile-header">
            <div class="tg-profile-avatar">
                <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($user['avatar']); ?>" alt="<?php echo html($user['display_name'] ?? $user['username']); ?>">
                <?php } else { ?>
                    <div class="tg-avatar-placeholder"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                <?php } ?>
            </div>
            <div class="tg-profile-info">
                <h1 class="tg-profile-name"><?php echo html($user['display_name'] ?? $user['username']); ?><?php if ($is_online) { ?><span class="tg-online"></span><?php } ?></h1>
                <div class="tg-profile-meta"><span class="tg-username">@<?php echo html($user['username']); ?></span><?php if (!$is_online && !empty($last_activity_human)) { ?><span>· <?php echo $last_activity_human; ?></span><?php } ?></div>
                <?php if (!empty($groups)) { ?><div class="tg-profile-groups"><?php foreach ($groups as $group) { ?><span class="tg-group-badge"><?php echo html($group['name']); ?></span><?php } ?></div><?php } ?>
            </div>
            <?php if ($is_own_profile) { ?><a href="<?php echo BASE_URL; ?>/profile/edit" class="tg-btn tg-btn-outline tg-btn-sm">править</a><?php } ?>
        </div>

        <div class="tg-profile-grid">
            <div class="tg-profile-sidebar">
                <div class="tg-card"><div class="tg-card-body"><h3 class="tg-card-title">о себе</h3><?php if (!empty($user['bio'])) { ?><div class="tg-bio"><?php echo nl2br(html($user['bio'])); ?></div><?php } else { ?><div class="tg-bio tg-bio-empty">пользователь пока ничего не рассказал</div><?php } ?></div></div>
                <div class="tg-card"><div class="tg-card-body"><h3 class="tg-card-title">статистика</h3><div class="tg-stats">
                    <div class="tg-stat"><span class="tg-stat-value"><?php echo (int)($postsCount ?? 0); ?></span><span class="tg-stat-label">постов</span></div>
                    <div class="tg-stat"><span class="tg-stat-value"><?php echo $commentsCount ?? 0; ?></span><span class="tg-stat-label">комментов</span></div>
                    <div class="tg-stat"><span class="tg-stat-value"><?php echo (int)($unlockedCount ?? 0); ?></span><span class="tg-stat-label">ачивок</span></div>
                </div></div></div>
                <?php if (!empty($achievements)) { ?>
                <div class="tg-card"><div class="tg-card-body"><h3 class="tg-card-title">достижения</h3><div class="tg-achievements-preview">
                    <?php foreach (array_slice($achievements, 0, 6) as $ach) { ?>
                        <div class="tg-achievement-mini" title="<?php echo html($ach['name']); ?>"><?php if (!empty($ach['image'])) { ?><img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo html($ach['image']); ?>" alt=""><?php } else { ?><span style="font-size:12px;font-weight:700">★</span><?php } ?></div>
                    <?php } ?>
                    <?php if (($unlockedCount ?? 0) > 6) { ?><div class="tg-achievement-more">+<?php echo $unlockedCount - 6; ?></div><?php } ?>
                </div></div></div>
                <?php } ?>
            </div>
            <div class="tg-profile-content">
                <?php if ($displayType === 'posts') { ?>
                    <?php if (!empty($posts)) { ?>
                        <div class="tg-posts-list">
                        <?php foreach ($posts as $post) {
                            $showCoverInList = !isset($post['show_cover_in_list']) || (int)$post['show_cover_in_list'] === 1;
                            $featuredImage = ($post['featured_image'] && $showCoverInList) ? BASE_URL . '/uploads/images/' . html($post['featured_image']) : null;
                        ?>
                            <article class="tg-post-card">
                                <div class="tg-post-meta"><span class="tg-post-date"><?php echo date('d.m.Y', strtotime($post['created_at'])); ?></span></div>
                                <h2 class="tg-post-title"><a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>"><?php echo html($post['title']); ?></a></h2>
                                <?php if (!empty($post['short_description'])) { ?><p class="tg-post-excerpt"><?php echo html($post['short_description']); ?></p><?php } ?>
                                <div class="tg-post-actions"><span class="tg-post-views">👁 <?php echo $post['views'] ?? 0; ?></span><span>♥ <?php echo $post['likes_count'] ?? 0; ?></span><span>💬 <?php echo $post['comments_count'] ?? 0; ?></span></div>
                            </article>
                        <?php } ?>
                        </div>
                    <?php } else { ?><div class="tg-empty-state"><h3 class="tg-empty-state-title">нет публикаций</h3></div><?php } ?>
                <?php } else { ?>
                    <div class="tg-card"><div class="tg-card-body">контент профиля</div></div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
