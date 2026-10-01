# 🛡️ Безопасность и отладка

Две стороны одной медали: писать код так, чтобы его нельзя было использовать против сайта, и уметь быстро находить причину, когда что-то сломалось.

---

# Безопасность

## Главные правила

| Правило | Как выглядит в коде |
|---|---|
| Экранируйте вывод | `<?= html($value) ?>` |
| Не склеивайте SQL | `$db->fetchAll("... WHERE id = ?", [$id])` |
| Проверяйте права | `AuthHelper::can('post_edit')` |
| Защищайте формы от CSRF | `CsrfToken::field()` / `AdminForm` |
| Проверяйте загружаемые файлы | `FileUpload::upload(...)` с типами и размером |
| Не доверяйте ничему из `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE` | Приводите типы, проверяйте белые списки |

## Экранирование вывода (XSS)

```php
<?php
// Плохо: пользователь может вставить <script>
echo $post['title'];

// Хорошо
echo html($post['title']);

// В атрибуте
<a href="<?= html($post['url']) ?>" title="<?= html($post['title']) ?>">…</a>

// Разрешённый HTML (например, контент после фильтра) — только если вы уверены
echo $post['content'];
```

> [!WARNING]
> Правило простое: **всё, что пришло от пользователя, экранируется**. Исключение — HTML-контент, который уже прошёл очистку (например, рендер пост-блоков).

## SQL-инъекции

```php
<?php
// Плохо
$db->fetch("SELECT * FROM posts WHERE slug = '{$_GET['slug']}'");

// Хорошо: подготовленный запрос
$post = $db->fetch("SELECT * FROM posts WHERE slug = ?", [$_GET['slug']]);

// Списки значений
$ids = array_map('intval', $_POST['ids']);
$in  = implode(',', array_fill(0, count($ids), '?'));
$db->fetchAll("SELECT * FROM posts WHERE id IN ($in)", $ids);
```

Сортировка и имена колонок не могут быть параметрами — пропускайте их через белый список:

```php
<?php
$allowed = ['title', 'created_at', 'views'];
$order = in_array($_GET['sort'] ?? '', $allowed, true) ? $_GET['sort'] : 'created_at';
$db->fetchAll("SELECT * FROM posts ORDER BY {$order} DESC");
```

## CSRF

Формы, меняющие данные, обязаны иметь токен:

```php
<?php // в форме
<form method="post" action="<?= ADMIN_URL ?>/reviews/save">
    <?= CsrfToken::field('review_form') ?>
    …
</form>
```

```php
<?php // при обработке
if (!CsrfToken::verify($_POST['csrf_token'] ?? '', 'review_form')) {
    Notification::error('Сессия истекла, попробуйте снова');
    $this->controller->redirect(ADMIN_URL . '/reviews');
}
```

Токен одноразовый, живёт 1 час. В формах на базе `AdminForm` проверка уже встроена в `handleRequest()`.

## Права и доступ

```php
<?php
// В экшене: право на действие
if (!AuthHelper::can('comment_delete')) {
    Notification::error(LANG_ACTION_COMMENTS_DELETE_NO_PERMISSION);
    $this->controller->redirect(BASE_URL);
}

// Админский маршрут — обязательно флаг admin
// 'admin/reviews' => ['controller' => 'Review', 'action' => 'adminIndex', 'admin' => true]

// В шаблоне: не показывать кнопку без права
<?php if (AuthHelper::can('review_approve')): ?>
    <button …>Одобрить</button>
<?php endif; ?>
```

> [!WARNING]
> Скрытие кнопки в шаблоне — не защита. Проверка права **обязательна** в экшене: пользователь может отправить запрос напрямую.

## Загрузка файлов

```php
<?php
$result = FileUpload::upload(
    $_FILES['image'] ?? null,
    UPLOADS_PATH . '/reviews/',
    ['image/jpeg', 'image/png', 'image/webp'],   // белый список MIME
    2048                                          // максимум, КБ
);

if (!$result['success']) {
    Notification::error($result['error']);
}
```

Что проверять всегда:

- MIME-тип через `mime_content_type()`, а не расширение из имени файла;
- размер;
- уникальное имя файла (`uniqid()` + безопасное имя);
- папка назначения внутри `uploads/` — никаких `../`;
- исполняемые расширения (`.php`, `.phtml`, `.phar`) — запрещены.

## Капча и антиспам

```php
<?php
// Проверка ответа капчи
if (!CaptchaHelper::verify($_POST['captcha_id'] ?? '', $_POST['captcha_answer'] ?? '')) {
    Notification::error(LANG_ACTION_COMMENTS_ADD_CAPTCHA_ERROR);
}

// Скрытое поле-ловушка: боты его заполняют
if (!CaptchaHelper::verifyHoneypot($_POST)) {
    // считаем отправку подозрительной
}

// Ограничение частоты: сколько запросов с одного IP допустимо
if (!CaptchaHelper::checkRateLimit('comment_form', $_SERVER['REMOTE_ADDR'], $settings)) {
    Notification::error('Слишком часто, попробуйте позже');
}
```

---

# Отладка

## Включение

**Настройки → Общие → Режим отладки (`debug_mode`).** Включённый режим включает:

- перехват ошибок и исключений (`DebugHandler`);
- запись в таблицу `debug_logs`;
- просмотр журнала в `/admin/debug`.

> [!WARNING]
> На публичном сайте держите режим отладки **выключенным**: он пишет много служебных данных и может раскрывать внутреннюю информацию.

## Запись в журнал

```php
<?php
Logger::info('Запущена синхронизация');
Logger::error('Ошибка синхронизации', ['error' => $e->getMessage()]);

DebugLogger::warning('Медленный запрос', ['ms' => 1500, 'sql' => $sql]);
DebugLogger::exception($e);   // стек и контекст
```

`DebugLogger` работает всегда (в лог сервера) и дополнительно — в журнал админки при включённом `debug_mode`.

## Что смотреть в `/admin/debug`

| Поле | Значение |
|---|---|
| Тип | `error`, `warning`, `notice` |
| Код и сообщение | Что произошло |
| Файл и строка | Где именно |
| Трасса | Стек вызовов |
| Контекст | Дополнительные данные |
| URL, метод, IP, пользователь | Обстоятельства |
| Флаг «исправлено» | Отметка, что ошибка закрыта |

Фильтры позволяют найти ошибки по типу, отметить их как исправленные, посмотреть статистику и очистить журнал.

## Перехват ошибок

Ядро перехватывает ошибки через `DebugHandler`:

```php
<?php
DebugHandler::init($debugEnabled, $db);   // вызывается в App::__construct()
// при включённом debug_mode регистрируются обработчики:
// handleError()     — ошибки PHP
// handleException() — необработанные исключения
// handleShutdown()  — фатальные ошибки
```

Обратите внимание: `index.php` подавляет только предупреждения о неопределённых константах (`Undefined constant` / `Use of undefined constant`) — чтобы локализация не засоряла лог.

## Приёмы отладки

```php
<?php
// 1. Быстрая проверка переменной
DebugLogger::info('Что в данных', ['data' => $data]);

// 2. Локальный дамп (только на своём окружении!)
echo '<pre>'; var_dump($result); echo '</pre>'; exit;

// 3. Проверка маршрутов
require_once SYSTEM_PATH . '/helpers/RouteHelper.php';
print_r(RouteHelper::getRoutesForController('Review'));

// 4. Список моделей, доступных через API
print_r(API::getAvailableModels());

// 5. Какие хуки загружены
print_r(Event::hasListeners('post.created'));
```

> [!TIP]
> Не оставляйте `var_dump()`/`exit` в коде: на боевом сайте это ломает страницы. Для быстрых проверок используйте `DebugLogger` — записи видно в админке.

## Типовые ошибки и решения

| Ошибка | Причина | Решение |
|---|---|---|
| `Template file not found` | Нет файла шаблона ни в теме, ни в `default` | Проверить путь и имя файла |
| `Action X not found in controller` | Метод `<action>Action()` отсутствует | Добавить метод или исправить `routes.php` |
| `Missing required parameter: X` | В маршруте нет параметра, который нужен методу | Добавить `{x}` в маршрут либо значение по умолчанию |
| `Controller class XController not found` | Неверное имя контроллера в маршруте или модуль отключён | Проверить `routes.php` и настройку `enabled` |
| «Undefined constant LANG_…» | Строка не добавлена в языковой файл | Добавить `define()` в нужную локаль |
| Пустая страница | Ошибка PHP | Включить `debug_mode`, смотреть `/admin/debug` |
| Стили/скрипты не применяются | Кеш блоков, либо ассеты добавлены в другой контекст | `clear_blocks_assets_cache()` / проверить контекст |
| Изменения в шаблоне не видны | Файл перекрыт копией в активной теме | Искать дубликат в `templates/<тема>/` |
| 404 на всех страницах | Не работает переписывание URL | Проверить `.htaccess` / `try_files` в Nginx |

## Отладка хуков

Хуки подключаются на старте, и ошибки внутри них легко не заметить:

```php
<?php
try {
    Event::listen('post.created', function ($postId, $data) {
        // …
    }, 10, 2);
} catch (\Throwable $e) {
    DebugLogger::error('Не удалось подписаться на post.created', ['error' => $e->getMessage()]);
}
```

Проверить, что подписка жива:

```php
<?php
var_dump(Event::hasListeners('post.created'));  // true
```

---

## Чек-лист перед коммитом

1. Все выводы экранированы (`html()`).
2. Все SQL-запросы — с параметрами, сортировки — из белого списка.
3. Пользовательские данные не попадают в имена файлов и пути.
4. Формы защищены CSRF, права проверяются в экшенах.
5. Загрузки проверяются по MIME, размеру и расширению.
6. `var_dump()`, `exit`, отладочные выводы удалены.
7. Ошибки логируются через `DebugLogger`, а не `echo`.
8. Режим отладки на боевом сайте выключен.

---

[← Справочник хелперов](11-helpery.md) · [Оглавление](../README.md) · [Стиль кода и соглашения →](13-stil-koda-i-soglasheniya.md)