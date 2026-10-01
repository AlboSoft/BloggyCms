# 📌 Константы

Справочник по константам движка: где определяются, что означают, когда ими пользоваться.

---

## Пути

Определяются в `system/config/config.php` (создаётся установщиком).

| Константа | Пример значения | Назначение |
|---|---|---|
| `ROOT_PATH` | `/var/www/bloggycms` | Корень сайта (задаётся в `index.php`) |
| `BASE_PATH` | `/var/www/bloggycms` | То же, что `ROOT_PATH`; основная константа путей |
| `SYSTEM_PATH` | `…/system` | Папка движка |
| `TEMPLATES_PATH` | `…/templates` | Папки тем |
| `UPLOADS_PATH` | `…/uploads` | Пользовательские файлы |
| `LANGUAGES_PATH` | `…/system/languages` | Языковые файлы |
| `CONTROLLERS_PATH` | `…/system/controllers` | Модули |
| `CACHE_DIR` | `…/cache` | Кеш (блоки, ассеты) |
| `ADDONS_TEMP_DIR` | `…/uploads/temp_addon/` | Временная папка установки пакетов |

```php
<?php
$file = SYSTEM_PATH . '/helpers/Helper.php';
$css  = TEMPLATES_PATH . '/' . get_current_template() . '/front/assets/css/main.css';
```

---

## URL и маршрутизация

| Константа | Пример | Назначение |
|---|---|---|
| `BASE_URL` | `https://example.com` | Адрес сайта **без** слэша на конце |
| `ADMIN_URL` | `https://example.com/admin` | Адрес панели управления |
| `DEFAULT_TEMPLATE` | `default` | Тема по умолчанию (fallback) |
| `USER_ONLINE_INTERVAL` | `300` | Сколько секунд считать пользователя онлайн |

```php
<?php
<a href="<?= BASE_URL ?>/posts">Посты</a>
<a href="<?= ADMIN_URL ?>/settings">Настройки</a>
```

> [!TIP]
> `BASE_URL` меняется в **Настройках → Сайт** (`base_url`). Если сайт переехал на другой домен, а письма ведут на старый адрес — проверьте эту настройку и файл `system/config/config.php`.

---

## База данных

Определяются в `system/config/database.php`.

| Константа | Пример | Назначение |
|---|---|---|
| `DB_HOST` | `localhost` | Сервер базы |
| `DB_NAME` | `bloggycms` | Имя базы |
| `DB_USER` | `bloggy` | Пользователь |
| `DB_PASS` | `••••••` | Пароль |
| `DB_PREFIX` | `bloggy_` | Префикс таблиц |
| `DB_CHARSET` | `utf8mb4` | Кодировка |
| `DB_COLLATE` | `utf8mb4_unicode_ci` | Сравнение |

```php
<?php
$table = DB_PREFIX . 'posts';       // полное имя таблицы
$prefix = Database::getInstance()->getPrefix();
```

> [!WARNING]
> Не меняйте `DB_PREFIX` после установки: все SQL-запросы в шаблонах и модулях используют таблицы без префикса, но данные в старой базе останутся под старым именем.

---

## Во время выполнения

| Константа | Когда определяется | Назначение |
|---|---|---|
| `CURRENT_LOCALE` | `index.php` | Текущая локаль (`ru_RU`, `en_En`, …) |
| `CURRENT_CONTROLLER` | `Controller::__construct()` | Имя активного контроллера |
| `DEBUG` | При необходимости | Включает вывод ошибок вместо страницы 500 |

```php
<?php
if (CURRENT_LOCALE === 'ru_RU') { /* … */ }
```

---

## Константы локализации

Все тексты интерфейса — константы `LANG_*`, определяются в `system/languages/<локаль>/…`.

| Префикс | Область |
|---|---|
| `LANG_CORE_*` | Ядро |
| `LANG_CONTROLLER_*` | Данные модулей (манифест, настройки) |
| `LANG_ACTION_*` | Строки экшенов |
| `LANG_TEMPLATE_*` | Шаблоны |
| `LANG_HELPER_*` | Хелперы |
| `LANG_FIELD_*` | Поля |
| `LANG_POSTBLOCK_*` | Пост-блоки |
| `LANG_HTMLBLOCK_*` | HTML-блоки |

```php
<?php
echo LANG_CORE_CONTROLLER_DEFAULT_PAGE_TITLE;
echo LANG_TEMPLATE_POSTS_CREATE_TITLE;
```

Полностью о локализации — [Шорткоды и локализация](../developer/09-shortcodes-i-lokalizaciya.md#локализация).

---

## Как безопасно работать с константами

Не все константы определены всегда (например, `DEBUG` появляется только при отладке). Проверяйте:

```php
<?php
if (defined('DEBUG') && DEBUG === true) {
    // …
}

// Хелпер делает то же самое
if (ConstantHelper::isDefined('DEBUG')) {
    $value = ConstantHelper::get('DEBUG', false);
}
```

Список всех определённых констант локализации:

```php
<?php
print_r(ConstantHelper::getDefinedLanguageConstants());
```

---

## Частые ошибки

| Ошибка | Причина | Решение |
|---|---|---|
| `Undefined constant "BASE_URL"` | Код выполняется до подключения конфигурации | Проверьте, что файл подключён после `index.php` |
| `BASE_URL` со слэшем на конце | В настройках указан `https://site.ru/` | Движок обрезает слэш; в своём коде используйте `rtrim($url, '/')` |
| Ссылки ведут на старый домен | `base_url` не обновлён после переезда | **Настройки → Сайт** |
| Не находятся файлы темы | Используется `BASE_PATH` вместо `TEMPLATES_PATH` | Проверьте путь |
| `DB_PREFIX` пуст | Конфигурация повреждена | Переустановите/восстановите `database.php` |

---

[← Стиль кода](../developer/13-stil-koda-i-soglasheniya.md) · [Оглавление](../README.md) · [Маршруты →](marshruty.md)