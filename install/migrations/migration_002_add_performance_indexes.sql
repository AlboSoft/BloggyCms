-- Migration: Add missing performance indexes
-- Phase 2 Performance Improvements
-- Run this migration on existing installations

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

ALTER TABLE `{#}posts` ADD INDEX `idx_posts_status` (`status`);
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_created_at` (`created_at`);
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_status_created` (`status`, `created_at`);
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_category_status` (`category_id`, `status`);
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_user_status` (`user_id`, `status`);
ALTER TABLE `{#}categories` ADD UNIQUE INDEX `idx_categories_slug` (`slug`);
ALTER TABLE `{#}users` ADD INDEX `idx_users_status` (`status`);
ALTER TABLE `{#}users` ADD INDEX `idx_users_last_activity` (`last_activity`);
ALTER TABLE `{#}tags` ADD UNIQUE INDEX `idx_tags_slug` (`slug`);
ALTER TABLE `{#}pages` ADD INDEX `idx_pages_status` (`status`);
ALTER TABLE `{#}pages` ADD INDEX `idx_pages_status_created` (`status`, `created_at`);
ALTER TABLE `{#}comments` ADD INDEX `idx_comments_post_status` (`post_id`, `status`);
ALTER TABLE `{#}comments` ADD INDEX `idx_comments_status_created` (`status`, `created_at`);
ALTER TABLE `{#}post_tags` ADD INDEX `idx_post_tags_tag_post` (`tag_id`, `post_id`);
ALTER TABLE `{#}post_likes` ADD INDEX `idx_post_likes_post_count` (`post_id`);
ALTER TABLE `{#}bookmarks` ADD INDEX `idx_bookmarks_user_post` (`user_id`, `created_at`);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
