# 🏗️ Архитектура и жизненный цикл запроса

BloggyCMS — это движок без фреймворка: 24 класса ядра, 40 хелперов и модули, которые лежат в своих папках. Никакого Composer и сборки — весь код исполняется напрямую PHP 8.

Этот документ — карта: как запрос превращается в страницу, что лежит в ядре и какими соглашениями живёт проект.

---

## Жизненный цикл запроса

```text
Браузер
   │  GET /post/moy-post
   ▼
.htaccess / Nginx ──▶ index.php
                         │
                         ├─ 1. session_start(), ROOT_PATH
                         ├─ 2. Проверка system/config/*.php → /install/ при отсутствии
                         ├─ 3. Подключение config.php + database.php
                         ├─ 4. Загрузка классов ядра (Event, Database, Controller, Router, App, AdminForm)
                         ├─ 5. new App()
                         │      ├─ registerMiddlewares()      — цепочка middleware
                         │      ├─ loadControllerHooks()      — все */hooks/*.php
                         │      ├─ initFieldShortcodes()      — шорткоды полей + базовые
                         │      │    └─ FragmentHelper::registerShortcodes()
                         │      └─ run()
                         │           ├─ Router::match($uri)    — поиск маршрута
                         │           ├─ Контроллер включён? (controller_<system>.enabled)
                         │           ├─ new <Post>Controller($db)
                         │           └─ callControllerAction() → <action>Action($params)
                         │                 └─ Action::execute()
                         │                      ├─ работа с моделями и базой
                         │                      ├─ Breadcrumbs, Event::trigger()
                         │                      └─ Controller::render($template, $data)
                         │                           ├─ Event::trigger('controller.render.before')
                         │                           ├─ include шаблона темы
                         │                           ├─ Event::filter('controller.render.content')
                         │                           └─ include layout.php (front/admin)
                         ▼
                      HTML-ответ
```

Ключевые точки:

- **Единая точка входа** — `index.php`. Любой URL проходит через него (кроме реальных файлов и папок).
- **Маршруты объявляют модули**, а не ядро: `system/controllers/<модуль>/routes.php`.
- **Контроллер — тонкий**: его метод `<action>Action()` создаёт объект экшена и вызывает `execute()`.
- **Вся логика — в экшенах** (`system/controllers/<модуль>/actions/`), они наследуются от базового экшена модуля.
- **Вывод — через `render()`**, который сам подбирает шаблон в активной теме и подключает layout.

---

## Состав ядра (`system/core/`)

| Класс | Ответственность |
|---|---|
| `App` | Запуск приложения: bootstrap, middleware, хуки, роутинг, 404 |
| `Router` / `RouteManager` | Сопоставление URL с маршрутами, параметры, генерация имён маршрутов |
| `Controller` | Базовый контроллер: `render()`, `redirect()`, хлебные крошки, модели, `processContent()` |
| `ControllerManager` | Обнаружение модулей, их манифестов, настроек, вкладка «Компоненты» |
| `Action` | Минимальная база для экшенов (принимает `$db` и параметры) |
| `Event` | События: `listen`, `trigger`, `filter`, `unlisten`, приоритеты |
| `Middleware` / `AdminAuthMiddleware` | Цепочка middleware, проверка доступа в админку |
| `Database` / `DatabaseRegistry` | PDO-обёртка: `query`, `fetch`, `fetchAll`, `fetchValue`, `insert`, `update`, `delete`, транзакции |
| `ModelAPI` / `APIAware` | Контракт моделей и доступ к ним как к API (`API::model('posts')`) |
| `PermissionManager` | Загрузка `permissions.php` всех модулей, проверка прав |
| `PostBlockManager` | Обнаружение пост-блоков, их ассеты и рендеринг |
| `HtmlBlockTypeManager` | Типы HTML-блоков, их шаблоны, ассеты и рендеринг |
| `FieldManager` / `BaseField` / `FieldFactory` (helper) | Пользовательские поля: типы, значения, шорткоды |
| `AssetManager` | Сбор CSS/JS: базовые, подключённые кодом, инлайн, из блоков |
| `AdminForm` | Декларативные формы админки: филдсеты, валидация, CSRF, обработка запроса |
| `BreadcrumbsManager` | Хлебные крошки для шаблонов |
| `Shortcodes` | Реестр и обработка шорткодов |
| `UserActivityManager` | Активность пользователей и онлайн-сессии |
| `DebugHandler` / `DebugLogger` (helper) | Перехват ошибок и запись в журнал отладки |

---

## Где что лежит

| Путь | Что там |
|---|---|
| `index.php` | Bootstrap и точка входа |
| `system/core/` | Ядро (не трогать без причины) |
| `system/controllers/<модуль>/` | Модули: контроллер, модель, маршруты, экшены, права, хуки, настройки |
| `system/helpers/` | Хелперы и глобальные функции |
| `system/fields/` | Типы пользовательских полей |
| `system/post_blocks/` | Типы пост-блоков |
| `system/html_blocks/` | Типы HTML-блоков |
| `system/languages/<локаль>/` | Языковые файлы |
| `system/config/` | `version.ini`, создаваемые `config.php` и `database.php` |
| `templates/<тема>/` | Темы: `front/` и `admin/` |
| `uploads/` | Пользовательские файлы |
| `install/` | Мастер установки (удаляется после запуска) |

---

## Анатомия модуля

Каждый модуль — самостоятельная папка. Достаточно положить её в `system/controllers/`, и движок её увидит.

```text
system/controllers/posts/
├── PostController.php      # класс PostController extends Controller
├── Model.php               # PostModel implements ModelAPI
├── manifest.php            # имя, автор, версия, описание, has_settings
├── routes.php              # карта URL → Controller@action
├── permissions.php         # права, которые модуль отдаёт в группы
├── Settings.php            # форма настроек (класс PostSettings::getForm)
├── actions/                # по классу на действие + общий <Module>Action
│   ├── PostAction.php
│   ├── Index.php
│   ├── AdminIndex.php
│   └── ...
└── hooks/                  # файлы, подключаемые при старте (слушатели событий)
    └── events.php
```

Подробно создание модуля разобрано в разделе [Свой контроллер](03-svoy-kontroller.md).

---

## Поток данных

1. **Экшен** получает данные через модели: `$this->postModel->getAllPaginated($page)`.
2. **Модель** обращается к базе через `Database` (PDO, подготовленные запросы).
3. **Экшен** формирует массив данных и вызывает `$this->controller->render('posts/index', $data)`.
4. **`render()`**:
   - вызывает событие `controller.render.before`;
   - подключает файл шаблона: `templates/<тема>/<template>.php` (для админки — всегда `default`);
   - если шаблона в активной теме нет, берёт его из `default`;
   - пропускает результат через фильтр `controller.render.content`;
   - подключает `layout.php`;
   - вызывает `controller.render.after`.

В шаблоне доступны переменные из `$data`, а также `$app`, `$db`, `$userModel`, `$pageTitle`, `$breadcrumbs`.

---

## Конфигурация и константы

`index.php` требует наличия двух файлов:

- `system/config/database.php` — доступы к базе;
- `system/config/config.php` — пути, URL, тема, служебные константы.

Если их нет — редирект на `/install/`.

Основные константы, на которые опирается код:

```php
ROOT_PATH            // корень сайта
BASE_PATH            // = ROOT_PATH
SYSTEM_PATH          // /system
TEMPLATES_PATH       // /templates
UPLOADS_PATH         // /uploads
LANGUAGES_PATH       // /system/languages
BASE_URL             // адрес сайта без слэша на конце
ADMIN_URL            // BASE_URL . '/admin'
DEFAULT_TEMPLATE     // 'default'
CACHE_DIR            // /cache
USER_ONLINE_INTERVAL // 300 (секунд)
DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PREFIX, DB_CHARSET, DB_COLLATE
```

Полный список — в [справочнике констант](../reference/konstanty.md).

> [!TIP]
> Свои модули проверяйте на наличие констант через `defined()`: при переносе сайта конфигурация может отличаться.

---

## Настройки: как модуль их получает

- Настройки хранятся в таблице `settings` под ключом группы (`group_key`).
- Настройки модуля лежат в группе `controller_<system_name>`, например `controller_posts`.
- Читаются через `SettingsHelper::get('controller_posts', 'homepage_type', 'default')`.

```php
<?php
$perPage = (int) SettingsHelper::get('controller_posts', 'homepage_posts_per_page', 10);
$siteName = SettingsHelper::get('general', 'site_name', 'BloggyCMS');
```

Если модуль можно выключить, движок проверяет ключ `enabled` в его группе настроек — при `false` маршруты модуля отдают 404.

---

## Что значит «расширять без правки ядра»

Практически это четыре механизма:

| Механизм | Для чего |
|---|---|
| **События** (`Event`) | Реагировать на действия движка, менять значения «на выходе» |
| **Модули** (`system/controllers/`) | Новые разделы, маршруты, страницы админки |
| **Блоки и поля** (`post_blocks`, `html_blocks`, `fields`) | Новые визуальные элементы и типы данных |
| **Шорткоды и хелперы** | Вставки в контент, переиспользуемая логика |

Ядро при этом остаётся неизменным — обновление движка не конфликтует с вашими доработками.

---

## Дальше по разделу

1. [События и хуки](02-sobytiya-i-huki.md) — первый инструмент расширения.
2. [Свой контроллер](03-svoy-kontroller.md) — полноценный модуль.
3. [Модели и база данных](04-modeli-i-baza-dannyh.md) — как работать с данными.
4. [Шаблонизация и темы](06-shablonizaciya-i-temy.md) — как выводить результат.

---

[← Оглавление](../README.md) · [События и хуки →](02-sobytiya-i-huki.md)