<?php

namespace html_blocks\actions;

/**
* Абстрактный базовый класс для действий управления HTML-блоками
* @package html_blocks\actions
* @abstract
*/
abstract class HtmlBlockAction {
    
    protected $db;
    protected $params;
    protected $controller;
    protected $htmlBlockModel;
    protected $blockTypeManager;
    protected $id;
    protected $breadcrumbs;
    protected $pageTitle;
    
    /**
    * Конструктор базового класса действий HTML-блоков
    * @param \Database $db Объект подключения к базе данных
    * @param array $params Дополнительные параметры действия
    */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->htmlBlockModel = new \HtmlBlockModel($db);
        $this->blockTypeManager = new \HtmlBlockTypeManager($db);
        $this->id = $params['id'] ?? null;
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    /**
    * Установка контроллера для действия
    * @param object $controller Объект контроллера
    * @return void
    */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
    * Установка ID блока
    * @param int $id ID HTML-блока
    * @return void
    */
    public function setId($id) {
        $this->id = $id;
    }

    /**
    * Устанавливает заголовок страницы
    * @param string $title Заголовок
    * @return self
    */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }

    /**
    * Добавляет элемент в хлебные крошки
    * @param string $title Название элемента
    * @param string|null $url URL элемента
    * @return self
    */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
    * Абстрактный метод выполнения действия
    * @return mixed Результат выполнения действия
    * @abstract
    */
    abstract public function execute();
    
    /**
    * Рендеринг шаблона с данными
    * @param string $template Путь к файлу шаблона
    * @param array $data Массив данных для передачи в шаблон
    * @return void
    * @throws \Exception Если контроллер не установлен
    */
    protected function render($template, $data = []) {
        if ($this->controller) {
            if (!isset($data['breadcrumbs'])) {
                $data['breadcrumbs'] = $this->breadcrumbs;
            }
            if (!isset($data['title']) && $this->pageTitle) {
                $data['title'] = $this->pageTitle;
            }
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    /**
    * Перенаправление на указанный URL
    * @param string $url URL для перенаправления
    * @return void
    */
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    /**
    * Получение доступных шаблонов из типов блоков
    * @param array $blockTypes Массив типов блоков
    * @return array Ассоциативный массив шаблонов с ключами и значениями
    */
    protected function getAvailableTemplates($blockTypes = []): array {
        $uniqueTemplates = ['all' => LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_ALL_TEMPLATES];
        
        foreach($blockTypes as $type) {
            $template = $type['template'] ?? 'all';
            if (!isset($uniqueTemplates[$template])) {
                $uniqueTemplates[$template] = $template;
            }
        }
        
        return $uniqueTemplates;
    }
    
    /**
    * Обработка файлов ресурсов (CSS, JS)
    * @param array $files Массив файлов ресурсов
    * @return array Отфильтрованный массив файлов
    */
    protected function processAssetFiles($files): array {
        return array_filter($files, function($file) {
            return !empty(trim($file));
        });
    }
    
    /**
    * Рендеринг формы с данными для повторного отображения
    * @param array $data Данные из формы
    * @param string $blockTypeName Имя типа блока
    * @param array|null $block Исходные данные блока (при редактировании)
    * @return void
    */
    protected function renderFormWithData($data, $blockTypeName, $block = null) {

        $blockTypes = $this->blockTypeManager->getBlockTypes();
        $selectedType = $blockTypeName;
        
        $settings = $data['settings'] ?? ($block ? json_decode($block['settings'], true) : []);

        $cssFiles = $data['css_files'] ?? ($block && $block['css_files'] ? json_decode($block['css_files'], true) : []);
        $jsFiles = $data['js_files'] ?? ($block && $block['js_files'] ? json_decode($block['js_files'], true) : []);
        $inlineCss = $data['inline_css'] ?? ($block['inline_css'] ?? '');
        $inlineJs = $data['inline_js'] ?? ($block['inline_js'] ?? '');
        
        $systemCss = [];
        $systemJs = [];

        $availableTemplates = ['default' => LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_DEFAULT_TEMPLATE];
        if ($blockTypeName !== 'DefaultBlock') {
            $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
            if ($blockType && $blockType['class']) {
                $availableTemplates = $blockType['class']->getAvailableTemplates();
                $systemCss = $blockType['class']->getSystemCss();
                $systemJs = $blockType['class']->getSystemJs();
            }
        }

        $selectedTemplate = $data['template'] ?? ($block['template'] ?? 'default');

        $this->render('admin/html_blocks/form', [
            'block' => $block,
            'data' => $data,
            'blockTypes' => $blockTypes,
            'selectedType' => $selectedType,
            'settings' => $settings,
            'cssFiles' => $cssFiles,
            'jsFiles' => $jsFiles,
            'inlineCss' => $inlineCss,
            'inlineJs' => $inlineJs,
            'systemCss' => $systemCss,
            'systemJs' => $systemJs,
            'availableTemplates' => $availableTemplates,
            'selectedTemplate' => $selectedTemplate
        ]);
    }
    
    /**
    * Рендеринг пустой формы или формы для редактирования
    * @param array|null $block Данные блока для редактирования (null для создания)
    * @param string $blockTypeName Имя типа блока
    * @return void
    */
    protected function renderForm($block = null, $blockTypeName = 'DefaultBlock') {
        $blockTypes = $this->blockTypeManager->getBlockTypes();
        $selectedType = $blockTypeName;
        
        $settings = [];
        if ($block && !empty($block['settings'])) {
            $settings = json_decode($block['settings'], true);
        }
        
        $cssFiles = [];
        $jsFiles = [];
        $inlineCss = '';
        $inlineJs = '';
        
        if ($block) {
            $cssFiles = !empty($block['css_files']) ? json_decode($block['css_files'], true) : [];
            $jsFiles = !empty($block['js_files']) ? json_decode($block['js_files'], true) : [];
            $inlineCss = $block['inline_css'] ?? '';
            $inlineJs = $block['inline_js'] ?? '';
        }
        
        $systemCss = [];
        $systemJs = [];
        if ($blockTypeName !== 'DefaultBlock') {
            $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
            if ($blockType && $blockType['class']) {
                $systemCss = $blockType['class']->getSystemCss();
                $systemJs = $blockType['class']->getSystemJs();
            }
        }

        $availableTemplates = ['default' => LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_DEFAULT_TEMPLATE];
        if ($blockTypeName !== 'DefaultBlock' && isset($blockType) && $blockType['class']) {
            $availableTemplates = $blockType['class']->getAvailableTemplates();
        }
        
        $selectedTemplate = $block['template'] ?? 'default';

        $this->render('admin/html_blocks/form', [
            'block' => $block,
            'blockTypes' => $blockTypes,
            'selectedType' => $selectedType,
            'settings' => $settings,
            'cssFiles' => $cssFiles,
            'jsFiles' => $jsFiles,
            'inlineCss' => $inlineCss,
            'inlineJs' => $inlineJs,
            'systemCss' => $systemCss,
            'systemJs' => $systemJs,
            'availableTemplates' => $availableTemplates,
            'selectedTemplate' => $selectedTemplate
        ]);
    }

    /* Возвращает менеджер хлебных крошек 
    * @return \BreadcrumbsManager
    */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }

    /**
    * Получение HTML-формы настроек для DefaultBlock
    * @param array $settings Текущие настройки
    * @return string HTML форма
    */
    protected function getDefaultBlockSettingsForm($settings = []) {
        $html = $settings['html'] ?? '';
        $useFragment = $settings['use_fragment'] ?? false;
        $selectedFragment = $settings['selected_fragment'] ?? '';
        
        ob_start();
        ?>
        <div class="mb-4">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" 
                    id="use_fragment" name="settings[use_fragment]" value="1"
                    <?php echo $useFragment ? 'checked' : ''; ?>>
                <label class="form-check-label fw-semibold" for="use_fragment">
                    <?php echo bloggy_icon('bs', 'puzzle', '16', '#0d6efd', 'me-1'); ?>
                    <?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_USE_FRAGMENT; ?>
                </label>
                <div class="form-text"><?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_USE_FRAGMENT_HINT; ?></div>
            </div>
            
            <div id="fragment-selector" style="display: <?php echo $useFragment ? 'block' : 'none'; ?>;">
                <div class="mb-3">
                    <label class="form-label fw-semibold d-flex align-items-center">
                        <?php echo bloggy_icon('bs', 'list-ul', '16', '#0d6efd', 'me-1'); ?>
                        <?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_SELECT_FRAGMENT; ?>
                    </label>
                    <select name="settings[selected_fragment]" id="selected_fragment" class="form-select">
                        <option value=""><?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_SELECT_FRAGMENT_OPTION; ?></option>
                    </select>
                    <div class="form-text"><?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_SELECT_FRAGMENT_HINT; ?></div>
                </div>
                
                <div id="fragment-shortcodes" class="mt-3" style="display: none;">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <?php echo bloggy_icon('bs', 'code-slash', '16', '#0d6efd', 'me-1'); ?>
                                <?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_AVAILABLE_SHORTCODES; ?>
                            </h6>
                        </div>
                        <div class="card-body" id="shortcodes-list">
                            <div class="text-muted text-center py-3">
                                <?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_SELECT_FRAGMENT_PROMPT; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mb-4" id="html-editor-container" style="display: <?php echo $useFragment ? 'none' : 'block'; ?>;">
            <label class="form-label fw-semibold d-flex align-items-center">
                <?php echo bloggy_icon('bs', 'code', '16', '#0d6efd', 'me-2'); ?>
                <?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_HTML_CODE; ?>
            </label>
            <div class="mb-2">
                <small class="text-muted">
                    <?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_HTML_CODE_HINT; ?>
                </small>
            </div>
            <div class="border rounded overflow-hidden">
                <div id="default-block-html-editor" style="height: 400px; width: 100%;" class="ace-editor"><?php echo html($html); ?></div>
            </div>
            <textarea name="settings[html]" id="default-block-html" style="display: none;"><?php echo html($html); ?></textarea>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const useFragmentCheckbox = document.getElementById('use_fragment');
            const fragmentSelector = document.getElementById('fragment-selector');
            const htmlEditorContainer = document.getElementById('html-editor-container');
            const fragmentSelect = document.getElementById('selected_fragment');
            const fragmentShortcodes = document.getElementById('fragment-shortcodes');
            const shortcodesList = document.getElementById('shortcodes-list');
            
            function loadFragments() {
                fetch('<?php echo ADMIN_URL; ?>/html-blocks/get-fragments')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const selectedValue = '<?php echo addslashes($selectedFragment); ?>';
                            fragmentSelect.innerHTML = '<option value=""><?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_SELECT_FRAGMENT_OPTION; ?></option>';
                            
                            data.fragments.forEach(fragment => {
                                const option = document.createElement('option');
                                option.value = fragment.system_name;
                                option.textContent = fragment.name + ' (' + fragment.fields.length + ' <?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_FIELDS_COUNT; ?>)';
                                if (option.value === selectedValue) {
                                    option.selected = true;
                                }
                                fragmentSelect.appendChild(option);
                            });
                            
                            if (selectedValue) {
                                loadFragmentShortcodes(selectedValue);
                            }
                        }
                    })
                    .catch(error => console.error('Error loading fragments:', error));
            }
            
            function loadFragmentShortcodes(systemName) {
                fetch('<?php echo ADMIN_URL; ?>/html-blocks/get-fragments')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const fragment = data.fragments.find(f => f.system_name === systemName);
                            if (fragment) {
                                let html = '<div class="mb-3"><strong><?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_BASIC_SHORTCODES; ?></strong>';
                                html += '<div class="bg-white rounded p-2 mb-2"><code class="d-block">' + fragment.shortcode_simple + '</code>';
                                html += '<small class="text-muted"><?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_SIMPLE_OUTPUT; ?></small></div>';
                                html += '<div class="bg-white rounded p-2"><code class="d-block">' + fragment.shortcode_loop + '</code>';
                                html += '<small class="text-muted"><?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_CUSTOM_OUTPUT; ?></small></div></div>';
                                
                                html += '<strong><?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_FRAGMENT_FIELDS; ?></strong>';
                                fragment.fields.forEach(field => {
                                    html += '<div class="bg-white rounded p-2 mb-2">';
                                    html += '<div class="fw-semibold">' + field.name + ' (' + field.type + ')</div>';
                                    html += '<code class="d-block small">' + field.shortcode + '</code>';
                                    html += '<code class="d-block small">' + field.display_shortcode + '</code>';
                                    html += '<small class="text-muted">' + (field.type === 'image' ? '<?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_IMAGE_DESC; ?>' : '<?php echo LANG_ACTION_HTMLBLOCKS_HTMLBLOCKACTION_VALUE_DESC; ?>') + '</small>';
                                    html += '</div>';
                                });
                                
                                shortcodesList.innerHTML = html;
                                fragmentShortcodes.style.display = 'block';
                            }
                        }
                    })
                    .catch(error => console.error('Error loading fragment shortcodes:', error));
            }
            
            useFragmentCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    fragmentSelector.style.display = 'block';
                    htmlEditorContainer.style.display = 'none';
                } else {
                    fragmentSelector.style.display = 'none';
                    htmlEditorContainer.style.display = 'block';
                    fragmentShortcodes.style.display = 'none';
                }
            });
            
            fragmentSelect.addEventListener('change', function() {
                if (this.value) {
                    loadFragmentShortcodes(this.value);
                } else {
                    fragmentShortcodes.style.display = 'none';
                }
            });
            
            if (typeof ace !== 'undefined' && document.getElementById('default-block-html-editor')) {
                const htmlEditor = ace.edit("default-block-html-editor", {
                    theme: "ace/theme/monokai",
                    mode: "ace/mode/html",
                    showPrintMargin: false,
                    fontSize: "14px",
                    tabSize: 4,
                    useSoftTabs: true,
                    wrap: true,
                    minLines: 20,
                    maxLines: 40
                });
                
                htmlEditor.session.setUseWrapMode(true);
                htmlEditor.setOptions({
                    enableBasicAutocompletion: true,
                    enableLiveAutocompletion: true,
                    enableSnippets: true
                });
                
                const form = document.getElementById('blockForm');
                if (form) {
                    form.addEventListener('submit', function() {
                        const textarea = document.getElementById('default-block-html');
                        textarea.value = htmlEditor.getValue();
                    });
                }
            }
            
            loadFragments();
        });
        </script>
        <?php
        return ob_get_clean();
    }
}