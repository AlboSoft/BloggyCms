# 🧩 Свой контроллер (модуль)

Модуль в BloggyCMS — это папка в `system/controllers/`, которую движок находит автоматически. Здесь описано, как создать полноценный модуль: страницу на сайте, раздел в админке, права, настройки и хуки.

Возьмём практический пример — модуль **«Отзывы»** (`reviews`).

---

## Структура модуля

```text
system/controllers/reviews/
├── ReviewController.php     # класс ReviewController extends Controller
├── Model.php                # ReviewModel implements ModelAPI
├── manifest.php             # карточка модуля
├── routes.php               # маршруты
├── permissions.php          # права для групп
├── Settings.php             # форма настроек (необязательно)
├── actions/                 # классы действий
│   ├── ReviewAction.php
│   ├── Index.php
│   ├── AdminIndex.php
│   └── AdminCreate.php
└── hooks/                   # необязательно: слушатели событий
    └── events.php
```

> [!TIP]
> Проще всего скопировать существующий модуль (`pages` или `tags`) и переименовать: все соглашения уже соблюдены.

---

## Шаг 1. Манифест

`manifest.php` — карточка модуля. Её видит администратор в разделе «Контроллеры» и на вкладке «Компоненты».

```php
<?php
return [
    'name'         => LANG_CONTROLLER_REVIEWS_MANIFEST_NAME,
    'author'       => 'Ваше имя',
    'version'      => '1.0.0',
    'has_settings' => true,      // есть ли Settings.php
    'description'  => LANG_CONTROLLER_REVIEWS_MANIFEST_DESCRIPTION,
];
```

| Ключ | Значение |
|---|---|
| `name` | Отображаемое имя модуля |
| `author` | Автор |
| `version` | Версия модуля |
| `has_settings` | Включает вкладку настроек модуля |
| `is_protected` | `true` запрещает удаление/отключение (для системных) |
| `description` | Краткое описание |

---

## Шаг 2. Контроллер

Контроллер — тонкий диспетчер. На каждый маршрут — метод `<action>Action()`, который создаёт экшен и вызывает `execute()`.

```php
<?php

class ReviewController extends Controller
{
    public function getSystemName()
    {
        return 'reviews';
    }

    /** Публичная страница со списком отзывов: /reviews */
    public function indexAction()
    {
        $action = new \reviews\actions\Index($this->db, $this->getRouteParams());
        $action->setController($this);
        return $action->execute();
    }

    /** Показ одного отзыва: /reviews/show/{id} */
    public function showAction($id)
    {
        $action = new \reviews\actions\Show($this->db, ['id' => (int) $id]);
        $action->setController($this);
        return $action->execute();
    }

    /** Список в админке: /admin/reviews */
    public function adminIndexAction()
    {
        $action = new \reviews\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
}
```

Что даёт базовый `Controller`:

| Метод | Назначение |
|---|---|
| `render($template, $data = [])` | Отрисовать шаблон в активной теме |
| `redirect($url)` | Редирект и выход |
| `loadModel($name, $alias = null)` | Загрузить модель (или взять из кэша) |
| `__get($name)` | Доступ к загруженной модели как к свойству |
| `processContent($content)` | Обработать шорткоды в контенте |
| `addBreadcrumb($title, $url = null)`, `clearBreadcrumbs()` | Хлебные крошки |
| `getRouteParams()`, `getRouteParam($index)` | Параметры маршрута |
| `getSystemName()`, `getControllerInfo()`, `hasSettings()` | Метаданные модуля |

> [!WARNING]
> Не размещайте бизнес-логику в контроллере. Контроллер отвечает только за выбор экшена и передачу параметров — иначе при повторном использовании кода начнутся дубли.

---

## Шаг 3. Экшены

Экшен — рабочий класс. Базовый экшен модуля наследуется от `Action` (или от собственного общего класса) и содержит удобные сервисы.

```php
<?php

namespace reviews\actions;

abstract class ReviewAction extends \Action
{
    protected $reviewModel;
    protected $controller;
    protected $breadcrumbs;

    public function __construct($db, $params = [])
    {
        parent::__construct($db, $params);
        $this->reviewModel = new \ReviewModel($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }

    abstract public function execute();
}
```

Публичный экшен:

```php
<?php

namespace reviews\actions;

class Index extends ReviewAction
{
    public function execute()
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $reviews = $this->reviewModel->getAllPaginated($page, 10);

        $this->breadcrumbs->add(LANG_ACTION_REVIEWS_INDEX_BREADCRUMB_HOME, BASE_URL);
        $this->breadcrumbs->add(LANG_ACTION_REVIEWS_INDEX_BREADCRUMB_REVIEWS);
        \BreadcrumbsHelper::setManager($this->breadcrumbs);

        return $this->controller->render('reviews/index', [
            'pageTitle' => LANG_ACTION_REVIEWS_INDEX_PAGE_TITLE,
            'reviews'   => $reviews['items'],
            'pagination'=> $reviews['pagination'],
        ]);
    }
}
```

Админский экшен — та же схема, только шаблон начинается с `admin/` и добавляются хлебные крошки админки:

```php
<?php

namespace reviews\actions;

class AdminIndex extends ReviewAction
{
    public function execute()
    {
        return $this->controller->render('admin/reviews/index', [
            'pageTitle' => LANG_ACTION_REVIEWS_ADMININDEX_PAGE_TITLE,
            'reviews'   => $this->reviewModel->getAll(),
        ]);
    }
}
```

---

## Шаг 4. Маршруты

`routes.php` — карта URL. Ключ с параметрами `{name}` попадает в аргументы метода.

```php
<?php
return [
    // Публичные маршруты
    'reviews'                => ['controller' => 'Review', 'action' => 'index'],
    'reviews/page/{page}'    => ['controller' => 'Review', 'action' => 'index'],
    'reviews/show/{id}'      => ['controller' => 'Review', 'action' => 'show'],

    // Админка: флаг admin => true обязателен
    'admin/reviews'                    => ['controller' => 'Review', 'action' => 'adminIndex', 'admin' => true],
    'admin/reviews/create'             => ['controller' => 'Review', 'action' => 'create',    'admin' => true],
    'admin/reviews/edit/{id}'          => ['controller' => 'Review', 'action' => 'edit',      'admin' => true],
    'admin/reviews/delete/{id}'        => ['controller' => 'Review', 'action' => 'delete',    'admin' => true],
];
```

| Ключ | Значение |
|---|---|
| `controller` | Имя класса без суффикса `Controller` (`Review` → `ReviewController`) |
| `action` | Имя метода без суффикса `Action` (`adminIndex` → `adminIndexAction()`) |
| `admin` | `true` — маршрут проходит через `AdminAuthMiddleware` |
| `{param}` | Параметр, передаётся в метод по имени |

> [!WARNING]
> Забыли `'admin' => true` — и админский экшен станет доступен без авторизации. Проверяйте каждый служебный маршрут.

Полная карта URL движка — в [справочнике маршрутов](../reference/marshruty.md).

---

## Шаг 5. Модель

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

    public function getAllPaginated(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $items = $this->db->fetchAll(
            "SELECT * FROM reviews WHERE status = 'published' ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $total = (int) $this->db->fetchValue("SELECT COUNT(*) FROM reviews WHERE status = 'published'");

        return [
            'items' => $items,
            'pagination' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
                'pages'    => (int) ceil($total / $perPage),
            ],
        ];
    }

    public function create(array $data): int
    {
        $this->db->insert('reviews', $data);
        return (int) $this->db->lastInsertId();
    }
}
```

Подробности — в разделе [Модели и база данных](04-modeli-i-baza-dannyh.md).

---

## Шаг 6. Права доступа

`permissions.php` объявляет права, которые модуль отдаёт в группы пользователей:

```php
<?php
return [
    'review_view' => [
        'title'       => LANG_CONTROLLER_REVIEWS_PERMISSION_VIEW_TITLE,
        'description' => LANG_CONTROLLER_REVIEWS_PERMISSION_VIEW_DESC,
    ],
    'review_add' => [
        'title'       => LANG_CONTROLLER_REVIEWS_PERMISSION_ADD_TITLE,
        'description' => LANG_CONTROLLER_REVIEWS_PERMISSION_ADD_DESC,
    ],
    'review_approve' => [
        'title'       => LANG_CONTROLLER_REVIEWS_PERMISSION_APPROVE_TITLE,
        'description' => LANG_CONTROLLER_REVIEWS_PERMISSION_APPROVE_DESC,
    ],
];
```

Использование в коде:

```php
<?php
// В экшене
if (!AuthHelper::can('review_add')) {
    Notification::error(LANG_ACTION_REVIEWS_NO_PERMISSION);
    $this->controller->redirect(BASE_URL . '/reviews');
}

// В шаблоне
<?php if (\AuthHelper::can('review_approve')): ?>
    <button class="btn btn-success">Одобрить</button>
<?php endif; ?>
```

Права появятся в админке автоматически: **Пользователи → Группы → Права**. Ничего дополнительно регистрировать не нужно — `PermissionManager` читает `permissions.php` всех модулей.

---

## Шаг 7. Настройки модуля

Создайте `Settings.php` с классом `getForm($currentSettings)`:

```php
<?php

namespace reviews;

class ReviewSettings
{
    public static function getForm($currentSettings)
    {
        $fieldsets = [
            new \Fieldset('Настройки отзывов', [
                'icon'    => 'bi bi-star',
                'columns' => '12',
                'fields'  => [
                    \FieldFactory::number('per_page', [
                        'title'   => 'Отзывов на странице',
                        'default' => 10,
                        'min'     => 1,
                        'max'     => 50,
                    ]),
                    \FieldFactory::checkbox('require_moderation', [
                        'title'   => 'Премодерация',
                        'default' => true,
                        'switch'  => true,
                    ]),
                ],
            ]),
        ];

        ob_start();
        ?>
        <div class="row g-4">
            <?php foreach ($fieldsets as $fieldset): ?>
                <div class="col-12"><?= $fieldset->render($currentSettings) ?></div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
```

Чтение настроек:

```php
<?php
$perPage = (int) SettingsHelper::get('controller_reviews', 'per_page', 10);
```

Имя группы формируется автоматически: `controller_` + `getSystemName()`.

---

## Шаг 8. Шаблоны

Файлы темы для модуля кладите в активную тему:

```text
templates/default/front/reviews/index.php
templates/default/admin/reviews/index.php
```

Если шаблона нет в активной теме, движок возьмёт его из `default`. Для админки всегда используется тема `default`.

---

## Шаг 9. Хуки (необязательно)

```php
<?php
// system/controllers/reviews/hooks/events.php

Event::listen('post.created', function ($postId, $data) {
    // Пример: связать отзыв с постом
}, 10, 2);
```

---

## Управление модулем

| Действие | Где |
|---|---|
| Просмотр списка модулей | `/admin/controllers` |
| Включить/выключить | `/admin/controllers/toggle` |
| Настройки модуля | `/admin/settings` → вкладка «Компоненты» |
| Права модуля | `/admin/users` → группы → «Права» |

Отключение модуля не удаляет данные: движок просто перестаёт отвечать на его маршруты (отдаётся 404).

> [!TIP]
> Проверка в `App::run()` использует настройку `enabled` в группе `controller_<system>`. Это удобно для отключения собственного модуля без удаления кода.

---

## Чек-лист нового модуля

1. Папка создана, класс контроллера назван `<Имя>Controller`.
2. `manifest.php` заполнен, системное имя совпадает с именем папки.
3. Все админские маршруты помечены `'admin' => true`.
4. Модель реализует `ModelAPI` (можно через `APIAware`).
5. Права объявлены в `permissions.php` и проверяются в экшенах.
6. Строки интерфейса взяты из языковых файлов, а не захардкожены.
7. Настройки (если есть) читаются через `SettingsHelper::get()`.
8. Данные выводятся через экранирование (`html()`), SQL — только через подготовленные запросы.
9. Модуль проверен с включённым и отключённым состоянием.

---

[← События и хуки](02-sobytiya-i-huki.md) · [Оглавление](../README.md) · [Модели и база данных →](04-modeli-i-baza-dannyh.md)