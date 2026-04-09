# План рефакторинга, оптимизации и улучшения BloggyCMS

> Документ составлен на основе глубокого анализа кодовой базы.
> Дата: 09.04.2026

---

## Критические проблемы безопасности (Приоритет: КРИТИЧЕСКИЙ)

### 1.1 XSS уязвимость в HtmlField
**Файл:** `system/fields/HtmlField.php:53-54`

```php
public function renderDisplay($value, $entityType, $entityId): string {
    return $value; // УЯЗВИМОСТЬ: возврат несанитизированного HTML
}
```

**Проблема:** Пользовательский HTML выводится без санитизации.
**Решение:** Использовать HTML Purifier или аналог для очистки HTML перед выводом. Проверить все места использования HtmlField.

**Список файлов для проверки:**
- `system/fields/HtmlField.php` — renderDisplay(), renderList()
- `system/core/BasePostBlock.php` — renderWithTemplate()

---

### 1.2 XSS в BasePostBlock шаблонизаторе
**Файл:** `system/core/BasePostBlock.php:525-526`

```php
if (strip_tags($value) !== $value) {
    $result = str_replace($placeholder, $value, $result); // ОПАСНО
}
```

**Проблема:** HTML-контент вставляется напрямую без экранирования.
**Решение:** Для пользовательского контента использовать whitelist допустимых тегов (как в TextBlock.php:318), для шаблонного — полная санитизация.

---

### 1.3 Plaintext пароли в CategoryModel
**Файл:** `system/controllers/categories/Model.php:247`

```php
return $category['password'] === $password; // plaintext сравнение
```

**Проблема:** Пароли категорий хранятся и сравниваются в plaintext.
**Решение:** Использовать `password_hash()`/`password_verify()` как в UserModel. Создать миграцию для существующих паролей.

**Аналогичная проблема:**
- `system/controllers/posts/Model.php:551` — `checkPassword()` использует plaintext сравнение

---

### 1.4 CSRF уязвимости в админке
**Файл:** `system/controllers/admin/AdminController.php`

| Метод | Строка | Проблема |
|-------|--------|----------|
| `saveTemplateFileAction()` | 249 | Нет проверки CSRF токена |
| `uploadFileAction()` | 531 | Нет проверки CSRF токена |
| `deleteFileAction()` | ~400+ | Нет проверки CSRF токена |
| `createFolderAction()` | ~400+ | Нет проверки CSRF токена |

**Проблема:** Злоумышленник может модифицировать шаблоны и загружать файлы через CSRF.
**Решение:** Добавить проверку `CsrfToken::verify()` во все экшены, изменяющие данные.

**Список защищенных контроллеров (для справки):**
- `profile/actions/` — используют CsrfToken
- `auth/actions/` — используют CsrfToken
- `forms/actions/` — используют CsrfToken

---

### 1.5 Использование extract() в Controller
**Файл:** `system/core/Controller.php:162`

```php
extract($data); // Опасно: перезаписывает переменные
```

**Проблема:** Злоумышленник может перезаписать внутренние переменные через данные.
**Решение:** Заменить на явную передачу через компактные массивы или использовать view objects.

---

### 1.6 Path traversal в addon upload
**Файл:** `system/controllers/admin/AdminUpload.php:371`

```php
private function copyFilesRecursive($source, $destination, $backupDir = null, $createBackup = false) {
    copy($sourcePath, $destPath); // Potential path traversal
}
```

**Проблема:** При рекурсивном копировании проверка `..` может не сработать.
**Решение:** Добавить валидацию после каждого copy + использовать realpath().

---

## Высокий приоритет рефакторинга (Приоритет: ВЫСОКИЙ)

### 2.1 N+1 запрос в getAllPaginated
**Файл:** `system/controllers/posts/Model.php:412-417`

```php
$allPosts = $this->db->fetchAll($sql);

$visiblePosts = [];
foreach ($allPosts as $post) {
    if ($this->checkPostVisibility($post['id'], $userGroups)) { // ЗАПРОС В ЦИКЛЕ
        $visiblePosts[] = $post;
    }
}
```

**Проблема:** Загружаются ВСЕ посты, затем фильтруются в PHP по одному. Для 1000 постов = 1000 запросов.
**Решение:** Перенести логику видимости в SQL WHERE условие через `buildVisibilityCondition()`:

```php
$visibility = $this->buildVisibilityCondition($userGroups);
$sql .= $visibility['where'];
$params = array_merge($params, $visibility['params']);
$allPosts = $this->db->fetchAll($sql, $params);
```

---

### 2.2 Запросы на КАЖДЫЙ рендер страницы
**Файл:** `system/core/Controller.php:153-156`

```php
$categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name");
$pages = $this->db->fetchAll("SELECT * FROM pages WHERE status = 'published' ORDER BY title");
$tags = $this->db->fetchAll("SELECT * FROM tags ORDER BY name");
```

**Проблема:** Эти данные запрашиваются при рендере ЛЮБОЙ публичной страницы. Каждая страница = 3 лишних запроса.

**Решение:**
1. Кешировать результаты в APCu/memcached/filecache
2. Или передавать через layout variables
3. Или загружать через layout hooks только когда нужно
4. Или вынести в FragmentHelper с lazy loading

---

### 2.3 Типографическая ошибка в SQL
**Файл:** `system/controllers/posts/Model.php:586`

```php
return $result ? (int)$result['likez_count'] : 0; // Должно быть 'likes_count'
```

**Проблема:** Опечатка в имени колонки — возвращает 0 всегда.
**Решение:** Исправить на `likes_count`.

---

### 2.4 Дублирование метода transliterate()
**Файлы:**
- `system/controllers/posts/Model.php:340-357`
- `system/controllers/categories/Model.php:307-327`

**Проблема:** Идентичный метод скопирован в два места.
**Решение:** Создать трейт `TransliterateTrait`:

```php
trait TransliterateTrait {
    protected function transliterate(string $string): string {
        $converter = array(...);
        return strtr($string, $converter);
    }
}
```

**Другие дублирования для вынесения в трейты:**
- `checkAdminAccess()` — PostController:57, CategoryController:61
- `isAjaxRequest()` — PostController:65, CategoryController:69
- Похожие методы валидации в FieldManager

---

### 2.5 Пустые блоки catch
**Файлы:**
- `App.php:65-68` — загрузка хуков
- `App.php:100-107` — инициализация field shortcodes
- `App.php:140` — загрузка модулей

```php
try {
    require_once $hookFile;
} catch (Exception $e) {} // ТИХОЕ подавление
```

**Проблема:** Ошибки игнорируются, отладка невозможна.
**Решение:** Логировать в `system/logs/`:

```php
} catch (Exception $e) {
    error_log("Hook error {$hookFile}: " . $e->getMessage());
}
```

---

### 2.6 Отсутствие type hints
**Проблема:** Большинство методов не имеют деклараций типов.

**Решение:** Добавить strict types и type hints:

```php
declare(strict_types=1);

public function getById(int $id): ?array
public function getAll(?int $limit = null): array
protected function buildVisibilityCondition(array $userGroups = []): array
```

---

### 2.7 Loose comparison (== вместо ===)
**Примеры:**
- `system/core/Controller.php` — множественные сравнения
- `system/fields/BaseField.php` — `==` вместо `===`

**Решение:** Провести аудит и заменить на строгие сравнения.

---

## Средний приоритет (Приоритет: СРЕДНИЙ)

### 3.1 Рефакторинг Event системы
**Файл:** `system/core/Event.php`

**Проблема:** Статическое состояние (`static $listeners`) делает невозможным тестирование и параллельное выполнение.

```php
private static $listeners = [];
private static $pendingListeners = [];
private static $initialized = false;
```

**Рекомендация:**
1. Переход на экземплярную систему с DI контейнером
2. Или добавить метод `reset()` для очистки состояния в тестах
3. Добавить интерфейс `EventDispatcherInterface`

---

### 3.2 Паттерн дублирования в контроллерах
**Дублируется в:** PostController, CategoryController, UsersController

```php
// checkAdminAccess()
private function checkAdminAccess() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

// isAjaxRequest()
protected function isAjaxRequest(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
```

**Решение:** Вынести в базовый `Controller` класс или создать трейт `AdminAccessTrait`.

---

### 3.3 Регулярка на КАЖДЫЙ SQL запрос
**Файл:** `system/core/Database.php:77-93`

```php
$sql = preg_replace_callback(
    '/(FROM|JOIN|INTO|UPDATE|TABLE|REFERENCES|DELETE\s+FROM)\s+`?([a-zA-Z][a-zA-Z0-9_]*)`?/i',
    function($matches) use ($skipTables) { ... }
);
```

**Проблема:** Regex выполняется для каждого запроса, даже если префикс уже применен.
**Решение:**
1. Кешировать преобразованные запросы
2. Использовать prepare/execute с префиксом в placeholder'ах
3. Добавить флаг `skipPrefix` для подзапросов

---

### 3.4 Отсутствие интерфейсов
**Проблема:** Нет абстракций, только конкретные классы.

**Рекомендация:** Создать интерфейсы:

```php
interface ModelInterface {
    public function getAll();
    public function getById(int $id);
}

interface FieldInterface {
    public function renderInput($value, string $entityType, int $entityId): string;
    public function renderDisplay($value, string $entityType, int $entityId): string;
}

interface PostBlockInterface {
    public function render(array $content, array $settings): string;
}
```

---

### 3.5 Regex в Shortcodes
**Файл:** `system/helpers/Shortcodes.php:67`

**Проблема:** Сложные regex паттерны выполняются дважды (paired + simple).
**Решение:** Оптимизировать в один проход или кешировать результаты.

---

### 3.6 Static state в AssetManager
**Файл:** `system/core/AssetManager.php`

```php
private static $instance = null;
private $assets = [...];
```

**Проблема:** Singleton + статическое состояние.
**Решение:** Перейти на DI или добавить `reset()` для тестирования.

---

## Низкий приоритет (Приоритет: НИЗКИЙ)

### 4.1 Кеширование
**Проблема:** Нет механизма кеширования данных.

**Рекомендуется кешировать:**
- Список категорий (categories)
- Список тегов (tags)
- Опубликованные страницы (pages)
- Меню
- Настройки (settings)
- Роутинг (routes)

**Решение:** Внедрить APCu или file-based кеш:

```php
// Пример
$cacheKey = 'categories_list';
if ($cached = Cache::get($cacheKey)) {
    return $cached;
}
$categories = $this->db->fetchAll("SELECT * FROM categories...");
Cache::set($cacheKey, $categories, 3600); // TTL 1 hour
return $categories;
```

---

### 4.2 Asset Manager оптимизация
**Файл:** `system/core/AssetManager.php`

**Текущие возможности:**
- Поддержка CSS/JS для frontend и admin
- Deduplication через registry
- Path normalization

**Недостатки:**
- Нет минификации CSS/JS
- Нет конкатенации файлов
- Нет HTTP/2 push
- Нет CDN support

---

### 4.3 Тестирование
**Проблема:** Нет PHPUnit, нет тестов.

**Рекомендация:**
1. Установить Composer + PHPUnit
2. Написать базовые тесты для:
   - Database query methods
   - Model operations
   - Event system
   - Router
   - Event::trigger() / Event::listen()

---

### 4.4 CSRF токены — непоследовательное использование
**Файлы с CsrfToken (для справки):**
- `system/helpers/CsrfToken.php` — полная реализация
- `profile/actions/` — используют
- `auth/actions/` — используют
- `forms/actions/` — используют

**Файлы БЕЗ CsrfToken:**
- `AdminController.php` — все методы
- `addons/actions/` — проверить
- `settings/actions/` — проверить

---

### 4.5 Дублирование renderCss() в layout.php
**Файл:** `templates/default/front/layout.php:51,73`

```php
<?php echo render_front_css(); ?>  // Первый вызов
...
<?php echo render_front_css(); ?>  // Второй вызов (дубликат)
```

**Проблема:** CSS массивы очищаются после первого рендера, второй вызов ничего не возвращает.
**Решение:** Удалить мертвый код.

---

### 4.6 Late registration race в FragmentHelper
**Файл:** `system/helpers/FragmentHelper.php`

**Проблема:** Если классы загружаются в неожиданном порядке, shortcodes могут зарегистрироваться поздно.
**Решение:** Гарантировать порядок загрузки или использовать event-driven registration.

---

## Структурные улучшения

### 5.1 Внедрение зависимостей (DI)
**Текущая проблема:**

```php
// В конструкторе PostController
public function __construct($db) {
    $this->postModel = new PostModel($db);
    $this->categoryModel = new CategoryModel($db);
    // ... 7 моделей
}
```

**Рекомендация:**

```php
// Через конструктор
public function __construct(Database $db, PostModel $postModel, ...) {
    $this->db = $db;
    $this->postModel = $postModel;
}

// Или через Service Locator
$postModel = App::get('PostModel');
```

---

### 5.2 Архитектура Field системы
**Сейчас:** `BaseField` — монолитный abstract class

```
BaseField (abstract)
├── StringField
├── TextField  
├── HtmlField ← CRITICAL XSS
├── NumberField
├── DateField
├── ImageField
├── SelectField
├── MultiSelectField
├── LinkField
├── ColorField
└── FlagField
```

**Рекомендация:**

```
FieldInterface
    ↓
AbstractField (base logic)
    ↓
StringField, HtmlField, ImageField etc.
```

---

### 5.3 Архитектура PostBlock системы
**Сейчас:** `BasePostBlock` — монолитный abstract class

**Рекомендация:** Аналогичная реструктуризация с интерфейсом.

---

### 5.4 Модель Hooks файлов
**Текущая проблема:**

```php
// App.php:64-68
require_once $hookFile; // Выполняется в глобальной области видимости
```

**Рекомендация:** Обернуть в namespace или использовать класс:

```php
// hooks/post_created.php
Event::listen('post.created', function($postId) {
    // Логика
}, 10);
```

Уже правильно, но нет изоляции. Можно добавить:

```php
class PostHooks {
    public static function register() {
        Event::listen('post.created', [self::class, 'onPostCreated'], 10);
    }
    public static function onPostCreated($postId) { ... }
}
```

---

## План выполнения (рекомендуемый порядок)

### Фаза 1: Безопасность (1-2 дня)
| # | Задача | Файлы |
|---|--------|-------|
| 1.1 | Исправить XSS в HtmlField | `system/fields/HtmlField.php` |
| 1.2 | Исправить XSS в BasePostBlock | `system/core/BasePostBlock.php` |
| 1.3 | Добавить CSRF во все admin actions | `AdminController.php` |
| 1.4 | Заменить plaintext password_hash в CategoryModel | `categories/Model.php` |
| 1.5 | Убрать extract() из Controller | `system/core/Controller.php` |
| 1.6 | Исправить path traversal в addon upload | `AdminUpload.php` |

---

### Фаза 2: Критические баги (1 день)
| # | Задача | Файлы |
|---|--------|-------|
| 2.1 | Исправить `likez_count` → `likes_count` | `posts/Model.php:586` |
| 2.2 | Исправить N+1 в getAllPaginated | `posts/Model.php:412-417` |
| 2.3 | Добавить error logging в пустые catch | `App.php` |
| 2.4 | Проверить plaintext checkPassword в posts | `posts/Model.php:551` |

---

### Фаза 3: Рефакторинг (3-5 дней)
| # | Задача | Файлы |
|---|--------|-------|
| 3.1 | Вынести transliterate() в трейт | `posts/Model.php`, `categories/Model.php` |
| 3.2 | Создать AdminAccessTrait | `system/core/` |
| 3.3 | Добавить type hints | Все core файлы |
| 3.4 | Убрать дублирование в контроллерах | `PostController`, `CategoryController` |
| 3.5 | Рефакторинг Event системы | `system/core/Event.php` |

---

### Фаза 4: Оптимизация (2-3 дня)
| # | Задача | Файлы |
|---|--------|-------|
| 4.1 | Кеширование категорий/тегов/страниц | `Controller.php` |
| 4.2 | Оптимизация regex в Database | `system/core/Database.php` |
| 4.3 | Внедрение DI контейнера | `system/core/` |
| 4.4 | Убрать запросы из Controller::render() | `system/core/Controller.php` |

---

### Фаза 5: Качество (2-3 дня)
| # | Задача | Файлы |
|---|--------|-------|
| 5.1 | Установить PHPUnit | `composer.json` (создать) |
| 5.2 | Написать базовые тесты | `tests/` (создать) |
| 5.3 | Добавить PHPStan level 1-5 | `phpstan.neon` (создать) |

---

## Чеклист перед релизом

- [ ] Все XSS уязвимости закрыты
- [ ] CSRF токены на всех формах
- [ ] Пароли хешированы (password_verify)
- [ ] Нет plaintext паролей в базе
- [ ] extract() заменен на безопасную альтернативу
- [ ] N+1 запросы исправлены
- [ ] Все catch блоки логируют ошибки
- [ ] Type hints добавлены
- [ ] Тесты проходят
- [ ] PHPStan level 1 проходит

---

## Метрики для оценки качества

| Метрика | Текущее значение | Целевое значение |
|---------|------------------|------------------|
| PHPStan level | 0 (не используется) | 5 |
| Code coverage | 0% | >50% |
| Cyclomatic complexity (средняя) | Неизвестно | <10 |
| N+1 запросов | >5 | 0 |
| XSS уязвимостей | 2 | 0 |
| CSRF уязвимостей | >5 | 0 |
