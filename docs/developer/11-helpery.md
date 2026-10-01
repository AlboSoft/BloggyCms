# 🧰 Справочник хелперов

В `system/helpers/` лежат 40 файлов: классы-хелперы и набор глобальных функций. Почти всё, что нужно модулю, уже есть — этот документ заменяет чтение исходников.

**Как быстро проверить, что доступно из вашего кода:**

```bash
ls system/helpers/                                   # список хелперов
grep -rn "SettingsHelper::get" system/ | head        # примеры реального использования
```

---

## Глобальные функции (Helper.php)

Доступны везде: в шаблонах, экшенах, блоках.

| Функция | Что делает |
|---|---|
| `render_html_block(string $slug)` | Вывести HTML-блок по slug |
| `get_html_block_type_manager($db = null)` | Получить менеджер типов блоков |
| `front_image(string $file, string $subpath = '')` | URL картинки из `templates/<тема>/front/assets/img/`; 2-й аргумент — подпапка |
| `get_home_url()`, `go_home()` | Адрес главной / редирект на главную |
| `get_current_template()` | Имя активной темы |
| `favicon($path = null)` | URL фавикона |
| `format_date($date)` | Дата с локализованным месяцем |
| `time_ago($date)` | «5 минут назад» |
| `format_number(int $number)` | Число с разделителями |
| `plural($number, $titles)` | Склонения |
| `selected($value, $current)`, `checked($value, $current)` | Атрибуты форм |
| `is_block_available_for_template($t)` | Доступен ли блок текущей теме |
| `init_blocks_cache()`, `get_blocks_css_url()`, `regenerate_blocks_css()`, `clear_blocks_assets_cache()`, `get_all_blocks_assets_cached()` | Кеш ассетов блоков |
| `minify_css(string $css)` | Минификация CSS |
| `load_block_js_assets($block)` | Подключить JS блока |

## Текст и вывод (Text.php)

| Функция | Что делает |
|---|---|
| `html($value)` | **Экранирование вывода** — используйте всегда |
| `bloggy_icon($set, $icon, $size = null, $color = null, $class = null)` | SVG-иконка из наборов темы |
| `truncate_text($text, $length = 100, $append = '...')` | Обрезка |
| `strip_html_and_truncate($html, $length = 100)` | Обрезка с удалением тегов |
| `highlight_search_terms($text, $query, $class = 'search-highlight')` | Подсветка найденного |
| `plural_form($number, array $forms)` | Склонения по формам |
| `get_numeric_ending($number, $endings)` | Окончание числительного |

```php
<?php
echo bloggy_icon('bs', 'rocket-takeoff', 24, '#ff6b35');
echo html($post['title']);
echo plural($count, ['комментарий', 'комментария', 'комментариев']);
```

---

## Настройки — `SettingsHelper`

| Метод | Назначение |
|---|---|
| `get($group, $key = null, $default = null)` | Значение настройки или вся группа |
| `save($group, array $settings)` | Сохранить группу |
| `merge($group, array $settings)` | Слить с текущими значениями |
| `getAllGroups()` | Список групп |
| `clearCache()`, `updateCache($group, $data)` | Управление кешем |
| `getBaseUrl()`, `getCurrentTemplate()` | Частые значения |

```php
<?php
$siteName = SettingsHelper::get('general', 'site_name', 'BloggyCMS');
$perPage  = (int) SettingsHelper::get('controller_posts', 'homepage_posts_per_page', 10);
```

## Пользователь и права — `Auth`, `AuthHelper`

```php
<?php
Auth::init($db);              // вызывается движком
Auth::isLoggedIn();           // авторизован ли
Auth::isAdmin();              // админ ли
Auth::getUserId();
Auth::getUsername();
Auth::getDisplayName();
Auth::getAvatar();
Auth::getUser();              // весь массив пользователя

AuthHelper::can('comment_add');                  // есть ли право
AuthHelper::canPostComments($postId);
AuthHelper::canAddComment($postId);
AuthHelper::canAddCommentWithoutModeration($postId);
AuthHelper::canEditComment($commentUserId);
AuthHelper::canDeleteComment($commentUserId);
AuthHelper::canViewAllComments();
```

## Безопасность форм — `CsrfToken`

```php
<?php
echo CsrfToken::field('my_form');                 // <input type="hidden" name="csrf_token" ...>
CsrfToken::verify($_POST['csrf_token'], 'my_form'); // true/false; токен одноразовый, живёт 1 час
```

## Капча — `CaptchaHelper`

```php
<?php
$captcha = CaptchaHelper::generate(CaptchaHelper::TYPE_MATH, [
    'min' => 1, 'max' => 10,
]);
// $captcha содержит данные для вывода и идентификатор

echo CaptchaHelper::render($captcha, $settings);   // разметка капчи

CaptchaHelper::verify($captchaId, $userAnswer);    // проверка ответа
CaptchaHelper::verifyHoneypot($_POST);             // проверка honeypot-поля
CaptchaHelper::checkRateLimit('comment_form', $ip, $settings); // лимит частоты
```

Типы: `TYPE_MATH` (пример), `TYPE_TEXT`, `TYPE_IMAGE`, плюс honeypot. Устаревшие капчи чистятся автоматически.

## Почта — `Email`

```php
<?php
Email::sendPasswordReset($email, $token, $username);
Email::sendPasswordChanged($email, $username);
Email::sendWelcomeEmail($email, $username);
```

## Уведомления — `Notification`

Тосты, которые показываются в интерфейсе (через сессию).

```php
<?php
Notification::success('Пост сохранён');
Notification::error('Не удалось сохранить');
Notification::warning('Файл слишком большой');
Notification::info('Это займёт минуту');
```

> [!TIP]
> После `Notification::success()` всегда делайте `redirect()` — иначе пользователь увидит уведомление при обновлении страницы ещё раз, а форма может отправиться повторно.

## Хлебные крошки — `BreadcrumbsManager` / `BreadcrumbsHelper`

```php
<?php
$bc = BreadcrumbsHelper::getManager();      // или new BreadcrumbsManager($db)
$bc->add(LANG_HOME, BASE_URL);
$bc->add($post['title']);                   // текущий элемент — без ссылки
$bc->prepend('Админка', ADMIN_URL);

$bc->getAll(); $bc->isEmpty(); $bc->count(); $bc->getLast();
echo $bc->render();                          // HTML
$bc->toArray(); $bc->toJson();               // для API
```

## Пагинация — `PaginationHelper`

```php
<?php
echo PaginationHelper::render($page, $totalItems, $perPage, BASE_URL . '/posts');
echo PaginationHelper::simple($currentPage, $totalPages, BASE_URL . '/posts');
```

## Меню — `MenuRenderer`, `CustomMenuParser`

```php
<?php
MenuRenderer::renderByTemplate('footer');      // меню, привязанное к шаблону
MenuRenderer::render('Главное меню');          // меню по имени
MenuRenderer::renderById(1);                   // меню по ID
MenuRenderer::getAllActiveMenus();
MenuRenderer::getAvailableShortcodes();

// Глобальные функции из helpers/Shortcodes.php — то же самое
render_menu_by_name('Главное меню');
render_menu_by_id(1);
```

## Блоки — `BlockRenderer`, `FragmentHelper`

```php
<?php
BlockRenderer::render($blockData);             // блок любого типа → HTML
BlockRenderer::renderBlock($blockData);        // одиночный блок
BlockRenderer::renderPostBlocks($blocks);      // массив блоков поста → HTML

FragmentHelper::renderFragment('reviews');     // по системному имени
FragmentHelper::renderFragmentWithTemplate('partners', $templateHtml);
```

## Пользовательские поля — `CustomFields`

```php
<?php
get_custom_fields('post', $postId);                          // все поля
get_custom_field_value('post', $postId, 'price');            // значение
get_custom_field_display('post', $postId, 'cover');          // готовый HTML
has_custom_field('post', $postId, 'price');                  // есть ли значение
render_icon_field('post', $postId, 'icon', ['size' => 24]);  // иконка
```

## Работа с файлами — `FileUpload`, `FilesUpload`, `BlockImageHelper`

```php
<?php
// Один файл
$result = FileUpload::upload($_FILES['avatar'], UPLOADS_PATH . '/avatars/', ['image/jpeg', 'image/png'], 2048);
FileUpload::delete($path);
FileUpload::isZip($path);

// Много файлов
$files = FilesUpload::uploadMultiple($_FILES['gallery'], UPLOADS_PATH . '/gallery/', ['image/jpeg'], 5120);
$image = FilesUpload::uploadGalleryImages($_FILES['images'], 'gallery');
FilesUpload::deleteMultiple($paths);
FilesUpload::getImageDimensions($path);

// Картинки в настройках блоков
BlockImageHelper::handleUpload('logo_path', $blockSystemName, $currentValue);
BlockImageHelper::handleDelete('logo_path', $currentValue);
BlockImageHelper::handleRepeaterUploads('items', $blockSystemName, $currentValues);
BlockImageHelper::getImageUrl($imagePath);
```

## Достижения — `AchievementsHelper`

```php
<?php
AchievementsHelper::setDatabase($db);
AchievementsHelper::checkAndUnlockAchievements($userId);
AchievementsHelper::getUserAchievements($userId);
AchievementsHelper::getAchievementProgress($userId, $achievementId);
AchievementsHelper::updateUserStat($userId, 'comments_count', 1);
echo AchievementsHelper::renderAchievementBadge($achievement);
echo AchievementsHelper::renderAchievementList($userId);
```

## Версия и обновления — `VersionHelper`

```php
<?php
VersionHelper::getVersion();      // 1.0.0
VersionHelper::getBuild();        // 5
VersionHelper::getVersionName();  // Release Candidate 5
VersionHelper::getVersionDate();
$info = VersionHelper::checkUpdates();
```

## API моделей — `API`

```php
<?php
API::reviews()->getPublished(5);
API::model('reviews')->getPublished(5);
API::call('reviews', 'getPublished', [5]);
API::reviews_getPublished(5);
API::hasModel('reviews');
API::getAvailableModels();
API::getModelMethods('reviews');
```

## Логирование и отладка — `Logger`, `DebugLogger`

```php
<?php
Logger::info('Модуль загружен');
Logger::error('Не удалось сохранить', ['id' => $id]);
Logger::debug('Значения', ['data' => $data]);

DebugLogger::info('Пользователь открыл отчёт');
DebugLogger::warning('Долгий запрос', ['ms' => 1200]);
DebugLogger::error('Ошибка синхронизации', ['error' => $e->getMessage()]);
DebugLogger::exception($e);
```

`Logger` пишет в обычный лог сервера, `DebugLogger` дополнительно складывает записи в таблицу `debug_logs`, если включён режим отладки, — они видны в `/admin/debug`.

## Активность пользователей — `UserActivityManager`

```php
<?php
UserActivityManager::getInstance($db)->touch($userId);
UserActivityManager::getInstance()->isOnline($userId);
UserActivityManager::getInstance()->getLastActivityInfo($userId);
```

## Прочее

| Хелпер | Назначение |
|---|---|
| `RouteHelper::getAllFrontendRoutes()` / `getRoutesForController($name)` | Список маршрутов (для отладки и генерации карты) |
| `ConstantHelper::get($name, $default)` / `isDefined($name)` | Безопасная работа с константами |
| `SimpleFormRenderer` | Рендер простых форм (`render_simple_form`) |
| `QuickActionsHelper` | Быстрые действия в админке (`hasQuickActions`, `renderQuickActions`) |
| `AdminActions` | Кнопки действий в админке + глобальные функции `admin_action()`, `admin_action_group()` |
| `BackupHelper` | Бэкапы файлов шаблонов (`createBackup`, `cleanupOldBackups`, `getBackupStats`) |
| `Fieldset`, `Field`, `FieldFactory`, `FieldShortcodes` | Формы и поля (см. [Формы и поля](05-formy-i-polya.md)) |
| `assets.php` | Подключение CSS/JS (см. [Ассеты](10-assets-i-frontend.md)) |
| `navigation_helper.php` | Пункты меню админки (`admin_menu_item`, `is_admin_section_active`) |

---

> [!NOTE]
> Список не статичен: хелперы добавляются вместе с модулями. Актуальный перечень всегда можно получить командой `ls system/helpers/` и поиском по репозиторию.

---

[← Ассеты и фронтенд](10-assets-i-frontend.md) · [Оглавление](../README.md) · [Безопасность и отладка →](12-bezopasnost-i-otladka.md)