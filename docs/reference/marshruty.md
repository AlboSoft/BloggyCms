# 🗺️ Маршруты

Полная карта URL движка. Маршруты объявляются модулями в `system/controllers/<модуль>/routes.php` и обрабатываются `Router` → `Controller@Action`.

**Как читать таблицу:** маршруты с пометкой 🔒 проходят через `AdminAuthMiddleware` (в `routes.php` помечены `'admin' => true`). `{...}` — параметр маршрута.

---

## Фронтенд

### Главная и контент

| URL | Модуль | Что делает |
|---|---|---|
| `/` | home | Главная страница |
| `/home` | home | То же (альтернативный адрес) |
| `/posts` | posts | Лента всех постов |
| `/post/{slug}` | posts | Пост по адресу |
| `/post/like/{id}` | posts | Поставить лайк |
| `/post/bookmark/{id}` | posts | Добавить/убрать закладку |
| `/user/bookmarks` | posts | Закладки пользователя |
| `/post/check-age/{id}` | posts | Подтверждение возраста (18+) |
| `/post/check-password/{id}` | posts | Ввод пароля к посту |
| `/page/{slug}` | pages | Страница по адресу |
| `/categories` | categories | Список категорий |
| `/category/{slug}` | categories | Посты категории |
| `/category/check-password/{id}` | categories | Пароль к категории (AJAX) |
| `/tags` | tags | Список тегов |
| `/tag/{slug}` | tags | Посты с тегом |
| `/archive` | archive | Архив по датам |
| `/search` | search | Поиск |
| `/search/type/{type}` | search | Поиск по типу контента |
| `/users` | users | Список пользователей |
| `/users/achievements` | users | Достижения |
| `/achievement/{id}` | users | Достижение по id |
| `/fragment/{systemName}` | fragments | Вывод фрагмента |
| `/block/{slug}` | html_blocks | Вывод HTML-блока |
| `/profile` | profile | Профиль (свой) |
| `/profile/{username}` | profile | Профиль пользователя |
| `/profile/edit`, `/profile/update` | profile | Редактирование профиля |
| `/profile/sessions` | profile | Активные сессии |
| `/profile/terminate-session`, `/profile/terminate-all-sessions` | profile | Завершение сессий |
| `/profile/delete` | profile | Удаление аккаунта |

### Авторизация

| URL | Что делает |
|---|---|
| `/login` | Форма входа |
| `/register` | Регистрация |
| `/logout` | Выход |
| `/forgot-password` | Запрос восстановления пароля |
| `/reset-password` | Установка нового пароля по токену |

### Комментарии

| URL | Что делает |
|---|---|
| `/comment/add` | Добавить комментарий |
| `/comment/edit/{id}` | Редактировать свой |
| `/comment/delete/{id}` | Удалить свой |
| `/comment/get/{id}` | Получить комментарий (AJAX) |

### SEO / служебные

| URL | Что отдаёт |
|---|---|
| `/sitemap.xml` | Карта сайта |
| `/robots.txt` | Правила для роботов |
| `/rss.xml` | Основная RSS-лента |
| `/rss/category/{slug}` | Лента категории |
| `/rss/tag/{slug}` | Лента тега |
| `/{key}.txt` | Файл ключа IndexNow |

---

## Админка 🔒

### Основное

| URL | Назначение |
|---|---|
| `/admin` | Дашборд |
| `/admin/login`, `/admin/logout` | Вход и выход администратора |
| `/admin/check-updates` | Проверка обновлений движка |
| `/admin/delete-install-folder` | Удалить папку установки |
| `/admin/stats/data`, `/admin/stats/export-html` | Статистика и её экспорт |

### Посты, страницы, таксономия

| URL | Назначение |
|---|---|
| `/admin/posts`, `/create`, `/edit/{id}`, `/delete/{id}`, `/toggle-status/{id}` | Управление постами |
| `/admin/posts/upload-featured-image`, `/upload-gallery-images`, `/upload-block-image` | Загрузка изображений поста |
| `/admin/pages`, `/create`, `/edit/{id}`, `/delete/{id}`, `/upload-image` | Управление страницами |
| `/admin/categories`, `/create`, `/edit/{id}`, `/delete/{id}`, `/reorder`, `/upload-image` | Категории |
| `/admin/tags`, `/create`, `/edit/{id}`, `/delete/{id}`, `/search`, `/create-ajax` | Теги |
| `/admin/comments`, `/edit/{id}`, `/delete/{id}`, `/approve/{id}` | Комментарии |

### Блоки, поля, фрагменты

| URL | Назначение |
|---|---|
| `/admin/html-blocks`, `/select-type`, `/create`, `/edit/{id}`, `/delete/{id}`, `/types` | HTML-блоки |
| `/admin/html-blocks/types/toggle/{systemName}`, `/types/delete/{systemName}` | Типы блоков |
| `/admin/html-blocks/get-block-settings`, `/get-block-assets`, `/get-block-templates`, `/get-fragments` | AJAX-данные блоков |
| `/admin/html-blocks/clear-cache` | Очистка кеша блоков |
| `/admin/post-blocks`, `/edit/{system_name}`, `/edit`, `/get-preview`, `/get-previews` | Пост-блоки |
| `/admin/post-blocks/get-block-blueprint`, `/get-settings-form`, `/get-default-content`, `/get-default-settings`, `/get-template` | Данные блока |
| `/admin/post-blocks/save-block`, `/save-block-data`, `/save-settings`, `/upload-block-files`, `/render-sample` | Сохранение и настройка |
| `/admin/post-blocks/get-presets`, `/create-preset`, `/update-preset`, `/delete-preset` | Пресеты блоков |
| `/admin/fields`, `/entity/{entityType}`, `/create/{entityType}`, `/edit/{id}`, `/delete/{id}`, `/toggle/{id}`, `/get-settings/{type}` | Пользовательские поля |
| `/admin/fragments`, `/create`, `/edit/{id}`, `/delete/{id}`, `/fields/{id}`, `/entries/{id}` | Фрагменты |
| `/admin/fragments/field/create/{fragment_id}`, `/field/edit/{id}`, `/field/delete/{id}`, `/field/reorder` | Поля фрагментов |
| `/admin/fragments/entry/create/{id}`, `/entry/edit/{id}`, `/entry/delete/{id}`, `/reorder-entries` | Записи фрагментов |

### Пользователи

| URL | Назначение |
|---|---|
| `/admin/users`, `/create`, `/edit/{id}`, `/delete/{id}`, `/toggle-status/{id}` | Пользователи |
| `/admin/users/manage-groups/{id}`, `/quick-assign-achievement/{userId}` | Группы и достижения пользователя |
| `/admin/user-groups`, `/create`, `/edit/{id}`, `/delete/{id}`, `/permissions/{id}` | Группы и права |
| `/admin/user-achievements`, `/create`, `/edit/{id}`, `/delete/{id}`, `/toggle/{id}` | Достижения |
| `/admin/user-achievements/assign/{userId}`, `/unassign/{userId}/{achievementId}` | Выдача и снятие достижений |

### Настройки и сервис

| URL | Назначение |
|---|---|
| `/admin/settings`, `/reset`, `/cleanup-backups` | Настройки |
| `/admin/upload/settings-image` | Загрузка изображений настроек |
| `/admin/language`, `/admin/language/save` | Языки интерфейса |
| `/admin/icons`, `/admin/icons/data` | Иконки |
| `/admin/menu`, `/create`, `/edit/{id}`, `/delete/{id}`, `/items/{id}`, `/reorder`, `/preview/{id}` | Меню |
| `/admin/menu/item/create/{menuId}`, `/item/edit/{id}`, `/item/delete/{id}` | Пункты меню |
| `/admin/templates`, `/get-files`, `/get-file`, `/save`, `/download-file`, `/upload-file`, `/create-front-folder` | Редактор шаблонов |
| `/admin/controllers`, `/admin/controllers/toggle` | Модули |
| `/admin/seo`, `/settings`, `/schema`, `/clear-cache`, `/test-indexnow`, `/process-queue` | SEO |
| `/admin/debug`, `/logs`, `/log/{id}`, `/delete/{id}`, `/delete-all`, `/mark-fixed/{id}`, `/stats`, `/toggle` | Отладка |
| `/admin/notifications`, `/get-unread-count`, `/get-list`, `/mark-as-read/{id}`, `/mark-all-read`, `/delete/{id}`, `/clear` | Уведомления |
| `/admin/search-history`, `/delete/{id}`, `/clear` | История поиска |
| `/admin/addons`, `/install`, `/upload`, `/analyze`, `/info/{id}`, `/delete/{id}`, `/check-updates` | Дополнения |
| `/admin/home/settings` | Настройки главной страницы |

---

## Как это устроено в коде

```php
<?php
// system/controllers/posts/routes.php
return [
    'posts'       => ['controller' => 'Post', 'action' => 'all'],
    'post/{slug}' => ['controller' => 'Post', 'action' => 'show'],
    'admin/posts' => ['controller' => 'Post', 'action' => 'adminIndex', 'admin' => true],
];
```

- `controller` → класс `<Имя>Controller`;
- `action` → метод `<имя>Action()`;
- `admin => true` → обязательная авторизация администратора;
- `{slug}` → аргумент метода (передаётся по имени).

Внутри роутера действует порядок: при совпадении используется первый найденный маршрут. Реальные файлы (картинки, CSS) обрабатываются веб-сервером и до роутера не доходят.

---

## Полезные команды

```bash
# Все маршруты одного модуля
grep -oP "^\s*'\K[^']+" system/controllers/posts/routes.php

# Маршруты программно
php -r "require 'index.php'; print_r(RouteHelper::getAllFrontendRoutes());"
```

Проверить конкретный URL удобно в браузере: `https://ваш-сайт/posts`.

---

[← Константы](konstanty.md) · [Оглавление](../README.md) · [Таблицы базы данных →](tablicy-bd.md)