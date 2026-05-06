<?php
/**
 * Template Name: Профиль пользователя
 */
$fieldModel = new FieldModel($this->db);
?>

<div class="tg-profile">
    <div class="tg-container">
        
        <div class="tg-profile-header">
            <div class="tg-profile-avatar">
                <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($user['avatar']); ?>"
                         alt="<?php echo html($user['display_name'] ?? $user['username']); ?>">
                <?php } else { ?>
                    <div class="tg-avatar-placeholder">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                <?php } ?>
                <?php if ($is_online) { ?>
                    <span class="tg-online" title="<?php echo LANG_TEMPLATE_PROFILE_ONLINE_TITLE; ?>"></span>
                <?php } ?>
            </div>
            <div class="tg-profile-info">
                <h1 class="tg-profile-name">
                <?php echo html($user['display_name'] ?? $user['username']); ?>
                <?php if ($showLastAchievement && !empty($last_achievement)) { ?>
                    <span class="tg-latest-achievement-badge" 
                        title="<?php echo html($last_achievement['name']); ?><?php echo !empty($last_achievement['description']) ? ' - ' . html($last_achievement['description']) : ''; ?>
                                (<?php echo $last_achievement['unlocked_formatted']; ?>)"
                        data-bs-toggle="tooltip">
                        <?php if (!empty($last_achievement['image'])) { ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo $last_achievement['image']; ?>" 
                                alt="<?php echo html($last_achievement['name']); ?>"
                                class="tg-achievement-badge-icon">
                        <?php } else { ?>
                            <?php 
                            $iconName = str_replace('bi-', '', $last_achievement['icon'] ?? 'trophy');
                            echo bloggy_icon('bs', $iconName, '14', '#ffc107', 'me-1');
                            ?>
                        <?php } ?>
                        <?php echo html($last_achievement['name']); ?>
                    </span>
                <?php } ?>
                <?php if ($is_online) { ?>
                    <span class="tg-online" title="<?php echo LANG_TEMPLATE_PROFILE_ONLINE_TITLE; ?>"></span>
                <?php } ?>
            </h1>
                <div class="tg-profile-meta">
                    <span class="tg-username">@<?php echo html($user['username']); ?></span>
                    <?php if (!$is_online && !empty($last_activity_human)) { ?>
                        <span class="tg-last-seen">• <?php echo $last_activity_human; ?></span>
                    <?php } ?>
                </div>
                <?php if (!empty($groups)) { ?>
                    <div class="tg-profile-groups">
                        <?php foreach ($groups as $group) { ?>
                            <span class="tg-group-badge"><?php echo html($group['name']); ?></span>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if (!empty($roleDisplay)) { ?>
                    <div class="tg-profile-role">
                        <span class="tg-role-badge"><?php echo html($roleDisplay); ?></span>
                    </div>
                <?php } ?>
            </div>
            <?php if ($is_own_profile) { ?>
                <a href="<?php echo BASE_URL; ?>/profile/edit" class="tg-edit-btn">
                    <?php echo bloggy_icon('bs', 'pencil', '16', 'currentColor'); ?>
                </a>
            <?php } ?>
        </div>

        <div class="tg-profile-grid">
            <div class="tg-profile-sidebar">

                <div class="tg-card">
                    <div class="tg-card-body">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'person', '18', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_ABOUT_TITLE; ?>
                        </h3>
                        <?php if (!empty($user['bio'])) { ?>
                            <div class="tg-bio"><?php echo nl2br(html($user['bio'])); ?></div>
                        <?php } else { ?>
                            <div class="tg-bio tg-bio-empty">
                                <?php echo sprintf(LANG_TEMPLATE_PROFILE_ABOUT_EMPTY, html($user['display_name'] ?? $user['username'])); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <?php if (!empty($customFields)) { 
                    $fieldValues = $fieldModel->getFieldValues($user['id'], 'user');
                    $fieldManager = new \FieldManager($this->db);
                ?>
                <div class="tg-card">
                    <div class="tg-card-body">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'info-circle', '18', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_ADDITIONAL_TITLE; ?>
                        </h3>
                        <div class="tg-custom-fields-list">
                            <?php foreach ($customFields as $field) { 
                                $fieldValue = $fieldValues[$field['system_name']] ?? null;
                                if (empty($fieldValue) && $fieldValue !== '0') {
                                    continue;
                                }
                                $displayValue = $fieldManager->renderFieldDisplay(
                                    $field['type'],
                                    $fieldValue,
                                    json_decode($field['config'] ?? '{}', true),
                                    'user',
                                    $user['id']
                                );
                            ?>
                                <div class="tg-custom-field-item">
                                    <div class="tg-custom-field-label"><?php echo html($field['name']); ?>:</div>
                                    <div class="tg-custom-field-value"><?php echo $displayValue; ?></div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <div class="tg-card">
                    <div class="tg-card-body">
                        <h3 class="tg-card-title mb-3">
                            <?php echo bloggy_icon('bs', 'bar-chart', '18', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_STATS_TITLE; ?>
                        </h3>
                        <div class="tg-stats" style="gap: 8px; margin-bottom: 12px; padding: 8px; background: var(--tg-bg); border-radius: var(--tg-radius-md);">
                            <?php if ($isAdmin || $profileUserIsAdmin) { ?>
                            <div class="tg-stat" style="flex: 1; text-align: center;">
                                <span class="tg-stat-value" style="font-size: 16px;"><?php echo $total = (int)($postsCount ?? 0); ?></span>
                                <span class="tg-stat-label" style="font-size: 10px;"><?php echo plural($total, [LANG_TEMPLATE_PROFILE_STATS_POSTS_1, LANG_TEMPLATE_PROFILE_STATS_POSTS_2, LANG_TEMPLATE_PROFILE_STATS_POSTS_3]); ?></span>
                            </div>
                            <?php } ?>
                            <div class="tg-stat" style="flex: 1; text-align: center;">
                                <span class="tg-stat-value" style="font-size: 16px;"><?php echo $commentsCount ?? 0; ?></span>
                                <span class="tg-stat-label" style="font-size: 10px;"><?php echo LANG_TEMPLATE_PROFILE_STATS_COMMENTS; ?></span>
                            </div>
                            <div class="tg-stat" style="flex: 1; text-align: center;">
                                <span class="tg-stat-value" style="font-size: 16px;"><?php echo $total = (int)($unlockedCount ?? 0); ?></span>
                                <span class="tg-stat-label" style="font-size: 10px;"><?php echo plural($total, [LANG_TEMPLATE_PROFILE_STATS_ACHIEVEMENTS_1, LANG_TEMPLATE_PROFILE_STATS_ACHIEVEMENTS_2, LANG_TEMPLATE_PROFILE_STATS_ACHIEVEMENTS_3]); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tg-card">
                    <div class="tg-card-body">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'trophy', '18', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo LANG_TEMPLATE_PROFILE_ACHIEVEMENTS_TITLE; ?>
                        </h3>
                        <div class="tg-achievements-summary">
                            <div class="tg-stat">
                                <span class="tg-stat-value"><?php echo $unlockedCount; ?></span>
                                <span class="tg-stat-label"><?php echo LANG_TEMPLATE_PROFILE_ACHIEVEMENTS_EARNED; ?></span>
                            </div>
                            <div class="tg-stat">
                                <span class="tg-stat-value"><?php echo $totalAchievementsInSystem - $unlockedCount; ?></span>
                                <span class="tg-stat-label"><?php echo LANG_TEMPLATE_PROFILE_ACHIEVEMENTS_LEFT; ?></span>
                            </div>
                        </div>
                        <?php if (!empty($achievements)) { ?>
                            <div class="tg-achievements-preview tg-mt-3">
                                <?php foreach (array_slice($achievements, 0, 6) as $achievement) { ?>
                                    <div class="tg-achievement-mini" title="<?php echo html($achievement['name']); ?>">
                                        <?php if (!empty($achievement['image'])) { ?>
                                            <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo html($achievement['image']); ?>"
                                                 alt="<?php echo html($achievement['name']); ?>">
                                        <?php } else { ?>
                                            <div class="tg-achievement-icon-compact">
                                                <?php 
                                                $iconName = str_replace('bi-', '', $achievement['icon'] ?? 'trophy');
                                                echo bloggy_icon('bs', $iconName, '14', '#fff');
                                                ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                                <?php if ($unlockedCount > 6) { ?>
                                    <div class="tg-achievement-more">
                                        <span>+<?php echo $unlockedCount - 6; ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <div class="tg-achievements-link tg-mt-3">
                            <a href="<?php echo BASE_URL; ?>/users/achievements" class="btn btn-outline-primary btn-sm w-100">
                                <?php echo bloggy_icon('bs', 'trophy-fill', '14', 'currentColor', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_PROFILE_ALL_ACHIEVEMENTS_BTN; ?>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="tg-profile-content">
                <?php if ($displayType === 'posts') { ?>
                    <div class="tg-profile-posts">
                        <div class="tg-card">
                            <div class="tg-card-header">
                                <h3 class="tg-card-title">
                                    <?php echo bloggy_icon('bs', 'file-text', '18', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_PROFILE_POSTS_TITLE; ?>
                                </h3>
                                <?php if (!empty($posts)) { ?>
                                    <span class="tg-posts-count"><?php echo count($posts); ?></span>
                                <?php } ?>
                            </div>
                            <?php if (!empty($posts)) { ?>
                                <div class="tg-posts-list">
                                    <?php foreach ($posts as $post) { 
                                        $featuredImage = $post['featured_image'] 
                                            ? BASE_URL . '/uploads/images/' . html($post['featured_image']) 
                                            : null;
                                    ?>
                                        <div class="tg-post-item">
                                            <?php if ($featuredImage) { ?>
                                                <a href="<?php echo BASE_URL; ?>/post/<?php echo html($post['slug']); ?>" 
                                                class="tg-post-item-image">
                                                    <img src="<?php echo $featuredImage; ?>" 
                                                        alt="<?php echo html($post['title']); ?>">
                                                </a>
                                            <?php } ?>
                                            <div class="tg-post-item-content">
                                                <h4 class="tg-post-item-title">
                                                    <a href="<?php echo BASE_URL; ?>/post/<?php echo html($post['slug']); ?>">
                                                        <?php echo html($post['title']); ?>
                                                    </a>
                                                </h4>
                                                <div class="tg-post-meta-info">
                                                    <span class="tg-post-meta-date">
                                                        <?php echo bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1'); ?>
                                                        <?php echo date('d.m.Y', strtotime($post['created_at'])); ?>
                                                    </span>
                                                    <span class="tg-post-meta-views">
                                                        <?php echo bloggy_icon('bs', 'eye', '12', 'currentColor', 'tg-mr-1'); ?>
                                                        <?php echo $post['views'] ?? 0; ?>
                                                    </span>
                                                    <?php if (($post['likes_count'] ?? 0) > 0) { ?>
                                                    <span class="tg-post-meta-likes">
                                                        <?php echo bloggy_icon('bs', 'heart', '12', 'currentColor', 'tg-mr-1'); ?>
                                                        <?php echo $post['likes_count'] ?? 0; ?>
                                                    </span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="tg-card-footer">
                                    <a href="<?php echo BASE_URL; ?>/posts" class="btn btn-outline-primary btn-sm w-100">
                                        <?php echo bloggy_icon('bs', 'arrow-right', '14', 'currentColor', 'me-2'); ?>
                                        <?php echo LANG_TEMPLATE_PROFILE_ALL_POSTS_BTN; ?>
                                    </a>
                                </div>
                            <?php } else { ?>
                                <div class="tg-empty-state tg-empty-state-small tg-text-center">
                                    <div class="tg-empty-state-icon">
                                        <?php echo bloggy_icon('bs', 'file-text', '32', 'var(--tg-text-secondary)'); ?>
                                    </div>
                                    <h4 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_PROFILE_NO_POSTS_TITLE; ?></h4>
                                    <p class="tg-empty-state-text"><?php echo LANG_TEMPLATE_PROFILE_NO_POSTS_TEXT; ?></p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                <?php } else if ($displayType === 'bookmarks') { ?>
                    <div class="tg-profile-bookmarks">
                        <div class="tg-card">
                            <div class="tg-card-header">
                                <h3 class="tg-card-title">
                                    <?php echo bloggy_icon('bs', 'bookmark-star', '18', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_PROFILE_BOOKMARKS_TITLE; ?>
                                </h3>
                                <?php if (!empty($bookmarks)) { ?>
                                    <span class="tg-bookmarks-count"><?php echo count($bookmarks); ?></span>
                                <?php } ?>
                            </div>
                            
                            <?php if (!empty($bookmarks)) { ?>
                                <div class="tg-bookmarks-list">
                                    <?php foreach ($bookmarks as $post) { 
                                        $featuredImage = $post['featured_image'] 
                                            ? BASE_URL . '/uploads/images/' . html($post['featured_image']) 
                                            : null;
                                        $userLiked = isset($post['userLiked']) && $post['userLiked'];
                                    ?>
                                    <div class="bookmark-item" data-post-id="<?php echo $post['id']; ?>">
                                        
                                        <?php if ($featuredImage) { ?>
                                        <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>" class="bookmark-image-link">
                                            <img src="<?php echo $featuredImage; ?>" 
                                                alt="<?php echo html($post['title']); ?>"
                                                loading="lazy">
                                        </a>
                                        <?php } ?>
                                        
                                        <div class="bookmark-content">
                                            <?php if (!empty($post['category_name'])) { ?>
                                            <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                                            class="bookmark-category">
                                                <?php echo html($post['category_name']); ?>
                                            </a>
                                            <?php } ?>
                                            
                                            <h3 class="bookmark-title">
                                                <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                                    <?php echo html($post['title']); ?>
                                                </a>
                                            </h3>
                                            
                                            <?php if (!empty($post['short_description'])) { ?>
                                            <p class="bookmark-excerpt">
                                                <?php echo html(mb_strimwidth($post['short_description'], 0, 120, '...')); ?>
                                            </p>
                                            <?php } ?>
                                            
                                            <div class="bookmark-meta">
                                                <span class="bookmark-date">
                                                    <?php echo bloggy_icon('bs', 'bookmark', '12', 'currentColor', 'tg-mr-1'); ?>
                                                    <?php echo sprintf(LANG_TEMPLATE_PROFILE_BOOKMARKS_SAVED, time_ago($post['bookmarked_at'])); ?>
                                                </span>
                                                
                                                <?php if ($post['views'] > 0) { ?>
                                                <span class="bookmark-views">
                                                    <?php echo bloggy_icon('bs', 'eye', '12', 'currentColor', 'tg-mr-1'); ?>
                                                    <?php echo $post['views']; ?>
                                                </span>
                                                <?php } ?>
                                                
                                                <?php if ($post['likes_count'] > 0) { ?>
                                                <span class="bookmark-likes">
                                                    <?php echo bloggy_icon('bs', 'heart', '12', 'currentColor', 'tg-mr-1'); ?>
                                                    <?php echo $post['likes_count']; ?>
                                                </span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        
                                        <button class="remove-bookmark" 
                                                data-post-id="<?php echo $post['id']; ?>"
                                                title="<?php echo LANG_TEMPLATE_PROFILE_BOOKMARKS_REMOVE_TITLE; ?>">
                                            ✕
                                        </button>
                                    </div>
                                    <?php } ?>
                                </div>
                                
                                <div class="tg-card-footer">
                                    <a href="<?php echo BASE_URL; ?>/user/bookmarks" class="btn btn-outline-primary btn-sm w-100">
                                        <?php echo bloggy_icon('bs', 'bookmark', '14', 'currentColor', 'me-2'); ?>
                                        <?php echo LANG_TEMPLATE_PROFILE_ALL_BOOKMARKS_BTN; ?>
                                    </a>
                                </div>
                                
                            <?php } else { ?>
                                <div class="tg-empty-state tg-empty-state-small tg-text-center">
                                    <div class="tg-empty-state-icon">
                                        <?php echo bloggy_icon('bs', 'bookmark', '32', 'var(--tg-text-secondary)'); ?>
                                    </div>
                                    <h4 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_PROFILE_NO_BOOKMARKS_TITLE; ?></h4>
                                    <p class="tg-empty-state-text"><?php echo LANG_TEMPLATE_PROFILE_NO_BOOKMARKS_TEXT; ?></p>
                                    <a href="<?php echo BASE_URL; ?>/posts" class="btn btn-sm btn-outline-primary mt-2">
                                        <?php echo LANG_TEMPLATE_PROFILE_GO_TO_POSTS_BTN; ?>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                <?php } else if ($displayType === 'restricted') { ?>
                    <div class="tg-profile-bookmarks">
                        <div class="tg-card">
                            <div class="tg-card-header">
                                <h3 class="tg-card-title">
                                    <?php echo bloggy_icon('bs', 'bookmark-star', '18', 'currentColor', 'tg-mr-1'); ?>
                                    <?php echo LANG_TEMPLATE_PROFILE_BOOKMARKS_TITLE; ?>
                                </h3>
                            </div>
                            <div class="tg-card-body">
                                <div class="tg-empty-state tg-empty-state-small tg-text-center">
                                    <div class="tg-empty-state-icon">
                                        <?php echo bloggy_icon('bs', 'lock', '32', 'var(--tg-text-secondary)'); ?>
                                    </div>
                                    <h4 class="tg-empty-state-title"><?php echo LANG_TEMPLATE_PROFILE_RESTRICTED_TITLE; ?></h4>
                                    <p class="tg-empty-state-text">
                                        <?php if ($profileUserIsAdmin) { ?>
                                            <?php echo LANG_TEMPLATE_PROFILE_RESTRICTED_ADMIN_TEXT; ?>
                                        <?php } else { ?>
                                            <?php echo LANG_TEMPLATE_PROFILE_RESTRICTED_TEXT; ?>
                                        <?php } ?>
                                    </p>
                                    <?php if (!isset($_SESSION['user_id'])) { ?>
                                        <a href="<?php echo BASE_URL; ?>/auth/login" class="btn btn-sm btn-outline-primary mt-2">
                                            <?php echo LANG_TEMPLATE_PROFILE_LOGIN_BTN; ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php
ob_start();
?>
<script>
    window.baseUrl = '<?php echo BASE_URL; ?>';
    window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<?php front_bottom_js(ob_get_clean()); ?>
<?php front_js('/templates/default/front/assets/js/user-action.js'); ?>
<?php front_js('/templates/default/front/assets/js/bookmarks.js'); ?>