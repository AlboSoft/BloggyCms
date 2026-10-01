# ❓ FAQ и типовые ошибки

Короткие ответы на частые вопросы — по установке, работе сайта, разработке и обслуживанию. Если ответа нет, начните с [поиска по документации](../README.md) и проверки `/admin/debug`.

---

## Установка и запуск

<details>
<summary><b>Какой минимум нужен для установки?</b></summary>
<br>
PHP 8.0+ с расширениями <code>pdo_mysql</code>, <code>mysqli</code>, <code>mbstring</code>, <code>json</code>, <code>fileinfo</code>, <code>session</code>, <code>openssl</code>, MySQL 5.7+/MariaDB 10.2+, права на запись в <code>uploads/</code>, <code>system/config/</code>, <code>templates/</code>. Для установки пакетов — расширение <code>ZipArchive</code>.
</details>

<details>
<summary><b>Установщик пишет, что директории не записываемые. Что делать?</b></summary>
<br>
<pre><code>chmod -R 755 uploads system/config templates</code></pre>
Если не помогло — владелец файлов и пользователь PHP должны совпадать (или входить в одну группу). Не выставляйте 777.
</details>

<details>
<summary><b>После установки вижу предупреждение про папку install/. Это критично?</b></summary>
<br>
Да. Пока папка существует, мастер установки можно запустить заново — в том числе постороннему. Удалите её: кнопка в админке или <code>rm -rf install/</code>.
</details>

<details>
<summary><b>Сайт отдаёт 404 на всех страницах. Почему?</b></summary>
<br>
Не работает переписывание URL. Apache: включите <code>mod_rewrite</code> и разрешите <code>AllowOverride All</code>. Nginx: добавьте <code>try_files $uri $uri/ /index.php?$query_string;</code>.
</details>

<details>
<summary><b>Можно ли работать без Composer и Node.js?</b></summary>
<br>
Да, они не нужны. Все библиотеки лежат внутри темы.
</details>

---

## Работа с сайтом

<details>
<summary><b>Пост не виден на сайте. Что проверить?</b></summary>
<br>
Статус (<code>published</code>), дату публикации, пароль на пост, флаг 18+, список групп в «Показывать/Скрывать», а также кеш браузера. Для черновика гость всегда получит 404.
</details>

<details>
<summary><b>Как поставить пост «в будущем»?</b></summary>
<br>
Укажите дату публикации в будущем и статус «опубликован». В ленте материал появится, когда наступит дата.
</details>

<details>
<summary><b>Где менять логотип и подвал?</b></summary>
<br>
Это HTML-блоки <code>header</code> и <code>footer</code> (раздел «Контент-блоки»). Редактирование кода не требуется.
</details>

<details>
<summary><b>Как добавить пункт в меню?</b></summary>
<br>
Меню → пункты. Ссылка может быть внутренней (<code>/posts</code>) или внешней. Поддерживаются подстановки вроде <code>{slug}</code>, <code>{site_name}</code>, <code>{user_field:имя}</code>.
</details>

<details>
<summary><b>Комментарии уходят на модерацию. Как отключить?</b></summary>
<br>
Либо выдайте право <code>comment_add_no_moderations</code> группе, либо настройте премодерацию в настройках модуля <code>comments</code>.
</details>

<details>
<summary><b>Страницы 18+ показываются всем. Как включить барьер?</b></summary>
<br>
Настройки → Посты → <code>adult_content_action</code> = «Возрастная проверка» или «Перенаправлять на вход». На постах должен стоять флаг 18+.
</details>

---

## Оформление и темы

<details>
<summary><b>Сломал тему правкой в редакторе шаблонов. Как восстановить?</b></summary>
<br>
Если включены бэкапы (<code>site.template_backups_enabled</code>), рядом с файлом лежит <code>&lt;файл&gt;.backup.&lt;дата&gt;</code> — переименуйте его обратно. Впредь включайте бэкапы до правок.
</details>

<details>
<summary><b>Изменения в CSS не видны на сайте.</b></summary>
<br>
Очистите кеш блоков (Контент-блоки → Очистить кеш блоков), затем обновите страницу с <code>Ctrl+F5</code>. Проверьте, что правите файл из активной темы, а не из <code>default</code>, если тема другая.
</details>

<details>
<summary><b>Можно ли поставить свою тему?</b></summary>
<br>
Да: скопируйте папку темы в <code>templates/</code> и выберите её в «Настройки → Сайт → Тема оформления». Обязательный минимум — <code>front/layout.php</code>, <code>404.php</code>, <code>500.php</code>.
</details>

<details>
<summary><b>Как изменить текст кнопки «Читать далее»?</b></summary>
<br>
Через языковые файлы: <code>system/languages/&lt;локаль&gt;/templates/…</code>. Либо переопределите шаблон в своей теме.
</details>

---

## Пользователи и права

<details>
<summary><b>Как запретить регистрацию?</b></summary>
<br>
Настройки → Авторизация → <code>enable_register</code> = выкл. Можно заполнить <code>disable_register_reason</code>, чтобы объяснить посетителям причину.
</details>

<details>
<summary><b>Забыл пароль администратора, восстановление не работает.</b></summary>
<br>
Восстановите через базу: создайте администратора напрямую в таблице <code>users</code> (<code>is_admin = 1</code>, пароль — хеш <code>password_hash()</code>) или восстановите дамп. На будущее держите второго администратора.
</details>

<details>
<summary><b>Как дать редактору доступ к постам, но не к настройкам?</b></summary>
<br>
Создайте группу, выдайте только нужные права (<code>post_view</code> и т. п.) и не ставьте флаг «Администратор» — без него вход в панель закрыт. Если нужен вход именно в админку, используйте флаг + ограничьте права, но проверяйте чувствительные разделы отдельно.
</details>

---

## SEO

<details>
<summary><b>sitemap.xml не обновляется.</b></summary>
<br>
Файл пересобирается при событиях контента; убедитесь, что есть права на запись в корень сайта, и проверьте, что карта включена (<code>enable_sitemap</code> / <code>seo_sitemap.enabled</code>).
</details>

<details>
<summary><b>IndexNow не отправляет адреса.</b></summary>
<br>
Проверьте: ключ сгенерирован, файл <code>/{key}.txt</code> открывается, <code>auto_submit</code> включён. Очередь разбирается вызовом <code>/admin/seo/process-queue?token=…</code> — настройте cron, если он не настроен.
</details>

<details>
<summary><b>Старые meta-теги на страницах.</b></summary>
<br>
Очистите кеш SEO: <code>/admin/seo/clear-cache</code>. Также проверьте, не заданы ли значения в самой сущности (они приоритетнее настроек по умолчанию).
</details>

---

## Разработка

<details>
<summary><b>Ошибка «Template file not found».</b></summary>
<br>
Нет файла шаблона ни в активной теме, ни в <code>default</code>. Проверьте путь и имя файла, переданные в <code>render()</code>.
</details>

<details>
<summary><b>Ошибка «Action X not found in controller».</b></summary>
<br>
В контроллере нет метода <code>XAction()</code>. Сверьте <code>routes.php</code> с методами контроллера: имя действия в маршруте и имя метода должны совпадать.
</details>

<details>
<summary><b>Как добавить свою страницу в админку?</b></summary>
<br>
Создайте модуль в <code>system/controllers/</code> с маршрутом <code>admin/my-thing</code> и флагом <code>'admin' =&gt; true</code>. Ссылку в меню можно добавить через фильтр <code>admin.menu.items</code>.
</details>

<details>
<summary><b>Как узнать, какие события можно слушать?</b></summary>
<br>
Полный список — в разделе <a href="../developer/02-sobytiya-i-huki.md">События и хуки</a>. Сверить в конкретной установке:
<pre><code>grep -rn "Event::trigger(" system/ | grep -o "trigger('[^']*"</code></pre>
</details>

<details>
<summary><b>Как добавить шорткод?</b></summary>
<br>
<pre><code>&lt;?php
Shortcodes::add('year', fn($attrs) =&gt; date('Y'));</code></pre>
Примеры и список встроенных шорткодов — в разделе <a href="../developer/09-shortcodes-i-lokalizaciya.md">Шорткоды и локализация</a>.
</details>

<details>
<summary><b>Как передать данные из PHP в JavaScript?</b></summary>
<br>
Через <code>js_config([...])</code> или <code>js_var('name', $value)</code> — без ручной склейки строк и рисков экранирования.
</details>

<details>
<summary><b>Почему строки не переводятся?</b></summary>
<br>
Значение настройки языка должно совпадать с именем папки локали (<code>ru_RU</code>, <code>en_En</code>). Непереведённые строки видны как имена констант <code>LANG_…</code>.
</details>

---

## Обслуживание

<details>
<summary><b>Что обязательно бэкапить?</b></summary>
<br>
Базу данных, папку <code>uploads/</code> и файлы <code>system/config/config.php</code> + <code>database.php</code>. Периодичность — от ежедневной для активных проектов.
</details>

<details>
<summary><b>Как обновить движок, не потеряв контент?</b></summary>
<br>
Бэкап → замена файлов движка (<code>index.php</code>, <code>system/</code>, <code>templates/default/</code>, <code>.htaccess</code>) → <b>не перезаписывать</b> <code>system/config/</code>, <code>uploads/</code> и свою тему. Проверить сайт и админку. Подробнее — в разделе <a href="../administrator/11-dopolneniya-i-obnovleniya.md">Дополнения и обновления</a>.
</details>

<details>
<summary><b>Куда пишутся ошибки?</b></summary>
<br>
В лог сервера и — при включённом <code>debug_mode</code> — в таблицу <code>debug_logs</code>, откуда их видно в <code>/admin/debug</code>.
</details>

<details>
<summary><b>Сайт стал медленным. С чего начать?</b></summary>
<br>
1) Оптимизируйте изображения; 2) очистите кеш блоков при подозрении на «раздутые» стили; 3) проверьте тяжёлые запросы в <code>debug_logs</code>; 4) обновите PHP до 8.3+; 5) проверьте сторонние скрипты, подключённые через <code>add_bottom_js()</code>.
</details>

<details>
<summary><b>Подозреваю взлом. Что делать первым делом?</b></summary>
<br>
Сменить пароли (админ + база), проверить пользователей с <code>is_admin</code>, просмотреть незнакомые PHP-файлы в корне и <code>uploads/</code>, изучить логи, восстановить сайт из бэкапа и обновить движок.
</details>

---

## Термины

| Термин | Значение |
|---|---|
| **Модуль (контроллер)** | Папка в `system/controllers/`, дающая маршруты, модели и экшены |
| **Экшен** | Класс, выполняющий одно действие (`Index`, `AdminCreate`, …) |
| **Пост-блок** | Элемент блочного редактора контента |
| **HTML-блок** | Секция каркаса сайта (шапка, подвал, hero) |
| **Фрагмент** | Переиспользуемая сущность контента с полями и записями |
| **Поле** | Пользовательские данные сущности (`post`, `page`, `category`, `user`) |
| **Слот** | Место вывода HTML-блока в теме (по его slug) |
| **Хук** | Файл `hooks/*.php` модуля, загружаемый при старте |
| **Событие** | Точка расширения `Event` (`post.created`, `admin.menu.items`) |
| **Шорткод** | Вставка в контент, например `{year}` или `{fragment:reviews}` |
| **Пакет (дополнение)** | ZIP с `package.ini` и папкой `files/` |

---

[← Таблицы базы данных](tablicy-bd.md) · [Оглавление](../README.md)