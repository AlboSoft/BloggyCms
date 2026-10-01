# 🎨 Шаблонизация и темы

Шаблоны в BloggyCMS — это обычные PHP-файлы. Никакого компилируемого шаблонизатора: вы используете `<?php ?>` напрямую, а движок подбирает файл в активной теме, подключает `layout.php` и передаёт туда данные.

---

## Как работает `render()`

```php
<?php
// В экшене
return $this->controller->render('posts/show', [
    'pageTitle' => $post['title'],     // заголовок страницы
    'post'      => $post,              // любые данные — в переменные шаблона
    'breadcrumbs' => $breadcrumbs,
]);
```

Порядок работы:

1. Событие `controller.render.before`.
2. Добавление `pageTitle`, `breadcrumbs`, `app`, `db`, `userModel` в данные.
3. `extract($data)` — каждая переменная становится доступна в шаблоне.
4. Поиск файла шаблона:

```text
templates/<активная_тема>/<шаблон>.php
        ↓ (если файла нет)
templates/default/<шаблон>.php
        ↓ (если файла нет)
       Exception «Template file not found»
```

5. Рендер шаблона и фильтр `controller.render.content`.
6. Подключение layout:
   - для шаблонов с префиксом `admin/` — `templates/default/admin/layout.php`;
   - для остальных — `templates/<активная_тема>/front/layout.php`;
7. Событие `controller.render.after`.

> [!NOTE]
> Админка **всегда** использует тему `default`. Активная тема влияет только на фронтенд.

---

## Что доступно в шаблоне

| Переменная | Откуда |
|---|---|
| `$data`, которые передал экшен | Параметр `render()` |
| `$pageTitle` | `render()` или свойство контроллера |
| `$breadcrumbs` | Хлебные крошки |
| `$app`, `$db` | Служебные объекты |
| `$userModel` | Модель пользователей |
| Глобальные функции и хелперы | Подключены всегда |

```php
<?php // templates/default/front/posts/show.php ?>
<article class="post">
    <h1><?= html($post['title']) ?></h1>
    <time><?= format_date($post['created_at']) ?></time>

    <?php if (!empty($post['featured_image'])): ?>
        <img src="<?= BASE_URL . '/uploads/images/' . html($post['featured_image']) ?>"
             alt="<?= html($post['title']) ?>">
    <?php endif; ?>

    <div class="post-content"><?= $post['content'] ?></div>

    <?= render_html_block('footer') ?>
</article>
```

---

## Функции, которыми пользуется тема

| Функция | Назначение |
|---|---|
| `render_html_block('header')` | Вывести HTML-блок по slug |
| `base_front_css(['bootstrap.min','main'])` | Подключить базовые CSS темы |
| `base_front_js([...])` | Подключить базовые JS |
| `render_front_css()` / `render_front_js()` | Вывести ассеты, подключённые кодом и блоками |
| `render_front_bottom_js()` | JS в конце документа |
| `front_css($path)` / `front_js($path)` | Подключить файл из кода экшена |
| `front_inline_css($css)` / `front_inline_js($js)` | Инлайн-код |
| `get_blocks_css_url()` | URL собранного CSS блоков |
| `init_blocks_cache()` | Подготовить кеш блоков (в начале layout) |
| `html($value)` | Экранировать вывод |
| `bloggy_icon('bs', 'star')` | SVG-иконка |
| `format_date($date)` / `time_ago($date)` | Даты |
| `plural($n, $forms)` | Склонения |
| `get_custom_field_display(...)` | Вывод пользовательского поля |
| `MenuRenderer::renderByTemplate('header')` | Вывод меню, привязанного к шаблону |
| `PaginationHelper::render(...)` | Пагинация |

Аналогичные функции есть для админки: `admin_css()`, `admin_js()`, `admin_inline_js()`, `render_admin_assets()`, `admin_menu_item()`.

---

## Структура темы

```text
templates/default/
├── front/
│   ├── layout.php          # обязателен: каркас страницы
│   ├── home/               # главная
│   ├── posts/              # лента и карточка поста
│   ├── pages/              # страницы
│   ├── category/           # категории
│   ├── tags/               # теги
│   ├── archive/            # архив
│   ├── search/             # поиск
│   ├── profile/            # профиль
│   ├── auth/               # вход/регистрация/восстановление
│   ├── comments/           # комментарии
│   ├── users/              # пользователи
│   └── assets/
│       ├── css/main.css
│       ├── js/
│       ├── html_blocks/    # шаблоны HTML-блоков
│       ├── postblocks/     # шаблоны пост-блоков
│       ├── forms/          # стили и разметка форм
│       └── menu/           # шаблоны меню
├── admin/
│   ├── layout.php          # каркас админки
│   ├── dashboard.php
│   └── assets/
├── 404.php
└── 500.php
```

### Каркас фронтенда

`front/layout.php` в стандартной теме отвечает за:

1. Режим обслуживания (503, если включён `maintenance_mode`).
2. `init_blocks_cache()` — кеш блоков.
3. `<head>`: favicon, мета-теги, Schema.org, CSS темы, CSS блоков, пользовательские CSS.
4. Слоты: `header`, `breadcrumbs`, контент, `footer`, `top`, `cookies`.
5. JS в конце документа.

Именно поэтому шапкой и подвалом можно управлять из админки, не трогая шаблон.

---

## Переопределение шаблонов

Движок ищет шаблон в активной теме, потом в `default`. Значит, чтобы изменить внешний вид, достаточно **повторить структуру папок в своей теме** и переопределить нужный файл:

```text
templates/my-theme/front/posts/show.php   ← перекроет default для этой страницы
```

Это же правило работает для шаблонов блоков:

```text
templates/my-theme/front/assets/postblocks/quoteblock/default.php
templates/my-theme/front/assets/html_blocks/DefaultHeaderBlock/default.php
```

> [!TIP]
> Такой способ апгрейдобезопасен: при обновлении движка вы не конфликтуете с `templates/default`, потому что ваши изменения лежат отдельно.

---

## Своя тема: пошагово

```bash
cp -r templates/default templates/my-theme
```

1. Определите тему в админке: **Настройки → Сайт → Тема оформления** — но сначала убедитесь, что она работоспособна.
2. Правьте `templates/my-theme/front/layout.php` и `front/assets/css/main.css`.
3. Сохраните обязательный минимум: `front/layout.php`, `404.php`, `500.php`.
4. Проверьте все типы страниц и мобильную вёрстку.

### Правила, которые нельзя нарушать

| Правило | Последствие нарушения |
|---|---|
| В начале `layout.php` вызывать `init_blocks_cache()` | Не собираются стили/скрипты блоков |
| Выводить слоты HTML-блоков | Администратор не сможет править шапку и подвал |
| Подключать ассеты через функции движка | Пользовательские CSS/JS блоков не подключатся |
| Экранировать вывод (`html()`) | XSS-уязвимость |
| Не менять `templates/default` при обновлениях | Правки затираются |

---

## Шаблоны и контент

| Что | Как связано с шаблоном |
|---|---|
| Пост-блоки | Каждый блок рендерится своим шаблоном в `front/assets/postblocks/<блок>/` |
| HTML-блоки | Тип блока задаёт шаблон; варианты — в `front/assets/html_blocks/<Тип>/` |
| Меню | Шаблон вывода выбирается в настройках меню, файлы — в `front/assets/menu/` |
| Формы | Стили и разметка — в `front/assets/forms/` (`modern`, `minimal`, `iconic`) |
| Страницы 404/500 | `templates/<тема>/404.php`, `500.php` |

---

## Отладка шаблонов

- Ошибка «Template file not found: …» — шаблона нет ни в активной теме, ни в `default`. Проверьте путь и имя.
- Пустая страница — включите `debug_mode`, посмотрите `/admin/debug`.
- Не обновляется CSS/JS — очистите кеш блоков (`/admin/html-blocks/clear-cache`) и кеш браузера.
- Правки в файле не видны — редактор сохранил в другую тему или файл перекрыт копией в активной теме.

---

## Работа с редактором шаблонов

Правки через `/admin/templates` удобны, но:

- включайте автобэкапы (`site.template_backups_enabled`);
- не работайте одновременно с файлом по FTP и в редакторе — потеряете одну из версий;
- для крупных изменений лучше использовать Git: тема — обычные файлы, их удобно версионировать.

Подробности для администратора — в разделе [Тема и оформление](../administrator/10-tema.md).

---

[← Формы и поля](05-formy-i-polya.md) · [Оглавление](../README.md) · [Пост-блоки →](07-post-bloki.md)