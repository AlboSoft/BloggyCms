# 🎛️ Ассеты и фронтенд

CSS и JS в BloggyCMS собираются через `AssetManager`: код (экшены, блоки, шаблоны) «просит» подключить файл, а тема в нужном месте выводит всё подключённое — с учётом порядка и контекста (фронтенд/админка).

---

## Два контекста

| Контекст | Для чего | Вывод в шаблоне |
|---|---|---|
| `frontend` | Публичная часть | `front/base_front_css()`, `render_front_css()` и аналоги |
| `admin` | Панель управления | `admin_css()`, `admin_js()`, `render_admin_assets()` |

Контекст указывается вторым параметром (`AssetManager::addCss($path, 'frontend')`), а функции-обёртки подставляют его автоматически.

---

## Функции для фронтенда

```php
<?php
// Подключить внешний файл (путь от корня сайта)
front_css('templates/' . get_current_template() . '/front/assets/css/reviews.css');
front_js('templates/' . get_current_template() . '/front/assets/js/reviews.js');

// Инлайн-код
front_inline_css('.review-card { border-radius: 12px; }');
front_inline_js('console.log("reviews ready");');

// JS в конце документа (до </body>)
add_bottom_js('https://example.com/widget.js');
```

Вывод — в `layout.php`:

```php
<?php echo base_front_css(['bootstrap.min', 'main']); ?>   <!-- базовые файлы темы -->
<link rel="stylesheet" href="<?= get_blocks_css_url() ?>">  <!-- собранные стили блоков -->
<?php echo render_front_css(); ?>                            <!-- всё, что подключил код -->

<?php echo base_front_js(['bootstrap.bundle.min', 'notifications', 'main']); ?>
<?php echo render_front_js(); ?>
<?php echo render_front_bottom_js(); ?>
```

## Функции для админки

```php
<?php
admin_css('templates/default/admin/assets/css/reviews.css');
admin_js('templates/default/admin/assets/js/reviews.js');
admin_inline_js('initReviewsTable();');
```

Вывод в `templates/default/admin/layout.php` выполняет `render_admin_assets()`.

---

## Полный список функций (`system/helpers/assets.php`)

| Группа | Функции |
|---|---|
| Подключение (фронт) | `front_css`, `front_js`, `front_inline_css`, `front_inline_js`, `add_bottom_js` |
| Подключение (админка) | `admin_css`, `admin_js`, `admin_inline_css`, `admin_inline_js` |
| Подключение (базовые) | `base_front_css`, `base_front_js`, `base_admin_css`, `base_admin_js` |
| Подключение (блоки) | `add_html_block_css`, `add_html_block_js` |
| Универсальные | `add_frontend_css`, `add_frontend_js`, `add_admin_css`, `add_admin_js`, `add_inline_css`, `add_inline_js` |
| Вывод | `render_front_css`, `render_front_js`, `render_front_bottom_js`, `render_front_assets`, `render_admin_css`, `render_admin_js`, `render_admin_bottom_js`, `render_admin_assets`, `render_assets`, `render_bottom_js` |
| Вспомогательное | `js_config`, `js_var` (передать данные из PHP в JS) |

Пример передачи данных в JS:

```php
<?php
js_config([
    'ajaxUrl' => BASE_URL . '/admin/reviews/load',
    'perPage' => 10,
]);
// В JS будет доступен объект конфигурации
```

---

## Ассеты блоков

Пост-блоки и HTML-блоки подключают свои файлы сами — через методы `getFrontendCss()`, `getFrontendJs()`, `getAdminCss()`, `getAdminJs()` и инлайн-варианты. Движок собирает их в общий поток и гарантирует, что файлы не продублируются.

```php
<?php
public function getFrontendCss(): array
{
    return ['templates/' . get_current_template() . '/front/assets/postblocks/calloutblock/callout.css'];
}

public function getFrontendJs(): array
{
    return ['templates/' . get_current_template() . '/front/assets/postblocks/calloutblock/callout.js'];
}
```

---

## Кеш блоков

Стили блоков собираются в отдельный файл, чтобы не грузить десятки мелких CSS:

| Функция | Назначение |
|---|---|
| `init_blocks_cache()` | Подготовить кеш — вызывается в начале `layout.php` |
| `get_blocks_css_url()` | URL собранного файла |
| `get_blocks_assets_cache_file()` / `get_blocks_css_cache_file()` | Пути к файлам кеша |
| `get_all_blocks_assets_cached()` | Прочитать кеш |
| `clear_blocks_assets_cache()` | Сбросить кеш |
| `regenerate_blocks_css()` | Пересобрать CSS |
| `minify_css($css)` | Минифицировать CSS |

Сброс происходит автоматически при сохранении и удалении HTML-блока (события `html_block.saved` / `html_block.deleted`), а вручную — кнопкой **«Очистить кеш блоков»** (`/admin/html-blocks/clear-cache`).

> [!TIP]
> Если после правок блока стили «не меняются» — почти всегда дело в кеше блоков или кеше браузера. Очистите кеш блоков и обновите страницу с `Ctrl+F5`.

---

## Изображения

```php
<?php
// Картинка из assets активной темы
$url = front_image('placeholder.png');        // templates/<тема>/front/assets/img/placeholder.png
$url = front_image('icon.svg', 'blocks');     // templates/<тема>/front/assets/img/blocks/icon.svg

<img src="<?= html($url) ?>" alt="Обложка" loading="lazy">
```

`front_image()` собирает URL картинки из **активной темы** (`templates/<тема>/front/assets/img/`), второй аргумент — подпапка внутри `img/`. Удобно для иконок и заглушек, которые различаются от темы к теме.

---

## Практики фронтенда

- 💡 **Не подключайте библиотеки повторно.** Bootstrap и jQuery уже лежат в теме — используйте их из `base_front_css()` / `base_front_js()`.
- 💡 **Проверяйте вес.** Перед публикацией сожмите изображения (`webp`/`jpeg`, ширина ≤ 1600 px) — это самый быстрый способ ускорить сайт.
- 💡 **JS — в конец документа.** Для тяжёлых скриптов используйте `add_bottom_js()`.
- 💡 **Инлайн — для маленького.** CSS на 20 строк лучше отдать инлайном, чем отдельным запросом.
- ⚠️ **Не правьте минифицированные файлы** (`bootstrap.min.css`, `jquery.min.js`) — изменения потеряются при обновлении. Свои стили держите в `main.css` или отдельном файле.
- ⚠️ **Экранируйте данные**, попадающие в JS: используйте `js_config()`/`js_var()`, а не ручную склейку строк.

---

## Отладка ассетов

```php
<?php
// Все подключённые файлы в текущем контексте
$stats = AssetManager::getInstance()->getStats();
var_dump($stats);

// Очистить очередь (например, перед своим выводом)
AssetManager::getInstance()->clear('frontend');
```

| Симптом | Причина |
|---|---|
| Стиль блока не применяется | Кеш блоков или файл не подключён через `getFrontendCss()` |
| CSS/JS подключился дважды | Файл добавлен и в `base_*`, и через `front_css()` |
| Пользовательский JS не работает | Скрипт подключён в `<head>` без `defer`; используйте `add_bottom_js()` |
| В админке нет стилей формы | Ассеты добавлены в контекст `frontend`, а выводятся в `admin` |

---

[← Шорткоды и локализация](09-shortcodes-i-lokalizaciya.md) · [Оглавление](../README.md) · [Справочник хелперов →](11-helpery.md)