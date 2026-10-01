# 🗃️ Таблицы базы данных

Схема базы BloggyCMS: **41 таблица**. Префикс по умолчанию — `bloggy_` (задаётся при установке, в этом документе опущен).

Все таблицы — `InnoDB`, `utf8mb4_unicode_ci`.

---

## Полный список

| # | Таблица | Назначение |
|---|---|---|
| 1 | `posts` | Посты |
| 2 | `pages` | Страницы |
| 3 | `categories` | Категории |
| 4 | `tags` | Теги |
| 5 | `post_tags` | Связь постов и тегов |
| 6 | `comments` | Комментарии |
| 7 | `users` | Пользователи |
| 8 | `user_groups` | Группы пользователей |
| 9 | `users_groups` | Пользователи в группах |
| 10 | `group_permissions` | Права групп |
| 11 | `user_activity` | Активность пользователей |
| 12 | `user_sessions_online` | Онлайн-сессии |
| 13 | `login_attempts` | Попытки входа и блокировки |
| 14 | `password_resets` | Токены восстановления пароля |
| 15 | `bookmarks` | Закладки |
| 16 | `post_likes` | Лайки постов |
| 17 | `post_blocks` | Блоки постов |
| 18 | `page_blocks` | Блоки страниц |
| 19 | `block_types` | Типы блоков |
| 20 | `post_block_settings` | Настройки пост-блоков |
| 21 | `post_block_presets` | Пресеты пост-блоков |
| 22 | `html_blocks` | HTML-блоки |
| 23 | `html_block_types` | Типы HTML-блоков |
| 24 | `template_blocks` | Привязка блоков к шаблонам страниц |
| 25 | `page_templates` | Шаблоны маршрутов страниц |
| 26 | `menus` | Меню |
| 27 | `fields` | Пользовательские поля |
| 28 | `field_values` | Значения пользовательских полей |
| 29 | `fragments` | Фрагменты |
| 30 | `fragments_fields` | Поля фрагментов |
| 31 | `fragment_entries` | Записи фрагментов |
| 32 | `user_achievements` | Достижения |
| 33 | `user_achievements_data` | Прогресс и выдача достижений |
| 34 | `achievement_conditions` | Условия автоматических достижений |
| 35 | `achievement_assignment_history` | История ручной выдачи |
| 36 | `notifications` | Уведомления |
| 37 | `settings` | Настройки (JSON по группам) |
| 38 | `search_queries` | Поисковые запросы |
| 39 | `debug_logs` | Журнал отладки |
| 40 | `queue_tasks` | Очередь задач (IndexNow и др.) |
| 41 | `installed_addons` | Установленные пакеты |

---

## Основные таблицы

### `posts`

| Колонка | Тип | Что это |
|---|---|---|
| `id` | int | Идентификатор |
| `title` | varchar(255) | Заголовок |
| `short_description` | text | Анонс |
| `slug` | varchar | Адрес |
| `category_id` | int | Категория |
| `user_id` | int | Автор |
| `status` | enum | `draft` / `published` |
| `allow_comments` | tinyint | Разрешить комментарии |
| `featured_image` | varchar(255) | Обложка |
| `show_cover_in_list`, `show_cover_in_post` | tinyint | Где показывать обложку |
| `meta_description`, `seo_title`, `meta_keywords` | text/varchar | SEO-поля |
| `password_protected`, `password` | tinyint/varchar | Защита паролем |
| `show_to_groups`, `hide_from_groups` | text | Видимость по группам |
| `is_adult` | tinyint | Контент 18+ |
| `views`, `rating`, `likes_count` | int | Счётчики |
| `created_at`, `updated_at` | timestamp | Даты |

### `pages`

`id`, `parent_id` (иерархия), `title`, `slug`, `content`, `status`, `created_at`, `updated_at`.

### `categories`

`id`, `name`, `slug`, `description`, `meta_title`, `meta_description`, `canonical_url`, `noindex`, `image`, `password_protected`, `password`, `sort_order`, `created_at`, `updated_at`.

### `tags`

`id`, `name`, `slug`, `image`, `description`, `created_at`. Связь с постами — в `post_tags` (`post_id`, `tag_id`).

### `comments`

`id`, `post_id`, `user_id`, `parent_id` (ответы), `author_name`, `author_email`, `content`, `status` (`pending` / `approved`), `created_at`, `updated_at`.

### `users`

| Колонка | Что это |
|---|---|
| `id`, `username`, `email`, `password` | Учётные данные |
| `display_name`, `avatar`, `bio`, `website` | Профиль |
| `is_admin` | Доступ в админку |
| `status` | `active` / `banned` |
| `last_login`, `last_activity`, `last_admin_ip` | Активность |
| `created_at` | Регистрация |

### `settings`

| Колонка | Что это |
|---|---|
| `group_key` | Ключ группы: `general`, `site`, `controller_posts`, `seo_meta`, … |
| `settings` | JSON со значениями |

```php
<?php
$general = SettingsHelper::get('general');   // уже декодированный массив
```

### `fields` / `field_values`

`fields`: `id`, `name`, `system_name`, `type`, `entity_type` (`post`/`page`/`category`/`user`), `description`, `is_required`, `is_active`, `sort_order`, `show_in_post`, `show_in_list`, `config` (JSON), даты.

`field_values`: `field_id`, `entity_type`, `entity_id`, `value` — значения по сущностям.

### Блоки

| Таблица | Колонки |
|---|---|
| `post_blocks` | `post_id`, `type`, `content`, `settings`, `order` |
| `page_blocks` | `page_id`, `type`, `content`, `settings`, `order` |
| `block_types` | `system_name`, `name`, `category`, `icon`, `is_active`, `config` |
| `post_block_settings` | `system_name`, `enable_in_posts`, `enable_in_pages`, `template` |
| `post_block_presets` | `block_system_name`, `preset_name`, `preset_template` |
| `html_blocks` | `name`, `slug`, `content`, `type_id`, `settings`, `css_files`, `js_files`, `inline_css`, `inline_js`, `template` |
| `html_block_types` | `name`, `system_name`, `description`, `template`, `is_active` |
| `template_blocks` | `template_id`, `html_block_id`, `position`, `sort_order`, `settings` |
| `page_templates` | `route`, `controller`, `action`, `template_name`, `template_file`, `priority` |

### Фрагменты

| Таблица | Колонки |
|---|---|
| `fragments` | `system_name`, `name`, `description`, `css_files`, `js_files`, `inline_css`, `inline_js`, `status` |
| `fragments_fields` | `fragment_id`, `system_name`, `name`, `type`, `config`, `sort_order`, `is_required`, `is_active`, `show_in_list` |
| `fragment_entries` | Записи фрагмента (значения полей) |

### Пользователи и права

| Таблица | Колонки |
|---|---|
| `user_groups` | `name`, `description`, `is_default` |
| `users_groups` | `user_id`, `group_id` |
| `group_permissions` | `group_id`, `permission_key` |
| `user_activity` | `user_id`, `last_activity`, `session_id`, `ip_address`, `user_agent` |
| `user_sessions_online` | `session_id`, `user_id`, `last_activity` |
| `login_attempts` | `ip_address`, `attempts`, `last_attempt`, `blocked_until` |
| `password_resets` | Токены сброса пароля |

### Достижения

| Таблица | Колонки |
|---|---|
| `user_achievements` | `name`, `description`, `icon`, `icon_color`, `image`, `type` (`auto`/`manual`), `is_active`, `priority` |
| `user_achievements_data` | `user_id`, `achievement_id`, `progress`, `max_value`, `is_unlocked`, `unlocked_at` |
| `achievement_conditions` | `achievement_id`, `condition_type`, `operator`, `value` |
| `achievement_assignment_history` | `user_id`, `achievement_id`, `admin_id`, `reason`, `assigned_at` |

Типы условий: `registration_days`, `comments_count`, `likes_count`, `bookmarks_count`, `login_days`.

### Меню

`menus`: `name`, `template` (где выводить), `structure` (JSON с деревом пунктов), `visibility_settings`, `use_custom_template`, `custom_template`, `status`.

> [!NOTE]
> Отдельной таблицы пунктов нет — дерево хранится в `structure`.

### Служебные

| Таблица | Колонки |
|---|---|
| `notifications` | `type`, `title`, `message`, `data`, `is_read`, `user_id`, `created_by`, `read_at` |
| `search_queries` | `query`, `count`, `last_searched_at` |
| `debug_logs` | `type`, `code`, `message`, `file`, `line`, `trace`, `context`, `url`, `method`, `ip`, `user_id`, `is_fixed` |
| `queue_tasks` | `task`, `data`, `status`, `attempts`, `processed_at` |
| `installed_addons` | `system_name`, `title`, версии (`version_major/minor/build/string/date`), `author_*`, `description`, `type`, `installed_at`, `is_active` |

---

## Полезные запросы

```sql
-- Сколько постов по статусам
SELECT status, COUNT(*) FROM bloggy_posts GROUP BY status;

-- Самые популярные теги
SELECT t.name, COUNT(*) AS cnt
FROM bloggy_post_tags pt JOIN bloggy_tags t ON t.id = pt.tag_id
GROUP BY t.id ORDER BY cnt DESC LIMIT 10;

-- Права конкретной группы
SELECT permission_key FROM bloggy_group_permissions WHERE group_id = 2;

-- Настройки модуля
SELECT settings FROM bloggy_settings WHERE group_key = 'controller_posts';

-- Незакрытые ошибки за неделю
SELECT type, COUNT(*) FROM bloggy_debug_logs
WHERE is_fixed = 0 AND created_at > NOW() - INTERVAL 7 DAY
GROUP BY type;

-- Очередь задач в ожидании
SELECT task, COUNT(*) FROM bloggy_queue_tasks WHERE status = 'pending' GROUP BY task;
```

---

## Резервное копирование схемы

```bash
# Только структура (полезно для разработки)
mysqldump --no-data -u bloggy -p bloggycms > schema.sql

# Данные одной таблицы
mysqldump -u bloggy -p bloggycms bloggy_settings > settings.sql

# Полный дамп
mysqldump -u bloggy -p bloggycms > bloggycms-$(date +%F).sql
```

> [!TIP]
> Перед экспериментами с таблицами (`ALTER`, массовые `UPDATE`) делайте дамп только затронутых таблиц — это быстро и снимает большую часть рисков.

---

[← Маршруты](marshruty.md) · [Оглавление](../README.md) · [FAQ и типовые ошибки →](faq.md)