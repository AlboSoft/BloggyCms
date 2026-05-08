-- Migration: Add missing performance indexes
-- Phase 2 Performance Improvements
-- Run this migration on existing installations

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- --------------------------------------------------------
-- Add missing indexes for posts queries
-- --------------------------------------------------------

-- Index for filtering published posts (most common query)
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_status` (`status`);

-- Index for ordering by date (latest posts, archives)
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_created_at` (`created_at`);

-- Composite index for published posts ordered by date (covers common query pattern)
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_status_created` (`status`, `created_at`);

-- Index for category + status queries (posts by category)
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_category_status` (`category_id`, `status`);

-- Index for user posts
ALTER TABLE `{#}posts` ADD INDEX `idx_posts_user_status` (`user_id`, `status`);

-- --------------------------------------------------------
-- Add missing indexes for categories
-- --------------------------------------------------------

-- Unique slug for categories
ALTER TABLE `{#}categories` ADD UNIQUE INDEX `idx_categories_slug` (`slug`);

-- --------------------------------------------------------
-- Add missing indexes for users
-- --------------------------------------------------------

-- Index for status filtering (active users, banned users)
ALTER TABLE `{#}users` ADD INDEX `idx_users_status` (`status`);

-- Index for user activity queries
ALTER TABLE `{#}users` ADD INDEX `idx_users_last_activity` (`last_activity`);

-- --------------------------------------------------------
-- Add missing indexes for tags
-- --------------------------------------------------------

-- Unique slug for tags
ALTER TABLE `{#}tags` ADD UNIQUE INDEX `idx_tags_slug` (`slug`);

-- --------------------------------------------------------
-- Add missing indexes for pages
-- --------------------------------------------------------

-- Index for status filtering (published pages)
ALTER TABLE `{#}pages` ADD INDEX `idx_pages_status` (`status`);

-- Composite for published + ordered
ALTER TABLE `{#}pages` ADD INDEX `idx_pages_status_created` (`status`, `created_at`);

-- --------------------------------------------------------
-- Add missing indexes for comments
-- --------------------------------------------------------

-- Index for approved comments on post
ALTER TABLE `{#}comments` ADD INDEX `idx_comments_post_status` (`post_id`, `status`);

-- Index for comment moderation queue
ALTER TABLE `{#}comments` ADD INDEX `idx_comments_status_created` (`status`, `created_at`);

-- --------------------------------------------------------
-- Add missing indexes for post_tags
-- --------------------------------------------------------

-- Composite index for tag queries
ALTER TABLE `{#}post_tags` ADD INDEX `idx_post_tags_tag_post` (`tag_id`, `post_id`);

-- --------------------------------------------------------
-- Add missing indexes for post_likes
-- --------------------------------------------------------

-- Index for likes count queries
ALTER TABLE `{#}post_likes` ADD INDEX `idx_post_likes_post_count` (`post_id`);

-- --------------------------------------------------------
-- Add missing indexes for bookmarks
-- --------------------------------------------------------

-- Index for user bookmarks
ALTER TABLE `{#}bookmarks` ADD INDEX `idx_bookmarks_user_post` (`user_id`, `created_at`);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
