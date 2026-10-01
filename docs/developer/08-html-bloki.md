# 🧩 HTML-блоки

HTML-блок — это именованная секция страницы: шапка, подвал, hero, «последние посты», карточка автора. Тема выводит блоки по слотам, а администратор управляет их настройками и содержимым из админки.

В отличие от пост-блоков (внутри контента), HTML-блоки живут **в каркасе сайта** и переиспользуются по всему лендингу.

---

## Как это устроено

1. Класс блока наследуется от `BaseHtmlBlock` и лежит в `system/html_blocks/<Имя>Block.php`.
2. `HtmlBlockTypeManager` находит классы в системной папке и в темах (по манифесту).
3. Администратор создаёт **экземпляр** блока (`html_blocks`): название, slug, настройки, шаблон.
4. Тема выводит блок по slug:

```php
<?php render_html_block('header'); ?>
```

5. Рендер идёт по шаблону: `templates/<тема>/front/assets/html_blocks/<SystemName>/<template>.php`.

---

## Минимальный HTML-блок

```php
<?php
// system/html_blocks/CtaBlock.php

class CtaBlock extends BaseHtmlBlock
{
    public function getName(): string             { return 'Призыв к действию'; }
    public function getSystemName(): string       { return 'CtaBlock'; }
    public function getDescription(): string      { return 'Баннер с заголовком и кнопкой.'; }
    public function getShortDescription(): string { return 'Призыв к действию'; }
    public function getIcon(): string             { return 'bi bi-hand-index-thumb'; }
    public function getAuthor(): string           { return 'Ваше имя'; }
    public function getVersion(): string          { return '1.0.0'; }

    /** 'all' — блок доступен любой теме; иначе — только указанной */
    public function getTemplate(): string
    {
        return 'all';
    }

    public function getDefaultSettings(): array
    {
        return [
            'title'    => 'Готовы начать?',
            'subtitle' => '',
            'button'   => 'Начать',
            'link'     => '/contacts',
        ];
    }

    public function getSettingsForm($currentSettings = []): string
    {
        $settings = array_merge($this->getDefaultSettings(), $currentSettings);

        return (string) new Fieldset('Содержимое', [
            'icon'    => 'bi bi-megaphone',
            'columns' => '12',
            'fields'  => [
                FieldFactory::string('title',    ['title' => 'Заголовок', 'value' => $settings['title']]),
                FieldFactory::string('subtitle', ['title' => 'Подзаголовок', 'value' => $settings['subtitle']]),
                FieldFactory::string('button',   ['title' => 'Текст кнопки', 'value' => $settings['button']]),
                \FieldFactory::string('link',    ['title' => 'Ссылка', 'value' => $settings['link']]),
            ],
        ])->render($settings);
    }
}
```

Шаблон блока:

```php
<?php
// templates/default/front/assets/html_blocks/CtaBlock/default.php
/** @var array $settings  Настройки блока */
/** @var BaseHtmlBlock $block  Экземпляр блока */
/** @var string $templateName  Имя использованного шаблона */
?>
<section class="cta-block">
    <h2><?= html($settings['title'] ?? '') ?></h2>
    <?php if (!empty($settings['subtitle'])): ?>
        <p><?= html($settings['subtitle']) ?></p>
    <?php endif; ?>
    <a class="btn btn-primary" href="<?= html($settings['link'] ?? '/') ?>">
        <?= html($settings['button'] ?? 'Подробнее') ?>
    </a>
</section>
```

Создайте в админке блок типа `CtaBlock` со slug `cta-home` — и выведите его в шаблоне:

```php
<?= render_html_block('cta-home') ?>
```

> [!TIP]
> Для вставки в конкретной странице, а не в каркасе, используйте шорткод HTML-блока или блок «Контентный блок» в блочном редакторе.

---

## API `BaseHtmlBlock`

| Метод | Назначение |
|---|---|
| `getName()` | Название типа |
| `getSystemName()` | Уникальное системное имя |
| `getDescription()` | Описание для списка типов |
| `getShortDescription()` | Короткая подпись (палитра блоков) |
| `getIcon()` | Иконка |
| `getAuthor()` / `getVersion()` / `getAuthorWebsite()` | Метаданные |
| `getSettingsForm($currentSettings)` | Форма настроек экземпляра блока |
| `getTemplate()` | Область применения: `'all'` или имя темы |
| `getAvailableTemplates()` | Список доступных шаблонов |
| `getAdminCss()` / `getAdminJs()` | Ассеты админки |
| `getFrontendCss()` / `getFrontendJs()` | Ассеты фронтенда |
| `getFrontendInlineCss()` / `getFrontendInlineJs()` | Инлайн-стили и скрипты |
| `getSystemCss()` / `getSystemJs()` / `getSystemInlineCss()` / `getSystemInlineJs()` | Общие ассеты типа блока |
| `processFrontend($settings, $templateName = null)` | Рендер (обычно вызывает ядро) |

### Поиск шаблона

Движок ищет файл в таком порядке:

1. тема, заявленная в `getTemplate()`;
2. активная тема (`get_current_template()`);
3. тема-источник блока;
4. `default`.

Если указанного имени шаблона нет — берётся `default.php`. Если нет и его — выводится заглушка «шаблон не найден».

```
templates/<тема>/front/assets/html_blocks/<SystemName>/<template>.php
```

> [!NOTE]
> Поддерживается и «легаси»-путь `templates/<тема>/front/html_blocks/<SystemName>.php` — но новые блоки стоит делать в `assets/`.

---

## Слоты темы

Стандартная тема выводит пять слотов:

| Slug | Слот |
|---|---|
| `header` | Шапка |
| `breadcrumbs` | Хлебные крошки |
| `footer` | Подвал |
| `top` | Плавающие элементы (кнопка «вверх») |
| `cookies` | Согласие на cookie |

Слот — это просто slug блока. Вы можете создать другие блоки (`promo`, `sidebar`) и вывести их в своей теме.

```php
<?php // в своей теме
render_html_block('promo');
```

> [!WARNING]
> Если выводить один и тот же блок дважды, движок корректно обработает подгрузку ассетов — но разметка продублируется. Планируйте слоты заранее.

---

## Блоки в теме (theme blocks)

Тема может поставлять собственные HTML-блоки. Для этого в теме создаётся манифест:

```php
<?php
// templates/my-theme/html_blocks/manifest.php
return [
    ['class' => 'MyHeroBlock', 'file' => 'MyHeroBlock.php'],
    ['class' => 'MyPricingBlock', 'file' => 'MyPricingBlock.php'],
];
```

Классы лежат рядом, в `templates/my-theme/html_blocks/`. Тематические блоки обязаны указывать `getTemplate()` либо `'all'`, либо имя своей темы — иначе движок их отклонит.

> [!TIP]
> Это удобный способ поставлять блоки вместе с темой: они не попадают в системную папку и не конфликтуют с другими темами.

---

## Кеш и ассеты

HTML-блоки участвуют в общем кеше блоков:

- `init_blocks_cache()` в начале `layout.php` — готовит кеш;
- `get_blocks_css_url()` — URL собранного CSS;
- `clear_blocks_assets_cache()` / `regenerate_blocks_css()` — сброс и пересборка (вызываются автоматически при сохранении и удалении блока через события `html_block.saved` / `html_block.deleted`).

Если правки блока не видны на сайте — очистите кеш: `/admin/html-blocks/clear-cache`.

---

## Управление в админке

| Маршрут | Что делает |
|---|---|
| `/admin/html-blocks` | Список экземпляров блоков |
| `/admin/html-blocks/select-type` | Выбор типа при создании |
| `/admin/html-blocks/create`, `/edit/{id}`, `/delete/{id}` | CRUD экземпляров |
| `/admin/html-blocks/types` | Список типов, включение/выключение |
| `/admin/html-blocks/get-block-settings` | Форма настроек типа |
| `/admin/html-blocks/get-block-assets` | Ассеты типа |
| `/admin/html-blocks/get-block-templates` | Доступные шаблоны |
| `/admin/html-blocks/get-fragments` | Фрагменты для вставки |
| `/admin/html-blocks/clear-cache` | Очистка кеша блоков |

---

## Чек-лист нового HTML-блока

1. Класс в `system/html_blocks/` (или в теме с манифестом).
2. `getSystemName()` уникален и совпадает с именем класса.
3. `getTemplate()` определён корректно (`all` или тема).
4. Есть `getDefaultSettings()` — блок работает даже без настройки.
5. Шаблон лежит в `front/assets/html_blocks/<SystemName>/default.php`, использует `$settings`.
6. Вывод экранируется (`html()`), изображения подставляются через `front_image()`.
7. Ассеты подключаются методами блока, а не хардкодом.
8. Кеш блоков очищен и блок проверен на сайте.
9. Строки интерфейса — в языковом файле `html_blocks.php`.

---

[← Пост-блоки](07-post-bloki.md) · [Оглавление](../README.md) · [Шорткоды и локализация →](09-shortcodes-i-lokalizaciya.md)