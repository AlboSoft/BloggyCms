# AGENTS.md — BloggyCMS

## Stack
- **PHP 8.0+**, MySQL 5.7+ (PDO with utf8mb4)
- **No package manager** — standalone application, no `composer.json`
- **No test suite** — no PHPUnit or other testing framework
- **No CI/CD** — no GitHub workflows or pre-commit hooks

## Architecture

### Entry Point
- `index.php` — main entry, handles routing, session init, auth middleware, autoloading

### Core (`system/core/`)
- `App.php` — bootstraps DB, router, middleware, hooks
- `Database.php` — PDO singleton
- `Controller.php` — base controller class
- `Router.php` — URL routing
- `Event.php` — event/hook system (listen/trigger pattern)
- `Action.php` — abstract command pattern for controller actions
- `ModelAPI.php` + `APIAware` trait — model interface

### Controllers (`system/controllers/{name}/`)
Each controller has:
- `{Name}Controller.php` — main controller extending `Controller`
- `actions/` — individual action classes extending `Action`
- `hooks/` — event listener files
- `Model.php` — data access model implementing `ModelAPI`
- `routes.php` — route definitions
- `manifest.php` — controller metadata

### Other Systems
- `system/fields/` — custom field type classes (`BaseField`)
- `system/post_blocks/` — content block types (`BasePostBlock`)
- `system/html_blocks/` — HTML block types
- `system/helpers/` — utility classes

### Templates
- `templates/default/` — default theme structure

## Setup
1. Copy `install/install.sql` to MySQL
2. Navigate to `/install/` — web-based wizard creates `system/config/config.php` and `system/config/database.php`
3. Required dirs created at runtime: `cache/`, `uploads/`, `system/logs/`

## Key Patterns

### Controller Action
```php
// system/controllers/posts/actions/Show.php
class Show extends Action {
    public function execute() {
        // $this->db, $this->params available
    }
}
```

### Model
```php
// implements ModelAPI, uses APIAware trait
class PostModel implements ModelAPI {
    use APIAware;
    protected $allowedAPIMethods = ['getAll', 'getById', ...];
}
```

### Event Hooks
```php
// In hooks/ file:
Event::listen('post.created', function($postId) {
    // handle event
}, $priority = 10);
```

## Database
- Table prefix defined via `DB_PREFIX` in config
- Schema in `install/install.sql` (~960 lines)
- Uses `{#}` placeholder for prefix in SQL files

## Important Paths
| Path | Purpose |
|------|---------|
| `/system/core/` | Framework classes |
| `/system/controllers/` | Feature modules |
| `/system/config/` | Generated config (not in repo) |
| `/install/` | DB schema + setup wizard |
| `/templates/` | Theme files |

## Notes
- All controller classes have `checkAdminAccess()` gating admin actions via `$_SESSION['is_admin']`
- Autoloader in `index.php` uses PSR-4-like path conversion with fallback search paths
- No lint/typecheck/test commands — code review is manual
