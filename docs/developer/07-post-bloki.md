# 🧱 Пост-блоки

Пост-блок — элемент блочного редактора: «кирпичик», из которого собирается содержимое поста или страницы. Движок сам находит ваши блоки в `system/post_blocks/`, показывает их в редакторе и подключает их ассеты.

Создание блока — самый быстрый способ добавить редактору новую возможность.

---

## Как это устроено

1. Класс блока наследуется от `BasePostBlock` и лежит в `system/post_blocks/<Имя>Block.php`.
2. `PostBlockManager` сканирует папку и регистрирует все неабстрактные наследники.
3. В редакторе блок предлагается как элемент; для него вызываются:
   - `getContentForm()` — форма содержимого;
   - `getSettingsForm()` — форма настроек;
   - `getEditorHtml()` — как блок выглядит в редакторе.
4. На сайте вызывается `processFrontend()`, который рендерит блок по шаблону.
5. Настройки блоков, их шаблоны и пресеты хранятся в базе (`post_block_settings`, `post_block_presets`), а не только в коде — поэтому администратор может менять разметку без правки PHP.

---

## Минимальный блок

```php
<?php
// system/post_blocks/CalloutBlock.php

class CalloutBlock extends BasePostBlock
{
    public function getName(): string        { return 'Врезка'; }
    public function getSystemName(): string  { return 'CalloutBlock'; }
    public function getDescription(): string { return 'Выделенное сообщение с иконкой.'; }
    public function getIcon(): string        { return 'bi bi-megaphone'; }
    public function getCategory(): string    { return 'text'; }

    public function getDefaultContent(): array
    {
        return ['text' => '', 'tone' => 'info'];
    }

    public function getDefaultSettings(): array
    {
        return ['icon' => 'info-circle', 'custom_class' => ''];
    }

    /** Форма содержимого в редакторе */
    public function getContentForm($currentContent = []): string
    {
        $content = array_merge($this->getDefaultContent(), $currentContent);

        return (string) new Fieldset('Содержимое', [
            'columns' => '12',
            'fields'  => [
                FieldFactory::textarea('text', [
                    'title' => 'Текст',
                    'value' => $content['text'],
                    'rows'  => 4,
                ]),
                FieldFactory::select('tone', [
                    'title'   => 'Тон',
                    'value'   => $content['tone'],
                    'options' => ['info' => 'Информация', 'warning' => 'Предупреждение'],
                ]),
            ],
        ])->render($content);
    }

    /** Панель настроек блока */
    public function getSettingsForm($currentSettings = []): string
    {
        $settings = array_merge($this->getDefaultSettings(), $currentSettings);

        return (string) new Fieldset('Настройки', [
            'columns' => '12',
            'fields'  => [
                FieldFactory::string('icon', [
                    'title'   => 'Иконка',
                    'value'   => $settings['icon'],
                    'hint'    => 'Например: info-circle, exclamation-triangle',
                ]),
                FieldFactory::string('custom_class', [
                    'title' => 'Дополнительный CSS-класс',
                    'value' => $settings['custom_class'],
                ]),
            ],
        ])->render($settings);
    }

    /** Как блок выглядит в редакторе */
    public function getEditorHtml($settings = [], $content = []): string
    {
        $content  = array_merge($this->getDefaultContent(), $content);
        $settings = array_merge($this->getDefaultSettings(), $settings);

        return '<div class="callout callout-' . html($content['tone']) . '">'
             . $this->getIconSvg($settings['icon'])
             . '<div>{text}</div>'
             . '</div>';
    }

    /** Рендер на сайте */
    public function processFrontend($content, $settings = []): string
    {
        $content  = array_merge($this->getDefaultContent(), $content);
        $settings = array_merge($this->getDefaultSettings(), $settings);

        return '<div class="callout callout-' . html($content['tone']) . '">'
             . '<div class="callout-icon">' . html($settings['icon']) . '</div>'
             . '<div class="callout-text">' . nl2br(html($content['text'])) . '</div>'
             . '</div>';
    }

    /** Что можно вставить в шаблон блока */
    public function getShortcodes(): array
    {
        return array_merge(parent::getShortcodes(), [
            '{text}' => 'Текст врезки',
            '{tone}' => 'Тон: info / warning',
            '{icon}' => 'Иконка',
        ]);
    }
}
```

Готово: блок появился в редакторе постов и страниц. Ничего регистрировать в базе вручную не нужно.

---

## API `BasePostBlock`

### Обязательно реализовать

| Метод | Назначение |
|---|---|
| `getName(): string` | Название блока в редакторе |
| `getSystemName(): string` | Уникальное системное имя (совпадает с именем класса) |
| `getDescription(): string` | Описание для списка блоков |
| `getSettingsForm($currentSettings): string` | Форма настроек блока |
| `getContentForm($currentContent): string` | Форма содержимого |
| `getEditorHtml($settings, $content): string` | Представление в редакторе |

### Можно переопределить

| Метод | Что даёт |
|---|---|
| `getIcon(): string` | Иконка (Bootstrap Icons, например `bi bi-image`) |
| `getAuthor()`, `getVersion()` | Метаданные блока |
| `getCategory(): string` | Группа в палитре блоков (`text`, `media`, `layout`, …) |
| `getDefaultContent(): array` | Значения по умолчанию для содержимого |
| `getDefaultSettings(): array` | Значения по умолчанию для настроек |
| `processFrontend($content, $settings): string` | Как блок выводится на сайте |
| `getShortcodes(): array` | Подсказка «какие подстановки доступны в шаблоне» |
| `canUseInPosts()` / `canUseInPages()` | Где блок разрешён |
| `validateSettings()` / `prepareSettings()` / `prepareContent()` | Нормализация данных |
| `getAvailablePresets()` / `getPresetSelector()` | Пресеты оформления |
| `getAdminCss()` / `getAdminJs()` / `getFrontendCss()` / `getFrontendJs()` | Подключение ассетов |
| `getAdminInlineCss()` / `getFrontendInlineCss()` и JS-аналоги | Инлайн-код блока |
| `loadAssets(bool $isAdmin = false)` | Ручное управление ассетами |

---

## Шаблоны блоков и шорткоды

Разметка блока хранится в базе: в общих настройках блока (`post_block_settings.template`), в настройках конкретного вхождения в пост или в пресете. Так администратор может поменять вид блока из админки (`/admin/post-blocks/edit/{system_name}`), не трогая код.

В шаблоне доступны подстановки:

| Подстановка | Значение |
|---|---|
| `{block_type}`, `{block_name}`, `{block_id}` | Идентификация блока |
| `{preset_id}`, `{preset_name}` | Пресет |
| `{custom_class}` | Дополнительный CSS-класс |
| Собственные (`{text}`, `{image_url}`, …) | Из `getShortcodes()` |

Пример шаблона для нашего `CalloutBlock`:

```html
<div class="callout {custom_class} preset-{preset_id}">
    <div class="callout-text">{text}</div>
</div>
```

### Пресеты

Пресет — сохранённый вариант шаблона с именем. Пользователь выбирает пресет в редакторе, а вы храните варианты оформления блока в базе. Реализуется через методы `getAvailablePresets()`, `getPreset($id)`, `getPresetSelector($currentSettings)` — в базовом классе уже есть рабочая логика, достаточно её использовать.

---

## Ассеты блока

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

Движок соберёт их в общий поток ассетов — отдельно подключать ничего не нужно. Файлы удобно держать рядом с шаблоном:

```text
templates/default/front/assets/postblocks/calloutblock/
├── callout.css
└── callout.js
```

> [!TIP]
> Инлайн-варианты (`getFrontendInlineCss()`, `getFrontendInlineJs()`) удобны для маленьких блоков — не создают лишних HTTP-запросов.

---

## Управление блоками в админке

| Раздел | Что можно |
|---|---|
| `/admin/post-blocks` | Список типов: включён ли в постах и страницах, кем создан, версия |
| `/admin/post-blocks/edit/{system_name}` | Редактор шаблона блока и его настроек |
| `/admin/post-blocks/get-preview` | AJAX-предпросмотр |
| `/admin/post-blocks/get-block-blueprint` | Схема блока (для редактора) |
| `/admin/post-blocks/get-settings-form` | Форма настроек конкретного блока |
| `/admin/post-blocks/get-presets` | Доступные пресеты |

Флаги `enable_in_posts` и `enable_in_pages` (таблица `post_block_settings`) позволяют спрятать блок из ненужного контекста.

---

## Чек-лист нового пост-блока

1. Класс в `system/post_blocks/`, имя файла = имя класса.
2. Реализованы 6 обязательных методов.
3. Все поля форм созданы через `FieldFactory`, значения подставлены из `$currentContent` / `$currentSettings`.
4. Текст экранируется: `html()`, `nl2br()` для переносов.
5. На сайте заданы значения по умолчанию — блок не должен «падать» с пустыми данными.
6. `getShortcodes()` описывает подстановки: администратору будет понятно, что менять.
7. CSS/JS блока подключаются через методы ассетов, а не жёстко в шаблоне.
8. Блок проверен в постах, страницах и в предпросмотре.
9. Строки интерфейса — в языковом файле `post_blocks.php` соответствующей локали.

---

[← Шаблонизация и темы](06-shablonizaciya-i-temy.md) · [Оглавление](../README.md) · [HTML-блоки →](08-html-bloki.md)