<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset=".github/assets/logo-dark.png">
    <source media="(prefers-color-scheme: light)" srcset=".github/assets/logo-light.png">
    <img src=".github/assets/logo-light.png" alt="BloggyCMS" width="112">
  </picture>
</p>

<h1 align="center">BloggyCMS</h1>

<p align="center">
  <b>Модульная CMS с открытым исходным кодом для блогов и контентных сайтов.</b><br>
  Блоки вместо WYSIWYG · Хуки вместо правок ядра · Только PHP 8 и MySQL — и ничего больше.
</p>

<p align="center">
  <a href="https://github.com/albosoft/BloggyCms/releases"><img src="https://img.shields.io/badge/version-1.0.0--RC-FF6B35?style=flat-square" alt="Версия"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.0+"></a>
  <a href="https://www.mysql.com"><img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 5.7+"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-3DA639?style=flat-square" alt="Лицензия MIT"></a>
  <a href="#требования"><img src="https://img.shields.io/badge/dependencies-none-2ea44f?style=flat-square" alt="Без зависимостей"></a>
  <a href="system/languages"><img src="https://img.shields.io/badge/UI-RU%20%7C%20EN-8957e5?style=flat-square" alt="Русский и английский интерфейс"></a>
  <a href="https://github.com/albosoft/BloggyCms/stargazers"><img src="https://img.shields.io/github/stars/albosoft/BloggyCms?style=flat-square&logo=github&color=gold" alt="Звёзды"></a>
  <a href="https://github.com/albosoft/BloggyCms/discussions"><img src="https://img.shields.io/badge/discussions-join-5865F2?style=flat-square&logo=github&logoColor=white" alt="Обсуждения"></a>
</p>

<p align="center">
  <a href="https://bloggy.su">Сайт проекта</a> ·
  <a href="https://github.com/albosoft/BloggyCms/wiki">Документация</a> ·
  <a href="https://github.com/albosoft/BloggyCms/releases">Релизы</a> ·
  <a href="https://github.com/albosoft/BloggyCms/discussions">Обсуждения</a> ·
  <a href="https://github.com/AlboSoft/BloggyOfficial/issues/new/choose">Сообщить об ошибке</a>
</p>

<p align="center">
  <a href="README.md">English</a> · <b>Русский</b>
</p>

---

> [!NOTE]
> BloggyCMS находится в стадии **Release Candidate 1.0.0** (последняя опубликованная сборка — **v1.0.0-rc.9**). Система стабильна и готова к работе, но отдельные детали API могут незначительно измениться до выхода стабильной версии 1.0.

## Содержание

- [Почему BloggyCMS](#почему-bloggycms)
- [Возможности](#возможности)
- [Требования](#требования)
- [Установка](#установка)
- [Структура проекта](#структура-проекта)
- [Архитектура](#архитектура)
- [Расширение BloggyCMS](#расширение-bloggycms)
- [FAQ](#faq)
- [Как помочь проекту](#как-помочь-проекту)
- [Безопасность](#безопасность)
- [Поддержать проект](#поддержать-проект)
- [Лицензия](#лицензия)

---

## Почему BloggyCMS

| | |
|---|---|
| ⚡ **Никакой сборки** | Без Composer, npm, консольных команд и миграций. Загрузите файлы, откройте домен и пройдите мастер из четырёх шагов. |
| 🧩 **Всё — модуль** | 26 встроенных контроллеров, каждый самодостаточен: `manifest.php`, `routes.php`, `Model.php`, `actions/`, `permissions.php`, `hooks/`. |
| 🧱 **Блоки вместо WYSIWYG** | 16 пост-блоков и 12 HTML-блоков «из коробки». Контент остаётся структурированной разметкой, а не мешаниной из инлайн-стилей. |
| 🪝 **Событийное ядро** | `Event::listen()`, `Event::trigger()`, `Event::filter()` с приоритетами. Расширяйте что угодно, не трогая файлы ядра. |
| 🔐 **Безопасность внутри** | Права по группам пользователей, CSRF-токены, middleware для админки, капча (математическая, текстовая, графическая, honeypot), защита от перебора паролей, проверка загружаемых файлов. |
| 🎨 **Темы и шаблоны блоков** | Переопределяйте любой файл шаблона — или разметку отдельного блока — без форка CMS. |
| 🌍 **Двуязычность из коробки** | Интерфейс движка на русском и английском, контент и меню — на любом языке. |
| 📦 **Менеджер дополнений** | Загрузите ZIP-пакет, анализатор его проверит, установка и удаление — прямо из админ-панели. |

## Возможности

### Контент

| Возможность | Что внутри |
|---|---|
| **Посты и страницы** | Категории, теги, архивы, черновики, обложки, галереи, лайки и закладки. |
| **Приватность** | Посты под паролем и возрастной барьер для контента 18+ (`post/check-age`, `post/check-password`). |
| **Пост-блоки** | 16 блоков для редактора: текст, заголовки, изображения, «изображение с текстом», галереи, видео, аудио, цитаты, списки, код, алерты, кнопки, спойлеры, произвольный HTML, вставка постов и переиспользуемые контентные блоки. |
| **HTML-блоки** | 12 блоков, работающих и как секции страницы, и как самостоятельные «мини-плагины»: шапка, подвал, hero, последние посты, услуги, категории, теги, карточка автора, дерево страниц, хлебные крошки, cookie-уведомление, кнопка «вверх». |
| **Фрагменты** | Переиспользуемые сущности контента, которые можно выводить в любом месте шаблона. |
| **Пользовательские поля** | 12 типов полей — `string`, `text`, `number`, `date`, `color`, `select`, `multiselect`, `flag`, `image`, `link`, `icon_with_style`, `html` — плюс повторители (repeater), блочные изображения и алерты. Поля привязываются к постам, страницам, пользователям, категориям и другим сущностям. |
| **Комментарии** | Модерация, права на редактирование и удаление, поддержка капчи, настройки для каждого контроллера. |
| **Меню** | Визуальный конструктор меню с сортировкой перетаскиванием и парсером произвольных меню. |
| **Уведомления и достижения** | Уведомления в админке и достижения по правилам (дни с регистрации, комментарии, лайки, закладки, входы). |

### Архитектура

Ядро намеренно компактное и читаемое — 24 класса ядра, 40 хелперов и никакого фреймворка «под капотом».

| Компонент | Роль |
|---|---|
| `App` / `Router` / `RouteManager` | Запуск запроса, загрузка хуков контроллеров, диспетчеризация маршрутов, объявленных в модулях. |
| `Event` | Слой «издатель — подписчик»: `listen()`, `trigger()`, `filter()`, `unlisten()`, приоритеты, отложенные слушатели до инициализации. |
| `Controller` / `ControllerManager` | Базовый контроллер и автообнаружение модулей, их манифестов, настроек и прав. |
| `Middleware` / `AdminAuthMiddleware` | Цепочка middleware для маршрутов: аутентификация администратора и проверка доступа. |
| `Database` / `DatabaseRegistry` / `Model` | Прозрачный слой данных без магии ORM — только подготовленные запросы. |
| `ModelAPI` / `APIAware` | Публикация методов моделей как вызываемого API для модулей и шаблонов. |
| `PermissionManager` | Читает `permissions.php` каждого контроллера и проверяет права пользователя. |
| `PostBlockManager` / `HtmlBlockTypeManager` | Обнаружение, подключение ассетов и рендеринг пост-блоков и HTML-блоков. |
| `FieldManager` / `FieldFactory` / `BaseField` | Реестр пользовательских полей, регистрация шорткодов и обработка значений. |
| `AssetManager` | Сбор CSS/JS, включая ассеты отдельных блоков, и вывод их в правильном порядке. |
| `AdminForm` / `Fieldset` | Декларативные формы админки: филдсеты, валидация, CSRF, обработка запроса. |
| `Shortcodes` | Синтаксис `{name}` / `{name attr="value"}…{/name}` для вставки контента. |
| `DebugHandler` / `DebugLogger` | Перехват нотисов и ошибок с выводом в дебаггер админ-панели. |

### SEO и производительность

- `sitemap.xml` и `rss.xml` (а также ленты по категориям и тегам), автоматически обновляемые при изменении контента через хуки модулей.
- Отдельный контроллер SEO с настройками, хуками и обработкой изменений контента.
- Asset-менеджер с агрегацией CSS/JS по блокам, менеджер хлебных крошек и хелперы пагинации.
- Дебаггер, который отлавливает нотисы и предупреждения и на фронтенде, и в админке.

### Админ-панель

- Дашборд с живой статистикой (с экспортом в HTML) и быстрыми действиями.
- Поиск по сайту и история поисковых запросов в админке.
- Встроенный редактор файлов шаблона с автоматическим резервным копированием изменённых файлов.
- Менеджер контроллеров: просмотр модулей, их манифестов и настроек.
- Проверка обновлений и удаление папки `install/` в один клик — сразу после запуска сайта.

## Требования

| Компонент | Минимум |
|---|---|
| **PHP** | 8.0 и выше (рекомендуется 8.3+) |
| **Расширения PHP** | `pdo_mysql`, `mysqli`, `mbstring`, `json`, `fileinfo`, `session`, `openssl` |
| **База данных** | MySQL 5.7+ / MariaDB 10.2+ с кодировкой `utf8mb4` |
| **Веб-сервер** | Apache с `mod_rewrite` (или Nginx — см. конфиг ниже) |
| **Права на запись** | `uploads/`, `system/config/`, `templates/` |
| **Браузер** | Любой современный |

> [!TIP]
> Установщик сам проверит всё перечисленное и покажет, чего не хватает, ещё до записи файлов конфигурации.

## Установка

### 1. Получите код

```bash
git clone https://github.com/AlboSoft/BloggyOfficial.git bloggycms
```

…либо скачайте архив со [страницы релизов](https://github.com/albosoft/BloggyCms/releases) и загрузите его в корень сайта.

### 2. Создайте базу данных

```sql
CREATE DATABASE bloggycms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bloggy'@'localhost' IDENTIFIED BY 'strong-password';
GRANT ALL PRIVILEGES ON bloggycms.* TO 'bloggy'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Выставьте права

```bash
chmod -R 755 uploads system/config templates
```

### 4. Запустите мастер установки

Откройте `https://ваш-домен/install/` — установщик двуязычный (RU / EN) и занимает около двух минут:

1. **Лицензия** — ознакомление и принятие условий MIT.
2. **Проверка окружения** — версия PHP, расширения и права на директории.
3. **База данных** — хост, имя, пользователь, пароль, префикс таблиц, опционально демо-данные.
4. **Администратор** — название сайта, аккаунт администратора, язык интерфейса.

### 5. Запуск

Войдите по адресу `https://ваш-домен/admin` и удалите папку `install/` — в админ-панели для этого есть кнопка в один клик.

### Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.example;
    root /var/www/bloggycms;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Внутренние файлы движка не должны быть доступны напрямую
    location ^~ /system/  { deny all; }
    location ^~ /install/ { deny all; }  # включите после установки
}
```

С Apache всё работает сразу — благодаря поставляемому `.htaccess`.

## Структура проекта

```text
BloggyCMS/
├── index.php                  # Единая точка входа и бутстрап
├── .htaccess                  # Правила rewrite, лимиты загрузки, защита директорий
├── install/                   # Двуязычный мастер установки из 4 шагов + демо-данные
├── system/
│   ├── config/                # version.ini, создаваемые config.php / database.php
│   ├── controllers/           # 26 модулей (posts, pages, users, seo, addons, …)
│   ├── core/                  # 24 класса ядра: Event, Router, App, Database, …
│   ├── fields/                # 12 типов пользовательских полей
│   ├── helpers/               # 40 хелперов: API, Auth, CSRF, Captcha, Backup, …
│   ├── html_blocks/           # 12 HTML-блоков для страниц и лейаутов
│   ├── languages/             # Наборы локалей ru_RU и en_En
│   └── post_blocks/           # 16 контентных блоков для редактора постов
├── templates/
│   └── default/
│       ├── front/             # Тема фронтенда: лейауты, партиалы, ассеты, виды блоков
│       └── admin/             # Тема админ-панели
├── uploads/                   # Пользовательский контент: изображения, аватары, теги, настройки
└── LICENSE                    # MIT
```

### Анатомия модуля

Каждый контроллер устроен одинаково и предсказуемо — просто добавьте папку, и CMS его найдёт:

```text
system/controllers/posts/
├── PostController.php     # Класс контроллера
├── Model.php              # Модель данных (реализует ModelAPI)
├── manifest.php           # Название, автор, версия, описание, флаги
├── routes.php             # Карта URL, с флагом admin: true для защищённых маршрутов
├── permissions.php        # Ключи прав, доступные группам пользователей
├── Settings.php           # Форма настроек, которую выводит ControllerManager
├── actions/               # По одному классу на действие (Index, Create, Edit, Delete, …)
└── hooks/                 # Файлы, загружаемые при старте — здесь живут слушатели событий
```

## Архитектура

```text
Запрос ──▶ index.php ──▶ App::run()
                           │
                           ├─ бутстрап: config → Database → классы ядра
                           ├─ загрузка hooks/ всех контроллеров (слушатели Event)
                           ├─ цепочка middleware (аутентификация, защита админки)
                           └─ Router ──▶ Controller@Action ──▶ render(шаблон)
                                                                   │
                             PostBlockManager · HtmlBlockTypeManager · FieldManager
                             AssetManager · Shortcodes · Breadcrumbs · Event::filter
```

Рендеринг компонуемый: шаблон страницы подтягивает HTML-блоки, пост — пост-блоки, внутри контента разворачиваются поля и шорткоды, а каждый шаг вызывает события, на которые можно подписаться.

## Расширение BloggyCMS

### Подписка на событие

Создайте `system/controllers/my_module/hooks/events.php` — файл загрузится автоматически:

```php
<?php

Event::listen('post.created', function (array $data) {
    // $data содержит id нового поста, url и полезную нагрузку
    Logger::info('Post created: ' . ($data['url'] ?? ''));
}, 10, 1);

// Изменение значения «на выходе»
Event::filter('post.excerpt', function (string $excerpt, array $post) {
    return mb_substr($excerpt, 0, 180) . '…';
}, 20, 2);
```

### Свой пост-блок

```php
<?php

class CalloutBlock extends BasePostBlock
{
    public function getName(): string        { return 'Callout'; }
    public function getSystemName(): string  { return 'CalloutBlock'; }
    public function getDescription(): string { return 'Заметка с выделением и ссылкой.'; }
    public function getIcon(): string        { return 'bi bi-megaphone'; }

    public function getSettingsForm($currentSettings = []): string
    {
        $fieldset = new \Fieldset('Callout', [
            'fields' => [
                \FieldFactory::select('tone', [
                    'title'   => 'Тон',
                    'options' => ['info' => 'Информация', 'warning' => 'Предупреждение'],
                ]),
            ],
        ]);

        return $fieldset->render($currentSettings);
    }

    public function getContentForm($currentContent = []): string { /* интерфейс редактора */ }
    public function getEditorHtml($settings = [], $content = []): string { /* превью */ }
}
```

Положите файл в `system/post_blocks/` — блок сразу появится в редакторе. HTML-блоки, поля и контроллеры работают по тому же принципу «один файл — автообнаружение».

### Регистрация шорткода

```php
Shortcodes::add('year', function ($attrs) {
    return date('Y');
});

// Использование в любом контенте: {year}
```

## FAQ

<details>
<summary><b>Нужны ли Composer, Node.js и сборка?</b></summary>
<br>
Нет. Все фронтенд-ассеты (Bootstrap, jQuery, иконки, скрипты редактора) лежат внутри темы. Загрузили — и работает.
</details>

<details>
<summary><b>Потеряю ли я контент при обновлении движка?</b></summary>
<br>
Нет. Данные хранятся в MySQL, загруженные файлы — в <code>uploads/</code>, а доступы — в <code>system/config/config.php</code> и <code>database.php</code>. При замене файлов движка они не затрагиваются. Сделайте бэкап базы и папки <code>uploads/</code> — и вы в безопасности.
</details>

<details>
<summary><b>Заработает ли на shared-хостинге?</b></summary>
<br>
Да, это одна из целей проекта. Достаточно PHP 8, MySQL и Apache с <code>mod_rewrite</code> — без shell-доступа, демонов и cron.
</details>

<details>
<summary><b>Как добавить язык в интерфейс админки?</b></summary>
<br>
Скопируйте <code>system/languages/en_En/</code> в папку новой локали и переведите языковые файлы — движок загружает набор локали, соответствующей выбранному языку.
</details>

<details>
<summary><b>Можно ли использовать Nginx?</b></summary>
<br>
Да, воспользуйтесь конфигом выше. Поставляемый <code>.htaccess</code> работает только на Apache.
</details>

## Как помочь проекту

<img src="https://img.shields.io/badge/PRs-welcome-brightgreen?style=flat-square" alt="PRs welcome">

1. **Сначала обсудите** идею, если это не просто исправление бага — всё для этого есть в [GitHub Discussions](https://github.com/albosoft/BloggyCms/discussions).
2. **Сделайте форк**, ветвитесь от `main` и держите коммиты сфокусированными.
3. **Используйте шаблоны issue**: [баг-репорт](https://github.com/AlboSoft/BloggyOfficial/issues/new/choose) или [запрос функциональности](https://github.com/AlboSoft/BloggyOfficial/issues/new/choose).
4. **Соблюдайте стиль проекта**: синтаксис PHP 8, форматирование в духе PSR-12, никаких сторонних runtime-зависимостей и шага сборки, а все строки интерфейса — в языковых файлах (никакого текста «в лоб» в шаблонах).

Самый дружелюбный способ помочь — добавить модуль: он целиком живёт в своей папке в `system/controllers/` или `system/post_blocks/`, поэтому ревью проходит быстро.

## Безопасность

Если вы нашли уязвимость, **не создавайте публичный issue**. Воспользуйтесь [приватными security advisory в GitHub](https://github.com/albosoft/BloggyCms/security/advisories/new), чтобы исправление можно было подготовить до раскрытия. Приложите шаги воспроизведения, затронутую версию и оценку влияния — мы постараемся ответить как можно быстрее.

## Поддержать проект

BloggyCMS развивается в свободное время и остаётся бесплатным по лицензии MIT.

- ⭐ Поставьте звезду [основному репозиторию](https://github.com/albosoft/BloggyCms) — это правда помогает.
- 💬 Отвечайте на вопросы в [обсуждениях](https://github.com/albosoft/BloggyCms/discussions).
- 💛 [Поддержать разработку через YooMoney](https://yoomoney.ru/to/4100119401729712).

## Лицензия

Распространяется по [лицензии MIT](LICENSE) — свободно для личного и коммерческого использования.
Copyright © 2026 [Pechora.Dev](https://github.com/albosoft).

<p align="center">
  <sub>Независимые зеркала и форки приветствуются. Пишите где угодно.</sub>
</p>
