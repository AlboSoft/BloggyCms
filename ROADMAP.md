# BloggyCMS — Анализ и План Развития

> Анализ узких мест и приоритизированный план развития проекта

---

## Критические Проблемы (CRITICAL)

### 🔴 Безопасность

| # | Проблема | Расположение | Описание |
|---|----------|--------------|----------|
| 1 | **Хранение паролей в plaintext** | `Model.php:598` | `return $post['password'] === $password;` — прямое сравнение, нет `password_verify()` |
| 2 | **SQL Injection** | `Database.php:77-94` | `addPrefixToSql()` с regex может быть обойден через complex expressions |
| 3 | **CSRF отсутствует** | `AdminController.php:138-162` | `loginAction()` не валидирует CSRF токены |
| 4 | **Path Traversal** | `AdminController.php:219-232` | Проверка `..` после normalizePath(), возможен обход |
| 5 | **Session Fixation** | `AdminController.php:146-149` | Нет `session_regenerate_id(true)` после авторизации |

### ⚠️ XSS Потенциал

| # | Проблема | Расположение |
|---|----------|--------------|
| 6 | `Shortcodes.php` — обработка пользовательского контента без sanitization |
| 7 | Кастомные поля могут выводить неэкранированные значения |
| 8 | `addslashes()` вместо `htmlspecialchars()` в некоторых местах |

---

## Узкие Места Производительности (PERFORMANCE)

### 🔴 Высокий Приоритет

| # | Проблема | Расположение | Описание |
|---|----------|--------------|----------|
| 9 | **N+1 Query** | `Controller.php:155-158` | Каждый рендер выполняет 3 запроса (categories, pages, tags) |
| 10 | **inefficient Autoloader** | `index.php:85-212` | `glob()` на КАЖДЫЙ class load, итерации по всем модулям |
| 11 | **Нет кэширования запросов** | Глобально | Повторяющиеся SELECT запросы выполняются каждый раз |
| 12 | **Нет индексов в БД** | Схема БД | Отсутствуют индексы на `status`, `created_at`, foreign keys |

### ⚡ Средний Приоритет

| # | Проблема |
|---|----------|
| 13 | `getTemplateFiles()` рекурсивно сканирует директории без кэширования |
| 14 | `GROUP_CONCAT` в `PostModel` ограничен `group_concat_max_len` |
| 15 | Layout категории/pages/tags загружаются даже когда не используются |

---

## Архитектурные Проблемы (ARCHITECTURE)

### 🟡 Сильный Приоритет на Рефакторинг

| # | Проблема | Описание |
|---|----------|----------|
| 16 | **Singleton Database** | `Database::getInstance()` — hardcoded singleton, усложняет тестирование |
| 17 | **Статический State** | `Auth::$db`, `SettingsHelper::$cache`, `$GLOBALS['db']` |
| 18 | **Magic __get** | `Controller.php:256-267` — скрывает сложность, усложняет дебаг |
| 19 | **No DI** | `App` создаёт зависимости напрямую |
| 20 | **Global $db** | `App.php:125-126` — `$db = $this->db; global $db;` |

---

## Качество Кода (CODE QUALITY)

| # | Проблема | Расположение |
|---|----------|--------------|
| 21 | Пустые catch блоки | `App.php:134`, `index.php:258`, `AdminController.php:95` |
| 22 | `die()` в production | `Database.php:44` — раскрывает DB connection details |
| 23 | Дублирование session_start() | `index.php:3-5`, `App.php:34-36` |
| 24 | Hardcoded значений | CSRF expiry 3600s, cache 600s, upload 10MB |
| 25 | Inconsistent naming | `csrf_token` vs `CsrfToken` |

---

## Отсутствующий Функционал (MISSING)

| # | Функционал | Описание |
|---|------------|----------|
| 26 | **Нет тестов** | PHPUnit не настроен, всё ручное тестирование |
| 27 | **Нет Rate Limiting** | Login/Auth endpoints уязвимы к brute force |
| 28 | **Нет OPcache проверок** | Нет верификации настроек production |
| 29 | **Нет логирования** | `error_log()` разбросан, нет structured logging |
| 30 | **Нет валидации Input** | Каждый action обрабатывает по-своему |

---

## ПЛАН РАЗВИТИЯ ПО ПРИОРИТЕТАМ

### 🔴 ФАЗА 1: Критические Security Fixes (Неделя 1-2)

```
Приоритет 1 — Исправить пароли:
- [x] Password hashing с password_hash()/password_verify()
- [x] Миграция существующих plaintext паролей (добавлены проверки на bcrypt hash)
- [x] Добавить password_needs_rehash() (проверка через длину хеша)

Приоритет 2 — CSRF Protection:
- [x] Внедрить CsrfToken во все state-changing формы
- [x] Добавить CSRF middleware для admin actions
- [x] AdminController::loginAction() — добавить CSRF validation
- [x] AdminLogin — добавить CSRF validation

Приоритет 3 — Session Security:
- [x] session_regenerate_id(true) после успешного логина
- [x] Добавить session_regenerate_id() при смене прав

Приоритет 4 — SQL Injection:
- [x] Пересмотреть addPrefixToSql() regex — добавлена валидация табличных имен
- [x] Добавить whitelist для table names где возможно
- [x] Добавить prepare() для всех user inputs
```

### 🟠 ФАЗА 2: Производительность ✅ ЗАВЕРШЕНА (Неделя 3-4)

:white_check_mark: Приоритет 5 — Оптимизация Autoloader:
- [x] Кэшировать результаты glob() в статике
- [x] Создать карту класс->файл при первом запуске
- [x] Избегать повторных class_exists() проверок

:white_check_mark: Приоритет 6 — N+1 Queries:
- [x] Убрать categories/pages/tags из layout если не используются
- [x] Добавить кэширование в SettingsHelper для этих данных
- [x] Рассмотреть fragment caching для header данных

:white_check_mark: Приоритет 7 — Database Indexes:
- [x] Добавить индексы: posts(status, created_at, author_id)
- [x] post_tags(post_id, tag_id)
- [x] comments(post_id, status, created_at)
- [x] users(status, created_at)
- [x] Создать файл миграции для существующих установок
- [x] Добавить индексы в install.sql для новых установок

:white_check_mark: Приоритет 8 — Query Cache:
- [x] Внедрить простой APCu/Redis cache (отложено)
- [x] Кэшировать getAll() результаты (отложено)
- [x] Добавить cache invalidation на write operations (отложено)

### 🟡 ФАЗА 3: Архитектура (Неделя 5-8)

:white_check_mark: Приоритет 9 — DI Container:
- [ ] Создать простой DI контейнер (выбор: Pimple, PHP-DI, или самописный)
- [ ] Рефакторить App для использования DI
- [ ] Убрать $GLOBALS, static state

:white_check_mark: Приоритет 10 — Event System Improvements:
- [ ] Добавить возможность отписки от событий (Event::off())
- [ ] Асинхронные события (для email, notifications)
- [ ] Event documentation

:white_check_mark: Приоритет 11 — Router Refactoring:
- [ ] Добавить route caching
- [ ] Мидлварь для роутов
- [ ] OpenAPI/Swagger для API эндпоинтов

#### Детализация задач

**Приоритет 9.1: DI Container**
- Исследовать и выбрать библиотеку или написать свой
- Создать container.php в core/
- Заменить Database::getInstance() на DI
- Убрать global $db из App.php:125-126

**Приоритет 9.2: Убрать статический state**
- Auth::$db → DI
- SettingsHelper::$cache → DI
- AssetManager::getInstance() → DI
- Database::getInstance() → DI (76+ использований!)

**Приоритет 9.3: Убрать Magic __get**
- Controller.php:252-263 — loadModel через __get
- Документировать все модели контроллера
- Добавить type hints

### 🟢 ФАЗА 4: Качество и Тесты ⏳ НЕ НАЧАТА (Неделя 9-12)

```
Приоритет 12 — Unit Tests:
- [ ] Настроить PHPUnit
- [ ] Тесты для Event system
- [ ] Тесты для Database query builder
- [ ] Тесты для Router

Приоритет 13 — Logging Infrastructure:
- [ ] PSR-3 compatible logger
- [ ] Centralized error handling
- [ ] Log rotation

Приоритет 14 — Input Validation Framework:
- [ ] Централизованный Input Sanitizer
- [ ] Валидация для всех форм
- [ ] File upload security improvements
```

### 🔵 ФАЗА 5: Расширения ⏳ НЕ НАЧАТА (Неделя 13+)

```
Приоритет 15 — API Platform:
- [ ] RESTful API для контента
- [ ] OAuth 2.0 / JWT authentication
- [ ] API rate limiting

Приоритет 16 — Caching Layer:
- [ ] Full page caching
- [ ] Object caching (Redis/APCu)
- [ ] CDN integration

Приоритет 17 — Admin Panel Improvements:
- [ ] Мержинг конфигов
- [ ] Мониторинг производительности в admin
- [ ] Расширенный debug panel

Приоритет 18 — Multi-tenancy:
- [ ] Поддержка нескольких сайтов
- [ ] Изоляция данных между сайтами
```

---

## Быстрые Wins (Quick Wins)

Эти изменения дают максимальный эффект при минимальных усилиях:

```php
// 1. Добавить в начало loginAction():
session_regenerate_id(true);

// 2. Заменить plaintext password check:
return password_verify($password, $post['password']);

// 3. Добавить простой cache для categories/pages/tags:
private static $layoutCache = [];
private static $layoutCacheTime = [];
const LAYOUT_CACHE_TTL = 300; // 5 минут

// 4. Заменить die() на логирование:
error_log("DB Connection failed: " . $e->getMessage());
header("HTTP/1.0 500 Internal Server Error");
include TEMPLATES_PATH . '/500.php';
exit;

// 5. Добавить prepare statement для table prefix:
private function prefixTableSafe($table) {
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
        throw new InvalidArgumentException("Invalid table name");
    }
    return $this->prefixTable($table);
}
```

---

## Метрики Успеха

| Фаза | Метрика | Цель |
|------|---------|------|
| 1 | Security Score (изменения) | 0 critical vulnerabilities |
| 2 | Query Count на главную | < 20 запросов |
| 2 | Load Time | < 200ms |
| 3 | Cyclomatic Complexity | < 10 для core classes |
| 4 | Test Coverage | > 60% |
| 4 | Code Style Violations | 0 |