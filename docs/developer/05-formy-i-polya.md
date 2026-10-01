# 📝 Формы и поля

Формы в BloggyCMS описываются **декларативно**: вы перечисляете поля и их параметры, а движок сам рисует разметку, валидирует данные и обрабатывает отправку. Это касается настроек модулей, карточек в админке и панелей блоков.

Три инструмента:

| Инструмент | Для чего |
|---|---|
| `FieldFactory` | Создать поле (`string`, `image`, `select`, …) |
| `Fieldset` | Сгруппировать поля в секцию с заголовком и колонками |
| `AdminForm` | Форма целиком: филдсеты, маппинг в БД, валидация, CSRF |

---

## `FieldFactory` — создание полей

```php
<?php
FieldFactory::string('name', ['title' => 'Название']);
FieldFactory::textarea('description', ['title' => 'Описание', 'rows' => 6]);
FieldFactory::number('count', ['title' => 'Количество', 'min' => 1, 'max' => 100]);
FieldFactory::checkbox('enabled', ['title' => 'Включено', 'switch' => true]);
FieldFactory::select('layout', ['title' => 'Раскладка', 'options' => ['grid' => 'Сетка', 'list' => 'Список']]);
FieldFactory::image('cover', ['title' => 'Обложка', 'upload_path' => 'uploads/covers/']);
FieldFactory::color('accent', ['title' => 'Акцентный цвет', 'default' => '#ff6b35']);
FieldFactory::date('publish_at', ['title' => 'Дата публикации']);
FieldFactory::icon('icon', ['title' => 'Иконка']);
FieldFactory::repeater('links', ['title' => 'Ссылки', 'fields' => [ /* вложенные поля */ ]]);
FieldFactory::alert('note', ['title' => 'Подсказка', 'hint' => 'Текст', 'type' => 'info']);
FieldFactory::blockImage('logo', ['title' => 'Логотип', 'preview_size' => '100px']);
```

### Общие параметры полей

| Параметр | Тип | Значение |
|---|---|---|
| `title` | string | Подпись поля |
| `hint` | string | Пояснение под полем |
| `default` | mixed | Значение по умолчанию |
| `required` | bool | Обязательное поле |
| `placeholder` | string | Подсказка внутри поля |
| `class` | string | Дополнительный CSS-класс |
| `attributes` | array | Произвольные HTML-атрибуты |
| `show` | string | Условие показа (см. ниже) |
| `column` | string | Ширина: `'6'`, `'12'` — внутри строки Bootstrap |
| `full_width` | bool | Поле на всю ширину филдсета |
| `storage` | string | Куда сохранять: `field` (таблица `field_values`) или другое |
| `db_field` | string | Колонка в БД (по умолчанию — имя поля) |
| `json_key` | string | Ключ внутри JSON-поля |

### Параметры по типам

| Тип | Дополнительно |
|---|---|
| `number` | `min`, `max`, `step` |
| `select`, `multiselect` | `options` — ассоциативный массив «значение → подпись» |
| `string`, `textarea` | `maxlength`, `rows` |
| `image`, `blockImage` | `upload_path`, `preview_size`, `allowed_types`, `max_size` |
| `checkbox` | `switch` — переключатель в стиле Bootstrap |
| `alert` | `type` (`info`, `warning`, `danger`, `success`), `icon` |
| `repeater` | `fields` — массив вложенных полей группы |

### Условный показ

Поле можно показывать только при определённом значении другого поля:

```php
<?php
FieldFactory::number('adult_min_age', [
    'title' => 'Минимальный возраст',
    'default' => 18,
    'show'  => 'field:adult_content_action != none',
]);
```

Синтаксис: `field:<имя> <оператор> <значение>`, операторы — `=`, `!=`, `>`, `<`, `>=`, `<=`. Работает и в админке (скрипт скрывает/показывает блок), и при валидации.

---

## `Fieldset` — секция формы

```php
<?php
$fieldset = new Fieldset('Основные настройки', [
    'icon'    => 'bi bi-gear',
    'columns' => '12',          // 'custom' — если ширину задаёт каждое поле
    'fields'  => [
        FieldFactory::string('title', ['title' => 'Заголовок', 'column' => '6']),
        FieldFactory::checkbox('active', ['title' => 'Активно', 'column' => '6', 'switch' => true]),
    ],
]);

echo $fieldset->render($currentSettings);   // $currentSettings — текущие значения
```

`render()` сам подставит сохранённые значения, нарисует условия показа и обёртки колонок.

---

## `AdminForm` — форма целиком

Когда нужна не только настройка, но и полноценная форма с валидацией и сохранением в таблицу, наследуйтесь от `AdminForm`.

```php
<?php

namespace tags\forms;

class TagForm extends AdminForm
{
    public function getTitle(): string
    {
        return 'Тег';
    }

    /** Какие поля и куда сохранять */
    public function getFieldMapping(): array
    {
        return [
            'name'        => ['storage' => 'field', 'db_field' => 'name'],
            'description' => ['storage' => 'field', 'db_field' => 'description'],
            'image'       => ['storage' => 'field', 'db_field' => 'image'],
        ];
    }

    /** Секции формы */
    public function getFieldsets(): array
    {
        return [
            new Fieldset('Основное', [
                'icon'    => 'bi bi-info-circle',
                'columns' => 'custom',
                'fields'  => [
                    FieldFactory::string('name', [
                        'title'    => 'Название',
                        'required' => true,
                        'column'   => '12',
                    ]),
                    FieldFactory::image('image', [
                        'title'       => 'Изображение',
                        'upload_path' => 'uploads/tags/',
                        'column'      => '6',
                    ]),
                ],
            ]),
        ];
    }

    /** Своя проверка (необязательно) */
    public function validate(): void
    {
        parent::validate();

        if (mb_strlen((string) $this->getData()['fields']['name'] ?? '') < 2) {
            $this->addError('name', 'Слишком короткое название');
        }
    }
}
```

Использование в экшене:

```php
<?php
$form = new \tags\forms\TagForm($this->db);

if ($form->isSubmitted()) {
    $form->handleRequest($_POST, $_FILES);   // подготовка, загрузка файлов, валидация

    if ($form->isValid()) {
        $data = $form->getData();
        $this->tagModel->create($data['fields']);
        Notification::success('Тег сохранён');
        $this->controller->redirect(ADMIN_URL . '/tags');
    }
}

echo $form->render($currentValues);
```

Ключевые методы:

| Метод | Назначение |
|---|---|
| `getTitle()` | Заголовок формы |
| `getFieldsets()` | Массив `Fieldset` |
| `getFieldMapping()` | Правила сохранения полей |
| `prepareData(array $post, array $files)` | Подготовить данные (можно переопределить) |
| `validate()` / `addError($field, $msg)` | Проверки |
| `isValid()` / `getErrors()` | Результат валидации |
| `getData()` / `getFieldsData()` / `getJsonData()` | Данные для сохранения |
| `isSubmitted()` | Форма отправлена |
| `getCsrfToken()` / `verifyCsrfToken($token)` | Защита от CSRF |
| `render(array $currentData = [])` | HTML формы |

> [!TIP]
> Форма сама добавляет CSRF-поле и проверяет токен в `handleRequest()`. Не отключайте эту защиту и не дублируйте её вручную.

---

## Пользовательские поля (типы данных)

Отдельная история — **пользовательские поля** для постов, страниц, категорий и пользователей. Они создаются в админке (`/admin/fields`) и доступны как шорткоды и через хелперы.

### Свой тип поля

Тип поля — класс, который наследуется от `BaseField` и живёт в `system/fields/`:

```php
<?php

class RatingField extends BaseField
{
    public function getType(): string
    {
        return 'rating';            // уникальный ключ типа
    }

    public function getName(): string
    {
        return 'Рейтинг';           // подпись в админке
    }

    public function renderInput($value, $entityType, $entityId): string
    {
        $value = (int) ($value ?? 5);
        $html = '<select name="' . $this->getName() . '" class="form-select">';
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<option value="' . $i . '"' . ($i === $value ? ' selected' : '') . '>' . $i . '</option>';
        }
        return $html . '</select>';
    }

    public function renderDisplay($value, $entityType, $entityId): string
    {
        return str_repeat('★', (int) $value) ?: '—';
    }

    public function renderList($value, $entityType, $entityId): string
    {
        return (string) (int) $value;
    }

    public function validate($value): bool
    {
        return is_numeric($value) && $value >= 1 && $value <= 5;
    }

    /** Необязательно: своя форма настроек типа */
    public function getSettingsForm(): string
    {
        return (new Fieldset('Рейтинг', [
            'fields' => [
                FieldFactory::number('max', ['title' => 'Максимум', 'default' => 5]),
            ],
        ]))->render($this->getConfig());
    }
}
```

**Как это работает:**

| Метод | Роль |
|---|---|
| `getType()` | Ключ типа, он же — значение в таблице `fields.type` |
| `getName()` | Название для администратора |
| `renderInput()` | Поле ввода в админке |
| `renderDisplay()` | Вывод значения на сайте |
| `renderList()` | Вывод в списках админки |
| `validate($value)` | Проверка значения |
| `processValue($value)` | Преобразование перед сохранением |
| `requiresFileUpload()` | Работа с файлами |
| `getShortcode()` | Имя шорткода (по умолчанию `<сущность>_<системное_имя>`) |
| `renderShortcode($attrs)` | Вывод через шорткод |

Движок сам подхватывает файлы из `system/fields/`: `FieldManager::loadCoreFields()` читает папку и регистрирует классы, реализующие `BaseField`.

> [!NOTE]
> Вспомогательные элементы форм (не «поля сущностей», а части интерфейса) лежат в `system/helpers/fields/`: `FieldAlert`, `FieldBlockImage`, `FieldCheckbox`, `FieldColor`, `FieldDate`, `FieldIcon`, `FieldImage`, `FieldNumber`, `FieldRepeater`, `FieldSelect`, `FieldString`, `FieldTextarea`.

---

## Вывод значений полей

```php
<?php
// Все поля сущности
$fields = get_custom_fields('post', $post['id']);

// Одно значение
$subtitle = get_custom_field_value('subtitle', 'post', $post['id']);

// Готовый вывод с учётом типа поля
echo get_custom_field_display('subtitle', 'post', $post['id']);

// Проверка наличия
if (has_custom_field('subtitle', 'post', $post['id'])) { /* ... */ }
```

Шорткоды:

```text
{post_subtitle}
{post_rating}
{field name="subtitle" type="post" id="12" default="—"}
{field_display name="cover" type="post" id="12"}
```

---

## Чек-лист форм

1. Поля описаны через `FieldFactory`, а не «руками» в HTML.
2. Обязательные поля помечены `required`, проверки — в `validate()`.
3. CSRF включён (не переопределяйте `getCsrfToken()`).
4. Условия показа протестированы с разными наборами данных.
5. Загружаемые файлы проверяются по типу и размеру.
6. Все подписи — из языковых файлов.
7. После сохранения показывается `Notification::success()` и выполняется редирект (защита от повторной отправки).

---

[← Модели и база данных](04-modeli-i-baza-dannyh.md) · [Оглавление](../README.md) · [Шаблонизация и темы →](06-shablonizaciya-i-temy.md)