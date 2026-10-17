<?php

/**
* Хелпер для работы с фрагментами
*/
class FragmentHelper {
    
    /**
    * @var FragmentModel
    */
    private static $fragmentModel;
    
    /**
    * @var FragmentEntryModel
    */
    private static $entryModel;
    
    /**
    * @var FieldManager
    */
    private static $fieldManager;
    
    /**
    * @var bool Флаг, что шорткоды уже зарегистрированы
    */
    private static $registered = false;
    
    /**
    * Инициализация
    */
    private static function init() {
        if (!self::$fragmentModel) {
            $db = Database::getInstance();
            self::$fragmentModel = new FragmentModel($db);
            self::$entryModel = new FragmentEntryModel($db);
            self::$fieldManager = new FieldManager($db);
        }
    }
    
    /**
    * Регистрация шорткодов фрагментов
    */
    public static function registerShortcodes() {
        if (!class_exists('ShortcodeRegistry')) {
            self::scheduleDelayedRegistration();
            return;
        }
        
        if (!class_exists('FragmentModel')) {
            self::scheduleDelayedRegistration();
            return;
        }
        
        if (self::$registered) {
            return;
        }
        
        self::init();
        
        $fragments = self::$fragmentModel->getActive();
        
        foreach ($fragments as $fragment) {
            $systemName = $fragment['system_name'];
            
            ShortcodeRegistry::add('fragment:' . $systemName, function($attrs) use ($systemName) {
                return self::renderFragment($systemName);
            });
            
            ShortcodeRegistry::add($systemName, function($attrs) use ($systemName) {
                return self::renderFragment($systemName);
            });
            
            ShortcodeRegistry::add('ctype:' . $systemName, function($attrs, $content = null) use ($systemName) {
                if ($content !== null) {
                    return self::renderFragmentWithTemplate($systemName, $content);
                }
                return self::renderFragment($systemName);
            });
        }
        
        self::$registered = true;
    }
    
    /**
    * Рендеринг фрагмента с кастомным шаблоном (без цикла!)
    * 
    * @param string $systemName
    * @param string $template
    * @return string
    */
    public static function renderFragmentWithTemplate($systemName, $template) {
        self::init();
        
        $fragment = self::$fragmentModel->getBySystemName($systemName);
        
        if (!$fragment || $fragment['status'] !== 'active') {
            return '<!-- Фрагмент "' . htmlspecialchars($systemName) . '" не найден -->';
        }
        
        $entries = self::$entryModel->getByFragment($fragment['id'], 'active');
        
        if (empty($entries)) {
            return '<!-- Нет записей во фрагменте "' . htmlspecialchars($systemName) . '" -->';
        }
        
        $fields = self::$fragmentModel->getFields($fragment['id']);
        $output = '';
        $index = 0;
        $total = count($entries);
        
        foreach ($entries as $entry) {
            $index++;
            $output .= self::applyEntryPlaceholders($template, $fragment, $entry, $fields, $index, $total);
        }
        
        return $output;
    }
    
    /**
    * Рендеринг шаблона одной записи фрагмента (страница записи)
    * 
    * @param array $fragment
    * @param array $entry
    * @param array $fields
    * @return string
    */
    public static function renderEntryContent($fragment, $entry, $fields) {
        self::init();
        
        $template = (string)($fragment['entry_template'] ?? '');
        
        if (trim($template) === '') {
            return self::renderDefaultEntry($fragment, $entry, $fields);
        }
        
        $output = self::applyEntryPlaceholders($template, $fragment, $entry, $fields, 1, 1);
        
        if (class_exists('Shortcodes')) {
            $output = \Shortcodes::process($output);
        }
        
        if (function_exists('process_shortcodes')) {
            $output = process_shortcodes($output);
        }
        
        return $output;
    }
    
    /**
    * Возвращает публичный адрес записи фрагмента
    * 
    * @param array $fragment
    * @param array $entry
    * @return string
    */
    public static function getEntryUrl($fragment, $entry) {
        if (empty($fragment['entry_template_enabled'])) {
            return '';
        }
        
        $systemName = trim((string)($fragment['system_name'] ?? ''));
        $slug = trim((string)($entry['slug'] ?? ''));
        
        if ($systemName === '' || $slug === '') {
            return '';
        }
        
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        
        return $baseUrl . '/f-' . rawurlencode($systemName) . '/' . rawurlencode($slug) . '.html';
    }
    
    /**
    * Подставляет значения записи в шаблон
    * 
    * @param string $template
    * @param array $fragment
    * @param array $entry
    * @param array $fields
    * @param int $index
    * @param int $total
    * @return string
    */
    private static function applyEntryPlaceholders($template, $fragment, $entry, $fields, $index, $total) {
        $url = self::getEntryUrl($fragment, $entry);
        $slug = (string)($entry['slug'] ?? '');
        
        $replacements = [
            '{index}' => (string)$index,
            '{total}' => (string)$total,
            '{is_first}' => $index === 1 ? 'true' : 'false',
            '{is_last}' => $index === $total ? 'true' : 'false',
            '{is_even}' => $index % 2 === 0 ? 'true' : 'false',
            '{is_odd}' => $index % 2 !== 0 ? 'true' : 'false',
            '{id}' => (string)($entry['id'] ?? ''),
            '{slug}' => htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'),
            '{entry_slug}' => htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'),
            '{url}' => $url !== '' ? htmlspecialchars($url, ENT_QUOTES, 'UTF-8') : '',
            '{entry_url}' => $url !== '' ? htmlspecialchars($url, ENT_QUOTES, 'UTF-8') : '',
            '{fragment_name}' => htmlspecialchars((string)($fragment['name'] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{fragment_system_name}' => htmlspecialchars((string)($fragment['system_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];
        
        $output = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        foreach ($fields as $field) {
            $fieldName = $field['system_name'];
            $value = $entry['data'][$fieldName] ?? '';
            
            $output = str_replace(
                '{field:' . $fieldName . '}',
                htmlspecialchars(is_scalar($value) ? (string)$value : '', ENT_QUOTES, 'UTF-8'),
                $output
            );
            $output = str_replace(
                '{field_display:' . $fieldName . '}',
                self::renderFieldValue($field, $value),
                $output
            );
        }
        
        return $output;
    }
    
    /**
    * Рендеринг фрагмента (простой вывод)
    * 
    * @param string $systemName
    * @return string
    */
    public static function renderFragment($systemName) {
        self::init();
        
        $fragment = self::$fragmentModel->getBySystemName($systemName);
        
        if (!$fragment || $fragment['status'] !== 'active') {
            return '<!-- Фрагмент "' . htmlspecialchars($systemName) . '" не найден -->';
        }
        
        self::loadFragmentAssets($fragment);
        
        $entries = self::$entryModel->getByFragment($fragment['id'], 'active');
        $fields = self::$fragmentModel->getFields($fragment['id']);
        
        $templateFile = self::findFragmentTemplate($fragment['system_name']);
        
        if ($templateFile && file_exists($templateFile)) {
            ob_start();
            extract(['fragment' => $fragment, 'entries' => $entries, 'fields' => $fields]);
            include $templateFile;
            return ob_get_clean();
        } else {
            return self::renderDefault($fragment, $entries, $fields);
        }
    }
    
    /**
    * Загрузка ресурсов фрагмента
    * @param array $fragment
    */
    private static function loadFragmentAssets($fragment) {
        if (!empty($fragment['css_files'])) {
            foreach ($fragment['css_files'] as $cssFile) {
                if (!empty($cssFile)) {
                    front_css($cssFile);
                }
            }
        }
        
        if (!empty($fragment['js_files'])) {
            foreach ($fragment['js_files'] as $jsFile) {
                if (!empty($jsFile)) {
                    front_js($jsFile);
                }
            }
        }
        
        if (!empty($fragment['inline_css'])) {
            front_inline_css($fragment['inline_css']);
        }
        
        if (!empty($fragment['inline_js'])) {
            front_inline_js($fragment['inline_js']);
        }
    }
    
    /**
    * Поиск шаблона фрагмента в теме
    * @param string $systemName
    * @return string|null
    */
    private static function findFragmentTemplate($systemName) {
        $currentTheme = defined('DEFAULT_TEMPLATE') ? DEFAULT_TEMPLATE : 'default';
        
        $paths = [
            BASE_PATH . "/templates/{$currentTheme}/front/fragments/{$systemName}.php",
            BASE_PATH . "/templates/default/front/fragments/{$systemName}.php"
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
    * Стандартный рендеринг фрагмента
    * @param array $fragment
    * @param array $entries
    * @param array $fields
    * @return string
    */
    private static function renderDefault($fragment, $entries, $fields) {
        ob_start();
        ?>
        <div class="fragment fragment-<?php echo htmlspecialchars($fragment['system_name']); ?>">
            <?php if (!empty($fragment['description'])) { ?>
                <div class="fragment-description">
                    <?php echo htmlspecialchars($fragment['description']); ?>
                </div>
            <?php } ?>
            
            <?php if (empty($entries)) { ?>
                <div class="fragment-empty">
                    <p>Нет записей в этом фрагменте</p>
                </div>
            <?php } else { ?>
                <div class="fragment-entries">
                    <?php foreach ($entries as $entry) { ?>
                        <div class="fragment-entry">
                            <?php echo self::renderEntryFields($entry, $fields); ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Стандартный рендеринг одной записи (если шаблон записи не задан)
    * 
    * @param array $fragment
    * @param array $entry
    * @param array $fields
    * @return string
    */
    private static function renderDefaultEntry($fragment, $entry, $fields) {
        ob_start();
        ?>
        <div class="fragment fragment-<?php echo htmlspecialchars($fragment['system_name']); ?>">
            <div class="fragment-entries">
                <div class="fragment-entry">
                    <?php echo self::renderEntryFields($entry, $fields); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Рендеринг полей одной записи
    * 
    * @param array $entry
    * @param array $fields
    * @return string
    */
    private static function renderEntryFields($entry, $fields) {
        ob_start();
        ?>
        <?php foreach ($fields as $field) { ?>
            <?php $value = $entry['data'][$field['system_name']] ?? null; ?>
            <?php if ($value !== null && $value !== '') { ?>
                <div class="fragment-field fragment-field-<?php echo htmlspecialchars($field['system_name']); ?>">
                    <div class="field-label"><?php echo htmlspecialchars($field['name']); ?>:</div>
                    <div class="field-value">
                        <?php echo self::renderFieldValue($field, $value); ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Рендеринг значения поля
    * @param array $field
    * @param mixed $value
    * @return string
    */
    private static function renderFieldValue($field, $value) {
        return self::$fieldManager->renderFieldDisplay(
            $field['type'],
            $value,
            $field['config'],
            'fragment_entry',
            0
        );
    }
    
    /**
    * Планирует отложенную регистрацию шорткодов
    */
    private static function scheduleDelayedRegistration() {
        static $scheduled = false;
        
        if ($scheduled) {
            return;
        }
        
        $scheduled = true;
        
        if (class_exists('Event')) {
            Event::listen('app.init', function() {
                self::registerShortcodes();
            }, 100);
        }
    }
}