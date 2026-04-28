SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}achievement_assignment_history`
--

CREATE TABLE IF NOT EXISTS `{#}achievement_assignment_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `achievement_id` (`achievement_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_assigned_at` (`assigned_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}achievement_conditions`
--

CREATE TABLE IF NOT EXISTS `{#}achievement_conditions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `achievement_id` int NOT NULL,
  `condition_type` enum('registration_days','comments_count','likes_count','bookmarks_count','login_days') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator` enum('>','<','=','>=','<=','!=') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_achievement_id` (`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}block_types`
--

CREATE TABLE IF NOT EXISTS `{#}block_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_name` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(50) DEFAULT 'basic',
  `icon` varchar(100) DEFAULT 'bi-square',
  `is_active` tinyint(1) DEFAULT '1',
  `config` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_name` (`system_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `{#}block_types`
--

INSERT IGNORE INTO `{#}block_types` (`id`, `system_name`, `name`, `description`, `category`, `icon`, `is_active`, `config`, `created_at`) VALUES
(1, 'HeaderBlock', 'Заголовок', 'Блок для заголовков разных уровней', 'basic', 'bi-type-h1', 1, '{"version": "1.0.0"}', '2025-10-29 12:14:31'),
(2, 'ImageBlock', 'Изображение', 'Блок для вставки изображений с подписью', 'basic', 'bi-image', 1, '{"version": "1.0.0"}', '2025-10-29 12:14:31'),
(3, 'TextBlock', 'Текст', 'Блок для текстового контента', 'basic', 'bi-text-paragraph', 1, '{"version": "1.0.0"}', '2025-10-29 12:14:31');

-- --------------------------------------------------------

--
-- Структура таблицы `{#}debug_logs`
-- Таблица для хранения данных Debug Controller
--

CREATE TABLE IF NOT EXISTS `{#}debug_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL COMMENT 'E_ERROR, E_WARNING, E_NOTICE, Exception',
  `code` int(11) DEFAULT NULL COMMENT 'Код ошибки',
  `message` text NOT NULL COMMENT 'Сообщение об ошибке',
  `file` varchar(512) DEFAULT NULL COMMENT 'Файл, где произошла ошибка',
  `line` int(11) DEFAULT NULL COMMENT 'Строка в файле',
  `trace` longtext COMMENT 'Стек вызовов (JSON)',
  `context` longtext COMMENT 'Контекст (переменные, сессии, запрос) (JSON)',
  `url` text COMMENT 'URL, на котором произошла ошибка',
  `method` varchar(10) DEFAULT NULL COMMENT 'HTTP метод (GET, POST)',
  `ip` varchar(45) DEFAULT NULL COMMENT 'IP адрес пользователя',
  `user_id` int(11) DEFAULT NULL COMMENT 'ID пользователя, если авторизован',
  `is_fixed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Пометка, что ошибка исправлена',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `created_at` (`created_at`),
  KEY `is_fixed` (`is_fixed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Логи ошибок для режима отладки';

--
-- Структура таблицы `{#}installed_addons`
-- Таблица для хранения установленных пакетов дополнений
--

CREATE TABLE IF NOT EXISTS `{#}installed_addons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_name` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `version_major` int NOT NULL DEFAULT 1,
  `version_minor` int NOT NULL DEFAULT 0,
  `version_build` int NOT NULL DEFAULT 0,
  `version_string` varchar(50) NOT NULL,
  `version_date` varchar(20) DEFAULT NULL,
  `author_name` varchar(255) DEFAULT NULL,
  `author_url` varchar(500) DEFAULT NULL,
  `author_email` varchar(255) DEFAULT NULL,
  `description` text,
  `type` enum('install','update') NOT NULL DEFAULT 'install',
  `installed_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_system_name` (`system_name`),
  KEY `idx_system_name` (`system_name`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Структура таблицы `{#}bookmarks`
--

CREATE TABLE IF NOT EXISTS `{#}bookmarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `post_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bookmark` (`user_id`,`post_id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}categories`
--

CREATE TABLE IF NOT EXISTS `{#}categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `canonical_url` varchar(500) DEFAULT NULL,
  `noindex` tinyint(1) NOT NULL DEFAULT '0',
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `password_protected` tinyint(1) DEFAULT '0',
  `password` varchar(255) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}comments`
--

CREATE TABLE IF NOT EXISTS `{#}comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `author_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','spam') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}fields`
--

CREATE TABLE IF NOT EXISTS `{#}fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `system_name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `entity_type` enum('post','page','category','user') NOT NULL,
  `description` text,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `show_in_post` tinyint(1) NOT NULL DEFAULT '1',
  `show_in_list` tinyint(1) NOT NULL DEFAULT '0',
  `config` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_name_entity` (`system_name`,`entity_type`),
  KEY `entity_type` (`entity_type`),
  KEY `is_active` (`is_active`),
  KEY `show_in_post` (`show_in_post`),
  KEY `show_in_list` (`show_in_list`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}field_values`
--

CREATE TABLE IF NOT EXISTS `{#}field_values` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_id` int NOT NULL,
  `entity_type` enum('post','page','category','user') NOT NULL,
  `entity_id` int NOT NULL,
  `value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field_entity` (`field_id`,`entity_type`,`entity_id`),
  KEY `entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}forms`
--

CREATE TABLE IF NOT EXISTS `{#}forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `template` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `structure` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `success_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `notifications` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `actions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}form_files`
--

CREATE TABLE IF NOT EXISTS `{#}form_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL,
  `field_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_submission_id` (`submission_id`),
  KEY `idx_field_name` (`field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}form_submissions`
--

CREATE TABLE IF NOT EXISTS `{#}form_submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form_id` int NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `referer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('new','read','processed','spam') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_form_id` (`form_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}group_permissions`
--

CREATE TABLE IF NOT EXISTS `{#}group_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_id` int DEFAULT NULL,
  `permission_key` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}html_blocks`
--

CREATE TABLE IF NOT EXISTS `{#}html_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type_id` int DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `css_files` json DEFAULT NULL,
  `js_files` json DEFAULT NULL,
  `inline_css` text,
  `inline_js` text,
  `template` varchar(50) NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `type_id` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}html_block_types`
--

CREATE TABLE IF NOT EXISTS `{#}html_block_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `description` text,
  `template` varchar(50) DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_name` (`system_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}login_attempts`
--

CREATE TABLE IF NOT EXISTS `{#}login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int DEFAULT '0',
  `last_attempt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `blocked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_blocked_until` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}menus`
--

CREATE TABLE IF NOT EXISTS `{#}menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `structure` text NOT NULL,
  `visibility_settings` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}notifications`
--

CREATE TABLE IF NOT EXISTS `{#}notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_read` tinyint(1) DEFAULT '0',
  `user_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}pages`
--

CREATE TABLE IF NOT EXISTS `{#}pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}page_blocks`
--

CREATE TABLE IF NOT EXISTS `{#}page_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_id` int NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `type` (`type`),
  KEY `order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}page_templates`
--

CREATE TABLE IF NOT EXISTS `{#}page_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `route` varchar(255) NOT NULL,
  `controller` varchar(100) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_file` varchar(255) DEFAULT 'layout.php',
  `description` text,
  `priority` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `route` (`route`),
  KEY `idx_route` (`route`),
  KEY `idx_template` (`template_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}password_resets`
--

CREATE TABLE IF NOT EXISTS `{#}password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `used` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Структура таблицы `{#}posts`
--

CREATE TABLE IF NOT EXISTS `{#}posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `short_description` text,
  `slug` varchar(255) NOT NULL,
  `category_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `allow_comments` tinyint(1) DEFAULT '1',
  `featured_image` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `seo_title` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `password_protected` tinyint(1) DEFAULT '0',
  `password` varchar(255) DEFAULT NULL,
  `show_to_groups` text,
  `hide_from_groups` text,
  `views` int DEFAULT '0',
  `rating` int DEFAULT '0',
  `likes_count` int DEFAULT '0',
  `is_adult` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}post_blocks`
--

CREATE TABLE IF NOT EXISTS `{#}post_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `content` longtext,
  `settings` text,
  `order` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}post_block_presets`
--

CREATE TABLE IF NOT EXISTS `{#}post_block_presets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_system_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preset_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preset_template` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_preset` (`block_system_name`,`preset_name`),
  KEY `idx_block_system_name` (`block_system_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}post_block_settings`
--

CREATE TABLE IF NOT EXISTS `{#}post_block_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_name` varchar(255) NOT NULL,
  `enable_in_posts` tinyint(1) DEFAULT '1',
  `enable_in_pages` tinyint(1) DEFAULT '1',
  `template` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_name` (`system_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}post_likes`
--

CREATE TABLE IF NOT EXISTS `{#}post_likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}post_tags`
--

CREATE TABLE IF NOT EXISTS `{#}post_tags` (
  `post_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}search_queries`
--

CREATE TABLE IF NOT EXISTS `{#}search_queries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `query` varchar(255) NOT NULL,
  `count` int DEFAULT '1',
  `last_searched_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `query` (`query`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}settings`
--

CREATE TABLE IF NOT EXISTS `{#}settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_key` varchar(50) NOT NULL,
  `settings` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}tags`
--

CREATE TABLE IF NOT EXISTS `{#}tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}template_blocks`
--

CREATE TABLE IF NOT EXISTS `{#}template_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `template_id` int NOT NULL,
  `html_block_id` int NOT NULL,
  `position` varchar(100) NOT NULL,
  `sort_order` int DEFAULT '0',
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `html_block_id` (`html_block_id`),
  KEY `idx_template_position` (`template_id`,`position`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}users`
--

CREATE TABLE IF NOT EXISTS `{#}users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `display_name` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.jpg',
  `bio` text,
  `website` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','banned') DEFAULT 'active',
  `last_admin_ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Структура таблицы `{#}fragments`
--
CREATE TABLE `{#}fragments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `system_name` varchar(100) NOT NULL COMMENT 'Системное имя (для шорткодов)',
    `name` varchar(255) NOT NULL COMMENT 'Название фрагмента',
    `description` text COMMENT 'Описание',
    `css_files` text COMMENT 'CSS файлы (JSON)',
    `js_files` text COMMENT 'JS файлы (JSON)',
    `inline_css` text COMMENT 'Инлайн CSS',
    `inline_js` text COMMENT 'Инлайн JS',
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `system_name` (`system_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------

--
-- Структура таблицы `{#}fragment_entries`
--
CREATE TABLE `{#}fragment_entries` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `fragment_id` int(11) NOT NULL COMMENT 'ID фрагмента',
    `data` text COMMENT 'Данные записи (JSON)',
    `sort_order` int(11) DEFAULT 0 COMMENT 'Порядок сортировки',
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fragment_id` (`fragment_id`),
    KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Структура таблицы `{#}fragments_fields`
--
CREATE TABLE IF NOT EXISTS `{#}fragments_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `fragment_id` int(11) NOT NULL,
    `system_name` varchar(100) NOT NULL,
    `name` varchar(255) NOT NULL,
    `type` varchar(50) NOT NULL DEFAULT 'string',
    `description` text,
    `is_required` tinyint(1) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `show_in_list` tinyint(1) NOT NULL DEFAULT 0,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `config` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fragment_id` (`fragment_id`),
    UNIQUE KEY `unique_system_name_per_fragment` (`fragment_id`, `system_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Структура таблицы `{#}queue_tasks`
--

CREATE TABLE IF NOT EXISTS `{#}queue_tasks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `task` varchar(100) NOT NULL,
    `data` text NOT NULL,
    `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
    `attempts` int(11) DEFAULT 0,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `processed_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}users_groups`
--

CREATE TABLE IF NOT EXISTS `{#}users_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_group` (`user_id`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}user_achievements`
--

CREATE TABLE IF NOT EXISTS `{#}user_achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'trophy',
  `icon_color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#0088cc',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('auto','manual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'auto',
  `is_active` tinyint(1) DEFAULT '1',
  `priority` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_achievement_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}user_achievements_data`
--

CREATE TABLE IF NOT EXISTS `{#}user_achievements_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `progress` int DEFAULT '0',
  `max_value` int DEFAULT '100',
  `is_unlocked` tinyint(1) DEFAULT '0',
  `unlocked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_achievement` (`user_id`,`achievement_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_achievement_id` (`achievement_id`),
  KEY `idx_is_unlocked` (`is_unlocked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}user_activity`
--

CREATE TABLE IF NOT EXISTS `{#}user_activity` (
  `user_id` int NOT NULL,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}user_groups`
--

CREATE TABLE IF NOT EXISTS `{#}user_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `{#}user_sessions_online`
--

CREATE TABLE IF NOT EXISTS `{#}user_sessions_online` (
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_user_activity` (`user_id`,`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `{#}achievement_assignment_history`
--
ALTER TABLE `{#}achievement_assignment_history`
  ADD CONSTRAINT `fk_{#}achievement_history_user` FOREIGN KEY (`user_id`) REFERENCES `{#}users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_{#}achievement_history_achievement` FOREIGN KEY (`achievement_id`) REFERENCES `{#}user_achievements` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}achievement_conditions`
--
ALTER TABLE `{#}achievement_conditions`
  ADD CONSTRAINT `fk_{#}achievement_conditions_achievement` FOREIGN KEY (`achievement_id`) REFERENCES `{#}user_achievements` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}bookmarks`
--
ALTER TABLE `{#}bookmarks`
  ADD CONSTRAINT `fk_{#}bookmarks_user` FOREIGN KEY (`user_id`) REFERENCES `{#}users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_{#}bookmarks_post` FOREIGN KEY (`post_id`) REFERENCES `{#}posts` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}comments`
--
ALTER TABLE `{#}comments`
  ADD CONSTRAINT `fk_{#}comments_post` FOREIGN KEY (`post_id`) REFERENCES `{#}posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_{#}comments_user` FOREIGN KEY (`user_id`) REFERENCES `{#}users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_{#}comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `{#}comments` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}form_files`
--
ALTER TABLE `{#}form_files`
  ADD CONSTRAINT `fk_{#}form_files_submission` FOREIGN KEY (`submission_id`) REFERENCES `{#}form_submissions` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}form_submissions`
--
ALTER TABLE `{#}form_submissions`
  ADD CONSTRAINT `fk_{#}form_submissions_form` FOREIGN KEY (`form_id`) REFERENCES `{#}forms` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}group_permissions`
--
ALTER TABLE `{#}group_permissions`
  ADD CONSTRAINT `fk_{#}group_permissions_group` FOREIGN KEY (`group_id`) REFERENCES `{#}user_groups` (`id`);

--
-- Ограничения внешнего ключа таблицы `{#}html_blocks`
--
ALTER TABLE `{#}html_blocks`
  ADD CONSTRAINT `fk_{#}html_blocks_type` FOREIGN KEY (`type_id`) REFERENCES `{#}html_block_types` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `{#}page_blocks`
--
ALTER TABLE `{#}page_blocks`
  ADD CONSTRAINT `fk_{#}page_blocks_page` FOREIGN KEY (`page_id`) REFERENCES `{#}pages` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}password_resets`
--
ALTER TABLE `{#}password_resets`
  ADD CONSTRAINT `fk_{#}password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `{#}users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}posts`
--
ALTER TABLE `{#}posts`
  ADD CONSTRAINT `fk_{#}posts_category` FOREIGN KEY (`category_id`) REFERENCES `{#}categories` (`id`),
  ADD CONSTRAINT `fk_{#}posts_user` FOREIGN KEY (`user_id`) REFERENCES `{#}users` (`id`);

--
-- Ограничения внешнего ключа таблицы `{#}post_likes`
--
ALTER TABLE `{#}post_likes`
  ADD CONSTRAINT `fk_{#}post_likes_post` FOREIGN KEY (`post_id`) REFERENCES `{#}posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_{#}post_likes_user` FOREIGN KEY (`user_id`) REFERENCES `{#}users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}post_tags`
--
ALTER TABLE `{#}post_tags`
  ADD CONSTRAINT `fk_{#}post_tags_post` FOREIGN KEY (`post_id`) REFERENCES `{#}posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_{#}post_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `{#}tags` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}template_blocks`
--
ALTER TABLE `{#}template_blocks`
  ADD CONSTRAINT `fk_{#}template_blocks_template` FOREIGN KEY (`template_id`) REFERENCES `{#}page_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_{#}template_blocks_block` FOREIGN KEY (`html_block_id`) REFERENCES `{#}html_blocks` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `{#}users_groups`
--
ALTER TABLE `{#}users_groups`
  ADD CONSTRAINT `fk_{#}users_groups_user` FOREIGN KEY (`user_id`) REFERENCES `{#}users` (`id`),
  ADD CONSTRAINT `fk_{#}users_groups_group` FOREIGN KEY (`group_id`) REFERENCES `{#}user_groups` (`id`);

--
-- Ограничения внешнего ключа таблицы `{#}user_achievements_data`
--
ALTER TABLE `{#}user_achievements_data`
  ADD CONSTRAINT `fk_{#}achievements_data_user` FOREIGN KEY (`user_id`) REFERENCES `{#}users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_{#}achievements_data_achievement` FOREIGN KEY (`achievement_id`) REFERENCES `{#}user_achievements` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
