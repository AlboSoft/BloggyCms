# 🗄️ Модели и база данных

Работа с данными в BloggyCMS намеренно простая: PDO-обёртка `Database`, обычные SQL-запросы с подготовленными параметрами и модели, которые группируют запросы по сущности. Никакой ORM-магии и «магических» миграций.

---

## Класс `Database`

Единственное подключение к базе — синглтон:

```php
<?php
$db = Database::getInstance();
```

| Метод | Что делает | Возвращает |
|---|---|---|
| `query($sql, $params = [])` | Выполнить запрос | `PDOStatement` |
| `fetch($sql, $params = [])` | Одна строка | `array \| false` |
| `fetchAll($sql, $params = [])` | Все строки | `array` |
| `fetchValue($sql, $params = [])` | Первая колонка первой строки | `mixed` |
| `insert($table, array $data)` | Вставка по массиву | `bool` |
| `update($table, array $data, $where)` | Обновление | `int` (число строк) |
| `delete($table, $conditions)` | Удаление | `int` |
| `lastInsertId()` | ID последней вставки | `int` |
| `beginTransaction()` / `commit()` / `rollBack()` | Транзакции | `bool` |
| `getPrefix()` | Префикс таблиц | `string` |

### Примеры

```php
<?php
$db = Database::getInstance();

// Выборка
$posts = $db->fetchAll(
    "SELECT id, title, slug FROM posts WHERE status = ? ORDER BY created_at DESC LIMIT ?",
    ['published', 10]
);

// Одно значение
$count = (int) $db->fetchValue("SELECT COUNT(*) FROM posts WHERE status = ?", ['published']);

// Вставка
$db->insert('reviews', [
    'title'      => $title,
    'content'    => $content,
    'status'     => 'published',
    'created_at' => date('Y-m-d H:i:s'),
]);
$id = (int) $db->lastInsertId();

// Обновление
$db->update('reviews', ['title' => $newTitle], ['id' => $id]);

// Удаление
$db->delete('reviews', ['id' => $id]);
```

> [!WARNING]
> Всегда передавайте значения **параметрами**, а не склейкой строки. Оформление (`ORDER BY {$column}`) — исключение, но только когда значение берётся из белого списка, а не из `$_GET`.

### Транзакции

```php
<?php
$db->beginTransaction();
try {
    $db->insert('reviews', ['title' => 'A', 'status' => 'draft']);
    $db->insert('reviews', ['title' => 'B', 'status' => 'draft']);
    $db->commit();
} catch (\Throwable $e) {
    $db->rollBack();
    DebugLogger::error('Не удалось создать отзывы', ['error' => $e->getMessage()]);
    throw $e;
}
```

### Префикс таблиц

Префикс задаётся при установке (`DB_PREFIX`) и подставляется автоматически в методах `insert/update/delete`. В «сырых» запросах пишите имя таблицы **без** префикса — движок добавляет его сам при выполнении.

```php
<?php
// Так: префикс добавится автоматически
$db->fetchAll("SELECT * FROM reviews");

// Если нужно имя с префиксом вручную
$table = $db->getPrefix() . 'reviews';
```

---

## Модели

Модель — это класс, который живёт рядом с контроллером: `system/controllers/<модуль>/Model.php`. Модель собирает в себе все запросы своей сущности, чтобы экшены оставались читаемыми.

### Контракт `ModelAPI`

```php
<?php

interface ModelAPI
{
    public static function getModelInfo();          // ['name' => ..., 'class' => ..., 'path' => ...]
    public function getAPIMethods();                // список публичных методов
    public function callAPI($method, $args);        // вызов метода по имени
}
```

Проще всего реализовать это через трейт `APIAware`:

```php
<?php

class ReviewModel implements ModelAPI
{
    use APIAware;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getPublished(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM reviews WHERE status = 'published' ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function create(array $data): int
    {
        $this->db->insert('reviews', $data);
        return (int) $this->db->lastInsertId();
    }
}
```

**Правила хорошей модели:**

| Правило | Почему |
|---|---|
| Только работа с данными | Логика и выводы — в экшенах и шаблонах |
| Возвращать массивы, а не HTML | Модель переиспользуется в API, CLI и шаблонах |
| Ограничивать выборки `LIMIT` | Длинные списки нужны почти всегда с пагинацией |
| Явно приводить типы | `(int)`, `(bool)` — защита от неожиданных строк из PDO |

### Ограничение API-методов

Если модель отдаётся во внешний API, список методов можно сузить свойством `allowedAPIMethods`:

```php
<?php

class ReviewModel implements ModelAPI
{
    use APIAware;

    protected $allowedAPIMethods = ['getPublished', 'getById'];
}
```

---

## Хелпер `API` — доступ к моделям по имени

`API` сканирует все модели, реализующие `ModelAPI`, и даёт к ним доступ без ручного подключения класса. Это удобно в хуках, шаблонах и сторонних модулях.

```php
<?php
// Прокси-объект модели: API::<имяМодели>() или API::model('reviews')
$reviews = API::reviews()->getPublished(5);
$reviews = API::model('reviews')->getPublished(5);

// Прямой вызов метода
$reviews = API::call('reviews', 'getPublished', [5]);

// Одной строкой: API::<модель>_<метод>(...)
$reviews = API::reviews_getPublished(5);

// Проверки и служебное
API::hasModel('reviews');            // bool
API::getAvailableModels();           // список всех моделей
API::getModelMethods('reviews');     // доступные методы модели
API::clearCache();                   // сбросить кэш сканирования
```

Имя модели определяется по имени папки контроллера: `system/controllers/posts/Model.php` → `API::posts()`.

> [!TIP]
> `API` — отличный способ развязать модули: ваш блок может получить данные о постах, не подключая класс `PostModel` напрямую.

---

## Схема данных

В поставке 41 таблица. Префикс по умолчанию — `bloggy_` (задаётся при установке). Ключевые таблицы:

| Таблица | Что хранит |
|---|---|
| `posts` | Посты: заголовок, slug, категория, статус, обложка, SEO-поля, приватность, счётчики |
| `pages` | Страницы: иерархия (`parent_id`), контент, статус |
| `categories`, `tags`, `post_tags` | Таксономия |
| `comments` | Комментарии: `post_id`, `parent_id`, статус модерации |
| `users`, `user_groups`, `users_groups`, `group_permissions` | Пользователи, группы, права |
| `user_activity`, `user_sessions_online`, `login_attempts` | Активность, сессии, попытки входа |
| `post_likes`, `bookmarks` | Лайки и закладки |
| `post_blocks`, `page_blocks` | Блоки контента постов и страниц |
| `block_types`, `post_block_settings`, `post_block_presets` | Настройки и пресеты пост-блоков |
| `html_blocks`, `html_block_types`, `template_blocks` | HTML-блоки и их типы |
| `menus` | Меню: структура (дерево пунктов), шаблон вывода, видимость |
| `fields`, `field_values` | Пользовательские поля и значения |
| `fragments`, `fragments_fields`, `fragment_entries` | Фрагменты, их поля и записи |
| `user_achievements`, `user_achievements_data`, `achievement_conditions`, `achievement_assignment_history` | Достижения |
| `notifications`, `debug_logs`, `search_queries`, `queue_tasks` | Уведомления, журнал отладки, поисковые запросы, очередь задач |
| `settings` | Настройки: `group_key` + JSON |
| `installed_addons` | Установленные пакеты |

Подробное описание колонок — в [справочнике таблиц](../reference/tablicy-bd.md).

---

## Соглашения по структуре таблиц

Движок придерживается единых правил, и новым таблицам стоит им следовать:

- первичный ключ — `id INT AUTO_INCREMENT`;
- даты — `created_at`, `updated_at` (`timestamp`/`datetime`);
- принадлежность — `user_id`, `post_id`, `category_id`;
- статусы — `enum` со строчными значениями (`draft`, `published`, `pending`);
- JSON — тип `json` для гибких структур (`settings`, `config`, `structure`);
- внешние ключи — с `ON DELETE CASCADE` там, где запись бессмысленна без родителя;
- кодировка — `utf8mb4` / `utf8mb4_unicode_ci`.

Пример таблицы модуля:

```sql
CREATE TABLE IF NOT EXISTS `bloggy_reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT,
  `rating` TINYINT DEFAULT 5,
  `status` ENUM('draft','published') DEFAULT 'draft',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `status` (`status`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Создавать таблицы своего модуля удобно в `install.php` пакета дополнения (см. [Дополнения и обновления](../administrator/11-dopolneniya-i-obnovleniya.md)) или одноразовым скриптом при разработке.

---

## Работа с настройками из кода

Настройки — это тоже данные, но к ним есть отдельный хелпер:

```php
<?php
// Одно значение
$siteName = SettingsHelper::get('general', 'site_name', 'BloggyCMS');

// Вся группа
$general = SettingsHelper::get('general');

// Сохранить
SettingsHelper::save('controller_reviews', ['per_page' => 20]);

// Слить с текущими
SettingsHelper::merge('controller_reviews', ['require_moderation' => false]);

// Служебное
SettingsHelper::clearCache();
SettingsHelper::getAllGroups();
SettingsHelper::getBaseUrl();
SettingsHelper::getCurrentTemplate();
```

---

## Чек-лист работы с данными

1. Все запросы — с подготовленными параметрами.
2. Модели возвращают массивы и не содержат HTML.
3. Длинные выборки ограничены и поддерживают пагинацию.
4. Массовые изменения обёрнуты в транзакцию.
5. Данные пользователя на выходе экранируются (`html()`).
6. Новые таблицы следуют соглашениям (`id`, `created_at`, `utf8mb4`).
7. Создание таблиц задокументировано в пакете дополнения.

---

[← Свой контроллер](03-svoy-kontroller.md) · [Оглавление](../README.md) · [Формы и поля →](05-formy-i-polya.md)