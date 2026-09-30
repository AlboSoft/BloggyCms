<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset=".github/assets/logo-dark.png">
    <source media="(prefers-color-scheme: light)" srcset=".github/assets/logo-light.png">
    <img src=".github/assets/logo-light.png" alt="BloggyCMS" width="112">
  </picture>
</p>

<h1 align="center">BloggyCMS</h1>

<p align="center">
  <b>A modular, open-source CMS for blogs and content sites.</b><br>
  Blocks instead of WYSIWYG · Hooks instead of core hacks · PHP 8 + MySQL, nothing else.
</p>

<p align="center">
  <a href="https://github.com/albosoft/BloggyCms/releases"><img src="https://img.shields.io/badge/version-1.0.0--RC-FF6B35?style=flat-square" alt="Version"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.0+"></a>
  <a href="https://www.mysql.com"><img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 5.7+"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-3DA639?style=flat-square" alt="MIT License"></a>
  <a href="#requirements"><img src="https://img.shields.io/badge/dependencies-none-2ea44f?style=flat-square" alt="No dependencies"></a>
  <a href="system/languages"><img src="https://img.shields.io/badge/UI-RU%20%7C%20EN-8957e5?style=flat-square" alt="Russian and English UI"></a>
  <a href="https://github.com/albosoft/BloggyCms/stargazers"><img src="https://img.shields.io/github/stars/albosoft/BloggyCms?style=flat-square&logo=github&color=gold" alt="Stars"></a>
  <a href="https://github.com/albosoft/BloggyCms/discussions"><img src="https://img.shields.io/badge/discussions-join-5865F2?style=flat-square&logo=github&logoColor=white" alt="Discussions"></a>
</p>

<p align="center">
  <a href="https://bloggy.su">Website</a> ·
  <a href="https://github.com/albosoft/BloggyCms/wiki">Documentation</a> ·
  <a href="https://github.com/albosoft/BloggyCms/releases">Releases</a> ·
  <a href="https://github.com/albosoft/BloggyCms/discussions">Discussions</a> ·
  <a href="https://github.com/AlboSoft/BloggyCms/issues/new/choose">Report a bug</a>
</p>

<p align="center">
  <b>English</b> · <a href="README.ru.md">Русский</a>
</p>

---

> [!NOTE]
> BloggyCMS is in its **1.0.0 Release Candidate** series (latest published build: **v1.0.0-rc.9**). The system is stable and production-ready, but small API details may still change before the 1.0 stable release.

## Table of contents

- [Why BloggyCMS](#why-bloggycms)
- [Feature tour](#feature-tour)
- [Requirements](#requirements)
- [Installation](#installation)
- [Project structure](#project-structure)
- [Architecture](#architecture)
- [Extending BloggyCMS](#extending-bloggycms)
- [FAQ](#faq)
- [Contributing](#contributing)
- [Security](#security)
- [Support the project](#support-the-project)
- [License](#license)

---

## Why BloggyCMS

| | |
|---|---|
| ⚡ **Zero build step** | No Composer, no npm, no CLI, no migration tooling. Upload the files, open your domain, finish the 4-step wizard. |
| 🧩 **Everything is a module** | 26 built-in controllers, each one self-contained: `manifest.php`, `routes.php`, `Model.php`, `actions/`, `permissions.php`, `hooks/`. |
| 🧱 **Blocks, not WYSIWYG** | 16 post blocks and 12 HTML blocks out of the box. Your content stays structured markup instead of a soup of inline styles. |
| 🪝 **Event-driven core** | `Event::listen()`, `Event::trigger()`, `Event::filter()` with priorities. Extend anything without editing a single core file. |
| 🔐 **Security in the box** | Permission-based groups, CSRF tokens, admin middleware, captcha (math / text / image / honeypot), login-attempt throttling, validated uploads. |
| 🎨 **Themes & per-block templates** | Override any template file, or a single block's markup, without forking the CMS. |
| 🌍 **Bilingual out of the box** | Engine UI ships in Russian and English; content and menus are language-agnostic. |
| 📦 **Add-on manager** | Upload a ZIP package, let the analyzer validate it, install or remove it from the admin panel. |

## Feature tour

### Content

| Feature | What you get |
|---|---|
| **Posts & pages** | Categories, tags, archives, drafts, featured images, galleries, likes and bookmarks. |
| **Privacy controls** | Password-protected posts and an age gate for adult content (`post/check-age`, `post/check-password`). |
| **Post blocks** | 16 building blocks: text, headings, images, image-with-text, galleries, video, audio, quotes, lists, code, alerts, buttons, spoilers, custom HTML, post embeds and reusable content blocks. |
| **HTML blocks** | 12 layout blocks that work both as page sections and as standalone "mini-plugins": header, footer, hero, latest posts, services, categories, tags, author card, page tree, breadcrumbs, cookie consent, scroll-to-top. |
| **Fragments** | Reusable content entities you can render anywhere from a template. |
| **Custom fields** | 12 field types — `string`, `text`, `number`, `date`, `color`, `select`, `multiselect`, `flag`, `image`, `link`, `icon_with_style`, `html` — plus repeaters, block images and alerts. Attach fields to posts, pages, users, categories and more. |
| **Comments** | Moderation, edit/delete permissions, captcha support, per-controller settings. |
| **Menus** | Visual menu builder with drag-and-drop ordering and custom menu parsing. |
| **Notifications & achievements** | In-admin notifications plus rule-based achievements (registration days, comments, likes, bookmarks, logins). |

### Architecture

The core is intentionally small and readable — 24 core classes, 40 helpers, no framework behind the scenes.

| Component | Role |
|---|---|
| `App` / `Router` / `RouteManager` | Bootstraps the request, loads controller hooks, dispatches routes declared in each module. |
| `Event` | Publish/subscribe layer: `listen()`, `trigger()`, `filter()`, `unlisten()`, priorities, pending listeners before init. |
| `Controller` / `ControllerManager` | Base controller plus auto-discovery of every module, its manifest, settings and permissions. |
| `Middleware` / `AdminAuthMiddleware` | Route middleware chain, e.g. admin authentication and access checks. |
| `Database` / `DatabaseRegistry` / `Model` | Thin, explicit data layer — no ORM magic, prepared statements everywhere. |
| `ModelAPI` / `APIAware` | Expose model methods as a callable API surface for modules and templates. |
| `PermissionManager` | Loads `permissions.php` from every controller and checks per-user rights. |
| `PostBlockManager` / `HtmlBlockTypeManager` | Discovery, asset loading and rendering for post blocks and HTML blocks. |
| `FieldManager` / `FieldFactory` / `BaseField` | Custom field registry, shortcode registration and value processing. |
| `AssetManager` | Collects CSS/JS (including per-block assets) and outputs them in the right order. |
| `AdminForm` / `Fieldset` | Declarative admin forms: fieldsets, validation, CSRF, request handling. |
| `Shortcodes` | `{name}` / `{name attr="value"}…{/name}` syntax for reusable content inserts. |
| `DebugHandler` / `DebugLogger` | Collects notices and errors and shows them in the admin debugger. |

### SEO & performance

- `sitemap.xml` and `rss.xml` (plus per-category and per-tag feeds), regenerated automatically on content changes through module hooks.
- A dedicated SEO controller with settings, hooks and a content-change pipeline.
- Asset manager with per-block CSS/JS aggregation, breadcrumbs manager and pagination helpers.
- Debugger that catches notices and warnings on both frontend and admin, so nothing slips into production logs.

### Admin experience

- Dashboard with live stats (exportable as HTML) and quick actions.
- Site search plus an admin-side search history.
- Built-in template file editor with automatic backups of every file it touches.
- Controller manager: browse modules, edit their settings, inspect their manifests.
- One-click update checks, and one-click removal of the `install/` directory after you go live.

## Requirements

| Component | Minimum |
|---|---|
| **PHP** | 8.0 or newer (8.3+ recommended) |
| **PHP extensions** | `pdo_mysql`, `mysqli`, `mbstring`, `json`, `fileinfo`, `session`, `openssl` |
| **Database** | MySQL 5.7+ / MariaDB 10.2+ with `utf8mb4` |
| **Web server** | Apache with `mod_rewrite` (or Nginx — see the config below) |
| **Writable paths** | `uploads/`, `system/config/`, `templates/` |
| **Browser** | Any modern browser (no IE-era polyfills needed) |

> [!TIP]
> The installer checks all of the above for you and tells you exactly what is missing before writing a single line of configuration.

## Installation

### 1. Get the code

```bash
git clone https://github.com/AlboSoft/BloggyCms.git bloggycms
```

…or download the archive from the [releases page](https://github.com/albosoft/BloggyCms/releases) and upload it to your web root.

### 2. Create a database

```sql
CREATE DATABASE bloggycms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bloggy'@'localhost' IDENTIFIED BY 'strong-password';
GRANT ALL PRIVILEGES ON bloggycms.* TO 'bloggy'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Set permissions

```bash
chmod -R 755 uploads system/config templates
```

### 4. Run the wizard

Open `https://your-domain.example/install/` — the installer is bilingual (RU / EN) and takes about two minutes:

1. **License** — review and accept the MIT terms.
2. **Environment check** — PHP version, extensions and directory permissions.
3. **Database** — host, name, user, password, table prefix, optional demo data.
4. **Administrator** — site name, admin account, interface language.

### 5. Go live

Sign in at `https://your-domain.example/admin` and delete the `install/` directory — there is even a one-click button for it in the admin panel.

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

    # Engine internals must never be reachable directly
    location ^~ /system/  { deny all; }
    location ^~ /install/ { deny all; }  # enable after installation
}
```

Apache works out of the box thanks to the bundled `.htaccess`.

## Project structure

```text
BloggyCMS/
├── index.php                  # Single entry point and bootstrap
├── .htaccess                  # Rewrite rules, upload limits, directory protection
├── install/                   # Bilingual 4-step web installer + demo data
├── system/
│   ├── config/                # version.ini, generated config.php / database.php
│   ├── controllers/           # 26 modules (posts, pages, users, seo, addons, …)
│   ├── core/                  # 24 core classes: Event, Router, App, Database, …
│   ├── fields/                # 12 custom field types
│   ├── helpers/               # 40 helpers: API, Auth, CSRF, Captcha, Backup, …
│   ├── html_blocks/           # 12 HTML blocks for page layouts
│   ├── languages/             # ru_RU and en_En locale sets
│   └── post_blocks/           # 16 content blocks for the post editor
├── templates/
│   └── default/
│       ├── front/             # Public theme: layouts, partials, assets, block views
│       └── admin/             # Admin panel theme
├── uploads/                   # User content: images, avatars, tags, settings
└── LICENSE                    # MIT
```

### Anatomy of a module

Every controller follows the same predictable shape — drop a folder in, and the CMS discovers it:

```text
system/controllers/posts/
├── PostController.php     # Controller class
├── Model.php              # Data model (implements ModelAPI)
├── manifest.php           # Name, author, version, description, flags
├── routes.php             # URL map, with admin: true for protected routes
├── permissions.php        # Permission keys exposed to user groups
├── Settings.php           # Settings form rendered by ControllerManager
├── actions/               # One class per action (Index, Create, Edit, Delete, …)
└── hooks/                 # Files auto-loaded on boot — event listeners live here
```

## Architecture

```text
Request ──▶ index.php ──▶ App::run()
                            │
                            ├─ bootstrap: config → Database → core classes
                            ├─ load every controller's hooks/ (Event listeners)
                            ├─ run middleware chain (auth, admin guard)
                            └─ Router ──▶ Controller@Action ──▶ render(template)
                                                                    │
                              PostBlockManager · HtmlBlockTypeManager · FieldManager
                              AssetManager · Shortcodes · Breadcrumbs · Event::filter
```

Rendering is composable: a page template pulls HTML blocks, a post pulls post blocks, fields and shortcodes resolve inside the content, and every step fires events you can hook into.

## Extending BloggyCMS

### Listen to an event

Create `system/controllers/my_module/hooks/events.php` — the file is loaded automatically:

```php
<?php

Event::listen('post.created', function (array $data) {
    // $data contains the new post id, url and payload
    Logger::info('Post created: ' . ($data['url'] ?? ''));
}, 10, 1);

// Rewrite a value on its way out
Event::filter('post.excerpt', function (string $excerpt, array $post) {
    return mb_substr($excerpt, 0, 180) . '…';
}, 20, 2);
```

### Add a post block

```php
<?php

class CalloutBlock extends BasePostBlock
{
    public function getName(): string        { return 'Callout'; }
    public function getSystemName(): string  { return 'CalloutBlock'; }
    public function getDescription(): string { return 'Highlighted note with a link.'; }
    public function getIcon(): string        { return 'bi bi-megaphone'; }

    public function getSettingsForm($currentSettings = []): string
    {
        $fieldset = new \Fieldset('Callout', [
            'fields' => [
                \FieldFactory::select('tone', [
                    'title'   => 'Tone',
                    'options' => ['info' => 'Info', 'warning' => 'Warning'],
                ]),
            ],
        ]);

        return $fieldset->render($currentSettings);
    }

    public function getContentForm($currentContent = []): string { /* editor UI */ }
    public function getEditorHtml($settings = [], $content = []): string { /* preview */ }
}
```

Drop the file into `system/post_blocks/` and it appears in the editor. HTML blocks, fields and controllers follow the same "one file, auto-discovered" pattern.

### Register a shortcode

```php
Shortcodes::add('year', function ($attrs) {
    return date('Y');
});

// Usage in any content: {year}
```

## FAQ

<details>
<summary><b>Do I need Composer, Node.js or a build pipeline?</b></summary>
<br>
No. All front-end assets (Bootstrap, jQuery, icons, editor scripts) are vendored inside the theme. Upload and run.
</details>

<details>
<summary><b>Will I lose my content when I update the engine?</b></summary>
<br>
No. Your data lives in MySQL, uploaded files in <code>uploads/</code>, and credentials in <code>system/config/config.php</code> + <code>database.php</code> — none of those are replaced when you swap engine files. Back up the database and <code>uploads/</code> and you are safe.
</details>

<details>
<summary><b>Can I use it on shared hosting?</b></summary>
<br>
Yes, that is the design goal. PHP 8, MySQL and Apache with <code>mod_rewrite</code> are enough — no shell access, no daemons, no cron required.
</details>

<details>
<summary><b>How do I add a language to the admin interface?</b></summary>
<br>
Copy <code>system/languages/en_En/</code> to a new locale folder and translate the language files — the engine loads the locale set matching the configured language.
</details>

<details>
<summary><b>Can I use it with Nginx?</b></summary>
<br>
Yes — use the Nginx snippet above. The bundled <code>.htaccess</code> only applies to Apache.
</details>

## Contributing

<img src="https://img.shields.io/badge/PRs-welcome-brightgreen?style=flat-square" alt="PRs welcome">

1. **Discuss first** for anything bigger than a bugfix — [GitHub Discussions](https://github.com/albosoft/BloggyCms/discussions) is the place.
2. **Fork**, branch from `main` and keep commits focused.
3. **Use the issue templates**: [bug report](https://github.com/AlboSoft/BloggyCms/issues/new/choose) or [feature request](https://github.com/AlboSoft/BloggyCms/issues/new/choose).
4. **Respect the house style**: PHP 8 syntax, PSR-12-ish formatting, no third-party runtime dependencies, no build step, and every user-facing string goes into a language file (never hard-code text in a template).

Adding a module is the friendliest way to contribute: it lives entirely in its own folder under `system/controllers/` or `system/post_blocks/`, so review is quick.

## Security

If you discover a vulnerability, please **do not open a public issue**. Use [GitHub private security advisories](https://github.com/albosoft/BloggyCms/security/advisories/new) so a fix can be prepared before disclosure. Include repro steps, affected version and impact assessment — we aim to respond as quickly as possible.

## Support the project

BloggyCMS is built and maintained in spare time and stays free under MIT.

- ⭐ Star the [main repository](https://github.com/albosoft/BloggyCms) — it genuinely helps.
- 💬 Answer questions in [Discussions](https://github.com/albosoft/BloggyCms/discussions).
- 💛 [Support development via YooMoney](https://yoomoney.ru/to/4100119401729712).

## License

Released under the [MIT License](LICENSE) — free for personal and commercial use.
Copyright © 2026 [Pechora.Dev](https://github.com/albosoft).

<p align="center">
  <sub>Independent mirrors and forks are welcome. Blog anywhere.</sub>
</p>
