<?php
/**
* Template Name: Страница списка пользователей
*/

front_css('templates/default/front/assets/css/users.css');

$totalPosts = 0;
$canViewAdminStats = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
$onlineCount = (int)($online_count ?? 0);
$totalUsers = (int)($total_users ?? 0);

if (!empty($users)) {
    foreach ($users as $user) {
        $totalPosts += (int)($user['posts_count'] ?? 0);
    }
}
?>

<div class="tg-page members-page">
    <div class="tg-container">
        <header class="members-hero">
            <div class="members-hero__main">
                <div class="members-hero__icon" aria-hidden="true">
                    <?php echo bloggy_icon('bs', 'people-fill', '30', 'currentColor'); ?>
                </div>
                <div class="members-hero__content">
                    <h1 class="members-hero__title"><?php echo LANG_TEMPLATE_USERS_TITLE; ?></h1>
                    <p class="members-hero__description">
                        <span class="members-hero__online"><?php echo $onlineCount; ?></span>
                        <?php echo plural($onlineCount, [LANG_TEMPLATE_USERS_ONLINE_1, LANG_TEMPLATE_USERS_ONLINE_2, LANG_TEMPLATE_USERS_ONLINE_3]); ?>
                        <?php echo LANG_TEMPLATE_USERS_OUT_OF; ?>
                        <strong><?php echo $totalUsers; ?></strong>
                        <?php echo plural($totalUsers, [LANG_TEMPLATE_USERS_MEMBER_1, LANG_TEMPLATE_USERS_MEMBER_2, LANG_TEMPLATE_USERS_MEMBER_3]); ?>
                    </p>
                </div>
            </div>

            <div class="members-hero__stats">
                <div class="members-summary">
                    <span class="members-summary__icon members-summary__icon--members" aria-hidden="true">
                        <?php echo bloggy_icon('bs', 'people', '20', 'currentColor'); ?>
                    </span>
                    <span class="members-summary__content">
                        <strong class="members-summary__value"><?php echo $totalUsers; ?></strong>
                        <span class="members-summary__label">
                            <?php echo plural($totalUsers, [LANG_TEMPLATE_USERS_MEMBER_1, LANG_TEMPLATE_USERS_MEMBER_2, LANG_TEMPLATE_USERS_MEMBER_3]); ?>
                        </span>
                    </span>
                </div>
                <div class="members-summary">
                    <span class="members-summary__icon members-summary__icon--online" aria-hidden="true">
                        <?php echo bloggy_icon('bs', 'broadcast', '20', 'currentColor'); ?>
                    </span>
                    <span class="members-summary__content">
                        <strong class="members-summary__value"><?php echo $onlineCount; ?></strong>
                        <span class="members-summary__label">
                            <?php echo plural($onlineCount, [LANG_TEMPLATE_USERS_ONLINE_1, LANG_TEMPLATE_USERS_ONLINE_2, LANG_TEMPLATE_USERS_ONLINE_3]); ?>
                        </span>
                    </span>
                </div>
                <?php if ($canViewAdminStats) { ?>
                    <div class="members-summary">
                        <span class="members-summary__icon members-summary__icon--posts" aria-hidden="true">
                            <?php echo bloggy_icon('bs', 'file-earmark-text', '20', 'currentColor'); ?>
                        </span>
                        <span class="members-summary__content">
                            <strong class="members-summary__value"><?php echo $totalPosts; ?></strong>
                            <span class="members-summary__label">
                                <?php echo plural($totalPosts, [LANG_TEMPLATE_USERS_POST_1, LANG_TEMPLATE_USERS_POST_2, LANG_TEMPLATE_USERS_POST_3]); ?>
                            </span>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </header>

        <?php if (!empty($users)) { ?>
            <ul class="members-list" role="list">
                <?php foreach ($users as $user) {
                    $username = (string)($user['username'] ?? '');
                    $displayName = !empty($user['display_name']) ? $user['display_name'] : $username;
                    $profileUrl = BASE_URL . '/profile/' . rawurlencode($username);
                    $isOnline = !empty($user['is_online']);
                    $isUserAdmin = !empty($user['is_admin']) || (($user['role'] ?? null) === 'admin');
                    $commentsCount = (int)($user['comments_count'] ?? 0);
                    $achievementsCount = (int)($user['unlocked_achievements_count'] ?? 0);
                    $postsCount = (int)($user['posts_count'] ?? 0);
                    $avatarInitial = $username !== '' ? mb_strtoupper(mb_substr($username, 0, 1)) : '?';
                    $presenceTitle = $isOnline
                        ? LANG_TEMPLATE_USERS_ONLINE_TITLE
                        : (!empty($user['last_activity_human'])
                            ? sprintf(LANG_TEMPLATE_USERS_WAS_ONLINE, $user['last_activity_human'])
                            : LANG_TEMPLATE_USERS_NO_ACTIVITY);
                ?>
                    <li class="member-card<?php echo $isOnline ? ' member-card--online' : ''; ?>">
                        <div class="member-card__body">
                            <div class="member-card__identity">
                                <a href="<?php echo html($profileUrl); ?>" class="member-card__avatar-link" aria-label="<?php echo html($displayName); ?>">
                                    <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                                        <img class="member-card__avatar"
                                             src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($user['avatar']); ?>"
                                             alt="<?php echo html($displayName); ?>"
                                             loading="lazy">
                                    <?php } else { ?>
                                        <span class="member-card__avatar member-card__avatar--placeholder" aria-hidden="true">
                                            <?php echo html($avatarInitial); ?>
                                        </span>
                                    <?php } ?>
                                    <span class="member-card__presence<?php echo $isOnline ? ' is-online' : ''; ?>"
                                          title="<?php echo html($presenceTitle); ?>"
                                          aria-hidden="true"></span>
                                </a>

                                <div class="member-card__info">
                                    <div class="member-card__name-row">
                                        <h2 class="member-card__name">
                                            <a href="<?php echo html($profileUrl); ?>"><?php echo html($displayName); ?></a>
                                        </h2>
                                        <?php if ($isOnline) { ?>
                                            <span class="member-card__online-badge">
                                                <span class="member-card__online-dot" aria-hidden="true"></span>
                                                <?php echo LANG_TEMPLATE_USERS_ONLINE_BADGE; ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                    <a class="member-card__username" href="<?php echo html($profileUrl); ?>">@<?php echo html($username); ?></a>
                                </div>
                            </div>

                            <div class="member-card__details">
                                <?php if (!empty($user['bio'])) { ?>
                                    <p class="member-card__bio">
                                        <?php echo html(truncate_text(strip_tags($user['bio']), 140)); ?>
                                    </p>
                                <?php } ?>

                                <div class="member-card__badges">
                                    <?php if (!empty($user['groups'])) { ?>
                                        <?php foreach (array_slice($user['groups'], 0, 2) as $group) { ?>
                                            <span class="member-card__group">
                                                <?php echo bloggy_icon('bs', 'people-fill', '11', 'currentColor'); ?>
                                                <?php echo html($group['name']); ?>
                                            </span>
                                        <?php } ?>
                                        <?php if (count($user['groups']) > 2) { ?>
                                            <span class="member-card__group member-card__group--more" title="<?php echo html(implode(', ', array_column(array_slice($user['groups'], 2), 'name'))); ?>">
                                                +<?php echo count($user['groups']) - 2; ?>
                                            </span>
                                        <?php } ?>
                                    <?php } ?>

                                    <?php if ($showLastAchievement && !empty($user['last_achievement'])) { ?>
                                        <span class="member-card__achievement"
                                              title="<?php echo html($user['last_achievement']['tooltip_text'] ?? $user['last_achievement']['name']); ?>"
                                              data-bs-toggle="tooltip">
                                            <?php if (!empty($user['last_achievement']['image'])) { ?>
                                                <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo html($user['last_achievement']['image']); ?>"
                                                     alt=""
                                                     loading="lazy">
                                            <?php } else { ?>
                                                <?php
                                                $iconName = str_replace('bi-', '', $user['last_achievement']['icon'] ?? 'trophy');
                                                echo bloggy_icon('bs', $iconName, '13', 'currentColor');
                                                ?>
                                            <?php } ?>
                                            <span><?php echo html($user['last_achievement']['name']); ?></span>
                                        </span>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="member-card__stats">
                                <?php if ($canViewAdminStats || $isUserAdmin) { ?>
                                    <div class="member-stat">
                                        <span class="member-stat__icon" aria-hidden="true">
                                            <?php echo bloggy_icon('bs', 'file-earmark-text', '17', 'currentColor'); ?>
                                        </span>
                                        <span class="member-stat__content">
                                            <strong class="member-stat__value"><?php echo $postsCount; ?></strong>
                                            <span class="member-stat__label">
                                                <?php echo plural($postsCount, [LANG_TEMPLATE_USERS_POST_1, LANG_TEMPLATE_USERS_POST_2, LANG_TEMPLATE_USERS_POST_3]); ?>
                                            </span>
                                        </span>
                                    </div>
                                <?php } ?>
                                <div class="member-stat">
                                    <span class="member-stat__icon" aria-hidden="true">
                                        <?php echo bloggy_icon('bs', 'chat-square-text', '17', 'currentColor'); ?>
                                    </span>
                                    <span class="member-stat__content">
                                        <strong class="member-stat__value"><?php echo $commentsCount; ?></strong>
                                        <span class="member-stat__label"><?php echo LANG_TEMPLATE_USERS_COMMENT_LABEL; ?></span>
                                    </span>
                                </div>
                                <div class="member-stat">
                                    <span class="member-stat__icon member-stat__icon--achievement" aria-hidden="true">
                                        <?php echo bloggy_icon('bs', 'trophy', '17', 'currentColor'); ?>
                                    </span>
                                    <span class="member-stat__content">
                                        <strong class="member-stat__value"><?php echo $achievementsCount; ?></strong>
                                        <span class="member-stat__label">
                                            <?php echo plural($achievementsCount, [LANG_TEMPLATE_USERS_ACHIEVEMENT_1, LANG_TEMPLATE_USERS_ACHIEVEMENT_2, LANG_TEMPLATE_USERS_ACHIEVEMENT_3]); ?>
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div class="member-card__aside">
                                <div class="member-card__activity<?php echo $isOnline ? ' is-online' : ''; ?>">
                                    <?php if ($isOnline) { ?>
                                        <?php echo bloggy_icon('bs', 'record-fill', '9', 'currentColor'); ?>
                                        <span><?php echo LANG_TEMPLATE_USERS_ONLINE_BADGE; ?></span>
                                    <?php } elseif (!empty($user['last_activity_human'])) { ?>
                                        <?php echo bloggy_icon('bs', 'clock-history', '14', 'currentColor'); ?>
                                        <span><?php echo sprintf(LANG_TEMPLATE_USERS_WAS_ONLINE, $user['last_activity_human']); ?></span>
                                    <?php } else { ?>
                                        <?php echo bloggy_icon('bs', 'clock-history', '14', 'currentColor'); ?>
                                        <span><?php echo LANG_TEMPLATE_USERS_NO_ACTIVITY; ?></span>
                                    <?php } ?>
                                </div>
                                <?php if (!empty($user['registration_days'])) { ?>
                                    <div class="member-card__registration">
                                        <?php echo bloggy_icon('bs', 'calendar3', '13', 'currentColor'); ?>
                                        <span><?php echo sprintf(LANG_TEMPLATE_USERS_DAYS, (int)$user['registration_days']); ?></span>
                                    </div>
                                <?php } ?>
                                <a href="<?php echo html($profileUrl); ?>" class="member-card__profile-link">
                                    <?php echo LANG_TEMPLATE_USERS_PROFILE_BTN; ?>
                                    <?php echo bloggy_icon('bs', 'arrow-right', '15', 'currentColor'); ?>
                                </a>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <div class="members-empty">
                <div class="members-empty__icon" aria-hidden="true">
                    <?php echo bloggy_icon('bs', 'people', '52', 'currentColor'); ?>
                </div>
                <h2 class="members-empty__title"><?php echo LANG_TEMPLATE_USERS_EMPTY_TITLE; ?></h2>
                <p class="members-empty__text"><?php echo LANG_TEMPLATE_USERS_EMPTY_TEXT; ?></p>
                <?php if (!isset($_SESSION['user_id'])) { ?>
                    <a href="<?php echo BASE_URL; ?>/register" class="members-empty__button">
                        <?php echo bloggy_icon('bs', 'person-plus', '17', 'currentColor'); ?>
                        <?php echo LANG_TEMPLATE_USERS_REGISTER_BTN; ?>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (isset($pagination) && $pagination['total_pages'] > 1) { ?>
            <div class="tg-pagination tg-mt-5">
                <?php if ($pagination['current_page'] > 1) { ?>
                    <a href="?page=<?php echo $pagination['current_page'] - 1; ?>" class="tg-page-link">
                        <?php echo bloggy_icon('bs', 'chevron-left', '16', 'currentColor'); ?>
                    </a>
                <?php } ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++) { ?>
                    <a href="?page=<?php echo $i; ?>"
                       class="tg-page-link <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php } ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']) { ?>
                    <a href="?page=<?php echo $pagination['current_page'] + 1; ?>" class="tg-page-link">
                        <?php echo bloggy_icon('bs', 'chevron-right', '16', 'currentColor'); ?>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
