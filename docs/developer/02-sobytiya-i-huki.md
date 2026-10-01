# 🪝 События и хуки

События — главный инструмент расширения BloggyCMS. Они позволяют реагировать на действия движка (создан пост, сохранён блок) и изменять данные «на лету» — не трогая ни один файл ядра.

Реализация — класс `Event` в `system/core/Event.php`.

---

## Три метода, которых достаточно

```php
<?php
// 1. Подписаться на событие
Event::listen(string $event, callable $callback, int $priority = 10, int $acceptedArgs = 1);

// 2. Вызвать событие (обычно это делает движок, не вы)
Event::trigger(string $event, array $args = []);

// 3. Отфильтровать значение: каждый слушатель получает его и возвращает изменённым
Event::filter(string $event, mixed $value, array $args = []);
```

Параметры `listen()`:

| Параметр | Значение |
|---|---|
| `$event` | Имя события: `post.created`, `admin.menu.items` |
| `$callback` | Функция или замыкание |
| `$priority` | Чем **выше** число, тем **раньше** выполнится (по умолчанию 10) |
| `$acceptedArgs` | Сколько аргументов передать в callback |

Дополнительно: `Event::unlisten($event, $callback, $priority)`, `Event::hasListeners($event)`, `Event::initialize()`.

> [!NOTE]
> Подписки, сделанные до инициализации движка, складываются в «отложенные» и регистрируются автоматически при `Event::initialize()` — это происходит в `index.php` перед запуском приложения.

---

## Где размещать слушатели

Файлы в папке `hooks/` любого контроллера подключаются автоматически при старте:

```text
system/controllers/my_module/hooks/events.php
```

```php
<?php
// Ничего не подключаем и не импортируем — хуки загружаются движком

Event::listen('post.created', function ($postId, $data) {
    Logger::info('Создан пост #' . $postId);
}, 10, 2);
```

> [!TIP]
> Один модуль — один повод для хука. Папка `hooks/` грузится всегда, поэтому не делайте там тяжёлых операций: только объявление подписок.

---

## Примеры

### Реакция на создание поста

Событие `post.created` передаёт `[$postId, $data]`:

```php
<?php
Event::listen('post.created', function ($postId, $data) {
    // Отправим уведомление в Telegram-канал (условно)
    $title = $data['title'] ?? 'Без названия';
    Logger::info("Новый пост: {$title} (#{$postId})");
}, 10, 2);
```

### Изменение значения «на выходе»

Фильтр работает как конвейер: что вернул последний слушатель — то и получил вызывающий код.

```php
<?php
Event::filter('controller.render.content', function ($content, $context) {
    // Прокинем счётчик символов в комментарий HTML (осторожно: только для отладки)
    return $content;
}, 10, 2);
```

### Своя карточка на дашборде админки

`admin.dashboard.stats_cards` — точка расширения дашборда. Слушатель может вернуть HTML, который вставится в сетку карточек:

```php
<?php
Event::listen('admin.dashboard.stats_cards', function ($stats, $controller) {
    $count = Database::getInstance()->fetchValue("SELECT COUNT(*) FROM `my_table`");
    return '<div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-value">' . (int) $count . '</div>
                    <div class="stat-label">Записей в моём модуле</div>
                </div>
            </div>';
}, 10, 2);
```

### Пункт в меню админки

Фильтр `admin.menu.items` получает массив пунктов меню — можно добавить свой:

```php
<?php
Event::filter('admin.menu.items', function (array $items) {
    $items[] = [
        'section'  => 'my-module',
        'url'      => ADMIN_URL . '/my-module',
        'icon'     => 'grid-1x2',
        'title'    => 'Мой модуль',
        'priority' => 95,
    ];
    return $items;
}, 10, 1);
```

Подпись `section` используется для подсветки активного пункта (`is_admin_section_active()`), `priority` — для порядка в списке.

### Дополнительные данные в статистике

```php
<?php
Event::filter('admin.dashboard.stats', function (array $stats, array $context) {
    $stats['my_metric'] = 42;
    return $stats;
}, 10, 2);
```

### Правка данных перед вставкой в базу

`Database::insert()` вызывает события динамически, по имени таблицы с префиксом:

```php
<?php
// Перед вставкой: изменить данные
Event::filter('db.insert.before.' . DB_PREFIX . 'my_table', function (array $data) {
    $data['created_at'] = date('Y-m-d H:i:s');
    return $data;
}, 10, 1);

// После вставки: узнать id и данные
Event::trigger('db.insert.after.' . DB_PREFIX . 'my_table'); // вызывается движком
Event::listen('db.insert.after.' . DB_PREFIX . 'my_table', function (array $payload) {
    Logger::info('Вставлена запись #' . $payload['id']);
}, 10, 1);
```

### Управление sitemap

События `seo.sitemap.post_url`, `seo.sitemap.page_url`, `seo.sitemap.category_url`, `seo.sitemap.tag_url` позволяют изменить адрес или исключить запись из карты:

```php
<?php
Event::trigger('seo.sitemap.post_url', [
    'post' => $post,
    'url'  => &$loc,   // адрес — по ссылке
    'skip' => &$skip,  // поставить true, чтобы исключить
]);
```

```php
<?php
// Пример: исключить посты из категории «Черновики» из sitemap
Event::listen('seo.sitemap.post_url', function (array $payload) {
    if (($payload['post']['category_id'] ?? 0) === 13) {
        $payload['skip'] = true;
    }
}, 10, 1);
```

---

## Полный список событий движка

### Жизненный цикл и рендеринг

| Событие | Тип | Аргументы | Когда |
|---|---|---|---|
| `app.init` | trigger | `['db' => $db, 'app' => null]` | После загрузки ядра, до `App::run()` |
| `controller.render.before` | trigger | `['template', 'data', 'controller']` | Перед подключением шаблона |
| `controller.render.content` | **filter** | `$content, ['template', 'data', 'controller']` | После рендера шаблона, до layout |
| `controller.render.after` | trigger | `['template', 'content' => &$content, 'data']` | Перед подключением layout |

### Контент

| Событие | Тип | Аргументы |
|---|---|---|
| `post.created` | trigger | `[$postId, $data]` |
| `post.updated` | trigger | `[$id, $oldPost, $data]` |
| `post.status_changed` | trigger | `[$id, $oldStatus, $newStatus]` |
| `post.deleted` | trigger | `[$id, $title, $slug]` |
| `page.created` | trigger | `[$pageId, $title, $slug, $data]` |
| `page.updated` | trigger | `[$id, $title, $slug, $data]` |
| `page.deleted` | trigger | `[$id, ...]` |
| `category.created` | trigger | `[$categoryId, $name, $slug, $data]` |
| `category.updated` | trigger | `[$id, $name, $slug, $data]` |
| `category.deleted` | trigger | `[$id, ...]` |
| `tag.created` | trigger | `[$tagId, $name, $slug, $data]` |
| `tag.updated` | trigger | `[$id, $name, $slug, $data]` |
| `tag.deleted` | trigger | `[$id, ...]` |
| `html_block.saved` | trigger | `['id' => $id, 'action' => 'create'\|'update']` |
| `html_block.deleted` | trigger | `['id' => $id]` |

> [!TIP]
> События контента — рабочая лошадка модуля SEO: именно на них пересобираются `sitemap.xml` и `rss.xml` (`system/controllers/seo/hooks/events.php`).

### Админка

| Событие | Тип | Аргументы | Назначение |
|---|---|---|---|
| `admin.dashboard.stats` | **filter** | `$stats, ['db', 'controller']` | Добавить метрики на дашборд |
| `admin.dashboard.stats_cards` | trigger | `[$stats, $controller]` | Вставить HTML-карточку, вернуть строку |
| `admin.menu.items` | **filter** | `$items` | Добавить/изменить пункт меню админки |

### База данных

| Событие | Тип | Аргументы |
|---|---|---|
| `db.insert.before.<таблица>` | **filter** | `$data` |
| `db.insert.after.<таблица>` | trigger | `['id', 'data', 'result']` |

`<таблица>` — имя с префиксом, например `db.insert.after.bloggy_posts`.

### SEO

| Событие | Тип | Аргументы |
|---|---|---|
| `seo.sitemap.post_url` | trigger | `['post', 'url' => &$url, 'skip' => &$skip]` |
| `seo.sitemap.page_url` | trigger | `['page', 'url' => &$url, 'skip' => &$skip]` |
| `seo.sitemap.category_url` | trigger | `['category', 'url' => &$url, 'skip' => &$skip]` |
| `seo.sitemap.tag_url` | trigger | `['tag', 'url' => &$url, 'skip' => &$skip]` |
| `seo.cache.clear` | trigger | — |
| `seo.meta.canonical` | — | Формирование канонического URL |

> [!NOTE]
> Список растёт вместе с модулями. Чтобы увидеть актуальный набор в своём проекте, выполните в корне сайта: `grep -rn "Event::" system/ | grep -o "Event::[a-z]*('[^']*'"`.

---

## Рекомендации

- 💡 **Именуйте события по схеме** `<область>.<действие>`: `post.created`, `shop.order.paid`. Так их проще искать и документировать.
- 💡 **Не делайте в слушателе тяжёлых запросов** без нужды — он выполняется на каждый запрос страницы.
- 💡 **Не полагайтесь на порядок**, если он не важен: используйте приоритеты осознанно.
- ⚠️ **Фильтры должны возвращать значение.** Если вернуть `null`, движок оставит исходное значение (для `Event::filter` проверяется `!== null`).
- 🚫 **Не редактируйте ядро**, чтобы «добавить хук» — сначала проверьте, нет ли подходящего события. Если нет, предложите его в issue: `Event::trigger()` в нужном месте ядра — хорошее улучшение движка.

---

[← Архитектура](01-arhitektura-i-zhiznennyy-cikl.md) · [Оглавление](../README.md) · [Свой контроллер →](03-svoy-kontroller.md)